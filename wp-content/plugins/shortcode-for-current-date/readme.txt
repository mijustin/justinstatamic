=== Shortcode for Current Date ===
Contributors: imtiazrayhan, un_rick
Tags: shortcode, current date, month, year
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 1.2.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Insert current Date, Month or Year anywhere in your WordPress site with a simple shortcode.

== Description ==

**Shortcode for Current Date** enables you to put current date, current month or current year anywhere in your WordPress site with a simple shortcode.

Just use the shortcode **[current_date]** and when the post or page is displayed, **[current_date]** will be replaced by the current date.

[__Live Demo__](http://imtiazrayhan.com/show-current-date-month-year-in-wordpress/)

By default it will show a date like this: 7th May 2017

But you can customize it to your needs. To do that, you'll need to pass the date format using an attribute with the shortcode.
Here are some examples:

**[current_date format='d/m/Y']** will show the date like this: **07/05/2017**
**[current_date format='F d, Y']** will show the date like this: **May 07, 2017**

If you don't understand how this formatting works, that's totally okay.

Check out [PHP date formats](http://php.net/manual/en/function.date.php) you can use.

== Installation ==
This is one way you can install the plugin:

1. Download the plugin from WordPress plugin repository and upload the zip file in 'Add New' under 'Plugins'.
2. Activate the plugin.

Another way :

1. From WordPress dashboard, go to Add New under Plugins section.
2. Search for  'Shortcode for Current Date'
3. From search results, click on  'Shortcode for Current Date'.
4. Click on Install button and activate the plugin.

== Changelog ==

= 1.2.5 = 

* Fix - Day Number of the Year bug.

= 1.2.4 =

* Add - Shortcode execution in Post Title.

= 1.2.3 =

* Fixed a bug.
* Added WP current_time funtion instead of date function to retrieve WP time instead of server time. Thanks to Rick Carter.

= 1.2.2 =

* Tested with WordPress 4.9

= 1.2.1 =

* Fixed a bug.

= 1.2 =

* Added a Menu item to quickly access the date formats.

= 1.1 =

* Added a welcome page.

= 1.0 =

* Initial Release
