<?php

/*
Plugin Name: Preset Quantity Buttons for WooCommerce
Plugin URI: https://wordpress.org/plugins/preset-quantity-buttons-for-woocommerce
Requires Plugins: woocommerce
Description: Adds preset quantity buttons to WooCommerce product pages, letting customers instantly add a predefined quantity of a product to their cart.
Author: Senff
Author URI: https://www.senff.com
Version: 1.0
Requires at least: 6.0
Requires PHP: 7.4
WC requires at least: 7.0
WC tested up to: 10.7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: preset-quantity-buttons-for-woocommerce
*/

defined('ABSPATH') or die('INSERT COIN');

define('PQB_VERSION',    '1.0');
define('PQB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PQB_PLUGIN_URL', plugin_dir_url(__FILE__));


// === WOOCOMMERCE COMPATIBILITY ========================================================

add_action('before_woocommerce_init', function() {
	if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
	}
});


// === ADMIN: PRODUCT DATA TAB =========================================================

// --- Add preset quantities tab --------------------------------------------------
	function pqb_add_product_tab($tabs) {
		global $post;

		if ($post && $post->post_type === 'product') {
			$terms        = get_the_terms($post->ID, 'product_type');
			$product_type = $terms ? $terms[0]->slug : 'simple';
			if (!in_array($product_type, array('simple', 'variable'), true)) {
				return $tabs;
			}
		}

		$tabs['pqb'] = array(
			'label'    => __('Preset Quantities', 'preset-quantity-buttons-for-woocommerce'),
			'target'   => 'pqb_product_data',
			'class'    => array('show_if_simple', 'show_if_variable', 'hide_if_subscription', 'hide_if_variable-subscription'),
			'priority' => 80,
		);
		return $tabs;
	}


// --- Output tab panel content ---------------------------------------------------
	function pqb_product_tab_content() {
		global $post;

		$use_global = get_post_meta($post->ID, '_pqb_use_global', true);
		if ($use_global === '' && get_option('pqb_apply_to_all', 'no') === 'yes') {
			$use_global = 'yes';
		}
		$quantities    = get_post_meta($post->ID, '_pqb_quantities', true);
		$hide_standard = get_post_meta($post->ID, '_pqb_hide_standard', true);
		$button_prefix = get_post_meta($post->ID, '_pqb_button_prefix', true);
		$button_suffix = get_post_meta($post->ID, '_pqb_button_suffix', true);

		if (!is_array($quantities)) {
			$quantities = array();
		}
		if ($hide_standard === '') {
			$hide_standard = 'no';
		}

		$settings_url = admin_url('admin.php?page=wc-settings&tab=pqb');
		$use_global_desc = sprintf(
			/* translators: %s: URL for the global quantity buttons settings page */
			__('Use this option to control if <a href="%s">global quantity buttons</a> are added to this product.', 'preset-quantity-buttons-for-woocommerce'),
			esc_url($settings_url)
		);
		?>
		<div id="pqb_product_data" class="panel woocommerce_options_panel">

			<p class="pqb-sold-notice"<?php if (get_post_meta($post->ID, '_sold_individually', true) !== 'yes') echo ' style="display:none"'; ?>><?php esc_html_e('This product is set to Sold Individually — the settings below will NOT be applied on the product page.', 'preset-quantity-buttons-for-woocommerce'); ?></p>

			<div class="options_group">
				<p class="form-field">
					<label for="pqb_use_global"><?php esc_html_e('Use global quantity buttons?', 'preset-quantity-buttons-for-woocommerce'); ?></label>
					<input type="checkbox" id="pqb_use_global" name="pqb_use_global" value="yes" <?php checked($use_global, 'yes'); ?> />
					<span class="description"><?php echo wp_kses($use_global_desc, array('a' => array('href' => array()))); ?></span>
				</p>
			</div>

			<div class="options_group">
				<p class="form-field">
					<label for="pqb_quantities"><?php esc_html_e('Preset Quantities', 'preset-quantity-buttons-for-woocommerce'); ?></label>
					<input type="text" id="pqb_quantities" name="pqb_quantities" value="<?php echo esc_attr(implode(', ', $quantities)); ?>" placeholder="<?php esc_attr_e('e.g. 2, 4, 10', 'preset-quantity-buttons-for-woocommerce'); ?>" style="min-width:300px" />
					<span class="description"><?php esc_html_e('Comma-separated list of quantities. For example, if you enter "2, 4, 10", it will result in 3 buttons; one button to add 2 to the cart, one button to add 4 to the cart, and one button to add 10 to the cart.', 'preset-quantity-buttons-for-woocommerce'); ?></span>
				</p>
				<p id="pqb-too-many-warning" class="form-field pqb-warning" style="display:none;">
					<?php esc_html_e('Warning: You have defined more than 10 preset quantities. This may result in a cluttered product page.', 'preset-quantity-buttons-for-woocommerce'); ?>
				</p>
			</div>

			<?php
			$global_prefix = get_option('pqb_button_prefix', '');
			$global_suffix = get_option('pqb_button_suffix', '');
			$field_prefix  = $button_prefix !== '' ? $button_prefix : $global_prefix;
			$field_suffix  = $button_suffix !== '' ? $button_suffix : $global_suffix;
			$preview       = !empty($quantities) ? $quantities[0] : 3;
			$prev_pre      = $field_prefix !== '' ? $field_prefix : __('ADD', 'preset-quantity-buttons-for-woocommerce');
			$preview_label = trim($prev_pre . ' ' . $preview . ($field_suffix !== '' ? ' ' . $field_suffix : ''));
			?>
			<div class="options_group">
				<p class="form-field">
					<label for="pqb_button_prefix"><?php esc_html_e('Button Prefix and Suffix', 'preset-quantity-buttons-for-woocommerce'); ?></label>
					<input type="text" id="pqb_button_prefix" name="pqb_button_prefix" value="<?php echo esc_attr($field_prefix); ?>" placeholder="<?php esc_attr_e('e.g. ADD', 'preset-quantity-buttons-for-woocommerce'); ?>" class="pqb-prefix-suffix-input" style="width:110px" />
					&nbsp;
					<input type="text" id="pqb_button_suffix" name="pqb_button_suffix" value="<?php echo esc_attr($field_suffix); ?>" placeholder="<?php esc_attr_e('e.g. TO CART', 'preset-quantity-buttons-for-woocommerce'); ?>" class="pqb-prefix-suffix-input" style="width:110px" />
					<span class="description"><?php esc_html_e('The words shown before and after the quantity on each button, e.g. "Buy 3" or "Add 3 to cart"', 'preset-quantity-buttons-for-woocommerce'); ?></span>
					<span class="pqb-preview-wrap" style="display:block"><span class="pqb-preview-label"><?php esc_html_e('PREVIEW:', 'preset-quantity-buttons-for-woocommerce'); ?></span><span class="pqb-btn-preview" data-preview-num="<?php echo esc_attr($preview); ?>"><?php echo esc_html($preview_label); ?></span></span>
				</p>
			</div>

			<div class="options_group">
				<p class="form-field">
					<label for="pqb_hide_standard"><?php esc_html_e('Quantity Buttons', 'preset-quantity-buttons-for-woocommerce'); ?></label>
					<select id="pqb_hide_standard" name="pqb_hide_standard">
						<?php foreach (pqb_get_button_placement_options() as $value => $label) : ?>
							<option value="<?php echo esc_attr($value); ?>" <?php selected($hide_standard, $value); ?>><?php echo esc_html($label); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			</div>

		</div>
		<?php
	}


// --- Save product meta ----------------------------------------------------------
	function pqb_save_product_meta($post_id) {
		if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['woocommerce_meta_nonce'])), 'woocommerce_save_data')) {
			return;
		}

		if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		update_post_meta($post_id, '_pqb_use_global', isset($_POST['pqb_use_global']) ? 'yes' : 'no');

		if (isset($_POST['pqb_quantities'])) {
			$quantities = array();
			$str        = sanitize_text_field(wp_unslash($_POST['pqb_quantities']));
			foreach (explode(',', $str) as $val) {
				$val = intval(trim($val));
				if ($val >= 1) {
					$quantities[] = $val;
				}
			}
			update_post_meta($post_id, '_pqb_quantities', array_values(array_unique($quantities)));
		}

		if (isset($_POST['pqb_hide_standard'])) {
			$hide = sanitize_text_field(wp_unslash($_POST['pqb_hide_standard']));
			update_post_meta($post_id, '_pqb_hide_standard', in_array($hide, array('yes', 'no'), true) ? $hide : 'no');
		}

		if (isset($_POST['pqb_button_prefix'])) {
			update_post_meta($post_id, '_pqb_button_prefix', sanitize_text_field(wp_unslash($_POST['pqb_button_prefix'])));
		}

		if (isset($_POST['pqb_button_suffix'])) {
			update_post_meta($post_id, '_pqb_button_suffix', sanitize_text_field(wp_unslash($_POST['pqb_button_suffix'])));
		}
	}


// === ADMIN: WOOCOMMERCE SETTINGS TAB =================================================

// --- Register settings tab ------------------------------------------------------
	function pqb_add_settings_tab($tabs) {
		$tabs['pqb'] = __('Preset Qty Buttons', 'preset-quantity-buttons-for-woocommerce');
		return $tabs;
	}


// --- Shared dropdown options for button placement -------------------------------
	function pqb_get_button_placement_options() {
		return array(
			'no'  => __('Placed BELOW the default Add to Cart button', 'preset-quantity-buttons-for-woocommerce'),
			'yes' => __('REPLACE default quantity field and Add to Cart button', 'preset-quantity-buttons-for-woocommerce'),
		);
	}


// --- Define settings fields -----------------------------------------------------
	function pqb_get_settings_fields() {
		return array(
			array(
				'title' => __('Preset Quantity Buttons', 'preset-quantity-buttons-for-woocommerce'),
				'type'  => 'title',
				'id'    => 'pqb_settings_section',
			),
			array(
				'title'       => __('Default Preset Quantities', 'preset-quantity-buttons-for-woocommerce'),
				'desc'        => __('Comma-separated list of quantities. Every number will add one button to the product page. For example, if you enter "2, 4, 10", it will add 3 buttons; one button to add 2 to the cart, one button to add 4 to the cart, and one button to add 10 to the cart. <strong>This can be overridden per product in the Preset Quantities tab.</strong>', 'preset-quantity-buttons-for-woocommerce'),
				'id'          => 'pqb_global_quantities',
				'type'        => 'text',
				'css'         => 'min-width:300px',
				'placeholder' => __('e.g. 2, 4, 10', 'preset-quantity-buttons-for-woocommerce'),
				'default'     => '',
			),
			array(
				'type' => 'pqb_prefix_suffix',
			),
			array(
				'title'   => __('Quantity Buttons', 'preset-quantity-buttons-for-woocommerce'),
				'id'      => 'pqb_global_hide_standard',
				'type'    => 'select',
				'options' => pqb_get_button_placement_options(),
				'default' => 'no',
			),
			array(
				'title'   => __('Apply to new products', 'preset-quantity-buttons-for-woocommerce'),
				'desc'    => __('When checked, all settings on this page are applied to every newly created product by default.', 'preset-quantity-buttons-for-woocommerce') . '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . __('This can be overridden per product in the Preset Quantities tab.', 'preset-quantity-buttons-for-woocommerce') . '</strong>',
				'id'      => 'pqb_apply_to_all',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'pqb_settings_section',
			),
		);
	}


// --- Render bulk action buttons below the Save Changes button -------------------
	function pqb_render_bulk_action_buttons() {
		$screen = get_current_screen();
		if (!$screen || $screen->id !== 'woocommerce_page_wc-settings') {
			return;
		}
		$tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, used only to determine whether to render bulk action buttons.
		if ($tab !== 'pqb') {
			return;
		}

		$apply_url  = wp_nonce_url(admin_url('admin-post.php?action=pqb_apply_to_all_now'), 'pqb_apply_to_all_now');
		$remove_url = wp_nonce_url(admin_url('admin-post.php?action=pqb_remove_from_all_now'), 'pqb_remove_from_all_now');
		?>
		<div id="pqb-bulk-actions" style="display:none; margin-top:20px">
			<a href="<?php echo esc_url($apply_url); ?>" class="button button-primary"><?php esc_html_e('APPLY TO ALL PRODUCTS NOW', 'preset-quantity-buttons-for-woocommerce'); ?></a>
			<p><?php esc_html_e('By selecting this button, all settings on this page will be applied to all existing Simple and Variable Products.', 'preset-quantity-buttons-for-woocommerce'); ?> <br><strong><?php esc_html_e('You can still override this for each product in the Preset Quantities tab.', 'preset-quantity-buttons-for-woocommerce'); ?></strong></p>
			<br>
			<a href="<?php echo esc_url($remove_url); ?>" class="button"><?php esc_html_e('REMOVE FROM ALL PRODUCTS NOW', 'preset-quantity-buttons-for-woocommerce'); ?></a>
			<p><?php esc_html_e('By selecting this button, all these settings will be removed from all products, and every product will use the settings you configured in their own Preset Quantities tab.', 'preset-quantity-buttons-for-woocommerce'); ?></p>
		</div>
		<?php
	}


// --- Handle apply-to-all action -------------------------------------------------
	function pqb_handle_apply_to_all_now() {
		if (!current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('Permission denied.', 'preset-quantity-buttons-for-woocommerce'));
		}
		check_admin_referer('pqb_apply_to_all_now');

		$product_ids = get_posts(array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => array('simple', 'variable'),
				),
			),
		));

		foreach ($product_ids as $id) {
			update_post_meta($id, '_pqb_use_global', 'yes');
		}

		set_transient('pqb_apply_all_success_' . get_current_user_id(), 1, 60);
		wp_safe_redirect(admin_url('admin.php?page=wc-settings&tab=pqb'));
		exit;
	}


// --- Handle remove-from-all action ----------------------------------------------
	function pqb_handle_remove_from_all_now() {
		if (!current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('Permission denied.', 'preset-quantity-buttons-for-woocommerce'));
		}
		check_admin_referer('pqb_remove_from_all_now');

		$product_ids = get_posts(array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => array('simple', 'variable'),
				),
			),
		));

		foreach ($product_ids as $id) {
			update_post_meta($id, '_pqb_use_global', 'no');
		}

		set_transient('pqb_remove_all_success_' . get_current_user_id(), 1, 60);
		wp_safe_redirect(admin_url('admin.php?page=wc-settings&tab=pqb'));
		exit;
	}


// --- Show success notices after bulk actions ------------------------------------
	function pqb_apply_all_admin_notice() {
		$screen = get_current_screen();
		if (!$screen || $screen->id !== 'woocommerce_page_wc-settings') {
			return;
		}

		$apply_key  = 'pqb_apply_all_success_' . get_current_user_id();
		$remove_key = 'pqb_remove_all_success_' . get_current_user_id();

		if (get_transient($apply_key)) {
			delete_transient($apply_key);
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Global quantity button settings have been enabled for all existing products.', 'preset-quantity-buttons-for-woocommerce') . '</p></div>';
		}

		if (get_transient($remove_key)) {
			delete_transient($remove_key);
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Global quantity button settings have been removed from all existing products.', 'preset-quantity-buttons-for-woocommerce') . '</p></div>';
		}
	}


// --- Render custom prefix/suffix field ------------------------------------------
	function pqb_admin_field_prefix_suffix() {
		$prefix   = get_option('pqb_button_prefix', __('Buy', 'preset-quantity-buttons-for-woocommerce'));
		$suffix   = get_option('pqb_button_suffix', '');
		$global   = get_option('pqb_global_quantities', '');
		$parts    = array_filter(array_map('trim', explode(',', $global)));
		$preview  = !empty($parts) ? absint(reset($parts)) : 3;
		if ($preview < 1) {
			$preview = 3;
		}
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label><?php esc_html_e('Button Prefix and Suffix', 'preset-quantity-buttons-for-woocommerce'); ?></label>
			</th>
			<td class="forminp">
				<?php
			$preview_label = trim(trim($prefix) . ' ' . $preview . (trim($suffix) !== '' ? ' ' . trim($suffix) : ''));
			?>
				<input type="text" name="pqb_button_prefix" value="<?php echo esc_attr($prefix); ?>" placeholder="<?php esc_attr_e('e.g. ADD', 'preset-quantity-buttons-for-woocommerce'); ?>" class="pqb-prefix-suffix-input" style="width:110px" />
				&nbsp;
				<input type="text" name="pqb_button_suffix" value="<?php echo esc_attr($suffix); ?>" placeholder="<?php esc_attr_e('e.g. TO CART', 'preset-quantity-buttons-for-woocommerce'); ?>" class="pqb-prefix-suffix-input" style="width:110px" />
				<p class="description"><?php esc_html_e('The words shown before and after the quantity on each button, e.g. "Buy 3" or "Add 3 to cart"', 'preset-quantity-buttons-for-woocommerce'); ?></p>
				<p class="pqb-preview-wrap"><span class="pqb-preview-label"><?php esc_html_e('PREVIEW:', 'preset-quantity-buttons-for-woocommerce'); ?></span><span class="pqb-btn-preview" data-preview-num="<?php echo esc_attr($preview); ?>"><?php echo esc_html($preview_label); ?></span></p>
			</td>
		</tr>
		<?php
	}


// --- Output and save settings ---------------------------------------------------
	function pqb_settings_tab_content() {
		woocommerce_admin_fields(pqb_get_settings_fields());
	}

	function pqb_save_settings() {
		check_admin_referer('woocommerce-settings');
		add_filter('gettext', 'pqb_filter_settings_saved_message', 10, 3);
		woocommerce_update_options(pqb_get_settings_fields());
		update_option('pqb_button_prefix', sanitize_text_field(wp_unslash($_POST['pqb_button_prefix'] ?? '')));
		update_option('pqb_button_suffix', sanitize_text_field(wp_unslash($_POST['pqb_button_suffix'] ?? '')));
	}

	function pqb_filter_settings_saved_message($translated, $original, $domain) {
		if ('woocommerce' === $domain && 'Your settings have been saved.' === $original) {
			remove_filter('gettext', 'pqb_filter_settings_saved_message', 10);
			return __('Settings saved.', 'preset-quantity-buttons-for-woocommerce');
		}
		return $translated;
	}


// --- Validate global quantities field -------------------------------------------
	function pqb_validate_global_quantities($value) {
		$value = trim($value);

		if ($value === '') {
			return $value;
		}

		if (!preg_match('/^[\d,\s]+$/', $value)) {
			WC_Admin_Settings::add_error(
				__('You can only enter numbers, separated by commas. Other characters are not allowed. Please try again.', 'preset-quantity-buttons-for-woocommerce')
			);
			return get_option('pqb_global_quantities', '');
		}

		$seen   = array();
		$unique = array();
		foreach (explode(',', $value) as $part) {
			$num = intval(trim($part));
			if ($num >= 1 && !in_array($num, $seen, true)) {
				$seen[]   = $num;
				$unique[] = $num;
			}
		}

		return implode(', ', $unique);
	}


// === FRONTEND: HELPER FUNCTIONS ======================================================

// --- Get quantities for a product -----------------------------------------------
	function pqb_get_product_quantities($product_id) {
		$use_global = get_post_meta($product_id, '_pqb_use_global', true);

		if ($use_global === 'yes') {
			$global = get_option('pqb_global_quantities', '');
			if (empty(trim($global))) {
				return array();
			}
			$result = array();
			foreach (explode(',', $global) as $val) {
				$val = intval(trim($val));
				if ($val >= 1) {
					$result[] = $val;
				}
			}
			return $result;
		}

		$quantities = get_post_meta($product_id, '_pqb_quantities', true);
		if (!is_array($quantities)) {
			return array();
		}
		return array_values(array_filter(array_map('intval', $quantities), function($v) { return $v >= 1; }));
	}


// --- Determine whether to hide standard controls --------------------------------
	function pqb_should_hide_standard($product_id) {
		$use_global = get_post_meta($product_id, '_pqb_use_global', true);

		if ($use_global === 'yes') {
			return get_option('pqb_global_hide_standard', 'no') === 'yes';
		}

		return get_post_meta($product_id, '_pqb_hide_standard', true) === 'yes';
	}


// === FRONTEND: RENDER BUTTONS ========================================================

// --- Output preset quantity buttons ---------------------------------------------
	function pqb_render_preset_buttons() {
		global $product;

		if (!$product) {
			return;
		}

		if (!$product->is_type(array('simple', 'variable'))) {
			return;
		}

		if ($product->is_sold_individually()) {
			return;
		}

		$product_id = $product->get_id();
		$quantities = pqb_get_product_quantities($product_id);

		if (empty($quantities)) {
			return;
		}

		$is_variable = $product->is_type('variable');

		$use_global = get_post_meta($product_id, '_pqb_use_global', true);
		if ($use_global === 'yes') {
			$prefix = get_option('pqb_button_prefix', __('Buy', 'preset-quantity-buttons-for-woocommerce'));
			$suffix = get_option('pqb_button_suffix', '');
		} else {
			$per_prefix = get_post_meta($product_id, '_pqb_button_prefix', true);
			$per_suffix = get_post_meta($product_id, '_pqb_button_suffix', true);
			$prefix = $per_prefix !== '' ? $per_prefix : get_option('pqb_button_prefix', __('Buy', 'preset-quantity-buttons-for-woocommerce'));
			$suffix = $per_suffix !== '' ? $per_suffix : get_option('pqb_button_suffix', '');
		}

		$stock_qty = null;
		$cart_qty  = 0;
		if (!$is_variable && $product->managing_stock() && !$product->backorders_allowed()) {
			$stock_qty = $product->get_stock_quantity();
			if (WC()->cart) {
				foreach (WC()->cart->get_cart() as $cart_item) {
					if ((int) $cart_item['product_id'] === $product_id) {
						$cart_qty += $cart_item['quantity'];
					}
				}
			}
		}

		echo '<div class="pqb-buttons' . ($is_variable ? ' pqb-variable' : '') . '">';

		foreach ($quantities as $qty) {
			$is_disabled    = $is_variable || ($stock_qty !== null && $qty > ($stock_qty - $cart_qty));
			$disabled_class = $is_disabled ? ' disabled' : '';

			echo '<button type="button" class="button alt wp-element-button pqb-btn' . esc_attr($disabled_class) . '" data-quantity="' . esc_attr($qty) . '"';
			if ($is_disabled) {
				echo ' disabled="disabled"';
			}
			echo '>' . esc_html(implode(' ', array_filter(array(trim($prefix), (string) $qty, trim($suffix)), 'strlen'))) . '</button>';
		}

		echo '</div>';
	}


// === FRONTEND: ENQUEUE SCRIPTS AND STYLES ============================================

	function pqb_enqueue_frontend_scripts() {
		if (!is_product()) {
			return;
		}

		global $post;

		$product = wc_get_product($post->ID);
		if (!$product || !$product->is_type(array('simple', 'variable'))) {
			return;
		}

		if ($product->is_sold_individually()) {
			return;
		}

		$quantities = pqb_get_product_quantities($post->ID);
		if (empty($quantities)) {
			return;
		}

		wp_enqueue_style(
			'pqb-frontend',
			PQB_PLUGIN_URL . 'assets/css/pqb-frontend.css',
			array(),
			PQB_VERSION
		);

		if (pqb_should_hide_standard($post->ID)) {
			wp_add_inline_style('pqb-frontend', '.single_add_to_cart_button, .quantity { display: none !important; }');
		}

		wp_enqueue_script(
			'pqb-frontend',
			PQB_PLUGIN_URL . 'assets/js/pqb-frontend.js',
			array('jquery'),
			PQB_VERSION,
			true
		);

		$js_data = array();

		if ($product->is_type('simple') && $product->managing_stock() && !$product->backorders_allowed()) {
			$stock_qty = $product->get_stock_quantity();
			$cart_qty  = 0;
			if (WC()->cart) {
				foreach (WC()->cart->get_cart() as $cart_item) {
					if ((int) $cart_item['product_id'] === $post->ID) {
						$cart_qty += $cart_item['quantity'];
					}
				}
			}
			$js_data['stock_qty'] = $stock_qty;
			$js_data['cart_qty']  = $cart_qty;
		}

		if ($product->is_type('variable') && WC()->cart) {
			$variation_cart_qtys = array();
			foreach (WC()->cart->get_cart() as $cart_item) {
				if ((int) $cart_item['product_id'] === $post->ID && !empty($cart_item['variation_id'])) {
					$vid = (int) $cart_item['variation_id'];
					$variation_cart_qtys[$vid] = ( $variation_cart_qtys[$vid] ?? 0 ) + $cart_item['quantity'];
				}
			}
			$js_data['variation_cart_qtys'] = $variation_cart_qtys;
		}

		wp_localize_script('pqb-frontend', 'pqb_params', $js_data);
	}


// === ADMIN: ENQUEUE SCRIPTS AND STYLES ===============================================

	function pqb_enqueue_admin_scripts($hook) {
		global $post;

		$on_product_page  = in_array($hook, array('post.php', 'post-new.php'), true) && $post && $post->post_type === 'product';
		$tab              = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, used only to determine which admin scripts to enqueue.
		$on_settings_page = $hook === 'woocommerce_page_wc-settings' && $tab === 'pqb';

		if (!$on_product_page && !$on_settings_page) {
			return;
		}

		wp_enqueue_script(
			'pqb-admin',
			PQB_PLUGIN_URL . 'assets/js/pqb-admin.js',
			array('jquery'),
			PQB_VERSION,
			true
		);
		wp_localize_script('pqb-admin', 'pqb_admin_params', array(
			'too_many_warning'  => __('Warning: You have defined more than 10 preset quantities. This may result in a cluttered product page.', 'preset-quantity-buttons-for-woocommerce'),
			'replace_warning'   => __('Warning: No preset quantities are defined. The default Add to Cart button will still be shown regardless of the setting above.', 'preset-quantity-buttons-for-woocommerce'),
		));

		wp_enqueue_style(
			'pqb-admin',
			PQB_PLUGIN_URL . 'assets/css/pqb-admin.css',
			array(),
			PQB_VERSION
		);
	}


// === PLUGIN ACTION LINKS =============================================================

// --- Add settings link on the plugins page --------------------------------------
	function pqb_settings_link($links) {
		$settings_link = '<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=pqb')) . '">' . esc_html__('Settings', 'preset-quantity-buttons-for-woocommerce') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}


// === HOOKS AND ACTIONS AND FILTERS AND SUCH ==========================================

	// Plugin action links
	$pqb_plugin = plugin_basename(__FILE__);
	add_filter("plugin_action_links_$pqb_plugin", 'pqb_settings_link');

	// Product data tab
	add_filter('woocommerce_product_data_tabs',    'pqb_add_product_tab');
	add_action('woocommerce_product_data_panels',  'pqb_product_tab_content');
	add_action('woocommerce_process_product_meta', 'pqb_save_product_meta');

	// WooCommerce settings tab
	add_action('woocommerce_admin_field_pqb_prefix_suffix',                        'pqb_admin_field_prefix_suffix');
	add_filter('woocommerce_settings_tabs_array',                                  'pqb_add_settings_tab', 50);
	add_action('woocommerce_settings_tabs_pqb',                                    'pqb_settings_tab_content');
	add_action('woocommerce_update_options_pqb',                                   'pqb_save_settings');
	add_filter('woocommerce_admin_settings_sanitize_option_pqb_global_quantities', 'pqb_validate_global_quantities');
	add_action('admin_post_pqb_apply_to_all_now',                                  'pqb_handle_apply_to_all_now');
	add_action('admin_post_pqb_remove_from_all_now',                               'pqb_handle_remove_from_all_now');
	add_action('admin_notices',                                                    'pqb_apply_all_admin_notice');
	add_action('admin_footer',                                                     'pqb_render_bulk_action_buttons');

	// Frontend
	add_action('woocommerce_after_add_to_cart_button', 'pqb_render_preset_buttons');
	add_action('wp_enqueue_scripts',                   'pqb_enqueue_frontend_scripts');

	// Admin scripts
	add_action('admin_enqueue_scripts', 'pqb_enqueue_admin_scripts');
