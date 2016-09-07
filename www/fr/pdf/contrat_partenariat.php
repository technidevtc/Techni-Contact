<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();
//extract(unserialize(file_get_contents('datas.txt')));
ob_start();
?> 
<style>
	table {width:100%;font-family: Arial, Helvetica, sans-serif;line-height:6mm;}
</style>

<?php
	// $id_estimate   =  "1126299538";
	$sql_estimate  =  "SELECT societe,adresse,cp,ville,pays,total_ht ,updated_mail_sent_pdf , client_id
					   FROM   estimate
					   WHERE  id='".$id_estimate."' ";
	$req_estimate  =   mysql_query($sql_estimate);
	$data_estimate =   mysql_fetch_object($req_estimate);
	//<img src="fr/manager/images/logo.jpg" />
	

	$sql_users  = "SELECT name ,uu.phone,fax,uu.email
					FROM  bo_users uu, estimate ee
					WHERE ee.updated_user_id = uu.id
					AND ee.id='".$id_estimate."' ";
	$req_users  =  mysql_query($sql_users);
	$data_users =  mysql_fetch_object($req_users);
//<img src="fr/manager/images/logo-devis-pdf.jpg" />
					
?>

<page backtop="20mm" backleft="20mm" backright="10mm" backbottom="30mm">
	<page_header>
		<div style="margin-left:72px;margin-top:10px"><img src="fr/manager/images/logo-devis-pdf.jpg" /></div>
	</page_header>
	<page_footer>
		<p style="text-align: center;width=100%;">
			M.D2i – 253 rue Gallieni – F-92774 BOULOGNE BILLANCOURT cedex<br />
			S.A.S. au capital de 160.000 € - RCS Nanterre B 392 772 497 - TVA intra : FR 12 392 772 497<br />
			Tel : 01 55 60 29 29 – Fax : 01 83 62 36 12<br />
			http://www.techni-contact.com
		</p>
	</page_footer>
	<br /><br /><br />
	<div >
	<div style="text-align:center;font-weight: bold;font-size: 12pt;">Contrat de partenariat</div>
	<div style="text-align:center">à renvoyer signé et tamponné au <span style="font-weight: bold;">01 83 62 36 12</span></div>
	</div>
	
	<br /><br />
	<div>
		Date  : <?= date('d/m/Y', $data_estimate->updated_mail_sent_pdf) ?><br />
		Validité : 1 mois suivant date de réception
	</div>
	 <br /><br /> 
	<div style="background: #ddd;border-radius: 10px">
	<table style="vertical-align:top;padding:5px">
		<tr style="vertical-align:top; ">
			<td style="width:45%;">
				<tr>
					<td style="line-height:15px">
						<span style="font-weight: bold; font-style: italic;">Fournisseur</span><br /><br />
						Md2i <br />
						253 rue Gallieni<br />
						92774<br />
						Boulogne Billancourt Cedex<br />
						Tel : 01 55 60 29 29<br />
						RCS Nanterre B 392 772 497<br />
						TVA intra : FR 12 392 772 497<br /><br />
						<span style="font-weight: bold; ">Votre conseiller Partenaire :</span><br />
						<?= utf8_decode($data_users->name) ?><br />
						<?= utf8_decode($data_users->phone) ?><br />
						<a href="<?= utf8_decode($data_users->email) ?>"><?= utf8_decode($data_users->email) ?></a><br />
						
					</td>
				</tr>				
			</td>		
			<td style="line-height:15px">
				<tr>
					<td >
					<?php
						if(!empty($data_estimate->client_id))	$client_id  = "(".$data_estimate->client_id.")";
						else $client_id  = "";
					?>
						<span style="font-weight: bold; font-style: italic;">Partenaire <?= $client_id ?></span><br /><br />
						Société  : <?= utf8_decode($data_estimate->societe) ?><br />
						Site web : <br />
						Siret : <br />
						Adresse : <?= utf8_decode($data_estimate->adresse) ?> <br />
						CP : <?= utf8_decode($data_estimate->cp) ?><br />
						Ville : <?= utf8_decode($data_estimate->ville) ?> <br />
						Pays : <?= utf8_decode($data_estimate->pays) ?><br />
						Nom du contact : <br />
						Téléphone du contact : <br />
						Email du contact : <br /><br />
					</td>
				</tr>				
			</td>		
		</tr>
		<br />	
	</table>
	</div>
	<br />
	<div style="text-decoration : underline;">Description de la prestation :</div><br />
	<div>
		<p>Présentation des produits et des services du « Partenaire » sur <a href="www.techni-contact.com">www.techni-contact.com</a><br />
		Envoi par le « Fournisseur » de contacts commerciaux qualifiés au « Partenaire ».<br />
		Mise à disposition du « Partenaire » d’un extranet archivant les contacts reçus et permettant de gérer les fiches produit du « Partenaire ».</p>
	</div>
	<div>
		<p style="font-weight: bold;">Prix de la prestation : <?= $data_estimate->total_ht ?> € par contact envoyé. Aucune durée d’engagement.</p>
	</div>
	<br />
	<div style="text-decoration : underline;">Conditions spécifiques : </div><br />
	
	<div>
	<p>Si le <span  style="font-weight: bold;">contact reçu ne peut faire l’objet d’un devis</span>, d’une offre orale ou écrite par le « Partenaire », possibilité pour celui-ci de le <span style="font-weight: bold;">rejeter</span> via son « extranet ». Le contact ne sera alors pas facturé</p>
	</div>
	<br />
	<div>L’impossibilité de faire un devis couvre les cas suivants :</div>
	<div style="border-bottom: 2px solid #000;">
	<ul>
		<li>Contact injoignable après plusieurs tentatives</li>
		<li>Produit ou service demandé ne rentrant pas dans le cadre de l’activité du « Partenaire »</li>
		<li>Situation géographique du contact non couverte par le « Partenaire »</li>
		<li>Contact se révélant être un particulier</li>
	</ul>
	</div>
	<br />
	<div>
		Bon pour accord. <br />
		Je déclare avoir lu les CGV et les accepte.
	</div>
	<br />
	<table style="vertical-align:top;">
	<td style="width:50%;line-height:15px">
		Nom et fonction :  <br />
		Date  : <br />
		Signature : 
	</td>
	<td align="center" style="width:50%;line-height:15px;text-align:center">
		Cachet de l’entreprise
	</td>
	</table>
</page>
<?php
$content = ob_get_clean();
require(PDF2HTML.'html2pdf.class.php');

try{
	$pdf = new HTML2PDF('P','A4','fr',false,'UTF-8');
	$pdf->writeHTML($content);
	$pdf->Output(PDF_PATH.'Contrat-de-partenariat-techni-contact.pdf','F');
	// $pdf->Output('test.pdf');
}catch(HTML2PDF_exception $e){
	die($e);
}
?> 