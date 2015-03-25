<?php
/*
 * Admin Settingspage for Custom Taxonomy Order NE
 */


function customtaxorder() {
	global $customtaxorder_settings, $sitepress;

	if ( function_exists('current_user_can') && !current_user_can('manage_categories') ) {
		die(__('Cheatin&#8217; uh?'));
	}

	customtaxorder_update_settings();
	$options = $customtaxorder_settings;
	$settings = ''; // The input and text for the taxonomy that's shown
	$parent_ID = 0;

	// Remove filter for WPML
	remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
	remove_filter( 'get_terms', array( $sitepress, 'get_terms_filter' ) );

	if ( $_GET['page'] == 'customtaxorder' ) {
		?>
		<h2>Custom Taxonomy Order NE</h2>
		<div class="order-widget">
			<p><?php _e('The ordering of categories and custom taxonomy terms through a simple drag-and-drop interface.', 'customtaxorder'); ?></p>
			<p><a href="http://products.zenoweb.nl/free-wordpress-plugins/custom-taxonomy-order-ne/" target="blank">
				<?php _e('Go to the plugin\'s Homepage','customtaxorder'); ?>
			</a></p>
		<?php
		$args = array( 'public' => true );
		$output = 'objects';
		$taxonomies = get_taxonomies( $args, $output );

		// Also make the link_category available if activated.
		$linkplugin = "link-manager/link-manager.php";
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active($linkplugin) ) {
			$args = array( 'name' => 'link_category' );
			$taxonomies2 = get_taxonomies( $args, $output );
			$taxonomies = array_merge($taxonomies, $taxonomies2);
		}

		if ( !empty( $taxonomies ) ) {
			echo "<h3>" . __('Taxonomies', 'customtaxorder') . "</h3><ul>";
			foreach ( $taxonomies as $taxonomy ) {
				echo '<li class="lineitem"><a href="' . admin_url( 'admin.php?page=customtaxorder-' . $taxonomy->name ) . '">' . $taxonomy->label . '</a></li>';
			}
		}
		echo "</ul></div>";
		return;
	} else {
		$args = array( 'public' => true );
		$output = 'objects';
		$taxonomies = get_taxonomies( $args, $output );

		// Also make the link_category available if activated.
		$linkplugin = "link-manager/link-manager.php";
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active($linkplugin) ) {
			$args = array( 'name' => 'link_category' );
			$taxonomies2 = get_taxonomies( $args, $output );
			$taxonomies = array_merge($taxonomies, $taxonomies2);
		}

		if ( !empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$com_page = 'customtaxorder-'.$taxonomy->name;
				if ( !isset($options[$taxonomy->name]) ) {
					$options[$taxonomy->name] = 0; // default if not set in options yet
				}
				if ( $_GET['page'] == $com_page ) {
					$settings .= '<label><input type="radio" name="customtaxorder_settings[' . $taxonomy->name . ']" value="0" ' . checked('0', $options[$taxonomy->name], false) . ' /> ' . __('Order by ID (default).', 'customtaxorder') . '</label><br />';
					$settings .= '<label><input type="radio" name="customtaxorder_settings[' . $taxonomy->name . ']" value="1" ' . checked('1', $options[$taxonomy->name], false) . ' /> ' . __('Custom Order as defined above.', 'customtaxorder') . '</label><br />';
					$settings .= '<label><input type="radio" name="customtaxorder_settings[' . $taxonomy->name . ']" value="2" ' . checked('2', $options[$taxonomy->name], false) . ' /> ' . __('Alphabetical Order.', 'customtaxorder') . '</label><br />';
					$tax_label = $taxonomy->label;
					$tax = $taxonomy->name;
				} else {
					if ( !isset($options[$taxonomy->name]) ) {
						$options[$taxonomy->name] = 0; // default if not set in options yet
					}
					$settings .= '<input name="customtaxorder_settings[' . $taxonomy->name . ']" type="hidden" value="' . $options[$taxonomy->name] . '" />';
				}
			}
		}
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
	<h2><?php echo __('Order ', 'customtaxorder') . $tax_label; ?></h2>
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
							<input type="submit" name="order-alpha" id="order-alpha" class="button-primary" value="<?php _e('Sort Alphabetical', 'customtaxorder') ?>" />
						</div>
						<div class="clear"></div>
					</div>
					<input type="hidden" id="hidden-custom-order" name="hidden-custom-order" />
					<input type="hidden" id="hidden-parent-id" name="hidden-parent-id" value="<?php echo $parent_ID; ?>" />
				</div>
				<?php $dropdown = customtaxorder_sub_query( $terms, $tax ); if( !empty($dropdown) ) { ?>
				<div class="widget order-widget">
					<h3 class="widget-top"><?php print(__('Sub-', 'customtaxorder').$tax_label); ?> | <small><?php _e('Choose a term from the drop down to order its sub-terms.', 'customtaxorder'); ?></small></h3>
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
	<form method="post" action="options.php" class="clear">
		<?php settings_fields('customtaxorder_settings'); ?>
		<div class="metabox-holder">
			<div class="order-widget">
				<h3 class="widget-top"><?php _e('Settings'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('Auto-Sort Queries of this Taxonomy', 'customtaxorder') ?></th>
					</tr>
					<tr valign="top">
						<td><?php echo $settings; ?></td>
					</tr>
				</table>
				<input type="hidden" name="customtaxorder_settings[update]" value="Updated" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'customtaxorder') ?>" />
				</p>
			</div>
		</div>
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
		jQuery("#order-alpha").click(function(e) {
			e.preventDefault();
			jQuery("#custom-loading").show();
			orderAlpha();
			//jQuery("#order-submit").trigger("click");
			setTimeout(function(){
				jQuery("#custom-loading").hide();
			},500);
			jQuery("#order-alpha").blur();
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

	function orderAlpha() {
		jQuery("#custom-order-list li").sort(asc_sort).appendTo('#custom-order-list');
		var newOrder = jQuery("#custom-order-list").sortable("toArray");
		jQuery("#custom-loading").show();
		jQuery("#hidden-custom-order").val(newOrder);
		return true;
	}

	// accending sort
	function asc_sort(a, b) {
		//return (jQuery(b).text()) < (jQuery(a).text()) ? 1 : -1;
		//console.log (jQuery(a).text());
		return jQuery(a).text().toUpperCase().localeCompare(jQuery(b).text().toUpperCase());
	}

// ]]>
</script>
<?php }
}
