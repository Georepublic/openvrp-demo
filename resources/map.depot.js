/**
 * Depot Tabpanel
 */
Ext.onReady(function() {

	/**
	 * Layer Definition
	 */
	GRP.layer.depot = new OpenLayers.Layer.Vector( "Depots", {
		styleMap: new OpenLayers.StyleMap({
			'default': OpenLayers.Util.applyDefaults({
					externalGraphic: './resources/images/icon_depot.png',
					graphicOpacity: 1.0,
					pointRadius: 32
				}, OpenLayers.Feature.Vector.style["default"]), 
			'select': {
				pointRadius: 32
			}, 
			'edit': {
				strokeWidth: 7,
				strokeDashstyle: 'line',
				strokeColor: '#00FF00'
			}
		})
	});
	GRP.map.addLayer(GRP.layer.depot);
	
	/**
	 * Control Definition
	 */
	GRP.layer.depot.modifyControl = new OpenLayers.Control.ModifyFeature(GRP.layer.depot);
	GRP.map.addControl(GRP.layer.depot.modifyControl);
	
	GRP.layer.depot.events.on({
		'featuremodified': function(evt) {
			Ext.getCmp('wkt-depot').setValue(wkt.write(evt.feature));
		}
	});
	
	/**
	 * Store Definition
	 */
	GRP.store.depot = new GeoExt.data.FeatureStore({
		layer: GRP.layer.depot,
		fields: [
			{name: 'id', type: 'int'},
			{name: 'name', type: 'string'},
			{name: 'wkt', type: 'string'},
			{name: 'created', type: 'date', dateFormat: 'c'},
			{name: 'updated', type: 'date', dateFormat: 'c'}
		],
		proxy: new GeoExt.data.ProtocolProxy({
			protocol: new OpenLayers.Protocol.HTTP({
				url: GRP.baseURL + 'depot/find',
				format: new OpenLayers.Format.GeoJSON()
			})
		})
	});
	GRP.store.depot.setDefaultSort('name', 'asc');
	GRP.store.depot.load();
	
	/**
	 * Grid Definition
	 */
	GRP.grid.depot = new Ext.grid.GridPanel({
		title: "List of Depots",
		region: "center",
		viewConfig: {forceFit: true},
		store: GRP.store.depot,
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
				{header: "Id", dataIndex: "id", width: 10, align: 'right', hidden: true},
				{header: "Name", dataIndex: "name"},
				{header: "Created", dataIndex: "created", width: 30, 
					renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
				{header: "Updated", dataIndex: "updated", width: 30, 
					renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
			]
		}),
		sm: new GeoExt.grid.FeatureSelectionModel({
			selectControl: GRP.layer.depot.modifyControl.selectControl,
			layer: GRP.layer.depot,
			singleSelect: true,
			listeners: {
				rowselect: function(sm, row, rec) {

					rec.data.feature.geometry.transform(
						new OpenLayers.Projection("EPSG:900913"),
						GRP.projection
					);

					// Load record
					GRP.form.depot.form.loadRecord(rec);
					Ext.getCmp('wkt-depot').setValue(wkt.write(rec.data.feature));
					
					rec.data.feature.geometry.transform(
						GRP.projection,
						new OpenLayers.Projection("EPSG:900913")
					);

					// Zoom to feature
					var a = rec.data.feature.geometry.getBounds();
					GRP.map.panTo(a.getCenterLonLat());
					
					// Enable modifications
					GRP.layer.depot.modifyControl.activate();
				},
				rowdeselect: function(sm, row, rec) {
					// Reset form
					GRP.form.depot.form.reset();
					
					// Disable modification
					GRP.layer.depot.modifyControl.deactivate();
				}
			}
		})
	});
		           
	/**
	 * Form Panel
	 */
	GRP.form.depot = new Ext.form.FormPanel({
		title:'Depot Details',
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
					fieldLabel: 'Depot name',
					emptyText: 'Choose name ...',
					name: 'name',
					allowBlank: false
				},{ 
					fieldLabel: 'Location', 
					name: 'wkt',
					emptyText: 'WKT point format',
					id: 'wkt-depot',
					//readonly: true,
					allowBlank: false
				}]
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
					fieldLabel: 'Depot ID',
					name: 'id', 
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
			text: 'Save changes',
			formBind: true,
			type: 'submit',
			iconCls: 'button-save',
			handler: function(){ 
				var rec = GRP.grid.depot.getSelectionModel().getSelected();	
				if(rec.data.id > 0) {	
					GRP.form.depot.form.submit({
						url: GRP.baseURL + 'depot/update/' + rec.data.id,
						success: function(form,action){ 
							GRP.store.depot.reload(); 
							GRP.form.depot.form.reset();
						},            
						failure: function(form,action){ console.info(action); }
					});
				} 
				
			}
		},{
			text: 'Add as new',
			formBind: true,
			type: 'submit',
			iconCls: 'button-add',
			handler: function(){ 
				GRP.form.depot.form.submit({
					url: GRP.baseURL + 'depot/create',
					success: function(form,action){ 
						GRP.store.depot.reload(); 
						GRP.form.depot.form.reset();
					},            
					failure: function(form,action){ console.info(action); }
				});
			}
		},{
			text: 'Delete',
			formBind: true,
			type: 'submit',
			iconCls: 'button-delete',
			handler: function(){ 
				var rec = GRP.grid.depot.getSelectionModel().getSelected();
				if(rec.data.id > 0) {
					GRP.form.depot.form.submit({
						url: GRP.baseURL + 'depot/delete/' + rec.data.id,
						success: function(form,action){ 
							GRP.store.depot.reload(); 
							GRP.form.depot.form.reset();
						},            
						failure: function(form,action){ console.info(action); }
					});
				}
			}
		},{
			text: 'Clear',
			iconCls: 'button-clear',
			handler: function(){ 
				GRP.form.depot.form.reset(); 
				var sm = GRP.grid.depot.getSelectionModel();
				sm.clearSelections();
			}
		}]
	});
	
	/**
	 * Tabpanel Definition
	 */
	GRP.tab.depot = new Ext.Panel({
		title: 'Depot Manager', 
		iconCls: 'tab-depot',
		id: 'depot',
		region: 'center',
		border: false,
		layout:'vbox',
		layoutConfig: { align : 'stretch', pack  : 'start' },
		items: [ GRP.grid.depot, GRP.form.depot ]
	});
	
});
