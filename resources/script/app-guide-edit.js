(function ($) {
	function CADBGuideEditor(element,options) {
		var self = this;
		this.settings = $.extend({}, $.fn.cadbGuideEditor.defaults, options);

		this.Root = jQuery(element);

		var transEndEventNames = {
			'WebkitTransition'	: 'webkitTransitionEnd',
			'MozTransition'		: 'transitionend',
			'transition'		: 'transitionend'
		}

		this.transitionEnd = transEndEventNames[ Modernizr.prefixed('transition') ];

		this.indexes = this.Root.find('.guide-clause-indexes-box');
		this.documents = this.Root.find('.guide-clause-document');

		this.initGuideInfo();
		this.initIndex();
		this.initContentEditor();
	}

	CADBGuideEditor.prototype = {
		initGuideInfo: function() {
			var self = this;

			this.info = this.Root.find('.guide-info-container form');

			this.info.submit(function(e) {
				e.preventDefault();
				$this = jQuery(this);
				if(!$this.find('#guide-subject').val()) {
					self.alertMessage($this,'제목을 입력하세요');
					return false;
				} else {
					self.removeAlert($this);
				}
				if(!$this.find('#guide-year').val()) {
					self.alertMessage($this,'년도를 입력하세요');
					return false;
				} else {
					self.removeAlert($this);
				}

				var url = "/admin/standards/save";
				var params = jQuery.param($this.serializeArray());

				jQuery.ajax({
					url:  url,
					data: params,
					dataType: 'json',
					method: 'POST',
					beforeSend: function() {
						self.loading();
					},
					success: function(json) {
						var error = parseInt(json.error);
						var message = json.message;
						self.removeLoading();
						if(error < 0) {
							self.abortDialog(message, ( error * -1 ) )
						} else if(!error) {
							self.abortDialog(message, 0 )
						} else {
						}
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						self.removeLoading();
						self.abortDialog(jqXHR.responseText,0);
					}
				});
			});

			this.info.find('button.delete').click(function(e) {
				var url = "/admin/standards/save";
				var params = "table="+this.info.find('input[name="table"]').val();
				params += "mode=delete";
				params += "nid="+this.info.find('input[name="nid"]').val();

				jQuery.ajax({
					url:  url,
					data: params,
					dataType: 'json',
					method: 'POST',
					beforeSend: function() {
						self.loading();
					},
					success: function(json) {
						var error = parseInt(json.error);
						var message = json.message;
						self.removeLoading();
						if(error < 0) {
							self.abortDialog(message, ( error * -1 ) )
						} else if(!error) {
							self.abortDialog(message, 0 )
						} else {
							document.location.href = '/admin/standards';
						}
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						self.removeLoading();
						self.abortDialog(jqXHR.responseText,0);
					}
				});
			});
		},

		initIndex: function() {
			var self = this;
			this.indexes.find('dl').each(function() {
				$this = jQuery(this);
				if($this.hasClass('dummy')) {
					self.makeMarkup($this);
				} else {
					self.bindDl($this);
				}
			});
		},

		makeMarkup: function(element) {
			this.chapter = element.clone();
			this.chapter.removeClass('dummy').addClass('collapsed');

			this.article = this.chapter.find('dd.chapter-articles article.dummry').clone();;
			this.article.removeClass('dummry').addClass('article');
			this.article.prepend('<span></span>');
		},

		bindDl: function(element) {
			var self = this;
			element.find('dt').click(function(e) {
				$this = jQuery(this);
				var dl = $this.parent();
				dl.addClass('current');
				dl.siblings().removeClass('current');

				if(dl.hasClass('collapsed')) {
					dl.removeClass('collapsed');
				} else {
					dl.addClass('collapsed');
				}
				var cId = self.documents.attr("data-id");
				if(cId != $this.attr('data-id')) {
					self.getClause($this.attr('data-id'));
				}
				self.Root.find('.console').removeClass('extended');
			});
			self.bindnewChapter(element.find('dd.console'));
			element.find('dd.chapter-articles article').each(function() {
				$this = jQuery(this);
				self.bindDd($this);
			});
		},

		bindDd: function(element) {
			var self = this;
			element.find('span').click(function(e) {
				var cId = self.documents.attr("data-id");
				if(cId != element.attr('data-id')) {
					self.getClause(element.attr('data-id'));
				}
				if(!element.hasClass('current')) {
					self.Root.find('dd article').removeClass('current');
					element.addClass('current');
				}
				self.Root.find('.console').removeClass('extended');
			});
			self.bindnewArticle(element.find('.console'));
		},

		bindnewChapter: function(element) {
			var self = this;
			element.bind('click',function(e) {
				self.documents.attr('data-id',0);
				self.documents.attr('data-parent',0);
				var idx = parseInt(jQuery(this).parents('dl').attr('data-index')) + 1;
				self.documents.attr('data-index',idx);
				self.documents.find('h3#guide-clause-subject').text('').focus();
				self.documents.find('div#guide-clause-content').html('');
				self.documents.find('div.guide-clause-field').each(function(i) {
					var $this = jQuery(this);
					$this.removeClass('article').addClass('parent');
					$this.find('.guide-clause-field-content').html('');
				});
				self.initContentEditor();
				jQuery(this).addClass('extended');
			});
		},

		bindnewArticle: function(element) {
			var self = this;
			element.bind('click',function(e) {
				self.documents.attr('data-id',0);
				self.documents.attr('data-parent',jQuery(this).parents('article').attr('data-parent'));
				var idx = parseInt(jQuery(this).parents('article.article').attr('data-index')) + 1;
				self.documents.attr('data-index',idx);
				self.documents.find('h3#guide-clause-subject').text('').focus();
				self.documents.find('div#guide-clause-content').html('');
				self.documents.find('div.guide-clause-field').each(function(i) {
					var $this = jQuery(this);
					$this.addClass('article').removeClass('parent');
					$this.find('.guide-clause-field-content').html('');
				});
				self.initContentEditor();
				jQuery(this).addClass('extended');
			});
		},

		initContentEditor: function() {
			var self = this;
			var content = this.documents.find('div#guide-clause-content');
			var editor = content.data('editor');
			if(typeof editor == 'undefined') {
				var editor = new MediumEditor('#guide-clause-content', {
					buttonLabels: 'fontawesome',
					toolbar: {
						buttons: [
							"bold"
							, "italic"
							, "underline"
							, "anchor"
							, "h2"
							, "h3"
							, "h4"
							, "quote"
								, "table"
						]
					},
					extensions: {
						table: new MediumEditorTable()
					}
				});
				content.data('editor',editor);
			}
			var mbutton = this.documents.find('button.modify');
			if(mbutton.data('bind-event') != true) {
				mbutton.bind('click',function(e) {
					e.preventDefault();
					self.saveClause();
				});
				mbutton.data('bind-event',true);
			}
		},

		getClause: function(id) {
			var self = this;

			var url = '/api/standards';
			var params = 'id='+id;

			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'GET',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					var error = json.result.found;
					var standard = json.standard;
					self.removeLoading();
					if(error != true) {
						self.abortDialog(json.result.error,0);
					} else {
						self.pushDocument(json.standard);
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		pushDocument(json) {
			var self = this;
			this.documents.attr('data-id',json.id);
			this.documents.attr('data-index',json.idx);
			this.documents.attr('data-parent',json.parent);
			this.documents.find('h3#guide-clause-subject').text(json.subject);
			this.documents.find('div#guide-clause-content').html(json.content);
			this.initContentEditor();

			if(parseInt(json.parent) > 0) {
				this.documents.find('div.guide-clause-field').removeClass('parent').addClass('article');
				jQuery.each(json, function(k,data) {
					if(k.substr(0,1) == 'f') {
						self.documents.find('div#guide-clause-field-'+k+' .guide-clause-field-content').html(data);
					}
				});
				this.documents.find('div.guide-clause-field').each(function(i) {
					var $this = jQuery(this);
					var id = $this.attr('id');
					var editor = new MediumEditor('#'+id, {
						buttonLabels: 'fontawesome',
						toolbar: {
							buttons: [
								"bold"
								, "italic"
								, "underline"
								, "anchor"
								, "h2"
								, "h3"
								, "h4"
								, "quote"
								, "table"
							]
						},
						extensions: {
							table: new MediumEditorTable()
						}
					});
					$this.data('editor',editor);
				});
			} else {
				this.documents.find('div.guide-clause-field').removeClass('article').addClass('parent');
				this.documents.find('div.guide-clause-field').each(function(i) {
					var $this = jQuery(this);
					var editor = $this.data('editor');
					if(typeof editor != 'undefined') {
						editor.destroy();
					}
					$this.find('.guide-clause-field-content').html('');
				});
			}
		},

		saveClause: function() {
			var self = this;
			var id = parseInt(this.documents.attr('data-id'));
			var idx = parseInt(this.documents.attr('data-index'));

			var params = "table=clause";
			if(!id) {
				var mode = 'add';
				params += "&mode=add";
			} else {
				var mode = 'modify';
				params += "&mode=modify";
				params += "&id="+id;
			}

			var s = this.documents.find('#guide-clause-subject').text();
			if(!s) {
				this.alertMessage(this.documents.find('#guide-clause-subject'),'제목을 입력하세요');
				return false;
			} else {
				this.removeAlert(this.documents.find('#guide-clause-subject'));
			}
			params += "&subject="+encodeURIComponent(s);
			params += "&parent="+this.documents.attr('data-parent');
			params += "&idx="+this.documents.attr('data-index');
			params += "&content="+encodeURIComponent(this.documents.find('#guide-clause-content').html());
			this.documents.find('div.guide-clause-field').each(function(i) {
				$this = jQuery(this);
				if($this.hasClass('article')) {
					if($this.find('guide-clause-field-content').text()) {
						params += "&f"+$this.attr('data-fid')+"="+encodeURIComponent($this.find('guide-clause-field-content').html());
					} else {
						params += "&f"+$this.attr('data-fid')+"=";
					}
				}
			});

			var url = "/admin/standards/save";

			jQuery.ajax({
				url:  url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					var error = parseInt(json.error);
					var message = json.message;
					self.removeLoading();
					if(error < 0) {
						self.abortDialog(message, 0 )
					} else {
						var p = parseInt(self.documents.attr('data-parent'));
						if(mode == 'modify') {
							var id = parseInt(self.documents.attr('data-id'));
							if(p > 0) {
								self.indexes.find('dd.chapter-articles article#guide-article-'+id+' span').text(s);
							} else {
								self.indexes.find('dl#guide-chapter-'+id+' dt.chapter-title').text(s);
							}
						} else {
							self.documents.attr('data-id',error);
							if(p > 0) {
								self.appendArticle();
							} else {
								self.appendChapter();
							}
						}
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		appendChapter: function() {
			var chapter = this.chapter.clone();
			chapter.attr('id','guide-chapter-'+this.documents.attr('data-id'));
			chapter.attr('data-id',this.documents.attr('data-id'));
			chapter.attr('data-parent',this.documents.attr('data-parent'));
			chapter.attr('data-index',this.documents.attr('data-index'));
			chapter.find('dt.chapter-title').attr('data-id',this.documents.attr('data-id'));
			chapter.find('dt.chapter-title').text(this.documents.find('#guide-clause-subject').text());
			chapter.find('dd.chapter-articles article').atrr('data-parent',this.documents.attr('data-id'));
			chapter.removeClass('collapsed').addClass('current');

			self.bindDl(chapter);
			chapter.insertBefore(this.indexes.find('dl[data-index="'+this.documents.attr('data-index')+'"]'));

			this.resortChapters();
		},

		removeChapter: function(id) {
			var obj = this.indexes.find('dl#guide-chapter-'+id);
			if(obj.length > 0) {
				var art = obj.find('dd.chapter-articles article.article');
				if(art.length > 0) {
					return;
				}
				obj.remove();
				this.resortChapters();
			}
		},

		appendArticle: function() {
			var article = this.aritlce.clone();
			article.attr('id','guide-article-'+this.documents.attr('data-id'));
			article.attr('data-id',this.documents.attr('data-id'));
			article.attr('data-parent',this.documents.attr('data-parent'));
			article.attr('data-index',this.documents.attr('data-index'));
			article.find('span').text(this.documents.find('#guide-clause-subject').text());

			this.bindDd(article);
			chapter.insertBefore(this.indexes.find('dl#guide-chapter-'+this.documents.attr('data-parent')+' dd.chapter-articles article.article[data-index="'+this.documents.attr('data-index')+'"]'));

			this.resortArticles(this.documents.attr('data-parent'));
		},

		removeArticles: function(parents,id) {
			this.indexes.find('dl#guide-chapter-'+parents+' dd.chapter-articles article#guide-article-'+id).remove();
			this.resortArticles(parents);
		},

		resortChapter: function() {
			this.indexes.find('dl').each(function(i) {
				$this = jQuery(this);
				if(!$this.hasClass('dummy')) {
					$this.attr('data-index',(i+1));
				}
			});
		},

		resortArticles: function(id) {
			var dl = this.indexes.find('dl#guide-chapter-'+id);
			if(dl.length > 0) {
				dl.find('dd.chapter-articles article.article').each(function(i) {
					jQuery(this).attr('data-index',(i+1));
				});
			}
		},

		abortDialog: function(message,fid) {
			var self = this;
			this.focus_id = fid;
			if(!this.dialog) {
				this.dialog = jQuery(this.settings.dialog);
				this.dialog.appendTo('body');
			} else {
				this.dialog.show();
			}
			this.mode = 'dialog';
			this.dialog.find('.alert-dialog-inner').html(message);
			var w = this.dialog.outerWidth();
			var h = this.dialog.outerHeight();
			this.dialog.css({
				'left' : parseInt( ( jQuery(window).width() - w ) / 2 )+'px',
				'top' : parseInt( ( jQuery(window).height() - h ) / 2 )+'px'
			});
			var d = this.dialog.find('.close');
			if(d.attr('init-bind') != true) {
				d.bind('click',function(e) {
					self.closeDialog();
				});
				d.attr('init-bind',true);
			}
		},

		closeDialog: function() {
			this.dialog.hide();
			if(this.focus_id) {
			jQuery('.cadb-guide-column[tabindex="'+Math.abs(this.focus_id)+'"]').focus();
				this.focus_id = 0;
			}
			this.mode = 'dialog';
		},

		alertMessage: function(element,message) {
			element.focus();
			element.addClass('cadb-input-alert');
			var alertID = 'cadb-alert-'+element.attr('data-alert');
			var contain = element.parent();
			contain.addClass('cadb-field-alert');
			var alerts = jQuery('span#'+alertID);
			if(!alerts.length) {
				var ui = this.uniqueID(8);
				var alerts = jQuery('<span id="cadb-alert-' + ui + '" class="cadb-message-alert">'+message+'</span>');
				alerts.appendTo('body');
				var l = element.offset().left;
				var t = element.offset().top + element.outerHeight() + 8;
				alerts.css({
					'left' : l+'px',
					'top' : t+'px'
				});
				element.attr('data-alert',ui);
			} else {
				alerts.text(message);
			}
		},

		uniqueID: function(size) {
			var getRandomNumber = function(range) {
				return Math.floor(Math.random() * range);
			};

			var getRandomChar = function() {
				var chars = "abcdefghijklmnopqurstuvwxyzABCDEFGHIJKLMNOPQURSTUVWXYZ";
				return chars.substr( getRandomNumber(62), 1 );
			};

			var randomID = function(size) {
				var str = "";
				for(var i = 0; i < size; i++) {
					str += getRandomChar();
				}
				return str;
			};

			return randomID(size);
		},

		removeAlert: function(element) {
			var contain = element.parent();
			var alertID = 'cadb-alert-'+element.attr('data-alert');
			element.removeClass('cadb-input-alert');
			contain.removeClass('cadb-field-alert');
			jQuery('span#'+alertID).remove();
		},

		loading: function() {
			jQuery('body').append(jQuery('<div class="saving"><div class="saving-background"></div><div class="is-loading"><i class="fa fa-spinner fa-pulse"></i></div></div>'));
			jQuery('.saving .is-loading').css({
				'left' : parseInt( ( jQuery(window).width() - 100 ) / 2 ),
				'top' : parseInt( ( jQuery(window).height() - 100 ) / 2 )
			});
		},

		removeLoading: function() {
			jQuery('body .saving').remove();
		}
	}

	jQuery.fn.cadbGuideEditor = function(options) {
		return this.each(function() {
			var cadbGuideEditor = new CADBGuideEditor(jQuery(this),options);
		});
	};

	jQuery.fn.cadbGuideEditor.defaults = {
		dialog : '<div class="alert-dialog"><div class="alert-dialog-inner"></div><div class="close"><i class="fa fa-close"></i></div></div>'
	};
})(jQuery);

jQuery(document).ready(function(e) {
	jQuery('.guide-edit').cadbGuideEditor({
	});
});
