<?php
$this->content .= <<< EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv=content-type content="text/html; charset=utf-8"/>
	<link href="{$constant['URL']}ressources/styles.css" rel="stylesheet" type="text/css"/>
	<link href="{$constant['URL']}ressources/style_email.css" rel="stylesheet" type="text/css"/>
</head>
<body>
	<div class="mail_header">
		<a title="page d'accueil" href="{$constant['URL']}"><img id=logo alt="logo techni contact" src="{$constant['URL']}ressources/logo.gif" nosend="1"/></a>
		&nbsp;&nbsp;&nbsp;&nbsp;<b>{$title}</b>
	</div>
EOF;

?>