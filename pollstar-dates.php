<?php
/**
 * @package Pollstar_Dates
 */
/*
Plugin Name: Pollstar Artist Dates
Description: Show Pollstar Artist's tour dates on Wordpress site.  Requires Pollstar API key.
Version: 1.0
Author: Expomás Diseño Web
Author URI: https://expomas.com
*/

wp_enqueue_style('pollstar-dates', plugins_url( '/pollstar-dates.css', __FILE__ ), false, '', 'screen');

function pollstar_shows($atts) {

	//echo "Do we get here?";

	extract( shortcode_atts( array(
		'num_shows' => 1000 // set default to lots...
	), $atts ) );

	// 1st: figure out if we should write dates from pollstar-cache.xml, or Pollstar server...
	$cache = ABSPATH . 'wp-content/plugins/pollstar-dates/pollstar-cache.xml';

	// Is pollstar-cache.xml too old? (more than an hour old), then get new Pollstar data
	$cache_age = time() - filemtime($cache);

	// Is pollstar-cache.xml empty?  Then try and get new data (ex. on first use of plugin)
	$cache_stat = stat($cache);
	if ( ($cache_age > (60*60) ) OR ($cache_stat['size'] < 260) )  {

		$ch = curl_init();

		// Construct the $data var to pass to Pollstar using the db's API & Artist ID
		$artist_id = get_option('pollstar_artist');
		$pollstar_api = get_option('pollstar_api');

		$data = "artistID=$artist_id&startDate=1-1-2012&dayCount=0&page=0&pageSize=0&apiKey=$pollstar_api";

		curl_setopt($ch, CURLOPT_URL, 'http://data.pollstar.com/api/pollstar.asmx/ArtistEvents');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$xmlstr = curl_exec($ch);

		if (stripos($xmlstr, "not valid") OR stripos($xmlstr, "not authorized")) {
			echo "Pollstar API/Artist not valid<br /><br />";
			$invalid = true;
		}

		$response = new SimpleXMLElement($xmlstr);

		// write new Pollstar data to cache
		// Don't cache if we got an invalid response...

		if(is_file($cache) && is_readable($cache)){

			 $open = fopen($cache, 'w') or die ("File cannot be opened.");
			 fwrite($open, $response->asXML());
			 fclose($open);

		} else {

			echo "Error: check that pollstar-cache.xml is writable<br /><br />";
		}

		curl_close($ch);

	} else { // this means $cache is NOT too old, so use it instead of getting Pollstar data...

		$xmlstr = file_get_contents($cache);
		$response = new SimpleXMLElement($xmlstr);
	}

	//Check if there are Events for this Artist
	if (count($response->Events->Event) > 0) {

		// Are we writing table headers here?
		$show_headers = get_option('pollstar_show_headers');

		if ($show_headers == 1) {

			echo '<div id="pollstar_headers">';

			echo '<div class="pollstar_date">Date</div>';
			echo '<div class="pollstar_venue">Venue</div>';
			echo '<div class="pollstar_city">City</div>';
			echo '<div style="clear:both"></div>';

			echo '</div>'; // end pollstar_headers div
		}

		foreach($response->Events->Event as $result) {

			// This will check if it's a "festival" type show...
			$artist_type = $result->Artists->Artist->attributes()->ArtistTypeID;

			// format the date
			echo "<div class=\"pollstar_date\">".date(get_option('pollstar_date_format'),strtotime($result->attributes()->PlayDate))."</div>";

			echo "<div class=\"pollstar_venue\"><a href=\"".$result->attributes()->Url."\" target=\"_blank\">";

			// Account for "festival" type shows...
			if ($artist_type == 1) {

				echo $result->attributes()->VenueName."</a></div>";

			} else {

				echo $result->attributes()->EventName."</a></div>";
			}

			echo "<div class=\"pollstar_city\">".$result->attributes()->Region."</div><div style=\"clear:both\"></div>";

			$show_count++;

			if ($show_count == $num_shows) {
				break;
			}

		} // end foreach loop for each Event

	} else { // There are no events

		if (!($invalid)) {

			echo get_option('pollstar_noshow_text')."<br /><br />";
		}
	}
	?>

	<!-- required under Pollstar API terms -->
	<div id="pollstar_link"><a href="http://www.pollstar.com" target="_blank">Powered by Pollstar</a></div>

	<?php

}

add_shortcode('pollstar_shows','pollstar_shows');

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'pollstar_dates_install');

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'pollstar_dates_remove' );

function pollstar_dates_install() {

	/* Creates new database field */
	add_option("pollstar_artist", '00000', '', 'yes');
	add_option("pollstar_api", '00000-0000000', '', 'yes');
	add_option("pollstar_date_format", 'n/j/y', '', 'yes');
	add_option("pollstar_noshow_text", 'Currently no shows scheduled', '', 'yes');
	add_option("pollstar_show_headers", '0', '', 'yes');
	add_option("pollstar_terms", '0', '', 'yes');
}

function pollstar_dates_remove() {

	/* Deletes the database field */
	delete_option('pollstar_api');
	delete_option('pollstar_artist');
	delete_option('pollstar_date_format');
	delete_option('pollstar_noshow_text');
	delete_option('pollstar_show_headers');
	delete_option('pollstar_terms');
}

if ( is_admin() ){

	/* Call the html code */
	add_action('admin_menu', 'pollstar_dates_admin_menu');

	function pollstar_dates_admin_menu() {

		add_options_page('Pollstar Dates', 'Pollstar Dates', 'administrator',
		'pollstar-dates', 'pollstar_dates_html_page');
	}


} // end is_admin

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'pollstar_dates_action_links' );

function pollstar_dates_action_links( $links ) {
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=pollstar-dates') ) .'">Settings</a>';
   //$links[] = '<a href="http://wp-buddy.com" target="_blank">More plugins by WP-Buddy</a>';
   return $links;
}

function pollstar_dates_html_page() {
?>

    <div class="wrap" style="max-width: 61.727777777%;">
	<h2>Pollstar Artist Dates Options</h2>

	<br />
	<em>Any donations appreciated</em><br />

<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RY5H979GPM5ZE'
	target='_blank' style="float:left;">
	<img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" /></a>

	<div style="float:left;margin-left:50px;"><strong>Bitcoin:</strong>
		1MDRFyVS93BVLc5dgpGpVVupy3X5CSveDa</div>
<div style="clear:both;height:30px;"></div>

	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>

	<table width="710">
	<tr valign="top">
		<td colspan="2">

			<?php if (get_option('pollstar_show_terms') == 1) { ?>

				<input type="checkbox" name="terms" value="1" checked required>

			<?php } else { ?>

				<input type="checkbox" name="terms" value="0" required>

			<?php } ?>


		I agree to
			display "Powered by Pollstar" external link, as required by
			<a href="http://data.pollstar.com/api/" target="_blank">Pollstar's API Terms
			and conditions</a>.
		</td>
	</tr>
	<tr valign="top">
	<th width="192" style="text-align:left;">Enter Pollstar Artist ID</th>
	<td width="406">
	<input name="pollstar_artist" type="text" id="pollstar_artist"
	value="<?php echo get_option('pollstar_artist'); ?>" />
	<a href="http://data.pollstar.com/api/" target="_blank">Pollstar API reference</a>
	</td>
	</tr>

	<tr valign="top">
	<th width="192" style="text-align:left;">Enter Pollstar API key</th>
	<td width="406">
	<input name="pollstar_api" type="text" id="pollstar_api"
	value="<?php echo get_option('pollstar_api'); ?>" />
	</td>
	</tr>

	<tr valign="top">
	<th width="192" style="text-align:left;">Date format:</th>
	<td width="406">
	<input name="pollstar_date_format" type="text" id="pollstar_date_format"
	value="<?php echo get_option('pollstar_date_format'); ?>" />
	<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">Documentation on date and time formatting</a>
	</td>
	</tr>

	<tr valign="top">
	<th width="192" style="text-align:left;">No shows text:</th>
	<td width="406">
	<input name="pollstar_noshow_text" type="text" id="pollstar_noshow_text"
	value="<?php echo get_option('pollstar_noshow_text'); ?>" size="50" />
	</td>
	</tr>

	<tr valign="top">
	<th width="192" style="text-align:left;">Show column headers?:</th>
	<td width="406">

		<select name="pollstar_show_headers">

		<?php if (get_option('pollstar_show_headers') == 1) { ?>

			<OPTION VALUE="1" SELECTED>Yes
			<OPTION VALUE="0">No

		<?php } else { ?>

			<OPTION VALUE="0" SELECTED>No
			<OPTION VALUE="1">Yes

		<?php } ?>
		</select>
	</td>
	</tr>


	</table>

	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="pollstar_artist,pollstar_api,pollstar_date_format,pollstar_noshow_text,pollstar_show_headers" />

	<p>
	<input type="submit" value="<?php _e('Save Changes') ?>" />
	</p>

	</form>

	<br />
	<i>Also available (premium): Pollstar dates by venue, Dates for multiple Pollstar artists.<br />
		<a href="mailto:info@expomas.com" target="_blank">Contact us</a> for details.</i>.

	</div>
<?php
} // end function pollstar_dates_html_page
?>
