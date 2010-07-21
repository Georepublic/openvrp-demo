/**
 * Depot Tabpanel
 */
Ext.onReady(function() {

	/**
	 * Layer Definition
	 */
	GRP.layer.planner = new OpenLayers.Layer.Vector( "Darp", {
		displayInLayerSwitcher: false
	});
	GRP.map.addLayer(GRP.layer.planner);

	
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

	GRP.store.planner = new GeoExt.data.FeatureStore({
		layer: GRP.layer.planner,
		fields: [
			{name: 'id', type: 'int'},
			{name: 'order_id', type: 'int'},
			{name: 'vehicle_id', type: 'int'},
			{name: 'pick_up', type: 'text'},
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
		cm: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},
			columns: [
				{header: "Order ID", dataIndex: "order_id", align: 'right'},
				{header: "Vehicle ID", dataIndex: "vehicle_id", align: 'right'},
				{header: "Pickup", dataIndex: "pick_up"},
				{header: "Pickup at", dataIndex: "at", renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
			]
		}),
		sm: new GeoExt.grid.FeatureSelectionModel({
			//selectControl: GRP.layer.planner.modifyControl.selectControl,
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
		})
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
					width: 130,
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
					width: 130,
					forceSelection: true,
					triggerAction: 'all',
					selectOnFocus: true,
					allowBlank: false,
					getListParent: function() {
						return this.el.up('.x-menu');
					}
				}]
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
				
				/*GRP.form.planner.form.submit({
					url: GRP.baseURL + 'darp/calculate',
					success: function(form,action){ 
						//GRP.store.vehicle.reload(); 
						GRP.form.planner.form.reset();
					},            
					failure: function(form,action){  }
				});*/
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
