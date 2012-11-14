/**
 * Depot Tabpanel
 */
Ext.onReady(function() {

	/**
	 * Layer Definition
	 */
	GRP.layer.order = new OpenLayers.Layer.Vector( "Orders", {
		styleMap: new OpenLayers.StyleMap({
			'default': OpenLayers.Util.applyDefaults({
				strokeWidth: 4,
				strokeColor: '#E37A09'
				}, OpenLayers.Feature.Vector.style["default"]), 
			'select': {
				strokeColor: '#E37A09'
			}
		})
	});
	GRP.map.addLayer(GRP.layer.order);
	
	/**
	GRP.layer.routes = new OpenLayers.Layer.Vector(" Route Layer",{
		styleMap: new OpenLayers.StyleMap({
			'default': OpenLayers.Util.applyDefaults({
				strokeWidth: 4,
				strokeColor: '#E37A09'
				}, OpenLayers.Feature.Vector.style["default"]), 
			'select': {
				strokeColor: '#E37A09'
			}
		})
	});
	GRP.map.addLayer(GRP.layer.routes);
	 */

	/**
	 * Control Definition
	 */
	GRP.layer.order.modifyControl = new OpenLayers.Control.ModifyFeature(GRP.layer.order,{virtualStyle: 'edit'});
	GRP.map.addControl(GRP.layer.order.modifyControl);

	GRP.layer.order.events.on({
		'featuremodified': function(evt) {
		
			evt.feature.geometry.transform(
				new OpenLayers.Projection("EPSG:900913"),
				GRP.projection
			);

			var points = evt.feature.geometry.getVertices();
			Ext.getCmp('wkt-order-start').setValue('POINT(' + points[0].x + ' ' + points[0].y + ')');
			Ext.getCmp('wkt-order-end').setValue('POINT(' + points[1].x + ' ' + points[1].y + ')');

			evt.feature.geometry.transform(
				GRP.projection,
				new OpenLayers.Projection("EPSG:900913")
			);
		}
	});
	
	/**
	 * Store "order"
	 */
	GRP.store.order = new GeoExt.data.FeatureStore({
		layer: GRP.layer.order,
		fields: [
			{name: 'order_id', type: 'int'},
			{name: 'name', type: 'string'},
			{name: 'created', type: 'date', dateFormat: 'c'},
			{name: 'updated', type: 'date', dateFormat: 'c'},
			{name: 'size', type: 'float'},
			{name: 'pickup', type: 'date', dateFormat: 'c'},
			{name: 'pick_after', type: 'float'},
			{name: 'pick_before', type: 'float'},
			{name: 'dropoff', type: 'date', dateFormat: 'c'},
			{name: 'drop_after', type: 'float'},
			{name: 'drop_before', type: 'float'},
			{name: 'start', type: 'string', mapping: 'start.properties.wkt'},
			{name: 'goal', type: 'string', mapping: 'goal.properties.wkt'}
		],
		proxy: new GeoExt.data.ProtocolProxy({
			protocol: new OpenLayers.Protocol.HTTP({
				url: GRP.baseURL + 'order/find',
				format: new OpenLayers.Format.GeoJSON()
			})
		})
	});
	GRP.store.order.setDefaultSort('name', 'asc');
	GRP.store.order.load();
	
	/**
	 * Store "route"
	GRP.store.route = new GeoExt.data.FeatureStore({
		layer: GRP.layer.routes,
		root: 'features',
		fields: [
			{ name: 'order_id', type: 'int' }
		],
		proxy: new GeoExt.data.ProtocolProxy({
			protocol: new OpenLayers.Protocol.HTTP({
				url: GRP.ProxyURL,
				format: new OpenLayers.Format.GeoJSON()
			})
		}),
		listeners: {
			beforeload: function(store,options) {
				Ext.MessageBox.show({
					title: 'Route request',
					msg: 'Waiting for route ...',
					progressText: 'Connecting ...',
					width: 300,
					wait: true,
					waitConfig: {interval:200},
					//icon: 'ext-mb-download', //custom class in msg-box.html
					animEl: 'wait-capabilities'
				});
			},
			load: function(store, records, options){
				Ext.each(records, function(item){
					item.set('order_id', 100);
					item.commit();
				});
				
				Ext.MessageBox.hide();
			}
		}
	});
	 */


	/**
	 * Grid Definition
	 */
	GRP.grid.order = new Ext.grid.GridPanel({
		title: "List of Orders",
		region: "center",
		viewConfig: {forceFit: true},
		store: GRP.store.order,
		flex: 1,
		loadMask: true,
		stripeRows: true,
		cm: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true,
				align: 'right'
			},
			columns: [
				{header: "Id", dataIndex: "order_id", width: 10, hidden: true}, 
				{header: "Name", dataIndex: "name", width: 150, align: 'left'},
				{header: "Created", dataIndex: "created", hidden: true, renderer: Ext.util.Format.dateRenderer('Y/m/d H:i:s')},
				{header: "Updated", dataIndex: "updated", hidden: true, renderer: Ext.util.Format.dateRenderer('Y/m/d H:i:s')},
				{header: "Capacity", dataIndex: "size"},
				{header: "Pickup", dataIndex: "pickup", width: 140, renderer: Ext.util.Format.dateRenderer('Y/m/d H:i')},
				{header: "Pickup <b>-</b>", dataIndex: "pick_before"},
				{header: "Pickup <b>+</b>", dataIndex: "pick_after"},
				{header: "Dropoff", dataIndex: "dropoff", width: 140, renderer: Ext.util.Format.dateRenderer('Y/m/d H:i')},
				{header: "Dropoff <b>-</b>", dataIndex: "drop_before"},
				{header: "Dropoff <b>+</b>", dataIndex: "drop_after"}
			]
		}),
		sm: new GeoExt.grid.FeatureSelectionModel({
			selectControl: GRP.layer.order.modifyControl.selectControl,
			layer: GRP.layer.order,
			singleSelect: true,
			listeners: {
				rowselect: function(sm, row, rec) {
					// Load record
					GRP.form.order.form.loadRecord(rec);
					
					rec.data.feature.geometry.transform(
						new OpenLayers.Projection("EPSG:900913"),
						GRP.projection
					);

					var points = rec.data.feature.geometry.getVertices();

					Ext.getCmp('wkt-order-start').setValue('POINT(' + points[0].x + ' ' + points[0].y + ')');
					Ext.getCmp('wkt-order-end').setValue('POINT(' + points[1].x + ' ' + points[1].y + ')');

					rec.data.feature.geometry.transform(
						GRP.projection,
						new OpenLayers.Projection("EPSG:900913")
					);

					// Set slider
					Ext.getCmp('pickup_slider').setValue(0,rec.data.pick_before);
					Ext.getCmp('pickup_slider').setValue(1,rec.data.pick_after);
					Ext.getCmp('dropoff_slider').setValue(0,rec.data.drop_before);
					Ext.getCmp('dropoff_slider').setValue(1,rec.data.drop_after);
					
					// Zoom to feature
					var a = rec.data.feature.geometry.getBounds();
					GRP.map.panTo(a.getCenterLonLat());
					
					// Enable modifications
					GRP.layer.order.modifyControl.activate();
				},
				rowdeselect: function(sm, row, rec) {
					// Reset slider
					Ext.getCmp('pickup_slider').setValue(0,-3);
					Ext.getCmp('pickup_slider').setValue(1,3);
					Ext.getCmp('dropoff_slider').setValue(0,-3);
					Ext.getCmp('dropoff_slider').setValue(1,3);
					
					// Reset form
					GRP.form.order.form.reset();
					
					// Disable modification
					GRP.layer.order.modifyControl.deactivate();
				}
			}
		})
	});
		           
	/**
	 * Form Panel
	 */
	GRP.form.order = new Ext.form.FormPanel({
		title:'Order Details',
		autoHeight: true,
		border: true,
		frame: true,
		method: 'POST',
		monitorValid: true,
		labelWidth: 80,
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
					fieldLabel: 'Order name',
					emptyText: 'Choose name ...',
					name: 'name',
					allowBlank: false
				},{
					fieldLabel: 'Capacity',
					emptyText: 'Define capacity ...',
					xtype: 'numberfield',
					name: 'size',
					allowBlank: false
				},{
					xtype: 'combo',
					mode: 'local',
					store: GRP.store.account,
					fieldLabel: 'Customer',
					emptyText: '[disabled] Select customer ... ',
					displayField: 'name',
					hiddenName: 'account_id', 
					valueField: 'id',						
					allowBlank: true,
					disabled: true,
					editable: false,
					forceSelection: true,
					triggerAction: 'all',
					selectOnFocus: true
				},{ 
					fieldLabel: '<b><u>Departure</u></b>', 
					xtype: 'xdatetime',
					name: 'pickup',
					timeFormat: 'H:i:s',
					timeConfig: {
						altFormats: 'H:i:s',
						allowBlank:true    
					},
					dateFormat: 'Y-m-d',
					dateConfig: {
						altFormats: 'Y-m-d',
						allowBlank:true    
					}
				},{
					fieldLabel: 'Time window',
					xtype: 'slider',
					name: 'pickup_diff',
					id: 'pickup_slider',
					increment: 0.5,
					decimalPrecision: 1,
					minValue: -8,
					maxValue: 8,
					values  : [-3, 3],
					plugins: new Ext.slider.Tip({
						getText: function(thumb){
							return String.format('{0} h', thumb.value);
						}
					})
				},{
					fieldLabel: 'Start Point',
					name: 'wkt_start',
					emptyText: 'WKT point format',
					id: 'wkt-order-start',
					allowBlank: false,
					listeners: {
						change: function(field, newval, oldval){
							getRoute();			        
						}
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
					fieldLabel: 'Order ID',
					format: 'Y-m-d H:i:s',
					name: 'order_id',
					disabled: true 
				},{
					fieldLabel: 'Created',
					xtype: 'datefield',
					format: 'Y-m-d H:i:s',
					name: 'created',
					disabled: true
				},{
					fieldLabel: 'Updated',
					xtype: 'datefield',
					name: 'updated',
					disabled: true
				},{ 
					fieldLabel: '<b><u>Arrival</u></b>', 
					xtype: 'xdatetime',
					name: 'dropoff',
					timeFormat: 'H:i:s',
					timeConfig: {
						altFormats: 'H:i:s',
						allowBlank: true
					},
					dateFormat: 'Y-m-d',
					dateConfig: {
						altFormats: 'Y-m-d',
						allowBlank: true
					}
				},{
					fieldLabel: 'Time window',
					xtype: 'slider',
					name: 'dropoff_diff',
					id: 'dropoff_slider',
					increment: 0.5,
					decimalPrecision: 1,
					minValue: -8,
					maxValue: 8,
					values  : [-3, 3],
					plugins: new Ext.slider.Tip({
						getText: function(thumb){
							return String.format('{0} h', thumb.value);
						}
					})
				},{
					fieldLabel: 'End Point',
					name: 'wkt_goal',
					emptyText: 'WKT point format',
					id: 'wkt-order-end',
					allowBlank: false,
					listeners: {
						change: function(field, newval, oldval){
							getRoute();			        
						}
					}
				}]
			}]
		}],
		buttons: [{
			text: 'Save',
			formBind: true,
			type: 'submit',
			iconCls: 'button-save',
			handler: function(){ 
				var rec = GRP.grid.order.getSelectionModel().getSelected();
				
				if(rec.data.order_id > 0) {
					
					GRP.form.order.form.submit({
						url: GRP.baseURL + 'order/update/' + rec.data.order_id,
						params: {
							'pick_before': Math.abs(Ext.getCmp('pickup_slider').getValues()[0]) + ' hours',
							'pick_after':  Math.abs(Ext.getCmp('pickup_slider').getValues()[1]) + ' hours',
							'drop_before': Math.abs(Ext.getCmp('dropoff_slider').getValues()[0]) + ' hours',
							'drop_after':  Math.abs(Ext.getCmp('dropoff_slider').getValues()[1]) + ' hours'
						},
						success: function(form,action){ 
							GRP.store.order.reload(); 
							GRP.form.order.form.reset();
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
				GRP.form.order.form.submit({
					url: GRP.baseURL + 'order/create',
						params: {
							'pick_before': Math.abs(Ext.getCmp('pickup_slider').getValues()[0]) + ' hours',
							'pick_after':  Math.abs(Ext.getCmp('pickup_slider').getValues()[1]) + ' hours',
							'drop_before': Math.abs(Ext.getCmp('dropoff_slider').getValues()[0]) + ' hours',
							'drop_after':  Math.abs(Ext.getCmp('dropoff_slider').getValues()[1]) + ' hours'
						},
					success: function(form,action){ 
						GRP.store.order.reload(); 
						GRP.form.order.form.reset();
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
				var rec = GRP.grid.order.getSelectionModel().getSelected();

				if(rec.data.order_id > 0) {
					
					GRP.form.order.form.submit({
						url: GRP.baseURL + 'order/delete/' + rec.data.order_id,
						success: function(form,action){ 
							GRP.store.order.reload(); 
							GRP.form.order.form.reset();
						},            
						failure: function(form,action){}
					});
				}
			}
		},{
			text: 'Clear',
			iconCls: 'button-clear',
			handler: function(){ 
				GRP.form.order.form.reset(); 
				var sm = GRP.grid.order.getSelectionModel();
				sm.clearSelections();
			}
		}]
	});

	/**
	 * Tabpanel Definition
	 */
	GRP.tab.order = new Ext.Panel({
		title: 'Order Viewer', 
		iconCls: 'tab-order',
		id: 'order',
		region: 'center',
		border: false,
		layout:'vbox',
		layoutConfig: { align : 'stretch', pack  : 'start' },
		items: [ GRP.grid.order, GRP.form.order ]
	});
});
