<?php
/*
Plugin Name: Smart Reporter for WP e-Commerce
Plugin URI: http://www.storeapps.org/smart-reporter-for-wp-e-commerce/
Description: Store analysis like never before. 
Version: 1.2
Author: Store Apps
Author URI: http://www.storeapps.org/about/
Copyright (c) 2011 Store Apps All rights reserved.
*/

//Hooks
register_activation_hook ( __FILE__, 'sr_activate' );
register_deactivation_hook ( __FILE__, 'sr_deactivate' );

/**
 * Registers a plugin function to be run when the plugin is activated.
 */
function sr_activate() {
}

/**
 * Registers a plugin function to be run when the plugin is deactivated.
 */
function sr_deactivate() {
}

/**
 * Throw an error on admin page when WP e-Commerece plugin is not activated.
 */
if (is_admin ()) {
	// BOF automatic upgrades
	include ABSPATH . 'wp-includes/pluggable.php';
	$plugin = plugin_basename ( __FILE__ );
	define ( 'SR_PLUGIN_FILE', $plugin );
	define ( 'STORE_APPS_URL', 'http://www.storeapps.org/' );
	
	define ( 'ADMIN_URL', get_admin_url () ); //defining the admin url
	define ( 'SR_PLUGIN_DIRNAME', plugins_url ( '', __FILE__ ) );
	define ( 'SR_IMG_URL', SR_PLUGIN_DIRNAME . '/resources/themes/images/' );
	
	define ( 'SR_PLUGIN_DIRNAME', plugins_url ( '', __FILE__ ) );
	define ( 'SR_JSON_URL', SR_PLUGIN_DIRNAME . "/sr/json.php" );
	// EOF
	
	add_action ( 'admin_notices', 'sr_admin_notices' );
	add_action ( 'admin_init', 'sr_admin_init' );
	
	function sr_admin_init() {
		$plugin_info = get_plugins ( '/smart-reporter' );
		$ext_version = '0.1';
		
		// checking the version for WPSC plugin
		define ( 'IS_WPSC37', version_compare ( WPSC_VERSION, '3.8', '<' ) );
		define ( 'IS_WPSC38', version_compare ( WPSC_VERSION, '3.8', '>=' ) );
			
		if (IS_WPSC38) {
			wp_register_script ( 'sr_ext_all', plugins_url ( 'resources/ext/ext-all.js', __FILE__ ), array (), $ext_version );
			wp_register_script ( 'sr_js', plugins_url ( '/sr/sr.js', __FILE__ ), array ('sr_ext_all' ), $ext_version );
			wp_register_style ( 'sr_ext_all', plugins_url ( 'resources/css/ext-all.css', __FILE__ ), array (), $ext_version );
			wp_register_style ( 'sr_js', plugins_url ( '/sr/sr.css', __FILE__ ), array ('sr_ext_all' ), $plugin_info ['Version'] );
		}
	}
	
	function sr_admin_notices() {
		if (! is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' )) {
			echo '<div id="notice" class="error"><p>';
			_e ( '<b>Smart Reporter</b> add-on requires <a href="http://getshopped.org/">WP e-Commerce</a> plugin. Please install and activate it.' );
			echo '</p></div>', "\n";
		}
	}
	
	function sr_admin_scripts() {
		wp_enqueue_script ( 'sr_js' );
	}
	
	function sr_admin_styles() {
		wp_enqueue_style ( 'sr_js' );
	}
	
	function wpsc_add_modules_sr_admin_pages($page_hooks, $base_page) {
		$page = add_submenu_page ( $base_page, 'Smart Reporter', 'Smart Reporter', 'manage_options', 'smart-reporter', 'sr_show_console' );
		
		add_action ( 'admin_print_styles-' . $page, 'sr_admin_styles' );
		add_action ( 'admin_print_scripts-' . $page, 'sr_admin_scripts' );
		$page_hooks [] = $page;
		return $page_hooks;
	}
	add_filter ( 'wpsc_additional_pages', 'wpsc_add_modules_sr_admin_pages', 10, 2 );
	
	function sr_show_console() {
		$wp_ecom_path = WP_PLUGIN_DIR . '/wp-e-commerce/';
		$base_path = WP_PLUGIN_DIR . '/' . str_replace ( basename ( __FILE__ ), "", plugin_basename ( __FILE__ ) ) . 'sr/';
		?>
<div class="wrap">
<div id="icon-smart-manager" class="icon32"><img alt="Smart Reporter"
	src="<?php echo SR_IMG_URL.'/logo.png'?>"></div>
<h2><?php
		echo _e ( 'Smart Reporter' );
		?>
   	<p class="wrap"><span style="float: right"> <?php
		printf ( __ ( '<a href="%1s" target=_storeapps>Need Help?</a>' ), "http://www.storeapps.org/support" );
	?>
	</span><?php
		echo __ ( 'Store analysis like never before.' );
	?></p>
</h2>
</div>
<?php
			$error_message = '';
			if (file_exists ( $wp_ecom_path . 'wp-shopping-cart.php' )) {
				if (is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' )) {
					require_once ($wp_ecom_path . 'wp-shopping-cart.php');
					if (IS_WPSC38) {
						if (file_exists ( $base_path . 'reporter-console.php' )) {
							include_once ($base_path . 'reporter-console.php');
							return;
						} else {
							$error_message = 'A required Smart Reporter file is missing. Can\'t continue.';
						}

					} else {
						$error_message = 'Smart Reporter currently works only with WP e-Commerce 3.8 or above.';
					}
				} else {
					$error_message = 'WP e-Commerce plugin is not activated. <br /><br />Smart Manager add-on requires WP e-Commerce plugin.';
				}
			} else {
				$error_message = '<b>Smart Reporter</b> add-on requires <a href="http://getshopped.org/">WP e-Commerce</a> plugin to do its job. Please install and activate it.';
			}
			if ($error_message != '') {

				sr_display_err ( $error_message );
				?>
<?php
			}
	}
	function sr_display_err ($error_message) {
		echo "<div id='notice' class='error'>";
		echo _e ( '<b>Error: </b>' . $error_message );
		echo "</div>";
	}

	function sr_display_notice($notice) {
		echo "<div id='message' class='updated fade'>
             <p>";
		echo _e ( $notice );
		echo "</p></div>";
	}

// EOF auto upgrade code
}
?>