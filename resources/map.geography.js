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
			new OpenLayers.Control.LayerSwitcher({
				activeColor: 'green'
			}),
			new OpenLayers.Control.MousePosition({
				separator: ' | ',
				numDigits: 6,
				displayProjection: GRP.projection
			}),
			new OpenLayers.Control.MouseDefaults(),
			new OpenLayers.Control.NavigationHistory()
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
		),
		new OpenLayers.Layer.Google(" Google Streets",
			{sphericalMercator: true, numZoomLevels: 19}
		),
		new OpenLayers.Layer.Google(" Google Satellite",
			{type: G_SATELLITE_MAP, sphericalMercator: true, numZoomLevels: 19}
		),
		new OpenLayers.Layer.Google(" Google Hybrid",
			{type: G_HYBRID_MAP, sphericalMercator: true, numZoomLevels: 19}
		)
	]);

	/**
	 * Map Panel
	 */
	GRP.mapPanel = new Ext.Panel({
		region: 'east',
		border: true,
		split: true,
		collapsible: true,
		collapseMode: 'mini',
		hideCollapseTool: true,
		resizeTabs: true,
		width: '50%',
		minSize: 250,
		layout:'vbox',
		layoutConfig: { align: 'stretch', pack: 'start' },
		items: [ 
			new GeoExt.MapPanel({
				map: GRP.map,
				viewConfig: {forceFit: true},
				loadMask: true,
				center: GRP.defaultCenter.transform(
					GRP.projection, 
					new OpenLayers.Projection("EPSG:900913")
				),
				flex: 1,
				zoom: 14,
				border: false,
				tbar: ['<b>Map Panel</b>' ,'->', logout ]
			})
		]
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
});
