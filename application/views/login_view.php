<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$language?>" lang="<?=$language?>">
<head>
 	<title><?=$title?></title>	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="Content-Language" content="<?=$language?>"/>

	<script src="<?=base_url();?>resources/ext-3.2.1/adapter/ext/ext-base.js" type="text/javascript"></script> 
	<script src="<?=base_url();?>resources/ext-3.2.1/ext-all.js"  type="text/javascript"></script> 
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>resources/ext-3.2.1/resources/css/ext-all.css" /> 
</head>

<body>
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
	        width: 300,
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
				name: 'login', 
				/*iconCls: 'button-icon-door-in',*/
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
		
		var win = new Ext.Window({ title:"Login", items: [login] });
		win.show();

		Ext.getCmp('userField').focus();
	});
	</script> 
</body>
</html>
