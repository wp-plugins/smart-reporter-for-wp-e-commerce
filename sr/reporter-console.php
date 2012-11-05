<?php 
global $wpdb;

// to set javascript variable of file exists
$fileExists = (SRPRO === true) ? 1 : 0;
$selectedDateValue = (SRPRO === true) ? 'THIS_MONTH' : 'LAST_SEVEN_DAYS';

if (WPSC_RUNNING === true) {
	$currency_type = get_option( 'currency_type' );   //Maybe
	$wpsc_currency_data = $wpdb->get_row( "SELECT `symbol`, `symbol_html`, `code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id` = '" . $currency_type . "' LIMIT 1", ARRAY_A );
	$currency_sign = $wpsc_currency_data['symbol'];   //Currency Symbol in Html
	if ( IS_WPSC388 )	
		$orders_details_url = ADMIN_URL . "/index.php?page=wpsc-purchase-logs&c=item_details&id=";
	else
		$orders_details_url = ADMIN_URL . "/index.php?page=wpsc-sales-logs&purchaselog_id=";
} else {
	$currency_sign = get_woocommerce_currency_symbol();
}

if ($fileExists){

    if ( WPSC_RUNNING === true ) {
        $file_name =  SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php';
        $file_url =  WP_PLUGIN_URL.'/smart-reporter-for-wp-e-commerce/pro/sr.php';
    } else {
        $file_name =  SR_PLUGIN_DIR_ABSPATH. '/pro/sr-woo.php';
        $file_url =  WP_PLUGIN_URL. '/smart-reporter-for-wp-e-commerce/pro/sr-woo.php';
    }

    if ( !function_exists( 'update_site_option' ) ) {
        if ( ! defined('ABSPATH') ) {
            include_once ('../../../../wp-load.php');
        }
        include_once ABSPATH . 'wp-includes/option.php';
    }
        
    $sr_is_auto_refresh = get_site_option('sr_is_auto_refresh');
    $sr_what_to_refresh = get_site_option('sr_what_to_refresh');
    $sr_refresh_duration = get_site_option('sr_refresh_duration');
    
?>
<input type="hidden" id="sr_is_auto_refresh" value="<?php echo $sr_is_auto_refresh; ?>" />
<input type="hidden" id="sr_what_to_refresh" value="<?php echo $sr_what_to_refresh; ?>" />
<input type="hidden" id="sr_refresh_duration" value="<?php echo $sr_refresh_duration; ?>" />
<script type="text/javascript"> 
    jQuery(function(){
        if ( jQuery('input#sr_is_auto_refresh').val() == 'yes' && jQuery('input#sr_what_to_refresh').val() != 'select' ) {
            var refresh_time = Number('<?php echo $sr_refresh_duration; ?>');
            var auto_refresh = setInterval(
                function() {
                    jQuery.ajax({
                        url: '<?php echo $file_url; ?>',
                        dataType: 'html',
                        success: function( response ){
                            if ( jQuery('input#sr_what_to_refresh').val() == 'dashboard' || jQuery('input#sr_what_to_refresh').val() == 'all' ) {
                                jQuery('#reload').trigger('click');
                            }
                            if ( jQuery('input#sr_what_to_refresh').val() == 'kpi' || jQuery('input#sr_what_to_refresh').val() == 'all' ) {
                                jQuery('#wrap_sr_kpi').fadeOut('slow', function(){jQuery('#wrap_sr_kpi').html(response).fadeIn("slow");});
                            }
                        }
                    });
            }, Number(refresh_time * 60 * 1000));
        }
    });
</script>
<div id="wrap_sr_kpi">
<?php if ( file_exists ( $file_name ) ) include_once( $file_name ); ?>
</div>
<?php
}
echo "<script type='text/javascript'>
var adminUrl	      	 = '" .ADMIN_URL. "';
SR 			   		  	 =  new Object;";

if ( WPSC_RUNNING === true ) {
	echo "SR.defaultCurrencySymbol = '" .$currency_sign. "';";
} else {
	echo "SR.defaultCurrencySymbol = '" . get_woocommerce_currency_symbol() . "';";
}

echo "
var jsonURL    		  	 = '" .SR_JSON_URL. "';
var imgURL     		     = '" .SR_IMG_URL . "';
var fileExists 		  	 = '" .$fileExists. "';
var ordersDetailsLink   = '" . $orders_details_url . "';
var availableDays    	 = '" .SR_AVAIL_DAYS. "';
var selectedDateValue 	 = '" .$selectedDateValue. "';
var fileUrl 	 = '" .$file_url. "';
</script>";
?>
<br>
<div id="smart-reporter"></div>
