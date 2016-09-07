<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/catalogues/create.php
 Description : Génération des lettres

/=================================================================*/

if(!isset($_POST['sel']) || !preg_match('/^[0-9]{1,8}$/', $_POST['sel']))
{

    exit;
}


require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';



$handle = DBHandle::get_instance();


define('FPDF_FONTPATH', 'font/');
require('../../../includes/fpdf/fpdf.php');


if((!$result = & $handle->query('select nom, prenom, societe, adresse, cadresse, cp, ville, pays, gen, ind, col from catalogues where id = \'' . $handle->escape($_POST['sel']) . '\'')) || ($handle->numrows($result, __FILE__, __LINE__) != 1))
{
    header('Location: ' . URL);
    exit;
}

$row = & $handle->fetch($result);

$handle->query('update catalogues set imp = 1 where id = \'' . $handle->escape($_POST['sel']) . '\'', __FILE__, __LINE__);

$nom     = $row[0];
$prenom  = $row[1];
$societe = $row[2];
$adr1    = $row[3];
$adr2    = $row[4];
$cp      = $row[5];
$ville   = $row[6];
$pays    = $row[7];

$cat1 = ($row[8]  == 1) ? 'Le catalogue Général'       : '';
$cat2 = ($row[9]  == 1) ? 'Le catalogue Industries'    : '';
$cat3 = ($row[10] == 1) ? 'Le catalogue Collectivités' : '';


class PDF extends FPDF
{
    function AjouterChapitre($societe,$nom,$prenom,$adr1,$adr2,$cp,$ville,$pays,$dateactuelle,$cat1,$cat2,$cat3)
    {
	$this->AddPage();
	$this->SetFont('Arial','B',12);
	$this->Ln(20);
	$this->Cell(110);
	$this->Cell(40,5,$societe,0,2);
	$this->SetFont('');
	$this->Cell(40,5,$prenom.' '.$nom,0,2);
	$this->Cell(40,5,$adr1,0,2);
	$this->Cell(40,5,$adr2,0,2);
	$this->Cell(40,5,$cp.' '.$ville,0,2);
	$this->Cell(40,5,$pays,0,2);
	$this->Ln(15);
	$this->Cell(110);
	$this->Cell(40,5,'Boulogne, le '.$dateactuelle,0,2);
	$this->Ln(10);
	$this->Cell(110);
	$this->Cell(40,5,'A l\'attention de '.$prenom.' '.$nom.'.',0,2);
	$this->Ln(10);
	$this->Cell(40,5,'Ref. : TC/04P',0,2);
	$this->Cell(40,5,'Objet : DOCUMENTATION',0,1);
	$this->Ln(10);
	$this->Write(5,'Suite à votre demande, nous vous prions de trouver ci-joint : ');
	$this->Ln(10);
	$this->SetFont('Arial','B',14);
	$this->Cell(40,5,$cat1,0,2);
	$this->Cell(40,5,$cat2,0,2);
	$this->Cell(40,5,$cat3,0,2);
	$this->Ln(10);
	$this->SetFont('Arial','',12);
	$this->Write(5,'Afin de continuez à recevoir nos catalogues, nous vous prions d\'actualiser votre abonnement en vous réinscrivant une fois par an sur notre site Internet : www.techni-contact.com.');
	$this->Ln(20);
	$this->Write(5,'Dans cette attente, veuillez agréer, l\'expression de nos sincères salutations.');
	$this->Ln(65);
	$this->Cell(110);
	$this->Cell(40,5,'Service administratif.',0,2);

    }
}


$pdf=new PDF();
$pdf->AjouterChapitre($societe, $nom, $prenom, $adr1, $adr2, $cp, $ville, $pays, date('d/m/Y'), $cat1, $cat2, $cat3);
$pdf->Output();

?>

