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

	function CADBMemberEditor(element,options) {
		var self = this;
		this.settings = $.extend({}, $.fn.cadbMemberEditor.defaults, options);

		this.Root = jQuery(element);

		var transEndEventNames = {
			'WebkitTransition' : 'webkitTransitionEnd',
			'MozTransition'    : 'transitionend',
			'transition'       : 'transitionend'
		}

		this.transitionEnd = transEndEventNames[ Modernizr.prefixed('transition') ];

		self.initForm();
		self.initOrganize();
		self.keyhandle();
	}

	CADBMemberEditor.prototype = {

		keyhandle: function() {
			var self = this;
			jQuery(document).keydown(function(event) {
				var code = event.charCode || event.keyCode;
				if(self.mode == 'search-org' && code == 27) {
					self.closeSearchOrganizeBox();
				} else if(self.mode == 'dialog' && code == 27) {
					self.closeDialog();
				} else if(code == 83 && (event.ctrlKey || event.altKey)) {
					event.preventDefault();
					self.save();
				}
			});
		},

		initForm: function() {
			var self = this;
			this.Root.find('select#mb_level').change(function(e) {
				var roles = self.Root.find('.org-roles');
				if(parseInt(jQuery(this).val()) == 6) {
					roles.removeClass('collapsed');
				} else {
					roles.find('.organize-field ul li.organize').remove();
					roles.addClass('collapsed');
				}
			});
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

		initOrganize: function() {
			var self = this;
			this.Root.find('.organize-list li.organize').each(function() {
				var $this = jQuery(this);
				self.bindDeleteEventOrganize($this);
			});
			this.Root.find('.organize-list li.add i.add').bind('click.cadb',function(e) {
				self.openOrgaizeSearchBox();
			});
		},

		bindDeleteEventOrganize: function(element) {
			var self = this;
			element.find('i.delete').bind('click.cadb',function(e) {
				element.remove();
			});
		},

		openOrgaizeSearchBox: function() {
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
					var result = jQuery('<div class="search-result">총 '+json.result.orgs.total_cnt+' 건을 검색했습니다.</div>');
					wrapper.append(result);
					if(json.orgs.length > 0) {
						for(var i=0; i<json.orgs.length; i++) {
							var item = jQuery('<article data-oid="' + json.orgs[i].oid + '" data-vid="' + json.orgs[i].vid + '" data-name="' + jQuery('<span>'+json.orgs[i].fullname+'</span>').text() + '">' + jQuery('<span>'+json.orgs[i].fullname+'</span>').text() + '</article>');
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
			var oid = element.attr('data-oid');
			var vid = element.attr('data-vid');
			var name = element.attr('data-name');
			var org = this.Root.find('.org-roles ul.organize-list');
			var t = org.find('.organize[data-oid="'+oid+'"]');
			if(t.length < 1) {
				var item = jQuery('<li class="organize" data-oid="'+oid+'" data-vid="'+vid+'" data-role=1 data-name="'+name+'">'+name+'<i class="delete fa fa-close"></i></li>');
				self.bindDeleteEventOrganize(item);
				var a = org.find('li.add');
				item.insertBefore(a);
			}
			this.closeSearchOrganizeBox();
		},

		closeSearchOrganizeBox: function() {
			this.searchBoxBackground.hide();
			this.searchBox.hide();
			this.mode = '';
		},

		save: function() {
			var self = this;
			var url = "/admin/member/save";
			var mode = this.Root.find('input[name="mode"]').val();

			var params = 'mb_no='+this.Root.find('input[name="mb_no"]').val();
			params += '&mode='+mode;

			var mb_id = this.Root.find('input#mb_id');
			if(!mb_id.val()) {
				mb_id.attr('placeholder','아이디를 입력하세요').focus();
				return;
			}
			params += '&mb_id='+mb_id.val();
			var mb_password = this.Root.find('input#mb_password');
			if(mode == 'add') {
				if(!mb_password.val()) {
					mb_password.attr('placeholder','비밀번호를 입력하세요').focus();
					return;
				}
			}
			if(mb_password.val()) {
				var mb_password_confirm = this.Root.find('input#mb_password_confirm');
				if(!mb_password_confirm.val()) {
					mb_password_confirm.attr('placeholder','비밀번호 확인을 입력하세요').focus();
					return;
				}
				if(mb_password.val() != mb_password_confirm.val()) {
					mb_password_confirm.val('').attr('placeholder','비밀번호가 일치하지 않습니다').focus();
				}
				params += '&mb_id='+mb_id.val();
			}
			if(mb_password.val() && mb_password_confirm.val()) {
				params += '&mb_password='+mb_password.val();
				params += '&mb_password_confirm='+mb_password_confirm.val();
			}

			var mb_name = this.Root.find('input#mb_name');
			if(!mb_name.val()) {
				mb_name.attr('placeholder','이름을 입력하세요').focus();
				return;
			}
			params += "&mb_name="+mb_name.val();

			var mb_nick = this.Root.find('input#mb_nick');
			if(!mb_nick.val()) {
				mb_nick.attr('placeholder','닉네임을 입력하세요').focus();
				return;
			}
			params += "&mb_nick="+mb_nick.val();

			var mb_email = this.Root.find('input#mb_email');
			if(!mb_email.val()) {
				mb_email.attr('placeholder','이메일을 입력하세요').focus();
				return;
			}
			params += "&mb_email="+mb_email.val();

			oq = this.makeOrganizeParams();
			if(oq) {
				params += "&"+oq;
				params += "&mb_level=6";
			} else {
				var mb_level = this.Root.find('select#mb_level');
				if(parseInt(mb_level.val()) == 6) {
					self.abortDialog('담당자는 담당조직을 하나 이상 선택하셔야 합니다',0);
					return;
				}
				params += "&mb_level="+mb_level.val();
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
							mb_id.attr('placeholder',message).focus();
							break;
						case -2:
							mb_password.attr('placeholder',message).focus();
							break;
						case -3:
							mb_password_confirm.val('').attr('placeholder',message).focus();
							break;
						case -4:
							mb_name.attr('placeholder',message).focus();
							break;
						case -5:
							mb_nick.attr('placeholder',message).focus();
							break;
						case -6:
							mb_email.attr('placeholder',message).focus();
							break;
						case 0:
							var mb_no = parseInt(message);
							if(!self.Root.find('input[name="mb_no"]').val()) {
								self.Root.find('input[name="mb_no"]').val(mb_no);
								self.Root.find('input[name="mode"]').val('modify');
								if (window.history.replaceState) {
									window.history.pushState(null, '개인정보 수정하기', window.location.href.replace(/add/,'edit')+'?mb_no='+mb_no);
								}
								self.Root.find('button.modify').text('수정하기');
								self.Root.find('button.delete').addClass('show');
								jQuery('.header .member-name').text('회원 수정하기');
							} else {
								self.Root.find('input[name="mb_no"]').val(mb_no);
							}
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

		makeOrganizeParams: function() {
			var self = this;
			var json = [];
			q = '';
			if(this.Root.find('fieldset.org-roles').hasClass('collapsed')) return q;
			this.Root.find('fieldset.org-roles li.organize').each(function() {
				var $this = jQuery(this);
				var obj = {};
				obj.oid = $this.attr('data-oid');
				obj.role = 1;
				json.push(obj);
			});
			if(json.length > 0) {
				q = 'roles='+JSON.stringify(json);
			}
			return q;
		},

		deletes: function() {
			var self = this;
			var url = "/admin/member/delete";

			var params = 'mb_no='+this.Root.find('input[name="mb_no"]').val()+'&mode=delete';

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
						if(h.match(/admin\/member/)) {
							var _q = window.location.search.substr(1).split("&");
							var q = "";
							var c = 0;
							for(var i=0; i<_q.length; i++) {
								var _qp = _q[i].split("=");
								if(typeof(_qp[0]) == 'undefined' || _qp[0] == 'mb_no') continue;
								q += (c++ ? "&" : "")+_q[i];
							}
							window.location.href = '/admin/member/'+(q ? "?"+q : '');
						} else {
							self.goList();
						}
					} else {
						self.abortDialog(message,0);
					}
				},
				error( jqXHR, textStatus, errorThrown ) {
					self.removeLoading();
					self.abortDialog(jqXHR.responseText,0);
				}
			});
		},

		goList: function() {
			var h = window.location.href;
			if(h.match(/admin\/member/)) {
				var _q = window.location.search.substr(1).split("&");
				var q = "";
				var c = 0;
				for(var i=0; i<_q.length; i++) {
					var _qp = _q[i].split("=");
					if(typeof(_qp[0]) == 'undefined' || _qp[0] == 'mb_no') continue;
					q += (c++ ? "&" : "")+_q[i];
				}
				window.location.href = '/admin/member/'+(q ? "?"+q : '');
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

		removeLoading: function() {
			jQuery('body .saving').remove();
		}
	}

	jQuery.fn.cadbMemberEditor = function(options) {
		return this.each(function() {
			var cadbMemberEditor = new CADBMemberEditor(jQuery(this),options);
		});
	};

	jQuery.fn.cadbMemberEditor.defaults = {
		searchBoxBackground : '<div class="searching-org-list"></div>',
		searchBox : '<div class="search-list-box"><div class="search-inner-box"><h3>조직 검색</h3><form action="/api/orgs" method="POST"><input type="text" name="q" value=""><button type="submit">검색</button></form><section class="orgs"></section><div class="progress-spinner><i class="fa fa-spinner"></i></div></div><div class="close"><i class="fa fa-close"></i></div></div>',
		dialog : '<div class="alert-dialog"><div class="alert-dialog-inner"></div><div class="close"><i class="fa fa-close"></i></div></div>'
	};
	
	jQuery.fn.cadbMemberEditor.settings = {
	};
})(jQuery);

jQuery(document).ready(function(e) {
	jQuery('#member-edit-form').cadbMemberEditor({
	});
});
