jQuery(document).ready(function(e) {
	jQuery('.orgs-list-header button.option').click(function(e) {
		var c = jQuery('.orgs-list-header #org-search-option');
		if(c.hasClass('collapsed')) {
			c.removeClass('collapsed');
		} else {
			c.addClass('collapsed');
		}
	});

	jQuery('#autocomplete-admin-page .reset button').click(function(e) {
		var url = site_base_uri+'/admin/autocomplete/save';
		jQuery.ajax({
			url: url,
			dataType: 'json',
			method: 'GET',
			beforeSend: function() {
				jQuery('body').append(jQuery('<div class="saving"><div class="saving-background"></div><div class="is-loading"><i class="fa fa-spinner fa-pulse"></i></div></div>'));
				jQuery('.saving .is-loading').css({
					'left' : parseInt( ( jQuery(window).width() - 100 ) / 2 ),
					'top' : parseInt( ( jQuery(window).height() - 100 ) / 2 )
				});
			},
			success: function(json) {
				jQuery('body .saving').remove();
				var error = parseInt(json.error);
				if(!error) {
					var resultBox = jQuery('<div class="alert-dialog"><div class="alert-dialog-inner">'+json.message+'</div></div>');
					resultBox.appendTo('body');
					setTimeout(function() {
						jQuery('.alert-dialog').remove();
					},2000);
				} else {
					alert(json.message);
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				jQuery('body .saving').remove();
			}
		});
	});
});
