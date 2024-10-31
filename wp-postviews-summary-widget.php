<?php
/*
Plugin Name: Post Views Summary
Plugin URI: http://www.hilbring.de/2009/09/04/wp-postviews-dashboard/
Description: A dashboard plugin that shows the most and least visited posts and pages.
Author: Peter Hilbring
Version: 1.1.3
Author URI: http://www.hilbring.de/
License: GPL v3 - http://www.gnu.org/licenses/gpl.html

Requires WordPress 2.8 or later.

Recent changes:
1.1.3 - Translated into Belorussian by FatCow
1.1.2 - Translated into Spanisch by Juan Llamosas
1.1.1 - Russian translation updated by Flector
1.1 - Config menu added
1.0.1 - Translated into Brazilian by Rafael Dering
      - Translated into Russion by Flector
	  - some code cleanups
1.0 - Initial release


	Copyright 2009  Peter Hilbring  (email : peter@hilbring.de)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Creates a summary dashboard-widget for wp-postviews
 *
 * @author Peter Hilbring <peter@hilbring.de>
 **/
class PostViewsSummary
{
	protected $options = array();
	
	/**
	 * Start the process of including the widget
	 **/
	function PostViewsSummary()
	{
		if ( !$this->options = get_option( 'PostViewsSummary' ) ) {
			$this->options['show'] = 'both';
			$this->options['number'] = 10;
			$this->options['sorting'] = 'most';
			$this->options['limited'] = 'any';
			$this->options['tag'] = -1;
			$this->options['category'] = -1;
			add_option('PostViewsSummary', $this->options);
		}
		add_action('wp_dashboard_setup', array($this, 'addDashboardWidget'));
	}

	/**
	 * Add the widget to the dashboard
	 **/
	function addDashboardWidget()
	{
		wp_add_dashboard_widget('post-views-summary', __('Post Views Summary', 'post-views-summary'), array($this, 'widget'), array($this, 'widget_control'));
	}

	/**
	 * The widget display
	 **/
	function widget()
	{
		$no_wp_post_views = false;
		
		switch ($this->options['show']) {
			case 'both':
				$show = __('posts/pages', 'post-views-summary');
				break;
			case 'post':
				$show = __('posts', 'post-views-summary');
				break;
			case 'page':
				$show = __('pages', 'post-views-summary');
				break;
		}
		switch ($this->options['sorting']) {
			case 'most':
				$sorting = __('most', 'post-views-summary');
				break;
			case 'least':
				$sorting = __('least', 'post-views-summary');
				break;
		}
		switch ($this->options['limited']) {
			case 'any':
				$limited = '';
				break;
			case 'tag':
				$limited = sprintf(__('<br /><small>Limit to tag: %s</small>', 'post-views-summary'), get_tag($this->options['tag'])->name);
				break;
			case 'category':
				$limited = sprintf(__('<br /><small>Limit to category: %s</small>', 'post-views-summary'), get_cat_name($this->options['category']));
				break;
		}
		echo ('<p>');
		printf(__('<b>%d %s viewed %s</b>%s', 'post-views-summary'), $this->options['number'], $sorting, $show, $limited);
		echo ('</p><div style="background: #F9F9F9"><ul>');
		switch ($this->options['limited']) {
			case 'any':
				switch ($this->options['sorting']) {
					case 'most':
						if (function_exists('get_most_viewed'))
							get_most_viewed($this->options['show'], $this->options['number']);
						else
							$no_wp_post_views = true;
						break;
					case 'least':
						if (function_exists('get_least_viewed'))
							get_least_viewed($this->options['show'], $this->options['number']);
						else
							$no_wp_post_views = true;
						break;
				}
				break;
			case 'tag':
				switch ($this->options['sorting']) {
					case 'most':
						if (function_exists('get_most_viewed_tag'))
							get_most_viewed_tag($this->options['tag'], $this->options['show'], $this->options['number']);
						else
							$no_wp_post_views = true;
						break;
					case 'least':
						if (function_exists('get_least_viewed_tag'))
							get_least_viewed_tag($this->options['tag'], $this->options['show'], $this->options['number']);
						else
							$no_wp_post_views = true;
						break;
				}
				break;
			case 'category':
				switch ($this->options['sorting']) {
					case 'most':
						if (function_exists('get_most_viewed_category'))
							get_most_viewed_category($this->options['category'], $this->options['show'], $this->options['number']);
						else
							$no_wp_post_views = true;
						break;
					case 'least':
						if (function_exists('get_least_viewed'))
							get_least_viewed_category($this->options['category'], $this->options['show'], $this->options['number']);
						else
							$no_wp_post_views = true;
						break;
				}
				break;
		}
		if ($no_wp_post_views == true)
			_e('<b>Install WP-PostViews plugin</b>', 'post-views-summary');
		echo ('</ul><br />');
		printf ('<a href="%s/?v_sortby=views&v_orderby=%s" target="_blank">%s</a></div>', get_option('siteurl'), ( $this->options['sorting'] == 'most' ) ? 'desc' : 'asc', __('Show posts summary', 'post-views-summary') );
	}
	
	/**
	 * The widget control display
	 **/
	function widget_control()
	{
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['PostViewsSummary']) ) {
			$this->options['show'] = $_POST['PostViewsSummary']['show'];
			$this->options['number'] = $_POST['PostViewsSummary']['number'];
			$this->options['sorting'] = $_POST['PostViewsSummary']['sorting'];
			$this->options['limited'] = $_POST['PostViewsSummary']['limited'];
			$this->options['tag'] = $_POST['PostViewsSummary']['tag'];
			$this->options['category'] = $_POST['PostViewsSummary']['category'];
			update_option('PostViewsSummary', $this->options);
		}
?>
		<p>
			<label for="PostViewsSummary[show]"><?php _e('Show ', 'post-views-summary') ?></label>
			<select name="PostViewsSummary[show]" id="PostViewsSummary_show">
				<option value="post" <?php if ( $this->options['show'] == 'post' ) echo ' selected="selected"'; ?>><?php _e('posts', 'post-views-summary') ?></option>
				<option value="page" <?php if ( $this->options['show'] == 'page' ) echo ' selected="selected"'; ?>><?php _e('pages', 'post-views-summary') ?></option>
				<option value="both" <?php if ( $this->options['show'] == 'both' ) echo ' selected="selected"'; ?>><?php _e('posts/pages', 'post-views-summary') ?></option>
			</select>
		</p>
		<p>
			<label for="PostViewsSummary[number]"><?php _e('Up to ', 'post-views-summary') ?></label>
			<select name="PostViewsSummary[number]" id="PostViewsSummary_number">
				<option value="1" <?php if ( $this->options['number'] == 1 ) echo ' selected="selected"'; ?>>1</option>
				<option value="5" <?php if ( $this->options['number'] == 5 ) echo ' selected="selected"'; ?>>5</option>
				<option value="10" <?php if ( $this->options['number'] == 10 ) echo ' selected="selected"'; ?>>10</option>
				<option value="15" <?php if ( $this->options['number'] == 15 ) echo ' selected="selected"'; ?>>15</option>
				<option value="20" <?php if ( $this->options['number'] == 20 ) echo ' selected="selected"'; ?>>20</option>
			</select>
		</p>
		<p>
			<label for="PostViewsSummary[sorting]"><?php _e('Sorting ', 'post-views-summary') ?></label>
			<select name="PostViewsSummary[sorting]" id="PostViewsSummary_sorting">
				<option value="most" <?php if ( $this->options['sorting'] == 'most' ) echo ' selected="selected"'; ?>><?php _e('most viewed', 'post-views-summary') ?></option>
				<option value="least" <?php if ( $this->options['sorting'] == 'least' ) echo ' selected="selected"'; ?>><?php _e('least viewed', 'post-views-summary') ?></option>
			</select>
		</p>
		<p>
			<label for="PostViewsSummary[limited]"><?php _e('Limited to', 'post-views-summary') ?><br />
				<label><input name="PostViewsSummary[limited]" type="radio" id="PostViewsSummary_limited_0" value="any" <?php if ( $this->options['limited'] == 'any' ) echo ' checked="checked"'; ?> /><?php _e(' not limited', 'post-views-summary') ?></label>
				<br />
				<label>
					<input type="radio" name="PostViewsSummary[limited]" value="tag" id="PostViewsSummary_limited_1" <?php if ( $this->options['limited'] == 'tag' ) echo ' checked="checked"'; ?> />
					<label for="PostViewsSummary[tag]"><?php _e('tag ', 'post-views-summary') ?></label>
					<select name="PostViewsSummary[tag]" id="PostViewsSummary_tag">
					<?php
						foreach(get_terms('post_tag') as $val) {
							echo '<option value="'.$val->term_id.'"';
							if ( $this->options['tag'] == $val->term_id )
								echo ' selected="selected"';
							echo '>'.$val->name.'</option>';
						}
					?>
					</select>
				</label>
				<br />
				<label>
					<input type="radio" name="PostViewsSummary[limited]" value="category" id="PostViewsSummary_limited_2" <?php if ( $this->options['limited'] == 'category' ) echo ' checked="checked"'; ?> />
					<label for="PostViewsSummary[category]"><?php _e('category ', 'post-views-summary') ?></label>
					<select name="PostViewsSummary[category]" id="PostViewsSummary_category">
					<?php
						foreach(get_categories('orderby=name') as $val) {
							echo '<option value="'.$val->term_id.'"';
							if ( $this->options['category'] == $val->term_id )
								echo ' selected="selected"';
							echo '>'.$val->name.'</option>';
						}
					?>
					</select>
				</label>
				<br />
			</label>
		</p>
<?php
	}
} // END class

# Load the localization information
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain('post-views-summary', 'wp-content/plugins/' . $plugin_dir . '/lang', $plugin_dir . '/lang');

# Check if we have a version of WordPress greater than 2.8
if ( function_exists('register_widget') ) $PostViewsSummary = new PostViewsSummary();

?>
