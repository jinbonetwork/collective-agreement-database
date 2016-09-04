(function ($) {
	function CADBAutocomplete(element,options) {
		var self = this;
		this.settings = $.extend({}, $.fn.cadbautocomplete.defaults, options);
		console.log(this.settings);

		this.Root = jQuery(element);

		var transEndEventNames = {
			'WebkitTransition' : 'webkitTransitionEnd',
			'MozTransition'    : 'transitionend',
			'transition'       : 'transitionend'
		}

		this.transitionEnd = transEndEventNames[ Modernizr.prefixed('transition') ];
		this.searchBoxAppend = false;
		this.searchBoxActivate = false;
		this.query = '';

		self.init();
		self.keyinit();
	}

	CADBAutocomplete.prototype = {
		init: function() {
			var self = this;

			this.Root.addClass('cadb-autocomplete-input');
			var l = this.Root.offset().left;
			var t = this.Root.offset().top;
			var w = this.Root.outerWidth(true);
			var h = this.Root.outerHeight();
			var max_w = jQuery(window).width() - l - 40;
			var min_w = w;

			this.searchBox = jQuery(this.settings.selectBox);
			this.searchBox.addClass('cadb-autocomplete-search');
			this.searchBox.css({
				'position': 'absolute',
				'z-index': 100000000,
				'left': l+'px',
				'top': (l + h)+'px',
				'max-width': max_w+'px',
				'min-width': min_w+'px'
			});

			this.Root.keyup(function(event) {
				$this = jQuery(this);
				var code = event.charCode || event.keyCode;
				if(code == 27) {
					self.destory();
					return;
				}
				if($this.val()) {
					if(self.searchBoxAppend !== true) {
						self.searchBox.appendTo('body');
						self.searchBoxAppend = true;
						self.searchBoxActivate = true;
					} else {
						self.searchBox.removeClass('hidding');
						self.searchBoxActivate = true;
					}
					self.search($this.val());
				}
			});
			this.Root.focus(function(e) {
				self.search(jQuery(this).val());
			});
		},

		keyinit: function() {
			var self = this;
			jQuery(document).keydown(function(event) {
				var code = event.charCode || event.keyCode;
				if(self.searchBoxActivate == true && code == 27) {
					self.destory();
				}
			});

			jQuery(document).click(function(event) {
				var c = jQuery(event.target).closest('.cadb-autocomplete-input', '.cadb-autocomplete-search');
				if(c.length < 1) {
					self.destory();
				}
			});
		},

		search: function(q) {
			var self = this;
			if(!q) return;
			if(q == this.query) {
				this.searchBox.removeClass('hidding');
				this.searchBoxActivate = true;
				return;
			}
			var url = site_base_uri + '/api/autocomplete';
			var params = "q="+q;
			var container = this.searchBox.find('ul');

			jQuery.ajax({
				url:  url,
				data: params,
				dataType: 'json',
				method: 'POST',
				success: function(json) {
					container.empty();
					self.searchBox.addClass('hidding');
					self.searchBoxActivate = false;
					if(json.result.found == true && parseInt(json.result.total_cnt)) {
						self.searchBox.removeClass('hidding');
						self.searchBoxActivate = true;
						for(var i=0; i<parseInt(json.result.total_cnt); i++) {
							var l = jQuery('<li class="autocomplete-item"><span class="autocomplete-name">'+json.recommand[i]+'</span></li>');
							self.bindClick(l);
							l.appendTo(container);
						}
					}
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					console.log(jqXHR.responseText,0);
				}
			});
		},

		bindClick: function(element) {
			var self = this;

			element.click(function(e) {
				$this = jQuery(this);
				var query = $this.find('.autocomplete-name').text();
				self.Root.val(query);
				if(self.settings.callback && typeof(self.settings.callback) == 'function') {
					self.settings.callback();
				}
				self.destory();
			});
		},

		destory: function() {
			this.searchBox.addClass('hidding');
			this.searchBoxActivate = false;
		},

		remove: function() {
			this.searchBox.remove();
			this.searchBoxAppend = false;
		}
	}

	jQuery.fn.cadbautocomplete = function(options) {
		return this.each(function() {
			var cadbautocomplete = new CADBAutocomplete(jQuery(this),options);
		});
	};

	jQuery.fn.cadbautocomplete.defaults = {
		selectBox: '<div class="autocomplete-box"><ul></ul></div>'
	};
})(jQuery);
