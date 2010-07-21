/**
 * Geography Panel
 */
Ext.onReady(function() {

	GRP.defaultCenter = new OpenLayers.LonLat(135.5,34.55);
	GRP.projection = new OpenLayers.Projection("EPSG:4326");

	/**
	 * Map Definition
	 */
	GRP.map = new OpenLayers.Map({
		'projection': 'EPSG:900913',
		'units':"m",
		'maxResolution': 156543.0339,
		'maxExtent': new OpenLayers.Bounds(-20037508.0,-20037508.0,20037508.0,20037508.0),
		'transitionEffect': 'resize',
		'controls': [
			new OpenLayers.Control.PanZoomBar(),
			new OpenLayers.Control.LayerSwitcher(),
			new OpenLayers.Control.MousePosition({
				separator: ' | ',
				numDigits: 6,
				displayProjection: GRP.projection
			}),
			new OpenLayers.Control.MouseDefaults()
		],
		panMethod: null
	});
		
	/**
	 * Layer Definition
	 */
	GRP.map.addLayers([
		new OpenLayers.Layer.OSM(" OSM Mapnik"),
		new OpenLayers.Layer.TMS(" OSM Cycle Map", 
			["http://a.andy.sandbox.cloudmade.com/tiles/cycle/",
			 "http://b.andy.sandbox.cloudmade.com/tiles/cycle/",
			 "http://c.andy.sandbox.cloudmade.com/tiles/cycle/"],
			{ type: 'png', getURL: GRP.getTileURL, displayOutsideMaxExtent: true,
			  attribution: '<b>OpenCycleMap.org - the <a href="http://www.openstreetmap.org">OpenStreetMap</a> Cycle Map</b><br />', 
			  transitionEffect: 'resize'}
		),
		new OpenLayers.Layer.WMS(" Blue Marble",
			"http://maps.opengeo.org/geowebcache/service/wms",
			{layers: "bluemarble"}
		)
	]);
	
	/**
	 * Map Panel
	 */
	GRP.mapPanel = new GeoExt.MapPanel({
		region: "east",
		border: false,
		map: GRP.map,
		center: GRP.defaultCenter.transform(
			GRP.projection, 
			new OpenLayers.Projection("EPSG:900913")
		),
		zoom: 14,
		border: true,
		split: true,
		collapsible: true,
		collapseMode: 'mini',
		hideCollapseTool: true,
		resizeTabs: true,
		width: '50%',
		minSize: 250
	});
	
});
