var maxGuideIndex = 0;

(function ($) {
	Array.prototype.insert = function(index) {
		this.splice.apply(this, [index, 0].concat(
		Array.prototype.slice.call(arguments, 1)));
		return this;
	};

	Array.prototype.remove = function(from, to) {
		var rest = this.slice((to || from) + 1 || this.length);
		this.length = from < 0 ? this.length + from : from;
		return this.push.apply(this, rest);
	}

	Date.prototype.isValid = function () {
		// An invalid date object returns NaN for getTime() and NaN is the only
		// object not strictly equal to itself.
		return this.getTime() === this.getTime();
	};

	function isInt(value) {
		return !isNaN(value) && (function(x) { return (x | 0) === x; })(parseFloat(value))
	}

	function CADBArticleEditor(element,options) {
		var self = this;
		this.settings = $.extend({}, $.fn.cadbArticleEditor.defaults, options);

		this.Root = jQuery(element);

		var transEndEventNames = {
			'WebkitTransition' : 'webkitTransitionEnd',
			'MozTransition'    : 'transitionend',
			'transition'       : 'transitionend'
		}

		this.transitionEnd = transEndEventNames[ Modernizr.prefixed('transition') ];
		this.mode = '';
		this.maxPageIndex = 0;

		self.loading();

		self.initTaxonomySingle();
		self.initOrganize();
		self.initContent();
		self.initGuideTaxonomy();
		self.initForm();
		self.keyhandle();
		self.addGuideBackground();

		self.removeLoading();
	}

	CADBArticleEditor.prototype = {
		initTaxonomySingle: function() {
			var self = this;
			this.Root.find('.taxonomy-field.single').each(function() {
				self.handleTaxonomySingle(jQuery(this));	
			});
		},

		handleTaxonomySingle: function(element) {
			var self = this;
			element.addClass('collapsed');

			var label = element.find('label.select-box');
			label.bind('click.cadb',function(e) {
				element.toggleClass('collapsed');
			});

			var select = element.find('ul.taxonomy-items');
			select.find('li.taxonomy-item').each(function(idx) {
				var obj = jQuery(this);
				obj.bind('click.cadb', function(e) {
					var item = jQuery(this);
					item.addClass('selected');
					item.siblings().removeClass('selected');
					var pid = parseInt( item.attr('data-parent') );
					if( pid ) {
						self.selectParentTaxonomySingle(element,pid);
					}
					self.rebuildTaxonomyValue(element);
				});
			});
		},

		selectParentTaxonomySingle: function(element,pid) {
			var self = this;
			element.find('[data-tid="'+pid+'"]').each(function() {
				var item = jQuery(this);
				item.addClass('selected');
				var pid = parseInt( item.attr('data-parent') );
				if( pid ) {
					self.selectParentTaxonomySingle(elementtem,pid);
				}
			});
		},

		rebuildTaxonomyValue: function(element) {
			var label = element.find('label.select-box');

			var $tl = element.find('.cadb-field.taxonomy-list');
			if(!$tl.length) {
				var $tl = jQuery('<ul class="cadb-field taxonomy-list" data-fid="'+element.attr('data-fid')+'"></ul>');
			} else {
				$tl.empty();
			}

			var $select = element.find('ul.taxonomy-items');
			var $select_items = 0;
			$select.find('li.taxonomy-item.selected').each(function(idx) {
				var $this = jQuery(this);
				var markup = '<li class="taxonomy" data-cid="'+$this.attr('data-cid')+'" data-tid="'+$this.attr('data-tid')+'" data-vid="'+$this.attr('data-vid')+'" data-name="'+$this.attr('data-name')+'">'+$this.attr('data-name')+'</li>';
				$tl.append(jQuery(markup));
				$select_items++;
			});
			if(!$select_items) {
				label.html('지정안됨');
			} else {
				label.empty().append($tl);
			}
		},

		initOrganize: function() {
			var self = this;
			this.Root.find('.organize-list').each(function() {
				self.handleOrganize(jQuery(this));	
			});
		},

		handleOrganize: function(element) {
			var self = this;
			element.find('li.organize').each(function() {
				var $this = jQuery(this);
				self.bindDeleteEventOrganize($this);
				self.bindChangeOwner($this);
			});
			element.find('li.add i.add').bind('click.cadb',function(e) {
				self.addOrganizeByString(element);
			});
			element.find('li.add i.search').bind('click.cadb',function(e) {
				self.openOrgaizeSearchBox(element);
			});
		},

		bindChangeOwner: function(element) {
			var self = this;
			element.find('input[type="checkbox"]').change(function(e) {
				if(jQuery(this).is(":checked")) {
					element.attr('data-owner','1').addClass('is-owner');
				} else {
					element.attr('data-owner','0').removeClass('is-owner');
				}
			});
		},

		bindDeleteEventOrganize: function(element) {
			var self = this;
			element.find('i.delete').bind('click.cadb',function(e) {
				element.remove();
			});
		},

		openOrgaizeSearchBox: function(element) {
			var self = this;
			if(!this.searchBox) {
				this.searchBoxBackground = jQuery(this.settings.searchBoxBackground);
				this.searchBoxBackground.bind('click',function(e) {
					self.closeSearchOrganizeBox();
				});
				this.searchBox = jQuery(this.settings.searchBox);
				this.searchBoxBackground.appendTo('body');
				this.searchBox.appendTo('body');
			} else {
				this.searchBoxBackground.show();
				this.searchBox.show();
			}
			this.mode = 'search-org';
			this.searchBox.attr('data-fid',element.attr('data-fid'));
			this.searchBox.find('input[name="q"]').focus();
			var $form = this.searchBox.find('form');
			if($form.attr('init-bind') !== true) {
				$form.bind('submit',function(e) {
					e.preventDefault();
					self.OrganizeSearch(jQuery(this));
					var url = jQuery(this).attr('action');

				});
				$form.attr('init-bind',true);
			}
			var d = this.searchBox.find('.close');
			if(d.attr('init-bind') != true) {
				d.bind('click',function(e) {
					self.closeSearchOrganizeBox();
				});
				d.attr('init-bind',true);
			}
		},

		OrganizeSearch: function($form) {
			var q = $form.find('input[name="q"]').val();
			if(!q) {
				$form.find('input[name="q"]').attr('placeholder','검색어를 입력하세요').focus();
				return;
			}
			this.getOrganizeList(q,1);
		},

		getOrganizeList: function(q,page) {
			var self = this;
			var url = this.searchBox.find('form').attr('action');
			var params = 'q='+q+'&page='+page;
			var wrapper = this.searchBox.find('section.orgs');

			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'GET',
				success: function(json) {
					wrapper.empty();
					var result = jQuery('<div class="search-result">총 '+json.result.total_cnt+' 건을 검색했습니다.</div>');
					wrapper.append(result);
					if(json.orgs.length > 0) {
						for(var i=0; i<json.orgs.length; i++) {
							var item = jQuery('<article data-oid="' + json.orgs[i].oid + '" data-vid="' + json.orgs[i].vid + '" data-name="' + json.orgs[i].fullname + '">' + json.orgs[i].fullname + '</article>');
							wrapper.append(item);
						}
					}
					if(json.result.total_page > 0) {
						var paging = self.makeOrganizeListNav(q,json.result.total_page,page);
						wrapper.append(paging);
					}
				},
				complete: function() {
					wrapper.find('article').bind('click.cadb', function(e) {
						self.addOrganize(jQuery(this));
					});
					wrapper.find('.search-nav li.page').bind('click.cadb',function(e) {
						self.getOrganizeList( q, jQuery(this).attr('data-page') );
					});
				},
				error( jqXHR, textStatus, errorThrown ) {
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		makeOrganizeListNav(q,total_page,page) {
			var page_num = 10;
			var s_page = ( parseInt( ( page - 1 ) / page_num ) * page_num ) + 1;
			var e_page = Math.min( total_page, ( s_page + page_num - 1 ) );
			if(s_page > 1) {
				var p_page = s_page - 1;
			} else {
				var p_page = 0;
			}
			if(e_page < total_page) {
				var n_page = e_page + 1;
			} else {
				var n_page = 0;
			}
			var paging = jQuery('<ul class="search-nav"></ul>');
			if(p_page) {
				var pg = jQuery('<li class="page prev" data-page="'+p_page+'"><span><i class="fa fa-angle-double-left"></i></span></li>');
				paging.append(pg);
			}
			for(var p=s_page; p<=e_page; p++) {
				if(p == page) {
					var pg = jQuery('<li class="current"><span>'+p+'</span></li>');
				} else {
					var pg = jQuery('<li class="page" data-page="'+p+'"><span>'+p+'</span></li>');
				}
				paging.append(pg);
			}
			if(n_page) {
				var pg = jQuery('<li class="page next" data-page="'+n_page+'"><span><i class="fa fa-angle-double-right"></i></span></li>');
				paging.append(pg);
			}
			return paging;
		},

		addOrganize: function(element) {
			var self = this;
			var fid = this.searchBox.attr('data-fid');
			var oid = element.attr('data-oid');
			var vid = element.attr('data-vid');
			var name = element.attr('data-name');
			var org = this.Root.find('.organize-field[data-fid="'+fid+'"] ul.cadb-field.organize-list');
			var t = org.find('.organize[data-oid="'+oid+'"]');
			if(t.length < 1) {
				var item = jQuery('<li class="organize is-owner" data-oid="'+oid+'" data-vid="'+vid+'" data-owner="1" data-name="'+name+'"><input type="checkbox" data-oid="'+oid+'" value="1" checked />'+name+'<i class="delete fa fa-close"></i></li>');
				self.bindDeleteEventOrganize(item);
				self.bindChangeOwner(item);
				var a = org.find('li.add');
				item.insertBefore(a);
			}
			this.closeSearchOrganizeBox();
		},

		addOrganizeByString: function(element) {
			var fid = element.attr('data-fid');
			var input = element.find('input#new-f'+fid);
			if(!input.val()) {
				input.attr('placeholder','추가할 단체를 입력하세요').focus();
				return;
			}
			var org = this.Root.find('.organize-field[data-fid="'+fid+'"] ul.cadb-field.organize-list');
			var item = jQuery('<li class="organize" data-oid="0" data-vid="0" data-name="'+input.val()+'">'+input.val()+'<i class="delete fa fa-close"></i></li>');
			this.bindDeleteEventOrganize(item);
			var a = org.find('li.add');
			item.insertBefore(a);
			input.val('');
		},

		closeSearchOrganizeBox: function() {
			this.searchBoxBackground.hide();
			this.searchBox.hide();
			this.mode = '';
		},

		initContent: function() {
			this.content = jQuery('.editor.content');

			this.guideIndex = {};
			maxGuideIndex = 0;

			var self = this;

			var initPTagButton = jQuery('<button type="button" class="init-sup-button">책갈피 재설정</button>');
			initPTagButton.click(function(e) {
				self.initPTag();
			});
			this.Root.find('fieldset.fields-title legend').append(initPTagButton);
			this.content.find('sup').each(function() {
				var obj = jQuery(this);
				if(obj.find('span').length < 1) {
					obj.remove();
				} else {
					var fid = parseInt( obj.attr('id').replace(/cadb\-taxo\-/,'') );
					if(fid > maxGuideIndex)
						maxGuideIndex = fid;
					self.bindGuideEvent(obj,fid);
					self.guideIndex[fid] = {};
					self.guideIndex[fid].fid = fid;
					self.guideIndex[fid].obj = obj;
					self.guideIndex[fid].items = [];
					obj.find('span').each(function() {
						var $this = jQuery(this);
						var tid = parseInt( $this.attr('id').replace(/cadb\-taxo\-term\-/,'') );
						var item = {};
						item.tid = tid;
						item.name = $this.text();
						item.fid = fid;
						item.obj = $this;
						self.bindGuideItemEvent(item.obj,fid,tid);
						self.guideIndex[fid].items.push(item);
					});
				}
			});

			self.initPTag();

			var editor = new MediumEditor('.editor',{
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
		},

		initPTag: function() {
			var self = this;
			jQuery('.editor.content>p').each(function() {
			    if( !jQuery(this).hasClass('insertGuidePos') ) {
					self.maxPageIndex++;
					jQuery(this).addClass('insertGuidePos').attr('data-index',self.maxPageIndex);
					var pos = jQuery(this).offset();
					var addItem = jQuery('<button type="button" class="addGuideItem" data-index="'+self.maxPageIndex+'"><i class="fa fa-plus-circle"></i></button>');
					jQuery(this).prepend(addItem);
					addItem.css({
						'left' : '-35px',
						'top' : 0
					});
					addItem.click(function(e) {
						e.preventDefault();
						self.addGuide( jQuery(this).attr('data-index') );
					});
				}
			});
		},

		bindGuideEvent: function(element,fid) {
			var self = this;
			element.hover(
				function() {
					var b1 = jQuery('<button type="button" class="t-add" data-fid="'+fid+'" title="새 모범단협을 추가합니다."><i class="fa fa-plus"></i></button>');
					b1.bind('click', function(e) {
						e.preventDefault();
						var $this = jQuery(this);
						self.addGuideItem( $this.attr('data-fid') );
					});
					var b2 = jQuery('<button type="button" class="f-delete" data-fid="'+fid+'" title="단협조합 그룹을 삭제합니다."><i class="fa fa-close"></i></button>');
					b2.bind('click', function(e) {
						e.preventDefault();
						var $this = jQuery(this);
						self.removeGuide( $this.attr('data-fid') );
					});
					jQuery(this).append(b1);
					jQuery(this).append(b2);
				},
				function() {
					jQuery(this).find('button.t-add').remove();
					jQuery(this).find('button.f-delete').remove();
				}
			);
		},

		bindGuideItemEvent: function(element,fid,tid) {
			var self = this;
			element.hover(
				function() {
					var bt = jQuery('<button type="button" class="t-delete" data-fid="'+fid+'" data-tid="'+tid+'" title="이 모범단협 조항을 삭제합니다."><i class="fa fa-close"></i></button>');
					bt.bind('click',function(e) {
						e.preventDefault();
						var $this = jQuery(this);
						self.removeGuideItem( $this.attr('data-fid'), $this.attr('data-tid') );
					});
					jQuery(this).append(bt);
				},
				function() {
					jQuery(this).find('button.t-delete').remove();
				}
			);
		},

		removeGuide: function( fid ) {
			for( var $i=0; $i < this.guideIndex[fid].items.length; $i++ ) {
				this.removeGuideItem( fid, this.guideIndex[fid].items[$i].tid );
			}
			this.guideIndex[fid].obj.remove();
			this.guideIndex[fid] = null;
			delete this.guideIndex[fid];
		},

		addGuide: function(index) {
			this.guideTaxonomyTarget = 0;
			this.guidePageTarget = index;
			this.openGuide();
		},

		addGuideItem: function( fid ) {
			this.openGuide();
			this.guideTaxonomyTarget = fid;
			this.guidePageTarget = 0;
		},

		removeGuideItem: function( fid, tid ) {
			for( var $i=0; $i < this.guideIndex[fid].items.length; $i++ ) {
				var item = this.guideIndex[fid].items[$i];
				if(item.tid == tid) {
					item.obj.remove();
					this.guideIndex[fid].items.remove($i);
					break;
				}
			}
		},

		keyhandle: function() {
			var self = this;
			jQuery(document).keydown(function(event) {
				var code = event.charCode || event.keyCode;
				if(self.mode == 'search-org' && code == 27) {
					self.closeSearchOrganizeBox();
				} else if(self.mode == 'dialog' && code == 27) {
					self.closeDialog();
				} else if(self.mode == 'guide-taxonomy' && code == 27) {
					self.closeGuide();
				} else if(code == 83 && (event.ctrlKey || event.altKey)) {
					event.preventDefault();
					self.save();
				} else if(code == 13) {
					self.initPTag();
				}
			});
		},

		initGuideTaxonomy: function() {
			var self = this;
			this.guideTaxonomy = this.Root.find('.fields-guide-group');
			this.guideTaxonomy.find('.radio-button label').click(function(e) {
				if(self.guideTaxonomy.hasClass('collapsed')) {
					self.openGuide();
				} else {
					self.closeGuide();
				}
			});
			var gsc = this.guideTaxonomy.find('.guide-sub-category');
			this.guideTaxonomy.find('li.guide-chapter label.guide-chapter-label').each(function() {
				jQuery(this).click(function(e) {
					if(jQuery(this).hasClass('has-sub-category')) {
						self.toggleGuideSubCategory(jQuery(this));
					} else {
						self.selectGuideTaxonomy(jQuery(this));
					}
				});
			});
			this.guideTaxonomy.find('ul.guide-sub-items').each(function() {
				var clones = jQuery(this).clone();
				gsc.append(clones);
				jQuery(this).remove();
			});
			this.guideTaxonomy.find('ul.guide-sub-items label.guide-sub-item-label').each(function() {
				jQuery(this).click(function(e) {
					self.selectGuideTaxonomy(jQuery(this));
				});
			});
		},

		openGuide: function() {
			this.mode = 'guide-taxonomy'
			this.guideTaxonomy.removeClass('collapsed');
			this.guideBackground.show();
		},

		closeGuide: function() {
			this.mode = '';
			this.guideTaxonomy.addClass('collapsed');
			this.guideTaxonomyTarget = 0;
			this.guideBackground.hide();
		},

		toggleGuideSubCategory: function(element) {
			var self = this;
			var ptid = element.attr('data-tid');
			var subcategory = this.guideTaxonomy.find('#guide-sub-item-' + ptid);
			if(subcategory.hasClass('slideTo')) {
				element.parents('li.guide-chapter').removeClass('extended');
				subcategory.css( { 'z-index' : 99997 } ).removeClass('slideTo');
			} else {
				this.guideTaxonomy.find('.guide-sub-items').css({ 'z-index' : 99997 }).removeClass('slideTo');
				element.parents('li.guide-chapter').siblings().removeClass('extended');
				element.parents('li.guide-chapter').addClass('extended');
				subcategory.css( {'z-index': 99998} ).addClass('slideTo');
			}
		},

		selectGuideTaxonomy: function(element) {
			var cid = this.guideTaxonomy.find('ul.guide-items').attr('data-cid');
			var tid = element.attr('data-tid');
			var vid = element.attr('data-vid');
			var name = element.attr('data-name');

			var obj = jQuery('<span id="cadb-taxo-term-'+tid+'">'+name+'</span>');
			var item = {};
			item.tid = tid;
			item.name = name;
			item.obj = obj;

			if(this.guideTaxonomyTarget) {
				var fid = this.guideTaxonomyTarget;
				item.fid = fid;
				this.bindGuideItemEvent(item.obj,fid,tid);
				this.guideIndex[fid].items.push(item);
				this.content.find('sup#cadb-taxo-'+this.guideTaxonomyTarget).append(item.obj);
			} else if(this.guidePageTarget) {
				maxGuideIndex++;
				var fid = maxGuideIndex;
				item.fid = fid;
				this.bindGuideItemEvent(item.obj,fid,tid);

				var fobj = jQuery('<sup id="cadb-taxo-'+maxGuideIndex+'"></sup>');
				fobj.append(item.obj);

				jQuery('.insertGuidePos[data-index="'+this.guidePageTarget+'"]').before(fobj);

				this.bindGuideEvent(fobj,fid);

				this.guideIndex[fid] = {};
				this.guideIndex[fid].fid = fid;
				this.guideIndex[fid].obj = fobj;
				this.guideIndex[fid].items = [];
				this.guideIndex[fid].items.push(item);

				this.guidePageTarget = 0;
				this.guideTaxonomyTarget = fid;

			}
		},

		initForm: function() {
			var self = this;
			this.Root.bind('submit',function(e) {
				e.preventDefault();
				self.save();
			});
			this.Root.find('button.article-delete').bind('click',function(e) {
				self.deletes();
			});
		},

		save: function() {
			var self = this;
			var url = "/api/save/articles";

			var params = 'nid='+this.Root.find('input[name="nid"]').val();
			params += '&did='+this.Root.find('input[name="did"]').val();

			var sf = this.Root.find('input#subject');
			if(!sf.val()) {
				sf.attr('placeholder','단협 제목을 입력하세요').focus();
				return;
			}
			params += "&subject="+sf.val();

			var cont = this.Root.find('.editor.content').clone();
			cont.find('p.insertGuidePos').removeClass('insertGuidePos');
			cont.find('button.addGuideItem').remove();
			params += "&content="+encodeURIComponent(cont.html());

			this.save_abort = false;
			this.Root.find('.cadb-field').each(function() {
				var $this = jQuery(this);
				if( $this.hasClass('taxonomy-list') ) {
					params += '&'+self.makeTaxonomyParams($this);
				} else if( $this.hasClass('organize-list') ) {
					params += '&'+self.makeOrganizeParams($this);
				} else if( $this.hasClass('date') ) {
					params += '&'+self.makeParams($this, 'date');
				} else if( $this.hasClass('int') ) {
					params += '&'+self.makeParams($this, 'int');
				} else {
					params += '&'+self.makeParams($this, 'text');
				}
			});

			if(this.save_abort == true) {
				return;
			}

			params += "&guide="+JSON.stringify(this.guideIndex);

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
					switch(error) {
						case -1:
							sf.attr('placeholder',message).focus();
							break;
						case -2:
							self.abortDialog(message,0);
							break;
						case -3:
						case -4:
						case -5:
							self.abortDialog(message,0);
							break;
						case 0:
							break;
						default:
							self.abortDialog(message,error);
							break;
					}
				},
				error( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		makeTaxonomyParams: function(element) {
			var q = 'f'+element.attr('data-fid')+'=';
			var json = [];
			element.find('.taxonomy').each(function() {
				var $this = jQuery(this);
				var obj = {};
				obj.cid = $this.attr('data-cid');
				obj.tid = $this.attr('data-tid');
				obj.vid = $this.attr('data-vid');
				obj.name = $this.attr('data-name');
				json.push(obj);
			});
			if( parseInt( element.attr('data-required') ) == 1 ) {
				if(json.length < 1) {
					this.save_abort = true;
					var fd = element.parents('fieldset.fields');
					this.abortDialog( fd.find('label.field-label').text()+" 는 반드시 입력해야 합니다", 0 );
					return;
				}
			}
			q += JSON.stringify(json);

			return q;
		},

		makeOrganizeParams: function(element) {
			var q = 'f'+element.attr('data-fid')+'=';
			var json = [];
			element.find('.organize').each(function() {
				var $this = jQuery(this);
				var obj = {};
				obj.oid = $this.attr('data-oid');
				obj.vid = $this.attr('data-vid');
				obj.owner = $this.attr('data-owner');
				obj.name = $this.attr('data-name');
				json.push(obj);
			});
			if( parseInt( element.attr('data-required') ) == 1 ) {
				if(json.length < 1) {
					this.save_abort = true;
					var fd = element.parents('fieldset.fields');
					this.abortDialog( fd.find('label.field-label').text()+" 는 반드시 입력해야 합니다", 0 );
					return;
				}
			}
			q += JSON.stringify(json);

			return q;
		},

		makeParams: function(element,type) {
			var q = 'f'+element.attr('data-fid')+'=';
			var fd = element.parents('fieldset.fields');
			if( parseInt( element.attr('data-required') ) == 1 ) {
				if( !element.val() ) {
					this.save_abort = true;
					this.abortDialog( fd.find('label.field-label').text()+" 는 반드시 입력해야 합니다", element.attr('data-fid') );
					return;
				}
			}
			switch(type) {
				case "date":
					d = new Date( element.val() );
					if( d.isValid() == false ) {
						this.save_abort = true;
						this.abortDialog( fd.find('label.field-label').text()+" 는 날짜 형식이 아닙니다.", element.attr('data-fid') );
						return;
					}
					break;
				case "int":
					if( isInt(element.val()) == false) {
						this.save_abort = true;
						this.abortDialog( fd.find('label.field-label').text()+" 는 숫자 형식이 아닙니다.", element.attr('data-fid') );
						return;
					}
					break;
				default:
					break;
			}
			q += element.val();

			return q;
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

		closeDialog() {
			this.dialog.hide();
			if(this.focus_id) {
				jQuery('.cadb-field[data-fid="'+this.focus_id+'"]').focus();
				this.focus_id = 0;
			}
			this.mode = 'dialog';
		},

		loading: function() {
			jQuery('body').append(jQuery('<div class="saving"><div class="saving-background"></div><div class="is-loading"><i class="fa fa-spinner fa-pulse"></i></div></div>'));
			jQuery('.saving .is-loading').css({
				'left' : parseInt( ( jQuery(window).width() - 100 ) / 2 ),
				'top' : parseInt( ( jQuery(window).height() - 100 ) / 2 )
			});
		},

		addGuideBackground: function() {
			var self = this;
			this.guideBackground = jQuery('<div class="guide-select-background"></div>');
			jQuery('.inner-container').append(this.guideBackground);
			this.guideBackground.bind('click',function(e) {
				self.closeGuide();
			});
		},

		removeLoading: function() {
			jQuery('body .saving').remove();
		}
	}

	jQuery.fn.cadbArticleEditor = function(options) {
		return this.each(function() {
			var cadbArticleEditor = new CADBArticleEditor(jQuery(this),options);
		});
	};

	jQuery.fn.cadbArticleEditor.defaults = {
		searchBoxBackground : '<div class="searching-org-list"></div>',
		searchBox : '<div class="search-list-box"><div class="search-inner-box"><h3>조직 검색</h3><form action="/api/orgs" method="POST"><input type="text" name="q" value=""><button type="submit">검색</button></form><section class="orgs"></section><div class="progress-spinner><i class="fa fa-spinner"></i></div></div><div class="close"><i class="fa fa-close"></i></div></div>',
		dialog : '<div class="alert-dialog"><div class="alert-dialog-inner"></div><div class="close"><i class="fa fa-close"></i></div></div>'
	};

	jQuery.fn.cadbArticleEditor.settings = {
	};
})(jQuery);

jQuery(document).ready(function(e) {
	jQuery('#article-edit-form').cadbArticleEditor({
	});
});
