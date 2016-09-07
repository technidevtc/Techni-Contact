<?php

  if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
    //require_once '../../../config.php';
    require_once 'C:/data/technico/config.php';

    //Changes on 08/12/2014
    $file_path_condition = __FILE__;
    $file_path_condition = str_replace('\\','/',$file_path_condition);
    
  }else{
    require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
    
    //Changes on 08/12/2014
    $file_path_condition = __FILE__;
  }

try {


  //if ($standalone = $_SERVER['SCRIPT_FILENAME'] == __FILE__) {
  if ($standalone = $_SERVER['SCRIPT_FILENAME'] == $file_path_condition) {
    
    $web_id = filter_input(INPUT_GET, 'web_id', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[\w\d]{32}$/i')));
    $type = filter_input(INPUT_GET, 'type', FILTER_VALIDATE_INT);
    $dl = !!filter_input(INPUT_GET, 'dl', FILTER_VALIDATE_INT);
    
    if (!$web_id)
      throw new Exception("Identifiant facture ou avoir invalide");
    if (!isset($type) || !isset(Invoice::$typeList[$type]))
      throw new Exception("Type facture ou avoir non défini");
  } else {  
    global $conn;
  }
  
  
  $conn->setCharset('latin1'); // fpdf doesn't work in utf-8
  $q = Doctrine_Query::create()
      ->select('i.*,
                il.*,
                o.id,
                o.alternate_id')
      ->from('Invoice i')
      ->leftJoin('i.lines il')
      ->leftJoin('i.order o')
      ->where('i.web_id = ?', $web_id)
      ->andWhere('i.type = ?', $type);
  $i = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
  $showIntra = !empty($i['tva_intra']) && in_array((int)$i['activity'], Invoice::$activityTvaIntraList);
  
  if (!$i)
    throw new Exception("Identifiant ".$i['type_text']." invalide");
  
  if ($i['activity'] < Invoice::ACTIVITY_ANNONCEUR) {
    // add delivery fees as a new line to simplify further processing
    $i['lines'][] = $i['fdp_ht'] ? array(
      'desc' => "Frais de port",
      'pu_ht' => $i['fdp_ht'],
      'quantity' => 1,
      'tva_code' => 1,
      'total_ht' => $i['fdp_ht'],
      'total_tva' => $i['fdp_tva']
    ) : array(
      'desc' => "Frais de port offerts",
      'tva_code' => 1,
      'total_ht' => 0,
      'total_tva' => 0
    );
  }
  
  // do the same for global comment if there is one
  if (!empty($i['comment']))
    $i['lines'][] = array('desc' => $i['comment'], 'tva_code' => 1);
  
  // and always add this global mention
  $i['lines'][] = array(
    'desc' => "\nImportant :\n\nEn cas de retard de paiement, seront exigibles, conformément à l'article L 441-6 du code de commerce, une indemnité calculée sur la base de trois fois le taux de l'intérêt légal en vigueur ainsi qu'une indemnité forfaitaire pour frais de recouvrement de 40 euros.",
    'tva_code' => 1
  );
  
  if ($showIntra)
    $i['lines'][] = array('desc' => "Article 283-2 du CGI", 'tva_code' => 1);
  
  // then get total line count
  $iLineCount = count($i['lines']);
  
  $tvas = Tva::getFullList($i['created'], 3);

  $tva_table = array();
  foreach($tvas as $tva) {
    $tva_table[$tva['id']] = array(
      'rate' => $tva['taux'],
      'base' => 0,
      'total' => 0
    );
  }

  $isPrivate = preg_match("/\bparticulier\s*$/i", $i['fonction']);
  
  switch ($i['website_origin']) {
    case "MOB":
      $pdf = new PDFInvoiceModelMOB();
      $siteName = "Mobaneo";
      $domain = "mobaneo.com";
      $tel = "01 83 62 96 95";
      break;
    case "TC":
    default:
      $pdf = new PDFInvoiceModelTC();
      $siteName = "Techni-Contact";
      $domain = "techni-contact.com";
      $tel = "01 55 60 29 29";
      break;
  }

  $pdf->SetAutoPageBreak(false);
  
  $pdf->AddPage();
  
  $bx = $x = round($pdf->getX(),1); // default margin
  $by = round($pdf->GetY(),1); // base y
  
  $pdf->writeTitle($i['type_text'].' N°'.$i['rid']);
  
  /*****************************************************************************
   * top rects
   ****************************************************************************/
  // headers
  $pdf->SetY($pdf->GetY()+5);
  $pdf->SetFont('Arial','B',9);
  $pdf->Cell(80,4," Numéro : ".$i['rid'],1,0,'L');
  $pdf->Cell(55,4," Adresse livraison",1,0,'L');
  $pdf->Cell(55,4," Adresse facturation",1,0,'L');
  $pdf->Ln();
  
  $maxY = $y = round($pdf->GetY(),1);
  
  $pdf->setY($y+0.5);
  // infos
  if ($i['type'] == Invoice::TYPE_INVOICE) {
    $pdf->SetFont('Arial');
    $pdf->Cell(80,3.3," Commande d'origine : ".($i['order']['alternate_id'] ? $i['order']['alternate_id'] : $i['order_id']),0,1);
    $pdf->Ln();
    $pdf->Cell(80,3.3," Date facture : ".date('d/m/Y',$i['issued']),0,1);
    $pdf->Ln();
    $pdf->SetFont('Arial','I');
    $pdf->Cell(80,3.3," Contact comptabilité :",0,1);
    $pdf->SetFont('Arial');
    $pdf->Cell(80,3.3," Tel : ".$tel,0,1);
    //$pdf->Cell(80,3.3," Email : comptabilite@techni-contact.com",0,1);
    $pdf->Ln();
    $pdf->SetFont('Arial','B');
    $pdf->Cell(33,3.3," Echéance : ");
    $pdf->SetFont('Arial');
    $pdf->Cell(47,3.3,date('d/m/Y',$i['due_date']),0,1);
    $pdf->SetFont('Arial','B');
    $pdf->Cell(33,3.3," Mode de règlement : ");
    $pdf->SetFont('Arial');
    $pdf->MultiCell(47,3.3,utf8_decode(Invoice::getPaymentModeText($i['payment_mode'])),0,1);


    //Changes on 08/12/2014
    //Include Payment type
    $pdf->SetFont('Arial','B');
    $pdf->Cell(33,3.3," Moyen de paiement : ");
    $pdf->SetFont('Arial');

    //Switch payment_mean (Payment Type)
    switch($i['payment_mean']){
      case '0':
        $payment_mean_print = ' Carte Bancaire (type en attente) ';
      break;
      
      case '1':
        $payment_mean_print = ' Carte Bancaire (Carte Bleue) ';
      break;
      
      case '2':
        $payment_mean_print = ' Carte Bancaire (Visa) ';
      break;
      
      case '3':
        $payment_mean_print = ' Carte Bancaire (Mastercard) ';
      break;
      
      case '4':
        $payment_mean_print = ' Carte Bancaire (American Express) ';
      break;
      
      case '5':
        $payment_mean_print = ' Paypal ';
      break;
      
      case '10':
        $payment_mean_print = ' Chèque ';
      break;
      
      case '20':
        $payment_mean_print = ' Virement bancaire ';
      break;
      
      case '30':
        $payment_mean_print = ' Paiement différé ';
      break;
      
      case '40':
        $payment_mean_print = ' Contre-remboursement ';
      break;
      
      case '50':
        $payment_mean_print = ' Mandat administratif ';
      break;
      
      case '60':
        $payment_mean_print = ' Prélèvement ';
      break;
      
      case '70':
        $payment_mean_print = ' Lettre de change ';
      break;
      
      default:
        $payment_mean_print = ' - ';
      break;
    
    }//End switch
      $pdf->Cell(47,3.3, $payment_mean_print,0,1);
  
  } else {
    $pdf->SetFont('Arial');
    $pdf->Cell(80,3.3," Avoir sur la facture n°: ".$i['invoice_rid'],0,1);
    $pdf->Ln();
    $pdf->Cell(80,3.3," Date : ".date('d/m/Y',$i['issued']),0,1);
    $pdf->Ln();
    $pdf->SetFont('Arial','I');
    $pdf->Cell(80,3.3," Contact comptabilité :",0,1);
    $pdf->SetFont('Arial');
    $pdf->Cell(80,3.3," Tel : ".$tel,0,1);
    //$pdf->Cell(80,3.3," Email : comptabilite@techni-contact.com",0,1);
  }
  $maxY = max($maxY, $pdf->getY());
  
  
  // delivery address
  $pdf->SetFont('Arial');
  $pdf->SetXY($x+80.5,$y+0.5);
  $pdf->MultiCell(54.5,3.5,
    ($isPrivate ? $i['prenom2']." ".$i['nom2']."\n" : $i['societe2']."\n").
    $i['adresse2']."\n".
    $i['cadresse2']."\n".
    $i['cp2']." – ".$i['ville2']."\n".
    $i['pays2']."\n".
    "Tel : ".$i['tel2']."\n".
    $i['delivery_infos'],0,"L");
  $maxY = max($maxY, $pdf->getY());
  
  // billing address
  $pdf->SetXY($x+135.5,$y+0.5);
  $pdf->MultiCell(54.5,3.5,
    ($isPrivate ? $i['prenom']." ".$i['nom']."\n" : $i['societe']."\n").
    $i['adresse']."\n".
    $i['cadresse']."\n".
    $i['cp']." – ".$i['ville']."\n".
    $i['pays']."\n".
    "Tel : ".$i['tel']."\n".
    ($showIntra ? "TVA intra : ".$i['tva_intra'] : "")."\n",0,"L");
  $maxY = max($maxY, $pdf->getY());
  
  $maxY += 3; // bottom min margin
  // lines
  $a_h = max($maxY-$y, 30);
  $pdf->Rect($x    ,$y,80,$a_h);
  $pdf->Rect($x+ 80,$y,55,$a_h);
  $pdf->Rect($x+135,$y,55,$a_h);
  
  /*****************************************************************************
   * references
   ****************************************************************************/
  $y += $a_h + 4;
  
  $pdf->SetY($y);
  $t_lpf_h = 4+26+5.5+28; // totals & last page footer height
  $fp_h_dft = 277-$t_lpf_h-$y+3.5; // first page reference height =110 if no delivery infos
  $sp_h_dft = 110+(107-$by); // subsequent page reference height
  $cwl = array(
    'ref'   => 28,
    'label' => 80,
    'pu_ht' => 19,
    'disc'  => 13,
    'disc_ht' => 20,
    'qty'   => 14,
    'tt_ht' => 16
  ); // Column Width List
  $cxl = array(); // Column X List
  $cx = $x;
  foreach ($cwl as $cn => $cw) {
    $cxl[$cn] = $cx;
    $cx += $cw;
  }
  $max_i_w = $cwl['ref']-8; // max image width
  $max_i_h = 4*4; // max image height
  $o_i_x = 0; // image x offset
  $o_i_y = 4; // image y offset
  
  $pdf->SetFont("Arial","B",8);
  $pdf->Cell(28,5,"Référence",1);
  $pdf->Cell(80,5,"Désignation",1);
  $pdf->Cell(19,5,"P.U. € HT",1,0,"C");
  $pdf->Cell(13,5,"Rem",1,0,"C");
  $pdf->Cell(20,5,"PU HT Rem",1,0,"C");
  $pdf->Cell(14,5,"Qté",1,0,"C");
  $pdf->Cell(16,5,"Total € HT",1);
  $pdf->Ln();
  
  $y = $pdf->GetY();
  $r_t_y = $y; // ref top y
  $r_b_y = $y + $fp_h_dft; // ref bottom y
  $tt_on_next_page = false;
  
  // references
  $y += 0.5; // top margin
  $z = 2;
  foreach ($i['lines'] as $li => $line) {
    
    // appending comments to desc
    if (!empty($line['comment']))
      $line['desc'] .= "\n".$line['comment'];
    
    // image calcs
    $i_path = PRODUCTS_IMAGE_INC.'thumb_small/'.$line['pdt_id'].'-1.jpg';
    if (is_file($i_path)) {
      $i_i = getimagesize($i_path);
      $i_w = $i_i[0]; // image width
      $i_h = $i_i[1]; // image height
      $i_r = max($i_w/$max_i_w, $i_h/$max_i_h); // max ratio
      if ($i_r > 1) {
        $i_w = floor($i_w/$i_r);  // Width Destination
        $i_h = floor($i_h/$i_r);  // Height Destination
      }
    }
    else {
      $i_w = $i_h = 0;
    }
    
    $pdf->SetFont('Arial');
    
    // line height
    $label_h = $pdf->GetNbLines($cwl['label'],4,$line['desc'],0,'L') * 4 + // desc height
               ($line['et_ht'] > 0 ? 8 : 0) + // eco tax if not empty
               (!empty($line['delivery_time']) ? 8 : 0); // delivery time if not empty
    $l_h = max($label_h, $i_h+$o_i_y, 4); // line height
    
    if ($y+$l_h > $r_b_y) { // line is too big
      if (!$tt_on_next_page)
        $r_b_y += $t_lpf_h; // totals will be on the next page
      if ($y+$l_h > $r_b_y) { // line is still too big even without the totals
        $pdf->Line(10, $r_b_y, 200, $r_b_y);
        $pdf->draw_ref_lines($r_t_y, $r_b_y, $cwl, $cxl);
        $pdf->AddPage();
        $pdf->SetFont("Arial","B",8);
        $pdf->Cell(28,5,"Référence",1);
        $pdf->Cell(80,5,"Désignation",1);
        $pdf->Cell(19,5,"P.U. € HT",1,0,"C");
        $pdf->Cell(13,5,"Rem",1,0,"C");
        $pdf->Cell(20,5,"PU HT Rem",1,0,"C");
        $pdf->Cell(14,5,"Qté",1,0,"C");
        $pdf->Cell(16,5,"Total € HT",1);
        $pdf->Ln();

        //$pdf->draw_ref_headers($cwl, $cxl);
        $y = $pdf->GetY();
        $r_t_y = $y; // ref top y
        $r_b_y = $y + $sp_h_dft; // ref bottom y
        $tt_on_next_page = false;
      } else {
        $pdf->setY($y);
        $tt_on_next_page = true;
      }
    }
    else {
      $pdf->setY($y);
    }
    
    $pdf->Cell($cwl['ref'],4,$line['pdt_ref_id'] ? $line['pdt_ref_id'] : "",0,0,'C');
    if ($i_w) // there's an image to show
      $pdf->Image($i_path,$cxl['ref']+($cwl['ref']-$i_w)/2+$o_i_x,$y+$o_i_y,$i_w,$i_h);
    
    // font in italic for the last line with the global mention
    if ($li == $iLineCount-1)
      $pdf->SetFont('Arial','I');
    
    $prix_ht_remiser = ($line['pu_ht'] * ($line['discount']/100));
    $prix_ht_remiser_final = $line['pu_ht'] - $prix_ht_remiser;

    $pdf->MultiCell($cwl['label'],4, $line['desc'],0,'L');
    $y2 = $pdf->getY(); // y after label
    
    $pdf->setXY($cxl['pu_ht'],$y);
    $pdf->Cell($cwl['pu_ht'],4,$line['pu_ht'] ? sprintf('%0.2f',$line['pu_ht']) : "",0,0,'C');
    $pdf->Cell($cwl['disc'],4,$line['discount'] ? $line['discount'].'%' : "",0,0,'C');
    $pdf->Cell($cwl['disc_ht'],4,$line['discount'] ? sprintf('%0.2f', $prix_ht_remiser_final ) : "",0,0,'C');
    $pdf->Cell($cwl['qty'],4,$line['quantity'] ? $line['quantity'] : "",0,0,'C');
    $pdf->Cell($cwl['tt_ht'],4,$line['total_ht'] ? sprintf('%0.2f',$line['total_ht']) : "",0,1,'C');
    
    if ($line['et_ht'] > 0) {
      $pdf->setXY($cxl['label'], $y2+4);
      $pdf->Cell($cwl['label'],4,"Taxe Eco Contribution");
      $pdf->Cell($cwl['pu_ht'],4,sprintf('%0.2f',$line['et_ht']),0,0,'C');
      $pdf->setX($cxl['tt_ht']);
      $pdf->Cell($cwl['tt_ht'],4,sprintf('%0.2f',$line['et_total_ht']),0,1,'C');
      $y2 += 8;
    }
    
    if (!empty($line['delivery_time'])) {
      $pdf->SetFont('Arial','I');
      $pdf->setXY($cxl['label'], $y2+4);
      $pdf->Cell($cwl['label'],4,"Livraison : ".$line['delivery_time']);
    }
    
    $tva_table[$line['tva_code']]['base'] += $line['total_ht'] + $line['et_total_ht'];
    $tva_table[$line['tva_code']]['total'] += $line['total_tva'] + $line['et_total_tva'];
    
    $y += $l_h + 2;
  }
  $pdf->Line(10, $r_b_y, 200, $r_b_y);
  $pdf->draw_ref_lines($r_t_y, $r_b_y, $cwl, $cxl); // draw lines
  
  if ($tt_on_next_page) { // draw a new page only for totals
    $pdf->AddPage();
    $pdf->draw_ref_headers($cwl, $cxl);
    $y = $pdf->GetY();
    $r_t_y = $y;
    $r_b_y = $y + $sp_h_dft;
    //$pdf->draw_ref_lines($r_t_y, $r_b_y, $cwl, $cxl);
  }
  
  /*****************************************************************************
   * VAT/Total Tables
   ****************************************************************************/
  $cwl = array(
    'base'    => 25,
    'rate'    => 23,
    'vat'     => 21,
    'payment' => 71,
    'totals'  => 50
  ); // Column Width List
  
  $cxl = array(); // Column X List
  $cx = $x;
  foreach ($cwl as $cn => $cw) {
    $cxl[$cn] = $cx;
    $cx += $cw;
  }
  
  $y = $r_b_y + 4;
  $t_h = 26;
  $pdf->SetY($y);
  $pdf->Rect($cxl['base']   ,$y,$cwl['base']   ,$t_h);
  $pdf->Rect($cxl['rate']   ,$y,$cwl['rate']   ,$t_h);
  $pdf->Rect($cxl['vat']    ,$y,$cwl['vat']    ,$t_h);
  $pdf->Rect($cxl['payment'],$y,$cwl['payment'],$t_h);
  $pdf->Rect($cxl['totals'] ,$y,$cwl['totals'] ,$t_h);
  $pdf->Line($cxl['base'],$y+8,$cxl['totals'],$y+8);
  
  $pdf->SetY($y+0.5);
  $pdf->SetFont('Arial','B',9);
  $pdf->Cell($cwl['base'],3.5," Base € HT");
  $pdf->Cell($cwl['rate'],3.5," Taux");
  $pdf->MultiCell($cwl['vat'],3.5," Montant\n TVA");
  $pdf->SetFont('Arial','B',10);
  $pdf->SetXY($cxl['payment'],$y+0.5);
  if ($i['type'] == Invoice::TYPE_INVOICE)
    $pdf->Cell($cwl['payment'],3.5,"Etat du réglement",0,0,'C');
  
  $pdf->SetFont('Arial','',9);
  $pdf->SetY($y+8.5);
  foreach($tva_table as $tva_id => $tva_amount) {
    $pdf->SetX($cxl['base']);
    $pdf->Cell($cwl['base'],3.5,$tva_amount['base'] ? sprintf('%0.2f',$tva_amount['base'])."€ " : "",0,0,'R');
    $pdf->Cell($cwl['rate'],3.5,$tva_amount['rate']."% ",0,0,'R');
    $pdf->Cell($cwl['vat'] ,3.5,$tva_amount['total'] ? sprintf('%0.2f',$tva_amount['total'])."€ " : "",0,0,'R');
    $pdf->Ln();
  }
  //$pdf->SetX($cxl['rate']);
  //$pdf->MultiCell($cwl['rate'],3.5,"(Valeurs des \ncodes TVA) ",0,'R');
  
  $pdf->SetXY($cxl['payment'], $y+8.5);
  $pdf->SetFont('Arial','B',12);
  if ($i['type'] == Invoice::TYPE_INVOICE) {
    switch ($i['payment_mode']) {
      case Invoice::PAYMENT_MODE_50_ORDER_50_INVOICING:
        $pdf->MultiCell($cwl['payment'],5,
          "Reste à payer pour le\n".
          date('d/m/Y',$i['due_date'])."\n".
          sprintf('%.02f',$i['total_ttc']/2)."€",0,'C');
        break;
      case Invoice::PAYMENT_MODE_30_DAYS_INVOICING:
      case Invoice::PAYMENT_MODE_MONEY_ORDER:
        $pdf->MultiCell($cwl['payment'],5,
          "A payer pour le\n".
          date('d/m/Y',$i['due_date']),0,'C');
        break;
      default:
        $pdf->MultiCell($cwl['payment'],5,"Payé",0,'C');
    }
  }
  
  $pdf->SetXY($cxl['totals'], $y+0.5);
  $pdf->SetFont('Arial','B',10);
  $pdf->MultiCell($cwl['totals'],4.5,
    "Montant total H.T. \n".
    sprintf('%.02f',$i['total_ht'])."€ \n".
    "\n".
    "Montant total T.T.C. \n".
    sprintf('%.02f',$i['total_ttc'])."€ ",0,'R');
  
  $y += $t_h;
  
  // last page footer
  $y += 5.5;
  $pdf->SetXY($x+13,$y);
  $pdf->SetFont('Arial','IB',11);
  $pdf->Cell(0,3.5,"Important :",0,1);
  
  //Changes on 08/12/2014 desactivate the row
  //$pdf->Ln();
  //$pdf->SetX($x+13);
  //$pdf->SetFont('Arial','',9);
  // $pdf->Cell(0,3.5,"Chèques à libeller au nom de MD2i. A envoyer au 253 rue Gallieni - 92774 Boulogne Billancourt Cedex",0,1);
  
  $pdf->Ln();
  $pdf->SetFont('Arial','I');
  $pdf->SetX($x+13);
  $pdf->Cell(0,3.5,"Coordonnées bancaires BNP PARIBAS Boucle de Seine :",0,1);
  $pdf->SetFont('Arial');
  $pdf->SetX($x+13);
  $pdf->MultiCell(0,3.5,"RIB : 30004 01896 00010001645 13\nIBAN : FR76 3000 4018 9600 0100 0164 513\nBIC : BNPAFRPPGNV");
  //$this->Image(SECURE_PATH.'ressources/images/footer-print-fevad.jpg',$x+30,$y+2,33);
  //$this->Image(SECURE_PATH.'ressources/images/footer-print-partners.jpg',$x+87,$y+2,72);
  
  if ($standalone) {
    $pdf->Output(Invoice::getTypeText($i['type'])." ".$i['rid'].".pdf", $dl?'D':'I');
  } else {
    $pdf->Output(PDF_INVOICE.Invoice::getTypeText($i['type'])." ".$i['rid'].".pdf", 'F');
    $conn->setCharset('utf8'); // set back to utf8 if not in standalone mode
  }

} catch (Exception $e) {
  echo $e->getMessage();
}
