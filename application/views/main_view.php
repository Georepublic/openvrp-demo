<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>OpenVRP Manager 0.1</title>

	<link rel="shortcut icon" href="<?=base_url();?>resources/images/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/resources/css/xtheme-gray.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/css/GridFilters.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/css/RangeMenu.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/geoext-0.7/resources/css/geoext-all.css" />
	<link href='http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:regular,bold' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/main.css" />
</head>

<body>
	<div id="loading">
	    <div class="loading-indicator">
	    	<img src="<?=base_url();?>resources/images/loading-icon.gif"/>
			<span id="loading-title">
				<script type="text/javascript">
					document.write("OpenVRP Manager 0.1");
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
	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=ABQIAAAAXRQTsj9_bEUYstPWwJ4iOBRDCOZJPnKRX0oU_LmqFt7NOTjq6hS2mPCvcmLNCe4ZigJKszqAOVqRpA" type="text/javascript"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/proj4js/lib/proj4js-combined.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/openlayers-2.9.1/OpenLayers.js"></script>

	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Loading ExtJS ..."</script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/ext-all.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext.ux.datetime.js"></script>  
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/menu/RangeMenu.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/menu/ListMenu.js"></script>
	
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/GridFilters.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/filter/Filter.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/filter/StringFilter.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/filter/DateFilter.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/filter/ListFilter.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/filter/NumericFilter.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/examples/ux/gridfilters/filter/BooleanFilter.js"></script>
	
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Loading GeoExt ..."</script>
	<script type="text/javascript" src="<?=base_url();?>resources/geoext-0.7/script/GeoExt.js"></script>  
	
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Initialize ..."</script>
	<!--script type="text/javascript" src="<?=base_url();?>resources/beautify/beautify.js" ></script-->

	<script type="text/javascript">	
		Ext.ns('GRP');
		Ext.ns('GRP.store');
		Ext.ns('GRP.layer');
		Ext.ns('GRP.grid');
		Ext.ns('GRP.form');
		Ext.ns('GRP.tab');
		
		var wkt  = new OpenLayers.Format.WKT();
		var kml  = new OpenLayers.Format.KML();
		var json = new OpenLayers.Format.GeoJSON();
		
		var logout = 'User: [<?=$account?>] | <?=anchor("login/logout", "Logout");?>';

		GRP.baseURL = "<?=base_url();?>";
		
		Ext.BLANK_IMAGE_URL = '<?=base_url();?>resources/ext-3.2.1/resources/images/default/s.gif';

		OpenLayers.IMAGE_RELOAD_ATTEMPTS = 5;
		OpenLayers.Util.onImageLoadErrorColor = 'transparent';	
   	</script> 

	<script type="text/javascript" src="<?=base_url();?>resources/map.geography.js"></script>  
	<script type="text/javascript" src="<?=base_url();?>resources/map.account.js"></script>  
	<script type="text/javascript" src="<?=base_url();?>resources/map.depot.js"></script>  
	<script type="text/javascript" src="<?=base_url();?>resources/map.vehicle.js"></script>  
	<script type="text/javascript" src="<?=base_url();?>resources/map.order.js"></script>  
	<script type="text/javascript" src="<?=base_url();?>resources/map.planner.js"></script>  
	
	<script type="text/javascript">
	
	Ext.onReady(function() {
	
		Ext.QuickTips.init();
		Ext.form.Field.prototype.msgTarget = 'side';
		
		/**
		 * Viewport Layout 
		 */
		var viewport = new Ext.Viewport({
			layout: 'border',
			renderTo: Ext.getBody(),
			listeners : {
				afterlayout: function() {
					Ext.get('loading').hide();
				}
			},
			items: [{
				xtype: 'panel',
				region: 'center',
				layout: 'border',
				border: true,
				bbar: new Ext.Toolbar({
					items: [
						{ xtype: "tbtext", text: '<a href="http://georepublic.de" target="_blank">Georepublic UG</a> & <a href="http://georepublic.co.jp" target="_blank">Georepublic Japan</a> &copy;2010' }, "-",
						{ xtype: 'tbtext', text: 'OpenVRP Manager - Prototype' }, "->",
						{ xtype: "tbtext", text: 'Data/Maps Copyright 2010 <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap and contributors</a> | License: <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">Creative Commons BY-SA</a>' }
					]
				}),
				items: [{
					region: 'north',
					html: '<h3>OpenVRP manager</h3>',
					baseCls: 'openvrp-header-panel',
					height: 100
				},{
					region: 'center',
					xtype: 'tabpanel',
					header: false,
					activeTab: 0, 
					border: true,
					enableTabScroll: true,
					defaults: {autoScroll:true},
					items: [
						GRP.tab.planner,
						GRP.tab.order,
						GRP.tab.depot, 
						GRP.tab.vehicle, 
						GRP.tab.account
					],
					listeners: {
						beforetabchange: function(panel, newtab, curtab){
							try{
								GRP.store[newtab.id].load();
							}
							catch(e){}
							
							GRP.layer.order.modifyControl.deactivate();
							GRP.layer.depot.modifyControl.deactivate();
						}
					}
				}, 
				GRP.mapPanel]
			}]
		});
		
	});
	</script> 

        <!-- Piwik -->
        <script type="text/javascript">
                var pkBaseURL = (("https:" == document.location.protocol) ? "https://stats.georepublic.net/" : "http://stats.georepublic.net/");
                document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
        </script><script type="text/javascript">
                try {
                        var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 6);
                        piwikTracker.trackPageView();
                        piwikTracker.enableLinkTracking();
                } catch( err ) {}
        </script><noscript><p><img src="http://stats.georepublic.net/piwik.php?idsite=6" style="border:0" alt="" /></p></noscript>
        <!-- End Piwik Tag -->

</body>
</html>
