<?php 
// to set javascript variable of file exists
$fileExists = (SRPRO === true) ? 1 : 0;
$selectDateValue = (SRPRO === true) ? 'THIS_MONTH' : 'LAST_SEVEN_DAYS';

if ($fileExists){
	if(file_exists ( SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php' )){
		include_once( SR_PLUGIN_DIR_ABSPATH. '/pro/sr.php' );
	}
}

echo "<script type='text/javascript'>
var jsonURL    = '" .SR_JSON_URL. "';
var imgURL     = '" .SR_IMG_URL . "';
var fileExists = '" .$fileExists. "';
var selectDateValue = '" .$selectDateValue. "';
</script>";
?>
<br>
<div id="smart-reporter"></div>