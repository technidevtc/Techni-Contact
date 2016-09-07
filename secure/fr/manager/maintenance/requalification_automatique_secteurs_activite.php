<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 02 Décembre 2011

 Fichier : /manager/maintenance/requalification_automatique_secteurs_activite.php
 Description : requalification automatique des secteurs d'activités, parcours les tables données et affecte
 * secteur d'activité
 * surqualification du secteur d'activité
 * Naf
 en fonction de la raison sociale

/=================================================================*/
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();
$dateDebut = date('d-m-Y H:i:s');
$tables_a_parcourir = array(
    'clients' => array(
        'nom_classe' => 'CustomerUser',
        'champ_raison_soc' => 'societe',
        'champ_sect_act' => 'secteur_activite',
        'champ_sect_qual' => 'secteur_qualifie',
        'champ_naf' => 'code_naf'
        )
    );
    
    foreach ($tables_a_parcourir as $nom_table => $nom_champs){

    
$ActivitySector = Doctrine_Core::getTable('ActivitySectorSurqualification');
          $ActivitySector->batchUpdateIndex();
      $query = 'SELECT id, '.$nom_champs['champ_raison_soc'].' FROM '.$nom_table;
      $res = $db->query($query);
      while($row = $db->fetch($res)){
        if(!empty($row[1])){

          $terms = preg_replace('/ de /', ' ', $row[1]);
          $terms = explode(' ', $terms);
          
          $array_results = array();
          foreach($terms as $term){
            $term = Utils::toDashAz09(utf8_decode($term));

            if($result = $ActivitySector->search($term)){
              $q = Doctrine_Query::create()
                ->from('ActivitySector as')
                ->leftJoin('as.Surqualifications ass')
                ->where('ass.id = ?', $result[0]['id']);

              $array_results[$result[0]['id']] = $result[0]['id'];
              $sector = $q->fetchArray();

//              if(count($results) > 1)
//                echo 'Réponses multiples, vérifier les paramètres de surqualification des secteurs d\'activités';
              $results[] = $result;
            }
          }

          if(count($array_results) == 1){
            $secteur_activite = $sector[0]['sector'];
            $secteur_qualifie = $sector[0]['Surqualifications'][0]['qualification'];
            $code_naf = $sector[0]['Surqualifications'][0]['naf'];

            $query_update = 'UPDATE `'.$nom_table.'` SET `'.
            $nom_champs['champ_sect_act'].'` = \''.$db->escape($secteur_activite).'\', `'.
            $nom_champs['champ_sect_qual'].'` = \''.$db->escape($secteur_qualifie).'\', `'.
            $nom_champs['champ_naf'].'` = \''.$db->escape($code_naf).'\''.
            ' WHERE `id` = '.$db->escape($row[0]);

            $res2 = $db->query($query_update);

          }
//          elseif(count($array_results)> 1)
//            echo 'Réponses multiples';   UPDATE `technico-test`.`clients` SET `secteur_qualifie` = 'surqualif téstaiueauie' WHERE `clients`.`id` =793217880;


        }
//        echo PHP_EOL;
      }
      echo 'fin du traitement'.PHP_EOL;
      echo 'date debut : '.$dateDebut.PHP_EOL ;
      echo 'date fin : '.date('d-m-Y H:i:s').PHP_EOL;
    }

?>
