<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

 	<title><?=$title?></title>	
</head>

<body>
<?php echo form_open('login/process') . "\n"; ?>
    <?php echo form_fieldset('Login') . "\n"; ?>

        <?php echo $this->session->flashdata('message'); ?>

        <p><label for="username">Username: </label><?php echo form_input($username); ?></p>
        <p><label for="password">Password: </label><?php echo form_password($password); ?></p>
        <p><?php echo form_submit('login', 'Login'); ?></p>
        
    <?php echo form_fieldset_close(); ?>
<?php echo form_close(); ?>
</body>
</html>
