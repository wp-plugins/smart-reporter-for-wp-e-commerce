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
		if ( file_exists ( SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php' ) ) include_once( SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php' );
	} else {
		if ( file_exists ( SR_PLUGIN_DIR_ABSPATH. '/pro/sr-woo.php' ) ) include_once( SR_PLUGIN_DIR_ABSPATH. '/pro/sr-woo.php' );
	}
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
</script>";
?>
<br>
<div id="smart-reporter"></div>
