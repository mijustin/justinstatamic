<?php

/**
 * Welcome Page View
 *
 * @since 1.1
 *
 */

// If accessed directly, exit
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap about-wrap">

	<h1><?php printf( __( 'Shortcode for Current Date', 'sfcd' ) ); ?></h1>

	<div class="about-text">
		<?php printf( __( "Thank you for using Shortcode for Current date plugin.
		Shortcode for Current Date is ready to show the current date, month and year for you. ", 'sfcd' ), '1.2.2' ); ?>
	</div>

	<div class="wp-badge welcome__logo"></div>

	<div class="feature-section two-col">
		<div class="col">
			<h3><?php _e( "Let's Get Started", 'sfcd' ); ?></h3>
			<ul>
				<li>In your post editor just put <strong>[current_date]</strong> where you want to show the date. </li>
				<li>If you want to show the month or year you would have to add something to the shortcode.</li>
				<li>You would need to write the shortcode like this: <br><br><strong>[current_date format='F, Y']</strong></li>
			</ul>

			The shortcode basically uses the PHP date function to generate the date, month or year. <br><br>That means, you can use any format the function accepts. <br>
			<br>
			Check out the date formats and use the ones that suits your needs.
			<br>
		</div>
		<div class="col">
			<h3><?php _e( "Check Out This Cool Theme - Echoes!", 'sfcd' ); ?></h3>
			Very lightweight, fast loading and responsive theme for blogging.<br><br>
			<a href="https://wordpress.org/themes/echoes/" target="_blank">Echoes</a> lets you have a fast loading, optimized blog without thinking about the speed and design.<br><br>
			<img width="500" height="300" src="<?php echo plugins_url( 'assets/img/screenshot.png', dirname( __FILE__ ) ); ?>">

		</div>
	</div>

	<div class="col">

		<table class="tbl1">
			<thead>
			<tr>
				<th>Format</th>
				<th>Description</th>
				<th>Returned values</th>
			</tr>

			</thead>

			<tbody class="tbody">

			<tr>
				<td style="text-align: center;"><em class="emphasis">Day</em></td>
				<td>---</td>
				<td>---</td>
			</tr>

			<tr>
				<td><em>d</em></td>
				<td>Day of the month, 2 digits with leading zeros</td>
				<td><em>01</em> to <em>31</em></td>
			</tr>

			<tr>
				<td><em>D</em></td>
				<td>A textual representation of a day, three letters</td>
				<td><em>Mon</em> through <em>Sun</em></td>
			</tr>

			<tr>
				<td><em>j</em></td>
				<td>Day of the month without leading zeros</td>
				<td><em>1</em> to <em>31</em></td>
			</tr>

			<tr>
				<td><em>l</em> (lowercase &#039;L&#039;)</td>
				<td>A full textual representation of the day of the week</td>
				<td><em>Sunday</em> through <em>Saturday</em></td>
			</tr>

			<tr>
				<td><em>N</em></td>
				<td>ISO-8601 numeric representation of the day of the week (added in
					PHP 5.1.0)</td>
				<td><em>1</em> (for Monday) through <em>7</em> (for Sunday)</td>
			</tr>

			<tr>
				<td><em>S</em></td>
				<td>English ordinal suffix for the day of the month, 2 characters</td>
				<td>
					<em>st</em>, <em>nd</em>, <em>rd</em> or
					<em>th</em>.  Works well with <em>j</em>
				</td>
			</tr>

			<tr>
				<td><em>w</em></td>
				<td>Numeric representation of the day of the week</td>
				<td><em>0</em> (for Sunday) through <em>6</em> (for Saturday)</td>
			</tr>

			<tr>
				<td><em>z</em></td>
				<td>The day of the year (starting from 0)</td>
				<td><em>0</em> through <em>365</em></td>
			</tr>

			<tr>
				<td style="text-align: center;"><em class="emphasis">Week</em></td>
				<td>---</td>
				<td>---</td>
			</tr>

			<tr>
				<td><em>W</em></td>
				<td>ISO-8601 week number of year, weeks starting on Monday</td>
				<td>Example: <em>42</em> (the 42nd week in the year)</td>
			</tr>

			<tr>
				<td style="text-align: center;"><em class="emphasis">Month</em></td>
				<td>---</td>
				<td>---</td>
			</tr>

			<tr>
				<td><em>F</em></td>
				<td>A full textual representation of a month, such as January or March</td>
				<td><em>January</em> through <em>December</em></td>
			</tr>

			<tr>
				<td><em>m</em></td>
				<td>Numeric representation of a month, with leading zeros</td>
				<td><em>01</em> through <em>12</em></td>
			</tr>

			<tr>
				<td><em>M</em></td>
				<td>A short textual representation of a month, three letters</td>
				<td><em>Jan</em> through <em>Dec</em></td>
			</tr>

			<tr>
				<td><em>n</em></td>
				<td>Numeric representation of a month, without leading zeros</td>
				<td><em>1</em> through <em>12</em></td>
			</tr>

			<tr>
				<td><em>t</em></td>
				<td>Number of days in the given month</td>
				<td><em>28</em> through <em>31</em></td>
			</tr>

			<tr>
				<td style="text-align: center;"><em class="emphasis">Year</em></td>
				<td>---</td>
				<td>---</td>
			</tr>

			<tr>
				<td><em>L</em></td>
				<td>Whether it&#039;s a leap year</td>
				<td><em>1</em> if it is a leap year, <em>0</em> otherwise.</td>
			</tr>

			<tr>
				<td><em>o</em></td>
				<td>ISO-8601 week-numbering year. This has the same value as
					<em>Y</em>, except that if the ISO week number
					(<em>W</em>) belongs to the previous or next year, that year
					is used instead. (added in PHP 5.1.0)</td>
				<td>Examples: <em>1999</em> or <em>2003</em></td>
			</tr>

			<tr>
				<td><em>Y</em></td>
				<td>A full numeric representation of a year, 4 digits</td>
				<td>Examples: <em>1999</em> or <em>2003</em></td>
			</tr>

			<tr>
				<td><em>y</em></td>
				<td>A two digit representation of a year</td>
				<td>Examples: <em>99</em> or <em>03</em></td>
			</tr>

			<tr>
				<td style="text-align: center;"><em class="emphasis">Time</em></td>
				<td>---</td>
				<td>---</td>
			</tr>

			<tr>
				<td><em>a</em></td>
				<td>Lowercase Ante meridiem and Post meridiem</td>
				<td><em>am</em> or <em>pm</em></td>
			</tr>

			<tr>
				<td><em>A</em></td>
				<td>Uppercase Ante meridiem and Post meridiem</td>
				<td><em>AM</em> or <em>PM</em></td>
			</tr>

			<tr>
				<td><em>B</em></td>
				<td>Swatch Internet time</td>
				<td><em>000</em> through <em>999</em></td>
			</tr>

			<tr>
				<td><em>g</em></td>
				<td>12-hour format of an hour without leading zeros</td>
				<td><em>1</em> through <em>12</em></td>
			</tr>

			<tr>
				<td><em>G</em></td>
				<td>24-hour format of an hour without leading zeros</td>
				<td><em>0</em> through <em>23</em></td>
			</tr>

			<tr>
				<td><em>h</em></td>
				<td>12-hour format of an hour with leading zeros</td>
				<td><em>01</em> through <em>12</em></td>
			</tr>

			<tr>
				<td><em>H</em></td>
				<td>24-hour format of an hour with leading zeros</td>
				<td><em>00</em> through <em>23</em></td>
			</tr>

			<tr>
				<td><em>i</em></td>
				<td>Minutes with leading zeros</td>
				<td><em>00</em> to <em>59</em></td>
			</tr>

			<tr>
				<td><em>s</em></td>
				<td>Seconds, with leading zeros</td>
				<td><em>00</em> through <em>59</em></td>
			</tr>

			<tr>
				<td><em>u</em></td>
				<td>
					Microseconds (added in PHP 5.2.2). Note that
					<span class="function"><strong>date()</strong></span> will always generate
					<em>000000</em> since it takes an <span class="type"><a href="language.types.integer.php" class="type integer">integer</a></span>
					parameter, whereas <span class="methodname"><a href="datetime.format.php" class="methodname">DateTime::format()</a></span> does
					support microseconds if <a href="class.datetime.php" class="classname">DateTime</a> was
					created with microseconds.
				</td>
				<td>Example: <em>654321</em></td>
			</tr>

			<tr>
				<td><em>v</em></td>
				<td>
					Milliseconds (added in PHP 7.0.0). Same note applies as for
					<em>u</em>.
				</td>
				<td>Example: <em>654</em></td>
			</tr>

			<tr>
				<td style="text-align: center;"><em class="emphasis">Timezone</em></td>
				<td>---</td>
				<td>---</td>
			</tr>

			<tr>
				<td><em>e</em></td>
				<td>Timezone identifier (added in PHP 5.1.0)</td>
				<td>Examples: <em>UTC</em>, <em>GMT</em>, <em>Atlantic/Azores</em></td>
			</tr>

			<tr>
				<td><em>I</em> (capital i)</td>
				<td>Whether or not the date is in daylight saving time</td>
				<td><em>1</em> if Daylight Saving Time, <em>0</em> otherwise.</td>
			</tr>

			<tr>
				<td><em>O</em></td>
				<td>Difference to Greenwich time (GMT) in hours</td>
				<td>Example: <em>+0200</em></td>
			</tr>

			<tr>
				<td><em>P</em></td>
				<td>Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)</td>
				<td>Example: <em>+02:00</em></td>
			</tr>

			<tr>
				<td><em>T</em></td>
				<td>Timezone abbreviation</td>
				<td>Examples: <em>EST</em>, <em>MDT</em> ...</td>
			</tr>

			<tr>
				<td><em>Z</em></td>
				<td>Timezone offset in seconds. The offset for timezones west of UTC is always
					negative, and for those east of UTC is always positive.</td>
				<td><em>-43200</em> through <em>50400</em></td>
			</tr>

			<tr>
				<td style="text-align: center;"><em class="emphasis">Full Date/Time</em></td>
				<td>---</td>
				<td>---</td>
			</tr>

			<tr>
				<td><em>c</em></td>
				<td>ISO 8601 date (added in PHP 5)</td>
				<td>2004-02-12T15:19:21+00:00</td>
			</tr>

			<tr>
				<td><em>r</em></td>
				<td><a href="http://www.faqs.org/rfcs/rfc2822" class="link external">&raquo;&nbsp;RFC 2822</a> formatted date</td>
				<td>Example: <em>Thu, 21 Dec 2000 16:01:07 +0200</em></td>
			</tr>

			<tr>
				<td><em>U</em></td>
				<td>Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)</td>
				<td>See also <span class="function"><a href="function.time.php" class="function">time()</a></span></td>
			</tr>

			</tbody>

		</table>
	</div>

	<div class="feature-section two-col">
		<div class="col">
			<h3><?php _e( 'Help Us Spread the Word!', 'sfcd' ); ?></h3>
			<p>Hey, if you like using this plugin, could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word.
			~ Imtiaz Rayhan</p>
			<a class="button-primary" href="https://wordpress.org/support/plugin/shortcode-for-current-date/reviews/#new-post">Ok, You deserve it!</a>
		</div>
		<div class="col">
			<h3><?php _e( 'Other Plugins', 'sfcd' ); ?></h3>
			<p><?php _e( 'You can also check out these other plugins I have created.', 'sfcd' ); ?></p>
			<ul>
				<li>
					<a href="https://wordpress.org/plugins/wp-coupons-and-deals/" target="_blank">WP Coupons and Deals</a>
				</li>
				<li>
					<a href="https://wordpress.org/plugins/icons-with-links-widget/" target="_blank">Icons with Links Widget</a>
				</li>
			</ul>
		</div>
	</div>

</div>
