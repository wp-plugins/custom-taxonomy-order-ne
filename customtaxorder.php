<?php
/*
Plugin Name: Custom Taxonomy Order NE
Plugin URI: http://products.zenoweb.nl/free-wordpress-plugins/custom-taxonomy-order-ne/
Description: Allows for the ordering of categories and custom taxonomy terms through a simple drag-and-drop interface.
Version: 2.6.3
Author: Marcel Pol
Author URI: http://zenoweb.nl/
License: GPLv2 or later
Text Domain: customtaxorder
Domain Path: /lang/

Copyright 2013-2015   Marcel Pol   (email: marcel@timelord.nl)

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


function customtaxorder_register_settings() {
	register_setting('customtaxorder_settings', 'customtaxorder_settings', 'customtaxorder_settings_validate');
}
add_action('admin_init', 'customtaxorder_register_settings');


function customtaxorder_update_settings() {
	global $customtaxorder_settings, $customtaxorder_defaults;
	if ( isset($customtaxorder_settings['update']) ) {
		echo '<div class="updated fade" id="message"><p>' . __('Custom Taxonomy Order NE settings', 'customtaxorder') . ' ' . $customtaxorder_settings['update'] . '</p></div>';
		unset($customtaxorder_settings['update']);
		update_option('customtaxorder_settings', $customtaxorder_settings);
	}
}


function customtaxorder_settings_validate($input) {
	$args = array( 'public' => true );
	$output = 'objects';
	$taxonomies = get_taxonomies( $args, $output );
	foreach ( $taxonomies as $taxonomy ) {
		if ( $input[$taxonomy->name] != 1 ) {
			if ( $input[$taxonomy->name] != 2 ) {
				$input[$taxonomy->name] = 0; //default
			}
		}
	}
	return $input;
}


function customtaxorder_menu() {
	$args = array( 'public' => true );
	$output = 'objects';
	$taxonomies = get_taxonomies($args, $output);

	// Also make the link_category available if activated.
	$linkplugin = "link-manager/link-manager.php";
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active($linkplugin) ) {
		$args = array( 'name' => 'link_category' );
		$taxonomies2 = get_taxonomies( $args, $output );
		$taxonomies = array_merge($taxonomies, $taxonomies2);
	}

	add_menu_page(__('Term Order', 'customtaxorder'), __('Term Order', 'customtaxorder'), 'manage_categories', 'customtaxorder', 'customtaxorder', 'dashicons-images-alt', 122.35);
	foreach ($taxonomies as $taxonomy ) {
		add_submenu_page('customtaxorder', __('Order ', 'customtaxorder') . $taxonomy->label, __('Order ', 'customtaxorder') . $taxonomy->label, 'manage_categories', 'customtaxorder-'.$taxonomy->name, 'customtaxorder');
	}
}


function customtaxorder_css() {
	if ( isset($_GET['page']) ) {
		$pos_page = $_GET['page'];
		$pos_args = 'customtaxorder';
		$pos = strpos($pos_page,$pos_args);
		if ( $pos === false ) {} else {
			wp_enqueue_style('customtaxorder', plugins_url('css/customtaxorder.css', __FILE__), 'screen');
		}
	}
}


function customtaxorder_js_libs() {
	if ( isset($_GET['page']) ) {
		$pos_page = $_GET['page'];
		$pos_args = 'customtaxorder';
		$pos = strpos($pos_page,$pos_args);
		if ( $pos === false ) {} else {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
		}
	}
}
add_action('admin_menu', 'customtaxorder_menu');
add_action('admin_print_styles', 'customtaxorder_css');
add_action('admin_print_scripts', 'customtaxorder_js_libs');


/*
 * customtax_cmp
 * Sorting of an array with objects, ordered by term_order
 * Sorting the query with get_terms() doesn't allow sorting with term_order
 */

function customtax_cmp( $a, $b ) {
	if ( $a->term_order ==  $b->term_order ) {
		return 0;
	} else if ( $a->term_order < $b->term_order ) {
		return -1;
	} else {
		return 1;
	}
}


/*
 * customtaxorder_update_order
 * Function to update the database with the submitted order
 */

function customtaxorder_update_order() {
	if (isset($_POST['hidden-custom-order']) && $_POST['hidden-custom-order'] != "") {
		global $wpdb;
		$new_order = $_POST['hidden-custom-order'];
		$IDs = explode(",", $new_order);
		$ids = Array();
		$result = count($IDs);
		for($i = 0; $i < $result; $i++) {
			$id = (int) str_replace("id_", "", $IDs[$i]);
			$wpdb->query( $wpdb->prepare(
				"
					UPDATE $wpdb->terms SET term_order = '%d' WHERE term_id ='%d'
				",
				$i,
				$id
			) );
			$wpdb->query( $wpdb->prepare(
				"
					UPDATE $wpdb->term_relationships SET term_order = '%d' WHERE term_taxonomy_id ='%d'
				",
				$i,
				$id
			) );
			$ids[] = $id;
		}
		echo '<div id="message" class="updated fade"><p>'. __('Order updated successfully.', 'customtaxorder').'</p></div>';
		do_action('customtaxorder_update_order', $ids);
	} else {
		echo '<div id="message" class="error fade"><p>'. __('An error occured, order has not been saved.', 'customtaxorder').'</p></div>';
	}
}


/*
 * customtaxorder_sub_query
 * Function to give an option for the list of sub-taxonomies
 */

function customtaxorder_sub_query( $terms, $tax ) {
	$options = '';
	foreach ( $terms as $term ) :
		$subterms = get_term_children( $term->term_id, $tax );
		if ( $subterms ) {
			$options .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';
		}
	endforeach;
	return $options;
}


/*
 * customtaxorder_apply_order_filter
 * Function to sort the standard WordPress Queries.
 */

function customtaxorder_apply_order_filter($orderby, $args) {
	global $customtaxorder_settings;
	$options = $customtaxorder_settings;
	if ( isset( $args['taxonomy'] ) ) {
		$taxonomy = $args['taxonomy'];
	} else {
		$taxonomy = 'category';
	}
	if ( !isset ( $options[$taxonomy] ) ) {
		$options[$taxonomy] = 0; // default if not set in options yet
	}
	if ( $args['orderby'] == 'term_order' ) {
		return 't.term_order';
	} elseif ( $args['orderby'] == 'name' ) {
		return 't.name';
	} elseif ( $options[$taxonomy] == 1 && !isset($_GET['orderby']) ) {
		return 't.term_order';
	} elseif ( $options[$taxonomy] == 2 && !isset($_GET['orderby']) ) {
		return 't.name';
	} else {
		return $orderby;
	}
}
add_filter('get_terms_orderby', 'customtaxorder_apply_order_filter', 10, 2);


/*
 * customtaxorder_wp_get_object_terms_order_filter
 * wp_get_object_terms is used to sort in wp_get_object_terms and wp_get_post_terms functions.
 * get_terms is used in wp_list_categories and get_terms functions.
 * get_the_terms is used in the the_tags function.
 * tag_cloud_sort is used in the wp_tag_cloud and wp_generate_tag_cloud functions (but then the get_terms filter here does nothing).
 * Default sorting is by name (according to the codex).
 *
 */

function customtaxorder_wp_get_object_terms_order_filter( $terms ) {
	global $customtaxorder_settings;
	$options = $customtaxorder_settings;

	if ( empty($terms) || !is_array($terms) ) {
		return $terms; // only work with an array of terms
	}
	foreach ($terms as $term) {
		if ( is_object($term) && isset( $term->taxonomy ) ) {
			$taxonomy = $term->taxonomy;
		} else {
			return $terms; // not an array with objects
		}
		break; // just the first one :)
	}

	if ( !isset ( $options[$taxonomy] ) ) {
		$options[$taxonomy] = 0; // default if not set in options yet
	}
	if ( $options[$taxonomy] == 1 && !isset($_GET['orderby']) ) {
		if (current_filter() == 'get_terms' ) {
			if ( $taxonomy == 'post_tag' || $taxonomy == 'product_tag' ) {
				// no filtering so the test in wp_generate_tag_cloud() works out right for us
				// filtering will happen in the tag_cloud_sort filter sometime later
				// post_tag = default tags
				// product_tag = woocommerce product tags
				return $terms;
			}
		}
		usort($terms, 'customtax_cmp');
		return $terms;
	}
	return $terms;
}
add_filter( 'wp_get_object_terms', 'customtaxorder_wp_get_object_terms_order_filter', 10, 3 );
add_filter( 'get_terms', 'customtaxorder_wp_get_object_terms_order_filter', 10, 3 );
add_filter( 'get_the_terms', 'customtaxorder_wp_get_object_terms_order_filter', 10, 3 );
add_filter( 'tag_cloud_sort', 'customtaxorder_wp_get_object_terms_order_filter', 10, 3 );


/*
 * customtaxorder_order_categories
 * Filter to sort the categories according to term_order
 *
 */

function customtaxorder_order_categories($categories) {
	global $customtaxorder_settings;
	$options = $customtaxorder_settings;
	if ( !isset ( $options['category'] ) ) {
		$options['category'] = 0; // default if not set in options yet
	}
	if ( $options['category'] == 1 && !isset($_GET['orderby']) ) {
		usort($categories, 'customtax_cmp');
		return $categories;
	}
	return $categories;
}
add_filter('get_the_categories', 'customtaxorder_order_categories', 10, 3);


/*
 * customtaxorder_links
 * Add Settings link to the main plugin page
 *
 */

function customtaxorder_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/customtaxorder.php' ) ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=customtaxorder' ) . '">'.__( 'Settings' ).'</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'customtaxorder_links', 10, 2 );


/*
 * customtaxorder_initialize
 * Function called at initialisation.
 * - Loads language files
 * - set defaults
 * - get settings
 */

function customtaxorder_initialize() {
	global $customtaxorder_settings, $customtaxorder_defaults;

	load_plugin_textdomain('customtaxorder', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');

	$customtaxorder_defaults = array('category' => 0);
	$args = array( 'public' => true, '_builtin' => false );
	$output = 'objects';
	$taxonomies = get_taxonomies( $args, $output );
	foreach ( $taxonomies as $taxonomy ) {
		$customtaxorder_defaults[$taxonomy->name] = 0;
	}
	$customtaxorder_defaults = apply_filters('customtaxorder_defaults', $customtaxorder_defaults);
	$customtaxorder_settings = get_option('customtaxorder_settings');
	$customtaxorder_settings = wp_parse_args($customtaxorder_settings, $customtaxorder_defaults);
}
add_action('plugins_loaded', 'customtaxorder_initialize');


/*
 * customtaxorder_activate
 * Function called at activation time.
 */

function _customtaxorder_activate() {
	global $wpdb;
	$init_query = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'");
	if ($init_query == 0) { $wpdb->query("ALTER TABLE $wpdb->terms ADD term_order INT( 4 ) NULL DEFAULT '0'"); }
}


function customtaxorder_activate($networkwide) {
	global $wpdb;
	if (function_exists('is_multisite') && is_multisite()) {
		$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach ($blogids as $blog_id) {
			switch_to_blog($blog_id);
			_customtaxorder_activate();
			restore_current_blog();
		}
	} else {
		_customtaxorder_activate();
	}
}
register_activation_hook( __FILE__, 'customtaxorder_activate' );


function customtaxorder_activate_new_site($blog_id) {
	switch_to_blog($blog_id);
	_customtaxorder_activate();
	restore_current_blog();
}
add_action( 'wpmu_new_blog', 'customtaxorder_activate_new_site' );

// include Settingspage
include('page-customtaxorder.php');


