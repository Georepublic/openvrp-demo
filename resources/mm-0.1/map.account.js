/**
 * Account Tabpanel
 */
Ext.onReady(function() {

	Ext.apply(Ext.form.VTypes, {
		password : function(val, field) {
			if (field.initialPassField) {
				var pwd = Ext.getCmp(field.initialPassField);
				return (val == pwd.getValue());
			}
			return true;
		},
		passwordText : "Invalid Password"
	});	

	/**
	 * Layer Definition
	 */
	GRP.layer.account = new OpenLayers.Layer.Vector( "Customer", {
		displayInLayerSwitcher: false
	});
	GRP.map.addLayer(GRP.layer.account);
	
	/**
	 * Store Definition
	 */
	GRP.store.account = new GeoExt.data.FeatureStore({
		layer: GRP.layer.account,
		fields: [
			{name: 'id', type: 'int'},
			{name: 'name', type: 'string'},
			{name: 'created', type: 'date', dateFormat: 'c'},
			{name: 'updated', type: 'date', dateFormat: 'c'}
		],
		proxy: new GeoExt.data.ProtocolProxy({
			protocol: new OpenLayers.Protocol.HTTP({
				url: GRP.baseURL + 'account/find',
				format: new OpenLayers.Format.GeoJSON()
			})
		})
	});
	GRP.store.account.setDefaultSort('name', 'asc');
	GRP.store.account.load();
	
	/**
	 * Grid Definition
	 */
	GRP.grid.account = new Ext.grid.GridPanel({
		title: "User accounts",
		region: "center",
		viewConfig: {forceFit: true},
		store: GRP.store.account,
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
				{header: "ID", dataIndex: "id", width: 10, align: 'right', hidden: true},
				{header: "Name", dataIndex: "name"},
				{header: "Created", dataIndex: "created", width: 30, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
				{header: "Updated", dataIndex: "updated", width: 30, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
			]
		}),
		sm: new GeoExt.grid.FeatureSelectionModel({
			//selectControl: GRP.layer.account.modifyControl.selectControl,
			layer: GRP.layer.account,
			singleSelect: true,
			listeners: {
				rowselect: function(sm, row, rec) {
					GRP.form.account.form.loadRecord(rec);
				},
				rowdeselect: function(sm, row, rec) {
					GRP.form.account.form.reset();
				}
			}
		})
	});
           
	/**
	 * Form Panels
	 */
	GRP.form.account = new Ext.form.FormPanel({
		title: 'User Account Details',
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
				labelWidth: 120,
				defaults: { 
					xtype: 'textfield',
					anchor: '90%'
				},
				items: [{
					fieldLabel: 'Username',
					emptyText: 'Choose username ...',
					name: 'name',
					allowBlank: false
				},{ 
					fieldLabel: 'New password', 
					id: 'pass', 
					name: 'pass', 
					inputType: 'password',
					minLengthText: 'Must be longer than 3 characters',
					minLength: 3 
				},{ 
					fieldLabel: 'Confirm password', 
					name: 'confirm',
					initialPassField: 'pass', 
					vtype: 'password', 
					inputType: 'password'
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
					fieldLabel: 'Account ID',
					name: 'id', 
					id: 'account-form-id'
				},{
					fieldLabel: 'Created',
					name: 'created'
				},{
					fieldLabel: 'Updated',
					name: 'updated'
				}]
			}]
		}],
		buttons: [{
			text: 'Save',
			formBind: true,
			type: 'submit',
			iconCls: 'button-save',
			handler: function(){ 
				var rec = Ext.getCmp('account-form-id').getValue();
				
				if(rec) {				
					GRP.form.account.form.submit({
						url: GRP.baseURL + 'account/update/' + rec,
						success: function(form,action){ 
							GRP.store.account.reload(); 
							GRP.form.account.form.reset();
						},            
						failure: function(form,action){}
					});
				}
				else {
					GRP.form.account.form.submit({
						url: GRP.baseURL + 'account/create',
						success: function(form,action){ 
							GRP.store.account.reload(); 
							GRP.form.account.form.reset();
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
				GRP.form.account.form.submit({
					url: GRP.baseURL + 'account/create',
					success: function(form,action){ 
						GRP.store.account.reload(); 
						GRP.form.account.form.reset();
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
				var rec = GRP.grid.account.getSelectionModel().getSelected();

				if(rec.data.id) {					
					GRP.form.account.form.submit({
						url: GRP.baseURL + 'account/delete/' + rec.data.id,
						success: function(form,action){ 
							GRP.store.account.reload(); 
							GRP.form.account.form.reset();
						},       
						failure: function(form,action){}
					});
				}
			}
		},{
			text: 'Clear',
			iconCls: 'button-clear',
			handler: function(){ 
				GRP.form.account.form.reset(); 
				var sm = GRP.grid.account.getSelectionModel();
				sm.clearSelections();
			}
		}]
	});
	
	/**
	 * Tabpanel Definition
	 */
	GRP.tab.account = new Ext.Panel({
		title: 'Customer Panel', 
		iconCls: 'tab-user',
		id: 'account',
		region: 'center',
		border: false,
		layout:'vbox',
		layoutConfig: { align : 'stretch', pack  : 'start' },
		items: [ GRP.grid.account, GRP.form.account ]
	});
	
});
