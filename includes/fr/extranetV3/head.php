<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 15 juillet 2005

 Fichier : /includes/extranet/head.php
 Description : Fichier générique entête extranet

/=================================================================*/

require(EXTRANET."logs.php");
require(ICLASS."ExtranetUser.php");
require(ADMIN."statut.php");

$handle = DBHandle::get_instance();

	//For the auto connect with the autoid
	$login = $pass = "";
	require(EXTRANET.'head_autoconnect_uid.php');

$user = new ExtranetUser($handle);

//Code commented on 18/11/2014 to include the auto uid on the old extranet
/*$login = $pass = "";
if (isset($_GET["uid"]) && preg_match("/^[a-zA-Z0-9]{30,32}$/", $_GET["uid"])) {
	
	$result = & $handle->query("select eu.login, eu.pass from extranetusers eu, advertisers a where eu.webpass = '" . $_GET["uid"] . "' and a.from_web = 1 and a.id = eu.id", __FILE__, __LINE__);
	if ($handle->numrows($result, __FILE__, __LINE__) == 1)
	{
		list($login, $pass) = $handle->fetch($result);
	}
}*/



//Condition add on 13/11/2014 15h32m
//To auto redirect the user when hi's category changed from 1 to (0, 2, 3, 4 or 5)
if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)!=0){
	header('Location: '.EXTRANET_URL);
}
//End modification code !
		
		
if(!$user->login($login, $pass) || !$user->active) {
    header("Location: ".EXTRANET_URL."login.html");
    exit();
}

$sid = session_name()."=".session_id();

if (WHERE != WHERE_COMMANDS && WHERE != WHERE_INVOICES)
  require(EXTRANET."head2.php");
