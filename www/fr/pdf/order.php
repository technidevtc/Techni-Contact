<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

try {

  if ($standalone = $_SERVER['SCRIPT_FILENAME'] == __FILE__) {
    $web_id = filter_input(INPUT_GET, 'web_id', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[\w\d]{32}$/i')));
    $dl = !!filter_input(INPUT_GET, 'dl', FILTER_VALIDATE_INT);
    
    if (empty($web_id))
      throw new Exception("Identifiant commande invalide");
  } else {
    global $conn;
  }
  
  $conn->setCharset('latin1'); // fpdf doesn't work in utf-8
  $q = Doctrine_Query::create()
      ->select('o.*,
                ol.*')
      ->from('Order o')
      ->leftJoin('o.lines ol')
      ->where('o.web_id = ?', $web_id);
  $o = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
  $showIntra = !empty($o['tva_intra']) && in_array((int)$o['activity'], Order::$activityTvaIntraList);
  
  if (!$o)
    throw new Exception("Identifiant commande invalide");
  
  // add delivery fees as a new line to simplify further processing
  $o['lines'][] = $o['fdp_ht'] ? array(
    'desc' => "Frais de port",
    'pu_ht' => $o['fdp_ht'],
    'quantity' => 1,
    'tva_code' => 1,
    'total_ht' => $o['fdp_ht'],
    'total_tva' => $o['fdp_tva']
  ) : array(
    'desc' => "Frais de port offerts",
    'tva_code' => 1,
    'total_ht' => 0,
    'total_tva' => 0
  );
  
  // do the same for global comment if there is one
  if (!empty($o['comment']))
    $o['lines'][] = array('desc' => $o['comment'], 'tva_code' => 1);
  
  if ($showIntra)
    $o['lines'][] = array('desc' => "Article 283-2 du CGI", 'tva_code' => 1);
  
  $tvas = Tva::getFullList($o['created'], 3);
  
  $tva_table = array();
  foreach($tvas as $tva) {
    $tva_table[$tva['id']] = array(
      'rate' => $tva['taux'],
      'base' => 0,
      'total' => 0
    );
  }
  
  $isPrivate = preg_match("/\bparticulier\s*$/i", $o['fonction']);

  switch ($o['website_origin']) {
    case "MOB":
      $pdf = new PDFInvoiceModelMOB();
      $siteName = "Mobaneo";
      $domain = "mobaneo.com";
      break;
    case "TC":
    default:
      $pdf = new PDFInvoiceModelTC();
      $siteName = "Techni-Contact";
      $domain = "techni-contact.com";
      break;
  }

  $pdf->SetAutoPageBreak(false);
  
  $pdf->AddPage();
  
  $bx = $x = round($pdf->getX(),1); // default margin
  $by = round($pdf->GetY(),1); // base y
  
  $pdf->writeTitle("Bon de commande N°".$o['id']);
  
  /*****************************************************************************
   * top rects
   ****************************************************************************/
  // headers
  $pdf->SetY($pdf->GetY()+5);
  $pdf->SetFont('Arial','B',9);
    $datePdf = " - Le ".date('d/m/Y', time());
  $pdf->Cell(80,4," Numéro : ".($o['alternate_id'] ? $o['alternate_id'] : $o['id']).$datePdf,1,0,'L');
  $pdf->Cell(55,4," Adresse livraison",1,0,'L');
  $pdf->Cell(55,4," Adresse facturation",1,0,'L');
  $pdf->Ln();
  
  $maxY = $y = round($pdf->GetY(),1);
  
  // infos
  $pdf->setY($y+4);
  $pdf->SetFont('', 'B');
  $pdf->Cell(80,3.5," Bon de commande à retourner signé",0,1);
  $pdf->Ln();
  $pdf->SetFont('', 'I');
  $pdf->Cell(80,3.5," Par courrier :",0,1);
  $pdf->SetFont('', '');
  $pdf->Cell(80,3.5," ".$siteName." - BDC",0,1);
  $pdf->Cell(80,3.5," 253 rue Gallieni",0,1);
  $pdf->Cell(80,3.5," F-92774 BOULOGNE BILLANCOURT cedex",0,1);

  $pdf->Ln();
  $pdf->SetFont('Arial', 'I');
  $pdf->Write(3.5," Par fax : ");
  $pdf->SetFont('Arial');
  $pdf->Write(3.5,"01 83 62 36 12");
  $pdf->Ln();
  $pdf->SetFont('Arial', 'I');
  $pdf->Write(3.5," Par mail : ");
  $pdf->SetFont('Arial');
  $pdf->Write(3.5,"commandes@".$domain);
  $pdf->Ln();
  $maxY = max($maxY, $pdf->getY());
  
  // delivery address
  $pdf->SetFont('Arial');
  $pdf->SetXY($x+80.5,$y+0.5);
  $pdf->MultiCell(54.5,3.5,
    ($isPrivate ? $o['prenom2']." ".$o['nom2']."\n" : $o['societe2']."\n").
    $o['adresse2']."\n".
    $o['cadresse2']."\n".
    $o['cp2']." - ".$o['ville2']."\n".
    $o['pays2']."\n".
    "Tel : ".$o['tel2']."\n".
    $o['delivery_infos'],0,"L");
  $maxY = max($maxY, $pdf->getY());
  
  // billing address
  $pdf->SetXY($x+135.5,$y+0.5);
  $pdf->MultiCell(54.5,3.5,
    ($isPrivate ? $o['prenom']." ".$o['nom']."\n" : $o['societe']."\n").
    $o['adresse']."\n".
    $o['cadresse']."\n".
    $o['cp']." - ".$o['ville']."\n".
    $o['pays']."\n".
    "Tel : ".$o['tel']."\n".
    ($showIntra ? "TVA intra : ".$o['tva_intra'] : "")."\n",0,"L");
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
  $fp_h_dft = 277-$t_lpf_h-$y-7; // first page reference height =100 if no delivery infos
  $sp_h_dft = 100+(106.5-$by); // subsequent page reference height

  $cwl = array(
    'ref'   => 28,
    'label' => 80,
    'pu_ht' => 19,
    'disc'  => 13,
    'disc_ht' => 20,
    'qty'   => 14,
    'tt_ht' => 16
  );
  
  
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
  foreach ($o['lines'] as $line) {
    
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
        $pdf->draw_ref_lines($r_t_y, $r_b_y, $cwl, $cxl);
        $pdf->AddPage();
        $pdf->draw_ref_headers($cwl, $cxl);
        $y = $pdf->GetY();
        $r_t_y = $y; // ref top y
        $r_b_y = $y + $sp_h_dft; // ref bottom y
        $tt_on_next_page = false;
      }
      else {
        $pdf->setY($y);
        $tt_on_next_page = true;
      }
    }
    else {
      $pdf->setY($y);
    }
    
    $prix_ht_remiser = ($line['pu_ht'] * ($line['discount']/100));
    $prix_ht_remiser_final = $line['pu_ht'] - $prix_ht_remiser;

    $pdf->Cell($cwl['ref'],4,$line['pdt_ref_id'] ? $line['pdt_ref_id'] : "",0,0,'C');
    if ($i_w) // there's an image to show
      $pdf->Image($i_path,$cxl['ref']+($cwl['ref']-$i_w)/2+$o_i_x,$y+$o_i_y,$i_w,$i_h);
    
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
      $pdf->Cell($cwl['label'],4,"Éco participation");
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
    $r_t_y = $y;
    $r_b_y = $y + $sp_h_dft;
    //$pdf->Line(10, $r_b_y, 200, 211.5);
    $pdf->Line(10, $r_b_y, 200, $r_b_y);
    $pdf->draw_ref_lines($r_t_y, $r_b_y, $cwl, $cxl);
  }
  
  /*****************************************************************************
   * VAT/Total Tables
   ****************************************************************************/
  $cwl = array(
    'bpa'    => 71,
    'base'   => 25,
    'rate'   => 23,
    'vat'    => 21,
    'totals' => 50
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
  $pdf->Rect($cxl['bpa']   ,$y,$cwl['bpa']   ,$t_h);
  $pdf->Rect($cxl['base']  ,$y,$cwl['base']  ,$t_h);
  $pdf->Rect($cxl['rate']  ,$y,$cwl['rate']  ,$t_h);
  $pdf->Rect($cxl['vat']   ,$y,$cwl['vat']   ,$t_h);
  $pdf->Rect($cxl['totals'],$y,$cwl['totals'],$t_h);
  $pdf->Line($cxl['base'],$y+8,$cxl['totals'],$y+8);
  
  $pdf->SetY($y+0.5);
  $pdf->SetFont('Arial','B',10);
  $pdf->Cell($cwl['bpa'],3.5,"Bon pour accord",0,0,'C');
  $pdf->SetFont('Arial','B',9);
  $pdf->Cell($cwl['base'],3.5," Base € HT");
  $pdf->Cell($cwl['rate'],3.5," Taux");
  $pdf->MultiCell($cwl['vat'],3.5," Montant\n TVA");
  
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
  
  $pdf->SetXY($cxl['totals'], $y+0.5);
  $pdf->SetFont('Arial','B',10);
  $pdf->MultiCell($cwl['totals'],4.5,
    "Montant total H.T. \n".
    sprintf('%.02f',$o['total_ht'])."€ \n".
    "\n".
    "Montant total T.T.C. \n".
    sprintf('%.02f',$o['total_ttc'])."€ ",0,'R');
  
  $y += $t_h;
  
  // last page footer
  $y += 4;
  $pdf->SetY($y);
  $pdf->SetFont('Arial','B',9);
  $pdf->Cell(90,3.5,"Mode de règlement :",0,1);
  $pdf->Ln();
  $pdf->SetFont('Arial');
  $pdf->Cell(90,3.5,"[  ] Chèque",0,1);
  $pdf->Cell(90,3.5,"[  ] Virement",0,1);
  $pdf->Cell(90,3.5,"[  ] Mandat administratif",0,1);
  
  $x += 90;
  $pdf->SetXY($x,$y);
  $pdf->Cell(90,3.5,"Chèques à libeller au nom de MD2i",0,1);
  $pdf->Ln();
  $pdf->SetX($x);
  $pdf->SetFont('Arial','IU');
  $pdf->Cell(90,3.5,"Coordonnées bancaires :",0,1);
  $pdf->SetX($x);
  $pdf->SetFont('Arial','I');
  $pdf->Cell(90,3.5,"BNP PARIBAS Boucle de Seine :",0,1);
  $pdf->SetX($x);
  $pdf->SetFont('Arial');
  $pdf->Cell(90,3.5,"RIB : 30004 01896 00010001645 13",0,1);
  $pdf->SetX($x);
  $pdf->Cell(90,3.5,"IBAN : FR76 3000 4018 9600 0100 0164 513",0,1);
  $pdf->SetX($x);
  $pdf->Cell(90,3.5,"BIC : BNPAFRPPGNV",0,1);
  
  $x = $bx;
  $y = $pdf->GetY()+4;
  $pdf->SetY($y);
  
  if ($domain == "techni-contact.com") {
    $pdf->SetFont('Arial','B');
    $pdf->Cell(190,3.5,"Important : Conditions Générales de Vente disponibles à cette adresse",0,1,'C');
    $pdf->SetFont('Arial');
    $pdf->Cell(190,3.5,"http://www.techni-contact.com/media/cgv.pdf",0,0,'C');
  }
  
  if ($standalone) {
    $pdf->Output("Commande ".($o['alternate_id'] ? $o['alternate_id'] : $o['id']).".pdf", $dl?'D':'I');
  } else {
    $pdf->Output(PDF_ORDER."Commande ".($o['alternate_id'] ? $o['alternate_id'] : $o['id']).".pdf", 'F');
    $conn->setCharset('utf8'); // set back to utf8 if not in standalone mode
  }

} catch (Exception $e) {
  echo $e->getMessage();
}
