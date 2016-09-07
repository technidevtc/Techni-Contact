<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 18 juin 2005

 Fichier : /secure/manager/newsletter/index.php
 Description : Fichier global newsletter

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN  . 'logs.php');
require(ICLASS . 'ManagerUser.php');

$handle = DBHandle::get_instance();
$user   = & new ManagerUser($handle);

if(!$user->login())
{
    exit;
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title>Manager TECHNI-CONTACT | Newsletter</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<frameset rows="*" cols="300,*" framespacing="0" frameborder="NO" border="0">
  <frame src="left.php?<?php print(session_name() . '=' . session_id()) ?>" name="select" frameborder="no" scrolling="auto" id="select">
  <frame src="main.php?<?php print(session_name() . '=' . session_id()) ?>" name="main" frameborder="no" noresize id="main">
</frameset>
<noframes><body>

</body></noframes>

</html>
