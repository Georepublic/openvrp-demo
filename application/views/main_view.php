<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Mobility Manager 0.1</title>

	<link rel="shortcut icon" href="<?=base_url();?>resources/images/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/resources/css/xtheme-gray.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/geoext-0.7/resources/css/geoext-all.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/css/main.css" />
</head>

<body>
	<div id="loading">
	    <div class="loading-indicator">
	    	<img src="<?=base_url();?>resources/images/loading-icon.gif"/>
			<span id="loading-title">
				<script type="text/javascript">
					document.write("Mobility Manager 0.1");
				</script>
			</span><br/>
			<span id="loading-msg">
				<script type="text/javascript">
					document.write("Loading CSS ...");
				</script>
			</span>
		</div>
	</div>		
	
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Loading OpenLayers ..."</script>
	<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAAXRQTsj9_bEUYstPWwJ4iOBR0n6wdtRn3aS13_s93gCCDNKnYOhTWTI0apiNy8GvBRpxiDe5WFcX3_A"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/proj4js/lib/proj4js-combined.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/openlayers-2.9.1/lib/OpenLayers.js"></script>

	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Loading ExtJS ..."</script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/ext-all.js"></script>
	
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Loading GeoExt ..."</script>
	<script type="text/javascript" src="<?=base_url();?>resources/geoext-0.7/lib/GeoExt.js"></script>  
	
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Initialize ..."</script>
	<script type="text/javascript" src="<?=base_url();?>resources/beautify/beautify.js" ></script>
	<script type="text/javascript">
	
	Ext.BLANK_IMAGE_URL = '<?=base_url();?>resources/ext-3.2.1/resources/images/default/s.gif';

	Ext.onReady(function() {
	
		OpenLayers.IMAGE_RELOAD_ATTEMPTS = 5;
		OpenLayers.Util.onImageLoadErrorColor = 'transparent';
		//OpenLayers.ProxyHost = 'proxy.php?url=http://foss4g.orkney.jp/1.1.0';

		Ext.QuickTips.init();
		Ext.form.Field.prototype.msgTarget = 'side';

		var map = new OpenLayers.Map();
		var layer = new OpenLayers.Layer.WMS(
		    "Global Imagery",
		    "http://maps.opengeo.org/geowebcache/service/wms",
		    {layers: "bluemarble"}
		);
		map.addLayer(layer);

		new Ext.Viewport({
		    layout: "border",
			listeners : {
				afterlayout: function() {
					Ext.get('loading').hide();
				}
			},
		    items: [{
		        region: "center",
		        id: "mappanel",
		        title: "Mobility Manager 0.1 - Map",
		        xtype: "gx_mappanel",
		        map: map,
		        layers: [layer],
				bbar: new Ext.Toolbar({
					items: [
						{ xtype: 'tbtext', text: "User: [<?=$account?>]" }, '->',
						{ xtype: 'tbtext', text: '<?=anchor("login/logout", "Logout");?>' }
					]
				})
		    }]
		});
	});
	
	</script> 
</body>
</html>
