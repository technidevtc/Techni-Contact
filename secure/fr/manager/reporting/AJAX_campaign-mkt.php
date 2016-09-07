<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 21 février 2011

 Fichier : /secure/fr/manager/reporting/AJAX_campaign-mkt.php
 Description : gestion des campagnes marketing par ajax

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirÃ©, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

$o = array();

if (!$user->get_permissions()->has("m-admin--sm-campaign-mkt","r")) {
    print "Vous n'avez pas les droits adéquats pour réaliser cette opération";
    exit();
  }

function getCampaignList($args){
  // get campaignList
  $allCampaigns = MktCampaign::get($args);
  
  foreach ($allCampaigns as $campaign){
    $campaignMkt = new MktCampaign($campaign['id']);

    $campaign = $campaignMkt->getFields();
    $campaign['type'] = $campaignMkt->getTypeName($campaign['id_mkt_campaigns_type']);
    $retour[] = $campaign;
  }
  return $retour;
}

  // arguments de requete, ordre de tri et pagination
  $page	= isset($_GET['page'])	? (int)trim($_GET['page']) : 1; if ($page < 1) $page = 1;
  $formerpage     = isset($_GET['formerpage'])     ? trim($_GET['formerpage']) : '';
  $sort     = isset($_GET['sort'])     ? trim($_GET['sort']) : '';
  $lastsort = isset($_GET['lastsort']) ? trim($_GET['lastsort']) : '';
  $sortway  = isset($_GET['sortway'])  ? trim($_GET['sortway']) : '';
  $NB = !empty($_GET['NB']) && is_numeric($_GET['NB']) ? $_GET['NB'] : 30;
  define('NB', $NB);

//  if (($page-1) * NB >= $nbcmd) $page = ($nbcmd - $nbcmd%NB) / NB + 1;
//  if (($formerpage-1) * NB >= $nbcmd) $formerpage = ($nbcmd - $nbcmd%NB) / NB + 1;

  $argsQuery = array();

  if ($sort == $lastsort && $sort != '')
  {
          if ($formerpage == $page) $sortway = $sortway == 'asc' ? 'desc' : 'asc';
          else $sortway = ($sortway == 'asc' ? 'asc' : 'desc');
  }
  else $sortway = 'asc';

  switch ($sort)
  {

          case 'date'   : $sortName = "timestamp"; break;
          case 'id'    : $sortName = "id"; break;
          case 'type' : $sortName = "id_mkt_campaigns_type"; break;
          case 'name'    : $sortName = "nom"; break;

          default : $sortway == ('asc' ? 'desc' : 'asc'); $sort = 'date';
  }

  $argsQuery[] = " order by ".$sortName.' '.$sortway;
  $lastsort = $sort;
  $formerpage = $page;
  $argsQuery[] = " limit " . (($page-1)*NB) . "," . NB;
  // arguments de requete, ordre de tri et pagination

$nbcmd = MktCampaign::getCount();

if(!empty ($_GET['testID']) && preg_match("/^[1-9]{1}[0-9]{0,9}$/", $_GET['testID'])){

  if (!$user->get_permissions()->has("m-admin--sm-campaign-mkt","r")) {
    print "Vous n'avez pas les droits adéquats pour réaliser cette opération";
    exit();
  }
  $campaignMkt = new MktCampaign($_GET['testID']);
  
  if($campaignMkt->exists){
    
    $retour = $campaignMkt->getFields();
  }else
    $retour = array('campagne inexistante');

}elseif($_POST['supprime_campagne'] == 1 && preg_match("/^[1-9]{1}[0-9]{0,9}$/", $_POST['campaignID'] )){

  if (!$user->get_permissions()->has("m-admin--sm-campaign-mkt","d")) {
    print "Vous n'avez pas les droits adéquats pour réaliser cette opération";
    exit();
  }

  MktCampaign::delete($_POST['campaignID']);

  $retour = getCampaignList($argsQuery);

}  elseif ($_GET['searchCampaign'] == 1 && !empty ($_GET['item'])) {

  if(preg_match("/^[1-9]{1}[0-9]{0,9}$/", $_GET['item'] ))
    $arg = 'id='.$_GET['item'];
  else
    $arg = "nom like '%".$_GET['item']."%'";

  $argsQuery[] = $arg;

    $nbcmd = MktCampaign::getCount(null, $arg);
    $campaigns = MktCampaign::get ($argsQuery);

    foreach ($campaigns as $campaign){
      $campaignMkt = new MktCampaign($campaign['id']);

      $campaign = $campaignMkt->getFields();
      $campaign['type'] = $campaignMkt->getTypeName($campaign['id_mkt_campaigns_type']);
      $retour[] = $campaign;
    }
    
}else {
  $retour = getCampaignList($argsQuery);
}

if(!$errorstring){

  if(empty ($retour) || count($retour) == 0)
    $o['reponses'] = 'Liste vide';
  else
    $o['reponses'] = $retour;

}else
  $o['error'] = $errorstring;

$lastpage = $nbcmd > NB ? ceil($nbcmd/NB) : 1;

$o['pagination'] = array('lastsort' => $lastsort , 'sort' =>  $sort, 'sortway' =>  $sortway, 'formerpage' => $formerpage, 'lastpage' => $lastpage , 'page' => $page, 'NB' => NB, 'nbcmd' => $nbcmd);

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>
