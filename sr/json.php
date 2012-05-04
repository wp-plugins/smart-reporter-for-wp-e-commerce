<?php
include_once ('../../../../wp-load.php');
include_once ('../../../../wp-includes/wp-db.php');
include_once (ABSPATH . WPINC . '/functions.php');

// for delete logs.
require_once ('../../' . WPSC_FOLDER . '/wpsc-includes/purchaselogs.class.php');

$del = 3;
$result  = array ();
$encoded = array ();
$months  = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
$cat_rev = array ();

global $wpdb;

if (isset ( $_GET ['start'] ))
	$offset = $_GET ['start'];
else
	$offset = 0;

if (isset ( $_GET ['limit'] ))
	$limit = $_GET ['limit'];

// For pro version check if the required file exists
if (file_exists ( '../pro/sr.php' )){
	define ( 'SRPRO', true );
} else {
	define ( 'SRPRO', false );
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

function arr_init($arr_start, $arr_end, $category = '') {
	global $cat_rev, $months, $order_arr;

	for($i = $arr_start; $i <= $arr_end; $i ++) {
		$key = ($category == 'month') ? $months [$i - 1] : $i;
		$cat_rev [$key] = 0;
	}
}

function get_grid_data( $select, $from, $where, $order_by ) {
	global $wpdb, $cat_rev, $months, $order_arr;
	
		$wpsc_default_image = WP_PLUGIN_URL . '/wp-e-commerce/wpsc-theme/wpsc-images/noimage.png';
		$group_by   = " GROUP BY prodid";
		$query = "$select $from $where $group_by $order_by ";

		$results 	= $wpdb->get_results ( $query, 'ARRAY_A' ); 
		$num_rows   = $wpdb->num_rows;
		$no_records = $num_rows;
			
		if ($no_records == 0) {
			$encoded ['gridItems'] 		= '';
			$encoded ['gridTotalCount'] = '';
			$encoded ['msg']			= 'No records found';
		} else {
			$count = 0 ;
			$grid_data = array();
			$total = get_total_sales_and_discounts_wpsc();
			if ($_GET ['searchText'] == '') {
				$grid_data [$count] ['sales']    = '';
				$grid_data [$count] ['products'] = 'All Products';
				$grid_data [$count] ['period']   = 'selected period';
				$grid_data [$count] ['category'] = 'All Categories';
				$grid_data [$count] ['id'] 	     = '';
				$grid_data [$count] ['quantity'] = 0;
				$grid_data [$count] ['image'] = $wpsc_default_image;		//../wp-content/plugins/wp-e-commerce/wpsc-theme/wpsc-images/noimage.png

				foreach ( $results as $result ) {
					$grid_data [$count] ['sales'] = $grid_data[$count] ['sales'] + $result ['sales'];
					$grid_data [$count] ['quantity'] = $grid_data[$count] ['quantity'] + $result ['quantity'];
				}
				$count++;
			}
			
			foreach ( $results as $result ) {
				$grid_data [$count] ['products'] = $result ['products'];
				$grid_data [$count] ['period']   = $result ['period'];
				$grid_data [$count] ['sales']    = $result ['sales'];
				$grid_data [$count] ['category'] = $result ['category'];
				$grid_data [$count] ['id'] 	 	 = $result ['id'];
				$grid_data [$count] ['quantity'] = $result ['quantity'];
				$grid_data [$count] ['image']    = isset( $result ['image'] ) ? $result ['image'] : $wpsc_default_image;
				$count++;
			}			
			
			$encoded ['gridItems']      = $grid_data;
			$encoded ['period_div'] 	= $parts ['category'];
			$encoded ['gridTotalCount'] = count($grid_data);
		}

	return $encoded;
}

function get_graph_data( $product_id, $select, $from, $group_by, $where, $parts ) {
	global $wpdb, $cat_rev, $months, $order_arr;
	
	$encoded = get_last_few_order_details( $product_id, $select, $from, $group_by, $where );
	
		$select  .= " ,FROM_UNIXTIME(wtpl.`date`, '{$parts ['abbr']}') AS period";
		$group_by = " GROUP BY period";
	
		if (isset ( $product_id ) && $product_id != 0) {
			$where 	   .= " AND prodid = $product_id ";
		}
		
		$query = "$select $from $where $group_by ";
		
		$results 	= $wpdb->get_results ( $query, 'ARRAY_A' );
		$num_rows   = $wpdb->num_rows;
		$no_records = ($num_rows != 0) ? count ( $cat_rev ) : 0;

		if ($no_records != 0) {
			foreach ( $results as $result ) { // put within condition
				$cat_rev [$result['period']]  = $result ['sales'];
			}
			
			foreach ( $cat_rev as $mon => $rev ) {
				$record ['period'] = $mon;
				$record ['sales'] = $rev;
				$records [] = $record;
			}
		}
		
		if ($no_records == 0) {
			$encoded ['graph'] ['items'] = '';
			$encoded ['graph'] ['totalCount'] = 0;
		} else {
			$encoded ['graph'] ['items'] = $records;
			$encoded ['graph'] ['totalCount'] = count($cat_rev);
		}
	
	return $encoded;
}

function get_last_few_order_details( $product_id, $select, $from, $group_by, $where ) {
	global $wpdb, $cat_rev, $months, $order_arr;
	
		$select .= ",wtcc.purchaseid as PurchaseID,wtpl.date as date,wtpl.totalprice as totalprice";
		$select .= ",(select concat(a.value,' ',b.value) from {$wpdb->prefix}wpsc_submited_form_data as a 
					Join {$wpdb->prefix}wpsc_submited_form_data as b
					where a.form_id=(select id from {$wpdb->prefix}wpsc_checkout_forms where unique_name like 'billingfirstname')
					and b.form_id=(select id from {$wpdb->prefix}wpsc_checkout_forms where unique_name like 'billinglastname')
					and a.log_id=PurchaseID and b.log_id=PurchaseID) as cname";
		
		$order_by = "ORDER BY date DESC";
		$limit = "limit 0,5";
		
		if ( isset( $product_id ) ) $group_by  .= "GROUP BY PurchaseID";
		
		if (isset ( $product_id ) && $product_id != 0) {
			$where 	   .= " AND prodid = $product_id ";
		}
		
		$query = "$select $from $where $group_by $order_by $limit";
		$results 	= $wpdb->get_results ( $query, 'ARRAY_A' );
		$num_rows   = $wpdb->num_rows;
		$no_records = $num_rows;
			
		if ($no_records == 0) {
			$encoded ['orderDetails'] ['order'] 		= '';
			$encoded ['orderDetails'] ['orderTotalCount'] = 0;
		}  else {			
			$cnt = 0;
			$order_data = array();
			foreach ( $results as $result ) { // put within condition	
				$order_data [$cnt] ['purchaseid'] = $result ['PurchaseID'];
				$order_data [$cnt] ['date']       = date("d-M-Y",$result ['date']); 
				$order_data [$cnt] ['totalprice'] = wpsc_currency_display($result ['totalprice']);
				$order_data [$cnt] ['cname']      = $result ['cname'];	
				$orders [] = $order_data [$cnt];				
				$cnt++;
			}	
		
			$encoded ['orderDetails'] ['order'] = $orders;
			$encoded ['orderDetails'] ['orderTotalCount'] = count($orders);
		}
		
	return $encoded;
}

if (isset ( $_GET ['cmd'] ) && (($_GET ['cmd'] == 'getData') || ($_GET ['cmd'] == 'gridGetData'))) {

	if (isset ( $_GET ['fromDate'] )) {
		$from ['date'] = strtotime ( $_GET ['fromDate'] );
		$to ['date'] = strtotime ( $_GET ['toDate'] );
	 
		if ($to ['date'] == 0) {
			$to ['date'] = strtotime ( 'today' );
		}
		// move it forward till the end of day
		$to ['date'] += 86399;

		// Swap the two dates if to_date is less than from_date
		if ($to ['date'] < $from ['date']) {
			$temp = $to ['date'];
			$to ['date'] = $from ['date'];
			$from ['date'] = $temp;
		}

		if (SRPRO == true){
			$where = "WHERE (wtpl.`date` between '{$from ['date']}' AND '{$to['date']}')";
		}else{
			$diff = 86400 * 7;
			if ( (( $from ['date'] - $to ['date'] ) <= $diff ) )
			$where = "WHERE (wtpl.`date` between '{$from ['date']}' AND '{$to['date']}')";
		}

		//BOF bar graph calc

		$frm ['yr'] = date ( "Y", $from ['date'] );
		$to ['yr'] = date ( "Y", $to ['date'] );

		$frm ['mon'] = date ( "n", $from ['date'] );
		$to ['mon'] = date ( "n", $to ['date'] );

		$frm ['week'] = date ( "W", $from ['date'] );
		$to ['week'] = date ( "W", $to ['date'] );

		$frm ['day'] = date ( "j", $from ['date'] );
		$to ['day'] = date ( "j", $to ['date'] );

		$parts ['category'] = '';
		$parts ['no'] = 0;

		if ($frm ['yr'] == $to ['yr']) {
			if ($frm ['mon'] == $to ['mon']) {

				if ($frm ['week'] == $to ['week']) {
					if ($frm ['day'] == $to ['day']) {
						$diff = $to ['date'] - $from ['date'];
						$parts ['category'] = 'hr';
						$parts ['no'] = 23;
						$parts ['abbr'] = '%k';

						arr_init ( 0, $parts ['no'],'hr' );
					} else {
						$parts ['category'] = 'day';
						$parts ['no'] = date ( 't', $from ['date'] );
						$parts ['abbr'] = '%e';

						arr_init ( 1, $parts ['no'] );
					}
				} else {
					$parts ['category'] = 'day';
					$parts ['no'] = date ( 't', $from ['date'] );
					$parts ['abbr'] = '%e';

					arr_init ( 1, $parts ['no'] );
				}
			} else {
				$parts ['category'] = 'month';
				$parts ['no'] = $to ['mon'] - $frm ['mon'];
				$parts ['abbr'] = '%b';

				arr_init ( $frm ['mon'], $to ['mon'], $parts ['category'] );
			}
		} else {
			$parts ['category'] = 'year';
			$parts ['no'] = $to ['yr'] - $frm ['yr'];
			$parts ['abbr'] = '%Y';

			arr_init ( $frm ['yr'], $to ['yr'] );
		}
		// EOF
	}
	
	$static_select  = " SELECT prodid as id, sum(quantity) as quantity, sum(price * quantity) as sales, wtcc.name as products";	
	$from   = "  FROM 		{$wpdb->prefix}wpsc_cart_contents AS wtcc
                 	   JOIN {$wpdb->prefix}wpsc_purchase_logs AS wtpl ON (wtcc.`purchaseid` = wtpl.`id`)
                  LEFT JOIN {$wpdb->prefix}posts              AS p    ON (p.ID = prodid)";
		
	$order_by = "ORDER BY sales DESC";
	
	//To get categories
	$static_select .= " , (   SELECT GROUP_CONCAT( DISTINCT wt.name)
	                   FROM 		 	{$wpdb->prefix}posts
			                  LEFT JOIN {$wpdb->prefix}term_relationships AS wtr  ON (if(post_parent = 0,ID,post_parent) = wtr.object_id)
			                  LEFT JOIN {$wpdb->prefix}term_taxonomy      AS wtt  ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id AND taxonomy = 'wpsc_product_category')
			                  LEFT JOIN {$wpdb->prefix}terms              AS wt   ON (wtt.term_id = wt.term_id)
	                   WHERE ID = prodid
	                   GROUP BY ID
                  	) AS category";
	$static_select .= ", (select rp.guid from {$wpdb->prefix}posts rp, {$wpdb->prefix}posts rp1
					where rp.post_parent=rp1.ID and rp.post_mime_type!='' and rp1.ID=prodid
				  ) as image";
	
	if (isset ( $_GET ['searchText'] ) && $_GET ['searchText'] != '') {
		$search_on = $wpdb->_real_escape ( trim ( $_GET ['searchText'] ) );
		$where .= " AND (wtcc.name LIKE '%$search_on%' 
            			 OR prodid in (                         
                         SELECT prodid 
                         FROM 	   {$wpdb->prefix}wpsc_cart_contents
                              JOIN {$wpdb->prefix}posts              AS p    ON (p.ID = prodid)
                         LEFT JOIN {$wpdb->prefix}term_relationships AS wtr  ON (if(p.post_parent = 0,prodid,post_parent) = wtr.object_id)
                         LEFT JOIN {$wpdb->prefix}term_taxonomy      AS wtt  ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id AND taxonomy = 'wpsc_product_category')
                         LEFT JOIN {$wpdb->prefix}terms              AS wt   ON (wtt.term_id = wt.term_id)
                         WHERE wt.name LIKE '%$search_on%'
              						)
		             	)";
	}

	if ($_GET ['cmd'] == 'gridGetData') {
		
		$encoded = get_grid_data( $static_select, $from, $where, $order_by );
		
	} else if ($_GET ['cmd'] == 'getData') {

		$encoded = get_graph_data( $_GET ['id'], $static_select, $from, $group_by, $where, $parts );
		
	}
}
echo json_encode ( $encoded );
?>