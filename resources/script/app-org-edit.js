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

	function CADBOrgEditor(element,options) {
		var self = this;
		this.settings = $.extend({}, $.fn.cadbOrgEditor.defaults, options);

		this.Root = jQuery(element);

		var transEndEventNames = {
			'WebkitTransition' : 'webkitTransitionEnd',
			'MozTransition'    : 'transitionend',
			'transition'       : 'transitionend'
		}

		this.transitionEnd = transEndEventNames[ Modernizr.prefixed('transition') ];

		self.initOrg();
		self.initTaxonomySingle();
		self.initTaxonomyMultiple();
		self.initForm();
		self.keyhandle();
	}

	CADBOrgEditor.prototype = {
		initOrg: function() {
			var self = this;
			self.orgs = [];
			this.Root.find('fieldset.org-name').each(function(i) {
				var $this = jQuery(this);
				var obj = {};

				obj.obj = $this;
				obj.f = $this.find('.org-name-form');
				obj.p = $this.find('input.org-parent');
				obj.name = $this.find('input.org-name');
				
				var bt = $this.find('button.search-orgs');
				obj.button = bt;
				obj.depth = bt.attr('data-depth');

				self.handleSearchOrg(bt);
				self.checkOrgNameValue(obj.name);

				self.orgs.push(obj);
			});
		},

		handleSearchOrg: function(element) {
			var self = this;
			element.click(function(e) {
				var depth = parseInt( jQuery(this).attr('data-depth') );
				var orgs = self.orgs[(depth - 1)];
				if(!orgs.f.hasClass('collapsed')) {
					self.hideOrgBox( (depth-1) );
				} else {
					if(depth > 1) {
						if(!self.orgs[0].p.val()) {
							self.alertMessagee(0,'노조를 선택하세요');
							return;
						}
					}
					if(depth > 3) {
						if(!self.orgs[1].p.val() && !self.orgs[2].p.val()) {
							self.alertMessagee(0,'본부 또는 지부를 선택하세요');
							return;
						}
					}
					var q = '';
					for(var p=0; p<(depth-1); p++) {
						if(self.orgs[p].p.val()) {
							q += (q ? "&" : '')+'p'+ (p+1) +'='+self.orgs[p].p.val();
						}
					}
					q += (q ? "&": '')+"pdepth="+depth;

					var oq = orgs.f.attr('data-q');
					if(oq != q) {
						var url = "/api/orgs";

						jQuery.ajax({
							url: url,
							data: q,
							dataType: 'json',
							method: 'GET',
							beforeSend: function() {
								orgs.f.find('button').addClass('loading');
							},
							success: function(data) {
								orgs.f.find('button').removeClass('loading');
								orgs.f.attr('data-q',q);
								if(data.result.orgs.count) {
									self.showOrgBox( data, (depth-1) );
								}
							},
							error: function( jqXHR, textStatus, errorThrown ) {
								orgs.f.find('button').removeClass('loading');
								self.abortDialog(jqXHR.responseText,0);
							}
						});
					} else {
						self.decollapseOrgBox( (depth-1) );
					}
				}
			});
		},

		showOrgBox: function(data, idx) {
			var self = this;
			var ul = this.orgs[idx].f.find('ul');
			ul.empty();
			this.orgs[idx].f.removeClass('collapsed');
			jQuery(data.orgs).each(function(i,value) {
				switch(idx) {
					case 0:
						var name = value.nojo;
						var target = 'nojo';
						break;
					case 1:
						var name = value.sub1;
						var target = 'sub1';
						break;
					case 2:
						var name = value.sub2;
						var target = 'sub2';
						break;
					case 3:
						var name = value.sub3;
						var target = 'sub3';
						break;
					case 4:
						var name = value.sub4;
						var target = 'sub4';
						break;
					default:
						var name = '';
						var target = '';
						break;
				}
				var li = jQuery('<li data-idx="'+idx+'" data-parent="'+ul.attr('data-target')+'" data-target="'+target+'" data-oid="'+value.oid+'" data-org-name="'+name+'">'+name+'</li>');
				li.bind('click',function(e) {
					var $this = jQuery(this);
					var idx = parseInt( $this.attr('data-idx') );
					var orgs = self.orgs[idx];
					orgs.name.val( $this.attr('data-org-name') );
					if(orgs.p.length > 0)
						orgs.p.val( $this.attr('data-oid') );
					self.hideOrgBox(idx);
				});
				ul.append(li);
			});
		},

		hideOrgBox: function(idx) {
			this.orgs[idx].f.addClass('collapsed');
		},

		decollapseOrgBox: function(idx) {
			this.orgs[idx].f.removeClass('collapsed');
		},

		checkOrgNameValue: function(element) {
			var self = this;
			jQuery(element).on('input',function(e) {
				if(!jQuery(this).val()) {
					var depth = parseInt(jQuery(this).attr('data-depth'));
					self.orgs[(depth-1)].p.val('0');
				}
			});
		},

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
					self.selectParentTaxonomySingle(element,pid);
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

		initTaxonomyMultiple: function() {
			var self = this;
			this.Root.find('.taxonomy-field.multiple').each(function() {
				self.handleTaxonomyMultiple(jQuery(this));
			});
		},

		handleTaxonomyMultiple: function(element) {
			var self = this;
			element.addClass('collapsed');

			var label = element.find('label.select-box');
			label.bind('click.cadb',function(e) {
				element.toggleClass('collapsed');
			});

			var select = element.find('ul.taxonomy-items');
			select.find('li.taxonomy-item input[type="checkbox"]').each(function(idx) {
				var obj = jQuery(this);
				if(obj.is(':checked')) {
					obj.parents('.taxonomy-item').addClass('selected');
				}
				obj.change(function(e) {
					var item = jQuery(this);
					if(item.is(':checked')) {
						item.parents('.taxonomy-item').addClass('selected');
						var pid = parseInt( item.attr('data-parent') );
						if( pid ) {
							self.selectParentTaxonomySingle(element,pid);
						}
					} else {
						item.parents('.taxonomy-item').removeClass('selected');
					}
					self.rebuildTaxonomyValue(element);
				});
			});
		},

		keyhandle: function() {
			var self = this;
			jQuery(document).keydown(function(event) {
				var code = event.charCode || event.keyCode;
				if(self.mode == 'dialog' && code == 27) {
					self.closeDialog();
				} else if(code == 83 && (event.ctrlKey || event.altKey)) {
					event.preventDefault();
					self.save();
				}
			});
		},

		initForm: function() {
			var self = this;
			this.Root.find('button.modify').bind('click',function(e) {
				e.preventDefault();
				self.save();
			});
			this.Root.find('button.delete').bind('click',function(e) {
				e.preventDefault();
				self.deletes();
			});
			this.Root.find('button.back').bind('click',function(e) {
				e.preventDefault();
				self.goList();
			});
		},

		save: function() {
			var self = this;
			var url = "/api/save/orgs";

			var params = 'oid='+this.Root.find('input[name="oid"]').val();
			params += '&vid='+this.Root.find('input[name="vid"]').val();

			var nojo = this.Root.find('input#nojo');
			if(!nojo.val()) {
				nojo.attr('placeholder','노조명을 입력하세요').focus();
				return;
			}
			params += "&nojo="+nojo.val();
			params += "&p1="+this.Root.find('input#p1').val();

			var sub1 =  this.Root.find('input#sub1');
			if(sub1.val()) {
				params += '&sub1='+sub1.val();
				params += '&p2='+this.Root.find('input#p2').val();
			} else {
				params += '&p2=0';
			}

			var sub2 =  this.Root.find('input#sub2');
			if(sub2.val()) {
				params += '&sub2='+sub2.val();
				params += '&p3='+this.Root.find('input#p3').val();
			} else {
				params += '&p3=0';
			}

			var sub3 =  this.Root.find('input#sub3');
			if(sub3.val()) {
				params += '&sub3='+sub3.val();
				params += '&p4='+this.Root.find('input#p4').val();
			} else {
				params += '&p4=0';
			}

			var sub4 =  this.Root.find('input#sub4');
			if(sub4.val()) {
				params += '&sub4='+sub4.val();
			}

			this.save_abort = false;
			this.Root.find('.cadb-field').each(function() {
				var $this = jQuery(this);
				if( $this.hasClass('taxonomy-list') ) {
					params += '&'+self.makeTaxonomyParams($this);
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
							nojo.attr('placeholder',message).focus();
							break;
						case -2:
						case -3:
							self.abortDialog(message,0);
							break;
						case 0:
							var oid = parseInt(message);
							if(!self.Root.find('input[name="oid"]').val()) {
								self.Root.find('input[name="oid"]').val(oid);
								self.Root.find('input[name="vid"]').val(oid);
								if (window.history.replaceState) {
									window.history.pushState(null, '단체정보 수정하기', window.location.href.replace(/add/,'edit')+'?oid='+oid);
								}
								self.Root.find('button.modify').text('수정하기');
								self.Root.find('button.delete').addClass('show');
								jQuery('.header .organ-name').text('단체정보 수정하기');
							} else {
								self.Root.find('input[name="oid"]').val(oid);
							}
							break;
						default:
							self.abortDialog(message,error);
							break;
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
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
					var fd = element.parents('fieldset.fields-org');
					this.abortDialog( fd.find('label.field').text()+" 는 반드시 입력해야 합니다", 0 );
					return;
				}
			}
			q += JSON.stringify(json);

			return q;
		},

		makeParams: function(element,type) {
			var q = 'f'+element.attr('data-fid')+'=';
			var fd = element.parents('fieldset.fields-org');
			if( parseInt( element.attr('data-required') ) == 1 ) {
				if( !element.val() ) {
					this.save_abort = true;
					this.abortDialog( fd.find('label.field').text()+" 는 반드시 입력해야 합니다", element.attr('data-fid') );
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

		deletes: function() {
			var self = this;
			var url = "/api/save/orgs";

			var params = 'oid='+this.Root.find('input[name="oid"]').val()+'&mode=delete';

			jQuery.ajax({
				url: url,
				data: params,
				dataType: 'json',
				method: 'POST',
				beforeSend: function() {
					self.loading();
				},
				success: function(json) {
					self.removeLoading();
					var error = parseInt(json.error);
					var message = json.message;
					if(!error) {
						var h = window.location.href;
						if(h.match(/admin\/orgs/)) {
							var _q = window.location.search.substr(1).split("&");
							var q = "";
							var c = 0;
							for(var i=0; i<_q.length; i++) {
								var _qp = _q[i].split("=");
								if(typeof(_qp[0]) == 'undefined' || _qp[0] == 'oid') continue;
								q += (c++ ? "&" : "")+_q[i];
							}
							window.location.href = '/admin/orgs/'+(q ? "?"+q : '');
						} else {
							self.goList();
						}
					} else {
						self.abortDialog(message,0);
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		goList: function() {
			var h = window.location.href;
			if(h.match(/admin\/orgs/)) {
				var _q = window.location.search.substr(1).split("&");
				var q = "";
				var c = 0;
				for(var i=0; i<_q.length; i++) {
					var _qp = _q[i].split("=");
					if(typeof(_qp[0]) == 'undefined' || _qp[0] == 'oid') continue;
					q += (c++ ? "&" : "")+_q[i];
				}
				window.location.href = '/admin/orgs/'+(q ? "?"+q : '');
			} else {
				history.back();
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

		removeLoading: function() {
			jQuery('body .saving').remove();
		}
	}

	jQuery.fn.cadbOrgEditor = function(options) {
		return this.each(function() {
			var cadbOrgEditor = new CADBOrgEditor(jQuery(this),options);
		});
	};

	jQuery.fn.cadbOrgEditor.defaults = {
		searchBoxBackground : '<div class="searching-org-list"></div>',
		searchBox : '<div class="search-list-box"><div class="search-inner-box"><h3>조직 검색</h3><form action="/api/orgs" method="POST"><input type="text" name="q" value=""><button type="submit">검색</button></form><section class="orgs"></section><div class="progress-spinner><i class="fa fa-spinner"></i></div></div><div class="close"><i class="fa fa-close"></i></div></div>',
		dialog : '<div class="alert-dialog"><div class="alert-dialog-inner"></div><div class="close"><i class="fa fa-close"></i></div></div>'
	};
	
	jQuery.fn.cadbOrgEditor.settings = {
	};
})(jQuery);

jQuery(document).ready(function(e) {
	jQuery('#org-edit-form').cadbOrgEditor({
	});
});
