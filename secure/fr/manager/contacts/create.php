<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/contacts/create.php
 Description : Génération des fax
/=================================================================*/

if(!isset($_POST['sel']) || !preg_match('/^[0-9]{1,8}$/', $_POST['sel'])) {
  exit;
}

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$handle = DBHandle::get_instance();

define('FPDF_FONTPATH', 'font/');
require(INCLUDES . 'fpdf/fpdf.php');


if((!$result = & $handle->query('select a.nom1, a.nom2, a.contact, a.adresse1, a.adresse2, a.cp, a.ville, a.pays, c.type, p.name, c.societe, c.adresse, c.cadresse, c.cp, c.ville, c.pays, c.tel, c.fax, c.precisions, p.id, p.descc from contacts c, products_fr p, advertisers a where c.id = \'' . $handle->escape($_POST['sel']) . '\' and c.idProduct = p.id and c.idAdvertiser = a.id', __FILE__, __LINE__)) || ($handle->numrows($result, __FILE__, __LINE__) == 0))
{
    header('Location: ' . ADMIN_URL);
    exit;
}

$row = & $handle->fetch($result);

$handle->query('update contacts set gen = 1 where id = \'' . $handle->escape($_POST['sel']) . '\'', __FILE__, __LINE__);

$societe = $row[0];

if(!empty($row[1]))
{
   $societe .= ' ' . $row[1];
}

$contact = $row[2];
$adr1 = $row[3];
$adr2 = $row[4];
$cp = $row[5];
$ville = $row[6];
$pays = $row[7];
switch($row[8])
{
    case 1  : $type = 'Demande d\'informations'; break;
    case 2  : $type = 'Demande de contact téléphonique'; break;
    case 3  : $type = 'Demande de devis'; break;
    default : $type = 'Commande';
}

$np = $row[9];
$csociete = $row[10];
$cadr1 = $row[11];
$cadr2 = $row[12];
$ccp = $row[13];
$cville = $row[14];
$cpays = $row[15];
$tel = $row[16];
$fax = $row[17];
$precisions = $row[18];

$id = $row[19];

$desc = $row[20];


class PDF extends FPDF
{
    function AjouterChapitre($societe,$contact,$adr1,$adr2,$cp,$ville,$pays,$dateactuelle,$type,$np,$csociete,$cadr1,$cadr2,$ccp,$cville,$cpays,$tel,$fax,$precisions,$id,$desc)
    {
	$this->AddPage();
	$this->Image('logo.jpg', 10, 10, 67, 36);
	$this->SetFont('Arial','B',12);
	$this->Ln(20);
	$this->Cell(110);
	$this->Cell(40,5,$societe,0,2);
	$this->SetFont('');
	$this->Cell(40,5,$adr1,0,2);
	$this->Cell(40,5,$adr2,0,2);
	$this->Cell(40,5,$cp.' '.$ville,0,2);
	$this->Cell(40,5,$pays,0,2);
	$this->Ln(15);
	$this->Cell(110);
	$this->Cell(40,5,'Boulogne, le '.$dateactuelle,0,2);
	$this->Ln(10);

 	if(!empty($contact))
 	{
	    $this->Cell(110);
	    $this->Cell(40,5,'A l\'attention de '.$contact.'.',0,2);
	    $this->Ln(10);
	}

        $this->Cell(40,5,'Objet : ' . $type,0,1);
	$this->Ln(10);
	$this->Write(5,'Cher annonceur,');
	$this->Ln(10);
        $this->Write(5,'Un utilisateur de notre site http://www.techni-contact.com vient de demander des informations sur votre produit '.$np.'.');
	$this->Ln(10);
        $this->Write(5,'L\'équipe TECHNI-CONTACT.');
	$this->Ln(20);
	$this->Write(5,'Coordonnées :');
	$this->Ln(10);
        $this->SetFont('Arial','B',12);
	$this->Cell(40,5,$csociete,0,2);
	$this->SetFont('');
	$this->Cell(40,5,$cadr1,0,2);
	
        if(!empty($cadr2))
	{
	    $this->Cell(40,5,$cadr2,0,2);
	}
        
        $this->Cell(40,5,$ccp.' '.$cville,0,2);
	$this->Cell(40,5,$cpays,0,2);
	$this->Ln(5);
        $this->Cell(40,5,'Tél : ' . $tel,0,2);
	if(!empty($fax))
	{
	    $this->Cell(40,5,'Fax : ' . $fax,0,2);
	}
       
        if(!empty($precisions))
        {  
            $this->Ln(5);
	    //$this->Cell(40,5,'Précisions demandées : ' . $precisions,0,2);
	    $this->Write(5, 'Précisions demandées : ' . str_replace("\n", ' ', $precisions));

	}


        $this->Ln(10);
        $this->Cell(40,5,'Produit : ' . $np,0,2);
        $this->Ln(5);
        $this->Write(5,"Description :\n\n" . preg_replace('/ +/', ' ', strip_tags(str_replace(array('<br>', "\n", '&nbsp;', '&eacute;', '&egrave;', '&ecirc;', '&euro;', '&agrave;', '&bull;', '&quot;', '&icirc;', '&#9642;', '&ccedil;', '&acirc;', '&ocirc;', '&trade;', '&Eacute;'), array(' ', ' ', '', 'é', 'è', 'ê', '€', 'à', '', '"', 'î', '.', 'ç', 'â', 'ô', '™', 'E'), $desc))));
        $this->Ln(15);

        if(is_file(PRODUCTS_IMAGE_INC . 'zoom/'.$id.'.jpg'))
        {
           $this->AddPage();
           $this->Image(PRODUCTS_IMAGE_INC . 'zoom/'.$id.'.jpg', 10, 10);
        }

    }
    
    function Footer()
    {
        //Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        //Police Arial italique 8
        $this->SetFont('Arial','I',8);
        //Numéro de page centré
        $this->Cell(0,10,'M.D.2i  SAS 253 rue Galliéni – 92774 BOULOGNE BILLANCOURT cedex Téléphone : (33) 01.55.60.29.29 Télécopie : (33) 01.55.60.08 40 –',0,0,'C');
        $this->Ln(3);
        $this->Cell(0,10,'http://www.techni-contact.com – e-mail :  info@techni-contact.com SAS au capital de 40 000 Euros – R.C. NANTERRE B 392 772 497',0,0,'C');
    }
}

$pdf=new PDF();
$pdf->AjouterChapitre($societe, $contact, $adr1, $adr2, $cp, $ville, $pays, date('d/m/Y'), $type, $np, $csociete, $cadr1, $cadr2, $ccp, $cville, $cpays, $tel, $fax, $precisions, $id, $desc);
$pdf->Output();

?>

