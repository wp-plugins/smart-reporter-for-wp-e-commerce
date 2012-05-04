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

if ( ! function_exists( 'get_total_sales_and_discounts_wpsc' ) ) {
	function get_total_sales_and_discounts_wpsc () {
		global $wpdb;
		$total = array();
		$query = "SELECT sum(totalprice) as actual_total_sales,
						 ( SELECT sum( price * quantity ) FROM {$wpdb->prefix}wpsc_cart_contents ) as total_sales_including_discounts 
						 FROM {$wpdb->prefix}wpsc_purchase_logs";
		$results = $wpdb->get_results ( $query );
		$total ['sales'] = (double)$results[0]->actual_total_sales;
		$total ['discount'] = (double)$results[0]->total_sales_including_discounts - (double)$results[0]->actual_total_sales;
		return $total;
	}
}

if ( ! function_exists( 'get_total_sales_and_discounts_woo' ) ) {
	function get_total_sales_and_discounts_woo ( $where_date = '' ) {
		global $wpdb;
		$total = array();
		// Query used by woocommerce
		$order_totals = $wpdb->get_row("
			SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts
			
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )
	
			WHERE 	meta.meta_key 		= '_order_total'
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			$where_date
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('completed', 'processing', 'on-hold')
		");
		$total ['sales'] = $order_totals->total_sales;
		
		// Query used by woocommerce
		$total ['discount'] = $wpdb->get_var("
			SELECT SUM(meta.meta_value) AS total_sales FROM {$wpdb->posts} AS posts
			
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
			LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
			LEFT JOIN {$wpdb->terms} AS term USING( term_id )
	
			WHERE 	meta.meta_key 		IN ('_order_discount','_cart_discount')
			AND 	posts.post_type 	= 'shop_order'
			AND 	posts.post_status 	= 'publish'
			$where_date
			AND 	tax.taxonomy		= 'shop_order_status'
			AND		term.slug			IN ('completed', 'processing', 'on-hold')
		");
		
		return $total;
	}
}

if (WPSC_RUNNING === true)
	$total = get_total_sales_and_discounts_wpsc();
else
	$total = get_total_sales_and_discounts_woo();

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
var totalSales 			 = " . $total ['sales'] . ";
var totalDiscount 		 = " . $total ['discount'] . ";
</script>";
?>
<br>
<div id="smart-reporter"></div>
