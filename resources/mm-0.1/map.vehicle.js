/**
 * Vehicle Tabpanel
 */
Ext.onReady(function() {

	/**
	 * Layer Definition
	 */
	GRP.layer.vehicle = new OpenLayers.Layer.Vector( "Vehicles", {
		displayInLayerSwitcher: false
		/*styleMap: new OpenLayers.StyleMap({
			'default': OpenLayers.Util.applyDefaults({
					externalGraphic: './resources/images/icon_vehicle.png',
					graphicOpacity: 1.0,
					pointRadius: 20
				}, OpenLayers.Feature.Vector.style["default"]), 
			'select': {
				pointRadius: 32
			}, 
			'edit': {
				strokeWidth: 7,
				strokeDashstyle: 'line',
				strokeColor: '#00FF00'
			}
		})*/
	});
	GRP.map.addLayer(GRP.layer.vehicle);
	
	/**
	 * Store Definition
	 */
	GRP.store.vehicle = new GeoExt.data.FeatureStore({
		layer: GRP.layer.vehicle,
		fields: [
			{name: 'vehicle_id', type: 'int'},
			{name: 'name', type: 'string'},
			{name: 'capacity', type: 'float'},
			{name: 'depot_id', type: 'int'},
			{name: 'created', type: 'date', dateFormat: 'c'},
			{name: 'updated', type: 'date', dateFormat: 'c'}
		],
		proxy: new GeoExt.data.ProtocolProxy({
			protocol: new OpenLayers.Protocol.HTTP({
				url: GRP.baseURL + 'vehicle/find',
				format: new OpenLayers.Format.GeoJSON()
			})
		})
	});
	GRP.store.vehicle.setDefaultSort('name', 'asc');
	GRP.store.vehicle.load();
	
	/**
	 * Grid Definition
	 */
	GRP.grid.vehicle = new Ext.grid.GridPanel({
		title: "List of Vehicles",
		region: "center",
		viewConfig: {forceFit: true},
		store: GRP.store.vehicle,
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
				{header: "Id", dataIndex: "vehicle_id", width: 10, align: 'right', hidden: true},
				{header: "Name", dataIndex: "name"},
				{header: "Capacity", dataIndex: "capacity", width: 35},
				{header: "Depot", dataIndex: "depot_id", width: 35},
				{header: "Created", dataIndex: "created", width: 30, 
					renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
				{header: "Updated", dataIndex: "updated", width: 30, 
					renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
			]
		}),
		sm: new GeoExt.grid.FeatureSelectionModel({
			//selectControl: GRP.layer.vehicle.modifyControl.selectControl,
			layer: GRP.layer.vehicle,
			singleSelect: true,
			listeners: {
				rowselect: function(sm, row, rec) {
					GRP.form.vehicle.form.loadRecord(rec);
				},
				rowdeselect: function(sm, row, rec) {
					GRP.form.vehicle.form.reset();
				}
			}
		})
	});
	
	/**
	 * Form Panel
	 */
	GRP.form.vehicle = new Ext.form.FormPanel({
		title:'Vehicle Details',
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
				columnWidth: .6,
				layout: 'form',
				labelWidth: 80,
				defaults: { 
					xtype: 'textfield',
					anchor: '90%'
				},
				items: [{
					fieldLabel: 'Vehicle name',
					emptyText: 'Choose name ...',
					allowBlank: false,
					name: 'name'
				},{
					fieldLabel: 'Capacity',
					emptyText: 'Define capacity ...',
					xtype: 'numberfield',
					allowBlank: false,
					name: 'capacity'
				},{
					xtype: 'combo',
					mode: 'local',
					store: GRP.store.depot,
					fieldLabel: 'Depot',
					emptyText: 'Select Depot ...',
					displayField: 'name',
					hiddenName: 'depot_id', 
					valueField: 'id',						
					editable: false,
					forceSelection: true,
					triggerAction: 'all',
					selectOnFocus: true
				}/*,{ 
					xtype: 'compositefield',
					fieldLabel: 'Location', 
					items: [{
						xtype: 'textfield',
						name: 'wkt',
						//readonly: true,
						allowBlank: false
					},{ 
						xtype: 'button', 
						tooltip: 'Set Location',
						enableToggle: true,
						toggleGroup: "set-point",
						iconCls: 'button-set-point',
						listeners: {
							toggle: function(btn,state){
								if(state){}
								else {}
							}
						}
					}]
				}*/]
			},{
				columnWidth: .4,
				layout: 'form',
				labelWidth: 80,
				defaults: { 
					xtype: 'datefield',
					format: 'Y-m-d H:i:s',
					anchor: '90%',
					disabled: true
				},
				items: [{
					xtype: 'textfield',
					fieldLabel: 'Vehicle ID',
					name: 'vehicle_id', 
				},{
					fieldLabel: 'Created',
					name: 'created',
				},{
					fieldLabel: 'Updated',
					name: 'updated',
				}]
			}]
		}],
		buttons: [{
			text: 'Save',
			formBind: true,
			type: 'submit',
			iconCls: 'button-save',
			handler: function(){ 
				var rec = GRP.grid.vehicle.getSelectionModel().getSelected();				
				if(rec) {					
					GRP.form.vehicle.form.submit({
						url: GRP.baseURL + 'vehicle/update/' + rec.data.vehicle_id,
						success: function(form,action){ 
							GRP.store.vehicle.reload(); 
							GRP.form.vehicle.form.reset();
						},            
						failure: function(form,action){}
					});
				}
			}
		},{
			text: 'Add as new',
			formBind: true,
			type: 'submit',
			iconCls: 'button-add',
			handler: function(){ 
				GRP.form.vehicle.form.submit({
					url: GRP.baseURL + 'vehicle/create',
					success: function(form,action){ 
						GRP.store.vehicle.reload(); 
						GRP.form.vehicle.form.reset();
					},            
					failure: function(form,action){}
				});
			}
		},{
			text: 'Delete',
			formBind: true,
			type: 'submit',
			iconCls: 'button-delete',
			handler: function(){ 
				var rec = GRP.grid.vehicle.getSelectionModel().getSelected();

				if(rec) {					
					GRP.form.vehicle.form.submit({
						url: GRP.baseURL + 'vehicle/delete/' + rec.data.vehicle_id,
						success: function(form,action){ 
							GRP.store.vehicle.reload(); 
							GRP.form.vehicle.form.reset();
						},            
						failure: function(form,action){}
					});
				}
			}
		},{
			text: 'Clear',
			iconCls: 'button-clear',
			handler: function(){ 
				GRP.form.vehicle.form.reset(); 
				var sm = GRP.grid.vehicle.getSelectionModel();
				sm.clearSelections();
			}
		}]
	});
	
	/**
	 * Tabpanel Definition
	 */
	GRP.tab.vehicle = new Ext.Panel({
		title: 'Fleet Manager', 
		iconCls: 'tab-fleet',
		id: 'vehicle',
		region: 'center',
		border: false,
		layout:'vbox',
		layoutConfig: { align : 'stretch', pack  : 'start' },
		items: [ GRP.grid.vehicle, GRP.form.vehicle ]
	});
	
});
