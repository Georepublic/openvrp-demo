/**
 * Planner Tabpanel
 */
Ext.onReady(function() {

	/**
	 * Layer Definition
	 */
	var plannerStyle = new OpenLayers.StyleMap({
			'default': OpenLayers.Util.applyDefaults({
					pointRadius: 10
				}, OpenLayers.Feature.Vector.style["default"]), 
			'select': {
				fillOpacity: 1.0
			}
		});	
	plannerStyle.addUniqueValueRules("default", "pick_up", {
		"t": {fillColor: 'green', strokeColor: 'green'},
		"f": {fillColor: 'red', strokeColor: 'red'}
	});

	GRP.layer.planner = new OpenLayers.Layer.Vector( "Planner", {
		styleMap: plannerStyle
	});
	GRP.map.addLayer(GRP.layer.planner);

	var routeStyle = new OpenLayers.StyleMap({
			'default': OpenLayers.Util.applyDefaults({
				strokeWidth: 5,
				strokeColor: 'orange',
				strokeOpacity: 0.8,
				strokeDashstyle: 'dash'
			}, OpenLayers.Feature.Vector.style["default"])
		});	
	routeStyle.addUniqueValueRules("default", "vehicle_id", {
		"1": {strokeColor: '#FF00FF'},
		"2": {strokeColor: '#800000'},
		"3": {strokeColor: '#800080'},
		"4": {strokeColor: '#808000'},
		"5": {strokeColor: '#008080'},
		"6": {strokeColor: '#000080'},
		"7": {strokeColor: '#00FF00'},
		"8": {strokeColor: '#FF4500'},
		"9": {strokeColor: '#000000'}
	});

	GRP.layer.route = new OpenLayers.Layer.Vector( "Route", {
		styleMap: routeStyle
	});
	GRP.map.addLayer(GRP.layer.route);
	
	/**
	 * Store Definition
	 */
	GRP.store.calculation = new Ext.data.SimpleStore({
		fields: ['method', 'name'],
		autoLoad: true,
		data : [
			[ 'euclidian', 'euclidian' ]
		]
	});

	GRP.store.nearest = new Ext.data.SimpleStore({
		fields: ['nearest', 'name'],
		autoLoad: true,
		data : [
			[ '*', 'All orders' ],
			[ '10', 'Nearest 10 orders' ],
			[ '20', 'Nearest 20 orders' ],
			[ '40', 'Nearest 40 orders' ]
		]
	});

	GRP.store.planner = new GeoExt.data.FeatureStore({
		layer: GRP.layer.planner,
		fields: [
			{name: 'id', type: 'int'},
			{name: 'size', type: 'float'},
			{name: 'order_id', type: 'int', convert: function(v,r){
				switch(v){
					case '0':
						return "<span style='color:blue;'>Depot</span>";
						break;							
					default :
						return v;
						break;
				}
			}},
			{name: 'vehicle_id', type: 'int'},
			{name: 'pick_up', type: 'text', convert: function(v,r){
				switch(v){
					case 't':
						return "<span style='color:green;'>Pickup</span>";
						break;							
					case 'f':
						return "<span style='color:red;'>Dropoff</span>";
						break;
				}
			}},
			{name: 'at', type: 'date', dateFormat: 'c'}
		],
		proxy: new GeoExt.data.ProtocolProxy({
			protocol: new OpenLayers.Protocol.HTTP({
				readWithPOST: true,
				url: GRP.baseURL + 'darp/calculate',
				format: new OpenLayers.Format.GeoJSON()
			})
		})
	});
	GRP.store.planner.setDefaultSort('id', 'asc');
	
    var filters = new Ext.ux.grid.GridFilters({
        encode: false,
        local: true,
        filters: [{
            type: 'numeric',
            dataIndex: 'order_id'
        },{
            type: 'numeric',
            dataIndex: 'vehicle_id'
        },{
            type: 'date',
            dataIndex: 'at'
        }]
    });    

	/**
	 * Grid Definition
	 */
	GRP.grid.planner = new Ext.grid.GridPanel({
		title: "DARP Calculation",
		region: "center",
		viewConfig: {forceFit: true},
		store: GRP.store.planner,
		flex: 1,
		loadMask: true,
		stripeRows: true,
		autoWidth: true,
		autoScroll: true,
		autoExpandColumn: 1,			
        plugins: [filters],
		cm: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},
			columns: [
				{header: "ID", dataIndex: "id", align: 'right', width: 30},
				{header: "Order ID", dataIndex: "order_id", align: 'right', filterable: true},
				{header: "Vehicle ID", dataIndex: "vehicle_id", align: 'right', filterable: true},
				{header: "Capacity", dataIndex: "size", align: 'right'},
				{header: "Service", dataIndex: "pick_up"},
				{header: "at (time)", dataIndex: "at", renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), filterable: true}
			]
		}),
		sm: new GeoExt.grid.FeatureSelectionModel({
			layer: GRP.layer.planner,
			singleSelect: true,
			listeners: {
				rowselect: function(sm, row, rec) {
					// Zoom to feature
					//var a = rec.data.feature.geometry.getBounds();
					//GRP.map.panTo(a.getCenterLonLat());
				},
				rowdeselect: function(sm, row, rec) {
					//GRP.form.account.form.reset();
				}
			}
		}),
		bbar: ['->',{
            text: 'Clear Filter Data',
			iconCls: 'button-filter',
            handler: function () {
                GRP.grid.planner.filters.clearFilters();
            } 
        },{
            text: 'Draw Route',
			iconCls: 'button-route',
            handler: function() {
				GRP.layer.route.destroyFeatures(GRP.layer.route.features);
				
				var vehicles = GRP.store.planner.collect('vehicle_id');	
				
				Ext.each(vehicles,function(item, idx, arr){
					
					var points = [];					
					GRP.store.planner.filter('vehicle_id',item);
					
					GRP.store.planner.data.each(function(i){	
						
						var g = i.data.feature.geometry;
						/*g.transform(
							new OpenLayers.Projection("EPSG:900913"),
							new OpenLayers.Projection("EPSG:4326")
						);*/

						points.push(							
							new OpenLayers.Geometry.Point(g.x, g.y)
						);
					});
					
					/*
					Ext.each(points,function(item, idx, pts){	
						if(idx >0) {
							var url = 'http://api.cirius.co.jp/wrs/1.0.0/suuchi/lonlat/shortest_path.geojson?';
							var get = [
								'start=' + pts[idx]['x'] + '%20' + pts[idx]['y'],
								'end=' + pts[idx-1]['x'] + '%20' + pts[idx-1]['y'],
								'api_key=ecba2ad10552f1082b1d36bc9867c5249415a52a84c9d90e10db3e26a45f7e24',
								'sridIn=4326'
							];	
							
							GRP.store.route.load({
								params: { 'url': url + get.join('&') },
								add: true
							});						
						}	
					});*/
					
					var lineString = new OpenLayers.Geometry.LineString(points);
					var lineFeature = new OpenLayers.Feature.Vector(
								lineString, {
									vehicle_id: item
								}
							); 					
							console.info(lineFeature);
					GRP.layer.route.addFeatures([lineFeature]);

					GRP.store.planner.clearFilter();
				});			
            } 
        }]
	});	
            
	/**
	 * Form Panel
	 */
	GRP.form.planner = new Ext.form.FormPanel({
		title:'DARP Settings',
		autoHeight: true,
		border: true,
		frame: true,
		method: 'POST',
		monitorValid: true,
		items: [{
			layout: 'column',
			border: false,
			frame: true,
			items: [{
				columnWidth: .5,
				layout: 'form',
				labelWidth: 80,
				defaults: { 
					xtype: 'textfield',
					anchor: '90%'
				},
				items: [{
					fieldLabel: '<b><u>Depot</u></b>',
					emptyText: 'Select Depot ...',
					xtype: 'combo',
					mode: 'local',
					store: GRP.store.depot,
					displayField: 'name',
					hiddenName: 'depot_id', 
					valueField: 'id',						
					editable: false,
					forceSelection: true,
					triggerAction: 'all',
					selectOnFocus: true,
					allowBlank: false,
					getListParent: function() {
						return this.el.up('.x-menu');
					}
				}]
			},{
				columnWidth: .5,
				layout: 'form',
				labelWidth: 80,
				defaults: { 
					xtype: 'textfield',
					anchor: '90%'
				},
				items: [{
					fieldLabel: '<b><u>Calculation</u></b>',
					emptyText: 'Select Method ...',
					xtype: 'combo',
					mode: 'local',
					store: GRP.store.calculation,
					displayField: 'name',
					hiddenName: 'method', 
					valueField: 'method',						
					editable: false,
					forceSelection: true,
					triggerAction: 'all',
					selectOnFocus: true,
					allowBlank: false,
					getListParent: function() {
						return this.el.up('.x-menu');
					}
				}/*,{
					emptyText: 'Select nearest ...',
					xtype: 'combo',
					mode: 'local',
					store: GRP.store.nearest,
					displayField: 'name',
					hiddenName: 'nearest', 
					valueField: 'nearest',						
					editable: false,
					forceSelection: false,
					triggerAction: 'all',
					selectOnFocus: true,
					allowBlank: true,
					getListParent: function() {
						return this.el.up('.x-menu');
					}
				}*/]
			}]
		}],
		buttons: [{
			text: 'Calculate',
			formBind: true,
			type: 'submit',
			iconCls: 'button-calc',
			handler: function(evt){ 
				GRP.layer.route.destroyFeatures(GRP.layer.route.features);
				GRP.store.planner.load({
					params: GRP.form.planner.form.getValues()
				});
			}
		},{
			text: 'Debug (SQL)',
			formBind: true,
			type: 'submit',
			handler: function(evt){ 
				Ext.Ajax.request({
					url: GRP.baseURL + 'darp/debugsql',
					method: 'POST',
					isUpload: true,
					params: GRP.form.planner.form.getValues(),
					success : function(form, action) {
						var result = form.responseText;
						Ext.getCmp('response-text').setValue(result);						
				        debugPopup.show();
					}
				});
			}
		},{
			text: 'Report',
			type: 'submit',
			disabled: true,
			iconCls: 'button-report',
			handler: function(evt){ 
				// todo
			}
		}]
	});      

	/**
	 * Debug Popup
	 */
	var debugPopup = new Ext.Window({
		title: 'Debug - SQL',
		border: false,
		width:700,
		height:400,
		modal: true,
		layout: 'fit',
		resizable: false,
		closeAction: 'hide',
		items: [
			new Ext.FormPanel({
				layout: 'fit',
				border: false,
				items: [{
					xtype: 'textarea',
					id: 'response-text',
					monitorResize: true
				}]
			})
		],
		plain: true,				
		buttons: [{
			text: 'Close',
			handler: function(){
				debugPopup.hide();
			}
		}]
	});

	/**
	 * Tabpanel Definition
	 */
	GRP.tab.planner = new Ext.Panel({
		title: 'Trip Planer', 
		iconCls: 'tab-planner',
		id: 'trip-manager',
		region: 'center',
		border: false,
		layout:'vbox',
		layoutConfig: {
			align : 'stretch',
			pack  : 'start'
		},
		items: [
			GRP.grid.planner, GRP.form.planner
		]
	});
	
});
