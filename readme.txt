=== Mixed Updates ===
Contributors: complexli,SarahG111
Tags: blogroll, bookmarks, links, sidebar, rss
Requires at least: 2.0
Tested up to: 3.5
Stable tag: 0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use the RSS field in your Blogroll and display the most recent post from sites that you link to, ordered by date, not by site.

== Description ==

This plugin allows you to display the most recent posts from your favourite blogs somewhere on your site. 

It utilises the RSS link field from the blogroll for each of your links. If the RSS link exists it attempts to get the last posts from it. 

It then displays the most recent X number of posts depending on your settings, ordered by date.  This means that some blogs will contribute many more items to the list than others, simply because they post more frequently.

== Installation ==

Installation Instructions:

1. Download the plugin and unzip it.
2. Put the 'mixed-updates.php' file into your wp-content/plugins/ directory.
3. Go to the Plugins page in your WordPress Administration area and click 'Activate' next to Mixed Updates.
4. Go to the Options > Mixed Updates and configure your options.
5. Go to Manage - Links and either add RSS feed links to your existing links or add new links with their RSS feed (the RSS feed link goes into the Advanced section on the Manage - Links page).
6. For a non widget sidebar use this function to display a list of results:
   if (function_exists('mixed_updates')) mixed_updates();
7. For widget sidebars just go to your Widgets page in your admin and add the widget.

== Frequently Asked Questions ==

None so far.

== Changelog ==

= 0.5 =
* First public version, based on What Others Are Saying

== Upgrade Notice ==

= 0.5 =
First public version

== Screenshots ==

None so far.

== Support ==

Support is provided at http://www.complexli.com/contact/
