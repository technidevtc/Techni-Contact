<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 28 février 2011

 Fichier : /cron/fr/amorce-notation-spool.php
 Description : script de chargement initial du spool des demandes de commentaires par produit

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$handle = DBHandle::get_instance();

// on récupère toutes les commandes passées depuis le 1er janvier, non annulées, dont le client est actif et les produits sont
// proposés par des fournisseurs
$commande_query = 'SELECT DISTINCT c.id, c.idClient, c.create_time, c.statut_traitement, c.produits, cl.nom, cl.prenom,
  cl.email, a.category FROM commandes c
  INNER JOIN commandes_advertisers ca ON c.id = ca.idCommande
  INNER JOIN advertisers a ON a.id = ca.idAdvertiser
  INNER JOIN clients cl ON cl.id = c.idClient
  WHERE c.create_time >= 1293832800 AND cl.actif != 0 AND c.statut_traitement >= 20 AND c.statut_traitement != 21 AND c.statut_traitement < 99 AND a.category = '.__ADV_CAT_SUPPLIER__;

// Pour chaque commande trouvée, je l'enregistre dans le spool
$result = & $handle->query($commande_query, __FILE__, __LINE__);
    while($rec = & $handle->fetchAssoc($result)){

    $note = new ProductNotationSpool();
    $data = array(
        'id_commande' => $rec['id'],
        'insertion_timestamp' => $rec['create_time'],
        'mail_sent' => 0
    );
    $note->setData($data);
    $note->save();
}
?>

