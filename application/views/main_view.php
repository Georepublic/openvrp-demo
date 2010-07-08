<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

 	<title><?=$title?></title>	

	<script src="<?=base_url();?>resources/ext-3.2.1/adapter/ext/ext-base.js" type="text/javascript"></script> 
	<script src="<?=base_url();?>resources/ext-3.2.1/ext-all.js"  type="text/javascript"></script> 
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/resources/css/ext-all.css" /> 

	<script src="<?=base_url();?>resources/openlayers-2.9.1/lib/OpenLayers.js" type="text/javascript"></script> 
	<script src="<?=base_url();?>resources/geoext-0.7/lib/GeoExt.js" type="text/javascript"></script>  
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/geoext-0.7/resources/css/geoext-all-debug.css" />

	<script type="text/javascript"> 
	
		Ext.onReady(function() {
		    var map = new OpenLayers.Map();
		    var layer = new OpenLayers.Layer.WMS(
		        "Global Imagery",
		        "http://maps.opengeo.org/geowebcache/service/wms",
		        {layers: "bluemarble"}
		    );
		    map.addLayer(layer);
	 
		    new GeoExt.MapPanel({
		        renderTo: 'gxmap',
		        height: 400,
		        width: 600,
		        map: map,
		        title: 'A Simple GeoExt Map'
		    });
		    
		});
	</script> 

</head>

<body>
	<h1>Hello <?=$account?>!</h1>
	
	<div id="gxmap"></div> 

	<p><?=anchor('login/logout', 'Logout');?></p>
</body>
</html>
