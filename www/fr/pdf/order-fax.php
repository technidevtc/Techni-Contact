<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

try {

  //$cart_id = filter_input(INPUT_GET, 'cart_id', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[\w\d]{32}$/i')));
  $dl = !!filter_input(INPUT_GET, 'dl', FILTER_VALIDATE_INT);

  $db = DBHandle::get_instance();
  $db->set_charset('latin1'); // fpdf doesn't work in utf-8 (OD: but you can use utf8_decode for strings and chr(128) for euro symbol)
  $session = new UserSession($db);
  $c = new Cart($db, $session->getID());//$cart_id

  if (!$c->existsInDB)
    throw new Exception("Identifiant panier invalide");
  $c->calculateCart();
  
  $lines = $c->items; // make local copy of cart items
  
  // add delivery fees as a new line to simplify further processing
  $lines[] = $c->fdpHT ? array(
    'desc' => "Frais de port",
    'price' => $c->fdpHT,
    'ecotax' => 0,
    'quantity' => 1,
    'discountpc' => 0,
    'promotionpc' => 0,
    'sum_base' => $c->fdpHT,
    'sumEcotax' => 0
  ) : array(
    'desc' => "Frais de port offerts",
    'discountpc' => 0,
    'promotionpc' => 0,
    'sum_base' => 0,
    'sumEcotax' => 0
  );

  $isPrivate = preg_match("/\bparticulier\s*$/i", $c->fonction);
  
  $pdf = new PDFInvoiceModelTC();
  $siteName = "Techni-Contact";
  $domain = "techni-contact.com";

  $pdf->SetAutoPageBreak(false);
  
  $pdf->AddPage();
  
  $bx = $x = round($pdf->getX(),1); // default margin
  $by = round($pdf->GetY(),1); // base y
  
  $pdf->writeTitle("Bon de commande fax");
  
  /*****************************************************************************
   * top rects
   ****************************************************************************/
  // headers
  $pdf->SetY($pdf->GetY()+5);
  $pdf->SetFont('Arial','B',9);
  $pdf->Cell(80,4,  /*utf8_decode(" Numéro : ".$c->id)*/'',0,0,'L');
  $pdf->Cell(55,4," Adresse facturation",1,0,'L');
  $pdf->Cell(55,4," Adresse livraison",1,0,'L');
  //$pdf->Ln();
  
  $maxY = $y = round($pdf->GetY(),1);
  
  // infos
  $pdf->setY($y+4);
  $pdf->SetFont('', 'B');
  $pdf->Cell(80,3.5," Bon de commande à retourner signé",0,1);//avec votre\nrèglement
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
  /*$pdf->MultiCell(54.5,3.5,
    ($isPrivate ? $c['prenom2']." ".$c['nom2']."\n" : $c['societe2']."\n").
    $c['adresse2']."\n".
    $c['cadresse2']."\n".
    $c['cp2']." - ".$c['ville2']."\n".
    $c['pays2']."\n".
    "Tel : ".$c['tel2']."\n".
    $c['delivery_infos'],0,"L");*/
  
  $pdf->SetFont('Arial', 'BI');
  $pdf->MultiCell(54.5,3.5,
    "\n".
    "\n".
    "\n".
    "\n".
    "\n".
    "\n".
    "Infos complémentaires".
    $c->delivery_infos,0,"L");
    $pdf->SetXY($x+80.5,$y+25.5);
    $pdf->SetFont('Arial', '');
  $pdf->MultiCell(54.5,3.5,
    "Nom du contact : \n".
    "Tel : \n".
    "Email (obligatoire) :\n",0,"L");
  
  // billing address
  $pdf->SetXY($x+135.5,$y+0.5);
  /*$pdf->MultiCell(54.5,3.5,
    ($isPrivate ? $c['prenom']." ".$c['nom']."\n" : $c['societe']."\n").
    $c['adresse']."\n".
    $c['cadresse']."\n".
    $c['cp']." - ".$c['ville']."\n".
    $c['pays']."\n".
    "Tel : ".$c['tel']."\n",0,"L");*/
    
    $pdf->MultiCell(54.5,3.5,
    "\n".
    "\n".
    "\n".
    "\n".
    "\n".
    "\n".
    "Tel : \n".
    "Nom du contact : \n".
    "Instructions de livraison : \n".
    $c->delivery_infos,0,"L");
  /*$pdf->MultiCell(54.5,3.5,
    "\n".
    "\n".
    "\n".
    "\n".
    "\n".
    "Tel : \n",0,"L");*/
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
    'label' => 91,
    'pu_ht' => 21,
    'qty'   => 12,
    'disc'  => 15,
    'tt_ht' => 23
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
  
  $pdf->draw_ref_headers($cwl, $cxl);
  $y = $pdf->GetY();
  $r_t_y = $y; // ref top y
  $r_b_y = $y + $fp_h_dft; // ref bottom y
  $tt_on_next_page = false;
  
  // references
  $y += 0.5; // top margin
  $z = 2;
  
  foreach ($lines as $line) {
    if (empty($line['desc']))
      $line['desc'] = $line['cart_desc'];
    
    // appending comments to desc
    if (!empty($line['comment']))
      $line['desc'] .= "\n".$line['comment'];
    
    // image calcs
    $i_path = PRODUCTS_IMAGE_INC.'thumb_small/'.$line['idProduct'].'-1.jpg';
    if (is_file($i_path)) {
      $i_i = getimagesize($i_path);
      $i_w = $i_i[0]; // image width
      $i_h = $i_i[1]; // image height
      $i_r = max($i_w/$max_i_w, $i_h/$max_i_h); // max ratio
      if ($i_r > 1) {
        $i_w = floor($i_w/$i_r);	// Width Destination
        $i_h = floor($i_h/$i_r);	// Height Destination
      }
    }
    else {
      $i_w = $i_h = 0;
    }
    
    $pdf->SetFont('Arial');
    
    // line height
    $label_h = $pdf->GetNbLines($cwl['label'],4,$line['desc'],0,'L') * 4 + // desc height
               ($line['ecotax'] > 0 ? 8 : 0) + // eco tax if not empty
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
    
    $pdf->Cell($cwl['ref'],4,$line['idTC'] ? $line['idTC'] : "",0,0,'C');
    if ($i_w) // there's an image to show
      $pdf->Image($i_path,$cxl['ref']+($cwl['ref']-$i_w)/2+$o_i_x,$y+$o_i_y,$i_w,$i_h);
    
    $pdf->MultiCell($cwl['label'],4,$line['desc'],0,'L');
    $y2 = $pdf->getY(); // y after label
    
    $pdf->setXY($cxl['pu_ht'],$y);
    $pdf->Cell($cwl['pu_ht'],4,$line['price'] ? sprintf('%0.2f',$line['price']-$line['ecotax']) : "",0,0,'C');
    $pdf->Cell($cwl['qty'],4,$line['quantity'] ? $line['quantity'] : "",0,0,'C');
    $pdf->Cell($cwl['disc'],4,$line['discountpc'] ? $line['discountpc'] + $line['promotionpc'] : "",0,0,'C');
    if ($line['sum_base']) {
      $discountMul = (100-$line['discountpc']-$line['promotionpc'])/100;
      $lineTotalHT = ($line['sum_base']-$line['sumEcotax']) * $discountMul;
    } else {
      $discountMul = 1;
      $lineTotalHT = 0;
    }
    $pdf->Cell($cwl['tt_ht'],4,$line['sum_base'] ? sprintf('%0.2f',$lineTotalHT) : "",0,1,'C');
    
    if ($line['ecotax'] > 0) {
      $pdf->setXY($cxl['label'], $y2+4);
      $pdf->Cell($cwl['label'],4,"Taxe Eco Contribution");
      $pdf->Cell($cwl['pu_ht'],4,sprintf('%0.2f',$line['ecotax']),0,0,'C');
      $pdf->setX($cxl['tt_ht']);
      $pdf->Cell($cwl['tt_ht'],4,sprintf('%0.2f',$line['sumEcotax'] * $discountMul),0,1,'C');
      $y2 += 8;
    }
    
    if (!empty($line['delivery_time'])) {
      $pdf->SetFont('Arial','I');
      $pdf->setXY($cxl['label'], $y2+4);
      $pdf->Cell($cwl['label'],4,"Livraison : ".$line['delivery_time']);
    }
    
    $y += $l_h + 2;
  }
  $pdf->draw_ref_lines($r_t_y, $r_b_y, $cwl, $cxl); // draw lines

  // add a line for delivery fees
    $pdf->SetFont('Arial');
    $pdf->setXY($cxl['label'],$y);
    $pdf->Cell($cwl['label'],4,$fdp['desc'] ? $fdp['desc'] : "",0,0,'L');
    $pdf->setXY($cxl['pu_ht'],$y);
    $pdf->Cell($cwl['pu_ht'],4,"",0,0,'L');
    $pdf->Cell($cwl['qty'],4,"",0,0,'C');
    $pdf->Cell($cwl['disc'],4,"",0,0,'C');
    $pdf->Cell($cwl['tt_ht'],4,$fdp['sum_base'] ? sprintf('%0.2f',$fdp['sum_base']) : "",0,1,'C');
    $pdf->draw_ref_lines($r_t_y, $r_b_y, $cwl, $cxl); // draw lines
  
  if ($tt_on_next_page) { // draw a new page only for totals
    $pdf->AddPage();
    $pdf->draw_ref_headers($cwl, $cxl);
    $y = $pdf->GetY();
    $r_t_y = $y;
    $r_b_y = $y + $sp_h_dft;
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
  $pdf->SetFont('Arial','B',9);
  $pdf->Cell(70,3.5,"Instructions  :",0,1);
  $pdf->SetFont('Arial');
  $pdf->MultiCell(70,3.5,"/ Bon de commande valable pour la France métropolitaine uniquement",0,1);
  $pdf->MultiCell(70,3.5,"/ Bien remplir les adresses de livraison, facturation et les informations complémentaires",0,1);
  $pdf->MultiCell(70,3.5,"/ Bon de commande à retourner signé avec votre règlement.",0,1);
  $pdf->MultiCell(70,3.5,"/ Les frais de ports pourront éventuellement être réévalués en fonction du poids des produits.",0,1);

 $x += 69;
  $pdf->SetXY($x,$y);
  $pdf->Rect($x ,$y,$cwl['bpa']   ,$t_h);
  $pdf->Rect($cxl['totals'],$y,$cwl['totals'],$t_h);
  
  $pdf->SetXY($x,$y+0.5);
  $pdf->SetFont('Arial','B',10);
  $pdf->Cell($x,3.5,"Bon pour accord",0,0,'L');

  
  $pdf->SetXY($cxl['totals'], $y+0.5);
  $pdf->SetFont('Arial','B',10);
  $pdf->MultiCell($cwl['totals'],4.5,
    "Montant total H.T. \n".
    sprintf('%.02f',$c->totalHT).chr(128)." \n".
    "\n".
    "Montant total T.T.C. \n".
    sprintf('%.02f',$c->totalTTC).chr(128)." ",0,'R');
  
  $y += $t_h;
  
  // last page footer
  $x = $bx;
  $y += 8;
  $pdf->SetY($y);
  $pdf->SetFont('Arial','B',9);
  $pdf->Cell(90,3.5,"Mode de règlement :",0,1);
  $pdf->Ln();
  $pdf->SetFont('Arial');
  $pdf->Cell(90,3.5,"[  ] Chèque",0,1);
  $pdf->Cell(90,3.5,"[  ] Virement",0,1);
  $pdf->Cell(90,3.5,"[  ] Mandat administratif",0,1);
    $pdf->Ln();
  $pdf->Cell(90,3.5,"Chèques à libeller au nom de MD2i",0,1);
  
  $x += 90;
  $pdf->SetXY($x,$y);
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
  $y = $pdf->GetY()+8;
  $pdf->SetY($y);
  
  if ($domain == "techni-contact.com") {
    $pdf->SetFont('Arial','B');
    $pdf->Cell(190,3.5,"Important : Conditions Générales de Vente disponibles à cette adresse",0,1,'C');
    $pdf->SetFont('Arial');
    $pdf->Cell(190,3.5,"http://www.techni-contact.com/media/cgv.pdf",0,0,'C');
  }
  
  $pdf->Output("Commande par fax.pdf", $dl?'D':'I');

} catch (Exception $e) {
  echo $e->getMessage();
}
