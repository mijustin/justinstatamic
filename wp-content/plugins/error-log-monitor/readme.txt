=== Error Log Monitor ===
Contributors: whiteshadow
Tags: dashboard widget, administration, error reporting, admin, maintenance, php
Requires at least: 4.5
Tested up to: 4.9
Stable tag: 1.6.2

Adds a Dashboard widget that displays the latest messages from your PHP error log. It can also send logged errors to email.

== Description ==

This plugin adds a Dashboard widget that displays the latest messages from your PHP error log. It can also send you email notifications about newly logged errors.

**Features**

* Automatically detects error log location.
* Explains how to configure PHP error logging if it's not enabled yet.
* The number of displayed log entries is configurable.
* Sends you email notifications about logged errors (optional).
* Configurable email address and frequency.
* You can easily clear the log file.
* The dashboard widget is only visible to administrators.
* Optimized to work well even with very large log files.

**Usage**

Once you've installed the plugin, go to the Dashboard and enable the "PHP Error Log" widget through the "Screen Options" panel. The widget should automatically display the last 20 lines from your PHP error log. If you see an error message like "Error logging is disabled" instead, follow the displayed instructions to configure error logging.

Email notifications are disabled by default. To enable them, click the "Configure" link in the top-right corner of the widget and enter your email address in the "Periodically email logged errors to:" box. If desired, you can also change email frequency by selecting the minimum time interval between emails from the "How often to send email" drop-down.

== Installation ==

Follow these steps to install the plugin on your site: 

1. Download the .zip file to your computer.
2. Go to *Plugins -> Add New* and select the "Upload" option.
3. Upload the .zip file.
4. Activate the plugin through the *Plugins -> Installed Plugins" page.
5. Go to the Dashboard and enable the "PHP Error Log" widget through the "Screen Options" panel.
6. (Optional) Click the "Configure" link in the top-right of the widget to configure the plugin.

== Screenshots ==

1. The "PHP Error Log" widget added by the plugin. 
2. Dashboard widget configuration screen.

== Changelog ==

= 1.6.2 =
* Added a setup wizard that helps new users create a log file and enable error logging. You can still do it manually you prefer. The setup notice will automatically disappear if logging is already configured.
* Fixed a bug where activating the plugin on individual sites in a Multisite network could, in some cases, trigger a fatal error.
* Additional testing with WP 5.0-alpha.

= 1.6.1 =
* Fixed the "upgrade" link being broken in certain configurations.

= 1.6 =
* Added a colored dot showing the severity level to each error message. Fatal errors are red, warnings are orange, notices and strict-standards messages are grey, and custom or unrecognized messages are blue.
* Added a new setting for email notifications: "how often to check the log for new messages". 
* Added a notice explaining how to configure WordPress to log all types of errors (including PHP notices) instead of just fatal errors and warnings.
* Added Freemius integration.
* Added a link to the Pro version to bottom of the widget.
* Improved parsing of multi-line log entries. Now the plugin will show all of the lines as part of the same message instead of treating every line as an entirely separate error.
* Improved stack trace formatting.
* In Multisite, the dashboard widget now also shows up in the network admin dashboard.
* Changed permissions so that only Super Admins can change plugin settings or clear the log file. Regular administrators can still see the widget.

= 1.5.7 =
* The widget now displays log timestamps in local time instead of UTC.
* Fixed a runtime exception "Backtrack buffer overflow" that was thrown when trying to parse very long log entries.

= 1.5.6 =
* The dashboard widget now shows the log file size and the "Clear Log" button even when all entries are filtered out.
* Tested with WP 4.9 and WP 5.0-alpha.

= 1.5.5 =
* Fixed two PHP notices: "Undefined index: schedule in [...]Cron.php on line 69" and "Undefined index: time in [...]Cron.php on line 76".
* Added "error_reporting(E_ALL)" to the example code to log all errors and notices.
* Tested up to WP 4.9-beta2.

= 1.5.4 =
* Fixed the error "can't use method return value in write context". It was a compatibility issue that only affected PHP versions below 5.5.

= 1.5.3 =
* You can send email notifications to multiple addresses. Just enter a comma-separated list of emails.
* Made sure that email notifications are sent no more often than the configured frequency even when WordPress is unreliable and triggers cron events too frequently.
* Tested up to WP 4.9-alpha-40871.

= 1.5.2 =
* Fixed a fatal error caused by a missing directory. Apparently, SVN externals don't work properly in the wordpress.org plugin repository.

= 1.5.1 =
* Added an option to ignore specific error messages. Ignored messages don't show up in the dashboard widget and don't generate email notifications, but they stay in the log file.
* Added limited support for parsing stack traces generated by PHP 7.
* Made the log output more compact.
* Improved log parsing performance.
* Fixed an "invalid argument supplied for foreach" warning in scbCron.

= 1.5 =
* Added a severity filter. For example, you could use this feature to make the plugin send notifications about fatal errors but not warnings or notices.
* Added limited support for XDebug stack traces. The stack trace will show up as part of the error message instead of as a bunch of separate entries. Also, stack trace items no longer count towards the line limit.

= 1.4.2 =
* Hotfix for a parse error that was introduced in version 1.4.1.

= 1.4.1 =
* Fixed a PHP compatibility issue that caused a parse error in Plugin.php on sites using an old version of PHP.

= 1.4 =
* Added an option to send an email notification when the log file size exceeds the specified threshold.
* Fixed a minor translation bug.
* The widget now shows the full path of the WP root directory along with setup instructions. This should make it easier to figure out the absolute path of the log file.
* Tested with WP 4.6-beta3.

= 1.3.3 =
* Added i18n support.
* Added an `elm_show_dashboard_widget` filter that lets other plugins show or hide the error log widget.
* Tested with WP 4.5.1 and WP 4.6-alpha.

= 1.3.2 =
* Tested up to WP 4.5 (release candidate).

= 1.3.1 =
* Added support for Windows and Mac style line endings.

= 1.3 =
* Added an option to display log entries in reverse order (newest to oldest).
* Added a different error message for the case when the log file exists but is not accessible.
* Only load the plugin in the admin panel and when running cron jobs.
* Fixed the error log sometimes extending outside the widget.
* Tested up to WP 4.4 (alpha version).

= 1.2.4 =
* Tested up to WP 4.2 (final release).
* Added file-based exclusive locking to prevent the plugin occasionally sending duplicate email notifications.

= 1.2.3 =
* Tested up to WP 4.2-alpha.
* Refreshing the page after clearing the log will no longer make the plugin to clear the log again.

= 1.2.2 = 
* Updated Scb Framework to the latest revision.
* Tested up to WordPress 4.0 beta.

= 1.2.1 = 
* Tested up to WordPress 3.9.

= 1.2 = 
* Tested up to WordPress 3.7.1.

= 1.1 = 
* Fixed plugin homepage URL.
* Fix: If no email address is specified, simply skip emailing the log instead of throwing an error.
* Tested with WordPress 3.4.2.

= 1.0 =
* Initial release.
