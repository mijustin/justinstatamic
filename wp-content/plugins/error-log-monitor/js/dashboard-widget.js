jQuery(function($) {
	var widget = $('#ws_php_error_log'),

		dashboardNoFilterOption = widget.find('#elm_dashboard_message_filter_all'),
		dashboardCustomFilterOption = widget.find('#elm_dashboard_message_filter_selected'),

		emailMatchFilterOption = widget.find('#elm_email_message_filter_same'),
		emailCustomFilterOption = widget.find('#elm_email_message_filter_selected'),

		dashboardFilterOptions = widget.find('input[name^="ws_php_error_log[dashboard_severity_option-"]'),
		emailFilterOptions = widget.find('input[name^="ws_php_error_log[email_severity_option-"]');

	function updateDashboardOptions() {
		dashboardFilterOptions.prop('disabled', !dashboardCustomFilterOption.is(':checked'))
	}
	function updateEmailOptions() {
		emailFilterOptions.prop('disabled', !emailCustomFilterOption.is(':checked'));
	}

	//First enable/disable the checkboxes when the page loads.
	updateDashboardOptions();
	updateEmailOptions();

	//Then refresh them when the user changes filter settings.
	dashboardCustomFilterOption.add(dashboardNoFilterOption).on('change', function() {
		updateDashboardOptions();
	});
	emailCustomFilterOption.add(emailMatchFilterOption).on('change', function() {
		updateEmailOptions();
	});

	//Handle the "Ignore" link.
	widget.on('click', '.elm-ignore-message', function() {
		var row = $(this).closest('.elm-entry'),
			message = row.data('raw-message');

		//Hide all copies of this message.
		row.closest('.elm-log-entries').find('.elm-entry').filter(function() {
			return $(this).data('raw-message') === message;
		}).hide().remove();

		AjawV1.getAction('elm-ignore-message').post({ message: message });

		return false;
	});

	//And the "Unignore" link.
	widget.on('click', '.elm-unignore-message', function() {
		var row = $(this).closest('tr'),
			message = row.data('raw-message');

		row.remove();
		AjawV1.getAction('elm-unignore-message').post({ message: message });

		return false;
	});

	//Handle the "Show X more" context link.
	widget.on('click', '.elm-show-mundane-context', function() {
		var link = $(this),
			container = link.closest('.elm-context-group-content');
		container.removeClass('elm-hide-mundane-items');
		link.hide().closest('tr,li').hide();
		return false;
	});

	//Handle the "Hide" link that hides the "Upgrade to Pro" notice.
	widget.on('click', '.elm-hide-upgrade-notice', function(event) {
		$(this).closest('.elm-upgrade-to-pro-footer').hide();
		AjawV1.getAction('elm-hide-pro-notice').post();
		event.preventDefault();
		return false;
	});

	//Move the "Upgrade to Pro" section to the very bottom of the widget settings panel, below the "Submit" button.
	var settingsForm = widget.find('.dashboard-widget-control-form'),
		proSection = settingsForm.find('#elm-pro-version-settings-section');
	if (settingsForm.length > 0) {
		proSection.appendTo(settingsForm).show();
	}
});
