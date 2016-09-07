<?php

require_once(INCLUDES_PATH.'fpdf/fpdf.php');

class PDFCatalogModel extends FPDF {

  function FrontHeader() {
    $x = $this->x = 20;
    $y = $this->y = 10; // make sure to have 0.1 rounded values
    $lMargin = $this->lMargin;
    $tMargin = $this->tMargin;
    $rMargin = $this->rMargin;
    $this->SetMargins($x,$y,$x);
    $this->Image(SECURE_PATH.'ressources/images/catalogue_pdf_logo_TC.jpg',10,10,100);
  }
  
  function Header() {
    if($this->showHeader == true){
      $x = $this->x = 20;
      $y = $this->y = 10; // make sure to have 0.1 rounded values
      $lMargin = $this->lMargin;
      $tMargin = $this->tMargin;
      $rMargin = $this->rMargin;
      $this->SetMargins($x,$y,$x);
      $this->Image(SECURE_PATH.'ressources/images/catalogue_pdf_page_header.png',10,10,60);
      $this->SetTextColor(0, 0, 0);
      $this->SetFont('Georgia','',12);
      $nomFamille3 = isset($this->nomFamille3) ? $this->nomFamille3 : '';
      $this->SetX($x+100);
      $this->MultiCell(80, 5, $nomFamille3,0,'R');
    }
    $this->showHeader = true;
  }
  
  function Footer() {
    if($this->showFooter == true){
      $this->SetFont('Arial','',10);
      $this->SetTextColor(0, 0, 0);
      $this->SetY($y+260);
      $this->MultiCell(80,4,
      "Techni-Contact\n".
      "253 rue Gallieni\n".
      "92774 - Boulogne Billancourt\n".
      "Tél : 01 55 60 29 29\n".
      "Fax : 01 83 62 36 12",0);
      $this->SetY($y+280);
      $this->SetFont('Georgia','',13);
      $this->Cell(95,5,$this->page -1,0,1,0,0,'');
      $this->Image(SECURE_PATH.'ressources/images/catalogue_pdf_page_footer_cart.jpg',190,270,8);
      $this->SetMargins($lMargin,$tMargin,$rMargin);
      $this->SetY($y+35);
    }
    $this->showFooter = true;
  }
  
  // get multi cell line count without writing it
  // code taken directly from MultiCell in FPDF, without border/alignment/output management
  function GetNbLines($w, $h, $txt, $border=0, $align='J', $fill=false) {
    $cw = &$this->CurrentFont['cw'];
    if ($w==0)
      $w = $this->w-$this->rMargin-$this->x;
    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    $s = str_replace("\r",'',$txt);
    $nb = strlen($s);
    if ($nb>0 && $s[$nb-1]=="\n")
      $nb--;
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $nl = 1;
    while($i<$nb) {
      $c = $s[$i];
      if($c=="\n") {
        $i++;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
        continue;
      }
      if ($c==' ') {
        $sep = $i;
        $ls = $l;
      }
      $l += $cw[$c];
      if ($l>$wmax) {
        if ($sep==-1) {
          if ($i==$j)
            $i++;
        }
        else
          $i = $sep+1;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
      }
      else
        $i++;
    }
    return $nl;
  }
  
  /*private $source; // source
  function setSource($source) {
    $this->source = $source;
  }*/
  
  function writeFrontBlueTitle($title) {
    $this->x += 30;
    $this->SetTextColor(0, 113, 188);
    $this->SetFont('Georgia','',28);
    $this->Cell(80,5,$title,0,1,0,0,'R');
    $this->y += 5;
  }
  
  function writeFrontGreyTitle($title) {
    $this->x += 30;
    $this->SetTextColor(128, 128, 128);
    $this->SetFont('Georgia','',28);
    $this->Cell(80,5,$title,0,1,0,0,'R');
    $this->y += 5;
  }
  
  function writeFrontAddressBottom($title) {
    $this->x += 30;
    $this->SetTextColor(0, 0, 0);
    $this->SetFont('Arial','',14);
    $this->Cell(80,5,$title,0,1,0,0,'R');
    $this->y += 2;
  }
 
  
}
