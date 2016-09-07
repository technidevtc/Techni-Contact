<?php


if(!isset($_POST['sel']) || !preg_match('/^[0-9]{1,8}$/', $_POST['sel']) || !isset($_POST['idfacture']))
{

    exit;
}


require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';



$handle = DBHandle::get_instance();


define('FPDF_FONTPATH', 'font/');
require('../../../includes/fpdf/fpdf.php');


if((!$result = & $handle->query('select nom, prenom, societe, adresse, cadresse, cp, ville, pays from catalogues where id = \'' . $handle->escape($_POST['sel']) . '\'')) || ($handle->numrows($result, __FILE__, __LINE__) != 1))
{
    header('Location: ' . URL);
    exit;
}

$row = & $handle->fetch($result);


class PDF extends FPDF
{
    function CreateFacture(& $data)
    {
        $des  = ($data[7] == 'FRANCE') ? 'Catalogue(s) France' : 'Catalogue(s) Etranger';
        $cost = ($data[7] == 'FRANCE') ? '15'                  : '30';

        $this->SetDisplayMode('real');
        $this->SetLineWidth(0.01);
        $this->SetLeftMargin(1);
        $this->SetRightMargin(1);
	$this->AddPage();
	$this->setFont('Arial', 'B', 10);
        $this->Rect(12, 5, 8, 3.2);
        $this->SetXY(12.1, 5.4);
        $this->MultiCell(10, 0, $data[2]);
	$this->setFont('Arial', '', 10);
	$this->SetXY(12.1, 6.2);
        $this->MultiCell(10, 0, $data[3]);
	$this->SetXY(12.1, 6.6);
        $this->MultiCell(10, 0, $data[4]);
	$this->SetXY(12.1, 7.3);
        $this->MultiCell(10, 0, $data[5] . '-' . $data[6]);
	$this->SetXY(12.1, 7.7);
        $this->MultiCell(10, 0, $data[7]);

	$this->SetXY(1.1, 5.3);
	$this->setFont('Arial', 'B', 12);
        $this->MultiCell(10, 1, 'FACTURE', 1, 'C');
	$this->SetXY(1.1, 6.3);
	$this->setFont('Arial', 'B', 10);
        $this->MultiCell(3.9, 0.8, 'N° facture', 1, 'C');
	$this->SetXY(5, 6.3);
        $this->MultiCell(2.2, 0.8, 'Date', 1, 'C');
	$this->SetXY(7.2, 6.3);
        $this->MultiCell(3.9, 0.8, 'Code client', 1, 'C');
	$this->SetXY(1.1, 7.1);
        $this->MultiCell(3.9, 0.8, $_POST['idfacture'], 1, 'C');
	$this->SetXY(5, 7.1);
        $this->MultiCell(2.2, 0.8, date('d/m/Y'), 1, 'C');
	$this->SetXY(7.2, 7.1);
        $this->MultiCell(3.9, 0.8, '', 1, 'C');
	
        
	$this->setFont('Arial', '', 10);
        $this->SetXY(1.0, 9.4);
        $this->Cell(10, 0.8, 'Mode de paiement : 100% à la commande');
        $this->SetXY(1.0, 10.2);
        $this->Cell(10, 0.8, 'Date d\'échéance : ' . date('d/m/Y'));

	$this->setFont('Arial', 'B', 10);
        $this->SetXY(1.1, 11);
        $this->setFillColor(200, 200, 200);
        $this->MultiCell(2.1, 0.8, 'Référence', 1, 'C', 1);
        $this->SetXY(3.1, 11);
        $this->MultiCell(7.2, 0.8, 'Description', 1, 'C', 1);
        $this->SetXY(10.3, 11);
        $this->MultiCell(2.1, 0.8, 'Quantité', 1, 'C', 1);
        $this->SetXY(12.3, 11);
        $this->MultiCell(3.2, 0.8, 'Prix unitaire HT', 1, 'C', 1);
        $this->SetXY(15.5, 11);
        $this->MultiCell(2.2, 0.8, 'Total TVA', 1, 'C', 1);
        $this->SetXY(17.7, 11);
        $this->MultiCell(2.2, 0.8, 'Total TTC', 1, 'C', 1);

        $this->setFillColor(255, 255, 255);
        $this->setFont('Arial', '', 10);
        $this->SetXY(1.1, 11.8);
        $this->MultiCell(2.1, 0.8, "\n\n\n\n\n\n", 1, 'L', 1);
        $this->SetXY(3.1, 11.8);
        $this->MultiCell(7.2, 0.8, $des . "\n \n \n \n \n ", 1, 'L', 1);
        $this->SetXY(10.3, 11.8);
        $this->MultiCell(2.1, 0.8, "1 \n \n \n \n \n ", 1, 'R', 1);
        $this->SetXY(12.3, 11.8);
        $this->MultiCell(3.2, 0.8, $cost . " \n \n \n \n \n ", 1, 'R', 1);
        $this->SetXY(15.5, 11.8);
        
        $tva = 20.0 * $cost / 100;
        if(strstr($tva, '.'))
        {

            $ttva = explode('.', $tva);
            $tva  = $ttva[0] . ',' . substr($ttva[1], 0, 2);
        }


        $this->MultiCell(2.2, 0.8, $tva . " \n \n \n \n \n ", 1, 'R', 1);
        $this->SetXY(17.7, 11.8);
        
        $total = str_replace(',', '.', $tva) + $cost;

        $this->MultiCell(2.2, 0.8, $total . " \n \n \n \n \n ", 1, 'R', 1);

	$this->setFont('Arial', '', 8);
        $this->SetXY(1.0, 17);
        $this->Cell(10, 0.8, 'Pas de pénalité de retard - Pas d\'escompte en cas de paiement anticipé');

	$this->setFont('Arial', 'B', 10);
        $this->SetXY(1.1, 19);
        $this->setFillColor(200, 200, 200);
        $this->MultiCell(2.1, 0.8, '% TVA', 1, 'C', 1);
        $this->SetXY(3.2, 19);
        $this->MultiCell(2.4, 0.8, 'Base', 1, 'C', 1);
        $this->SetXY(5.6, 19);
        $this->MultiCell(3.1, 0.8, 'Montant TVA', 1, 'C', 1);
        $this->SetXY(8.7, 19);
        $this->MultiCell(2.7, 0.8, 'Total HT', 1, 'C', 1);
        $this->SetXY(11.4, 19);
        $this->MultiCell(2.7, 0.8, 'Total TVA', 1, 'C', 1);
        $this->SetXY(14.1, 19);
        $this->MultiCell(2.7, 0.8, 'Total TTC', 1, 'C', 1);
        $this->SetXY(16.8, 19);
        $this->MultiCell(3.1, 0.8, 'Net à payer', 1, 'C', 1);



        $this->setFillColor(255, 255, 255);
        $this->setFont('Arial', '', 10);
        $this->SetXY(1.1, 19.8);
        $this->MultiCell(2.1, 0.8, '19,6', 1, 'R', 1);
        $this->SetXY(3.2, 19.8);
        $this->MultiCell(2.4, 0.8, '1', 1, 'R', 1);
        $this->SetXY(5.6, 19.8);
        $this->MultiCell(3.1, 0.8, $tva, 1, 'R', 1);
        $this->SetXY(8.7, 19.8);
        $this->MultiCell(2.7, 0.8, $cost, 1, 'R', 1);
        $this->SetXY(11.4, 19.8);
        $this->MultiCell(2.7, 0.8, $tva, 1, 'R', 1);
        $this->SetXY(14.1, 19.8);
        $this->MultiCell(2.7, 0.8, $total, 1, 'R', 1);
        $this->SetXY(16.8, 19.8);
        $this->MultiCell(3.1, 0.8, $total, 1, 'R', 1);

        $this->setFont('Arial', 'I', 8);
        $this->setXY(17, 21);
        
        $total  = $total * 6.55957;
        
        if(strstr($total, '.'))
        {

            $ttotal = explode('.', $total);
            $total = $ttotal[0] . ',' . substr($ttotal[1], 0, 2);
        }

        $this->Cell(10, 0.8, '(Soit ' . $total . ' francs )');
    }
}

$pdf = new PDF('P', 'cm', 'A4');
$pdf->CreateFacture($row);
$pdf->Output();

?>

