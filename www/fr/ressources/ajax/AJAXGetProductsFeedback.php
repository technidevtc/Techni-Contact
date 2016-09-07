<?php

/**
 * Ajax request to get a product notation and feedback
 *
 * 17/4/2012 OD pour Hook-Network
 */
require substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), "/", stripos(dirname(__FILE__), "technico")+1) + 1) . "config.php";
 
$handle = DBHandle::get_instance();

$idProduct = $_GET["idProduct"];
if (!is_numeric($idProduct)) {
	$o["error"] = "Id product incorrect";
}


if (isset($o["error"])) {
	//mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
	print json_encode($o);
	exit();
}else{

$notations = ProductNotation::get('id_product = '.$idProduct, 'inactive = 0');

if(!empty ($notations)){
      $a=0;
      foreach ($notations as $i => $notation){
        $notation_maker = new CustomerUser($handle, $notation['id_client']);
        $notations['list'][$i] = $notation;
        if(!empty ($notation_maker) && count($notation_maker) == 1){
          $notations['list'][$i]['prenom'] = $notation['anonymous'] == true ? 'Client' : $notation_maker->prenom;
          $notations['list'][$i]['ville'] = $notation_maker->ville;
        }  else {
          $notations['list'][$i]['prenom'] = 'Prénom non défini';
          $notations['list'][$i]['ville'] = '';
        }
        $sumNote += $notation['note'];

        $mois = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
        $date = getdate($notations['list'][$i]['timestamp']);
        $notations['list'][$i]['date'] = date('d' ,$notations['list'][$i]['timestamp']).' '.$mois[$date['mon']-1].' '.date('Y' ,$notations['list'][$i]['timestamp']);
        unset($notations[$i]);
        $a++;
      }
      $notations['average_note'] = round($sumNote/$a);
      $notations['nb_comments'] = $a;
      //pp($notations);
    }else{
      $o["error"] = "Aucun commentaire disponible";
      //mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
	print json_encode($o);
	exit();
    }

//mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $notations);
print json_encode($notations);
exit();

}