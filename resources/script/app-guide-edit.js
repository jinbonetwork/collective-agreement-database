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

				var url = site_base_uri+"/admin/standards/save";
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
				var url = site_base_uri+"/admin/standards/save";
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

			var article = this.chapter.find('dd.chapter-articles article.dummy');
			this.article = article.clone();
			this.article.removeClass('dummy').addClass('article');
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
					dl.removeClass('collapsed').siblings().addClass('collapsed');
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
				jQuery(this).parents('dl').removeClass('current');
				self.documents.addClass('new').removeClass('delete');
				self.documents.attr('data-id',0);
				self.documents.attr('data-parent',0);
				var idx = parseInt(jQuery(this).parents('dl').attr('data-index')) + 1;
				self.documents.attr('data-index',idx);
				self.documents.find('h3#guide-clause-subject').text('').focus();
				var _l = self.documents.find('.guide-clause-taxonomy-item>label').text();
				var l = _l.split(/ /g);
				self.documents.find('.guide-clause-taxonomy-item .guide-clause-taxonomy-value').attr('data-tid',0).text('['+l[0]+'] 선택');
				self.documents.find('.guide-clause-taxonomy-item .guide-clause-taxonomy-list li').removeClass('selected');
				var deditor = self.documents.find('div#guide-clause-content').data('editor');
				deditor.setContent('');
				self.documents.find('div.guide-clause-field').each(function(i) {
					var $this = jQuery(this);
					$this.removeClass('article').addClass('parent');
					var editor = $this.find('.guide-clause-field-content').data('editor');
					editor.setContent('');
				});
				jQuery(this).addClass('extended');
			});
		},

		bindnewArticle: function(element) {
			var self = this;
			element.bind('click',function(e) {
				jQuery(this).parents('article').removeClass('current');
				self.documents.addClass('new').removeClass('delete');
				self.documents.attr('data-id',0);
				self.documents.attr('data-parent',jQuery(this).parents('article').attr('data-parent'));
				var idx = parseInt(jQuery(this).parents('article').attr('data-index')) + 1;
				self.documents.attr('data-index',idx);
				self.documents.find('h3#guide-clause-subject').text('').focus();
				var _l = self.documents.find('.guide-clause-taxonomy-item>label').text();
				var l = _l.split(/ /g);
				self.documents.find('.guide-clause-taxonomy-item .guide-clause-taxonomy-value').attr('data-tid',0).text('['+l[0]+'] 선택');
				self.documents.find('.guide-clause-taxonomy-item .guide-clause-taxonomy-list li').removeClass('selected');
				var deditor = self.documents.find('div#guide-clause-content').data('editor');
				deditor.setContent('');
				self.documents.find('div.guide-clause-field').each(function(i) {
					var $this = jQuery(this);
					$this.addClass('article').removeClass('parent');
					var editor = $this.find('.guide-clause-field-content').data('editor');
					editor.setContent('');
				});
				jQuery(this).addClass('extended');
			});
		},

		initContentEditor: function() {
			var self = this;

			this.documents.find('.guide-clause-taxonomy-item').each(function() {
				$this = jQuery(this);
				var v = $this.find('.guide-clause-taxonomy-value');
				var ul = $this.find('.guide-clause-taxonomy-list');
				var s = ul.find('li.selected');
				if(s.length > 0) {
					v.attr('data-tid',s.attr('data-tid'));
					v.text(s.text());
				} else {
					v.attr('data-tid',0);
					v.text('미지정');
				}
				self.bindSelectTaxonomy(v);
			});

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

			this.documents.find('div.guide-clause-field').each(function(i) {
				var $this = jQuery(this);
				var id = $this.attr('id');
				var editor = $this.find('.guide-clause-field-content').data('editor');
				if(typeof editor == 'undefined') {
					var editor = new MediumEditor('#'+id+'-content', {
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
					$this.find('#'+id+'-content').data('editor',editor);
				}
			});

			var mbutton = this.documents.find('button.modify');
			if(mbutton.data('bind-event') != true) {
				mbutton.bind('click',function(e) {
					e.preventDefault();
					self.saveClause();
				});
				mbutton.data('bind-event',true);
			}
			var dbutton = this.documents.find('button.delete');
			if(dbutton.data('bind-event') != true) {
				dbutton.bind('click',function(e) {
					e.preventDefault();
					self.deleteClause();
				});
				dbutton.data('bind-event',true);
			}
		},

		bindSelectTaxonomy: function(element) {
			self = this;
			var w = element.outerWidth();
			if(element.data('bind-event') != true) {
				element.bind('click',function(e) {
					this.select_taxonomy_mode = true;

					var ul = jQuery(this).parent().find('ul.guide-clause-taxonomy-list');
					ul.css({'width': w+'px'}).addClass('show');
					var l = ul.find('li.selected');
					if(l.length > 0) {
						var t = ul.find('li').index(l) * l.outerHeight();
						ul.scrollTop(t);
					}
					self.activateSelectTaxonomy(ul);

					var taxo_back = jQuery('<div class="taxonomy-background"></div>');
					taxo_back.appendTo('body');
					taxo_back.bind('click',function(e) {
						if(self.editTaxonomyMode == true)
							self.closeTaxonomyControl();
						else
							self.unbindSelectTaxonomy();
					});
				});
				element.data('bind-event', true);
			}
		},

		activateSelectTaxonomy: function(element) {
			var self = this;
			element.find('li').each(function() {
				var $this = jQuery(this);
				var $tn = $this.find('div.taxonomy-name');
				self.selectTaxonomy($tn);

				var $cn = $this.find('i.fa-gear');
				self.bind_showTaxonomyTermControl($cn);
			});
		},

		selectTaxonomy: function($tn) {
			var self = this;
			if( $tn.data('bind-event') != true ) {
				$tn.bind('click',function(e) {
					if(!jQuery(this).parents('ul.guide-clause-taxonomy-list').hasClass('editing')) {
						var selected = self.documents.find('.guide-clause-taxonomy-value');
						selected.text(jQuery(this).text());
						selected.attr('data-tid', jQuery(this).attr('data-tid'));
						jQuery(this).parent().addClass('selected').siblings().removeClass('selected');
						self.unbindSelectTaxonomy();
					}
				});
				$tn.data('bind-event', true);
			}
		},

		unbindSelectTaxonomy: function() {
			this.select_taxonomy_mode = false;
			this.documents.find('ul.guide-clause-taxonomy-list').removeClass('show');
			jQuery('.taxonomy-background').remove();
		},

		bind_showTaxonomyTermControl: function(element) {
			var self = this;
			if(element.data('bind-event') != true ) {
				element.bind('click', function(e) {
					self.showTaxonomyTermControl(element);
				});
				element.data('bind-event', true);
			}
		},

		showTaxonomyTermControl: function(element) {
			var self = this;
			this.editTaxonomyMode = true;

			var container = element.parents('li');
			container.addClass('editing');
			container.parents('ul.guide-clause-taxonomy-list').addClass('editing');
			var tid = parseInt(container.attr('data-tid'));
			var cid = parseInt(container.parents('.guide-clause-taxonomy-item').attr('data-cid'));
			var parents = parseInt(container.attr('data-parent'));
			var controll = jQuery('<div class="taxonomy-control-panel" data-cid="' + cid + '" data-tid="' + tid + '"><i class="fa fa-close"></i><div class="inner"></div></div>');

			var modify = jQuery('<button class="modify"><span>수정하기</span></button>');
			modify.bind('click', function(e) {
				self.modifyTaxonomyTerm(tid);
			});
			modify.appendTo(controll.find('.inner'));

			var insert = jQuery('<button class="add"><span>아래에 새분류삽입</span></button>');
			insert.bind('click', function(e) {
				var li = jQuery(this).parents('li');
				if(li.hasClass('sub')) {
					self.addTaxonomyTerm(cid, tid,'after');
				} else {
					var tid2 = 0;
					while( ( li = li.next() ) ) {
						if(!li.hasClass('sub')) {
							tid2 = li.attr('data-tid');
							self.addTaxonomyTerm(cid, tid2,'before');
							break;
						}
					}
					if(!tid2) {
						self.addTaxonomyTerm(cid, 0, 'append');
					}
				}
			});
			insert.appendTo(controll.find('.inner'));

			if(!container.hasClass('sub')) {
				var insert2 = jQuery('<button class="reply"><span>아래에 서브 분류삽입</span></button>');
				insert2.bind('click', function(e) {
					var li = jQuery(this).parents('li');
					var tid2 = 0;
					li = li.next();
					if(!li || !li.hasClass('sub')) {
						self.addTaxonomyTerm(cid, tid, 'prepend');
					} else {
						var tid2 = li.attr('data-tid');
						container.removeClass('editing');
						container.find('.taxonomy-name').removeClass('editing');
						self.addTaxonomyTerm(cid, tid2, 'before');
					}
				});
				insert2.appendTo(controll.find('.inner'));
			}

			var deletes = jQuery('<button class="delete"><span>삭제하기</span></button>');
			deletes.bind('click', function(e) {
				self.deleteTaxonomyTerm(cid,tid);
			});
			deletes.appendTo(controll.find('div.inner'));

			controll.appendTo(container);
			this.taxonomy_controll = controll;

			this.taxonomy_controll.find('i.fa-close').click(function(e) {
				self.closeTaxonomyControl();
				jQuery(this).parent().remove();
			});
		},

		removeTaxonomyControl: function() {
			this.taxonomy_controll.remove();
		},

		closeTaxonomyControl: function() {
			this.editTaxonomyMode = false;
			this.documents.find('ul.guide-clause-taxonomy-list.editing li.editing').removeClass('editing');
			this.documents.find('ul.guide-clause-taxonomy-list.editing li.add').remove();
			this.documents.find('ul.guide-clause-taxonomy-list.editing').removeClass('editing');
			this.removeTaxonomyControl();
			this.resetTaxonomyTerm();
		},

		modifyTaxonomyTerm: function(tid) {
			var self = this;

			this.removeTaxonomyControl();
			var tl = this.documents.find('ul.guide-clause-taxonomy-list li[data-tid="'+tid+'"]');
			var tn = tl.find('.taxonomy-name');
			tn.addClass('editing').attr('contenteditable',true).attr('data-orgin',tn.text()).focus();
			var b = jQuery('<button class="save">저장</button>');
			b.bind('click',function(e) {
				self.saveTaxnomyTerm(jQuery(this));
			});
			b.appendTo(tl);
		},

		addTaxonomyTerm: function(cid, tid,direction) {
			var self = this;

			this.removeTaxonomyControl();
			var li = this.documents.find('ul.guide-clause-taxonomy-list[data-cid="' + cid + '"] li[data-tid="'+tid+'"]');
			li.removeClass('editing');
			li.find('.taxonomy-name').removeClass('editing');

			var classes = 'current editing add '+direction;
			var issub = false;
			if(li.hasClass('sub') || direction == 'prepend') {
				classes += ' sub';
				issub = true;
			}

			var obj = jQuery('<li class="'+classes+'" data-parent="' + ( direction == 'prepend' ? li.attr('data-tid') : li.attr('data-parent') ) + '" data-tid="' + ( direction != 'prepend' ? tid : 0 ) + '"><div class="taxonomy-name editing"></div><button class="save">추가</button><div class="taxonomy-control"><i class="fa fa-close" title="취소"></i></div></li>');

			if(direction == 'after')
				obj.insertAfter(li);
			else if(direction == 'before')
				obj.insertBefore(li);
			else if(direction == 'prepend')
				obj.insertAfter(li);
			else if(direction == 'append') {
				var ul = li.parents('ul');
				obj.appendTo(ul);
			}

			obj.find('button.save').bind('click',function(e) {
				self.saveTaxnomyTerm(jQuery(this));
			});
			var c = obj.find('i.fa-close');
			c.bind('click', function(e) {
				jQuery(this).parents('li').slideUp('normal',function() {
					self.closeTaxonomyControl();
					jQuery(this).remove();
				});
			});
			obj.slideDown('normal',function() {
				jQuery(this).find('.taxonomy-name').attr('contenteditable',true).focus();
			});
		},

		saveTaxnomyTerm: function(element) {
			var self = this;
			var ul = element.parents('ul.guide-clause-taxonomy-list');
			var cid = ul.attr('data-cid');

			var li = element.parents('li');
			if(li.hasClass('add')) {
				var mode = 'add';
			} else {
				var mode = 'modify';
			}
			if(mode == 'add' && ( li.hasClass('before') || li.hasClass('after') ) ) {
				var after_tid = li.attr('data-tid');
				var tid = 0;
				var vid = 0;
			} else if(mode == 'modify') {
				var after_tid = 0;
				var tid = li.attr('data-tid');
				var vid = li.attr('data-vid');
			}
			var parents = li.attr('data-parent');
			var tn = li.find('.taxonomy-name');
			if(mode == 'modify') {
				if(tn.attr('data-orgin') == tn.text()) {
					this.closeTaxonomyControl();
					return;
				}
			}
			if(!tn.text()) {
				this.alertMessage(tn, '조항분류 이름을 입력하세요.');
				return;
			} else {
				this.removeAlert(tn);
			}

			var url = site_base_uri+'/admin/fields/terms';
			var params = "mode=" + mode + "&cid=" + cid + "&parent= " + parents;
			if(mode == 'add' && ( li.hasClass('prepend') || li.hasClass('append') ) ) {
			} else {
				params += "&tid=" + tid;
				if(mode == 'modify') params += "&vid=" + vid;
			}
			if(after_tid) {
				if( li.hasClass('after') )
					params += "&after_tid="+after_tid;
				else if( li.hasClass('before') )
					params += "&before_tid="+after_tid;
			}
			params += "&name=" + tn.text();

			jQuery.ajax({
				url: url,
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
					if(error <= 0) {
						if(error == -3) {
							self.alertMessage(tn, message);
						} else {
							self.abortDialog(message,0);
							self.removeAlert(tn);
						}
					} else {
						if(mode == 'add') {
							li.attr('data-tid',message.tid);
							li.attr('data-vid',message.vid);
							li.removeClass('add');

							self.selectTaxonomy(li.find('div.taxonomy-name'));

							var c = li.find('div.taxonomy-control');
							c.find('i.fa-close').remove();
							var cl = jQuery('<i class="fa fa-gear" title="관리패널열기"></i>');
							self.bind_showTaxonomyTermControl(cl);
							cl.appendTo(c);
						}
						self.closeTaxonomyControl();
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		deleteTaxonomyTerm: function(cid,tid) {
			var self = this;

			this.removeTaxonomyControl();
			var li = this.documents.find('ul.guide-clause-taxonomy-list[data-cid="' + cid + '"] li[data-tid="'+tid+'"]');

			var url = site_base_uri+'/admin/fields/terms';
			var params = "mode=delete&cid=" + cid + "&parent= " + li.attr('data-parent') + "&tid=" + tid;

			jQuery.ajax({
				url: url,
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
					if(error <= 0) {
						self.abortDialog(message,0);
						self.removeAlert(tn);
					} else {
						li.remove();
						self.closeTaxonomyControl();
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		resetTaxonomyTerm: function() {
			this.documents.find('ul.guide-clause-taxonomy-list li').removeClass('editing');
			this.documents.find('ul.guide-clause-taxonomy-list li .taxonomy-name').removeClass('editing').attr('contenteditable',false);;
			this.documents.find('ul.guide-clause-taxonomy-list li button.save').remove();
		},

		getClause: function(id) {
			var self = this;

			var url = site_base_uri+'/admin/standards/clause';
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
			this.documents.removeClass('new').removeClass('delete');
			this.documents.attr('data-id',json.id);
			this.documents.attr('data-index',json.idx);
			this.documents.attr('data-parent',json.parent);
			this.documents.find('h3#guide-clause-subject').text(json.subject);
			if(json.taxonomy.length > 0) {
				for(var i=0; i<json.taxonomy.length; i++) {
					var c = this.documents.find('#guide-clause-taxonomy-'+json.taxonomy[i].cid);
					var u = c.find('ul.guide-clause-taxonomy-list');
					u.find('li[data-tid="'+json.taxonomy[i].tid+'"]').addClass('selected').siblings().removeClass('selected');
					var v = c.find('div.guide-clause-taxonomy-value');
					v.attr('data-tid',json.taxonomy[i].tid);
					v.text(json.taxonomy[i].name);
				}
			}
			var deditor = this.documents.find('div#guide-clause-content').data('editor');
			deditor.setContent(json.content);

			if(parseInt(json.parent) > 0) {
				this.documents.find('div.guide-clause-field').removeClass('parent').addClass('article');
				jQuery.each(json, function(k,data) {
					if(k.substr(0,1) == 'f') {
						var container = self.documents.find('div#guide-clause-field-'+k);
						var editor = container.find('.guide-clause-field-content').data('editor');
						editor.setContent(data);
					}
				});
			} else {
				this.documents.find('div.guide-clause-field').removeClass('article').addClass('parent');
				this.documents.find('div.guide-clause-field').each(function(i) {
					var $this = jQuery(this);
					var editor = $this.find('.guide-clause-field-content').data('editor');
					editor.setContent('');
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
			params += "&nid="+this.Root.attr('data-nid');

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

			var tid = [];
			this.documents.find('.guide-clause-taxonomy-value').each(function() {
				 tid.push(jQuery(this).attr('data-tid'));
			});
			if(tid.length > 0) {
				params += '&tid='+tid;
			}

			params += "&content="+encodeURIComponent(this.documents.find('#guide-clause-content').html());
			this.documents.find('div.guide-clause-field').each(function(i) {
				$this = jQuery(this);
				if($this.hasClass('article')) {
					if($this.find('.guide-clause-field-content').html()) {
						params += "&f"+$this.attr('data-fid')+"="+encodeURIComponent($this.find('.guide-clause-field-content').html());
					} else {
						params += "&f"+$this.attr('data-fid')+"=";
					}
				}
			});

			var url = site_base_uri+"/admin/standards/save";

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
							self.documents.removeClass('new').removeClass('delete');
						}
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		deleteClause: function() {
			var self = this;
			var id = parseInt(this.documents.attr('data-id'));
			var idx = parseInt(this.documents.attr('data-index'));

			var params = "table=clause";
			if(!id) {
				return;
			}

			var parents = parseInt(this.documents.attr('data-parent'));
			if(!parents) {
				var articles = this.indexes.find('dl#guide-chapter-' + id + ' dd.chapter-articles article.article');
				if(articles.length > 0) {
					this.abortDialog('서브조항이 있는 조항은 삭제할 수 없습니다.<br>하위 조항을 다 지우고 지우세요',0);
					return;
				}
			}

			var mode = 'delete';
			params += "&mode=delete";
			params += "&id="+id;
			params += "&parent="+parents;
			params += "&nid="+this.Root.attr('data-nid');

			var url = site_base_uri+"/admin/standards/save";

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
						var id = parseInt(self.documents.attr('data-id'));
						if(!p) {
							self.removeChapter(id);
						} else {
							self.removeArticles(p,id);
						}
						self.documents.addClass('delete');
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
			chapter.find('dd.chapter-articles article').attr('data-parent',this.documents.attr('data-id'));
			chapter.removeClass('collapsed').addClass('current');

			this.bindDl(chapter);
			if(parseInt(this.documents.attr('data-index')) > 1) {
				chapter.insertBefore(this.indexes.find('dl[data-index="'+this.documents.attr('data-index')+'"]'));
			} else {
				chapter.appendTo(this.indexes.find('.guide-clause-indexes-box'));
			}
			chapter.siblings().removeClass('current').addClass('collapsed');
			this.Root.find('.console').removeClass('extended');

			this.resortChapter();
		},

		removeChapter: function(id) {
			var obj = this.indexes.find('dl#guide-chapter-'+id);
			if(obj.length > 0) {
				var art = obj.find('dd.chapter-articles article.article');
				if(art.length > 0) {
					return;
				}
				obj.remove();
				this.resortChapter();
			}
		},

		appendArticle: function() {
			var article = this.article.clone();
			article.attr('id','guide-article-'+this.documents.attr('data-id'));
			article.attr('data-id',this.documents.attr('data-id'));
			article.attr('data-parent',this.documents.attr('data-parent'));
			article.attr('data-index',this.documents.attr('data-index'));
			article.find('span').text(this.documents.find('#guide-clause-subject').text());

			this.bindDd(article);
			var dd = this.indexes.find('dl#guide-chapter-'+this.documents.attr('data-parent')+' dd.chapter-articles');
			if(parseInt(this.documents.attr('data-index')) > 1) {
				article.insertBefore(dd.find('article.article[data-index="'+this.documents.attr('data-index')+'"]'));
			} else {
				article.appendTo(dd);
			}
			article.addClass('current').siblings().removeClass('current');

			this.Root.find('.console').removeClass('extended');
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
