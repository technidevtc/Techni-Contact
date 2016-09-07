<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . "CUserSession.php");
require(ICLASS . "CCustomerUser.php");
require(ICLASS . "CCart.php");

$handle = DBHandle::get_instance();
$session = new UserSession($handle);

$gotoaccount = false;
$cartID = isset($_POST["cartID"]) ? $_POST["cartID"] : (isset($_GET["cartID"]) ? $_GET["cartID"] : 0);
if (!preg_match("/^[0-9a-f]{32}$/", $cartID)) {
	$gotoaccount = true;
}
else {
/*	if (!$session->logged){
		$session->pageAfterLogin = COMPTE_URL . "estimate.html?cartID=" . $cartID;
		header("Location: " . COMPTE_URL . "login.html");
		exit();
	}*/
	$esti = new Cart($handle, $cartID);
	if (!$esti->existsInDB) {
		$gotoaccount = true;
	}
	/*elseif ($esti->idClient != $session->userID) {
		$gotoaccount = true;
	}*/
}

if ($gotoaccount) {
	header("Location: " . COMPTE_URL . "infos.html");
	exit();
}

$user = & new CustomerUser($handle, $session->userID);
$coord = $user->getCoordFromArray();
$coord["titre"] = CustomerUser::getTitle($coord["titre"]);
$esti->calculateCart();

require(INCLUDES_PATH . "fpdf/fpdf.php");

$pdf = new FPDF();

$pdf->AddPage();

$pdf->SetAutoPageBreak(true, 10);
$pdf->SetXY(10,6.5);

// Coordonnées Techni-Contact
$pdf->SetFont("Arial","B",18);
$pdf->Cell(80,7.5,"TECHNI-CONTACT MD2I",0,1);
$pdf->SetFont("Arial","",10);
$pdf->Cell(80,3.9,"253 RUE GALLIENI",0,1);
$pdf->Ln();
$pdf->Cell(80,3.9,"92774     BOULOGNE BILLANCOURT CEDEX",0,1);
$pdf->Ln();
$pdf->Cell(80,3.9,utf8_decode("N°Siret : 39277249700013"),0,1);
$pdf->Cell(80,3.9,"N.A.F. : 744 B",0,1);
$pdf->Cell(80,3.9,utf8_decode("N° intracommunautaire : FR1239277249700013"),0,1);
$pdf->Ln();
$pdf->Cell(80,3.9,utf8_decode("Tél. : 01 55 60 29 29"),0,1);
$pdf->Cell(80,3.9,"Fax : 01 83 62 36 12",0,1);
$pdf->Cell(80,3.9,"E-mail : info@techni-contact.com",0,1);

// Coordonnées Client
$pdf->SetLeftMargin(110);
$pdf->SetY(38);
$pdf->SetX(110);
$pdf->SetFont("Arial","B",11);
$pdf->Cell(80,7,utf8_decode($coord["societe"]),0,1);
$pdf->SetFont("Arial","",11);
$pdf->Cell(80,4.3,utf8_decode($titre . " " . $coord["prenom"] . " " . $coord["nom"]),0,1);
$pdf->Cell(80,4.3,utf8_decode($coord["adresse"]),0,1);
$pdf->Cell(80,4.3,utf8_decode($coord["cp"] . " " . $coord["ville"]),0,1);

// Infos Devis
$pdf->SetLeftMargin(10);
$pdf->SetY(70);
$pdf->SetX(10);
$pdf->SetFont("Arial","B",17);
$pdf->Cell(80,7,utf8_decode("DEVIS n° " . strtoupper($esti->estimate)),0,1);
$pdf->SetXY(175, $pdf->GetY()-4);
$pdf->SetFont("Arial","B",10);
$pdf->Cell(25,4,utf8_decode("du " . date("d/m/Y", $esti->create_time)),0,1);
//$pdf->Ln();

// Headers des colonnes
$pdf->SetFont("Arial","B",8);
$pdf->Cell(28,5,utf8_decode("Réf. TC"),1);
$pdf->Cell(69,5,utf8_decode("Désignation"),1);
$pdf->Cell(17,5,utf8_decode("Qté."),1,0,"C");
$pdf->Cell(14,5,utf8_decode("Unité"),1,0,"C");
$pdf->Cell(22,5,utf8_decode("P.U. HT"),1,0,"C");
$pdf->Cell(10,5,"",1,0,"C");
$pdf->Cell(22,5,"MT HT",1,0,"C");
$pdf->Cell( 8,5,"Tva",1,0,"C");
$pdf->Ln();

$pdf->SetFont("Arial","",8);
$pdf->SetTextColor(0,0,128);

$RectY = $pdf->GetY();

// contenus des colonnes
foreach ($esti->items as $item) {
  if (!empty($item["customCols"])) {
    $itemDesc = $item["label"];
    foreach($item["customCols"] as $ccol_header => $ccol_content)
      $itemDesc .= " - ".$ccol_header.": ".$ccol_content;
  }
  else {
    $itemDesc = $item["name"].(empty($item["fastdesc"]) ? "" : " - ".$item["fastdesc"]).(empty($item["label"]) ? "" : " - ".$item["label"]); 
  }
	
	$pdf->Cell(28,3,$item["idTC"],0);
	
	$lineY1 = $pdf->GetY();
	$pdf->MultiCell(69,3,utf8_decode($itemDesc) . "\n\n\n",0);
	$lineY2 = $pdf->GetY();
	$pdf->SetXY(107, $lineY1);
	
	$pdf->Cell(17,3,utf8_decode($item["quantity"]),0,0,"R");
	$pdf->Cell(14,3,utf8_decode($item["unite"]),0,0,"L");
	$pdf->Cell(22,3,utf8_decode(sprintf("%.02f", $item["price"])),0,0,"R");
	$pdf->Cell(10,3,"",0,0,"R");
	$pdf->Cell(22,3,utf8_decode(sprintf("%.02f", $item["sum_base"])),0,0,"R");
	$pdf->Cell( 8,3,utf8_decode(sprintf("%.02f", $item["tauxTVA"])),0,0,"R");
	$pdf->SetY($lineY2);

	// Promotions
	if (!empty($item["promotion"])) {
		$pdf->SetX(38);
		$lineY1 = $pdf->GetY();
		$pdf->MultiCell(69,3,utf8_decode("Promotion de" . " " . sprintf("%.02f", $item["promotionpc"]) . "% " . "pour" . " " . $item["quantity"] . " x " . $item["name"] . "\n\n"));
		$lineY2 = $pdf->GetY();
		$pdf->SetXY(170, $lineY1);
		$pdf->Cell(22,3,utf8_decode(sprintf("%0.2f", -$item["sum_promotion"])),0,0,"R");
		$pdf->SetY($lineY2);
	}
	
	// Discounts
	if (!empty($item["discount"])) {
		$pdf->SetX(38);
		$lineY1 = $pdf->GetY();
		$pdf->MultiCell(69,3,utf8_decode("Remise de" . " " . sprintf("%.02f", $item["discountpc"]) . "% " . "pour" . " " . $item["quantity"] . " x " . $item["name"] . "\n\n"));
		$lineY2 = $pdf->GetY();
		$pdf->SetXY(170, $lineY1);
		$pdf->Cell(22,3,utf8_decode(sprintf("%0.2f", -$item["sum_discount"])),0,0,"R");
		$pdf->SetY($lineY2);
	}
}

// Frais de Port
$pdf->Ln();
$pdf->SetX(38);
if ($esti->fdpHT == 0) {
	$pdf->Cell(69,3,"FRANCO DE PORT\n\n",0,1);
}
else {
	$pdf->Cell(69,3,"FRAIS DE PORT\n\n");
	$pdf->SetX(170);
	$pdf->Cell(22,3,utf8_decode(sprintf("%0.2f", $esti->fdpHT)),0,0,"R");
	$pdf->Cell( 8,3,$esti->fdp_tva,0,1,"R");
}
$pdf->SetX(10);

// Traits des colonnes
$RectH = $pdf->GetY() - $RectY;
if ($RectH < 160); $RectH = 160;

$pdf->Rect( 10, $RectY, 28, $RectH);
$pdf->Rect( 38, $RectY, 69, $RectH);
$pdf->Rect(107, $RectY, 17, $RectH);
$pdf->Rect(124, $RectY, 14, $RectH);
$pdf->Rect(138, $RectY, 22, $RectH);
$pdf->Rect(160, $RectY, 10, $RectH);
$pdf->Rect(170, $RectY, 22, $RectH);
$pdf->Rect(192, $RectY,  8, $RectH);
$pdf->SetY($RectY + $RectH + 5);


// Calcul des totaux

// Totaux des Taux de TVA
// Headers
$pdf->SetFont("Arial","B",10);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(12,7,"Code",1,0,"C");
$pdf->Cell(28,7,"Base ".chr(128)." HT",1,0,"C");
$pdf->Cell(13,7,"Taux",1,0,"C");
$pdf->Cell(28,7,"Montant Tva",1,1,"C");

$pdf->SetTextColor(0,0,128);

// Total par taux
foreach ($esti->tvaTable as $vatRate => $amount) {
	$pdf->SetFont("Arial","",9);
	$pdf->Cell(12,7,"","L",0,"C");
	$pdf->SetFont("Arial","",10);
	$pdf->Cell(28,7,sprintf("%.02f", $amount["total"]),"",0,"R");
	$pdf->Cell(13,7,sprintf("%.02f", $vatRate),"R",0,"R");
	$pdf->Cell(28,7,sprintf("%.02f", $amount["tva"]),"LR",1,"R");
}

// Foot
$pdf->SetFont("Arial","",10);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(12,7,"Total","TBL",0,"L");
$pdf->SetTextColor(0,0,128);
$pdf->Cell(28,7,sprintf("%.02f", $esti->stotalHT),"TB",0,"R");
$pdf->Cell(13,7,"","TBR",0,"R");
$pdf->SetFont("Arial","B",10);
$pdf->Cell(28,7,sprintf("%.02f", $esti->totalTVA),1,1,"R");


// Montant Totaux
$pdf->SetLeftMargin(150);
$pdf->SetXY(150, $RectY + $RectH + 5);

$pdf->SetFont("Arial","B",10);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(50,8.5,"Montant Total ".chr(128)." HT",1,1,"C");
$pdf->SetFont("Arial","B",11);
$pdf->SetTextColor(0,0,128);
$pdf->Cell(50,8.5,sprintf("%.02f", $esti->totalHT) . " ".chr(128),1,1,"R");
$pdf->SetY($pdf->GetY() + 3);

$pdf->SetFont("Arial","B",10);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(50,8.5,  "Montant Total ".chr(128)." TTC",1,1,"C");
$pdf->SetFont("Arial","B",11);
$pdf->SetTextColor(0,0,128);
$pdf->Cell(50,8.5,sprintf("%.02f", $esti->totalTTC . " ".chr(128)),1,1,"R");


$pdf->Output();

?>