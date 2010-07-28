/**
 * Depot Tabpanel
 */
Ext.onReady(function() {

	/**
	 * Layer Definition
	 */
	var plannerStyle = new OpenLayers.StyleMap({
			'default': OpenLayers.Util.applyDefaults({
					pointRadius: 12
				}, OpenLayers.Feature.Vector.style["default"]), 
			'select': {
				pointRadius: 16
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

	GRP.layer.route = new OpenLayers.Layer.Vector( "Route");
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
	
	GRP.store.route = new GeoExt.data.FeatureStore({
		layer: GRP.layer.route
	});

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
					//GRP.form.account.form.loadRecord(rec);
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
				/*
				var points = [];
				console.info(GRP.store.route);
				
				var p;
				
                GRP.store.planner.data.each(function(i){
								
					if(i.data.vehicle_id == 1) {
						
						p = OpenLayers.Geometry.Point(
							i.data.feature.geometry.x,
							i.data.feature.geometry.y
						);
						
						points.push(p);
					}
				});
				
				GRP.layer.route.addFeatures([
					new OpenLayers.Geometry.LineString(points)
				]);
				GRP.layer.route.refresh({force: true});*/
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
				GRP.store.planner.load({
					params: GRP.form.planner.form.getValues()
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
