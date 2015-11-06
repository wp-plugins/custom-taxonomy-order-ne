<?php


function custom_taxonomy_order() {

	if (isset($_POST['order-submit'])) {
		customtaxorder_update_taxonomies();
	}

	?>
	<div class='wrap'>
		<?php screen_icon('customtaxorder'); ?>
		<h1><?php _e('Order Taxonomies', 'custom-taxonomy-order-ne'); ?></h1>
		<p><?php _e('The ordering of taxonomies themselves.', 'custom-taxonomy-order-ne'); ?></p>

		<form name="custom-order-form" method="post" action="">
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

			if ( $taxonomies ) {

				$taxonomies_ordered = customtaxorder_sort_taxonomies( $taxonomies );
				?>

				<div id="poststuff" class="metabox-holder">
					<div class="widget order-widget">
						<h2 class="widget-top">
							<?php _e('Order Taxonomies', 'custom-taxonomy-order-ne'); ?> |
							<small><?php _e('Order the taxonomies by dragging and dropping them into the desired order.', 'custom-taxonomy-order-ne') ?></small>
						</h2>
						<div class="misc-pub-section">
							<ul id="custom-taxonomy-list">
								<?php
								foreach ( $taxonomies_ordered as $taxonomy ) { ?>
									<li id="<?php echo $taxonomy->name; ?>" class="lineitem"><?php echo $taxonomy->name; ?></li>
									<?php
								} ?>
							</ul>
						</div>
						<div class="misc-pub-section misc-pub-section-last">
							<div id="publishing-action">
								<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" id="custom-loading" style="display:none" alt="" />
								<input type="submit" name="order-submit" id="order-submit" class="button-primary" value="<?php _e('Update Order', 'custom-taxonomy-order-ne') ?>" />
							</div>
							<div class="clear"></div>
						</div>
						<input type="hidden" id="hidden-taxonomy-order" name="hidden-taxonomy-order" />
					</div>
				</div>

				<script type="text/javascript">
				// <![CDATA[

					jQuery(document).ready(function(jQuery) {
						jQuery("#custom-loading").hide();
						jQuery("#order-submit").click(function() {
							orderSubmit();
						});
					});

					function customtaxorderAddLoadEvent(){
						jQuery("#custom-taxonomy-list").sortable({
							placeholder: "sortable-placeholder",
							revert: false,
							tolerance: "pointer"
						});
					};

					addLoadEvent(customtaxorderAddLoadEvent);

					function orderSubmit() {
						var newOrder = jQuery("#custom-taxonomy-list").sortable("toArray");
						jQuery("#custom-loading").show();
						jQuery("#hidden-taxonomy-order").val(newOrder);
						return true;
					}

				// ]]>
				</script>

			<?php } else { ?>
				<p><?php _e('No taxonomies found', 'custom-taxonomy-order-ne'); ?></p>
			<?php }
			?>
		</form>
	</div>
	<?php
}


/*
 * Save order of the taxonomies in an option
 */
function customtaxorder_update_taxonomies() {
	if (isset($_POST['hidden-taxonomy-order']) && $_POST['hidden-taxonomy-order'] != "") {

		$new_order = $_POST['hidden-taxonomy-order'];
		$new_order = sanitize_text_field( $new_order );

		update_option('customtaxorder_taxonomies', $new_order);

		echo '<div id="message" class="updated fade notice is-dismissible"><p>'. __('Order updated successfully.', 'custom-taxonomy-order-ne').'</p></div>';
	} else {
		echo '<div id="message" class="error fade notice is-dismissible"><p>'. __('An error occured, order has not been saved.', 'custom-taxonomy-order-ne').'</p></div>';
	}
}


/*
 * Sort the taxonomies
 *
 * Parameter: $taxonomies, array with a list of taxonomy objects.
 *
 * Returns: array with list of taxonomies, ordered correctly.
 *
 * Since: 2.7.0
 *
 */
function customtaxorder_sort_taxonomies( $taxonomies = array() ) {
	$order = get_option( 'customtaxorder_taxonomies', array() );
	$order = explode( ",", $order );

	$taxonomies_ordered = array();

	if ( ! empty($order) && is_array($order) ) {
		foreach ( $order as $tax ) {
			foreach ( $taxonomies as $tax_name => $tax_obj ) {
				if ( is_object( $tax_obj ) && $tax === $tax_name ) {
					$taxonomies_ordered[ $tax_name ] = $tax_obj;
					unset( $taxonomies[ $tax_name ] );
				}
			}
		}
	}

	// The leftovers
	foreach ( $taxonomies as $tax_name => $tax_obj ) {
		$taxonomies_ordered[ $tax_name ] = $tax_obj;
	}

	return $taxonomies_ordered;
}

