<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 15 juillet 2005
 Mise à jour le : 29 mai 2005
 
 Fichier : /secure/extranet/index.html
 Description : Fichier accueil extranet

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(EXTRANET  . 'logs.php');
require(ICLASS    . 'ExtranetUser.php');

$handle = DBHandle::get_instance();
$user   = & new ExtranetUser($handle);

if(!$user->login() || !$user->active)
{
    header('Location: ' . EXTRANET_URL . 'login.html');
    exit();
}

$sid = session_name() . '=' . session_id();

if ($user->parent != '61049')
{
    header('Location: ' . EXTRANET_URL . 'index.html?' . $sid);
    exit();
}

if (!isset($_POST['idCommande']) || !preg_match('/^[1-9]{1}[0-9]{0,8}$/', $_POST['idCommande']))
{
    header('Location: ' . EXTRANET_URL . 'commandes.html?' . $sid);
    exit();
}

require(ICLASS . 'Command.php');
$commande = & new Command($handle);
$commande->setID($_POST['idCommande']);

if (!$commande->advertiserIsInCommand($user->id))
{
	header('Location: ' . EXTRANET_URL . 'commandes.html?' . $sid);
	exit();
}

$cmdInfos = & $commande->loadInfos();

if ($cmdInfos['status_traitement'] < 10)
{
	header('Location: ' . EXTRANET_URL . 'commandes.html?' . $sid);
	exit();
}

if ($cmdInfos['status_traitement'] >= 20)
{
	header('Location: ' . EXTRANET_URL . 'commande.html?idCommande=' . $_POST['idCommande'] . '&' . $sid);
	exit();
}

$commande->set_status_traitement(20);
header('Location: ' . EXTRANET_URL . 'commande.html?idCommande=' . $_POST['idCommande'] . '&' . $sid);


?>

