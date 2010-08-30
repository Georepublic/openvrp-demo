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
	 * Capabilities area
	 */
	GRP.layer.area = new OpenLayers.Layer.Vector(" Service Extent",{
		styleMap: new OpenLayers.StyleMap({
			'default': OpenLayers.Util.applyDefaults({
				strokeWidth: 4,
				strokeColor: '#27ca43',
				fill: false
				}, OpenLayers.Feature.Vector.style["default"]), 
			'select': {
				strokeColor: '#27ca43',
				fill: false
			}
		})
	});
	GRP.map.addLayer(GRP.layer.area);

	/**
	 * Profile Store
	 */
	var profileStore = new Ext.data.Store({
		url: './data/profiles.xml',
		autoLoad: true,
		reader: new Ext.data.XmlReader({
			record: 'profile',
			id: '@name'
		},[
			{ name: 'name', mapping: '@name' },
			{ name: 'title', mapping: '@title' },
			{ name: 'description', mapping: 'description' }
		]),
		listeners: {
			load: function(store, rec, idx) {
				var combo = Ext.getCmp('combo-select-profile');
				combo.setValue('19070025e2ca2c0a65f29e8bcf8dc1e4');
				combo.fireEvent('select', combo, store.getAt(0), 0);
			}
		}
	});

	/**
	 * Capabilities store
	 */
	var capabilities = new Ext.data.JsonStore({
		root: 'capabilities',
		proxy: new Ext.data.HttpProxy({
			url: GRP.ProxyURL 
		}),
		idProperty: 'name',
		fields: ['name','title','description','parameters','services'],
		listeners: {
			beforeload: function(store,options) {
				Ext.MessageBox.show({
					title: 'Profile request',
					msg: 'Requesting capabilities document ...',
					progressText: 'Connecting ...',
					width: 300,
					wait: true,
					waitConfig: {interval:200},
					animEl: 'wait-capabilities'
				});
			},
			load: function(store, records, options){

				var rdata = records[0].data;

				var setp = Ext.getCmp('button-set-point');
				(records.length > 0) ? setp.enable() : setp.disable();

				// Pan to default extent
				var e = rdata.parameters.extent.value.split(',');
				var p1 = trim(e[0]);
				var p2 = trim(e[1]);
				
				var extent = [];
				extent.push(parseFloat(p1.split(' ')[0]));
				extent.push(parseFloat(p1.split(' ')[1]));
				extent.push(parseFloat(p2.split(' ')[0]));
				extent.push(parseFloat(p2.split(' ')[1]));
				
				var bounds = OpenLayers.Bounds.fromArray(extent);
				bounds.transform(
					new OpenLayers.Projection(rdata.parameters.srid_in.value), 
					GRP.map.getProjectionObject()
				);

				if(!bounds.containsBounds(GRP.map.getExtent())){
					GRP.map.zoomToExtent(bounds);

					// Empty stores
					//pointStore.removeAll();
					//routeStore.removeAll();
				}

				// Draw service extent
				GRP.layer.area.destroyFeatures();
				var feature = new OpenLayers.Feature.Vector(bounds.toGeometry());
				GRP.layer.area.addFeatures([feature]);

				Ext.getCmp('button-set-point').toggle(false);
				Ext.MessageBox.hide();
			}
		}
	});

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
				tbar: ['<b>Service Area</b> ', {
					xtype: 'combo',
					mode: 'local',
					store: profileStore,
					width: 250,
					emptyText: 'Select Area ...',
					displayField: 'title',
					hiddenName: 'name', 
					valueField: 'name',
					editable: false,
					forceSelection: true,
					triggerAction: 'all',
					selectOnFocus: true,
					autoSelect: true,
					id: 'combo-select-profile',
					listeners: {
						select: function(combo, record, idx){
							// Get profile capabilties
							var url = GRP.ProxyURL + '/' 
										+ record.data.name + '/capabilities.json'
							capabilities.proxy.setUrl(url, true);
							capabilities.load();
						}
					}
				},'-', 
				new GeoExt.Action({
					text: "Set Point",
					iconCls: 'button-set-point',
					id: 'button-set-point',
					map: GRP.map,
					disabled: true,
					toggleGroup: "menu-route",
					group: "menu-route",
					control: new OpenLayers.Control.Click({
						trigger: function(evt) {
							var loc = GRP.map.getLonLatFromViewPortPx(evt.xy);
							addToPopup(loc);
						}
					})
				}),
				'->', logout ]
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

	/**
	 * Trim function
	 */
	function trim (text) {
		return text.replace (/^\s+/, '').replace (/\s+$/, '');
	}
	
	/**
	 * addToPopup function
	 */
    function addToPopup(loc) {
    
		loc.transform(
			new OpenLayers.Projection("EPSG:900913"),
			GRP.projection
		);							
        
        // create the popup if it doesn't exist
        if(!popup) {
        
            popup = new GeoExt.Popup({
                title: "Set Point",
                id: "map-popup",
                width: 260,
                maximizable: false,
                collapsible: false,
                map: GRP.map,
                anchored: true,
                listeners: {
                    close: function() { popup = null; }
                },
				buttons: [{
					text: 'Start/Depot',
					handler: function(){ 
					
						feature.geometry.transform(
							new OpenLayers.Projection("EPSG:900913"),
							GRP.projection
						);

						switch(Ext.getCmp('tab-panel').getActiveTab().getId()){
							case 'order':
								Ext.getCmp('wkt-order-start').setValue(wkt.write(feature));
								break;
								
							case 'depot':
								Ext.getCmp('wkt-depot').setValue(wkt.write(feature));
								break;
						}
						
						popup.close();
						popup = null;
					}
				},{
					text: 'End',
					handler: function(){ 

						feature.geometry.transform(
							new OpenLayers.Projection("EPSG:900913"),
							GRP.projection
						);

						switch(Ext.getCmp('tab-panel').getActiveTab().getId()){
							case 'order':
								Ext.getCmp('wkt-order-end').setValue(wkt.write(feature));
								break;
						}

						popup.close();
						popup = null;
					}
				},{
					text: 'Close',
					handler: function(){ 
						popup.close();
						popup = null;
					}
				}]
            });
        }
        
        //popup.setTitle("You clicked " + loc.lon.toFixed(2) + ", " + loc.lat.toFixed(2) );

		loc.transform(
			GRP.projection,
			new OpenLayers.Projection("EPSG:900913")
		);	
								
        // This is awkward, but for now we need to create a feature to update
        // the popup position.  TODO: fix this for 1.0
        var feature = new OpenLayers.Feature.Vector(
            new OpenLayers.Geometry.Point(loc.lon, loc.lat)
        );  
        
        popup.feature = feature;        
        popup.doLayout();
        popup.show();
    }

});

/**
 * Click Control Class
 */
OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {                

    defaultHandlerOptions: {
        single: true,
        double: false,
        pixelTolerance: 0,
        stopSingle: true
    },

    initialize: function(options) {

        this.handlerOptions = OpenLayers.Util.extend(
            options && options.handlerOptions || {}, 
            this.defaultHandlerOptions
        );
        OpenLayers.Control.prototype.initialize.apply(
            this, arguments
        ); 
        this.handler = new OpenLayers.Handler.Click(
            this, 
            {
                click: this.trigger
            }, 
            this.handlerOptions
        );
    },
    
    CLASS_NAME: "OpenLayers.Control.Click"

});
