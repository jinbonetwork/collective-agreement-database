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

		self.initTaxonomySingle();
		self.initTaxonomyMultiple();
	}

	CADBOrgEditor.prototype = {
		initOrg: function() {
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
