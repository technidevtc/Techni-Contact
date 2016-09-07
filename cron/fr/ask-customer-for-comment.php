<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 28 février 2011

 Fichier : /cron/fr/ask-customer-for-comment.php
 Description : cron d'envoi de mails de demande de commentaire pour produit

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$handle = $handle2 = DBHandle::get_instance();
$_40jours = (86400 * 40);

// à partir des commandes insérées dans le spool de notation des commandes, il y a plus de 40 jours et pour lesquelles on n'a pas envoyé de mail de demande de commentaire
$spool_notation = ProductNotationSpool::get('insertion_timestamp <= '.(time()-$_40jours), 'mail_sent = 0');

foreach($spool_notation as $commande_notation){
  // on prend la commande si les produits sont proposés par des fournisseurs, non annulées, dont de client est actif,
  // au moins en état 'En cours de traitement'
  /*$commande_query = 'SELECT DISTINCT c.id, c.idClient, c.create_time, c.statut_traitement, c.produits, cl.nom, cl.prenom,
    cl.login, cl.email, a.category FROM commandes c
    INNER JOIN commandes_advertisers ca ON c.id = ca.idCommande
    INNER JOIN advertisers a ON a.id = ca.idAdvertiser
    INNER JOIN clients cl ON cl.id = c.idClient
    WHERE c.id = '.$commande_notation['id_commande'].' AND cl.actif != 0 AND c.statut_traitement >= 20 AND c.statut_traitement < 99 AND c.statut_traitement != 21 AND a.category = '.__ADV_CAT_SUPPLIER__;*/
  // new request since commandes table is replaced by order table
    $commande_query = "SELECT DISTINCT c.id, c.client_id, FROM_UNIXTIME(c.created, '%d/%m/%Y') as create_time, c.processing_status, cl.nom, cl.prenom,
    cl.login, cl.email FROM `order` c
    INNER JOIN clients cl ON cl.id = c.client_id
    WHERE c.id = ".$commande_notation['id_commande']." AND cl.actif != 0 AND c.processing_status >= 20 AND c.processing_status < 99 AND c.processing_status != 21";
 
  // Si je trouve la commande, je traite les données, j'envoie le mail, je renseigne la table des notations produits et j'indique l'envoi de l'email
  if(($result = & $handle->query($commande_query, __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1){
    $rec = & $handle->fetchAssoc($result);
    /*$pdt = unserialize($rec['produits']);
    array_shift($pdt); // on supprime la 1ère ligne du tableau qui correspond aux en-tetes*/
    $pdtRes = $handle2->query('SELECT * FROM `order_line` WHERE `order_id` = '.$commande_notation['id_commande'], __FILE__, __LINE__);
     while($row = $handle2->fetchAssoc($pdtRes))
      $pdt[] = $row;
    
    if(!empty ($pdt)){
      $products_list = '<ul style="list-style-type: none">';

      $nbProducts = 0;
      foreach($pdt as $produit){
        $res3 = $handle2->query('SELECT p.id, pf.idFamily
        FROM products p
        INNER JOIN products_fr pfr ON p.id = pfr.id
        AND pfr.active =1
        INNER JOIN products_families pf ON pf.idProduct = p.id
        INNER JOIN advertisers a ON p.idAdvertiser = a.id
        AND a.actif =1 AND a.category = '.__ADV_CAT_SUPPLIER__.'
        LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.vpc = 1 AND rc.deleted = 0
        AND rc.classement =1
        WHERE p.id ='.$produit['pdt_id']);

        // test si produit valide
        
        $rowProd = $handle2->fetchAssoc($res3);
        if ($handle2->numrows($res3) == 1){
          // Si ce produit n'est déjà enregistré en notation pour ce client
          $product_note_list = ProductNotation::get("id_product = ". $produit['pdt_id'], "id_client = ".$rec['client_id']);
          
          if(count($product_note_list[0]) == 0){
            $pic_url = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$produit['pdt_id']."-1".".jpg") ? PRODUCTS_IMAGE_URL."thumb_small/".$produit['pdt_id']."-1".".jpg" : PRODUCTS_IMAGE_URL."no-pic-thumb_big.gif";;
            $token = md5('notationproduit'.$rec['client_id'].$produit['pdt_id']);
            $products_list .= '<li><a href="'.URL.'notation-produit.html?pdtID='.$produit['pdt_id'].'&catID='.$rowProd['idFamily'].'&c='.$rec['client_id'].'&token='.$token.'"><img src='.$pic_url.' alt="'.$produit['desc'].'" /><br />Notez ce produit</a></li>';

            //reste à renseigner products_notation
            $product_note = new ProductNotation();
            $product_note_data = array(
              "id_product" => $produit['pdt_id'],
              "id_client" => $rec['client_id'],
              "id_commande" => $rec['id'],
              "token" => $token,
              "inactive" => 1
            );
            $product_note->setData($product_note_data);
            $product_note->save();

            $nbProducts++;
          }
        }
      }
      $products_list .= '</ul>';
    }
    
    // customer mail
    if($nbProducts){
      $customerMail = $rec['email'] != '' ? $rec['email'] : $rec['login'];

        $email_data = array(
          "email" => $customerMail,
          "subject" => "Notez les produits que vous avez commandés",
          "headers" => "From: Service client Techni-Contact.com<sav@techni-contact.com>\nReply-To: sav@techni-contact.com<sav@techni-contact.com>\r\n",
          "template" => "user-fo_product-ask_notation",
          "data" => array(
            'CUSTOMER_NAMES' => $rec['prenom'].' '.$rec['nom'],
            'COMMAND_DATE' => $rec['create_time'],
            'PRODUCTS_TABLE' => $products_list
          )
        );

        $mail = new Email($email_data);
        $mail->send();


      // je sors la commande du spool
      $note = new ProductNotationSpool($commande_notation['id']);
      $note->mail_sent = 1;
      $note->save();
      // envoi d'un mail toute les 3 secondes
      sleep (3);
    }else
      ProductNotationSpool::delete($commande_notation['id']);

  }else{
    // je ne trouve pas la commande correspondante ou elle a été annulée ou elle est en SAV ou le client est inactif, je la sors du spool en la supprimant
    ProductNotationSpool::delete($commande_notation['id']);
  }

}

?>
