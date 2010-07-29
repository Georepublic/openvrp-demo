<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>OpenVRP Manager 0.1</title>

	<link rel="shortcut icon" href="<?=base_url();?>resources/images/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/resources/css/xtheme-gray.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/main.css" />
</head>

<body>
	<div id="loading">
	    <div class="loading-indicator">
	    	<img src="<?=base_url();?>resources/images/loading-icon.gif"/>
			<span id="loading-title">
				<script type="text/javascript">
					document.write("OpenVRP Manager 0.1");
				</script>
			</span><br/>
			<span id="loading-msg">
				<script type="text/javascript">
					document.write("OpenVRP CSS ...");
				</script>
			</span>
		</div>
	</div>		
	
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Loading ExtJS ..."</script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="<?=base_url();?>resources/ext-3.2.1/ext-all.js"></script>
	
	<script type="text/javascript">document.getElementById('loading-msg').innerHTML = "Initialize ..."</script>
	<script type="text/javascript">
	
	Ext.BLANK_IMAGE_URL = '<?=base_url();?>resources/ext-3.2.1/resources/images/default/s.gif';

	Ext.onReady(function() {
	
		Ext.QuickTips.init();
		Ext.form.Field.prototype.msgTarget = 'side';
	
		var login = new Ext.FormPanel({ 
		    monitorValid: true,
			standardSubmit: true,
		    onSubmit: Ext.emptyFn,
		    url: 'login/process', 
		    frame: true, 
		    width: 350,
		    autoHeight:true,
		    labelWidth: 80,
			bodyStyle: 'padding:15px;',
		    items:[{
				xtype: 'panel',
				height: 40,
				html: "<?=$this->session->flashdata('message');?>" || "<b>Please fill in your username and password und click the login button.</b>"
			},{ 
				xtype: 'textfield',
				fieldLabel: "Username", 
				id: 'userField',
				name: 'username', 
				allowBlank: false 
			},{ 
				xtype: 'textfield',
				fieldLabel: "Passwword", 
				name: 'password', 
				inputType: 'password', 
				allowBlank: false 
			}],
		    buttons:[{ 
				text: "Login",
				formBind: true,	
				iconCls: 'button-login',
				handler: function(){ 
					login.getForm().submit(); 
				}
			}], 
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(){ 
					login.getForm().submit(); 
				}
			}]
		});
	
		var win = new Ext.Window({ 
			title:"OpenVRP Manager 0.1 - Login", 
			modal: false,
			closable: false,
			resizable: false,
			draggable: false,
			listeners : {
				afterlayout: function() {
					Ext.get('loading').hide();
				}
			},
			items: [login] 
		});
		win.show();

		Ext.getCmp('userField').focus();
	});
	
	</script> 
</body>
</html>
