<?php

	header('Cache-Control: maxage=120'); 
	header('Expires: '.date(DATE_COOKIE,time()+120)); // Cache for 2 mins 
	header('Pragma: public'); 
	header('Content-type: application/force-download');  
	header('Content-Transfer-Encoding: Binary');  
	header('Content-Type: application/octet-stream');  
	header('Content-Disposition: attachment; filename="openvrp.geojson"'); 
	echo $_GET['geojson'];

?>

