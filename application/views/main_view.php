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
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/css/main.css" />
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
		
		var wkt = new OpenLayers.Format.WKT();

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
				border: false,
				tbar: new Ext.Toolbar({
					items: [
						"->", { xtype: "tbtext", text: 'Current user: [<?=$account?>] | <?=anchor("login/logout", "Logout");?>' } 
					]
				}),				
				bbar: new Ext.Toolbar({
					items: [
						{ xtype: "tbtext", text: "OpenVRP Manager - Prototype &copy;2010" },"->",
						{ xtype: "tbtext", text: 'Data/Maps Copyright 2010 <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap and contributors</a> | License: <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">Creative Commons BY-SA</a>' } 
					]
				}),				
				items: [{
					region: 'north',
					html: '<h3 style="margin:20px;">OpenVRP Manager - Protoype 0.1</h3>',
					height: 60
				},{
					region: 'center',
					xtype: 'tabpanel',
					header: false,
					activeTab: 0, 
					border: false,
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
	
	/**
	 * OSM getTileURL calculation
	 */
	GRP.getTileURL = function(bounds) {
		var res = this.map.getResolution();
		var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
		var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
		var z = this.map.getZoom();
		var limit = Math.pow(2, z);
		
		if (y < 0 || y >= limit){
			return null;
		}
		else {
			x = ((x % limit) + limit) % limit;
			
			var url = this.url;
			var path = z + "/" + x + "/" + y + ".png";
			
			if (url instanceof Array) {
				url = this.selectUrl(path, url);
			}
			return url + path;
		}
	}
	</script> 
</body>
</html>
