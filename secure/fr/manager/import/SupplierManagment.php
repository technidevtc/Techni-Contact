<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 28 janvier 2011

 Fichier : /secure/manager/import/SupplierManagment.php
 Description : Fichier mécanisme de lancement d'import ou d'annulation d'import des prix fournisseurs AJAX

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

if(isset($_GET['action']) && isset($_GET['idImport']) && is_numeric($_GET['idImport'])){

  require('_ClassImport.php');
  
  if($_GET['action'] == 'importe'){

      $imp = & new Import($handle, $_GET['idImport']);
      
      if (!$imp->exist) exit();
      else{

        if($imp->status == __I_VF__){
            if($imp->finalizeImport())
                print '{"result": ["ok"] }';
              else
                print '{"result": ["error"] }';
        }else
          print '{"result": ["error"] }';

      }
  }elseif($_GET['action'] == 'annule'){

    $imp = & new Import($handle, $_GET['idImport']);

      if (!$imp->exist) exit();
      else{

        if($imp->status == __I_V__){
            if($imp->cancelImport())
                print '{"result": ["ok"] }';
              else
                print '{"result": ["error"] }';
        }else
          print '{"result": ["error"] }';
      }

  }
}
?>
