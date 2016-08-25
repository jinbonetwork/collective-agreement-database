jQuery(document).ready(function(e) {
	jQuery('.orgs-list-header button.option').click(function(e) {
		var c = jQuery('.orgs-list-header #org-search-option');
		if(c.hasClass('collapsed')) {
			c.removeClass('collapsed');
		} else {
			c.addClass('collapsed');
		}
	});
});
