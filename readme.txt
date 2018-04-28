=== Embed Pollstar Artist Tour Dates ===
Contributors: expomas
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RY5H979GPM5ZE
Tags: pollstar, tour
Requires at least: 3.1
Tested up to: 4.9.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display artist's tour dates using Pollstar's ArtistEvents API method.  Pollstar API key required.

== Description ==

Show upcoming tour dates for your Pollstar artist on any Wordpress page or post using a shortcode.

Default usage is:

`[pollstar_shows]`

which will show all upcoming shows.  You may specify a number of upcoming shows as follows:

`[pollstar_shows num_shows=5]`

which will show only the next five shows.

CSS hooks are included in the embed-pollstar-dates.css file included in the plugin folder.

Tour dates are shown in the following format: Date (date format configurable in Settings), Venue (which will appear as a link
to the Pollstar listing to that Event), and City.  For "festival" type shows, the "Event Name" will be shown instead of Venue.

Plugin shows a "Powered by Pollstar" link, as required under Pollstar's API
<a href="http://data.pollstar.com/api/" target="_blank">Terms of Service</a>.

The XML returned from Pollstar will be cached in a file in the plugins folder embed-pollstar-cache.xml.  The plugin will only
get new data from the Pollstar API if this cache is more than one hour old.  embed-pollstar-cache.xml must be writable on your
web server.


== Installation ==

Download and extract the embed-pollstar-dates.zip

Extract the folder and upload it to the plugins directory of your WP build.

Activate the plugin from WP Admin, entering your Pollstar API key, and your artist's ID (as provided by Pollstar).
You must also agree to display an external link to Pollstar, as required by their terms and conditions.

Add the shortcode `[pollstar_shows]` to your desired page or post.

The plugin can also be called from a template file by using

`<?php echo do_shortcode('[pollstar_shows]'); ?>`

== Screenshots ==

1. Plugin Settings page in WP Admin
2. Sample page showing default settings of Pollstar dates

Also available (premium): Pollstar dates by venue, Dates for multiple Pollstar artists
