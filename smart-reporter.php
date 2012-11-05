<?php
/*
Plugin Name: Smart Reporter for e-commerce
Plugin URI: http://www.storeapps.org/products/smart-reporter-for-e-commerce/
Description: <strong>Lite Version Installed.</strong> Store analysis like never before. 
Version: 1.9
Author: Store Apps
Author URI: http://www.storeapps.org/about/
Copyright (c) 2011, 2012 Store Apps All rights reserved.
*/

//Hooks
register_activation_hook ( __FILE__, 'sr_activate' );
register_deactivation_hook ( __FILE__, 'sr_deactivate' );

/**
 * Registers a plugin function to be run when the plugin is activated.
 */
function sr_activate() {
	global $wpdb, $blog_id;
	
        if ( false === get_site_option( 'sr_is_auto_refresh' ) ) {
            update_site_option( 'sr_is_auto_refresh', 'no' );
            update_site_option( 'sr_what_to_refresh', 'all' );
            update_site_option( 'sr_refresh_duration', '5' );
        }
        
        if ( is_multisite() ) {
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}", 0 );
	} else {
		$blog_ids = array( $blog_id );
	}
	foreach ( $blog_ids as $blog_id ) {
		if ( ( file_exists ( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) && ( is_plugin_active ( 'woocommerce/woocommerce.php' ) ) ) {
			$wpdb_obj = clone $wpdb;
			$wpdb->blogid = $blog_id;
			$wpdb->set_prefix( $wpdb->base_prefix );
			$create_table_query = "
				CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sr_woo_order_items` (
				  `product_id` bigint(20) unsigned NOT NULL default '0',
				  `order_id` bigint(20) unsigned NOT NULL default '0',
				  `product_name` text NOT NULL,
				  `quantity` int(10) unsigned NOT NULL default '0',
				  `sales` decimal(11,2) NOT NULL default '0.00',
				  `discount` decimal(11,2) NOT NULL default '0.00',
				  KEY `product_id` (`product_id`),
				  KEY `order_id` (`order_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	   		dbDelta( $create_table_query );
	   		
			add_action( 'load_sr_woo_order_items', 'load_sr_woo_order_items' );
	   		do_action( 'load_sr_woo_order_items', $wpdb );
	   		$wpdb = clone $wpdb_obj;
		}
	}
}

/**
 * Registers a plugin function to be run when the plugin is deactivated.
 */
function sr_deactivate() {
	global $wpdb;
	if ( is_multisite() ) {
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}", 0 );
	} else {
		$blog_ids = array( $blog_id );
	}
	foreach ( $blog_ids as $blog_id ) {
		$wpdb_obj = clone $wpdb;
		$wpdb->blogid = $blog_id;
		$wpdb->set_prefix( $wpdb->base_prefix );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}sr_woo_order_items" );
		$wpdb = clone $wpdb_obj;
	}
}

function get_latest_version($plugin_file) {
	$sr_plugin_info = get_site_transient ( 'update_plugins' );
	$latest_version = $sr_plugin_info->response [$plugin_file]->new_version;
	return $latest_version;
}

function get_user_sr_version($plugin_file) {
	$sr_plugin_info = get_plugins ();
	$user_version = $sr_plugin_info [$plugin_file] ['Version'];
	return $user_version;
}

function is_pro_updated() {
	$user_version = get_user_sr_version (SR_PLUGIN_FILE);
	$latest_version = get_latest_version (SR_PLUGIN_FILE);
	return version_compare ( $user_version, $latest_version, '>=' );
}

/**
 * Throw an error on admin page when WP e-Commerece plugin is not activated.
 */
if ( is_admin () || ( is_multisite() && is_network_admin() ) ) {
	// BOF automatic upgrades
	include ABSPATH . 'wp-includes/pluggable.php';
	
	$plugin = plugin_basename ( __FILE__ );
	define ( 'SR_PLUGIN_DIR',dirname($plugin));
	define ( 'SR_PLUGIN_DIR_ABSPATH', dirname(__FILE__) );
	define ( 'SR_PLUGIN_FILE', $plugin );
	define ( 'STORE_APPS_URL', 'http://www.storeapps.org/' );
		
	define ( 'ADMIN_URL', get_admin_url () ); //defining the admin url
	define ( 'SR_PLUGIN_DIRNAME', plugins_url ( '', __FILE__ ) );
	define ( 'SR_IMG_URL', SR_PLUGIN_DIRNAME . '/resources/themes/images/' );	
	
	// EOF
	
	add_action ( 'admin_notices', 'sr_admin_notices' );
	add_action ( 'admin_init', 'sr_admin_init' );
	
	if ( is_multisite() && is_network_admin() ) {
		
		function sr_add_license_key_page() {
			$page = add_submenu_page ('settings.php', 'Smart Reporter', 'Smart Reporter', 'manage_options', 'sr-settings', 'sr_settings_page' );
			add_action ( 'admin_print_styles-' . $page, 'sr_admin_styles' );
		}
		
		if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr.js' ))
			add_action ('network_admin_menu', 'sr_add_license_key_page', 11);
			
	}
	
	function sr_admin_init() {
		$plugin_info 	= get_plugins ();
		$sr_plugin_info = $plugin_info [SR_PLUGIN_FILE];
		$ext_version 	= '4.0.1';
		if (is_plugin_active ( 'woocommerce/woocommerce.php' ) && is_plugin_active ( basename(WPSC_URL).'/wp-shopping-cart.php' )) {
			define('WPSC_WOO_ACTIVATED',true);
		} elseif (is_plugin_active ( basename(WPSC_URL).'/wp-shopping-cart.php' )) {
			define('WPSC_ACTIVATED',true);
		} elseif (is_plugin_active ( 'woocommerce/woocommerce.php' )) {
			define('WOO_ACTIVATED', true);
		}
		
		wp_register_script ( 'sr_ext_all', plugins_url ( 'resources/ext/ext-all.js', __FILE__ ), array (), $ext_version );
		if ($_GET['post_type'] == 'wpsc-product' || $_GET['page'] == 'smart-reporter-wpsc') {
			wp_register_script ( 'sr_main', plugins_url ( '/sr/smart-reporter.js', __FILE__ ), array ('sr_ext_all' ), $sr_plugin_info ['Version'] );
			define('WPSC_RUNNING', true);
			define('WOO_RUNNING', false);
			// checking the version for WPSC plugin
			define ( 'IS_WPSC37', version_compare ( WPSC_VERSION, '3.8', '<' ) );
			define ( 'IS_WPSC38', version_compare ( WPSC_VERSION, '3.8', '>=' ) );
			if ( IS_WPSC38 ) {		// WPEC 3.8.7 OR 3.8.8
				define('IS_WPSC387', version_compare ( WPSC_VERSION, '3.8.7.6.2', '<=' ));
				define('IS_WPSC388', version_compare ( WPSC_VERSION, '3.8.7.6.2', '>' ));
			}
		} else if ($_GET['post_type'] == 'product' || $_GET['page'] == 'smart-reporter-woo') {
			wp_register_script ( 'sr_main', plugins_url ( '/sr/smart-reporter-woo.js', __FILE__ ), array ('sr_ext_all' ), $sr_plugin_info ['Version'] );
			define('WPSC_RUNNING', false);
			define('WOO_RUNNING', true);
			// checking the version for WooCommerce plugin
			define ( 'IS_WOO13', version_compare ( WOOCOMMERCE_VERSION, '1.4', '<' ) );
		}
		wp_register_style ( 'sr_ext_all', plugins_url ( 'resources/css/ext-all.css', __FILE__ ), array (), $ext_version );
		wp_register_style ( 'sr_main', plugins_url ( '/sr/smart-reporter.css', __FILE__ ), array ('sr_ext_all' ), $sr_plugin_info ['Version'] );
		
		if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr.js' )) {
			wp_register_script ( 'sr_functions', plugins_url ( '/pro/sr.js', __FILE__ ), array ('sr_main' ), $sr_plugin_info ['Version'] );
			define ( 'SRPRO', true );
		} else {
			define ( 'SRPRO', false );
		}
		
		if (SRPRO === true) {
			include ('pro/upgrade.php');
			add_action ( 'after_plugin_row_' . plugin_basename ( __FILE__ ), 'sr_plugin_row', '', 1 );
//			do_action ( 'after_plugin_row_' . plugin_basename ( __FILE__ ));			// Fix: For automatic upgrade issue
			add_action ( 'after_plugin_row_' . plugin_basename ( __FILE__ ), 'sr_show_registration_upgrade');
			add_action ( 'in_plugin_update_message-' . plugin_basename ( __FILE__ ), 'sr_update_notice' );
                        add_action ( 'all_admin_notices', 'sr_update_overwrite' );
		}
	}
	
	function sr_admin_notices() {
		if (! is_plugin_active ( 'woocommerce/woocommerce.php' ) && ! is_plugin_active ( basename(WPSC_URL).'/wp-shopping-cart.php' )) {
			echo '<div id="notice" class="error"><p>';
			_e ( '<b>Smart Reporter</b> add-on requires <a href="http://www.storeapps.org/wpec/">WP e-Commerce</a> plugin or <a href="http://www.storeapps.org/woocommerce/">WooCommerce</a> plugin. Please install and activate it.' );
			echo '</p></div>', "\n";
		}
	}
	
	function sr_admin_scripts() {		
		if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr.js' )) {
			wp_enqueue_script ( 'sr_functions' );
		}
		wp_enqueue_script ( 'sr_main' );
	}
	
	function sr_admin_styles() {
		wp_enqueue_style ( 'sr_main' );
	}
	
	function woo_add_modules_sr_admin_pages() {
		$page = add_submenu_page ('woocommerce', 'Smart Reporter', 'Smart Reporter', 'manage_options', 'smart-reporter-woo', 'sr_show_console' );
	    
		if ($_GET ['action'] != 'sr-settings') { // not be include for settings page
			add_action ( 'admin_print_scripts-' . $page, 'sr_admin_scripts' );
		}
		add_action ( 'admin_print_styles-' . $page, 'sr_admin_styles' );
	}
	add_action ('admin_menu', 'woo_add_modules_sr_admin_pages');
	
	function wpsc_add_modules_sr_admin_pages($page_hooks, $base_page) {
		$page = add_submenu_page ( $base_page, 'Smart Reporter', 'Smart Reporter', 'manage_options', 'smart-reporter-wpsc', 'sr_show_console' );
		add_action ( 'admin_print_styles-' . $page, 'sr_admin_styles' );
		if ($_GET ['action'] != 'sr-settings') { // not be include for settings page
			add_action ( 'admin_print_scripts-' . $page, 'sr_admin_scripts' );
		}
		$page_hooks [] = $page;
		return $page_hooks;
	}
	add_filter ( 'wpsc_additional_pages', 'wpsc_add_modules_sr_admin_pages', 10, 2 );
	
	add_action( 'woocommerce_order_actions', 'sr_woo_refresh_order' );			// Action to be performed on clicking 'Save Order' button from Order panel

	// Actions on order change
	add_action( 'woocommerce_order_status_pending', 	'sr_woo_remove_order' );
	add_action( 'woocommerce_order_status_failed', 		'sr_woo_remove_order' );
	add_action( 'woocommerce_order_status_refunded', 	'sr_woo_remove_order' );
	add_action( 'woocommerce_order_status_cancelled', 	'sr_woo_remove_order' );
	add_action( 'woocommerce_order_status_on-hold', 	'sr_woo_add_order' );
	add_action( 'woocommerce_order_status_processing', 	'sr_woo_add_order' );
	add_action( 'woocommerce_order_status_complete', 	'sr_woo_add_order' );

	function sr_woo_refresh_order( $order_id ) {
		sr_woo_remove_order( $order_id );
		$order_status = wp_get_object_terms( $order_id, 'shop_order_status', array('fields' => 'slugs') );
		if ( $order_status[0] == 'on-hold' || $order_status[0] == 'processing' || $order_status[0] == 'completed' )
			sr_woo_add_order( $order_id );
	}
	
	function sr_woo_add_order( $order_id ) {
		global $wpdb;
		$order = new WC_Order( $order_id );
		$order_items = $order->get_items();
		$values = array();
		$insert_query = "INSERT INTO {$wpdb->prefix}sr_woo_order_items 
							( `product_id`, `order_id`, `product_name`, `quantity`, `sales`, `discount` ) VALUES ";
		foreach ( $order_items as $item ) {
			$order_item = array();
			$order_item['product_id'] = ( $item['variation_id'] > 0 ) ? $item['variation_id'] : $item['id'];
			$order_item['order_id'] = $order_id;
			if ( $item['variation_id'] > 0 ) {
				$variation_name = array();
				foreach ( $item['item_meta'] as $item_object ) {
					$variation_name[] = ucfirst( $item_object['meta_value'] );
				}
				$order_item['product_name'] = $item['name'] . ' ( ' . implode( ', ', $variation_name ) . ' )'; 
			} else {
				$order_item['product_name'] = $item['name'];
			}
			$order_item['quantity'] = $item['qty'];
			$order_item['sales'] = isset( $item['line_total'] ) ? $item['line_total'] : ( isset( $item['cost'] ) ? ( $item['cost'] * $item['qty'] ) : '' );
			$order_item['discount'] = isset( $item['line_total'] ) ? ( $item['line_subtotal'] - $item['line_total'] ) : ( isset( $item['cost'] ) ? ( ( $item['base_cost'] - $item['cost'] ) * $item['qty'] ) : '' );
			$values[] = "( {$order_item['product_id']}, {$order_item['order_id']}, '{$order_item['product_name']}', {$order_item['quantity']}, {$order_item['sales']}, {$order_item['discount']} )";
		}
		$insert_query .= implode( ',', $values );
		$wpdb->query( $insert_query );
	}
	
	function sr_woo_remove_order( $order_id ) {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}sr_woo_order_items WHERE order_id = {$order_id}" );
	}
	
	// Function to load table sr_woo_order_items
	function load_sr_woo_order_items( $wpdb ) {
//		global $wpdb;
	
		// WC's code to get all order items
		$results = $wpdb->get_results ("
			SELECT meta.post_id AS order_id, meta.meta_value AS items FROM {$wpdb->posts} AS posts
			
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )
	
			WHERE 	meta.meta_key 		= '_order_items'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('completed', 'processing', 'on-hold')
		");
		
		$values = array();
		$insert_query = "INSERT INTO {$wpdb->prefix}sr_woo_order_items 
							( `product_id`, `order_id`, `product_name`, `quantity`, `sales`, `discount` ) VALUES ";
		
		foreach ( $results as $result ) {
			$order_items_array = maybe_unserialize( $result->items );
			$order_id = $result->order_id;
			if (is_array($order_items_array)) {
				foreach ($order_items_array as $item) {
					$order_item = array();
					$order_item['product_id'] = ( $item['variation_id'] > 0 ) ? $item['variation_id'] : $item['id'];
					$order_item['order_id'] = $order_id;
					if ( $item['variation_id'] > 0 ) {
						$variation_name = array();
						foreach ( $item['item_meta'] as $item_object ) {
							$variation_name[] = ucfirst( $item_object['meta_value'] );
						}
						$order_item['product_name'] = $item['name'] . ' ( ' . implode( ', ', $variation_name ) . ' )'; 
					} else {
						$order_item['product_name'] = $item['name'];
					}
					$order_item['quantity'] = $item['qty'];
					$order_item['sales'] = isset( $item['line_total'] ) ? $item['line_total'] : ( isset( $item['cost'] ) ? ( $item['cost'] * $item['qty'] ) : '' );
					$order_item['discount'] = isset( $item['line_total'] ) ? ( $item['line_subtotal'] - $item['line_total'] ) : ( isset( $item['cost'] ) ? ( ( $item['base_cost'] - $item['cost'] ) * $item['qty'] ) : '' );
					$values[] = "( {$order_item['product_id']}, {$order_item['order_id']}, '{$order_item['product_name']}', {$order_item['quantity']}, {$order_item['sales']}, {$order_item['discount']} )";
				}
			}
		}
		$insert_query .= implode( ',', $values );
		$wpdb->query( $insert_query );
	}
	
	function sr_show_console() {
		
		if (WPSC_RUNNING === true) {
			$json_filename = 'json';
		} else if (WOO_RUNNING === true) {
			$json_filename = 'json-woo';
		}
		define ( 'SR_JSON_URL', SR_PLUGIN_DIRNAME . "/sr/$json_filename.php" );
		
		//set the number of days data to show in lite version.
		define ( 'SR_AVAIL_DAYS', 30);
		
		$latest_version = get_latest_version (SR_PLUGIN_FILE );
		$is_pro_updated = is_pro_updated ();
		
		if ($_GET ['action'] == 'sr-settings') {
			sr_settings_page (SR_PLUGIN_FILE);
		} else {
			$base_path = WP_PLUGIN_DIR . '/' . str_replace ( basename ( __FILE__ ), "", plugin_basename ( __FILE__ ) ) . 'sr/';
		?>
<div class="wrap">
<div id="icon-smart-reporter" class="icon32"><img alt="Smart Reporter"
	src="<?php echo SR_IMG_URL.'/logo.png'; ?>"></div>
<h2><?php
		echo _e ( 'Smart Reporter' );
		echo ' ';
			if (SRPRO === true) {
				echo _e ( 'Pro' );
			} else {
				echo _e ( 'Lite' );
			}
		?>
   	<p class="wrap" style="font-size: 12px">
	   	<span style="float: right"> <?php
			if ( SRPRO === true && ! is_multisite() ) {
				$before_plug_page = '<a href="admin.php?page=smart-reporter-';
				$after_plug_page = '&action=sr-settings">Settings</a> | ';
				if (WPSC_RUNNING == true) {
					$plug_page = 'wpsc';
				} elseif (WOO_RUNNING == true) {
					$plug_page = 'woo';
				}
			} else {
				$before_plug_page = '';
				$after_plug_page = '';
				$plug_page = '';
			}
			printf ( __ ( '%1s%2s%3s<a href="%4s" target=_storeapps>Need Help?</a>' ), $before_plug_page, $plug_page, $after_plug_page, "http://www.storeapps.org/support" );
		?>
		</span>
		<?php
			echo __ ( 'Store analysis like never before.' );
		?>
	</p>
	<h6 align="right"><?php
			if (isset($is_pro_updated) && ! $is_pro_updated) {
				$admin_url = ADMIN_URL . "plugins.php";
				$update_link = "An upgrade for Smart Reporter Pro  $latest_version is available. <a align='right' href=$admin_url> Click to upgrade. </a>";
				sr_display_notice ( $update_link );
			}
			?>
   </h6>
   <h6 align="right">
	<?php
	if (SRPRO === true) {
		if (function_exists ( 'sr_get_license_key' )) {
			$license_key = sr_get_license_key();
			if( $license_key == '' ) {
				if ( ! is_multisite() ) {
					if (WPSC_RUNNING == true) {
						$plug_page = 'wpsc';
					} elseif (WOO_RUNNING == true) {
						$plug_page = 'woo';
					}
					sr_display_notice("Please enter your license key for automatic upgrades and support to get activated. <a href=admin.php?page=smart-reporter-" . $plug_page . "&action=sr-settings>Enter License Key</a>");
				}
			}
		}
	}
	?>
</h2>
</div>

<?php
if (SRPRO === false) {
				?>
<div id="message" class="updated fade">
<p><?php
printf ( __ ( "<b>Important:</b> To get the sales and sales KPI's for more than 30 days upgrade to Pro . Take a <a href='%2s' target=_livedemo> Live Demo here </a>." ), 'http://demo.storeapps.org/' );
				?></p>
</div>
<?php
}
			?>
<?php
			$error_message = '';
			if ( ( file_exists ( WPSC_FILE_PATH . '/wp-shopping-cart.php' ) ) || ( file_exists ( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) ) {
				if ( file_exists ( WPSC_FILE_PATH . '/wp-shopping-cart.php' ) ) {
					if ( is_plugin_active ( WPSC_FOLDER.'/wp-shopping-cart.php' ) ) {
						require_once (WPSC_FILE_PATH . '/wp-shopping-cart.php');
						if (IS_WPSC37 || IS_WPSC38) {
							if (file_exists ( $base_path . 'reporter-console.php' )) {
								include_once ($base_path . 'reporter-console.php');
								return;
							} else {
								$error_message = 'A required Smart Reporter file is missing. Can\'t continue.';
							}
						} else {
							$error_message = 'Smart Reporter currently works only with WP e-Commerce 3.7 or above.';
						}
					} else {
						$error_message = 'WP e-Commerce plugin is not activated. <br/><b>Smart Reporter</b> add-on requires WP e-Commerce plugin, please activate it.';
					}
				} else if ( file_exists ( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
					if ( is_plugin_active ( 'woocommerce/woocommerce.php' ) ) {
						if ( IS_WOO13 ) {
							$error_message = 'Smart Reporter currently works only with WooCommerce 1.4 or above.';
						} else {
							if ( file_exists ( $base_path . 'reporter-console.php' ) ) {
								include_once ( $base_path . 'reporter-console.php' );
								return;
							} else {
								$error_message = 'A required Smart Reporter file is missing. Can\'t continue.';
							}
						}
					} else {
						$error_message = 'WooCommerce plugin is not activated. <br/><b>Smart Reporter</b> add-on requires WooCommerce plugin, please activate it.';
					}
				}
			} else {
				$error_message = '<b>Smart Reporter</b> add-on requires <a href="http://www.storeapps.org/wpec/">WP e-Commerce</a> plugin or <a href="http://www.storeapps.org/woocommerce/">WooCommerce</a> plugin. Please install and activate it.';
			}

			if ($error_message != '') {
				sr_display_err ( $error_message );
				?>
<?php
			}
		}
	}
	
	function sr_update_notice() {
		if ( !function_exists( 'sr_get_download_url_from_db' ) ) return;
                $download_details = sr_get_download_url_from_db();
//                $plugins = get_site_transient ( 'update_plugins' );
		$link = $download_details['results'][0]->option_value;                                //$plugins->response [SR_PLUGIN_FILE]->package;
		
                if ( !empty( $link ) ) {
                    $current  = get_site_transient ( 'update_plugins' );
                    $r1       = sr_plugin_reset_upgrade_link ( $current, $link );
                    set_site_transient ( 'update_plugins', $r1 );
                    echo $man_download_link = " Or <a href='$link'>click here to download the latest version.</a>";
                }
	}
		
	if (! function_exists ( 'sr_display_err' )) {
		function sr_display_err($error_message) {
			echo "<div id='notice' class='error'>";
			echo _e ( '<b>Error: </b>' . $error_message );
			echo "</div>";
		}
	}
	
	if (! function_exists ('sr_display_notice')) {
		function sr_display_notice($notice) {
			echo "<div id='message' class='updated fade'>
             <p>";
			echo _e ( $notice );
			echo "</p></div>";
		}
	}
// EOF auto upgrade code
}
?>
