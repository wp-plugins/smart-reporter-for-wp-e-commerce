<?php
/*
Plugin Name: Smart Reporter for WP e-Commerce
Plugin URI: http://www.storeapps.org/smart-reporter-for-wp-e-commerce/
Description: <strong>Lite Version Installed.</strong> Store analysis like never before. 
Version: 1.6
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
if (is_admin ()) {
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
	define ( 'SR_JSON_URL', SR_PLUGIN_DIRNAME . "/sr/json.php" );
	// EOF
	
	add_action ( 'admin_notices', 'sr_admin_notices' );
	add_action ( 'admin_init', 'sr_admin_init' );
	
	function sr_admin_init() {
		$plugin_info 	= get_plugins ();
		$sr_plugin_info = $plugin_info [SR_PLUGIN_FILE];
		$ext_version 	= '4.0.1';
		
		// checking the version for WPSC plugin
		define ( 'IS_WPSC37', version_compare ( WPSC_VERSION, '3.8', '<' ) );
		define ( 'IS_WPSC38', version_compare ( WPSC_VERSION, '3.8', '>=' ) );
			
		if (IS_WPSC38) {
			wp_register_script ( 'sr_ext_all', plugins_url ( 'resources/ext/ext-all.js', __FILE__ ), array (), $ext_version );
			wp_register_script ( 'sr_main', plugins_url    ( '/sr/smart-reporter.js', __FILE__ ), array ('sr_ext_all' ), $ext_version );
			wp_register_style  ( 'sr_ext_all', plugins_url ( 'resources/css/ext-all.css', __FILE__ ), array (), $ext_version );
			wp_register_style  ( 'sr_main', plugins_url    ( '/sr/smart-reporter.css', __FILE__ ), array ('sr_ext_all' ), $sr_plugin_info ['Version'] );
		}
		
		if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr.js' )) {
			wp_register_script ( 'sr_functions', plugins_url ( '/pro/sr.js', __FILE__ ), array ('sr_main' ), $sr_plugin_info ['Version'] );
			define ( 'SRPRO', true );
		} else {
			define ( 'SRPRO', false );
		}
		
		if (SRPRO === true) {
			include ('pro/upgrade.php');
			add_action ( 'after_plugin_row_' . plugin_basename ( __FILE__ ), 'sr_plugin_row', '', 1 );
			add_action ( 'in_plugin_update_message-' . plugin_basename ( __FILE__ ), 'sr_update_notice' );
		}
	}
	
	function sr_admin_notices() {
		if (! is_plugin_active ( basename(WPSC_URL).'/wp-shopping-cart.php' )) {
			echo '<div id="notice" class="error"><p>';
			_e ( '<b>Smart Reporter</b> add-on requires <a href="http://getshopped.org/">WP e-Commerce</a> plugin. Please install and activate it.' );
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
	
	function wpsc_add_modules_sr_admin_pages($page_hooks, $base_page) {
		$page = add_submenu_page ( $base_page, 'Smart Reporter', 'Smart Reporter', 'manage_options', 'smart-reporter', 'sr_show_console' );
		
		add_action ( 'admin_print_styles-' . $page, 'sr_admin_styles' );
		add_action ( 'admin_print_scripts-' . $page, 'sr_admin_scripts' );
		$page_hooks [] = $page;
		return $page_hooks;
	}
	add_filter ( 'wpsc_additional_pages', 'wpsc_add_modules_sr_admin_pages', 10, 2 );
	
	function sr_show_console() {
		
		//set the number of days data to show in lite version.
		define ( 'SR_AVAIL_DAYS', 6);
		
		$latest_version = get_latest_version (SR_PLUGIN_FILE );
		$is_pro_updated = is_pro_updated ();
		
		if ($_GET ['action'] == 'sr-settings') {
			sr_settings_page (SR_PLUGIN_FILE);
		} else {
			$base_path = WP_PLUGIN_DIR . '/' . str_replace ( basename ( __FILE__ ), "", plugin_basename ( __FILE__ ) ) . 'sr/';
		?>
<div class="wrap">
<div id="icon-smart-reporter" class="icon32"><img alt="Smart Reporter"
	src="<?php echo SR_IMG_URL.'/logo.png'?>"></div>
<h2><?php
		echo _e ( 'Smart Reporter' );
		echo ' ';
			if (SRPRO === true) {
				echo _e ( 'Pro' );
			} else {
				echo _e ( 'Lite' );
			}
		?>
   	<p class="wrap">
	   	<span style="float: right"> <?php
				if (SRPRO === true) {
					printf ( __ ( '<a href="admin.php?page=smart-reporter&action=sr-settings">Settings</a> |
	                               <a href="%1s" target=_storeapps>Need Help?</a>' ), "http://www.storeapps.org/support" );
				} else {
					printf ( __ ( '<a href="%1s" target=_storeapps>Need Help?</a>' ), "http://www.storeapps.org/support" );
				}
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
				sr_display_notice("Please enter your license key for automatic upgrades and support to get activated. <a href=admin.php?page=smart-reporter&action=sr-settings>Enter License Key</a>");
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
printf ( __ ( "<b>Important:</b> To get the sales and sales KPI's for more than 7 days upgrade to Pro . Take a <a href='%2s' target=_livedemo> Live Demo here </a>." ), 'http://demo.storeapps.org/' );
				?></p>
</div>
<?php
}
			?>
<?php
			if (file_exists ( WPSC_FILE_PATH . '/wp-shopping-cart.php' )) {
				if (is_plugin_active ( WPSC_FOLDER.'/wp-shopping-cart.php' )) {
					require_once (WPSC_FILE_PATH . '/wp-shopping-cart.php');
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
					$error_message = 'WP e-Commerce plugin is not activated. <br /><br />Smart Reporter add-on requires WP e-Commerce plugin.';
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
	}
	
	function sr_update_notice() {
		$plugins = get_site_transient ( 'update_plugins' );
		$link = $plugins->response [SR_PLUGIN_FILE]->package;
		
		echo $man_download_link = " Or <a href='$link'>click here to download the latest version.</a>";
	
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
