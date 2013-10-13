<?php
/*
Plugin Name: Custom Taxonomy Order NE
Plugin URI: http://timelord.nl/wordpress/product/custom-taxonomy-order-ne?lang=en
Description: Allows for the ordering of categories and custom taxonomy terms through a simple drag-and-drop interface.
Version: 2.1
Author: Marcel Pol
Author URI: http://timelord.nl/
License: GPLv2 or later
Text Domain: customtaxorder
Domain Path: /lang/
*/

/*
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
		echo '<div class="updated fade" id="message"><p>Custom Taxonomy Order NE settings '.$customtaxorder_settings['update'].'.</p></div>';
		unset($customtaxorder_settings['update']);
		update_option('customtaxorder_settings', $customtaxorder_settings);
	}
}
function customtaxorder_settings_validate($input) {
	$input['category'] = ($input['category'] == 1 ? 1 : 0);
	$args = array( 'public' => true, '_builtin' => false );
	$output = 'objects';
	$taxonomies = get_taxonomies( $args, $output );
	foreach ( $taxonomies as $taxonomy ) {
		$input[$taxonomy->name] = ($input[$taxonomy->name] == 1 ? 1 : 0);
	}
	return $input;
}

function customtaxorder_menu() {
	$args = array( 'public' => true, '_builtin' => false );
	$output = 'objects';
	$taxonomies = get_taxonomies($args, $output);
	add_menu_page(__('Term Order', 'customtaxorder'), __('Term Order', 'customtaxorder'), 'manage_categories', 'customtaxorder', 'customtaxorder', plugins_url('images/cat_order.png', __FILE__), 122.35);
	add_submenu_page('customtaxorder', __('Order Categories', 'customtaxorder'), __('Order Categories', 'customtaxorder'), 'manage_categories', 'customtaxorder', 'customtaxorder');
	foreach ($taxonomies as $taxonomy ) {
		add_submenu_page('customtaxorder', __('Order ', 'customtaxorder') . $taxonomy->label, __('Order ', 'customtaxorder') . $taxonomy->label, 'manage_categories', 'customtaxorder-'.$taxonomy->name, 'customtaxorder');
	}
}
function customtaxorder_css() {
	$pos_page = $_GET['page'];
	$pos_args = 'customtaxorder';
	$pos = strpos($pos_page,$pos_args);
	if ( $pos === false ) {} else {
		wp_enqueue_style('customtaxorder', plugins_url('css/customtaxorder.css', __FILE__), 'screen');
	}
}
function customtaxorder_js_libs() {
	$pos_page = $_GET['page'];
	$pos_args = 'customtaxorder';
	$pos = strpos($pos_page,$pos_args);
	if ( $pos === false ) {} else {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
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

function customtaxorder() {
	global $customtaxorder_settings;
	customtaxorder_update_settings();
	$options = $customtaxorder_settings;
	$settings = '';
	$parent_ID = 0;
	if ( $_GET['page'] == 'customtaxorder' ) {
		$args = array( 'public' => true, '_builtin' => false );
		$output = 'objects';
		$taxonomies = get_taxonomies( $args, $output );
		foreach ( $taxonomies as $taxonomy ) {
			$settings .= '<input name="customtaxorder_settings[' . $taxonomy->name . ']" type="hidden" value="' . $options[$taxonomy->name] . '" />';
		}
		$settings .= '<input name="customtaxorder_settings[category]" type="checkbox" value="1" ' . checked('1', $options['category'], false) . ' /> <label for="customtaxorder_settings[category]">' . __('Check this box if you want to enable Automatic Sorting of all instances from this taxonomy.', 'customtaxorder') . '</label>';
		$tax_label = 'Categories';
		$tax = 'category';
	} else {
		$args = array( 'public' => true, '_builtin' => false );
		$output = 'objects';
		$taxonomies = get_taxonomies( $args, $output );
		foreach ( $taxonomies as $taxonomy ) {
			$com_page = 'customtaxorder-'.$taxonomy->name;
			if ( $_GET['page'] == $com_page ) {
				$settings .= '<input name="customtaxorder_settings[' . $taxonomy->name . ']" type="checkbox" value="1" ' . checked('1', $options[$taxonomy->name], false) . ' /> <label for="customtaxorder_settings[' . $taxonomy->name . ']">' . __('Check this box if you want to enable Automatic Sorting of all instances from this taxonomy.', 'customtaxorder') . '</label>';
				$tax_label = $taxonomy->label;
				$tax = $taxonomy->name;
			} else {
				$settings .= '<input name="customtaxorder_settings[' . $taxonomy->name . ']" type="hidden" value="' . $options[$taxonomy->name] . '" />';
			}
		}
		$settings .= '<input name="customtaxorder_settings[category]" type="hidden" value="' . $options['category'] . '" />';
	}
	if (isset($_POST['go-sub-posts'])) {
		$parent_ID = $_POST['sub-posts'];
	}
	elseif (isset($_POST['hidden-parent-id'])) {
		$parent_ID = $_POST['hidden-parent-id'];
	}
	if (isset($_POST['return-sub-posts'])) {
		$parent_term = get_term($_POST['hidden-parent-id'], $tax);
		$parent_ID = $parent_term->parent;
	}
	$message = "";
	if (isset($_POST['order-submit'])) {
		customtaxorder_update_order();
	}
?>
<div class='wrap'>
	<?php screen_icon('customtaxorder'); ?>
	<h2><?php _e('Order ' . $tax_label, 'customtaxorder'); ?></h2>
	<form name="custom-order-form" method="post" action="">
		<?php
		$args = array(
			'orderby' => 'term_order',
			'order' => 'ASC',
			'hide_empty' => false,
			'parent' => $parent_ID,
		);
		$terms = get_terms( $tax, $args );
			if ( $terms ) {
				usort($terms, 'customtax_cmp');
		?>
		<div id="poststuff" class="metabox-holder">
			<div class="widget order-widget">
				<h3 class="widget-top"><?php _e( $tax_label) ?> | <small><?php _e('Order the taxonomies by dragging and dropping them into the desired order.', 'customtaxorder') ?></small></h3>
				<div class="misc-pub-section">
					<ul id="custom-order-list">
						<?php foreach ( $terms as $term ) : ?>
						<li id="id_<?php echo $term->term_id; ?>" class="lineitem"><?php echo $term->name; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="misc-pub-section misc-pub-section-last">
					<?php if ($parent_ID != 0) { ?>
						<input type="submit" class="button" style="float:left" id="return-sub-posts" name="return-sub-posts" value="<?php _e('Return to Parent', 'customtaxorder'); ?>" />
					<?php } ?>
					<div id="publishing-action">
						<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" id="custom-loading" style="display:none" alt="" />
						<input type="submit" name="order-submit" id="order-submit" class="button-primary" value="<?php _e('Update Order', 'customtaxorder') ?>" />
					</div>
					<div class="clear"></div>
					</div>
				<input type="hidden" id="hidden-custom-order" name="hidden-custom-order" />
				<input type="hidden" id="hidden-parent-id" name="hidden-parent-id" value="<?php echo $parent_ID; ?>" />
			</div>
			<?php $dropdown = customtaxorder_sub_query( $terms, $tax ); if( !empty($dropdown) ) { ?>
			<div class="widget order-widget">
				<h3 class="widget-top"><?php _e('Sub-' . $tax_label, 'customtaxorder'); ?> | <small><?php _e('Choose a term from the drop down to order its sub-terms.', 'customtaxorder'); ?></small></h3>
				<div class="misc-pub-section misc-pub-section-last">
					<select id="sub-posts" name="sub-posts">
						<?php echo $dropdown; ?>
					</select>
					<input type="submit" name="go-sub-posts" class="button" id="go-sub-posts" value="<?php _e('Order Sub-terms', 'customtaxorder') ?>" />
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<p><?php _e('No terms found', 'customtaxorder'); ?></p>
		<?php } ?>
	</form>
	<form method="post" action="options.php">
		<?php settings_fields('customtaxorder_settings'); ?>
		<table class="form-table">
			<tr valign="top"><th scope="row"><?php _e('Auto-Sort Queries', 'customtaxorder') ?></th>
			<td><?php echo $settings; ?></td>
			</tr>
		</table>
		<input type="hidden" name="customtaxorder_settings[update]" value="Updated" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'customtaxorder') ?>" />
		</p>
	</form>
</div>
<?php if ( $terms ) { ?>
<script type="text/javascript">
// <![CDATA[
	jQuery(document).ready(function(jQuery) {
		jQuery("#custom-loading").hide();
		jQuery("#order-submit").click(function() {
			orderSubmit();
		});
	});
	function customtaxorderAddLoadEvent(){
		jQuery("#custom-order-list").sortable({
			placeholder: "sortable-placeholder",
			revert: false,
			tolerance: "pointer"
		});
	};
	addLoadEvent(customtaxorderAddLoadEvent);
	function orderSubmit() {
		var newOrder = jQuery("#custom-order-list").sortable("toArray");
		jQuery("#custom-loading").show();
		jQuery("#hidden-custom-order").val(newOrder);
		return true;
	}
// ]]>
</script>
<?php }
}
function customtaxorder_update_order() {
	if (isset($_POST['hidden-custom-order']) && $_POST['hidden-custom-order'] != "") {
		global $wpdb;
		$new_order = $_POST['hidden-custom-order'];
		$IDs = explode(",", $new_order);
		$result = count($IDs);
		for($i = 0; $i < $result; $i++) {
			$str = str_replace("id_", "", $IDs[$i]);
			$wpdb->query("UPDATE $wpdb->terms SET term_order = '$i' WHERE term_id ='$str'");
		}
		echo '<div id="message" class="updated fade"><p>'. __('Order updated successfully.', 'customtaxorder').'</p></div>';
	} else {
		echo '<div id="message" class="error fade"><p>'. __('An error occured, order has not been saved.', 'customtaxorder').'</p></div>';
	}
}
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
	if ( $args['orderby'] == 'term_order' ) {
		return 't.term_order';
	} elseif ( $options[$taxonomy] == 1 && !isset($_GET['orderby']) ) {
		return 't.term_order';
	} else {
		return $orderby;
	}
}
add_filter('get_terms_orderby', 'customtaxorder_apply_order_filter', 10, 2);

/*
 * customtaxorder_init
 * Function called at initialisation.
 * - Loads language files
 * - set defaults
 * - get settings
 */

function customtaxorder_init() {
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
add_action('plugins_loaded', 'customtaxorder_init');

/*
 * customtaxorder_activate
 * Function called at activation time.
 */

function customtaxorder_activate() {
	global $wpdb;
	$init_query = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'");
	if ($init_query == 0) {	$wpdb->query("ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'"); }
}
register_activation_hook(__FILE__, 'customtaxorder_activate');

//Just for fun here:
function customtaxorder_most_recently_used( $id ) {
	global $wpdb;
	$post_count = 0;
	$post_type = get_post_type( $id );
	$taxonomies = wp_get_object_taxonomies( $post_type );
	$args = array('fields' => 'ids');
	$terms = wp_get_object_terms( $id, $taxonomies, $args );
	$thirtydaysago = date_i18n('U', strtotime('-30 days') );
	foreach( $terms as $term ) :
		$querystr = "SELECT count FROM $wpdb->term_taxonomy, $wpdb->posts, $wpdb->term_relationships WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id AND $wpdb->term_taxonomy.term_id = $term AND $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_date > $thirtydaysago";
		$result = $wpdb->get_var($querystr);
		$post_count = $result;
		$wpdb->query("UPDATE $wpdb->terms SET term_order = '$post_count' WHERE term_id ='$term'");
   endforeach;
}
?>