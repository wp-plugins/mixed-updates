<?php
/*
  Plugin Name: Mixed Updates
  Plugin URI: http://www.complexli.com/
  Description: Use the RSS field in your Blogroll and display the most recent post from sites that you link to, ordered by date, not by site.
  Version: 0.5
  Author: complexli
  Author URI: http://www.complexli.com/

  Based on What Others Are Saying by SarahG111
*/

/*
  This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.

  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*/

// admin page options
function mixu_adminmenu() {
  add_options_page('Mixed Updates', 'Mixed Updates', 8, basename(__FILE__), 'mixu_options_page');
}
	 
function mixu_options_page() {
  if (isset($_POST['mixu_update_options'])) {
    update_option('mixu_filename', $_POST['mixu_filename']);
    update_option('mixu_waittime', (int)$_POST['mixu_waittime']);
    update_option('mixu_postCount', (int)$_POST['mixu_postCount']);
    update_option('mixu_follow', (int)$_POST['mixu_follow']);
    update_option('mixu_abbreviate', (int)$_POST['mixu_abbreviate']);
    update_option('mixu_siteLinked', (int)$_POST['mixu_siteLinked']);
    update_option('mixu_wrongcase', $_POST['mixu_wrongcase']);

    ?>
    <div id="message" class="updated fade"><p>Options Saved!</p></div>
       <?php
       }

  if (get_option('mixu_abbreviate')) {
    $mixu_abbreviate = 'checked="checked"';
  } else {
    $mixu_abbreviate = '';
  }
  if (get_option('mixu_siteLinked')) {
    $mixu_siteLinked = 'checked="checked"';
  } else {
    $mixu_siteLinked = '';
  }
  if (get_option('mixu_follow')) {
    $mixu_follow = 'checked="checked"';
  } else {
    $mixu_follow = '';
  }
			
  $mixu_filename = get_option('mixu_filename');			
  $mixu_waittime = get_option('mixu_waittime');
  $mixu_postCount = get_option('mixu_postCount');
  $mixu_wrongcase = get_option('mixu_wrongcase');
  ?>

<div class="wrap">
  <h2>Mixed Updates Options</h2>
  <p>Use this form to configure your plugin options.</p>
  <form id="mixu_form" method="post" action="" class="form-table">
    <fieldset>
      <legend>Available Options</legend>
      <div>
	<label for="mixu_waittime"><?php _e('Seconds between feed updates'); ?></label>
	<input type="text" size="25" id="mixu_waittime" name="mixu_waittime" value="<?php echo !empty($mixu_waittime) ? $mixu_waittime : 3600*3; ?>" />
      </div>
      <div>
	<label for="mixu_postCount"><?php _e('How many posts to display?'); ?></label>
	<input type="text" size="25" id="mixu_postCount" name="mixu_postCount" value="<?php echo !empty($mixu_postCount) ? $mixu_postCount : 5; ?>" />
      </div>
      <div>
	<label for="mixu_wrongcase"><?php _e('URLs that have wrong case'); ?></label>
	<input type="text" size="25" id="mixu_wrongcase" name="mixu_wrongcase" value="<?php echo !empty($mixu_wrongcase) ? $mixu_wrongcase : ''; ?>" />
      </div>
      <div>
	<label for="mixu_abbreviate"><?php _e('Abbreviate titles?'); ?></label>
	<input type="checkbox" id="mixu_abbreviate" name="mixu_abbreviate" value="1" <?php echo $mixu_abbreviate; ?> />
      </div>
      <div>
	<label for="mixu_siteLinked"><?php _e('Link the Site as well?'); ?></label>
	<input type="checkbox" id="mixu_siteLinked" name="mixu_siteLinked" value="1" <?php echo $mixu_siteLinked; ?> />
      </div>
      <div>
	<label for="mixu_follow"><?php _e('Set links as rel="nofollow"?'); ?></label>
	<input type="checkbox" id="mixu_follow" name="mixu_follow" value="1" <?php echo $mixu_follow; ?> />
      </div>
    </fieldset>
    <div class="submit">
      <input type="submit" name="mixu_update_options" value="Save Options"/>
    </div>
  </form>
</div>

  <?php		
}	 
	
// CSS for styling options form
function mixu_options_style() {
  ?>
  <style type="text/css" media="screen">
  #mixu_form legend { display: none; } 
  #mixu_form fieldset { border: none; margin: 0; padding: 0; }
  #mixu_form label { width: 225px; float: left; font-weight:bold; }
  #mixu_form fieldset div { clear: both; margin-top: 5px; background-color: #eaf3fa; padding: 12px; }
  </style>
      <?php
      }

add_action('admin_head', 'mixu_options_style');
add_action('admin_menu', 'mixu_adminmenu');	

register_activation_hook( __FILE__, 'mixu_activate' );

// activation function
function mixu_activate () {
  global $wpdb;
	
  // this is here as it won't connect if it's already connected, but some people had problems accessing the database
  mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
  mysql_select_db(DB_NAME);

  // check to see if the database table exists, if it doesn't then create it
  if ($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."mixuposts'") != $wpdb->prefix."mixuposts") {
    $sql = "CREATE TABLE ".$wpdb->prefix."mixuposts (
	 		id int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  			link_id int(5) UNSIGNED NOT NULL DEFAULT '0',
  			title tinytext NOT NULL,
  			link tinytext NOT NULL,
  			issued varchar(50) NOT NULL default '',
			PRIMARY KEY  (id)
			);";
	
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
	
  // options probably aren't set either
  add_option('mixu_lastCache', mktime()-(3600*24));
  add_option('mixu_waittime', 3600*3);
  add_option('mixu_wrongcase', '');
  add_option('mixu_postCount', 10);
  add_option('mixu_abbreviate', 1);
  add_option('mixu_siteLinked', 0);
  add_option('mixu_follow', 0);
}

// front end code
function mixed_updates() {
  global $wpdb;

  // get the options from the options table
  if (get_option('mixu_siteLinked')) {
    $siteLinked = TRUE;
  } else {
    $siteLinked = FALSE;
  }
  if (get_option('mixu_abbreviate')) {
    $mixu_abbr = TRUE;
  } else {
    $mixu_abbr = FALSE;
  }
			
  $mixu_follow = get_option('mixu_follow');
  $mixu_waittime = get_option('mixu_waittime');
  $mixu_postCount = get_option('mixu_postCount');
  $mixu_wrongcase = get_option('mixu_wrongcase');
  $mixu_lastCache = get_option('mixu_lastCache');

  $waittime = !empty($mixu_waittime) ? $mixu_waittime : 3600*3;
  $postCount = !empty($mixu_postCount) ? $mixu_postCount : 5;
  $wrongcase = !empty($mixu_wrongcase) ? $mixu_wrongcase : '';
  $cachetime = !empty($mixu_lastCache) ? $mixu_lastCache : mktime()-(3600*24);
  $nofollow = $mixu_follow == 1 ? TRUE : FALSE;

  // check whether there are posts in the table
  $postExist = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."mixuposts");

  define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');

  // check that the cache time exists and make sure that the cache time plus the delay is less than right now
  // also run if there are no posts in the mixupost table
  if ((!empty($cachetime) && $cachetime+$waittime < mktime()) || $postExist == 0) {
    // load the magpie rss fetch file
    // check for the rss file
    if (file_exists(ABSPATH . WPINC . '/rss.php')) {
      include_once (ABSPATH . WPINC . '/rss.php');
    } else {
      include_once (ABSPATH . WPINC . '/rss-functions.php');
    }
	 
    // select all of the links and their RSS links out of the bookmark table for those links that have their RSS set
    $sql = $wpdb->get_results("SELECT link_id, link_rss, link_name FROM ".$wpdb->links." WHERE link_rss != '' GROUP BY link_rss");

    if (@count($sql)) {
      // empty out the temporary mixuposts table to keep things clear
      $wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."mixuposts");
	 
      // this just sets the months as the FeedBurner feed stores them as short names instead of numbers
      $months = array("Jan" => "01", "Feb" => "02", "Mar" => "03", "Apr" => "04", "May" => "05", "Jun" => "06", "Jul" => "07", "Aug" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dec" => "12");

      // run through all the sites and get their last feed
      foreach ($sql as $site) {
	@$rss = fetch_rss($site->link_rss);

	// check that the $rss array got some data!
	if (is_array($rss->items)) {
	  foreach ($rss->items as $item) {
	    $title = str_replace(' & ', ' &amp; ', $item['title']);
	    $url   = $item['guid'];
							
	    // not a feedburner feed so try getting the link instead
	    if (empty($url) || (substr($url, 0, 4) != "http")) $url = $item['link'];
							
	    // if the feed is a Feedburner RSS feed then the pubdate should exist
	    if (!empty($item['pubdate'])) {
	      $issued = $item['pubdate'];
							
	      $issued = substr($issued, 5);
	      $day = substr($issued, 0, 2);
	      $month = substr($issued, 3, 3);
	      $year = substr($issued, 7, 4);
	      $ptime = substr($issued, 12, 8);
								 
	      $month = $months[$month];
								 
	      $issued = $year."-".$month."-".$day." ".$ptime;
	    } else {
	      // if the feed isn't RSS then one of the following may work!
	      if (!empty($item['published'])) {
		$issued = $item['published'];
	      } elseif (!empty($item['dc']['date'])) {
		$issued = $item['dc']['date'];
	      }
								 
	      $issdate = substr($issued, 0, 10);
	      $isstime = substr($issued, 11, 8);
								 
	      $issued = $issdate." ".$isstime; 
	    }

	    // correct title casing if URL on blacklist:
	    if (preg_match('/'.$wrongcase.'/i', $url))
	      $title = ucwords(strtolower($title));

	    // if the URL is empty then there's no point in adding it.
	    if (!empty($url)) {
	      // insert the entry into the database table
	      $wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."mixuposts (link_id, title, link, issued) VALUES (%d, %s, %s, %s)", $site->link_id, $title, $url, $issued));
	    }
	  }
	}
      }

      // update the cache time
      update_option('mixu_lastCache', mktime());
    } else {
      echo "No posts found";
    }
  }

  // Find the ID of the link category:
  $options = get_option('widget_mixu');
  $linkcatname = $options['linkcatname'];

  $linkcat = $wpdb->get_var("select tax.term_taxonomy_id from wp_term_taxonomy tax left join wp_terms term on tax.term_id = term.term_id where term.name = '$linkcatname'");
  $sqltext = "SELECT link, title, link_name, link_url FROM ".$wpdb->prefix."links, ".$wpdb->prefix."mixuposts WHERE ".$wpdb->prefix."links.link_visible='Y' AND ".$wpdb->prefix."links.link_id = ".$wpdb->prefix."mixuposts.link_id and ".$wpdb->prefix."links.link_id in (select object_id from ".$wpdb->prefix."term_relationships where term_taxonomy_id = $linkcat) GROUP BY link ORDER BY issued DESC LIMIT ".$postCount;
 
  $sql = $wpdb->get_results($sqltext);


  /* This is the output to the page so change this if you want it displaying different. */
  foreach ($sql AS $site) {
    echo "<li><a href='".$site->link."'".($nofollow ? ' rel="nofollow"' : '').">". abbreviate_mixu($site->title, 28) ."</a> ";
    if ($siteLinked) {
      echo "(<a href='".$site->link_url."'".($nofollow ? ' rel="nofollow"' : '').">". abbreviate_mixu($site->link_name, 18) . "</a>)</li>\n";
    } else {
      echo '(' . abbreviate_mixu($site->link_name, 18) . ")</li>\n";
    }
  }
}

function abbreviate_mixu($text, $max) {
  if ($mixu_abbr) {
    if (strlen($text) <= $max)
      return $text;
    return substr($text, 0, $max-3) . '...';
  } else {
    return $text;
  }
}

// creates widget
function wp_widget_mixu($args) {
  extract($args);
  $options = get_option('widget_mixu');
  $title = $options['title'];
  $linkcatname = $options['linkcatname'];

  if (empty($title))
    $title = 'Mixed Updates';

  if (empty($linkcatname))
    $linkcatname = 'Blogroll';

  echo $before_widget;

  $title ? print($before_title . $title . $after_title) : null;

  if (function_exists('mixed_updates'))
    mixed_updates();

  echo $after_widget;
}

function wp_widget_mixu_control() {
  $options = $newoptions = get_option('widget_mixu');
  if ($_POST["mixu_submit"]) {
    $newoptions['title'] = strip_tags(stripslashes($_POST["mixu_title"]));
    $newoptions['linkcatname'] = strip_tags(stripslashes($_POST["mixu_linkcatname"]));
  }
  if ($options != $newoptions) {
    $options = $newoptions;
    update_option('widget_mixu', $options);
  }
  $title = attribute_escape($options['title']);
  $linkcatname = attribute_escape($options['linkcatname']);
  ?>
<p>
  <label for="mixu_title"><?php _e('Title:'); ?>
    <input id="mixu_title" name="mixu_title" type="text" size="40" value="<?php echo $title; ?>" />
  </label>
</p>
<p>
  <label for="mixu_linkcatname"><?php _e('Link category name:'); ?>
    <input id="mixu_linkcatname" name="mixu_linkcatname" type="text" size="40" value="<?php echo $linkcatname; ?>" />
  </label>
</p>
<input type="hidden" id="mixu_submit" name="mixu_submit" value="1" />
  <?php
}

function wp_widget_mixu_register() {
  wp_register_sidebar_widget('mixu', __('Mixed Updates'), 'wp_widget_mixu', 'widget_mixu');
  wp_register_widget_control('mixu', __('Mixed Updates'), 'wp_widget_mixu_control', 100, 300);
}

add_action('plugins_loaded','wp_widget_mixu_register');

?>
