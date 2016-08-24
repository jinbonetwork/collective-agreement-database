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
				self.bindDl($this);
			});
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
			});
			element.find('dd article').each(function() {
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
			this.documents.find('button.modify').bind('click',function(e) {
				e.preventDefault();
				self.saveClause();
			});
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
				params += "&mode=add";
			} else {
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
						var id = parseInt(self.documents.attr('data-id'));
						var p = parseInt(self.documents.attr('data-parent'));
						if(p > 0) {
							self.indexes.find('dd.chapter-articles article#guide-article-'+id+' span').text(s);
						} else {
							self.indexes.find('dl#guide-chapter-'+id+' dt.chapter-title').text(s);
						}
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
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
