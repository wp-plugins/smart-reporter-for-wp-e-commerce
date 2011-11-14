<?php 
// to set javascript variable of file exists
$fileExists = (SRPRO === true) ? 1 : 0;
$selectedDateValue = (SRPRO === true) ? 'THIS_MONTH' : 'LAST_SEVEN_DAYS';

if ($fileExists){
	if(file_exists ( SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php' )){
		include_once( SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php' );
	}
}

echo "<script type='text/javascript'>
SR 			   		  =  new Object;
var jsonURL    		  = '" .SR_JSON_URL. "';
var imgURL     		  = '" .SR_IMG_URL . "';
var fileExists 		  = '" .$fileExists. "';
var availableDays     = '" .SR_AVAIL_DAYS. "';
var selectedDateValue = '" .$selectedDateValue. "';
</script>";
?>
<br>
<div id="smart-reporter"></div>