<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$db = DBHandle::get_instance();
$o = array("data" => array(), "error" => "");
$advID = (int)$_GET["advID"];
if (!$advID) {
	$o["error"] = "Advertiser ID is missing";
}
else {
	switch($_GET["action"]) {
		case "mod-pdt-margin" :
			if (!$user->get_permissions()->has("m-prod--sm-partners","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($_GET["adv_margin"]) || !is_numeric($_GET["adv_margin"])) {
        $o["error"] = "Valeur de la marge non spécifiée ou incorrect.";
        break;
      }
      if (!isset($_GET["adv_price_type"]) || !is_numeric($_GET["adv_price_type"])) {
        $o["error"] = "Type de prix non spécifié ou incorrect";
        break;
      }
      $adv_margin = (float)$_GET["adv_margin"];
      $adv_price_type = (int)$_GET["adv_price_type"];
      // update advertiser's margin
      $db->query("
        UPDATE `advertisers`
        SET
          `margeRemise` = '".$adv_margin."',
          `prixPublic` = '".$adv_price_type."',
          `timestamp` = '".time()."'
        WHERE `id` = '".$advID."'", __FILE__, __LINE__);
      // update products margin
      if ($adv_price_type == 0) {
        $db->query("
          UPDATE `references_content` rc
          INNER JOIN `products` p ON rc.`idProduct` = p.`id`
          SET
            rc.`marge` = ".$adv_margin.",
            rc.`price` = ROUND(rc.`price2`/(1-(".$adv_margin."/100)),2)
          WHERE p.`idAdvertiser` = '".$advID."' AND rc.deleted = 0", __FILE__, __LINE__);
        }
      else {
        $db->query("
          UPDATE `references_content` rc
          INNER JOIN `products` p ON rc.`idProduct` = p.`id`
          SET
            rc.`marge` = ".$adv_margin.",
            rc.`price2` = ROUND(rc.`price`*(1-(".$adv_margin."/100)),2)
          WHERE p.`idAdvertiser` = '".$advID."' AND rc.deleted = 0", __FILE__, __LINE__);
        }
      $o["data"] = "Marge modifiée avec succès !";
      break;
      
    case "send-bop-codes" :
			if (!$user->get_permissions()->has("m-prod--sm-partners","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      $res = $db->query("
        SELECT a.`email`, eu.`login`, eu.`pass`
        FROM advertisers a
        INNER JOIN extranetusers eu ON a.id = eu.id
        WHERE a.id = '".$advID."'", __FILE__, __LINE__);
      if ($db->numrows($res, __FILE__, __LINE__) != 1) {
        $o["error"] = "Advertiser's ID doesn't exist";
        break;
      }
      list($email, $login, $pass) = $db->fetch($res);
      if (empty($email)) {
        $o["error"] = "Erreur fatale : ce partenaire n'a pas d'adresse email principale.";
        break;
        
      }
      $mail = new Email(array(
        "email" => $email,
        "subject" => "Vos identifiants extranet Techni-Contact",
        "headers" => "From: Service client Techni-Contact <integration@techni-contact.com>\nReply-To: Service client Techni-Contact <integration@techni-contact.com>\r\n",
        "template" => "partner-bo_partners-bop_codes",
        "data" => array(
          "FO_URL" => URL,
          "BOP_URL" => EXTRANET_URL,
          "FO_PARTNER_URL" => URL."fournisseur/".$advID.".html",
          "BOP_LOGIN" => $login,
          "BOP_PASSWORD" => $pass
        )
      ));
      if ($mail->send())
        $o["data"] = "Codes envoyés avec succès à l'adresse email : ".$email;
      else
        $o["error"] = "Erreur fatale lors de l'envoi des codes.";
      break;
			
		default:
			$o["error"] .= "Action type is missing";
			break;
	}
}
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>