<?php
/*
Plugin Name: Latest Apple Movie Trailers
Plugin URI: http://www.sebs-studio.com/wp-plugins/latest-apple-movie-trailers/
Description: Displays the latest movie trailers from http://trailers.apple.com/ along with the movie poster and synopsis.
Author URI: http://www.sebs-studio.com/
Author: Sebs Studio (Sebastien)
Version: 1.3
License: GPL2
*/

/*  Copyright (C) 2012  Sebastien (email : sebastien@sebs-studio.com)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Plugin Name.
define('sebsstudio_lamt_plugin_name', 'Latest Apple Movie Trailers');

// Plugin Version.
define('sebsstudio_lamt_plugin_version', '1.3');

$plugin = plugin_basename(__FILE__);

function display_apple_movie_trailers(){
	require_once(ABSPATH.WPINC.'/rss.php');
	$rss = fetch_rss("http://trailers.apple.com/trailers/home/rss/newtrailers.rss"); /* Fetches the apple movie trailer feed. */
	$content = '<div class="trailers">';
	if(!empty($rss)){
		/* Displays the amount of movie trailers added recently. 10 is Default! */
		$items = array_slice($rss->items, 0, get_option('lamt_display_many'));
		foreach($items as $item){
			$content .= '<div class="trailer ';
			// Poster Size
			if(get_option('lamt_poster_size') == 'poster.jpg'){ $content .= 'normal'; }
			if(get_option('lamt_poster_size') == 'poster-large.jpg'){ $content .= 'large'; }
			if(get_option('lamt_poster_size') == 'poster-xlarge.jpg'){ $content .= 'extra'; }
			// Caption Postition
			if(get_option('lamt_caption_position') == 'next'){ $content .= ' caption'; }
			$content .= '">';
			/* Displays Movie Poster. */
			$content .= '<div class="poster">';
			$video_link = clean_url($item['link'], $protocolls = null, 'display');
			$content .= '<a target="_blank" href="'.clean_url($item['link'], $protocolls=null, 'display').'" title="'.htmlentities($item['title']).'"><img src="'.$video_link.'images/'.get_option('lamt_poster_size').'" border="0" alt="'.htmlentities($item['title']).'" /></a>';
			$content .= "</div>\n";
			/* Description of the Movie. */
			$content .= '<div class="description"><h3><a target="_blank" href="'.clean_url($item['link'], $protocolls=null, 'display').'" title="'.htmlentities($item['title']).'">'.htmlentities($item['title']).'</a></h3>';
			/* Date of movie trailer added. */
			$content .= '<p><b>Added:</b> <em>'.date('M j, Y', strtotime($item['pubdate'])).'</em></p>'; 
			$content .= '<p><b>Synopsis:</b> '.$item['description'].'</p>';
			$content .= '<p>Share: <a href="http://www.facebook.com/share.php?u='.clean_url($item['link'], $protocolls=null, 'display').'" target="_blank" title="Share this on Facebook">Facebook</a>&nbsp;<a href="http://twitter.com/home?status=See the trailer for &quot;'.htmlentities($item['title']).'&quot; '.clean_url($item['link'], $protocolls=null, 'display').'" target="_blank" title="Share this on Twitter">Twitter</a></p>';
			$content .= "</div></div>\n";
		}
	}
	else{
		$content .= "<p>Apple Movie Trailers Feed not found!</p>\n";
		$content .= "<p>Please try again later<p>\n";
	}
	$content .= '</div>';
	/* Displays the Apple Movie Trailers Feed. */
	return $content;
}
/* Add [apple_trailers] to your post or page to display latest movie trailers. */
if(function_exists('display_apple_movie_trailers')){
	/* Only works if plugin is active. */
	add_shortcode('apple_trailers', 'display_apple_movie_trailers');
}

/* Runs when plugin is activated */
register_activation_hook(__FILE__, 'apple_latest_trailers_install'); 

/* Runs on plugin deactivation */
register_deactivation_hook( __FILE__, 'apple_latest_trailers_remove');

/** 
 * Send an API ping when the plugin is activated.
 */
function activate_sebsstudio_lamt_plugin(){
	$api = "http://api.sebs-studio.com/?plugin=1&name=".urlencode(sebsstudio_lamt_plugin_name)."&version=".urlencode(sebsstudio_lamt_plugin_version)."&site=".urlencode(home_url());
	wp_remote_get($api);
}

/** 
 * Send an API ping when the plugin is disactivated.
 */
function deactivate_sebsstudio_lamt_plugin(){
	$api = "http://api.sebs-studio.com/?plugin=0&name=".urlencode(sebsstudio_lamt_plugin_name)."&site=".urlencode(home_url());
	wp_remote_get($api);
}

function apple_latest_trailers_install(){
	/* Creates new database field */
	add_option("lamt_display_many", "10", "", "yes");
	add_option("lamt_poster_size", "poster.jpg", "", "yes");
	add_option("lamt_caption_position", "next", "", "yes");
	//activate_sebsstudio_lamt_plugin();
}

function apple_latest_trailers_remove(){
	/* Deletes the database field */
	delete_option('lamt_display_many');
	delete_option('lamt_poster_size');
	delete_option('lamt_caption_position');
	//deactivate_sebsstudio_lamt_plugin();
}

add_action('wp_enqueue_scripts', 'latest_apple_movie_trailers_stylesheet');
function latest_apple_movie_trailers_stylesheet(){
	wp_register_style('latest-apple-movie-trailers-style', plugins_url('latest-apple-movie-trailers.css', __FILE__));
	wp_enqueue_style('latest-apple-movie-trailers-style');
}

/* Adds an action link for the plugin on the Plugins page. */
if(is_admin()){
	function lamt_plugin_action_links($links, $file){
		static $this_plugin;
		if(!$this_plugin){
			$this_plugin = plugin_basename(__FILE__);
		}
		// Check to make sure we are on the correct plugin.
		if($file == $this_plugin){
			// The anchor tag and href to the url of the settings page.
			$settings_link = '<a href="options-general.php?page=latest-apple-movie-trailers/latest_apple_movie_trailers.php">Settings</a>';
			$settings_link .= ' | ';
			$settings_link .= '<a href="http://www.sebs-studio.com/wp-plugins/" target="_blank">Other Plugins</a>';
			// add the link to the list
			array_unshift($links, $settings_link);
		}
		return $links;
	}
	add_filter("plugin_action_links_".$plugin, 'lamt_plugin_action_links', 10, 2);
}

/* Adds settings page link under 'Settings'. */
if(is_admin()){
	function apple_latest_trailers_menu(){
		add_options_page('Latest Apple Movie Trailers', 'Latest Apple Movie Trailers', 'manage_options', __FILE__, 'apple_latest_trailers_settings');
	}
	add_action('admin_menu', 'apple_latest_trailers_menu');
}

function apple_latest_trailers_settings(){
	$display_many = get_option('lamt_display_many');
	$poster_size = get_option('lamt_poster_size');
	$caption_position = get_option('lamt_caption_position');
?>
<style type="text/css">
.trailers{ width:100%; }
.trailer{ min-width:134; float:left; display:block; margin-bottom:10px; }
.trailer.caption{ float:left; display:block; }
.trailer .poster{ float:left; margin-right:10px; margin-bottom:6px; }
.trailer .poster img{ border:none; }
.trailer.normal .poster{ width:134px; }
.trailer.large .poster{ width:261px; }
.trailer.extra .poster{ width:540px; }
.trailer .description{ float:left; width:100%; clear:left; display:block; margin-top:0px; }
.trailer .description h3{ margin:6px 0px; }
.trailer .description p{ margin:4px 0px; }
.trailer .description em{ color:rgb(209, 13, 60); }
.trailer.caption .description{ clear:right; margin-top:0px; }
.trailer.caption .description h3{ margin:0px 0px 6px 0px; }
.trailer.normal.caption .description{ width:70%; }
.trailer.large.caption .description{ width:60%; }
.trailer.extra.caption .description{ width:30%; }

/* Support */
div.support{ background-color:#eee; padding:10px; display:block; position:absolute; top:50px; right:14px; width:240px; }
.support ul li a.share{ background-position:left center; background-repeat:no-repeat; padding-left:42px; height:40px; display:block; line-height:38px; }
.support ul li a.share.facebook{ background-image:url('<?php echo plugins_url('support/facebook.png', __FILE__); ?>'); }
.support ul li a.share.twitter{ background-image:url('<?php echo plugins_url('support/twitter.png', __FILE__); ?>'); }
.support ul li a.share.dropbox{ background-image:url('<?php echo plugins_url('support/dropbox.png', __FILE__); ?>'); }
.support ul li a.share.github{ background-image:url('<?php echo plugins_url('support/github.png', __FILE__); ?>'); }
</style>
<div class="wrap">
	<h2>Latest Apple Movie Trailers</h2>
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">How many trailers to display ?</th>
			<td>
			<select name="lamt_display_many" size="1">
			<?php
			for($show=1; $show<=20; $show++){
				echo '<option value="'.$show.'"';
				if($display_many == $show){ echo ' selected="selected"'; }
				echo '>'.$show.'</option>';
			}
			?>
			</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Poster Size</th>
			<td>
			<select name="lamt_poster_size" size="1">
			<option value="poster.jpg"<?php if($poster_size == 'poster.jpg'){ echo ' selected="selected"'; } ?>>Normal</option>
			<option value="poster-large.jpg"<?php if($poster_size == 'poster-large.jpg'){ echo ' selected="selected"'; } ?>>Large</option>
			<option value="poster-xlarge.jpg"<?php if($poster_size == 'poster-xlarge.jpg'){ echo ' selected="selected"'; } ?>>Extra Large</option>
			</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Description Position</th>
			<td>
			<select name="lamt_caption_position" size="1">
			<option value="next"<?php if($caption_position == "next"){ echo ' selected="selected"'; } ?>>Next to Poster</option>
			<option value="under"<?php if($caption_position == "under"){ echo ' selected="selected"'; } ?>>Underneith the Poster</option>
			</select>
			</td>
		</tr>
		<tr>
			<th scope="row">Deactivation</th>
			<td><p style="color:red;">If you deactivate the plugin, the options above will be deleted.</p></td>
		</tr>
	</table>
	<input type="hidden" name="action" value="update">
	<input type="hidden" name="page_options" value="lamt_display_many, lamt_poster_size, lamt_caption_position">
	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	</form>

	<h3>Preview</h3>
	<?php echo display_apple_movie_trailers(); ?>
</div>
<div class="support">
<a href="http://www.sebs-studio.com" target="_blank"><img src="<?php echo plugins_url('support/sebs-studio.png', __FILE__); ?>" width="240" height="50" alt="Sebs Studio" /></a>
<p>Plugin Version: <?php echo sebsstudio_lamt_plugin_version; ?>
<p><b>Show your Support</b></p>
<ul>
	<li><a href="http://wordpress.org/extend/plugins/latest-apple-movie-trailers/" target="_blank">Rate the plugin 5 star on WordPress.org</a></li>
	<li><a href="http://www.sebs-studio.com/wp-plugins/latest-apple-movie-trailers/#utm_source=wpadmin&utm_medium=sidebanner&utm_term=link&utm_campaign=wp_lamt_plugin" target="_blank">Blog about it & link to the plugin page</a></li>
	<li><a href="http://www.amazon.co.uk/registry/wishlist/1M6UT1XVTL88X" target="_blank">Buy me something from my wishlist</a></li>
	<li><a class="share facebook" href="https://www.facebook.com/sebsstudio" target="_blank">Like Sebs Studio on Facebook</a></li>
	<li><a class="share twitter" href="https://twitter.com/sebsstudio" target="_blank">Follow Sebs Studio on Twitter</a></li>
	<li><a class="share dropbox" href="http://db.tt/kgspZ0k" target="_blank">Sign Up to Dropbox</a></li>
	<li><a class="share github" href="https://github.com/seb86" target="_blank">Fork Me on Github</a></li>
</ul>
</div>
<?php
} // end of plugin.
?>