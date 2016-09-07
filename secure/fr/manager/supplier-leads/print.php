<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

try {
  $user = new BOUser();

  if (!$user->login())
    throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page");

  if (!$user->get_permissions()->has("m-comm--sm-supplier-leads","r"))
    throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération.");

  $db = DBHandle::get_instance();
  $db->query('SET NAMES latin1'); // fpdf doesn't work in utf-8

  if (!isset($_POST["leadIds"]) || !preg_match("/\d+(,\d+)*/",$_POST["leadIds"]))
    throw new Exception("Format des identifiant(s) devis fournisseurs non valide(s)");

  $leadIds = explode(",",$_POST["leadIds"]);

  require(INCLUDES_PATH."fpdf/fpdf.php");
  $pdf = new FPDF();

  foreach($leadIds as $leadId) {
    $res = $db->query("
      SELECT
        c.id,
        c.idFamily as cat_id,
        c.timestamp AS date,
        c.nom,
        c.prenom,
        c.fonction,
        c.societe,
        c.salaries,
        c.secteur,
        c.naf,
        c.siret,
        c.adresse,
        c.cadresse,
        c.cp,
        c.ville,
        c.pays,
        c.tel,
        c.fax,
        c.email,
        c.url,
        c.precisions,
        c.type,
        c.campaignID,
        c.customFields,
        c.invoice_status,
        c.income,
        c.income_total,
        c.parent,
        c.reject_timestamp,
        c.credited_on,
        c.processing_status,
        pfr.name AS pdt_name,
        pfr.fastdesc AS pdt_fastdesc,
        pfr.id AS pdt_id,
        pfr.ref_name AS pdt_ref_name,
        pfr.descc AS pdt_descc,
        IF(p.warranty='', a.warranty, p.warranty) AS pdt_warranty,
        IF(pfr.delai_livraison='', a.delai_livraison, pfr.delai_livraison) AS pdt_delivery_time,
        IF(a.contraintePrix>0 AND IFNULL(rc.price2, p.price2)>0, a.contraintePrix, 0) AS pdt_adv_min_amount,
        a.id AS adv_id,
        a.nom1 AS adv_name,
        a.category AS adv_cat,
        a.is_fields AS adv_is_fields,
        rc.price AS pdt_price,
        ffr.name AS cat_name,
        ffr.ref_name as cat_ref_name,
        IFNULL(bou1.name,'-') AS com_name,
        IFNULL(bou2.name,'-') AS com_p_name
      FROM contacts c
      INNER JOIN products p ON p.id = c.idProduct
      INNER JOIN products_fr pfr ON pfr.id = p.id
      INNER JOIN advertisers a ON a.id = c.idAdvertiser AND a.actif = 1 AND a.category = ".__ADV_CAT_SUPPLIER__."
      LEFT JOIN references_content rc ON rc.idProduct = p.id AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
      LEFT JOIN families_fr ffr ON ffr.id = c.idFamily
      LEFT JOIN bo_users bou1 ON bou1.id = c.id_user_commercial
      LEFT JOIN bo_users bou2 ON bou2.id = c.id_user_processed
      STRAIGHT_JOIN (
          SELECT
            SUM(IF(config_name='fdp', config_value, 0)) AS fdp,
            SUM(IF(config_name='fdp_franco', config_value, 0)) AS fdp_franco
          FROM config
          WHERE config_name IN ('fdp', 'fdp_franco')
      ) pdt_fdp
      WHERE c.id = ".$leadId, __FILE__, __LINE__);

    if ($db->numrows($res) != 1)
      throw new Exception("Le devis fournisseur n°".$leadId." n'existe pas");

    $lead = $db->fetchAssoc($res);

    $lead["pdt_adv_min_amount"] = $lead["pdt_adv_min_amount"] > 0 ? sprintf("%.2f", $lead["pdt_adv_min_amount"]*$pdt_max_margin)."€ HT" : "-";
    $lead["pdt_shipping_fee"] = $lead["pdt_fdp"] == 0 ? "Offerts" : $lead["pdt_fdp"]." € HT";

    $customFields = mb_unserialize($lead['customFields']);
    if (empty($customFields))
      $customFields = array();

    // single lead cost
    if ($lead["adv_is_fields"] != "") $lead["adv_is_fields"] = mb_unserialize($lead["adv_is_fields"]);
    else $lead["adv_is_fields"] = array();

    if (!empty($lead["adv_is_fields"]))
      $is_fields = $lead["adv_is_fields"][0];

    $lead["pdt_pic"]["card"] = is_file(PRODUCTS_IMAGE_INC."card/".$lead["pdt_id"]."-1.jpg") ? PRODUCTS_IMAGE_INC."card/".$lead["pdt_id"]."-1.jpg" : PRODUCTS_IMAGE_INC."no-pic-card.gif";
    $lead["adv_cat_name"] = $adv_cat_list[$lead["adv_cat"]]["name"];


    // Writing PDF page
    $pdf->AddPage();

    $pdf->SetAutoPageBreak(true, 8);
    $pdf->SetXY(10,6.5);

    // ID lead fournisseur
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"ID lead fournisseur :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["id"],0,1);

    // Commercial en charge
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Commercial en charge :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["com_name"],0,1);

    // Date de réception
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Date de réception :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,date("d/m/Y à H:i", $lead["date"]),0,1);

    // Nom produit
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Nom produit :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["pdt_name"],0,1);

    // ----------
    $pdf->Ln();
    $pdf->line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->Ln();

    // Information demandeur
    $pdf->SetFont("Arial","IB",10);
    $pdf->Cell(45,4,"Information demandeur :",0,1);
    $pdf->Ln();

    // Message
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Message :",0,1);
    $pdf->SetFont("Arial","",10);
    $pdf->Write(4,$lead["precisions"]);
    $pdf->Ln();
    $pdf->Ln();

    // Société
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Société :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["societe"],0,1);

    // Email
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Email :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["email"],0,1);

    // Téléphone
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Téléphone :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["tel"],0,1);

    // Prénom Contact
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Prénom Contact :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["prenom"],0,1);

    // Nom Contact
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Nom Contact :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["nom"],0,1);

    // Adresse
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Adresse :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["adresse"],0,1);

    // C Adresse
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"C Adresse :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["cadresse"],0,1);

    // CP
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"CP :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["cp"],0,1);

    // Ville
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Ville :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["ville"],0,1);

    // Pays
    $pdf->SetFont("Arial","B",10);
    $pdf->Cell(45,4,"Pays :",0);
    $pdf->SetFont("Arial","",10);
    $pdf->Cell(45,4,$lead["pays"],0,1);

		// Custom fields
    foreach ($customFields as $fieldName => $fieldData) {
      $pdf->SetFont("Arial","B",10);
      $pdf->Cell(45,4,$fieldName." :",0);
      $pdf->SetFont("Arial","",10);
      $pdf->Cell(45,4,$fieldData,0,1);
		}
    
    // ----------
    $pdf->Ln();
    $pdf->line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->Ln();

    // Rappel fiche produit
    $pdf->SetFont("Arial","IB",10);
    $pdf->Cell(45,4,"Rappel fiche produit :",0,1);
    $pdf->Ln();

    $pdf->SetFont("Arial","B",17);
    $pdf->SetTextColor(176,0,0);
    $pdf->Write(6,$lead["pdt_name"]);
    $pdf->Ln();
    $pdf->SetFont("Arial","B",10);
    $pdf->SetTextColor(0,0,0);
    $pdf->Write(4,$lead["pdt_fastdesc"]);
    $pdf->Ln();
    $pdf->SetFont("Arial","",10);
    $pdf->Write(4,"Code fiche produit : ".$lead["pdt_id"]);
    $pdf->Ln();
    $pdf->Write(4,"Partenaire : ");
    $pdf->SetFont("Arial","B",10);
    $pdf->Write(4,$lead["adv_name"]);
    $pdf->SetFont("Arial","",10);
    $pdf->Write(4," (".$lead["adv_cat_name"].")");
    $pdf->Ln();
    
    $image_top = $pdf->GetY();
    $pdf->SetDrawColor(204,204,204);
    $pdf->Rect($pdf->getX(),$pdf->getY()+1,66.49+0.4,50+0.4);
    $pdf->Image($lead["pdt_pic"]["card"],$pdf->getX()+0.2,$pdf->getY()+1.2,66.5,50);
    
    $pdf->Ln();
    $pdf->SetFont("Arial","",13);
    $pdf->SetTextColor(176,0,0);
    $pdf->Cell(70,7,"",0);
    $pdf->Cell(50,7,"à partir de",0,1,"C");
    $pdf->SetFont("Arial","B",17);
    $pdf->Cell(70,7,"",0);
    $pdf->Cell(50,7,isset($lead["pdt_price"]) ? sprintf("%0.2f",$lead["pdt_price"])."€ HT" : "sur devis",0,1,"C");
    $pdf->SetFont("Arial","",8);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(75,4,"",0);
    $pdf->MultiCell(0,4,"\nFrais de port : ".$lead["pdt_shipping_fee"]."\n".
                         "Commande minimum : ".$lead["pdt_adv_min_amount"]."\n".
                         "Livraison : ".$lead["pdt_delivery_time"]."\n".
                         "Garantie : ".$lead["pdt_warranty"],0);
    
    $pdf->SetY($image_top+54);
    $pdf->SetFont("Arial","B",11);
    $pdf->SetTextColor(176,0,0);
    $pdf->Write(4,"Description");
    $pdf->Ln();
    $pdf->SetFont("Arial","",10);
    $pdf->SetTextColor(0,0,0);
    $pdf->Write(4,html_entity_decode(filter_var($lead["pdt_descc"],FILTER_SANITIZE_STRING)));
    
    $pdf->Ln();
    $pdf->Ln();
    
    // showing references if there is some
    if (isset($lead['pdt_price'])) {
      // getting col headers data
      $lead["pdt_refs"] = array();
      $pdt_max_margin = 1;
      $res = $db->query("
        SELECT content
        FROM references_cols
        WHERE idProduct = ".$lead["pdt_id"], __FILE__, __LINE__);
      list($content_cols) = $db->fetch($res);
      $content_cols = mb_unserialize($content_cols);
      if ($content_cols === false)
        echo "lead_id = ".$lead['id'];
      $pdt_ref_headers = array_slice($content_cols, 3, -5);

      // getting lines data
      $res = $db->query("
        SELECT id, label, content, refSupplier, price, price2, idTVA, unite
        FROM references_content
        WHERE idProduct = ".$lead["pdt_id"]." AND vpc = 1 AND deleted = 0
        ORDER BY classement", __FILE__, __LINE__);
      while ($ref = $db->fetchAssoc($res)) {
        $ref["content"] = mb_unserialize($ref["content"]);
        if ($ref["price2"] > 0 && $pdt_max_margin < $ref["price"]/$ref["price2"])
          $pdt_max_margin = $ref["price"]/$ref["price2"];
        $lead["pdt_refs"][] = $ref;
      }
      $lead["pdt_ref_count"] = count($lead["pdt_refs"]);
      
      // Drawing references table
      $table_total_height = ($lead["pdt_ref_count"]+1) * 6;
      if ($table_total_height+$pdf->GetY() > 286) { // table doesn't fit in the same page
        if ($pdf->GetY() > 274) // 297-10-6*2-1 do not draw only the headers at the bottom of the page
          $pdf->AddPage();
        elseif ($table_total_height < 276) // 297-10*2-1 the table fit at most one page, so draw it on it alone
          $pdf->AddPage();
      }
      $col_len = 190/(count($pdt_ref_headers)+3);
      $pdf->SetLineWidth(0.1);
      $pdf->SetFont("Arial","",9);
      $pdf->SetTextColor(255,255,255);
      $pdf->SetFillColor(176,0,0);
      $pdf->Cell($col_len,6,"Réf. TC",0,0,"C",1);
      $pdf->Cell($col_len,6,"Libellé",0,0,"C",1);
      
      foreach ($pdt_ref_headers as $pdt_ref_header)
        $pdf->Cell($col_len,6,$pdt_ref_header,0,0,"C",1);
      $pdf->Cell($col_len,6,"Prix HT",0,0,"C",1);

      $table_top = $pdf->GetY();
      $table_left = $pdf->GetX();
      $pdf->Ln();
      
      $pdf->SetFont("Arial","",9);
      $pdf->SetTextColor(0,0,0);
      $pdf->SetFillColor(0,0,0);
      foreach ($lead["pdt_refs"] as $l => $pdt_ref) {
        if ($pdf->getY() > 280) { // 297-10-6-1 the table will continue on the next page
          $pdf->SetDrawColor(128,128,128);
          $pdf->Rect($pdf->getX()-0.1,$table_top-0.1,$table_left-$pdf->getX()+0.2 ,$pdf->getY()-$table_top+0.2);
          $pdf->SetDrawColor(204,204,204);
          $pdf->AddPage();
          $table_top = $pdf->GetY();
        }
        $t = $l?"T":"";
        $pdf->Cell($col_len,6,$pdt_ref["id"],"R".$t,0,"C");
        $pdf->Cell($col_len,6,$pdt_ref["label"],"R".$t,0,"C");
        foreach ($pdt_ref["content"] as $pdt_ref_ccol)
          $pdf->Cell($col_len,6,$pdt_ref_ccol,"R".$t,0,"C");
        $pdf->Cell($col_len,6,sprintf("%.2f",$pdt_ref["price"])."€ HT",$t,1,"C");
      }
      $pdf->SetDrawColor(128,128,128);
      $pdf->Rect($pdf->getX()-0.1,$table_top-0.1,$table_left-$pdf->getX()+0.2 ,$pdf->getY()-$table_top+0.2);
    }
    
  }

  $pdf->Output();

} catch (Exception $e) {
  $title = $navBar = "Impression devis fournisseurs";
  require(ADMIN."head.php");
?>
<div class="section">
	<div class="block">
    <div class="fatalerror"><?php echo $e->getMessage() ?></div>
  </div>
</div>
<?php
  require(ADMIN."tail.php");
}
