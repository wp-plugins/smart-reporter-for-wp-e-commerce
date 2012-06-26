<?php
include_once ('../../../../wp-load.php');
include_once ('../../../../wp-includes/wp-db.php');
include_once (ABSPATH . WPINC . '/functions.php');

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
if (file_exists ( '../pro/sr-woo.php' )){
	define ( 'SRPRO', true );
} else {
	define ( 'SRPRO', false );
}

function arr_init($arr_start, $arr_end, $category = '') {
	global $cat_rev, $months, $order_arr;

	for($i = $arr_start; $i <= $arr_end; $i ++) {
		$key = ($category == 'month') ? $months [$i - 1] : $i;
		$cat_rev [$key] = 0;
	}
}

function get_grid_data( $select, $from, $where, $where_date, $group_by, $search_condn, $order_by ) {
	global $wpdb, $cat_rev, $months, $order_arr;
		
		$woo_default_image = WP_PLUGIN_URL . '/smart-reporter-for-wp-e-commerce/resources/themes/images/woo_default_image.png';
		$query = "$select $from $where $where_date $group_by $search_condn $order_by ";
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
				$grid_data [$count] ['sales']    = '';
				$grid_data [$count] ['discount'] = '';
				$grid_data [$count] ['products'] = 'All Products';
				$grid_data [$count] ['period']   = 'selected period';
				$grid_data [$count] ['category'] = 'All Categories';
				$grid_data [$count] ['id'] 	     = '';
				$grid_data [$count] ['quantity'] = 0;
				$grid_data [$count] ['image'] = $woo_default_image;		//../wp-content/plugins/wp-e-commerce/wpsc-theme/wpsc-images/noimage.png

				foreach ( $results as $result ) {
					$grid_data [$count] ['quantity'] = $grid_data[$count] ['quantity'] + $result ['quantity'];
					$grid_data [$count] ['sales'] = $grid_data[$count] ['sales'] + $result ['sales'];
					$grid_data [$count] ['discount'] = $grid_data[$count] ['discount'] + $result ['discount'];
				}
				$count++;
			
			foreach ( $results as $result ) {
				$grid_data [$count] ['products'] = $result ['products'];
				$grid_data [$count] ['period']   = $result ['period'];
				$grid_data [$count] ['sales']    = $result ['sales'];
				$grid_data [$count] ['discount'] = $result ['discount'];
				$grid_data [$count] ['category'] = $result ['category'];
				$grid_data [$count] ['id'] 	 	 = $result ['id'];
				$grid_data [$count] ['quantity'] = $result ['quantity'];
				$thumbnail = isset( $result ['thumbnail'] ) ? wp_get_attachment_image_src( $result ['thumbnail'], 'admin-product-thumbnails' ) : '';
				$grid_data [$count] ['image']    = ( $thumbnail[0] != '' ) ? $thumbnail[0] : $woo_default_image;
				$count++;
			}
				
			$encoded ['gridItems']      = $grid_data;
			$encoded ['period_div'] 	= $parts ['category'];
			$encoded ['gridTotalCount'] = count($grid_data);
		}

	return $encoded;
}

function get_graph_data( $product_id, $where_date, $parts ) {
	global $wpdb, $cat_rev, $months, $order_arr;
	
	$encoded = get_last_few_order_details( $product_id, $where_date );

		$select  = "SELECT SUM( order_item.sales ) AS sales,
					DATE_FORMAT(posts.`post_date`, '{$parts ['abbr']}') AS period
				   ";
		
		$from = " FROM {$wpdb->prefix}sr_woo_order_items AS order_item
			  	  LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
				";
		
		$where = ' WHERE 1 ';
		
		$group_by = " GROUP BY period";
	
		if ( isset ( $product_id ) && $product_id != 0 ) {
			$where 	   .= " AND order_item.product_id = $product_id ";
		}
		
		$query = "$select $from $where $where_date $group_by ";
		
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

function get_last_few_order_details( $product_id, $where_date ) {
	global $wpdb, $cat_rev, $months, $order_arr;
		
		$select = "SELECT order_item.order_id AS order_id,
						  posts.post_date AS date,
						  GROUP_CONCAT( distinct postmeta.meta_value
								ORDER BY postmeta.meta_id 
								SEPARATOR ' ' ) AS cname,
						  ( SELECT post_meta.meta_value FROM {$wpdb->prefix}postmeta AS post_meta WHERE post_meta.post_id = order_item.order_id AND post_meta.meta_key = '_order_total' ) AS totalprice
				  ";
		
		$from = " FROM {$wpdb->prefix}sr_woo_order_items AS order_item
			  	  LEFT JOIN {$wpdb->prefix}posts AS posts ON ( posts.ID = order_item.order_id )
			  	  LEFT JOIN {$wpdb->prefix}postmeta AS postmeta ON ( order_item.order_id = postmeta.post_id AND postmeta.meta_key IN ( '_billing_first_name', '_billing_last_name' ) )
				";
		
		$where = ' WHERE 1 ';
		
		$order_by = "ORDER BY date DESC";
		
		$limit = "limit 0,5";
		
		if ( isset( $product_id ) ) $group_by  = "GROUP BY order_id";
		
		if ( isset ( $product_id ) && $product_id != 0 ) {
			$where 	   .= " AND order_item.product_id = $product_id ";
		}
		
		$query = "$select $from $where $where_date $group_by $order_by $limit";
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
				$order_data [$cnt] ['purchaseid'] = $result ['order_id'];
				$order_data [$cnt] ['date']       = date( "d-M-Y",strtotime( $result ['date'] ) ); 
				$order_data [$cnt] ['totalprice'] = woocommerce_price( $result ['totalprice'] );
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
		// date('Y-m-d H:i:s',(int)strtotime($_POST ['fromDate']))		$from ['date']		$to['date']
		if (SRPRO == true){
			$where_date = " AND (posts.`post_date` between '" . date('Y-m-d H:i:s',$from ['date']) . "' AND '" . date('Y-m-d H:i:s',$to['date']) . "')";
		}else{
			$diff = 86400 * 7;
			if ( (( $from ['date'] - $to ['date'] ) <= $diff ) )
			$where_date = " AND (posts.`post_date` between '" . date('Y-m-d H:i:s',$from ['date']) . "' AND '" . date('Y-m-d H:i:s',$to['date']) . "')";
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
	
	$static_select = "SELECT order_item.product_id AS id,
					 order_item.product_name AS products,
					 category,
					 SUM( order_item.quantity ) AS quantity,
					 SUM( order_item.sales ) AS sales,
					 SUM( order_item.discount ) AS discount,
					 image_postmeta.meta_value AS thumbnail
					";
		
	$from = " FROM {$wpdb->prefix}sr_woo_order_items AS order_item
			  LEFT JOIN {$wpdb->prefix}posts AS products ON ( products.id = order_item.product_id )
			  LEFT JOIN ( SELECT GROUP_CONCAT(wt.name SEPARATOR ', ') AS category, wtr.object_id
					FROM  {$wpdb->prefix}term_relationships AS wtr  	 
					JOIN {$wpdb->prefix}term_taxonomy AS wtt ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id and taxonomy = 'product_cat')
					JOIN {$wpdb->prefix}terms AS wt ON (wtt.term_id = wt.term_id)
					GROUP BY wtr.object_id) AS prod_categories on (products.id = prod_categories.object_id OR products.post_parent = prod_categories.object_id)
			  LEFT JOIN {$wpdb->prefix}postmeta as image_postmeta ON (products.ID = image_postmeta.post_id 
					AND image_postmeta.meta_key = '_thumbnail_id')
			  LEFT JOIN {$wpdb->prefix}posts as posts ON ( posts.ID = order_item.order_id )
			  ";
		
	$where = " WHERE products.post_type IN ('product', 'product_variation') ";
		
	$group_by = " GROUP BY order_item.product_id ";
		
	$order_by = " ORDER BY sales DESC ";
	
	if (isset ( $_GET ['searchText'] ) && $_GET ['searchText'] != '') {
		$search_on = $wpdb->_real_escape ( trim ( $_GET ['searchText'] ) );
		$search_ons = explode( ' ', $search_on );
		if ( is_array( $search_ons ) ) {	
			$search_condn = " HAVING ";
			foreach ( $search_ons as $search_on ) {
				$search_condn .= " order_item.product_name LIKE '%$search_on%' 
								   OR prod_categories.category LIKE '%$search_on%' 
								   OR order_item.product_id LIKE '%$search_on%'
								   OR";
			}
			$search_condn = substr( $search_condn, 0, -2 );
		} else {
			$search_condn = " HAVING order_item.product_name LIKE '%$search_on%' 
								   OR prod_categories.category LIKE '%$search_on%' 
								   OR order_item.product_id LIKE '%$search_on%'
						";
		}
		
	}
	
	if ($_GET ['cmd'] == 'gridGetData') {
		
		$encoded = get_grid_data( $static_select, $from, $where, $where_date, $group_by, $search_condn, $order_by );
		
	} else if ($_GET ['cmd'] == 'getData') {

		$encoded = get_graph_data( $_GET ['id'], $where_date, $parts );
		
	}
}
echo json_encode ( $encoded );
?>