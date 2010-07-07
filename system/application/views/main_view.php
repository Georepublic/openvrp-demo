<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?=$title?></title>	
</head>

<body>
	<h1><?=$heading?></h1>
	
	<ol>
	<?php foreach($todo as $item): ?>
	
		<li><?=$item?></li>
		
	<?php endforeach; ?>
	</ol>
</body>
</html>
