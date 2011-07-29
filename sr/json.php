<?php
include_once ('../../../../wp-load.php');
include_once ('../../../../wp-includes/wp-db.php');
include_once (ABSPATH . WPINC . '/functions.php');

// for delete logs.
require_once ('../../' . WPSC_FOLDER . '/wpsc-includes/purchaselogs.class.php');

$del = 3;
$result = array ();
$encoded = array ();
global $wpdb;

if (isset ( $_GET ['start'] ))
	$offset = $_GET ['start'];
else
	$offset = 0;

if (isset ( $_GET ['limit'] ))
	$limit = $_GET ['limit'];

	// Searching a product in the grid
//if (isset ( $_POST ['cmd'] ) && $_POST ['cmd'] == 'getData') {
if (isset ( $_GET ['cmd'] ) && (($_GET ['cmd'] == 'getData') || ($_GET ['cmd'] == 'gridGetData'))) {
	global $wpdb;
	$months = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
	
	$cat_rev = array ();
	function arr_init($arr_start, $arr_end, $category = '') {
		global $cat_rev, $months;
		
		for($i = $arr_start; $i <= $arr_end; $i ++) {
			$key = ($category == 'month') ? $months [$i - 1] : $i;
			$cat_rev [$key] = 0;
		
		}
	}
	
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
		
		$where = "WHERE (wtpl.`date` between '{$from ['date']}' AND '{$to['date']}') ";
		
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
	
	$select  = " SELECT prodid as id, sum(price * quantity) as sales, wtcc.name as products";
		
	$from    = " FROM {$wpdb->prefix}wpsc_cart_contents as wtcc
			     JOIN {$wpdb->prefix}wpsc_purchase_logs as wtpl on (wtcc.`purchaseid` = wtpl.`id`)";
		
	$limit    = " LIMIT " . $offset . "," . $limit . "";
	$where   .= "AND wtpl.`processed` >= 2";
	$order_by = "ORDER BY sales DESC";
	
	//To get categories 
	$select .= " , GROUP_CONCAT( DISTINCT wt.name ) AS category";
	$from   .= "  	   JOIN {$wpdb->prefix}posts 		      AS p    ON (p.ID = prodid)
					   JOIN {$wpdb->prefix}term_relationships AS wtr  ON (if(post_parent = 0,prodid,post_parent) = wtr.object_id)
             	  LEFT JOIN {$wpdb->prefix}term_taxonomy      AS wtt  ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id AND taxonomy = 'wpsc_product_category')
             	  LEFT JOIN {$wpdb->prefix}terms 			  AS wt   ON (wtt.term_id = wt.term_id)";
	
	if (isset ( $_GET ['searchText'] ) && $_GET ['searchText'] != '') {
		$search_on = mysql_escape_string ( trim ( $_GET ['searchText'] ) );
		$where .= " AND (wtcc.name LIKE '%$search_on%' 
            			 OR prodid in (                         
                         SELECT prodid 
                         FROM 	   {$wpdb->prefix}wpsc_cart_contents
                              JOIN {$wpdb->prefix}posts              AS p    ON (p.ID = prodid)
                              JOIN {$wpdb->prefix}term_relationships AS wtr  ON (if(p.post_parent = 0,prodid,post_parent) = wtr.object_id)
                         LEFT JOIN {$wpdb->prefix}term_taxonomy      AS wtt  ON (wtr.term_taxonomy_id = wtt.term_taxonomy_id AND taxonomy = 'wpsc_product_category')
                         LEFT JOIN {$wpdb->prefix}terms              AS wt   ON (wtt.term_id = wt.term_id)
                         WHERE wt.name LIKE '%$search_on%'
              						)
		             	)";
	}

	if ($_GET ['cmd'] == 'gridGetData') {
		
		$group_by   = " GROUP BY prodid";		
		$query 	    = "$select $from $where $group_by $order_by ";
		
		$results 	= $wpdb->get_results ( $query, 'ARRAY_A' );		
		$num_rows   = $wpdb->num_rows;
		$no_records = $num_rows;
			
		if ($no_records == 0) {
			$encoded ['gridItems'] 		= '';			
			$encoded ['gridTotalCount'] = '';
			$encoded ['msg']			= 'No Records Found';
		} else {
			
			$count = 0 ;
			$grid_data = array();
			if ($_GET ['searchText'] == '') {
				$grid_data [$count] ['sales']    = 0;
				$grid_data [$count] ['products'] = 'All Products';
				$grid_data [$count] ['period']   = 'selected period';
				$grid_data [$count] ['category'] = 'All Categories';
				$grid_data [$count] ['id'] 	     = '';

				foreach ( $results as $result ) {
					$grid_data [$count] ['sales'] = $grid_data[$count] ['sales'] + $result ['sales'];
				}
				$count++;
			}
			
			foreach ( $results as $result ) { 				
				$grid_data [$count] ['products'] = $result ['products'];
				$grid_data [$count] ['period']   = $result ['period'];
				$grid_data [$count] ['sales']    = $result ['sales'];
				$grid_data [$count] ['category'] = $result ['category'];
				$grid_data [$count] ['id'] 	 	 = $result ['id'];
				$count++;
			}
			
			$encoded ['gridItems']      = $grid_data;
			$encoded ['period_div'] 	= $parts ['category'];
			$encoded ['gridTotalCount'] = count($grid_data);
		}
	} else {
		$select  .= " ,FROM_UNIXTIME(wtpl.`date`, '{$parts ['abbr']}') AS period";		
		$group_by = " GROUP BY period";
		
		if (isset ( $_GET ['id'] ) && $_GET ['id'] != 0) {
			$group_by  .= ", prodid";
			$product_id = mysql_escape_string ( $_GET ['id'] );
			$where 	   .= " AND prodid = $product_id ";
		}
		
		$query = "$select $from $where $group_by";
		
		$results 	= $wpdb->get_results ( $query, 'ARRAY_A' );		
		$num_rows   = $wpdb->num_rows;
		$no_records = ($num_rows != 0)? count ( $cat_rev ) : 0;

		if ($no_records != 0) {
			foreach ( $results as $result ) { // put within condition
				$cat_rev [$result['period']] = $result ['sales'];
			}
			
			foreach ( $cat_rev as $mon => $rev ) {
				$record ['period'] = $mon;
				$record ['sales'] = $rev;
				$records [] = $record;
			}
		}
		
		if ($no_records == 0) {
			$encoded ['items'] = '';
			$encoded ['totalCount'] = 0;
			$encoded ['msg'] = 'No Records Found';
		} else {
			$encoded ['items'] = $records;
			$encoded ['totalCount'] = count($cat_rev);
		}		
	}
}

echo json_encode ( $encoded );
?>