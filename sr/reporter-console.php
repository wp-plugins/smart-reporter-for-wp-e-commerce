<?php 

if ( ! defined( 'ABSPATH' ) || !is_user_logged_in() || !is_admin() ) {
    exit; // Exit if accessed directly
}

global $wpdb, $_wp_admin_css_colors, $sr_json_file_nm, $sr_text_domain;

if ( !function_exists('sr_add_social_links') ) {
    function sr_add_social_links() {

        $social_link = '<style type="text/css">
                            div.sr_social_links > iframe {
                                max-height: 1.5em;
                                vertical-align: middle;
                                padding: 5px 2px 0px 0px;
                            }
                            iframe[id^="twitter-widget"] {
                                max-width: 10.3em;
                            }
                            iframe#fb_like_sr {
                                max-width: 6em;
                            }
                            span > iframe {
                                vertical-align: middle;
                            }
                        </style>';
        $social_link .= '<a href="https://twitter.com/storeapps" class="twitter-follow-button" data-show-count="true" data-dnt="true" data-show-screen-name="false">Follow</a>';
        $social_link .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
        $social_link .= '<iframe id="fb_like_sr" src="http://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FStore-Apps%2F614674921896173&width=100&layout=button_count&action=like&show_faces=false&share=false&height=21"></iframe>';
        $social_link .= '<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: en_US</script><script type="IN/FollowCompany" data-id="3758881" data-counter="right"></script>';

        return $social_link;

    }
}

// to set javascript variable of file exists
// $fileExists = ((defined(SRPRO)) && SRPRO === true) ? 1 : 0;

$orders_details_url = '';

$sr_text_domain = ( defined('SR_TEXT_DOMAIN') ) ? SR_TEXT_DOMAIN : 'smart-reporter-for-wp-e-commerce';

if (defined('SR_WPSC_RUNNING') && SR_WPSC_RUNNING === true) {
    $currency_type = get_option( 'currency_type' );   //Maybe
    $wpsc_currency_data = $wpdb->get_row( "SELECT `symbol`, `symbol_html`, `code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id` = '" . $currency_type . "' LIMIT 1", ARRAY_A );
    $currency_sign = $wpsc_currency_data['symbol'];   //Currency Symbol in Html
    if ( SR_IS_WPSC388 )   
        $orders_details_url = ADMIN_URL . "index.php?page=wpsc-purchase-logs&c=item_details&id=";
    else
        $orders_details_url = ADMIN_URL . "index.php?page=wpsc-sales-logs&purchaselog_id=";
}

// to set javascript variable of file exists
$fileExists = (defined('SRPRO') && SRPRO === true) ? 1 : 0;
$selectedDateValue = (defined('SRPRO') && SRPRO === true) ? 'THIS_MONTH' : 'LAST_SEVEN_DAYS';

$sr_const = array();

//Global Variables
$sr_const['currency_symbol']  = defined('SR_CURRENCY_SYMBOL') ? SR_CURRENCY_SYMBOL : '';
$sr_const['currency_pos']     = defined('SR_CURRENCY_POS') ? SR_CURRENCY_POS : '';
$sr_const['decimal_places']   = defined('SR_DECIMAL_PLACES') ? SR_DECIMAL_PLACES : 2;
$sr_const['img_up_green']     = defined('SR_IMG_UP_GREEN') ? SR_IMG_UP_GREEN : '';
$sr_const['img_up_red']       = defined('SR_IMG_UP_RED') ? SR_IMG_UP_RED : '';
$sr_const['img_down_red']     = defined('SR_IMG_DOWN_RED') ? SR_IMG_DOWN_RED : '';
$sr_const['is_woo22']         = defined('SR_IS_WOO22') ? SR_IS_WOO22 : '';
$sr_const['file_nm']          = defined('SR_JSON_FILE_NM') ? SR_JSON_FILE_NM : '';
$sr_const['img_url']          = defined('SR_IMG_URL') ? SR_IMG_URL : '';
$sr_const['num_format']       = defined('SR_NUMBER_FORMAT') ? SR_NUMBER_FORMAT : 0;
$sr_const['security']         = defined('SR_NONCE') ? SR_NONCE : '';


$sr_daily_widget_data = (defined('sr_daily_widget_data')) ? json_decode(sr_daily_widget_data,true) : array();

if (!empty($sr_is_woo22) && $sr_is_woo22 == "true") {
    // $sr_woo_order_search_url = "&source=sr&post_status=all&post_type=shop_order&action=-1&m=0&paged=1&mode=list&action2=-1";
    $sr_woo_order_search_url = "&source=sr&post_status=all&post_type=shop_order&action=-1&m=0&paged=1&mode=list&action2=-1";
} else {
    $sr_woo_order_search_url = "&source=sr&post_status=all&post_type=shop_order&action=-1&m=0&shop_order_status&_customer_user&paged=1&mode=list&action2=-1";
}

// include_once (WP_PLUGIN_DIR . '/smart-reporter-for-wp-e-commerce/pro/sr.js');
// include_once (ABSPATH . WPINC . '/functions.php');

// ================================================
// Code for SR WP Dashboard Widget
// ================================================

function sr_dashboard_widget_kpi($post, $args) {

    if ( ! wp_verify_nonce( $args['args']['security'], 'smart-reporter-security' ) ) {
      die( 'Security check' ); 
    }

    $data = (!empty($args['args']['data'])) ? json_decode($args['args']['data'],true) : array();

   ?>

    <!-- 
    // ================================================
    // Display Part Of SR Wordpress Dashboard
    // ================================================
    -->
    <div id= "sr_wordpress_dashboard_widget" style="overflow:hidden; cursor:pointer;">
       <!-- <div style="width:50%;"> daily_widgets_text_dashboard_margin_top-->
        <div>
                <div id = "daily_widget_1" class = "daily_widget_dashboard first">
                    <div class = "daily_widgets_icon_dashboard"> 
                        <i class = "fa fa-signal daily_widgets_icon1 daily_widgets_icon1_dashboard_font_size">   </i>
                    </div>

                    <div id="daily_total_sales" class="daily_widgets_data">
                        <?php echo $data['sales_today'];?>
                    </div>
                </div>
        </div>
        <div>
                <div id = "daily_widget_2" class="daily_widget_dashboard second">
                  <div class="daily_widgets_icon_dashboard">
                    <i class = "fa fa-user daily_widgets_icon1 daily_widgets_icon1_margin_left daily_widgets_icon1_dashboard_font_size"> </i>     
                  </div>

                  <div id="daily_new_cust" class="daily_widgets_data">
                    <?php echo $data['new_customers_today'];?>
                  </div>

                </div>
        </div>

        <div>
                <div id = "daily_widget_3" class="daily_widget_dashboard third">
                  <div class="daily_widgets_icon_dashboard">
                    <i class = "fa fa-thumbs-down daily_widgets_icon1 daily_widgets_icon1_margin_left daily_widgets_icon1_dashboard_font_size"> </i>   
                  </div>
                  <div id="daily_refund" class="daily_widgets_data">
                    <?php echo $data['refund_today'];?>
                  </div>

                </div>
        </div>

        <div>
                <div id = "daily_widget_4" class="daily_widget_dashboard fourth">
                    <div class="daily_widgets_icon_dashboard">
                      <i class = "fa fa-truck daily_widgets_icon1 daily_widgets_icon1_dashboard_font_size"> </i>   
                    </div>
                    <div id="daily_order_unfullfilment" class="daily_widgets_data">
                      <?php echo $data['orders_to_fulfill'];?>
                    </div>
                </div>
        </div>
        <!-- </div> -->

        <!-- <div class="row" style="width:50%;"> -->
        <div>
                <div id = "daily_widget_5" class = "daily_widget_dashboard first">
                    <div class = "daily_widgets_icon_dashboard"> 
                        <i class = "fa fa-dashboard daily_widgets_icon1 daily_widgets_icon1_dashboard_font_size">   </i>
                    </div>

                    <div id="month_to_date_sales" class="daily_widgets_data">
                            <?php echo $data['month_to_date_sales'];?>
                    </div>
                </div>
        </div>
        <div>
                <div id = "daily_widget_6" class="daily_widget_dashboard second">
                  <div class="daily_widgets_icon_dashboard">
                    <i class = "fa fa-filter daily_widgets_icon1 daily_widgets_icon1_margin_left daily_widgets_icon1_dashboard_font_size"> </i>     
                  </div>

                  <div id="average_sales_day" class="daily_widgets_data">
                    <?php echo $data['avg_sales/day'];?>
                  </div>

                </div>
        </div>

        <div>
                <div id = "daily_widget_7" class="daily_widget_dashboard third">
                    <div class="daily_widgets_icon_dashboard">
                      <i class = "fa fa-clock-o daily_widgets_icon1 daily_widgets_icon1_margin_left daily_widgets_icon1_dashboard_font_size"> </i>   
                    </div>
                    <div id="sales_frequency" class="daily_widgets_data" style="margin-top: -3.8em !important;">
                      <?php echo $data['one_sale_every'];?>
                    </div>
                </div>
        </div>

        <div>
                <div id = "daily_widget_8" class="daily_widget_dashboard fourth">
                  <div class="daily_widgets_icon_dashboard">
                    <i class = "fa fa-rocket daily_widgets_icon1 daily_widgets_icon1_dashboard_font_size"> </i>   
                  </div>
                  <div id="forecasted_sales" class="daily_widgets_data">
                    <?php echo $data['forecasted_sales'];?>
                  </div>

                </div>
        </div>

        
    <!-- </div> -->
        
    </div>

<?php
}

// ================================================
// Code for SR Beta
// ================================================


// if(defined(SR_BETA) && SR_BETA == "true") {
if ( !isset($_GET['view']) && ( isset($_GET['page']) && $_GET['page'] == 'wc-reports') && ( !empty($sr_const['is_woo22']) && $sr_const['is_woo22'] == "true" ) ) {

    //chk if the SR db dump table exists or not
    $table_name = "{$wpdb->prefix}woo_sr_orders";
    if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $query = "SELECT COUNT(*) as order_count
                  FROM {$wpdb->prefix}posts
                  WHERE post_type IN ('shop_order', 'shop_order_refund')";

        $order_count = $wpdb->get_var($query);

        if ($order_count > 0) {

        ?>

          <div id="sr_data_sync_msg" class="updated woocommerce-message wc-connect" style="margin-top:25%;text-align:center;border:0px;display: block!important;">
            <input id="sr_data_sync_orders" type="hidden" value="<?php echo $order_count; ?>"> 
            <p><?php _e( '<strong>Smart Reporter Data Sync Required</strong> &#8211; We just need to sync your orders for faster reporting', $sr_text_domain ); ?></p>
            <p class="submit"> <a id="sr_sync_link" href="<?php echo esc_url( add_query_arg( 'sr_data_sync', 'true', admin_url( 'admin.php?page=wc-reports&tab=smart_reporter' ) ) ); ?>" class="wc-update-now button-primary"><?php _e( 'Sync Now', $sr_text_domain ); ?></a> </p> 
            <label id="sr_data_sync_per">  </label> </p>
            
          </div>

        <?php

        if ( !empty($_GET) && !empty($_GET['sr_data_sync']) ) {

          ?> 

          <script type="text/javascript">

              jQuery(function($){
                  var ocount = $('#sr_data_sync_orders').val();

                  var ajax_count = 1;

                  $('#sr_sync_link').attr('disabled',true).click(function(e) {
                      return false;
                  });

                  if ( ocount > 100 ) {
                      for ( i=0; i<ocount; ) {
                          ajax_count ++;
                          i = i+100;
                      }
                  }
                  else{
                      ajax_count = 1;
                  }

                  // for ( i=1; i<=ajax_count; i++ ) {
                  var sr_sync_data_req = function(num) {

                      var sfinal = ( num == ajax_count ) ? 1 : 0;

                      $.ajax({
                            type : 'POST',
                            url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=sr_get_stats' : ajaxurl + '?action=sr_get_stats', 
                            dataType:"text",
                            action: 'sr_get_stats',
                            // async:false,
                            data: {
                                    cmd: 'sr_data_sync',
                                    part: num,
                                    sfinal: sfinal,
                                    params : <?php echo json_encode($sr_const); ?>
                                },
                            success: function(response) {

                              if ( num<=ajax_count ) {

                                  if ( num == ajax_count ) {
                                      $("#sr_sync_link").text( 'Sync Complete' );
                                      window.location = "<?php echo admin_url('admin.php?page=wc-reports'); ?>";
                                  } else {
                                      $("#sr_sync_link").text( ((num/ajax_count)*100).toFixed(2) + '% Completed' ); 
                                      num++;
                                      sr_sync_data_req(num); 
                                  }
                              }
                            }
                        });
                  }

                  sr_sync_data_req(1);
              });

          </script>

          <?php    
      }
        exit;

        }
    }

?>

<div id="smart_reporter_beta" syle="width:99%;">

<div id="chartjs-tooltip" style="display:none;"></div>

<!-- 
// ================================================
// Display Part Of Daily Widgets
// ================================================
-->
<div id ="daily_widgets_container">
    <div class="row">
    <div>
            <div id = "daily_widget_1" class = "daily_widget first daily_widget_today_sales">
                <div class = "daily_widgets_icon"> 
                    <i class = "fa fa-signal daily_widgets_icon1">   </i>
                </div>

                <div id="daily_total_sales" class="daily_widgets_data"> 
                    <?php echo $sr_daily_widget_data['sales_today'];?>
                </div>
            </div>
    </div>
    <div>
            <div id = "daily_widget_2" class="daily_widget second">
              <div class="daily_widgets_icon">
                <i class = "fa fa-user daily_widgets_icon1 daily_widgets_icon1_margin_left"> </i>
              </div>

              <div id="daily_new_cust" class="daily_widgets_data">
                  <?php echo $sr_daily_widget_data['new_customers_today'];?>
              </div>

            </div>
    </div>

    <div>
            <div id = "daily_widget_3" class="daily_widget third">
              <div class="daily_widgets_icon">
                <i class = "fa fa-thumbs-down daily_widgets_icon1 daily_widgets_icon1_margin_left"> </i>
              </div>
              <div id="daily_refund" class="daily_widgets_data">
                  <?php echo $sr_daily_widget_data['refund_today'];?>
              </div>

            </div>
    </div>

    <div>
            <div id = "daily_widget_4" class="daily_widget daily_widget_order_fulfill fourth">
                <div class="daily_widgets_icon">
                  <i class = "fa fa-truck daily_widgets_icon1"> </i>   
                </div>
                <div id="daily_order_unfullfilment" class="daily_widgets_data">
                    <?php echo $sr_daily_widget_data['orders_to_fulfill'];?>
                </div>
            </div>
    </div>
    </div>

    <div class="row">
    <div>
            <div id = "daily_widget_5" class = "daily_widget first daily_widget_today_sales">
                <div class = "daily_widgets_icon"> 
                    <i class = "fa fa-dashboard daily_widgets_icon1">   </i>
                </div>

                <div id="month_to_date_sales" class="daily_widgets_data">
                    <?php echo $sr_daily_widget_data['month_to_date_sales'];?>
                </div>
            </div>
    </div>
    <div>
            <div id = "daily_widget_6" class="daily_widget second">
              <div class="daily_widgets_icon">
                <i class = "fa fa-filter daily_widgets_icon1 daily_widgets_icon1_margin_left"> </i>     
              </div>

              <div id="average_sales_day" class="daily_widgets_data">
                  <?php echo $sr_daily_widget_data['avg_sales/day'];?>
              </div>

            </div>
    </div>

    <div>
            <div id = "daily_widget_7" class="daily_widget third">
                <div class="daily_widgets_icon">
                  <i class = "fa fa-clock-o daily_widgets_icon1 daily_widgets_icon1_margin_left"> </i>   
                </div>
                <div id="sales_frequency" class="daily_widgets_data" style="margin-top: -4.5em;">
                    <?php echo $sr_daily_widget_data['one_sale_every'];?>

                <!-- style="margin-top: 0.8em; margin-bottom:1em;" -->
                </div>
            </div>
    </div>

    <div>
            <div id = "daily_widget_8" class="daily_widget fourth">
              <div class="daily_widgets_icon">
                <i class = "fa fa-rocket daily_widgets_icon1"> </i>   
              </div>
              <div id="forecasted_sales" class="daily_widgets_data">
                    <?php echo $sr_daily_widget_data['forecasted_sales'];?>
              </div>
            </div>
    </div>

    
    </div>

</div>

<div id="container">

<!-- 
// ================================================
// Date Picker Display
// ================================================
 -->

<!-- <div id="sr_cumm_date" style="height:2.4em;width:97.85%"> -->
<div id="sr_cumm_date" style="height:3em;width:97.85%">
    <div id="sr_cumm_date1" class="sr_cumm_date" style="width:11.2em;">
        <form>
            
            <span id ="sr_smart_date" class="sr_cumm_date_picker"> 
                <select id ="sr_smart_date_select" style="height:auto;font-size:1.2em;padding:0px;margin-top:-0.05em;" >
                  <option value="" style="display:none;color: #333 !important;" selected> Select Date </option>
                  <option value="TODAY"> <?php _e('Today', $sr_text_domain); ?></option>
                  <option value="YESTERDAY"> <?php _e('Yesterday', $sr_text_domain); ?></option>
                  <option value="CURRENT_WEEK"> <?php _e('Current', $sr_text_domain); ?> Week</option>
                  <option value="LAST_WEEK"> <?php _e('Last', $sr_text_domain); ?> Week</option>
                  <option value="CURRENT_MONTH" <?php echo ($fileExists) ? 'selected' : ''; ?> > <?php _e('Current Month', $sr_text_domain); ?></option>
                  <option value="LAST_MONTH"> <?php _e('Last', $sr_text_domain); ?> Month</option>
                  <option value="3_MONTHS"> <?php _e('3', $sr_text_domain); ?> Months</option>
                  <option value="6_MONTHS"> <?php _e('6', $sr_text_domain); ?> Months</option>
                  <option value="CURRENT_YEAR"> <?php _e('Current', $sr_text_domain); ?> Year</option>
                  <option value="LAST_YEAR"> <?php _e('Last', $sr_text_domain); ?> Year</option>
                  <option value="CUSTOM_DATE"> <?php _e('Custom', $sr_text_domain); ?> Date</option>
                </select>
            </span>

            <span id ="sr_custom_date" style ="display : none;">
                <span>
                   <input type ="text" id="startdate_display" class = "sr_cumm_date_picker" >
                   <span id = "sr_startcal_icon" ><i class = "fa fa-calendar sr_cumm_date_icon" style="margin-right: 0.5em;"> </i> </span>
                   <!-- <img id = "sr_startcal_icon" src= "<?php echo SR_IMG_DATE_PICKER?>" class = "sr_cumm_date_icon"> -->
                </span>
                <label class = "sr_cumm_date_label"> To </label>

                <input type = "text" id="enddate_display" class = "sr_cumm_date_picker" >
                <span id = "sr_endcal_icon" ><i class = "fa fa-calendar sr_cumm_date_icon"> </i> </span>
                
                

                <span id ="startdate" style="padding-top: 2px;font-size : 1.4em; float:left; display: none"> </span>
                <span id ="enddate" style="padding-top: 2px;font-size : 1.4em; float:left; display: none"> </span>
            </span>


        <script type="text/javascript">

                var curr = "<?php echo $sr_const['currency_symbol'];?>";
                var get_data_flag = false; // Flag to handle call to get_data()
                var chart = new Array();

                var sr_chart_params = {
                                          data : {
                                                    labels: '',
                                                    datasets: [
                                                        {
                                                            
                                                            // strokeColor: "#5AA2E8",
                                                            // pointColor: "#368ee0",
                                                            pointColor: "#388BDC",
                                                            strokeColor: "#1471CB",
                                                            // strokeColor: "#46B2C8",
                                                            // pointColor: "#279EB4",
                                                            // pointColor: "#85BBEF",
                                                            pointStrokeColor: "#fff",
                                                            pointHighlightFill: "#fff",
                                                            pointHighlightStroke: "rgba(151,187,205,1)",
                                                            data: ''
                                                        }
                                                    ]
                                                  },
                                          options : {
                                                      showScale: false,
                                                      responsive: true,
                                                      maintainAspectRatio: true,
                                                      scaleShowGridLines : false,
                                                      bezierCurve : true,
                                                      bezierCurveTension : 0.4,
                                                      pointDot : true,
                                                      pointDotRadius : 3,
                                                      pointDotStrokeWidth : 1,
                                                      pointHitDetectionRadius : 1,                                            
                                                      datasetFill : false,
                                                      scaleShowLabels: true,
                                                      tooltipFillColor: "#0F434F",
                                                      tooltipCornerRadius: 6,
                                                      tooltipTemplate: "<%if (label){%><%=label%>::<%}%><%= value %>",
                                                      // tooltipTemplate: "<%%=datas.etLabel%> : <?php echo $sr_const['currency_symbol'];?> <%%= value %>"
                                                    }
                                      };

                var sr_plot_charts = function (data) {

                    jQuery(function($) {
                        if ( data.hasOwnProperty('period') === false ){
                            return;
                        }

                        // var s_colors = new Array('#AA3939' ,'#2EBB6F' ,'#73469D' ,'#46B2C8' ,'#1F2E32' ,'#DD6272'); 

                        sr_chart_params.data.labels = data.period;

                        // delete data.period;

                        // var i = 0;

                        for ( var key in data ) {

                              if (key == 'period') {
                                 continue;
                              }

                              sr_chart_params.data.datasets[0].data = data[key];

                              if ( typeof (chart[key]) != 'undefined' ) {
                                  chart[key].destroy();
                              }

                              if ( typeof ($("#sr_"+ key +"_graph").get(0)) != 'undefined' ) {

                                  sr_chart_params.options.pointDot = true;
                                  sr_chart_params.options.showTooltips = true;
                                  sr_chart_params.options.customTooltips= function(tooltip) {

                                        var tooltipEl = $('#chartjs-tooltip');

                                        // tooltip will be false if tooltip is not visible or should be hidden
                                        if (!tooltip) {
                                            tooltipEl.hide();
                                            return;
                                        }

                                        // Set caret Position
                                        tooltipEl.removeClass('above below');
                                        tooltipEl.addClass(tooltip.yAlign);

                                        v = tooltip.text.split('::');
                                        tooltipEl.html(v[0]+', '+"<?php echo $sr_const['currency_symbol'];?>"+v[1]);

                                        // Find Y Location on page
                                        var top;
                                        if (tooltip.yAlign == 'above') {
                                            top = tooltip.y - tooltip.caretHeight - tooltip.caretPadding;
                                        } else {
                                            top = tooltip.y + tooltip.caretHeight + tooltip.caretPadding;
                                        }

                                        // Display, position, and set styles for font
                                        tooltipEl.css({
                                            left: tooltip.chart.canvas.offsetLeft + tooltip.x + 'px',
                                            top: tooltip.chart.canvas.offsetTop + top + 'px',
                                            fontFamily: tooltip.fontFamily,
                                            fontSize: tooltip.fontSize,
                                            fontStyle: tooltip.fontStyle,
                                        });

                                        tooltipEl.show();
                                    };

                                  chart[key] = new Chart($("#sr_"+ key +"_graph").get(0).getContext("2d")).Line(sr_chart_params.data, sr_chart_params.options);

                              } else if ( typeof ($("#"+ key).get(0)) != 'undefined' ) {

                                  sr_chart_params.data.datasets[0].strokeColor = '#5AA2E8';
                                  sr_chart_params.options.pointDot = false;
                                  sr_chart_params.options.showTooltips = false;

                                  chart[key] = new Chart($("#"+ key).get(0).getContext("2d")).Line(sr_chart_params.data, sr_chart_params.options);
                              }
                        }
                    });
                }



                //Code for handling Smart Dates

                jQuery(function($) {
                    $("#sr_smart_date_select").on('change',function(){

                        var smartdateValue = this.value;
                            get_data_flag = false;

                        if(smartdateValue == "CUSTOM_DATE"){
                            $('#sr_cumm_date1').css({"width" :"37.5em"});
                            $("#sr_custom_date").css({ "display" : "block"});
                        } else {
                            $('#sr_cumm_date1').css({"width" :"11.2em"});
                            $("#sr_custom_date").css({ "display" : "none"});
                        }

                        <?php if (defined('SRPRO') && SRPRO === true) { ?>
                            var date = proSelectDate(smartdateValue, true);

                            var fromdate = new Date(date.fromDate);
                            var todate = new Date(date.toDate);

                            var month_abbrs = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

                            var start_date = fromdate.getFullYear()+"-"+(fromdate.getMonth()+1)+"-"+fromdate.getDate();
                            // var start_date_display = new Date(fromdate.getFullYear(), fromdate.getMonth(), fromdate.getDate());
                            var end_date = todate.getFullYear()+"-"+(todate.getMonth()+1)+"-"+todate.getDate();
                            // var end_date_display = new Date(todate.getFullYear(), todate.getMonth(), todate.getDate());
                     
                            $("#enddate").val(end_date);
                            $('#enddate_display').datepick('option', 'minDate', '');
                            $('#enddate_display').datepick("setDate", new Date(todate.getFullYear(), todate.getMonth(), todate.getDate()));

                            $("#startdate").val(start_date);
                            $('#startdate_display').datepick('option', 'maxDate', '');
                            $('#startdate_display').datepick("setDate", new Date(fromdate.getFullYear(), fromdate.getMonth(), fromdate.getDate()));

                            get_data();
                            get_data_flag = true;
                            
                        <?php } else {?>
                            alert("Sorry! Smart Date functionality is available only in Pro version");
                        <?php }?>
                    });
                });
                

                var sr_data = {tp_data : { kpi:[], chart:[] },
                                bc_data : {} }; // for top_prod chart data

                var daily_summary_report = function() {

                    var iframe = document.createElement("iframe");
                    iframe.src = '<?php echo content_url("/plugins/smart-reporter-for-wp-e-commerce/sr/json-woo.php"); ?>' + "?cmd=daily_summary_report";
                    iframe.style.display = "none";
                    document.body.appendChild(iframe);
                };
                
                var Dom_Id = new Array();
                var show;

                // Function to show spinner on loading
                var ajax_spinner = function(id,show){
                        
                    jQuery(function($) {
                      if ( typeof id == undefined || id == '' ) {
                          id = ["#sr_sales_graph", "#sr_cumm_sales_funnel_data", "#top_prod_data", "#sr_cumm_top_abandoned_products_data", "#top_cust_data", "#sr_cumm_top_coupons_data", "#sr_discount_graph", "#sr_cumm_taxes_data", "#sr_cumm_order_by_pm_data", "#sr_cumm_sales_countries_graph", "#sr_cumm_order_by_sm_data"];
                      }

                      for (var i = 0; i < id.length; i++) {
                                                                                
                          if(show){

                              $(id[i]).hide();
                              var height = $(id[i]).parents('.cumm_widget').height();
                              var width = $(id[i]).parents('.cumm_widget').width();
                              var pos = $(id[i]).parents('.cumm_widget').offset();
                              var adjHeight = $(id[i]).parents('.cumm_widget').find('.cumm_header').height();
                              var top = pos.top;
                              var Y = height - ( adjHeight * 4 );
                              if ( $(window).width() <= 557 ) {
                                  top = top + 20;
                                  Y = height - ( adjHeight * 2 );
                              }
                              var X = width - 3;
                              
                              $(id[i]).prev('div.ajax_loader').css("cssText" ,"width :" + X + "px !important");
                              $(id[i]).prev('div.ajax_loader').css({"height" : + Y , "top" : + top});
                              $(id[i]).prev('div.ajax_loader').show();

                          }else{

                              $(id[i]).prev('div.ajax_loader').hide();
                              $(id[i]).show();
                          }
                      };
                    });
                  };


              //Function to get the data for all the widgets on selection of any date
              var Master_myJsonObj = new Array();
              
              var get_data = function() {
                  var opt_id;
                  
                  ajax_spinner('', true);

                  jQuery(function($) {

                      if ($('#sr_opt_top_prod_price_label').hasClass('switch-label-on')) {
                          opt_id = "sr_opt_top_prod_price";
                      }
                      else {
                          opt_id = "sr_opt_top_prod_qty";
                      }

                      //  call for Sales, Sales Funnel and Cart Abandonment Rate Widget [sync call]
                                          
                      $.ajax({
                            type : 'POST',
                            url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=sr_get_stats' : ajaxurl + '?action=sr_get_stats', 
                            dataType:"text",
                            async: false,
                            action: 'sr_get_stats',
                            data: {
                                        cmd: 'cumm_sales',
                                        start_date : $("#startdate").val(),
                                        end_date : $("#enddate").val(),
                                        params : <?php echo json_encode($sr_const); ?>
                                },
                            success: function(response) {

                                Dom_Id = new Array();

                                resp = $.parseJSON(response);  

                                chart_data = resp.chart;

                                delete resp.chart;

                                //  sales widget
                                $('#sr_cumm_sales_actual').html( "<?php echo $sr_const['currency_symbol'];?>"+ sr_cumm_number_format(resp['kpi']['sales']) );
                                $('#sr_cumm_sales_indicator').removeClass();
                                $('#sr_cumm_sales_indicator').addClass( (resp['kpi']['sales'] > resp['kpi']['lp_sales']) ? "<?php echo $sr_const['img_up_green'];?>" : "<?php echo $sr_const['img_down_red'];?>"  );
                                $('#diff_cumm_sales').text( sr_cumm_number_format( (resp['kpi']['lp_sales'] > 0) ? ( ((resp['kpi']['sales'] - resp['kpi']['lp_sales'])/resp['kpi']['lp_sales']) * 100) : resp['kpi']['sales'] ) +'%');

                                //  discount widget
                                $('#sr_cumm_total_discount_actual').html( "<?php echo $sr_const['currency_symbol'];?>"+ sr_cumm_number_format(resp['kpi']['discount']) );
                                $('#sr_cumm_total_discount_indicator').removeClass();
                                $('#sr_cumm_total_discount_indicator').addClass( (resp['kpi']['discount'] > resp['kpi']['lp_discount']) ? "<?php echo $sr_const['img_up_green'];?>" : "<?php echo $sr_const['img_down_red'];?>"  );
                                $('#diff_cumm_total_discount').text( sr_cumm_number_format( (resp['kpi']['lp_discount'] > 0) ? ( ((resp['kpi']['discount'] - resp['kpi']['lp_discount'])/resp['kpi']['lp_discount']) * 100) : resp['kpi']['discount'] ) +'%');

                                $('#sr_cumm_avg_order_tot_content').removeClass().addClass('sr_cumm_small_widget_content');
                                $('#sr_cumm_avg_order_tot_content').removeAttr('style');
                                $('#average_order_tot_title').css({'margin-top':'0em'});

                                // Code for Avg Order Total widget
                                if( resp['kpi']['orders'] > 0 && resp['kpi']['lp_orders'] > 0 ) {

                                    $('#sr_cumm_avg_order_tot_content').removeClass('no_data_text').removeAttr('style');

                                    var c_avg_tot = (resp['kpi']['sales']/resp['kpi']['orders']);
                                    var l_avg_tot = (resp['kpi']['lp_sales']/resp['kpi']['lp_orders']);

                                    $('#sr_cumm_avg_order_tot_content').html('<span id ="sr_cumm_avg_order_tot_actual" class="sr_cumm_avg_order_value">'+ "<?php echo $sr_const['currency_symbol'];?>"+ sr_cumm_number_format( c_avg_tot ) + '</span><br>'+
                                    '  <div class="sr_cumm_avg_tot_content"> <i id="sr_cumm_avg_order_tot_img" class="'+ ( ( c_avg_tot > l_avg_tot ) ? "<?php echo $sr_const['img_up_green'];?>" : "<?php echo $sr_const['img_down_red'];?>" ) +'" > </i>'+
                                    ' <span id ="sr_cumm_avg_order_tot_diff" style="font-size : 0.5em;">'+ sr_cumm_number_format( ( l_avg_tot > 0 ) ? ( ((c_avg_tot - l_avg_tot)/l_avg_tot) * 100 ) : c_avg_tot ) +'%' +'</span></div>');
                                }else {
                                    $('#sr_cumm_avg_order_tot_content').text("<?php _e('No Data',  $sr_text_domain); ?>").addClass('no_data_text').css({'margin-top':'2.65em', 'margin-bottom':'1.2em','font-size':'0.55em'});
                                    $('#average_order_tot_title').css({'margin-top':'1.5em'});
                                }

                                top_payment_shipping_display(resp);

                                Dom_Id[0] = '#sr_sales_graph';
                                Dom_Id[1] = '#sr_discount_graph';
                                Dom_Id[2] = '#sr_cumm_taxes_data';
                                
                                ajax_spinner(Dom_Id, false);

                                $('#sr_cumm_avg_order_tot').removeClass('blur_widget');
                                
                                cumm_taxes_display(resp);

                                sr_plot_charts(chart_data); //plotting chart data

                                // Dom_Id.splice(1, 1);
                            }
                        });

                        // call for Top cust, prod Widget
                        $.ajax({
                            type : 'POST',
                            url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=sr_get_stats' : ajaxurl + '?action=sr_get_stats', 
                            dataType:"text",
                            action: 'sr_get_stats',
                            data: {
                                        cmd: 'cumm_cust_prod',
                                        start_date : $("#startdate").val(),
                                        end_date : $("#enddate").val(),
                                        params : <?php echo json_encode($sr_const); ?>
                                },
                            success: function(response) {
                                
                                resp = $.parseJSON(response);  

                                chart_data = resp.chart;

                                for ( var key in chart_data ) {
                                    if ( key == 'period' || key.substring(0,2) == 'tp' ) {
                                        sr_data.tp_data.chart[key] = chart_data[key];
                                    }
                                }

                                sr_data.tp_data.kpi['sales'] = resp.kpi.top_prod.sales;
                                sr_data.tp_data.kpi['qty'] = resp.kpi.top_prod.qty;

                                delete resp.chart;

                                if ( JSON.stringify(sr_data.bc_data) !== '{}' ) {
                                  sr_data.bc_data = resp.kpi.billing_country;
                                  sr_data.bc_data.s_link = (resp.meta.hasOwnProperty('s_link')) ? resp.meta.s_link : '';  
                                  cumm_sales_billing_country(sr_data.bc_data);
                                }
                                

                               

                                top_cust_display(resp);
                                sr_top_coupons_display(resp);
                                top_prod_display(resp.kpi.top_prod.sales, 'tps_');
                                top_ababdoned_products_display(resp.kpi.top_aprod);
                               
                                sr_plot_charts(chart_data); //plotting chart data
                                cumm_sales_funnel_display(resp);


                                $('#sr_cumm_avg_order_items_content, #sr_cumm_cart_abandonment_content, #sr_cumm_order_coupons_content').removeClass('no_data_text').removeAttr('style');

                                // Code for Avg Items Per Customer
                                if( resp['kpi']['aipc'] > 0 || resp['kpi']['lp_aipc'] > 0 ) {

                                    $('#sr_cumm_avg_order_items_content').html('<span id ="sr_cumm_avg_order_items_actual" class="sr_cumm_avg_order_value">'+ sr_cumm_number_format( resp['kpi']['aipc'] ) + '</span><br>'+
                                    '  <div class="sr_cumm_avg_tot_content"> <i id="sr_cumm_avg_order_items_img" class="'+ ( ( resp['kpi']['aipc'] > resp['kpi']['lp_aipc'] ) ? "<?php echo $sr_const['img_up_green'];?>" : "<?php echo $sr_const['img_down_red'];?>" ) +'" > </i>'+
                                    ' <span id ="sr_cumm_avg_order_items_diff" style="font-size : 0.5em;">'+ sr_cumm_number_format( ( resp['kpi']['lp_aipc'] > 0 ) ? (resp['kpi']['aipc'] - resp['kpi']['lp_aipc']) : resp['kpi']['aipc'] ) +'</span></div>');
                                } else {
                                    $('#sr_cumm_avg_order_items_content').text("<?php _e('No Data',  $sr_text_domain); ?>").addClass('no_data_text').css({'margin-top':'2.65em', 'margin-bottom':'1.2em','font-size':'0.55em'});
                                    $('#average_order_items_title').css({'margin-top':'1.5em'});
                                }

                                // Code for Sales with Coupons widget
                                if( resp['kpi']['car'] > 0 || resp['kpi']['lp_car'] > 0 ) {

                                    $('#sr_cumm_cart_abandonment_content').html('<span id ="sr_cumm_cart_abandonment_actual" class="sr_cumm_avg_order_value">'+ sr_cumm_number_format( resp['kpi']['car'] ) + ' % </span><br>'+
                                    '  <div class="sr_cumm_avg_tot_content"> <i id="sr_cumm_cart_abandonment_img" class="'+ ( ( resp['kpi']['car'] > resp['kpi']['lp_car'] ) ? "<?php echo $sr_const['img_up_green'];?>" : "<?php echo $sr_const['img_down_red'];?>" ) +'" > </i>'+
                                    ' <span id ="sr_cumm_cart_abandonment_count_diff" style="font-size : 0.5em;">'+ sr_cumm_number_format( ( resp['kpi']['lp_car'] > 0 ) ? (resp['kpi']['car'] - resp['kpi']['lp_car']) : resp['kpi']['car'] ) +' % </span></div>');
                                } else {
                                    $('#sr_cumm_cart_abandonment_content').text("<?php _e('No Data',  $sr_text_domain); ?>").addClass('no_data_text').css({'margin-top':'2.65em', 'margin-bottom':'1.2em','font-size':'0.55em'});
                                    $('#sr_cumm_cart_abandonment_title').css({'margin-top':'1.5em'});
                                }

                                // Code for Sales with Coupons widget
                                if( resp['kpi']['swc'] > 0 || resp['kpi']['lp_swc'] > 0 ) {

                                    $('#sr_cumm_order_coupons_content').html('<span id ="sr_cumm_order_coupons_actual" class="sr_cumm_avg_order_value">'+ sr_cumm_number_format( resp['kpi']['swc'] ) + ' % </span><br>'+
                                    '  <div class="sr_cumm_avg_tot_content"> <i id="sr_cumm_order_coupons_img" class="'+ ( ( resp['kpi']['swc'] > resp['kpi']['lp_swc'] ) ? "<?php echo $sr_const['img_up_green'];?>" : "<?php echo $sr_const['img_down_red'];?>" ) +'" > </i>'+
                                    ' <span id ="sr_cumm_order_coupons_count_diff" style="font-size : 0.5em;">'+ sr_cumm_number_format( ( resp['kpi']['lp_swc'] > 0 ) ? (resp['kpi']['swc'] - resp['kpi']['lp_swc']) : resp['kpi']['swc'] ) +' % </span></div>');
                                } else {
                                    $('#sr_cumm_order_coupons_content').text("<?php _e('No Data',  $sr_text_domain); ?>").addClass('no_data_text').css({'margin-top':'2.65em', 'margin-bottom':'1.2em','font-size':'0.55em'});
                                    $('#sr_cumm_order_coupons_title').css({'margin-top':'1.5em'});
                                }

                                $('#sr_cumm_avg_order_count, #sr_cumm_cart_abandonment, #sr_cumm_order_coupons_count').removeClass('blur_widget');
                            }
                        });
                        
            //             
                  });
            }
            
            
            var date_format = "<?php echo get_option('date_format'); ?>";
            
            //Code to format the date in a specific format
            date_format = date_format.replace(/(F|j|Y|m|d)/gi, function ($0){
              var index = {
                'F': 'M',
                'j': 'd',
                'Y':'yyyy',
                'y': 'yy',
                'm': 'mm',
                'd': 'dd'

              };
              return index[$0] != undefined ? index[$0] : $0;
            });

                var flag = 0;

            //Code for the display part of DatePicker

            jQuery(function($){

                var date = new Date();

                $('#startdate_display').datepick({dateFormat: date_format,
                    altField  : '#startdate',
                    altFormat : 'yyyy-mm-dd',
                    selectDefaultDate: true, // sets the default date visible in the text box
                    autoSize: true,
                    yearRange: '1900:' + new Date().getFullYear(),
                    pickerClass: 'datepick-jumps',
                    onSelect: function() {
                         
                          var date = $("#startdate").val();
                          var strt_date;
                          var end_min_date;

                            <?php if ($fileExists) { ?>
                              if(date) {
                                    strt_date = date.split("-");
                                    end_min_date = new Date(strt_date[0], strt_date[1]-1, strt_date[2]);
                              }
                              else {
                                    var date = new Date();
                                    date.setDate(date.getDate() - 30);
                                    end_min_date = date;
                              }

                              flag = 1;

                              $('#enddate_display').datepick('option', 'minDate', end_min_date);
                              $('#startdate_display').datepick('option', 'maxDate', end_min_date);

                            <?php } else { ?>
                                end_max_date = new Date();
                                
                                var date = new Date();
                                date.setDate(date.getDate() - 30);
                                end_min_date = date;

                                $('#enddate_display').datepick('option', 'minDate', end_min_date);
                                $('#enddate_display').datepick('option', 'maxDate', end_max_date);

                                $('#startdate_display').datepick('option', 'minDate', end_min_date);
                                $('#startdate_display').datepick('option', 'maxDate', end_max_date);

                            <?php } ?>
                        if(get_data_flag){
                          get_data();
                        }
                    },
                    renderer: $.extend({}, $.datepick.defaultRenderer, 
                        {picker: $.datepick.defaultRenderer.picker. 
                            replace(/\{link:prev\}/, '{link:prevJump}{link:prev}'). 
                            replace(/\{link:next\}/, '{link:next}{link:nextJump}')})
                });


                $('#enddate_display').datepick({dateFormat: date_format,
                    altField  : '#enddate',
                    altFormat : 'yyyy-mm-dd',
                    defaultDate: '0',
                    autoSize: true,
                    yearRange: '1900:' + new Date().getFullYear(),
                    selectDefaultDate: true,
                    maxDate: 0, // sets the max date to today
                    pickerClass: 'datepick-jumps', 
                    onSelect: function() {
                              
                          var date = $("#enddate").val();
                          var strt_date;
                          var end_min_date;

                          <?php if ($fileExists) { ?>
                              if(date) {
                                    strt_date = date.split("-");
                                    start_max_date = new Date(strt_date[0], strt_date[1]-1, '1');
                              }
                              else {

                                    var date = new Date();
                                    start_max_date = new Date(date.getFullYear(), date.getMonth(), 1);
                              }

                              if(flag == 0) {
                                $('#enddate_display').datepick('option', 'minDate', start_max_date);
                                $('#enddate_display').datepick('option', 'defaultDate',  start_max_date);
                              }
                              else {
                                flag = 0;
                              }

                              $('#startdate_display').datepick('option', 'maxDate', start_max_date);
                              $('#startdate_display').datepick('option', 'defaultDate', start_max_date);

                          <?php } else { ?>
                                
                                start_max_date = new Date();

                                var date = new Date();
                                date.setDate(date.getDate() - 30);
                                start_min_date = date;

                                $('#enddate_display').datepick('option', 'minDate', start_min_date);
                                $('#enddate_display').datepick('option', 'maxDate', start_max_date);

                                $('#startdate_display').datepick('option', 'minDate', start_min_date);
                                $('#startdate_display').datepick('option', 'maxDate', start_max_date);

                                $('#startdate_display').datepick('option', 'defaultDate', start_min_date);

                          <?php } ?>
                    if(get_data_flag){
                          get_data();

                        }
                    },
                    renderer: $.extend({}, $.datepick.defaultRenderer, 
                        {picker: $.datepick.defaultRenderer.picker. 
                            replace(/\{link:prev\}/, '{link:prevJump}{link:prev}'). 
                            replace(/\{link:next\}/, '{link:next}{link:nextJump}')})});

                
                    $("#sr_endcal_icon").click(function() {
                       $("#enddate_display").focus();
                    });

                    $("#sr_startcal_icon").click(function() {
                       $("#startdate_display").focus();
                    });
                });
            
                jQuery(document).on( 'ready', function() {

                    ajax_spinner('',true); // passing blank to show for all cumm widgets

                    setTimeout(function(){
                        get_data();
                        get_data_flag = true;
                    }, 1000)
                    
                });

          </script>
        </form>
      </div>   
    </div>
  </div>

</div>  <!-- Closing of Container div -->
<br>

<!-- 
// ================================================
// Cumm Sales Widget
// ================================================
 -->

<canvas id="sample1" width="400" height="400"> </canvas>

<div id="sr_cumm_sales" class="cumm_widget">

    <div id="sr_cumm_sales_value" style="height:60px;width:100%;">
          <div class="cumm_header">
              <i class="fa fa-bar-chart-o icon_cumm_widgets" ></i>
              <?php _e('Sales', $sr_text_domain); ?>
          </div>
          <div id="sr_cumm_sales_total" class="cumm_total">
              <span id ="sr_cumm_sales_actual"> </span>  <i id="sr_cumm_sales_indicator" ></i> <span id ="diff_cumm_sales" style="font-size : 0.5em;"></span>
          </div>    
    </div>
    <div class="ajax_loader cumm_widget" style="display: none;"></div>
    <!-- <canvas id="sr_sales_graph" class="sr_sales_graph "> </canvas> -->
    <canvas id="sr_sales_graph" class="sr_cumm_sales_graph"> </canvas>
    <script type="text/javascript"> 
        
        // ================================================================================
        // Code to override the Jqplot Functionality to display only one marker
        // ================================================================================
    
    // ================================================================================


    var sales_trend = new Array();
    var sales_trend1 = "1";   

    //Functions for on window resize

    jQuery(function($){

        $(window).resize(function() {
            cumm_sales_billing_country(sr_data.bc_data);
        });
    });

    //Function to handle the css of the widgets on window resize

    var widget_resize = function () {

        jQuery(function($){

            var docHeight = $(document).height();
            var scroll    = $(window).height() ;//+ $(window).scrollTop();
            if (docHeight > scroll) {
                //Date Picker

                $('#sr_cumm_date').css('width','98.35%');

                //Daily Widgets

                $("#daily_widget_1").css('margin-left','0em');
                $("#daily_widget_1").css('margin-top','0em');
                $("#daily_widget_1").css('margin-right','1.55em');

                $("#daily_widget_2").css('margin-top','0em');
                $("#daily_widget_2").css('margin-right','1.55em');

                $("#daily_widget_3").css('margin-top','0em');
                $("#daily_widget_3").css('margin-right','1.55em');

                $("#daily_widget_4").css('margin-top','0em');
                $("#daily_widget_4").css('margin-right','1.55em');

                //Cumm Widgets

                $("#sr_cumm_sales").css('margin-right','1.5em');
                $("#sr_cumm_sales").css('margin-left','0em');
                $("#sr_cumm_top_prod").css('margin-right','1.5em');
                $("#sr_cumm_top_prod").css('margin-left','0em');

            }
            else {
                
                //Date Picker

                $('#sr_cumm_date').css('width','97.85%');

                //Daily Widgets

                $("#daily_widget_1").css('margin-left','0.25em');
                $("#daily_widget_1").css('margin-top','0.29em');
                $("#daily_widget_1").css('margin-right','1.8em');

                $("#daily_widget_2").css('margin-top','0.29em');
                $("#daily_widget_2").css('margin-right','1.8em');

                $("#daily_widget_3").css('margin-top','0.29em');
                $("#daily_widget_3").css('margin-right','1.8em');

                $("#daily_widget_4").css('margin-top','0.29em');
                $("#daily_widget_4").css('margin-right','1.8em');

                //Cumm Widgets

                $("#sr_cumm_sales").css('margin-right','1.8em');
                $("#sr_cumm_sales").css('margin-left','0.35em');
                $("#sr_cumm_top_prod").css('margin-right','1.8em');
                $("#sr_cumm_top_prod").css('margin-left','0.35em');
                
            }

        });
    };


    var jqplot_flag = 0; // Flag to handle jqplot margin settings

    jQuery(document).ready(function($) {
        jqplot_flag = 1;
    });

    jQuery(function($){

        var font_size_default = $('body').css('font-size');

        if ( !$(document.body).hasClass('folded') ) {

            jqplot_flag = 2;

            // $('#sr_cumm_date1').css('width','21em');
            $('#sr_sales_graph, #sr_discount_graph').removeClass('sr_cumm_sales_graph_collapsed');
            $('#sr_sales_graph, #sr_discount_graph').addClass('sr_cumm_sales_graph_not_collapsed');

            // if(screen.width >= 1001 && screen.width <= 1150) {
            //     // $('body').css('font-size','0.655em');    
            //     $('body').css('font-size','66.5%');
            //     $('#sr_cumm_top_cust_coupons').css({'width':'53.7em !important'});    
            //     $('#sr_cumm_sales_countries').css('margin-left','-27em');
            // }
            // else if(screen.width >= 1151 && screen.width <= 1300) {

            // }
            // else if(screen.width >= 1301) {
            //     $('body').css('font-size','1.1em');       
            // }
        }
        else {

            jqplot_flag = 2;

            $('#sr_sales_graph, #sr_discount_graph').css("margin-top","-0.95em");
            // $('#sr_cumm_date1').css('width','20.8em');
            $('#sr_sales_graph, #sr_discount_graph').removeClass('sr_cumm_sales_graph_not_collapsed');
            $('#sr_sales_graph, #sr_discount_graph').addClass('sr_cumm_sales_graph_collapsed');

            // if(screen.width >= 1001 && screen.width <= 1150) {
            //     // $('body').css('font-size','0.745em');
            //     $('body').css('font-size','73%');
            //     $('#sr_cumm_top_cust_coupons').css('width','53.7em');    
            //     $('#sr_cumm_sales_countries').css('margin-left','0em');
            // }
            // else if(screen.width >= 1151 && screen.width <= 1300) {
            //     $('body').css('font-size','0.88em');
            // }
            // else if(screen.width >= 1301) {
            //     $('body').css('font-size','1.2em');       
            // }
        }
    

    //Code to handle the resizing of the widgets on folding and unfolding of the wordpress menu
        $('#collapse-menu').click(function(){

            jqplot_flag = 0;

            if ( $(document.body).hasClass('folded') ) {

                // $('#sr_sales_graph, #sr_discount_graph').removeClass('folded_height');
                $('#sr_sales_graph, #sr_discount_graph').removeAttr('style');
                $('#sr_sales_graph, #sr_discount_graph').css("margin-top","-2.75em");
                $('#sr_sales_graph, #sr_discount_graph').removeClass('sr_cumm_sales_graph_collapsed');
                $('#sr_sales_graph, #sr_discount_graph').addClass('sr_cumm_sales_graph_not_collapsed');
                // $('#sr_cumm_date1').css('width','21em');

                // if(screen.width >= 1001 && screen.width <= 1150) {
                //     // $('body').css('font-size','0.655em');
                //     $('body').css('font-size','66.5%');
                //     $('#sr_cumm_top_cust_coupons').css({'width':'53.7em !important'});    
                //     $('#sr_cumm_sales_countries').css('margin-left','-27em');    
                // }
                // else if(screen.width >= 1151 && screen.width <= 1300) {
                //     $('body').css('font-size','76.6%');
                // }
                // else if(screen.width >= 1301) {
                //     $('body').css('font-size','1.1em');       
                // }
                
            }
            else {            

                $('#sr_sales_graph, #sr_discount_graph').removeAttr('style');
                $('#sr_sales_graph, #sr_discount_graph').removeClass('sr_cumm_sales_graph_not_collapsed');
                $('#sr_sales_graph, #sr_discount_graph').addClass('sr_cumm_sales_graph_collapsed');
                // $('#sr_cumm_date1').css('width','20.8em');

                // if(screen.width >= 1001 && screen.width <= 1150) {
                //     // $('body').css('font-size','0.745em');
                //     $('body').css('font-size','73%');
                //     $('#sr_cumm_top_cust_coupons').css({'width':'53.7em !important'});  
                //     $('#sr_cumm_sales_countries').css('margin-left','0em');
                // }
                // else if(screen.width >= 1151 && screen.width <= 1300) {
                //     $('body').css('font-size','0.88em');
                // }
                // else if(screen.width >= 1301) {
                //     $('body').css('font-size','1.2em');       
                // }
                
            }

            //Code to replot the jqPlot graphs
            // monthly_display(Master_myJsonObj);
            // top_prod_display(Master_myJsonObj['monthly_top_products']);
            // top_gateway_display(Master_myJsonObj['monthly_payment_gateways']);
            // top_shipping_method_display(Master_myJsonObj['monthly_shipping_methods']);
            // sr_cumm_total_discount_display(Master_myJsonObj['monthly_total_discount']);
            // top_ababdoned_products_display(Master_myJsonObj['monthly_abandoned_products']);
            // cumm_taxes_display(Master_myJsonObj['monthly_taxes_shipping']);
            // cumm_sales_funnel_display(Master_myJsonObj);
            // cumm_sales_billing_country(Master_myJsonObj['monthly_billing_countries']);
        });
    });
       

    //Javascript function to handles Sales Figures
    var sr_cumm_number_format = function (number) {

        var decPlaces = "<?php echo $sr_const['decimal_places'];?>";
        var numformat = "<?php echo $sr_const['num_format'];?>";

        // 2 decimal places => 100, 3 => 1000, etc
        decPlaces = Math.pow(10,decPlaces);

        // Enumerate number abbreviations
        var abbrev = [ "k", "m", "b", "t" ];

        number =  ( typeof number != undefined || number != '' )  ? Math.abs(number) : 0;

        // for rounding off to decPlaces
        number = Math.round(number*decPlaces)/decPlaces;

        if ( numformat == 1 ) {
            return number;
        }

        // Go through the array backwards, so we do the largest first
        for (var i=abbrev.length-1; i>=0; i--) {

            // Convert array index to "1000", "1000000", etc
            var size = Math.pow(10,(i+1)*3);

            // If the number is bigger or equal do the abbreviation
            if(size <= number) {
                 // Here, we multiply by decPlaces, round, and then divide by decPlaces.
                 // This gives us nice rounding to a particular decimal place.

                 number = Math.round(number*decPlaces/size)/decPlaces;

                 // Handle special case where we round up to the next abbreviation
                 if((number == 1000) && (i < abbrev.length - 1)) {
                     number = 1;
                     i++;
                 }

                 // Add the letter for the abbreviation
                 number += abbrev[i];

                 // We are done... stop
                 break;
            }
        }

        return number;

    }

    //Function to handle the tooltip formatting for the Cumm Sales Widget
    var tickFormatter = function (format , number) {
        var currency_symbol = "<?php echo $sr_const['currency_symbol'];?>";
        number = sr_cumm_number_format(number);
        return currency_symbol + number;
    };

    //Function to handle the tooltip formatting for the Top gateway widget count graph
    var tickFormatter_top_gateway_shipping_sales_count = function (format , number) {
        number = sr_cumm_number_format(number);
        return 'No. of Orders: ' + number;
    };


    //Function to handle the tooltip formatting for the Top Abandoned Products widget graph
    var tickFormatter_top_abandoned_prod_graph = function (format , number) {
        number = sr_cumm_number_format(number);
        return 'Count: ' + number;
    };

    //Function to handle the tooltip formatting for the Top 5 Products Widget
    var tickFormatter_top_prod = function (format , number) {
        var currency_stmbol = "<?php echo $sr_const['currency_symbol'];?>";

        number = sr_cumm_number_format(number);
        
        if($('#sr_opt_top_prod_qty').is(':checked')) {
            return 'Qty: ' + number;
        }
        else {
            return currency_stmbol + number;
        }
    };
     
    var plot_monthly_sales;

    var monthly_display = function(resp){

        jQuery(function($) {
            var sales_trend = new Array();
            
            var tick_format = resp['tick_format'];
            var currency_symbol = resp['currency_symbol'];
            
            $('#sr_sales_graph').empty();
            $('#sr_sales_graph').removeAttr('style'); // remove styling after no data label

            if(resp['result_monthly_sales'].length > 0) {

                if ( (!$(document.body).hasClass('folded')) && jqplot_flag == 1) {                
                    $('#sr_sales_graph, #sr_discount_graph').removeAttr('style');
                    $('#sr_sales_graph, #sr_discount_graph').css("margin-top","-2.75em");
                }
                else if (($(document.body).hasClass('folded')) && jqplot_flag == 1) {
                    $('#sr_sales_graph, #sr_discount_graph').removeAttr('style');
                }

                $('#sr_sales_graph').removeClass().addClass('sr_cumm_sales_graph_not_collapsed sr_cumm_sales_graph');

                for(var i = 0, len = resp['result_monthly_sales'].length; i < len; i++) {
                    sales_trend[i] = new Array();
                    sales_trend[i][0] = resp['result_monthly_sales'][i].post_date;
                    sales_trend[i][1] = resp['result_monthly_sales'][i].sales;
                }

                    $(window).resize(function() {
                        $('#sr_sales_graph').empty();

                        setTimeout(function() {
                            monthly_sales_graph_resize();
                        }, 1000);

                    });

                    var monthly_sales_graph_resize = function() {
                        if (plot) {
                            plot.destroy();
                            plot.replot();
                        }                            
                    }

                    var monthly_sales_graph = function() { 
                        plot = $.jqplot('sr_sales_graph',  [sales_trend], {
                        axes: {
                             yaxis: {  
                                  tickOptions: {
                                  formatter: tickFormatter,
                                },
                                 showTicks: false,
                                 min:-resp['cumm_max_sales']/4,
                                 max: resp['cumm_max_sales'] + resp['cumm_max_sales']/4
                             } ,
                            xaxis: {                    
                                renderer:$.jqplot.DateAxisRenderer, 
                                tickOptions:{formatString:tick_format},
                                showTicks: false,
                                min: resp['cumm_sales_min_date'],
                                max: resp['cumm_sales_max_date']
                            }
                        },
                        axesDefaults: {
                            rendererOptions: {
                                baselineWidth: 1.5,
                                drawBaseline: false // property to hide the axes from the graph
                            }
                              
                        },
                        // actual grid outside the graph
                        grid: {
                            drawGridlines: false,
                            backgroundColor: 'transparent',
                            borderWidth: 0,
                            shadow: false

                        },
                        
                        highlighter: {
                            show: true,
                            sizeAdjust: 0.8,
                            tooltipLocation: 'ne'
                        },
                        cursor: {
                          show: false
                        },
                        series: [
                                { markerOptions: { style:"filledCircle" } },

                        ],
                        animate: true,
                        animateReplot : true,
                        seriesDefaults: {
                            showTooltip:true,
                            rendererOptions: {smooth: true},
                            lineWidth: 2,
                            color : '#368ee0',
                            fillToZero: true,
                            useNegativeColors: false,
                            fillAndStroke: true,
                            fillColor: '#85D1F9',
                            showMarker:true,
                            showLine: true // shows the graph trend line
                        }
                    });
                }

                monthly_sales_graph();

            }

            else {
                $('#sr_sales_graph').removeClass();            
                $('#sr_sales_graph').text("<?php _e('No Data',  $sr_text_domain); ?>");
                $('#sr_sales_graph').addClass('no_data_text');
                $('#sr_sales_graph').css('margin-top','5.4em');
            }
        });
    }

    //Code to handle the display of the tooltips
    jQuery(function($){
        $('#sr_sales_graph').on('jqplotMouseMove', 
            function (ev, seriesIndex, pointIndex, data) {
              if( data ) {
                $('#sr_sales_graph .jqplot-highlight-canvas').css('display','block');
                $('#sr_sales_graph .jqplot-highlighter-tooltip').css('display','block');
                $('#sr_sales_graph .jqplot-highlighter-tooltip').css('background','#E0DCDC');
                $('#sr_sales_graph .jqplot-highlighter-tooltip').css('border','1px solid #E0DCDC');
                $('#sr_sales_graph .jqplot-highlighter-tooltip').css('font-size','1.1em');
                $('#sr_sales_graph .jqplot-highlighter-tooltip').css('font-weight','500');

              }
              else {
                $('#sr_sales_graph .jqplot-highlight-canvas').css('display','none');
                $('#sr_sales_graph .jqplot-highlighter-tooltip').css('display','none'); 
              }
            }
        );

        $('#sr_sales_graph').on('jqplotMouseLeave', 
           function (ev, seriesIndex, pointIndex, data) {
              $('#sr_sales_graph .jqplot-highlight-canvas').css('display','none');
              $('#sr_sales_graph .jqplot-highlighter-tooltip').css('display','none');
           }
        );

    });
    
     </script>
<!-- </div> -->
</div>

<!-- 
// ================================================
// Sales Funnel Widget
// ================================================
 -->

<div id="sr_cumm_sales_funnel" class="cumm_widget">    
    <div class="cumm_header">
      <i class="fa fa-filter icon_cumm_widgets" ></i>
      <?php _e('Sales Funnel', $sr_text_domain); ?>
    </div>

    <!-- <div id="sr_cumm_sales_funnel_data" class="no_data_text" style="line-height: 0.75em; margin-top:2.17em;font-size:3.36em;"> -->
    <div class="ajax_loader cumm_widget" style="display: none;"></div>
    <div id="sr_cumm_sales_funnel_data" style="height:87%;margin-top:1.75em">
        
    </div>
    <script type="text/javascript">
    
    //Function to handle the display part of the Sales Funnel Widget
    var cumm_sales_funnel_display = function(resp) {

        jQuery(function($) {

            $('#sr_cumm_sales_funnel_data').empty();


            if ($('#sr_cumm_sales_funnel_data').hasClass('no_data_text')) {
                $('#sr_cumm_sales_funnel_data').removeClass('no_data_text');
                $('#sr_cumm_sales_funnel_data').removeAttr('style');

                $('#sr_cumm_sales_funnel_data').css('height' ,'87%');
                $('#sr_cumm_sales_funnel_data').css('margin-top' ,'1.75em');
            }
           
            if(resp != '' && resp.hasOwnProperty('kpi') && (resp.kpi.carts > 0 || resp.kpi.carts_prod > 0 || 
                                                            resp.kpi.orders > 0 || resp.kpi.orders_prod > 0 || 
                                                            resp.kpi.corders > 0 || resp.kpi.corders_prod > 0) ) {

              $('#sr_cumm_sales_funnel_data').html('<img id="sr_sales_funnel" style="max-width:70%;height:80%;float:left;" src="'+ "<?php echo $sr_const['img_url'];?>" +'sales_funnel.png">' +
                                                      '<div style="height:80%;font-size:0.7em;">'+
                                                        '<div style="top:18%;position:relative;right:5%;"> '+ resp.kpi.carts + ' Carts • '+ resp.kpi.carts_prod +' Products </div>' +
                                                        '<div style="top:39%;position:relative;right:11%;width:110%;"> '+ resp.kpi.orders + ' Orders Placed • '+ resp.kpi.orders_prod +' Products </div>' +
                                                        '<div style="top:60%;position:relative;right:16%;width:110%;"> '+ resp.kpi.corders + ' Orders Completed • '+ resp.kpi.corders_prod +' Products </div>' +
                                                      '</div>');  

            } else {
                $('#sr_cumm_sales_funnel_data').removeAttr('style');
                $('#sr_cumm_sales_funnel_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
                $('#sr_cumm_sales_funnel_data').addClass('no_data_text');
                $('#sr_cumm_sales_funnel_data').css('margin-top','6.7em');
            }

            Dom_Id[0] = '#sr_cumm_sales_funnel_data';
            ajax_spinner(Dom_Id, false); 

            //     $('#sr_cumm_sales_funnel_data').empty();

              

                                


            //     // $.jqplot('sr_cumm_sales_funnel_data',  [[['Added to Cart', resp['cumm_sales_funnel']['total_products_added_cart']],
            //     //                                          ['Orders Placed', resp['cumm_sales_funnel']['products_purchased_count']],
            //     //                                          ['Orders Completed',resp['cumm_sales_funnel']['products_sold_count']]]], {
                        
            //     //         grid: {
            //     //             backgroundColor: 'transparent',
            //     //             drawBorder: false,
            //     //             shadow: false
            //     //         },

            //     //         gridPadding: {top:-6.5, bottom:47, left:0, right:0},
            //     //         // gridPadding: {top:0, bottom:47, left:0, right:0},

            //     //             series:[{startAngle: -90,
            //     //                   dataLabels: 'percent',
            //     //                   padding: 0, 
            //     //                   sliceMargin: 4}],


            //     //             cursor: {
            //     //               show: false
            //     //             },
                           
            //     //            seriesDefaults: {
            //     //                renderer: $.jqplot.FunnelRenderer,

            //     //                shadow: false,
                            
            //     //                seriesColors: ['#04c0f0','#a6dba0','#e69a01'], // FINAL

            //     //                  rendererOptions:{
            //     //                          sectionMargin: 5,
            //     //                          widthRatio: 0.3,
            //     //                          showDataLabels: true,
            //     //                         dataLabels: [[resp['cumm_sales_funnel']['total_cart_count']+' • '+resp['cumm_sales_funnel']['total_products_added_cart']],
            //     //                                      [resp['cumm_sales_funnel']['orders_placed_count']+' • '+resp['cumm_sales_funnel']['products_purchased_count']],
            //     //                                      [resp['cumm_sales_funnel']['orders_completed_count']+' • '+resp['cumm_sales_funnel']['products_sold_count']]]
            //     //                   }
            //     //             },

            //     //             legend: { 
            //     //                 show:true,
            //     //                 placement: 'outsideGrid',                      
            //     //                 rendererOptions: {
            //     //                     numberRows: 1,
                                    
            //     //                 }, 
            //     //                 location: 's',
            //     //                 marginLeft: '-4.6em',
            //     //                 width: '31em'
            //     //             }

            //     //         });
            // } else {
            //     $('#sr_cumm_sales_funnel_data').removeAttr('style');
            //     $('#sr_cumm_sales_funnel_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
            //     $('#sr_cumm_sales_funnel_data').addClass('no_data_text');
            //     $('#sr_cumm_sales_funnel_data').css('margin-top','6.7em');
            // }

            


            // $('.jqplot-table-legend-swatch').css({"-moz-border-radius": "50px/50px",
            //                                         "-webkit-border-radius": "50px 50px",
            //                                         "border-radius": "50px/50px",
            //                                         "border-width": "6px"
            //                                         });


            // var funnel_legend = ["Added to Cart","Orders Placed","Orders Completed"]; 

            // $('td:contains("Added to Cart"), td:contains("Orders Placed")').css({"min-width": "7em"});
            // $('td:contains("Orders Completed")').css({"min-width": "9em"});
        

            // $('#sr_cumm_sales_funnel_data').on('jqplotDataMouseOver', function (ev, seriesIndex, pointIndex, data) {

            //         var tooltip_text_1 = "";

            //         if (data[0] == "Added to Cart") {

            //             tooltip_text_1 = resp['cumm_sales_funnel']['total_cart_count'] + " Carts";

            //         } else if(data[0] == "Orders Placed") {

            //             tooltip_text_1 = resp['cumm_sales_funnel']['orders_placed_count'] + " Orders Placed";

            //         } else {

            //             tooltip_text_1 = resp['cumm_sales_funnel']['orders_completed_count'] + " Orders Completed";

            //         }


            //       var mouseX = ev.pageX - 150; //these are going to be how jquery knows where to put the div that will be our tooltip
            //       var mouseY = ev.pageY;
            //       $('#chartpseudotooltip').html( '<div>' + tooltip_text_1 + '</div> <div>' + data[1] + " Products" + '</div>');
            //       var cssObj = {
            //           'position': 'absolute',
            //           'font-weight': 'bold',
            //           'left': mouseX + 'px', //usually needs more offset here
            //           'top': mouseY + 'px',
            //           'border' : '1px solid #6EADE7',
            //           'background-color': 'white',
            //           'font-size': '1.1em',
            //           'font-weight': '500',
            //           'z-index':'1'
            //       };
            //       $('#chartpseudotooltip').css(cssObj);
            //       $('#chartpseudotooltip').show();

            //   });

            //   $('#sr_cumm_sales_funnel_data').on('jqplotDataUnhighlight', function (ev) {
            //       $('#chartpseudotooltip').empty().hide();
            //   });
      });

    }

    </script>

</div>


<!-- 
// ================================================
// Top Products Widget
// ================================================
 -->

<div id="sr_cumm_top_prod" class="cumm_widget">    
    <div id="sr_cumm_top_prod_check" style="height:100%;width:100%;">
      <script type="text/javascript">

        //Funciton to handle the graph display part for Top Products Widget
        var top_prod_graph_display = function (display_data,tick_format,tick_format_yaxis,top_prod_data,min_date,max_date,plot_nm) {

            jQuery(function($) {

                // $(window).resize(function() {
                //    top_prod_graph_resize();
                // });

                var top_prod_graph_resize = function() {
                    
                    for(var i = 0, len = display_data.length; i < len; i++){

                        // var plot = plot_nm + i;
                        if (plot_nm_i) {
                            plot_nm_i.destroy();
                            plot_nm_i.replot();
                        }
                    }
                                                
                }

                for(var i = 0, len = display_data.length; i < len; i++){

                      var plot = plot_nm + i;

                      jQuery('#'+plot+'').empty(); // Making the plot as empty

                      plot_nm_i = jQuery.jqplot(plot,  [display_data[i]], {
                          axes: {
                               yaxis: {
                                   tickOptions: {
                                    formatter: tick_format_yaxis
                                   },
                                   showTicks: false,
                                   min: -top_prod_data[i]/3,
                                   max: top_prod_data[i] + top_prod_data[i]/3
                               } ,
                              xaxis: {
                                  renderer:$.jqplot.DateAxisRenderer, 
                                  tickOptions:{formatString:tick_format},
                                  showTicks: false,
                                  min: min_date,
                                  max: max_date
                              }
                          },
                          axesDefaults: {
                              rendererOptions: {
                                  baselineWidth: 1.5,
                                  drawBaseline: false // property to hide the axes from the graph
                              }
                          },
                          // actual grid outside the graph
                          grid: {
                              drawGridlines: false,
                              backgroundColor: 'transparent',
                              borderWidth: 0,
                              shadow: false
                          },
                          
                          highlighter: {
                              show: true,
                              sizeAdjust: 0.01,
                              lineWidthAdjust : 0.1,
                              tooltipLocation: 'ne'
                          },
                          cursor: {
                            show: false
                          },
                          series: [
                                  { markerOptions: { style:"filledCircle" } },

                          ],
                          animate: true,
                          animateReplot : true,
                          seriesDefaults: {
                              showTooltip:true,
                              rendererOptions: {smooth: true},
                              lineWidth:  1.5,
                              color : '#368ee0',
                              fillAndStroke: true,
                              fillColor: '#85D1F9',
                              fillToZero: true,
                              useNegativeColors: false,
                              showMarker:false,
                              showLine: true // shows the graph trend line
                          }
                      }
                      );
                  }
              });
          }

        //Function to sending the AJAX request on click of the Toggle Button
        var get_top_prod_graph_data = function (opt_id) {

            jQuery(function($) {

              // $.ajax({
              //       type : 'POST',
              //       // url : '<?php echo content_url("/plugins/smart-reporter-for-wp-e-commerce/sr/json-woo.php"); ?>',
              //       url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=sr_get_stats' : ajaxurl + '?action=sr_get_stats', 
              //       dataType:"text",
              //       async: false,
              //       action: 'sr_get_stats',
              //       data: {
              //           cmd: 'top_products_option',
              //           // security : "<?php echo $sr_const['security']; ?>",
              //           top_prod_option: opt_id,
              //           option : 1,
              //           start_date : $("#startdate").val(),
              //           end_date : $("#enddate").val(),
              //           params: '<?php echo json_encode($sr_const); ?>'
              //           // SR_IS_WOO22 : "<?php echo $sr_const['is_woo22']; ?>",
              //           // file: "<?php echo $sr_json_file_nm; ?>"
              //       },
              //       success: function(response) {
              //           var myJsonObj    = $.parseJSON(response);
              //           var top_prod_graph_data = new Array();
              //           var tick_format_yaxis;
              //           var top_prod_data = new Array();

              //           if (opt_id == 'sr_opt_top_prod_price') {
              //             tick_format_yaxis = "<?php echo $sr_const['currency_symbol'];?>%s";
              //           }
              //           else {
              //             tick_format_yaxis = 'Qty: %s';
              //           }

              //           for(var i = 0; i < myJsonObj['graph_data'].length; i++) { 
              //               var len = myJsonObj['graph_data'][i]['graph_data'].length;
              //               var graph_data = new Array();
              //               for(var j = 0; j < len; j++){
              //                   graph_data[j] = new Array();
              //                   graph_data[j][0] = myJsonObj['graph_data'][i]['graph_data'][j].post_date;
              //                   graph_data[j][1] = myJsonObj['graph_data'][i]['graph_data'][j].sales;
              //               }
              //               top_prod_graph_data[i] = graph_data;
              //               top_prod_data[i] = myJsonObj['graph_data'][i]['max_value'];
              //           }
                        
              //           if(top_prod_graph_data.length > 0) {
              //               top_prod_graph_display(top_prod_graph_data,myJsonObj.tick_format,tickFormatter_top_prod,top_prod_data,myJsonObj['cumm_sales_min_date'],myJsonObj['cumm_sales_max_date'],'span_top_prod_');
              //           }
              //           else {
              //               $('#top_prod_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
              //               $('#top_prod_data').addClass('no_data_text');
              //               $('#top_prod_data').css('margin-top','6.7em');
              //           }
              //       }
              //   });
            });
          }


          //Code to handle the display of the tooltips for the Top Products Widget

          // #sr_cumm_taxes_data,
          jQuery(function($){
              $("div[id^='span_top_prod_'], div[id^='span_top_gateway_sales_amt_'], div[id^='span_top_gateway_sales_count_'], div[id^='span_top_abandoned_prod_'], div[id^='span_top_shipping_method_sales_amt_'], div[id^='span_top_shipping_method_sales_count_']").live('jqplotMouseMove', 
                  function (ev, seriesIndex, pointIndex, data) {

                    var plot1 = '#' + this.id + ' .jqplot-highlight-canvas';
                    var plot2 = '#' + this.id + ' .jqplot-highlighter-tooltip';  

                    if (data) {
                        $( plot1 ).css('display','block');
                        $( plot2 ).css('display','block');  
                        $( plot2 ).css('background','#E0DCDC');
                        $( plot2 ).css('border','1px solid #E0DCDC');
                        $( plot2 ).css('font-size','1.1em');
                        $( plot2 ).css('font-weight','500');
                    }
                    else {
                        $( plot1 ).css('display','none');
                        $( plot2 ).css('display','none');
                    }

                  }
              );

              
                $("div[id^='span_top_prod_'], div[id^='span_top_gateway_sales_amt_'], div[id^='span_top_gateway_sales_count_'], div[id^='span_top_abandoned_prod_'], div[id^='span_top_shipping_method_sales_amt_'], div[id^='span_top_shipping_method_sales_count_']").live('jqplotMouseLeave', 
                 function (ev, seriesIndex, pointIndex, data) {

                    var plot1 = '#' + this.id + ' .jqplot-highlight-canvas';
                    var plot2 = '#' + this.id + ' .jqplot-highlighter-tooltip';

                    $( plot1 ).css('display','none');
                    $( plot2 ).css('display','none');
                 }
              );
            


            //Code to handle the click events of the Toggle Button
            $("#sr_opt_top_prod_price").on( "click", function() {

                if ($("#sr_opt_top_prod_price").is(":checked")) {

                    if (!($('#sr_opt_top_prod_price_label').hasClass('switch-label-on'))) {

                        $('#sr_opt_top_prod_price_label').addClass('switch-label-on');
                        $('#sr_opt_top_prod_price_label').removeClass('switch-label-off');

                        $('#sr_opt_top_prod_qty_label').removeClass('switch-label-on');
                        $('#sr_opt_top_prod_qty_label').addClass('switch-label-off');

                        $("#top_prod_selection_toggle").css('left','0em');

                        $("#sr_opt_top_prod_qty").prop("checked",false);
                        $("#sr_opt_top_prod_price").prop("checked",true);

                        // get_top_prod_graph_data('sr_opt_top_prod_price');
                        top_prod_display(sr_data.tp_data.kpi.sales, 'tps_');
                    }
                    else {

                        $('#sr_opt_top_prod_price_label').addClass('switch-label-off');
                        $('#sr_opt_top_prod_price_label').removeClass('switch-label-on');

                        $('#sr_opt_top_prod_qty_label').removeClass('switch-label-off');
                        $('#sr_opt_top_prod_qty_label').addClass('switch-label-on');


                        $("#sr_opt_top_prod_qty").prop("checked",true);
                        $("#sr_opt_top_prod_price").prop("checked",false);

                        $("#top_prod_selection_toggle").css('left','2.0em');

                        // get_top_prod_graph_data('sr_opt_top_prod_qty');
                        top_prod_display(sr_data.tp_data.kpi.qty, 'tpq_');
                    }

                    sr_plot_charts(sr_data.tp_data.chart);

                    $('#sr_opt_top_prod_price_label').removeClass('switch-label_price');

                }

              });


              $("#sr_opt_top_prod_qty").click( function() {

                if ($("#sr_opt_top_prod_qty").is(":checked")) {
                    
                    if (!($('#sr_opt_top_prod_qty_label').hasClass('switch-label-on'))) {

                        $('#sr_opt_top_prod_qty_label').addClass('switch-label-on');
                        $('#sr_opt_top_prod_qty_label').removeClass('switch-label-off');

                        $('#sr_opt_top_prod_price_label').removeClass('switch-label-on');
                        $('#sr_opt_top_prod_price_label').addClass('switch-label-off');

                        $("#top_prod_selection_toggle").css('left','2.0em');

                        $("#sr_opt_top_prod_qty").prop("checked",true);
                        $("#sr_opt_top_prod_price").prop("checked",false);

                        // get_top_prod_graph_data('sr_opt_top_prod_qty');
                        top_prod_display(sr_data.tp_data.kpi.qty, 'tpq_');
                    }
                    else {

                        $('#sr_opt_top_prod_qty_label').removeClass('switch-label-on');
                        $('#sr_opt_top_prod_qty_label').removeClass('switch-input:checked');

                        $('#sr_opt_top_prod_price_label').removeClass('switch-label-off');
                        $('#sr_opt_top_prod_price_label').addClass('switch-label-on');

                        $("#sr_opt_top_prod_qty").prop("checked",false);
                        $("#sr_opt_top_prod_price").prop("checked",true);

                        $("#top_prod_selection_toggle").css('left','0em');

                        // get_top_prod_graph_data('sr_opt_top_prod_price');
                        top_prod_display(sr_data.tp_data.kpi.sales, 'tps_');
                    }

                    sr_plot_charts(sr_data.tp_data.chart);

                    $('#sr_opt_top_prod_price_label').removeClass('switch-label_price');

                }

              });

            });

        </script>

        <div class="cumm_header">
    
            <i class = "fa fa-star icon_cumm_widgets"> </i>     

            <?php _e('Top Products', $sr_text_domain); ?>

                <span id="sr_cumm_top_prod_detailed_view" title="Expand" class="top_prod_detailed_view" >
                    <i id="sr_cumm_top_prod_detailed_view_icon" class= "fa fa-ellipsis-h icon_cumm_widgets" style="color:#B1ADAD" ></i>
                </span>

                <div class="switch switch-blue">
                  <input type="radio" class="switch-input" name="top_prod_toggle_price_option_nm" value="sr_opt_top_prod_price" id="sr_opt_top_prod_price" style="display:none">
                  <label id="sr_opt_top_prod_price_label" for="sr_opt_top_prod_price" class="switch-label switch-label_price switch-label-on"> <?php _e('Price', $sr_text_domain); ?></label>
                  <input type="radio" class="switch-input" name="top_prod_toggle_price_option_nm" value="sr_opt_top_prod_qty" id="sr_opt_top_prod_qty" style="display:none">
                  <label id="sr_opt_top_prod_qty_label" for="sr_opt_top_prod_qty" class="switch-label switch-label-off"> <?php _e('Qty', $sr_text_domain); ?></label>
                  <span id="top_prod_selection_toggle" class="switch-selection"></span>
                </div>

        </div>

        <div class="ajax_loader cumm_widget" style="display : none;"></div>
        <div id = "top_prod_data">
            
        </div>
    </div>

<!-- 
// ================================================
// Top Products Detailed View Widget
// ================================================
 -->
<a title="Top Products Detailed View" class="ajax-popup-link" id="detailed_view_link"></a>

<div id="top_prod_detailed_view_widget" class="white-popup mfp-hide" >

<script type="text/javascript">

    jQuery(function($){
        var sr_detailed_view_html = '<div id="sr_prod_details"  class="prod_details"> \
                                        <table id="prod_details_table" class="details_table"></table> \
                                    </div> \
                                    <div id="sr_prod_sales_details" class="prod_sales_details"> \
                                        <div id="sr_kpi_details" class="kpi_details"> \
                                            <table id="kpi_display_table" class="kpi_table"></table> \
                                        </div> \
                                        <div id="sr_recent_orders" class="recent_orders"> \
                                            <h3 id="recent_orders_heading" class="recent_orders_heading"><?php _e("Recent Orders" , $sr_text_domain); ?></h3>\
                                            <div id="recent_orders_container" class="recent_orders_container"> \
                                            <table id="recent_orders_table" class="recent_orders_table"></table> \
                                            </div> \
                                        </div> \
                                        <div id="sr_detail_sales_graph_funnel" class="sales_graph_funnel"> \
                                            <div id="draw_sales_graph" class="draw_sales_graph"></div> \
                                            <div id="draw_sales_funnel" class="draw_sales_funnel"></div> \
                                        </div> \
                                        <div id="sr_prod_sales_donuts" class="prod_sales_donuts"> \
                                            <div id="sr_sales_donut" class="draw_sales_donut"></div> \
                                        </div> \
                                    </div>';
                   
                        $("#sr_cumm_top_prod_detailed_view").on('mouseenter',function() {
                            $('#sr_cumm_top_prod_detailed_view_icon').css("color","#FFFFFF");
                        });

                        $("#sr_cumm_top_prod_detailed_view").on('mouseleave',function() {
                            $('#sr_cumm_top_prod_detailed_view_icon').css("color","#B1ADAD");
                        });

                        //code to display Top Product Detailed View Widget
                        $("#sr_cumm_top_prod_detailed_view").on('click', function() {
                            <?php 
                                    if (defined('SRPRO') && SRPRO === true) {
                                        ?>
                                    $('a#detailed_view_link').trigger('click');
                                    
                            <?php   }else {?>
                           
                                    alert('<?php _e( "Sorry! Expand Detailed View functionality is available only in Pro version" , $sr_text_domain );?>');
                            <?php   }?>      
                        });

                        var detail_view_data;// to store all the data for detailed view widget
                        var currency = "<?php echo $sr_const['currency_symbol']; ?>";
                        var decimals = "<?php echo $sr_const['decimal_places']; ?>";

                        $('.ajax-popup-link').magnificPopup({

                            items: {
                              src: '#top_prod_detailed_view_widget',
                              type: 'inline'
                          },
                          closeBtnInside: true,
                          // closeOnBgClick: true,
                          showCloseBtn  : true,
                          tError: "<?php _e('The content could not be loaded.',  $sr_text_domain ); ?>",
                          callbacks:{
                            open: function() {
                                // $.ajax({
                                //             type : 'POST',
                                //             // url : '<?php echo content_url("/plugins/smart-reporter-for-wp-e-commerce/sr/json-woo.php"); ?>',
                                //             url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=sr_get_stats' : ajaxurl + '?action=sr_get_stats',
                                //             dataType:"text",
                                //             async: false,
                                //             action: 'sr_get_stats',
                                //             data: {
                                //                 cmd: 'monthly_detailed_view',
                                //                 detailed_view: 1,
                                //                 total_monthly_sales : Master_myJsonObj['detailed_view_total_monthly_sales'],
                                //                 start_date : $("#startdate").val(),
                                //                 end_date : $("#enddate").val(),
                                //                 params : '<?php echo json_encode($sr_const); ?>'
                                //             },
                                //             success: function(response) {

                                //                 detail_view_data  = $.parseJSON(response);
                                //                 var table_html;
                                                 
                                //                 for (var i = 0; i < detail_view_data['top_prod_detail_view_data'].length; i++) {
                                                      
                                //                       var image = detail_view_data['top_prod_detail_view_data'][i].image;
                                //                       var prod_name = detail_view_data['top_prod_detail_view_data'][i].product_name;
                                //                       var prod_category = detail_view_data['top_prod_detail_view_data'][i].category;
                                //                       var prod_name_trimmed = "";
                                //                       var product_sales_show= detail_view_data['top_prod_detail_view_data'][i].product_sales_show;
                                //                       var prod_qty = detail_view_data['top_prod_detail_view_data'][i].product_qty;
                                //                       var prod_sku= detail_view_data['top_prod_detail_view_data'][i].sku;
                                //                       var prod_sku_cap = prod_sku.toUpperCase();
                                                       
                                //                       if (prod_name.length >= 52) {

                                //                         prod_name_trimmed = prod_name.substring(0,51) + "...";
                                                      
                                //                       } else {
                                                          
                                //                           prod_name_trimmed = prod_name;
                                                      
                                //                       }
                                                     
                                //                       if (prod_category.length >= 60) {

                                //                           prod_category_trimmed = prod_category.substring(0,56) + "...";
                                                      
                                //                       } else {

                                //                           prod_category_trimmed = prod_category;
                                                      
                                //                       }
                                                      
                                //                       table_html += '<tr id="'+i+'" ><td title = "'+ prod_name + '" style="width:5%;">' + image + '</td><td>' + '<div style="margin-top: 0.8em; color: #5C5C5C;" class="details_display">' + prod_name_trimmed + '</div>';

                                //                       if( prod_category_trimmed != "") {
                                //                          table_html += '<div class="details_display">' + prod_category_trimmed + '</div>';
                                //                       }
                                                                                                              
                                //                       if( prod_sku != "" ){
                                                       
                                //                         table_html += '<div style="font-family: monospace;" class="details_display">' + prod_sku_cap + ' • <span class="sales_highlight">' + product_sales_show + '</span> • ' + prod_qty + '</div></td></tr>';
                                                      
                                //                       } else {
                                                        
                                //                         table_html += '<div class="details_display"><span class="sales_highlight">'+ product_sales_show + '</span> • ' + prod_qty + '</div></td></tr>';
                                                      
                                //                       }

                                //                 };


                                //                 if(detail_view_data['top_prod_detail_view_data'].length > 0) {
                                //                         $('#top_prod_detailed_view_widget').removeClass('no_data_text mfp-hide');
                                //                         $('#top_prod_detailed_view_widget').addClass('white-popup');
                                //                         $('#top_prod_detailed_view_widget').html(sr_detailed_view_html);
                                //                         $('#prod_details_table').html(table_html);
                                //                         $('#prod_details_table').css("cursor" , "pointer");
                                //                         $('tr#0').trigger('click'); // default load sales & graph data of first row.
                                //                 }
                                //                 else {
                                //                         $('#top_prod_detailed_view_widget').empty();
                                //                         $('#top_prod_detailed_view_widget').append('<div class="no_data_text" style="margin-top:2.37em;height:3em;"><?php _e("No Data" , $sr_text_domain); ?></div>');
                                //                     }
                                //             }
                                //         });// end of ajax

                                  $('#top_prod_detailed_view_widget').empty();
                                  $('#top_prod_detailed_view_widget').append('<div class="no_data_text" style="margin-top:2.37em;height:3em;"><?php _e("Comming Soon" , $sr_text_domain); ?></div>');

                                },

                            parseAjax: function(mfpResponse) {
                            }
                        }
                    });

                        // On click of any row display sales data of that associative product
                            var top_prod_detailed_plot = '';
                            var index, prod_sales, prod_qty;
                            var discount_sales, discount_qty, refund_sales, refund_qty;
                            var non_discount_sales, non_discount_qty,total_sales, total_qty;
                            

                        $('#prod_details_table tr').live('click', function(){ 
                            var row     = $(this).attr('id');
                                index   = detail_view_data['top_prod_detail_view_data'][row];    
                            var tick_format           = detail_view_data['tick_format'];
                            var prod_name             = index.product_name;
                                prod_sales            = index.product_sales;
                                prod_qty              = index.product_qty;
                            var discount              = index.discount;
                                discount_qty          = index.discount_qty;
                                discount_qty_show     = index.discount_qty_show;
                                discount_sales        = index.discount_sales;
                                discount_sales_show   = index.discount_sales_show;
                            var no_of_orders          = index.sales_count;
                            var one_sale_every        = index.one_sale_every;
                            var per_day_sales         = index.per_day_sales;
                            var percent_of_total_sales    = index.percent_of_total_sales;
                            var percent_of_total_quantity = index.percent_of_total_quantity;
                            var abandonment_rate      = index.abandonment_rate;
                                refund_qty            = index.refund_qty;
                                refund_qty_show       = index.refund_qty_show;
                                refund_sales          = index.refund_sales;
                                refund_sales_show     = index.refund_sales_show;
                            var refund_rate           = index.refund_rate;

                                non_discount_sales    = index.non_discount_sales;
                                non_discount_sales_show    = index.non_discount_sales_show;
                                total_sales           = index.total_sales;
                                non_discount_qty      = index.non_discount_qty;
                                non_discount_qty_show = index.non_discount_qty_show;
                            
                            table_data = '<tr><td><span class="kpi_widgets_price">'+per_day_sales+'</span><p class ="kpi_widgets_text"><?php _e(" per Day Sales" , $sr_text_domain); ?></p></td>';
                            table_data+= '<td><span class="kpi_widgets_price">'+one_sale_every+'</span><p class ="kpi_widgets_text"><?php _e("1 Sale Every" , $sr_text_domain); ?></p></td>';
                            table_data+= '<td><span class="kpi_widgets_price">'+no_of_orders+'</span><p class ="kpi_widgets_text"><?php _e( "Orders Placed" , $sr_text_domain );?></p></td>';
                            table_data+= '<td><span class="kpi_widgets_price">'+abandonment_rate+'</span><p class ="kpi_widgets_text"><?php _e( "Abandonment Rate" , $sr_text_domain); ?></p></td>';
                            table_data+= '<td><span class="kpi_widgets_price">'+refund_rate+'</span><p class ="kpi_widgets_text"><?php _e("Refund Rate " , $sr_text_domain); ?></p></td></tr>';


                            $('#kpi_display_table').html(table_data);
                                                           
                              
                            // code to display last few ordes of every product
                            var last_few_orders = '';

                            for(var i = 0; i < index['last_few_orders']['orderDetails'].orderTotalCount; i++ ){
                                   
                              var date           = index['last_few_orders']['orderDetails']['order'][i].date;
                              var cust_name      = index['last_few_orders']['orderDetails']['order'][i].cname;
                              var cust_name_trimmed = "";
                              var country_code   = index['last_few_orders']['orderDetails']['order'][i].country_code;
                              var country_name   = index['last_few_orders']['orderDetails']['order'][i].country_name;
                              var country_name_trimmed = "";
                              var price          = index['last_few_orders']['orderDetails']['order'][i].totalprice;
                              var order_id       = index['last_few_orders']['orderDetails']['order'][i].purchaseid;
                              var editlink       = '<?php echo admin_url("post.php?post='+order_id+'&action=edit"); ?>';
                              
                                  cust_name      = cust_name.replace(/^\s+|\s+$/g,"");

                                  if (cust_name.length >= 25) {
                                    cust_name_trimmed = cust_name.substring(0,24) + "...";
                                  }
                                  else {
                                   cust_name_trimmed = cust_name;
                                  }

                                  if(country_name.length >=35){
                                    country_name_trimmed = country_name.substring(0,34) + "...";
                                  }
                                  else{
                                    country_name_trimmed = country_name;
                                  }
                                                           
                              
                              last_few_orders +='<tr><td style ="width:5em;" title = "' + date + '">' + date + '</td><td style ="width:20em;" title ="' + cust_name + '"><a href="' + editlink + '" target="_blank">' + cust_name_trimmed + '</a>' + ' - ' + country_name_trimmed + '</td><td style ="width:5em;" >' + price + '</td></tr>';
                            }
                              $( '#recent_orders_heading' ).css( 'display' , 'block' );
                              $( '#recent_orders_table' ).html( last_few_orders );
                            // code to display sales funnel
                                var sales_funnel = [];

                                if( index.sales_funnel !== 0 ) {

                                  sales_funnel[ 'total_cart_qty' ]      = index[ 'sales_funnel' ][ 'added_qty' ];
                                  sales_funnel[ 'total_ordered_qty' ]   = index[ 'sales_funnel' ][ 'ordered_qty' ];
                                  sales_funnel[ 'total_completed_qty' ] = index[ 'sales_funnel' ][ 'completed_qty' ];

                                } else{
                                  sales_funnel = 0;
                                    
                                }

                                if ( $( '#draw_sales_funnel' ).hasClass( 'no_data_text' ) ) {

                                    $( '#draw_sales_funnel' ).removeClass( 'no_data_text' );
                                    $( '#draw_sales_funnel' ).removeAttr( 'style' );
                                    $( '#draw_sales_funnel' ).addClass('draw_sales_funnel');
                                }
                                    
                                $('#draw_sales_funnel').empty();
                                
                                if( sales_funnel !== 0) {
                                    $.jqplot('draw_sales_funnel',  [[['Added to Cart', sales_funnel['total_cart_qty']],
                                                                         ['Orders Placed', sales_funnel['total_ordered_qty']],
                                                                         ['Orders Completed',sales_funnel['total_completed_qty']]]], {
                                        title: '<?php _e("Sales Funnel" , $sr_text_domain); ?>',                                        
                                        grid: {
                                            backgroundColor: 'transparent',
                                            drawBorder: false,
                                            shadow: false
                                        },

                                        gridPadding: {top:-6.5, bottom:47, left:0, right:0},
                                        // gridPadding: {top:0, bottom:47, left:0, right:0},

                                            series:[{startAngle: -90,
                                                  dataLabels: 'percent',
                                                  padding: 0, 
                                                  sliceMargin: 4}],


                                            cursor: {
                                              show: false
                                            },
                                           
                                           seriesDefaults: {
                                               renderer: $.jqplot.FunnelRenderer,

                                               shadow: false,
                                            
                                               seriesColors: ['#04c0f0','#a6dba0','#e69a01'], // FINAL

                                                 rendererOptions:{
                                                         highlightMouseOver: false,
                                                         sectionMargin: 5,
                                                         widthRatio: 0.3,
                                                         showDataLabels: true,
                                                         dataLabels: [[sales_funnel['total_cart_qty']],
                                                                     [sales_funnel['total_ordered_qty']],
                                                                     [sales_funnel['total_completed_qty']]]
                                                  }
                                            },

                                            legend: { 
                                                show:true,
                                                placement: 'outsideGrid',                      
                                                // placement: 'outside',                      
                                                rendererOptions: {
                                                    numberRows: 3
                                                }, 
                                                location: 'w'
                                            }
                                    });

                                
                                
                                $('#draw_sales_funnel').find('.jqplot-title').removeClass('jqplot-title').addClass('sales_funnel_title');                                
                                $('#draw_sales_funnel').find('.jqplot-table-legend').addClass('sales_funnel_table-legend');
                                
                                } else {
                                    $('#draw_sales_funnel').removeAttr('style');
                                    $('#draw_sales_funnel').append('<div class="sales_funnel_title" style="color: #727277;"><?php _e("Sales Funnel" , $sr_text_domain); ?></div>');
                                    $('#draw_sales_funnel').append('<div class="no_data_text" ><?php _e("No Data" , $sr_text_domain); ?></div>');
                                }

                                $('.jqplot-table-legend-swatch').addClass('sales_funnel_table-legend-swatch');
                                
                                var funnel_legend = ["Added to Cart","Orders Placed","Orders Completed"]; 

                                $('td:contains("Added to Cart"), td:contains("Orders Placed")').css({"min-width": "7em"});
                                $('td:contains("Orders Completed")').css({"min-width": "9em"});

                              // code to load graph data in array format for jqplot
                              for(var i = 0, len = index['graph_data'].length; i < len; i++) {
                                    sales_trend[i] = new Array();
                                    sales_trend[i][0] = index['graph_data'][i].post_date;
                                    sales_trend[i][1] = index['graph_data'][i].sales;
                                };

                            var monthly_detail_view_sales_graph = function() { 

                                
                                        if (top_prod_detailed_plot != '') {
                                            top_prod_detailed_plot.destroy();
                                        }
                                        
                                        if( prod_sales !== 0) {
                                        
                                           top_prod_detailed_plot = $.jqplot('draw_sales_graph',  [sales_trend], {
                                           title: '<?php _e("Sales Trend" , $sr_text_domain); ?>',
                                           axes: {
                                                     yaxis: {  
                                                          tickOptions: {
                                                          formatter: tickFormatter,
                                                        },
                                                         // label: 'Sales',
                                                         showTicks: true,
                                                         // min:-index.max_value/4,
                                                         min: 0,
                                                         max: precise_round( (index.max_value + index.max_value/4) , decimals)
                                                     } ,
                                                    xaxis: {                    
                                                        // label: 'Date',
                                                        renderer:$.jqplot.DateAxisRenderer, 
                                                        tickOptions:{formatString:tick_format},
                                                        showTicks: true,
                                                        min: detail_view_data['sales_min_date'],
                                                        max: detail_view_data['sales_max_date']
                                                    }
                                                },
                                                axesDefaults: {
                                                    // labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                                                    rendererOptions: {
                                                        baselineWidth: 1.5,
                                                        // drawBaseline: false // property to hide the axes from the graph
                                                        drawBaseline: true // property to hide the axes from the graph
                                                    }
                                                      
                                                },
                                                // actual grid outside the graph
                                                grid: {
                                                    drawGridlines: false,
                                                    backgroundColor: 'transparent',
                                                    borderWidth: 0,
                                                    shadow: false

                                                },
                                                
                                                highlighter: {
                                                    show: true,
                                                    sizeAdjust: 0.8,
                                                    tooltipLocation: 'ne'
                                                },
                                                cursor: {
                                                  show: false
                                                },
                                                series: [
                                                        { markerOptions: { style:"filledCircle" } },

                                                ],
                                                animate: true,
                                                animateReplot : true,
                                                seriesDefaults: {
                                                    showTooltip:true,
                                                    rendererOptions: {smooth: true},
                                                    lineWidth: 2,
                                                    color : '#368ee0',
                                                    fillToZero: true,
                                                    useNegativeColors: false,
                                                    fillAndStroke: true,
                                                    fillColor: '#85D1F9',
                                                    showMarker:true,
                                                    showLine: true // shows the graph trend line
                                            }
                                        });// closing of jqplot

                                    } else{
                                        
                                        $('#draw_sales_graph').removeAttr('style');
                                        $('#draw_sales_graph').append('<div class="sales_graph_donut_title" ><?php _e("Sales Trend" , $sr_text_domain); ?></div>');
                                        $('#draw_sales_graph').append('<div class="no_data_text" ><?php _e("No Data" , $sr_text_domain); ?></div>');
                                    }
                                }
                            
                            monthly_detail_view_sales_graph();// call function to draw graph 

                             $('#draw_sales_graph').find('.jqplot-title').removeClass("jqplot-title").addClass("sales_graph_donut_title");
                             $('#draw_sales_graph').find('.sales_graph_donut_title').css({ "top" : "-1em" });                       
                          
                            // code to draw sales kpi donut

                            var sales_kpi_donut = function() {

                                var kpi_data = new Array();

                                $('#sr_sales_donut').empty();
                                
                                if( prod_sales !== 0) {

                                        $('#sr_sales_donut').empty();

                                        if ($('#sr_sales_donut').hasClass('no_data_text')) {
                                            
                                            $('#sr_sales_donut').removeClass('no_data_text');
                                            $('#sr_sales_donut').removeAttr('style');
                                            $('#sr_sales_donut').removeAttr('style');
                                            $('#sr_sales_donut').addClass('draw_sales_donut');
                                        }
                                                                           
                                        kpi_data[0]    = new Array();
                                        kpi_data[0][0] = 'Non-Discount';
                                        kpi_data[0][1] = non_discount_sales;
                                        kpi_data[0][2] = precise_round((non_discount_sales/total_sales)*100 , decimals) + '%';
                                        kpi_data[0][3] = non_discount_qty_show;
                                        kpi_data[0][4] = non_discount_sales_show;

                                        kpi_data[1]    = new Array();
                                        kpi_data[1][0] = 'Discount';
                                        kpi_data[1][1] = discount_sales;
                                        
                                        if(discount_sales > 0){
                                        
                                        kpi_data[1][2] = precise_round((discount_sales /total_sales)*100 , decimals) + '%';
                                        
                                        } else {
                                            kpi_data[1][2]= 0.00 + '%';
                                        }

                                        kpi_data[1][3] = discount_qty_show;
                                        kpi_data[1][4] = discount_sales_show;

                                        kpi_data[2] = new Array();
                                        kpi_data[2][0] = 'Refund';
                                        kpi_data[2][1] = refund_sales;
                                        if(refund_sales > 0){
                                        
                                        kpi_data[2][2] = precise_round((refund_sales /total_sales)*100 , decimals) + '%';
                                        
                                        } else {
                                            kpi_data[2][2]= 0.00 + '%';
                                        }
                                        
                                        kpi_data[2][3] = refund_qty_show;
                                        kpi_data[2][4] = refund_sales_show


                                        $.jqplot('sr_sales_donut',  [kpi_data], {
                                        title: '<?php _e("Sales Distribution", $sr_text_domain); ?>',
                                            grid: {
                                                backgroundColor: 'transparent',
                                                drawBorder: false,
                                                shadow: false
                                            },

                                            gridPadding: {top:-6.5, bottom:47, left:0, right:0},
                                            // gridPadding: {top:0, bottom:47, left:0, right:0},

                                            series:[{startAngle: -90,
                                                  dataLabels: 'percent',
                                                  padding: 0, 
                                                  sliceMargin: 4}],

                                            cursor: {
                                              show: false
                                            },

                                            seriesDefaults: {
                                                shadow: false,
                                                // seriesColors: ['#04c0f0','#a6dba0','#e66101','#5e3c99'], // FINAL
                                                seriesColors: ['#04c0f0','#a6dba0','#e66101','#69639d'], // FINAL
                                                
                                                renderer: jQuery.jqplot.DonutRenderer,
                                                rendererOptions: {
                                                    highlightMouseOver: false,
                                                    innerDiameter: 50,
                                                    diameter : 80,
                                                    thickness: 20
                                                }
                                            },
                                            legend: { 
                                                show:true,
                                                placement: 'outsideGrid',                  
                                                rendererOptions: {
                                                    numberRows: 3
                                                }, 
                                                location: 'w',
                                                bottom: '1.875em',
                                                borderWidth: 0
                                            }
                                        });

                                        
                                        $('#sr_sales_donut').find('.jqplot-title').removeClass("jqplot-title").addClass("sales_graph_donut_title");
                                        $('#sr_sales_donut').find('.sales_graph_donut_title').css({ "top" : "-2em" });
                                        $('#sr_sales_donut').find('.jqplot-table-legend').addClass('sales_donut_table-legend');
                                        $('.jqplot-table-legend-swatch').addClass('funnel_donut_table-legend-swatch'); 

                                        $('.sales_donut_table-legend tr').each(function(index){
                                             $(this).append('<td class = "jqplot-table-legend sales_donut_table-legend" style ="padding-top: 0px; font-size: 1.2em;" >' + kpi_data[index][2] + '</td>');
                                             $(this).append('<td class = "jqplot-table-legend sales_donut_table-legend" style ="padding-top: 0px; " >' + '•' + '</td>');
                                             $(this).append('<td class = "jqplot-table-legend sales_donut_table-legend" style ="padding-top: 0px; font-size: 1.2em;" >' + kpi_data[index][4] + '</td>');
                                             $(this).append('<td class = "jqplot-table-legend sales_donut_table-legend" style ="padding-top: 0px; " >' + '•' + '</td>');
                                             $(this).append('<td class = "jqplot-table-legend sales_donut_table-legend" style ="padding-top: 0px; font-size: 1.2em;" >' + kpi_data[index][3] + '</td>');
                                        });            

                                } else {
                                    $('#sr_sales_donut').removeAttr('style');
                                    $('#sr_sales_donut').append('<div class="sales_graph_donut_title" ><?php _e("Sales Distribution" , $sr_text_domain); ?></div>');
                                    $('#sr_sales_donut').append('<div class="no_data_text" ><?php _e("No Data" , $sr_text_domain); ?></div>');
                                }
                                $('.jqplot-table-legend').css({"border": "0px solid #ccc"});
                            }

                         sales_kpi_donut(); // call function to draw sales kpi donut
                });// closing of tr click event function

     });//closing of jQuery main function
</script>

</div> 

  <script type="text/javascript">

    //Function to handle the display part of the Top Products Widget
    var top_prod_display = function(resp, c_id_prefix) {

        jQuery(function($) {

            var plot_data = false;
            var table_html = '<tr><th width=45%></th><th width=55%></th></tr> ';

            for ( var key in resp ) {

                if ( (resp[key].hasOwnProperty('sales') && resp[key].sales > 0) || 
                    (resp[key].hasOwnProperty('qty') && resp[key].qty > 0) ) {

                  name = (resp[key].title.length >= 25) ? resp[key].title.substring(0,24) + "..." : resp[key].title;

                  table_html += '<tr><td><canvas id="'+c_id_prefix+''+key+'" class="sr_cumm_top_prod_graph"></canvas></td><td title = "'+resp[key].title+'"><b style="font-weight:bold;">'+name+'</b><br>'+"<?php echo $sr_const['currency_symbol'];?>"+ sr_cumm_number_format(resp[key].sales) + ' • ' + resp[key].qty + '</td></tr> ';
                   
                  plot_data = true;
                }
            }

            if( plot_data === true ) {
                $('#top_prod_data').removeClass('no_data_text');
                $('#top_prod_data').removeAttr('style');
                $('#top_prod_data').html('<table id="top_prod_table" style="margin-top: 0.05em; width: 100%"> </table>');
                $('#top_prod_table').html(table_html);
            } else {
                $('#top_prod_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
                $('#top_prod_data').addClass('no_data_text');
                $('#top_prod_data').css('margin-top','6.7em');
            }

            // for hiding the spinner
            Dom_Id[0] = '#top_prod_data';
            ajax_spinner(Dom_Id, false);
        });
      }
    
  </script>

</div>

<!-- 
// ================================================
// Top Customer Widget
// ================================================
            AND
// ================================================
// Avg. Order Total & Avg. Order Items Widget
// ================================================
 -->
    <div id="sr_cumm_small_widget_cust" class="sr_cumm_small_widget_parent"> 
        <div id="sr_cumm_avg_order_tot" class = "sr_cumm_small_widget blur_widget">
            <div id="sr_cumm_avg_order_tot_value" class="average_order_total_amt">
                <div id="sr_cumm_avg_order_tot_content" class="sr_cumm_small_widget_content"></div>
                <p id="average_order_tot_title" class="average_order_total_text"> <?php _e('Avg Order Total', $sr_text_domain); ?> </p>
            </div>
        </div>


        <div id="sr_cumm_avg_order_count" class = "sr_cumm_small_widget blur_widget">
            <div id="sr_cumm_avg_order_items_value" class="average_order_total_amt">
                <div id="sr_cumm_avg_order_items_content" class="sr_cumm_small_widget_content"> </div>
                <p id="average_order_items_title" class="average_order_items_text"> <?php _e('Avg Items Per Customer', $sr_text_domain); ?> </p>
            </div>
        </div>

        <!-- 
        // ================================================
        // % Top Customers Widget
        // ================================================
         -->
        <div id="sr_cumm_top_cust" class="cumm_widget" style="height: 12.5em;" >    
              <!-- <div class="cumm_header_top_cust_coupons" style="width: 55%; margin-top: 0.25em" > -->
              <div class="cumm_header">
                  <i class = "fa fa-group icon_cumm_widgets"> </i>

                  <?php _e('Top Customers', $sr_text_domain); ?>
              
              </div>

              <div class="ajax_loader cumm_widget" style="display : none;"></div>
              <div id = "top_cust_data" class= "cumm_widget_table_data" >

              </div>
        </div>

        <script type="text/javascript">

        var display_orders = function (ids) {
            var post_ids = ids.split(",");
            document.cookie = "sr_woo_search_post_ids=" + post_ids;
        }


        var top_cust_display = function(resp) {

            jQuery(function($) {

              for ( var key in resp.kpi ) {

                if( key != 'top_cust' ) {
                  continue;
                } 

                var plot_data = false;

                var table_html = '<tr><th style="text-align:left;width:70%;"></th><th style="text-align:right;width:30%;"></th></tr> ';

                for ( var m in resp.kpi[key] ) {

                    if ( (resp.kpi[key][m].hasOwnProperty('sales') && resp.kpi[key][m].sales > 0) ) {

                      title = (resp.kpi[key][m].name != '') ? resp.kpi[key][m].name +'\n('+ resp.kpi[key][m].email +')' : '-\n('+ resp.kpi[key][m].email +')';
                      
                      name = (resp.kpi[key][m].name != '') ? resp.kpi[key][m].name : resp.kpi[key][m].email;
                      name = (name.length >= 35) ? name.substring(0,34) + "..." : name;

                      table_html += '<tr><td title = "'+ title +'">'+ name +'</td><td align="right"><a href="'+(resp.meta.s_link +''+ resp.kpi[key][m].s_link )+'" target="_blank" >'+ "<?php echo $sr_const['currency_symbol'];?>"+ sr_cumm_number_format(resp.kpi[key][m].sales)+'</a></td></tr>';  
                       
                      plot_data = true;

                    }
                }

                if( plot_data === true ) {
                    $('#top_cust_data').removeAttr('style');
                    $('#top_cust_data').removeClass('no_data_text');
                    $('#top_cust_data').addClass('cumm_widget_table_data');
                    $('#top_cust_data').html('<table id = "top_cust_table"  class = "cumm_widget_table_body" width = "100%">');
                    jQuery('#top_cust_table').html(table_html);
                } else {
                    $('#top_cust_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
                    $('#top_cust_data').removeClass('cumm_widget_table_data');
                    $('#top_cust_data').addClass('no_data_text');
                    $('#top_cust_data').css('margin-top','3.2em');
                }

                // for hiding the spinner
                Dom_Id[0] = '#top_cust_data';
                ajax_spinner(Dom_Id, false);

              }
            });
          }
          </script>
    </div>

    <!-- 
    // ================================================
    // Cart Abandonment Rate
    // ================================================
     -->
     <div id="sr_cumm_small_widget_coupons" class="sr_cumm_small_widget_parent">
        <div id="sr_cumm_cart_abandonment" class = "sr_cumm_small_widget blur_widget" style = "margin-right: 1.68em;">
            <div id="sr_cumm_cart_abandonment_rate" class="average_order_total_amt">
                <div id="sr_cumm_cart_abandonment_content" class="sr_cumm_small_widget_content"></div>
                <p id="sr_cumm_cart_abandonment_title" class="average_order_items_text"> <?php _e('Cart Abandonment Rate', $sr_text_domain); ?> </p>
            </div>
        </div>

    <!-- 
    // ================================================
    // % Of Orders Containing Coupons
    // ================================================
     -->

        <div id="sr_cumm_order_coupons_count" class = "sr_cumm_small_widget blur_widget" style = "margin-right: 1.64em">
            <div id="sr_cumm_order_coupons_value" class="average_order_total_amt">
                <div id="sr_cumm_order_coupons_content" class="sr_cumm_small_widget_content"></div>
                <p id="sr_cumm_order_coupons_title" class="average_order_items_text"> <?php _e('Sales with Coupons', $sr_text_domain); ?> </p>
            </div>
        </div>

        <!-- 
        // ================================================
        // Top Coupons Widget
        // ================================================
         -->
        <div id="sr_cumm_top_coupons" class="cumm_widget" style="height: 12.5em;">

            <div class="cumm_header">
              <i class = "fa fa-tags icon_cumm_widgets"> </i>     
              <?php _e('Top Coupons', $sr_text_domain); ?>
            </div>
            <div class="ajax_loader cumm_widget" style="display : none;"></div>
            <div id = "sr_cumm_top_coupons_data" class= "cumm_widget_table_data" > </div>
        </div> 

        <script type = "text/javascript">

        var sr_top_coupons_display = function(resp) {

            // onClick=display_orders('+resp['top_coupon_data'][i].order_ids+')

            jQuery(function($) {

                for ( var key in resp.kpi ) {

                if( key != 'top_coupons' ) {
                  continue;
                } 

                var plot_data = false;
                var table_html = '<tr><th style="text-align:left;width:60%;"></th><th style="text-align:right;width:20%;"></th><th style="text-align:right;width:20%;"></th></tr> ';

                for ( var m in resp.kpi[key] ) {

                    if ( (resp.kpi[key][m].hasOwnProperty('sales') && resp.kpi[key][m].sales > 0) ||
                        (resp.kpi[key][m].hasOwnProperty('count') && resp.kpi[key][m].count > 0) ) {

                      name = (resp.kpi[key][m].title.length >= 35) ?resp.kpi[key][m].title.replace(/^\s+|\s+$/g,"").substring(0,34) + "..." : resp.kpi[key][m].title.replace(/^\s+|\s+$/g,"");
                      table_html += '<tr><td title = "'+ resp.kpi[key][m].title +'">'+ name +'</td><td align="right">'+ "<?php echo $sr_const['currency_symbol'];?>"+ sr_cumm_number_format(resp.kpi[key][m].sales) +'</td><td align="right"><a href="'+(resp.meta.s_link +''+ resp.kpi[key][m].s_link )+'" target="_blank">'+resp.kpi[key][m].count+'</a></td></tr>';  

                      plot_data = true;

                    }
                }

                if( plot_data === true ) {
                    $('#sr_cumm_top_coupons_data').removeAttr('style');
                    $('#sr_cumm_top_coupons_data').removeClass('no_data_text');
                    $('#sr_cumm_top_coupons_data').addClass('cumm_widget_table_data');
                    $('#sr_cumm_top_coupons_data').html('<table id = "top_coupon_table"  class = "cumm_widget_table_body" width="100%">');
                    jQuery('#top_coupon_table').html(table_html);
                } else {
                    $('#sr_cumm_top_coupons_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
                    $('#sr_cumm_top_coupons_data').removeClass('cumm_widget_table_data');
                    $('#sr_cumm_top_coupons_data').addClass('no_data_text');
                    $('#sr_cumm_top_coupons_data').css('margin-top','3.2em');
                }

                // for hiding the spinner
                Dom_Id[0] = '#sr_cumm_top_coupons_data';
                ajax_spinner(Dom_Id, false);

              }
            });

          }

        </script>
    </div>


<!-- 
// ================================================
// Top Abandoned Products Widget
// ================================================
 -->

<div id="sr_cumm_top_abandoned_products" class="cumm_widget">    
    <div class="cumm_header" style="padding:4px 0 8px 6px;">
      <i class="fa fa-shopping-cart" style="font-size: 1.2em;"></i>
      <i class="fa fa-share" style="vertical-align: super;margin-left:-0.7em;font-size: 0.9em;"></i>
      <?php _e('Abandoned Products', $sr_text_domain); ?>

      <span id="sr_cumm_top_abandoned_products_export" title="Export" class="top_abandoned_prod_export">
        <!-- <input type="button" name="top_abandoned_prod_export" id="top_abandoned_prod_export" value="Export" onclick="top_ababdoned_products_export()"> -->
        <!-- <i id="sr_cumm_top_abandoned_products_export_icon" class = "fa fa-download-alt icon_cumm_widgets" style="color:#B1ADAD"> </i> -->
        <i id="sr_cumm_top_abandoned_products_export_icon" class = "fa fa-download icon_cumm_widgets" style="color:#B1ADAD"> </i>
      </span>
    </div>

    <!-- <div id="sr_cumm_top_abandoned_products_data" class="no_data_text" style="line-height: 0.75em; margin-top:2.17em;font-size:3.36em;"> -->
    <div class="ajax_loader cumm_widget" style="display : none;"></div>
    <div id = "sr_cumm_top_abandoned_products_data">
            
    </div>
    <script type="text/javascript">

            jQuery(function($){
                
                $("#sr_cumm_top_abandoned_products_export").on('mouseenter',function() {
                    $("#sr_cumm_top_abandoned_products_export_icon").css("color","#FFFFFF");
                });

                $("#sr_cumm_top_abandoned_products_export").on('mouseleave',function() {
                    $("#sr_cumm_top_abandoned_products_export_icon").css("color","#B1ADAD");
                });

                $("#sr_cumm_top_abandoned_products_export").on('click', function() {

                    <?php if (defined('SRPRO') && SRPRO === true) {?>
                        var iframe = document.createElement("iframe");
                        // iframe.src = '<?php echo content_url("/plugins/smart-reporter-for-wp-e-commerce/pro/sr-summary-mails.php"); ?>' + "?cmd=top_ababdoned_products_export&start_date=" + $("#startdate").val() + "&end_date=" + $("#enddate").val();
                        iframe.src = ajaxurl + '?action=top_ababdoned_products_export&params=<?php echo urlencode(json_encode($sr_const));?>&start_date=' + $("#startdate").val() + '&end_date=' + $("#enddate").val();
                        iframe.style.display = "none";
                        document.body.appendChild(iframe);
                    <?php }else {?>
                        alert("Sorry! Export CSV functionality is available only in Pro version");
                    <?php }?>      
                });
            });

    

    //Function to handle the display part of the Top Abandoned Products Widget
    var top_ababdoned_products_display = function(resp) {

        jQuery(function($) {

            var plot_data = false;
            var table_html = '<tr><th width=40% class="top_gateways_shipping_header"></th><th width=60% class="top_gateways_shipping_header"></th></tr> ';

            for ( var key in resp ) {

                if ( (resp[key].hasOwnProperty('sales') && resp[key].sales > 0) || 
                    (resp[key].hasOwnProperty('aqty') && resp[key].aqty > 0) ) {

                  name = (resp[key].title.length >= 25) ? resp[key].title.substring(0,24) + "..." : resp[key].title;

                  table_html += '<tr><td><canvas id="tapq_'+key+'" class="sr_cumm_top_prod_graph"></canvas></td><td title = "'+resp[key].title+'"><b style="font-weight:bold;">'+name+'</b><br>'+
                                "<?php echo $sr_const['currency_symbol'];?>"+ sr_cumm_number_format(resp[key].sales) + ' • '
                                          + sr_cumm_number_format(resp[key].arate) + '%  • '
                                          + resp[key].aqty +'</td></tr> ';

                  plot_data = true;
                }
            }

            if( plot_data === true ) {
                $('#sr_cumm_top_abandoned_products_data').removeClass('no_data_text');
                $('#sr_cumm_top_abandoned_products_data').removeAttr('style');
                $('#sr_cumm_top_abandoned_products_data').html('<table id="top_abandoned_prod_table" style="margin-top: 0.05em; width: 100%"> </table>');
                $('#top_abandoned_prod_table').html(table_html);
            } else {
                $('#sr_cumm_top_abandoned_products_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
                $('#sr_cumm_top_abandoned_products_data').addClass('no_data_text');
                $('#sr_cumm_top_abandoned_products_data').css('margin-top','6.7em');
            }

            // for hiding the spinner
            Dom_Id[0] = '#sr_cumm_top_abandoned_products_data';
            ajax_spinner(Dom_Id, false);
        });
        
      }
    
  </script>
</div>

  <!-- 
// ================================================
// Total Discount Widget
// ================================================
 -->
<div id="sr_cumm_total_discount" class="cumm_widget">

    <div id="sr_cumm_total_discount_value" style="height:60px;width:100%;">
          <div class="cumm_header">
              <i class="fa fa-location-arrow icon_cumm_widgets" ></i>
              <!-- <i class="fa fa-rocket icon_cumm_widgets" ></i> -->
              <?php _e('Discounts', $sr_text_domain); ?>
          </div>
          <div id="sr_cumm_total_discount_total" class="cumm_total">
              <span id ="sr_cumm_total_discount_actual"> </span>  <i id="sr_cumm_total_discount_indicator" ></i> <span id ="diff_cumm_total_discount" style="font-size : 0.5em;"></span>
          </div>    
    </div>
    
    <div class="ajax_loader cumm_widget" style="display : none;"></div>
    <!-- <div id="sr_discount_graph" class="sr_cumm_sales_graph ">  </div>  -->
    <canvas id="sr_discount_graph" class="sr_cumm_sales_graph ">  </canvas> 

<script type="text/javascript">

    var sr_cumm_total_discount_display = function(resp) {

        jQuery(function($) {

            var discount_trend = new Array();
            
            var tick_format = resp['tick_format'];
            var currency_symbol = resp['currency_symbol'];

            jQuery('#sr_discount_graph').empty();
            $('#sr_discount_graph').removeAttr('style'); // remove styling after no data label

            if(resp['graph_cumm_discount_sales'].length > 0) {

                if ( (!$(document.body).hasClass('folded')) && jqplot_flag == 1) {
                    $('#sr_sales_graph, #sr_discount_graph').removeAttr('style');
                    $('#sr_sales_graph, #sr_discount_graph').css("margin-top","-2.75em");
                }
                else if (($(document.body).hasClass('folded')) && jqplot_flag == 1) {
                    $('#sr_sales_graph, #sr_discount_graph').removeAttr('style');
                }
                else if(jqplot_flag != 2) {
                    jqplot_flag = 1;
                }

                $('#sr_discount_graph').removeClass().addClass('sr_cumm_sales_graph_not_collapsed sr_cumm_sales_graph');

                for(var i = 0, len = resp['graph_cumm_discount_sales'].length; i < len; i++) {
                    discount_trend[i] = new Array();
                    discount_trend[i][0] = resp['graph_cumm_discount_sales'][i].post_date;
                    discount_trend[i][1] = resp['graph_cumm_discount_sales'][i].sales;
                }

                jQuery.jqplot('sr_discount_graph',  [discount_trend], {
                axes: {
                     yaxis: {  
                          tickOptions: {
                          formatter: tickFormatter,
                        },
                         showTicks: false,
                         min:-resp['cumm_max_discount_total']/4,
                         max: resp['cumm_max_discount_total'] + resp['cumm_max_discount_total']/4
                     } ,
                    xaxis: {                    
                        renderer:$.jqplot.DateAxisRenderer, 
                        tickOptions:{formatString:tick_format},
                        showTicks: false,
                        min: resp['cumm_sales_min_date'],
                        max: resp['cumm_sales_max_date']
                    }
                },
                axesDefaults: {
                    rendererOptions: {
                        baselineWidth: 1.5,
                        drawBaseline: false // property to hide the axes from the graph
                    }
                      
                },
                // actual grid outside the graph
                grid: {
                    drawGridlines: false,
                    backgroundColor: 'transparent',
                    borderWidth: 0,
                    shadow: false

                },
                
                highlighter: {
                    show: true,
                    sizeAdjust: 0.8,
                    tooltipLocation: 'ne'
                },
                cursor: {
                  show: false
                },
                series: [
                        { markerOptions: { style:"filledCircle" } },

                ],
                animate: true,
                animateReplot : true,
                seriesDefaults: {
                    showTooltip:true,
                    rendererOptions: {smooth: true},
                    lineWidth: 2,
                    color : '#368ee0',
                    fillToZero: true,
                    useNegativeColors: false,
                    fillAndStroke: true,
                    fillColor: '#85D1F9',
                    showMarker:true,
                    showLine: true // shows the graph trend line
                }
            }
            );
            }

            else {
                $('#sr_discount_graph').removeClass();            
                $('#sr_discount_graph').text("<?php _e('No Data',  $sr_text_domain); ?>");
                $('#sr_discount_graph').addClass('no_data_text');
                $('#sr_discount_graph').css('margin-top','5.4em');
            }
        });
    }

</script>
 
</div>

<!-- 
// ================================================
// Taxes Widget
// ================================================
 -->

<div id="chartpseudotooltip"></div>

<div id="sr_cumm_taxes" class="cumm_widget">    
    <div class="cumm_header">
      <i class="fa fa-bolt icon_cumm_widgets" ></i>
      <?php _e('Taxes & Shipping', $sr_text_domain); ?>
    </div>

    <!-- style="line-height: 0.75em; margin-top:2.17em;font-size:3.36em;" -->

    <!-- style="height:92%;width:100%" -->
    <!-- class="sr_cumm_sales_graph  -->


    <div class="ajax_loader cumm_widget" style="display : none;"></div>
    <!-- <div id="sr_cumm_taxes_data" style="height:87%;width:100%"> </div>-->
    <canvas id="sr_cumm_taxes_data" style="height:25.5%;width:40%;margin-top:1.5em"> </canvas>
    
    <script type="text/javascript">

    //FUnction to round off the numbers
    function precise_round(num,decimals){
        return Math.round(num*Math.pow(10,decimals))/Math.pow(10,decimals);
    }

    // $(window).resize(function() {
    //     monthly_sales_graph_resize();
    // });

    // var monthly_sales_graph_resize = function() {
    //     if (plot) {
    //         plot.destroy();
    //         plot.replot();
    //     }                            
    // }

    //Function to handle the display part of the Top Gateway Widget
    var cumm_taxes_display = function(resp) {

        jQuery(function($) {

            var taxes_data = new Array();

            $('#sr_cumm_taxes_data, #sr_cumm_taxes_legend').empty();

            if( resp.kpi.sales > 0 ) {
                
                if ($('#sr_cumm_taxes_data').hasClass('no_data_text')) {
                    $('#sr_cumm_taxes_data').remove();
                    $('#sr_cumm_taxes').append('<canvas id="sr_cumm_taxes_data" style="height:25.5%;width:40%;margin-top:1.5em;"> </canvas>');
                }

              var data = [
                            {
                                value: resp.kpi.tax,
                                color:"#539EBD",
                                highlight: "#7CBBD6",
                                label: "Tax"
                            },
                            {
                                value: resp.kpi.shipping,
                                color: "#F8CC69",
                                highlight: "#FFDD91",
                                label: "Shipping"
                            },
                            {
                                value: resp.kpi.shipping_tax,
                                color:"#72479E",
                                highlight: "#926CB9",
                                label: "Shipping Tax"
                            },
                            {
                                value: (resp.kpi.sales-(resp.kpi.tax+resp.kpi.shipping_tax+resp.kpi.shipping)),
                                color: "#DB485F",
                                highlight: "#F27085",
                                label: "Net Sales"
                            }
                        ];  
              
              var options = {
                                segmentShowStroke : false,
                                percentageInnerCutout : 65,
                                animateRotate : true,
                                animationEasing : "easeOutBounce",
                                responsive: true,
                                legendTemplate : "<table id=\"sr_cumm_taxes_legend\" style=\"border:1px solid #E5E5E5;margin-top:0.7em;margin-left:2.5em;\"><tbody> <tr><% for (var i=0; i<segments.length; i++){%><% if(i == 0) {%><td style=\"padding: 0px !important;padding-left: 4px !important;\"><% }else {%><td style=\"padding: 0px !important;\"><% } %><div style=\"border-style: solid;border-radius: 50px;border-width: 6px;border-color:<%=segments[i].fillColor%>\"></div></td><td style=\"font-size:0.75em;\"><%if(segments[i].label){%><%=segments[i].label%><%}%></td><%}%></tr></tbody></table>",
                                customTooltips: function(tooltip) {

                                    var tooltipEl = $('#chartjs-tooltip');

                                    // tooltip will be false if tooltip is not visible or should be hidden
                                    if (!tooltip) {
                                        tooltipEl.hide();
                                        return;
                                    }

                                    // Set caret Position
                                    tooltipEl.removeClass('above below');
                                    tooltipEl.addClass(tooltip.yAlign);

                                    v = tooltip.text.split(':');
                                    tooltipEl.html(v[0]+': '+"<?php echo $sr_const['currency_symbol'];?>"+v[1].substring(1)+' ('+sr_cumm_number_format( (v[1]/resp.kpi.sales)*100 )+'%)');


                                    // Find Y Location on page
                                    var top;
                                    if (tooltip.yAlign == 'above') {
                                        top = tooltip.y - tooltip.caretHeight - tooltip.caretPadding;
                                    } else {
                                        top = tooltip.y + tooltip.caretHeight + tooltip.caretPadding;
                                    }

                                    // Display, position, and set styles for font
                                    tooltipEl.css({
                                        left: tooltip.chart.canvas.offsetLeft + tooltip.x + 'px',
                                        top: tooltip.chart.canvas.offsetTop + top + 'px',
                                        fontFamily: tooltip.fontFamily,
                                        fontSize: tooltip.fontSize,
                                        fontStyle: tooltip.fontStyle,
                                    });

                                    tooltipEl.show();
                                }
                            };

              var taxeschart = new Chart($("#sr_cumm_taxes_data").get(0).getContext("2d")).Doughnut(data,options);

              $('#sr_cumm_taxes').append(taxeschart.generateLegend());

            } else {
                $('#sr_cumm_taxes_data').remove();
                $('#sr_cumm_taxes').append('<div id="sr_cumm_taxes_data" class="no_data_text" style="margin-top:6.7em;">No Data</div>');
            }
        
      });

    }
    </script>
</div>

<!-- 
// ================================================
// Top Payment gateway Widget
// ================================================
 -->

<div id="sr_cumm_order_by_gateways" class="cumm_widget">    
    <div class="cumm_header">
      <i class="fa fa-credit-card icon_cumm_widgets" ></i>
      <?php _e('Payment Gateways', $sr_text_domain); ?>
    </div>

    <div class="ajax_loader cumm_widget" style="display : none;"></div>
    <div id = "sr_cumm_order_by_pm_data">
            
    </div>

    <script type="text/javascript">

    //Function to handle the display part of the Top Gateway & Top Shipping Widget
    var top_payment_shipping_display = function(resp) {

        jQuery(function($) {

            Dom_Id = new Array();

            for ( var key in resp.kpi ) {

              if( key != 'sm' && key != 'pm' ) {
                continue;
              }

              var plot_data = false;

              var table_html = '<tr><th width=25% class="top_gateways_shipping_header">Sales</th><th width=25% class="top_gateways_shipping_header">Qty</th><th width=50% class="top_gateways_shipping_header"></th></tr> ';

              for ( var m in resp.kpi[key] ) {

                  if ( (resp.kpi[key][m].hasOwnProperty('sales') && resp.kpi[key][m].sales > 0)
                        || (resp.kpi[key][m].hasOwnProperty('orders') && resp.kpi[key][m].orders > 0) ) {

                    // var name = resp.kpi[key][m].title;
                    title = (resp.kpi[key][m].title.length >= 25) ? resp.kpi[key][m].title.substring(0,24) + "..." : resp.kpi[key][m].title;

                    var orders = "<?php echo $sr_const['currency_symbol'];?>"+ sr_cumm_number_format(resp.kpi[key][m].sales) + ' • '
                                            + ( (resp.kpi.hasOwnProperty('sales') && resp.kpi.sales > 0) ? ( sr_cumm_number_format( (resp.kpi[key][m].sales/resp.kpi.sales)*100 ) + '%') : 'NA') + ' • '

                    if ( resp.kpi[key][m].hasOwnProperty('s_link') ) {
                        orders += '<a href="'+ (resp.meta.s_link +''+ resp.kpi[key][m].s_link ) +'" target="_blank">' + resp.kpi[key][m].orders + '</a>';
                    } else {
                        orders += resp.kpi[key][m].orders;
                    } 

                    table_html += '<tr><td><canvas id="'+ (key+'_'+m+'_sales') +'" class="sr_cumm_top_prod_graph"></canvas></td><td><canvas id="'+(key+'_'+m+'_orders')+'" class="sr_cumm_top_prod_graph"></canvas></td><td title = "'+resp.kpi[key][m].title+'"><b style="font-weight:bold;">'+title+'</b><br>'+orders+'</td></tr> ';
                     
                    plot_data = true;

                  }
              }

              if( plot_data === true ) {
                  $('#sr_cumm_order_by_'+key+'_data').removeClass('no_data_text');
                  $('#sr_cumm_order_by_'+key+'_data').removeAttr('style');
                  $('#sr_cumm_order_by_'+key+'_data').html('<table id="top_'+key+'_table" style="margin-top: 0.05em; width: 100%"> </table>');
                  $('#top_'+key+'_table').html(table_html);
              } else {
                  $('#sr_cumm_order_by_'+key+'_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
                  $('#sr_cumm_order_by_'+key+'_data').addClass('no_data_text');
                  $('#sr_cumm_order_by_'+key+'_data').css('margin-top','6.7em');
              }

            }

            // for hiding the spinner
            Dom_Id[0] = '#sr_cumm_order_by_pm_data';
            Dom_Id[1] = '#sr_cumm_order_by_sm_data';
            ajax_spinner(Dom_Id, false);

            
            // var tick_format = resp['tick_format'];
            // var currency_symbol = resp['currency_symbol'];

            // var tick_format_yaxis_sales_amt_graph ="<?php echo $sr_const['currency_symbol'];?>%s";
            // var tick_format_yaxis_sales_count_graph = 'No. of Orders: %s';

            // var top_gateway_graph_sales_amt_data = new Array();
            // var top_gateway_graph_sales_count_data = new Array();

            // var top_gateway_sales_amt_data  = new Array();
            // var top_gateway_sales_count_data  = new Array();

            // for (var i = 0; i < resp['top_gateway_data'].length; i++) {
            //   var span_id_sales_amt = "span_top_gateway_sales_amt_" + i;
            //   var span_id_sales_count = "span_top_gateway_sales_count_" + i;
            //   var gateway_name = resp['top_gateway_data'][i].payment_method;

            //   var link_id = "link_" + i;
            //   var site_url = resp['siteurl'] + "/wp-admin/edit.php?s="+resp['top_gateway_data'][i].payment_method+"<?php echo $sr_woo_order_search_url?>";

            //   var gateway_name_trimmed = "";
            //   var gateway_sales_display = resp['top_gateway_data'][i].gateway_sales_display + ' • '
            //                               + resp['top_gateway_data'][i].gateway_sales_percent + ' • '
            //                               + '<a id="'+link_id+'" href="'+site_url+'" target="_blank" onClick=display_orders("'+resp['top_gateway_data'][i].order_ids+'")>' + resp['top_gateway_data'][i].sales_count + '</a>';
                                          

            //   if (gateway_name.length >= 25) {
            //       gateway_name_trimmed = gateway_name.substring(0,24) + "...";
            //   }
            //   else {
            //       gateway_name_trimmed = gateway_name;
            //   }

            //   table_html += '<tr><td><div id="'+span_id_sales_amt+'" class="sr_cumm_top_prod_graph"></div></td><td><div id="'+span_id_sales_count+'" class="sr_cumm_top_prod_graph"></div></td><td title = "'+gateway_name+'"><b style="font-weight:bold;">'+gateway_name_trimmed+'</b><br>'+gateway_sales_display+'</td></tr> ';

            //   var sales_amt_graph_data = new Array();
            //   var sales_count_graph_data = new Array();

            //   var sales_amt_len = 0;
            //   var sales_count_len = 0;

            //     if ( resp['top_gateway_data'][i].hasOwnProperty('graph_data_sales_amt') ) {
            //         sales_amt_len = resp['top_gateway_data'][i].graph_data_sales_amt.length;
            //     }

            //     if ( resp['top_gateway_data'][i].hasOwnProperty('graph_data_sales_count') ) {
            //         sales_count_len = resp['top_gateway_data'][i].graph_data_sales_count.length;
            //     }

            //   //Array for gateway sales amt.

            //   for(var j = 0; j < sales_amt_len; j++){
            //       sales_amt_graph_data[j] = new Array();
            //       sales_amt_graph_data[j][0] = resp['top_gateway_data'][i].graph_data_sales_amt[j].post_date;
            //       sales_amt_graph_data[j][1] = resp['top_gateway_data'][i].graph_data_sales_amt[j].sales;
            //   }
              
            //   //Array for gateway sales count
            //   for(var j = 0; j < sales_count_len; j++){
            //       sales_count_graph_data[j] = new Array();
            //       sales_count_graph_data[j][0] = resp['top_gateway_data'][i].graph_data_sales_count[j].post_date;
            //       sales_count_graph_data[j][1] = resp['top_gateway_data'][i].graph_data_sales_count[j].sales;
            //   }

            //   top_gateway_graph_sales_amt_data[i] = sales_amt_graph_data;
            //   top_gateway_graph_sales_count_data[i] = sales_count_graph_data;

            //   top_gateway_sales_amt_data[i] = resp['top_gateway_data'][i].max_value_sales_amt;
            //   top_gateway_sales_count_data[i] = resp['top_gateway_data'][i].max_value_sales_count;

            // };


            // if(top_gateway_graph_sales_amt_data.length > 0 && top_gateway_graph_sales_count_data.length > 0) {
            //     $('#sr_cumm_order_by_pm_data').removeClass('no_data_text');
            //     $('#sr_cumm_order_by_pm_data').removeAttr('style');
            //     $('#sr_cumm_order_by_pm_data').html('<table id="top_gateway_table" style="margin-top: 0.05em; width: 100%"> </table>');
            //     $('#top_gateway_table').html(table_html);
            //     top_prod_graph_display(top_gateway_graph_sales_amt_data,tick_format,tickFormatter,top_gateway_sales_amt_data,resp['cumm_sales_min_date'],resp['cumm_sales_max_date'],'span_top_gateway_sales_amt_');    
            //     top_prod_graph_display(top_gateway_graph_sales_count_data,tick_format,tickFormatter_top_gateway_shipping_sales_count,top_gateway_sales_count_data,resp['cumm_sales_min_date'],resp['cumm_sales_max_date'],'span_top_gateway_sales_count_');    
            // }
            // else {
            //     $('#sr_cumm_order_by_pm_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
            //     $('#sr_cumm_order_by_pm_data').addClass('no_data_text');
            //     $('#sr_cumm_order_by_pm_data').css('margin-top','6.7em');
            // }
        });
      }
    
  </script>
</div>

<!-- 
// ================================================
// Sales By Countries Widget
// ================================================
 -->


<div id="sr_cumm_sales_countries" class="cumm_widget sr_cumm_sales_countries">

    <div id="sr_cumm_sales_countries_value" style="height:12%;width:100%;">
          <div class="cumm_header">
              <i class="fa fa-globe icon_cumm_widgets" ></i>
              <!-- <i class="fa fa-rocket icon_cumm_widgets" ></i> -->
              <?php _e('Billing Countries', $sr_text_domain); ?>
          </div>
    </div>
    
    <div class="ajax_loader cumm_widget sr_cumm_sales_countries" style="display : none;"></div>
    <div id="sr_cumm_sales_countries_graph" style="height:85%;width:100%;margin-top:0.5em">  </div>
    <!-- <div id="sr_cumm_sales_countries_graph" style="height:19.3em;width:50em;margin-top:0.5em">  </div> -->
<script type="text/javascript">

//Function to handle the display part of the Top Abandoned Products Widget
    var cumm_sales_billing_country = function(resp) {
        
        jQuery(function($){

            // for hiding the spinner
            Dom_Id[0] = '#sr_cumm_sales_countries_graph';
            ajax_spinner(Dom_Id, false);

            $('#sr_cumm_sales_countries_graph').empty();

            if( typeof (resp) != 'undefined' && resp.hasOwnProperty('sales') && Object.keys(resp.sales).length > 0) {
                $('#sr_cumm_sales_countries_graph').removeClass('no_data_text');
                $('#sr_cumm_sales_countries_graph').css('margin-top','0.5em');
                $("#sr_cumm_sales_countries_graph").width(($("#sr_cumm_sales_countries").width())+"px");
                $("#sr_cumm_sales_countries_graph").height(($("#sr_cumm_sales_countries").height()-75)+"px");

                // $('#sr_cumm_sales_countries_graph').css({"margin-top" :"0.5em","height" :"85%","width" :"100%"});

                $('#sr_cumm_sales_countries_graph').vectorMap({
                    map: 'world_mill_en',
                    backgroundColor: 'transparent',
                    // map: 'world_en',
                    // borderColor: '#000000',
                    regionStyle: {
                        initial: {
                            fill: '#dbdee1',
                            "fill-opacity": 1,
                            stroke: '#a2aaad',
                            // "stroke-width": 2,
                            "stroke-opacity": 1
                        }
                    },
                    series: {
                        regions: [{
                            values: resp.sales,
                            scale: ['#C8EEFF', '#006491'], // two colors: for minimum and maximum values
                            attribute: 'fill' 
                        }]
                    },
                    
                    normalizeFunction: 'polynomial',
                    hoverOpacity: 0.7,
                    hoverColor: false,
                    onRegionClick: function(event, code){
                        window.open( resp.s_link + "&s="+code+"&s_col=billing_country&s_val="+code ,'_newtab');
                    },
                    onRegionLabelShow: function(event, label, code){
                        label.html(
                            '<b>'+label.html()+'</b></br>'+'<b>Sales: </b>'+( "<?php echo $sr_const['currency_symbol'];?>" + (resp.sales.hasOwnProperty(code)) ? sr_cumm_number_format(resp.sales[code]) : 0)+'</br><b>Orders Count: </b>'+ ((resp.orders.hasOwnProperty(code)) ? resp.orders[code] : 0)
                        )
                    },
                    onRegionOver: function(event, code){
                        if (resp.sales.hasOwnProperty(code)) {
                            document.body.style.cursor = 'pointer';
                        }
                    },
                    onRegionOut: function(event, code) {
                        // return to normal cursor
                        document.body.style.cursor = 'default';
                    }
                });
                
            } else {
                $('#sr_cumm_sales_countries_graph').text("<?php _e('No Data',  $sr_text_domain); ?>");
                $('#sr_cumm_sales_countries_graph').addClass('no_data_text');
                $('#sr_cumm_sales_countries_graph').css('margin-top','6.7em');
            }

            

        });
    }

  </script>    

</div>

<!-- 
// ================================================
// Top Shipping Method Widget
// ================================================
 -->

<div id="sr_cumm_order_by_shipping_method" class="cumm_widget">    
    <div class="cumm_header">
      <i class="fa fa-truck icon_cumm_widgets" ></i>
      <?php _e('Shipping Methods', $sr_text_domain); ?>
    </div>

    <div class="ajax_loader cumm_widget" style="display : none;"></div>
    <div id = "sr_cumm_order_by_sm_data">
            
    </div>

    <script type="text/javascript">

    //Function to handle the display part of the Top Shipping Method Widget
    var top_shipping_method_display = function(resp) {

        jQuery(function($) {

            var table_html = '<tr><th width=25% class="top_gateways_shipping_header">Sales</th><th width=25% class="top_gateways_shipping_header">Qty</th><th width=50% class="top_gateways_shipping_header"></th></tr> ';

            var tick_format = resp['tick_format'];
            var currency_symbol = resp['currency_symbol'];

            var tick_format_yaxis_sales_amt_graph ="<?php echo $sr_const['currency_symbol'];?>%s";
            var tick_format_yaxis_sales_count_graph = 'No. of Orders: %s';

            var top_shipping_method_graph_sales_amt_data = new Array();
            var top_shipping_method_graph_sales_count_data = new Array();

            var top_shipping_method_sales_amt_data  = new Array();
            var top_shipping_method_sales_count_data  = new Array();

            for (var i = 0; i < resp['top_shipping_method_data'].length; i++) {
              var span_id_sales_amt = "span_top_shipping_method_sales_amt_" + i;
              var span_id_sales_count = "span_top_shipping_method_sales_count_" + i;
              var shipping_method_name = resp['top_shipping_method_data'][i].shipping_method;

              var link_id = "link_" + i;
              var site_url = resp['siteurl'] + "/wp-admin/edit.php?s="+resp['top_shipping_method_data'][i].shipping_method+"<?php echo $sr_woo_order_search_url?>";

              var shipping_method_name_trimmed = "";
              var shipping_method_sales_display = resp['top_shipping_method_data'][i].shipping_method_sales_display + ' • '
                                          + resp['top_shipping_method_data'][i].shipping_method_sales_percent + ' • '
                                          + '<a id="'+link_id+'" href="'+site_url+'" target="_blank" onClick=display_orders("'+resp['top_shipping_method_data'][i].order_ids+'")>' + resp['top_shipping_method_data'][i].shipping_count + '</a>';
                                          
            if(shipping_method_name !== null){

                  if (shipping_method_name.length >= 25) {
                      shipping_method_name_trimmed = shipping_method_name.substring(0,24) + "...";
                  }
                  else {
                      shipping_method_name_trimmed = shipping_method_name;
                  }

            }else{
                shipping_method_name_trimmed = "";
            }

              table_html += '<tr><td><div id="'+span_id_sales_amt+'" class="sr_cumm_top_prod_graph"></div></td><td><div id="'+span_id_sales_count+'" class="sr_cumm_top_prod_graph"></div></td><td title = "'+shipping_method_name+'"><b style="font-weight:bold;">'+shipping_method_name_trimmed+'</b><br>'+shipping_method_sales_display+'</td></tr> ';

              var sales_amt_graph_data = new Array();
              var sales_count_graph_data = new Array();

              var sales_amt_len = 0;
              var sales_count_len = 0;

                if ( resp['top_shipping_method_data'][i].hasOwnProperty('graph_data_sales_amt') ) {
                    sales_amt_len = resp['top_shipping_method_data'][i].graph_data_sales_amt.length;
                }

                if ( resp['top_shipping_method_data'][i].hasOwnProperty('graph_data_sales_count') ) {
                    sales_count_len = resp['top_shipping_method_data'][i].graph_data_sales_count.length;
                }

              //Array for gateway sales amt.

              for(var j = 0; j < sales_amt_len; j++){
                  sales_amt_graph_data[j] = new Array();
                  sales_amt_graph_data[j][0] = resp['top_shipping_method_data'][i].graph_data_sales_amt[j].post_date;
                  sales_amt_graph_data[j][1] = resp['top_shipping_method_data'][i].graph_data_sales_amt[j].sales;
              }
              
              //Array for gateway sales count
              for(var j = 0; j < sales_count_len; j++){
                  sales_count_graph_data[j] = new Array();
                  sales_count_graph_data[j][0] = resp['top_shipping_method_data'][i].graph_data_sales_count[j].post_date;
                  sales_count_graph_data[j][1] = resp['top_shipping_method_data'][i].graph_data_sales_count[j].sales;
              }

              top_shipping_method_graph_sales_amt_data[i] = sales_amt_graph_data;
              top_shipping_method_graph_sales_count_data[i] = sales_count_graph_data;

              top_shipping_method_sales_amt_data[i] = resp['top_shipping_method_data'][i].max_value_sales_amt;
              top_shipping_method_sales_count_data[i] = resp['top_shipping_method_data'][i].max_value_sales_count;

            };


            if(top_shipping_method_graph_sales_amt_data.length > 0 && top_shipping_method_graph_sales_count_data.length > 0) {
                $('#sr_cumm_order_by_sm_data').removeClass('no_data_text');
                $('#sr_cumm_order_by_sm_data').removeAttr('style');
                $('#sr_cumm_order_by_sm_data').html('<table id="top_shipping_method_table" style="margin-top: 0.05em; width: 100%"> </table>');
                $('#top_shipping_method_table').html(table_html);
                top_prod_graph_display(top_shipping_method_graph_sales_amt_data,tick_format,tickFormatter,top_shipping_method_sales_amt_data,resp['cumm_sales_min_date'],resp['cumm_sales_max_date'],'span_top_shipping_method_sales_amt_');    
                top_prod_graph_display(top_shipping_method_graph_sales_count_data,tick_format,tickFormatter_top_gateway_shipping_sales_count,top_shipping_method_sales_count_data,resp['cumm_sales_min_date'],resp['cumm_sales_max_date'],'span_top_shipping_method_sales_count_');
            }
            else {
                $('#sr_cumm_order_by_sm_data').text("<?php _e('No Data',  $sr_text_domain); ?>");
                $('#sr_cumm_order_by_sm_data').addClass('no_data_text');
                $('#sr_cumm_order_by_sm_data').css('margin-top','6.7em');
            }
        });
      }
    
  </script>

</div>
 

<!-- Code for rearranging all the Div Elements -->
<script type="text/javascript">

    jQuery(function($){
        
        $(".cumm_widget, .sr_cumm_small_widget").hover(
            function() { $(this).css('border', '0.2em solid #85898e');},
            function() { $(this).css('border', '0.2em solid #e8e8e8'); }
        );

    $("#sr_cumm_sales").insertAfter("#sr_cumm_date");

    $("#sr_cumm_sales_funnel").insertAfter("#sr_cumm_sales");

    $("#sr_cumm_top_prod").insertAfter("#sr_cumm_sales_funnel");

    $("#sr_cumm_small_widget_cust").insertAfter("#sr_cumm_top_prod");

    $("#sr_cumm_small_widget_coupons").insertAfter("#sr_cumm_small_widget_cust");

    $("#sr_cumm_top_abandoned_products").insertAfter("#sr_cumm_small_widget_coupons");

    $("#sr_cumm_total_discount").insertAfter("#sr_cumm_top_abandoned_products");  

    $("#sr_cumm_taxes").insertAfter("#sr_cumm_total_discount");

    $("#sr_cumm_order_by_gateways").insertAfter("#sr_cumm_taxes");   

    $("#sr_cumm_sales_countries").insertAfter("#sr_cumm_order_by_gateways");

    $("#sr_cumm_order_by_shipping_method").insertAfter("#sr_cumm_sales_countries");

    $("#sr_putler_promotion").insertAfter("#sr_cumm_order_by_shipping_method");
    
    $("#sr_footer").insertAfter("#sr_putler_promotion");
        

    });
</script>   
  
<?php
smart_reporter_footer();
}
else if ( !empty($_GET['page']) && ($_GET['page'] == 'wc-reports' || $_GET['page'] == 'smart-reporter-wpsc') ) {

    // to set javascript variable of file exists
    // $fileExists = (SRPRO === true) ? 1 : 0;
    // $selectedDateValue = (SRPRO === true) ? 'THIS_MONTH' : 'LAST_SEVEN_DAYS';

    if ( defined('SR_WPSC_RUNNING') && SR_WPSC_RUNNING === true ) {
        $currency_type = get_option( 'currency_type' );   //Maybe
        $wpsc_currency_data = $wpdb->get_row( "SELECT `symbol`, `symbol_html`, `code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id` = '" . $currency_type . "' LIMIT 1", ARRAY_A );
        $currency_sign = $wpsc_currency_data['symbol'];   //Currency Symbol in Html
        if ( defined('SR_IS_WPSC388') && SR_IS_WPSC388 === true )   
            $orders_details_url = ADMIN_URL . "index.php?page=wpsc-purchase-logs&c=item_details&id=";
        else
            $orders_details_url = ADMIN_URL . "index.php?page=wpsc-sales-logs&purchaselog_id=";
    } else {
        $currency_sign = get_woocommerce_currency_symbol();
    }

    $file_url = '';

    if ($fileExists){

        if ( defined('SR_WPSC_RUNNING') && SR_WPSC_RUNNING === true ) {
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

    if( ( isset($_GET['post_type']) && $_GET['post_type'] == 'wpsc-product') || ( isset($_GET['page']) && $_GET['page'] == 'smart-reporter-wpsc') 
        || ((isset($_GET['view']) && $_GET['view'] == "smart_reporter_old") || ( !empty($sr_const['is_woo22']) && $sr_const['is_woo22'] == "false" ) ) ) {

        echo "<script type='text/javascript'>
        var adminUrl             = '" .ADMIN_URL. "';
        var SR                       =  new Object;";

            if ( SR_WPSC_RUNNING === true ) {
                echo "SR.defaultCurrencySymbol = '" .$currency_sign. "';";
            } else {
                echo "SR.defaultCurrencySymbol = '" . get_woocommerce_currency_symbol() . "';";
            }   
        echo "
        var jsonFileNm           = '" .SR_JSON_FILE_NM. "';
        var srNonce           = '" .$sr_const['security']. "';
        var imgURL               = '" .SR_IMG_URL . "';
        var fileExists           = '" .$fileExists. "';
        var ordersDetailsLink   = '" . $orders_details_url . "';
        var availableDays        = '" .SR_AVAIL_DAYS. "';
        var selectedDateValue    = '" .$selectedDateValue. "';
        var fileUrl      = '" .$file_url. "';
        </script>";
        ?>
        <br>
        <div id="smart-reporter"></div>

    <?php

    }  
    smart_reporter_footer();      
}

function smart_reporter_footer() {

    global $sr_text_domain;

    if ( ! get_option( 'sr_admin_footer' ) ) {
      ?>
      <div id="sr_putler_promotion" class="sr_promotion_footer">
          <?php echo __(" For more Extensive Reporting use ", $sr_text_domain); ?> <a href="http://www.putler.com/?&utm_source=SR_IN_WP" target="_blank"> <?php echo __('Putler', $sr_text_domain); ?></a> 
      </div>

      <br/>
      <br/>

      <div id="sr_footer" style="float:left;">
        <div id="add_social_links" class="wrap sr_social_links">
            <?php echo sr_add_social_links();?>
        </div>


        <div id="sr_wp_rating" class="wrap" style="color:#9e9b9b;font-size:0.95em;">
          <?php
            echo sprintf( __( 'If you like <strong>Smart Reporter</strong> please leave us a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. A huge thank you from StoreApps in advance!', $sr_text_domain ), '<a href="https://wordpress.org/support/view/plugin-reviews/smart-reporter-for-wp-e-commerce?filter=5#postform" target="_blank" data-rated="' . esc_attr__( 'Thanks :)', $sr_text_domain ) . '">', '</a>' );
          ?>
        </div>
      </div>
    <?php
    }
}