<?php
/**
 * PHP Proxy example for Yahoo! Web services. 
 * 
 * Responds to both HTTP GET and POST requests
 * 
 * Author: Jason Levitt
 * December 7th, 2005
 */

if($_GET['json']){
	$json = str_replace('\"', '"', $_GET['json']);
	$json = str_replace(' ', '%20', $json);
	
	$url = $_GET['url'].'?json='.$json;
}
else{
	$url = $_GET['url'];
}

// Open the Curl session
$session = curl_init($url);

// Don't return HTTP headers. Do return the contents of the call
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

// Make the call
$response = curl_exec($session);

$format = explode('/', $url);
$extension = explode('.', $format[5]);

// The web service returns XML. Set the Content-Type appropriately
switch($extension[1]){
	case "kml":
		header('Content-type: application/vnd.google-earth.kml+xml');
		break;
	
	case "gml":
	case "xml":
		header("Content-Type: text/xml");
		break;
	
	case "json":
	case "geojson":
		header("Content-Type: application/jsonrequest");
		break;
	
	case "html":
		header("Content-Type: text/html");
		break;
	
	case "wkt":
	default: 
		header("Content-Type: text/plain");
		break;
}

echo $response;
curl_close($session);

?>
