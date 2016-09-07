<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN  . 'logs.php');
require(ADMIN . 'products.php');
require(ADMIN . 'advertisers.php');
require(ADMIN . 'tva.php');

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
    header('Location: ' . ADMIN_URL . 'login.html');
    exit();
}

function getRand($len, $type = 'all')
{
	if ($len < 1) $len = 1;
	elseif ($len > 32) $len = 32;
	
	switch ($type)
	{
		case 'all' : $acceptedChars = 'azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN0123456789'; break;
		case 'abc' : $acceptedChars = 'azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN'; break;
		case 'num' : $acceptedChars = '0123456789'; break;
		default    : $acceptedChars = 'azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN0123456789';
	}
	
	$max = strlen($acceptedChars)-1;
	$value = null;
	for($i=0; $i < $len; $i++)
	{
		$value .= $acceptedChars{mt_rand(0, $max)};
	}
	return $value;
}

$suppliers = & GetSuppliersInfos($handle, 'order by a.nom1');

$pdt_num_rand = getRand(3, 'num');

$nom				= 'Produit test ' . $pdt_num_rand;
$fastdesc			= 'Produit test ' . $pdt_num_rand . ' fastdesc';

// random advertiser
$listadv = array();
$result = $handle->query('select id from advertisers where parent = 61049');
while ($record = & $handle->fetch($result)) $listadv[] = $record[0];
$advertiser = $listadv[mt_rand(0, count($listadv)-1)];

// random familiesHidden
$listfam = array();
$result = $handle->query('select id from families');
while ($record = & $handle->fetch($result)) $listfam[] = $record[0];
$_familiesHidden = '';	$rand_nb = mt_rand(1,10);
for ($i = 0; $i < $rand_nb ; $i++)
$familiesHidden .= $listfam[mt_rand(0, count($listfam)-1)] . ',';

$alias				= 'Produit test ' . $pdt_num_rand . ' alias';
$keywords			= 'test, retest';
$desc				= 'Produit test ' . $pdt_num_rand . ' desc';
$descd				= 'Produit test ' . $pdt_num_rand . ' descd';
$type1				= '.doc';
$type2				= '.pdf';
$type3				= '';
$gen				= true;
$ind				= true;
$col				= true;

// random productsHidden
$listprd = array();
$result = $handle->query('select id from products');
while ($record = & $handle->fetch($result)) $listprd[] = $record[0];
$productsHidden = '';	$rand_nb = mt_rand(0,10);
for ($i = 0; $i < $rand_nb ; $i++)
$productsHidden .= $listprd[mt_rand(0, count($listprd)-1)] . ',';

// $typeprice 1 = 'sur demande' | 2 = 'sur devis' | 3 = 'nous contacter' | 4 = 'ref' | autre = prix par défaut
$typeprice          = 4;
$refSupplier		= 'REF' . getRand(9);
$price				= getRand(5, 'num') . ',' . getRand(2, 'num');
$price2				= getRand(5, 'num') . ',' . getRand(2, 'num');
$margeRemise		= getRand(2, 'num') . ',' . getRand(2, 'num');
$idTVA				= '2';
$contrainteProduit	= getRand(3, 'num');
$tauxRemise			= '200<->10<_>1000<->15';
if ($tauxRemise != '')
{
	$tauxRemise_tab = array();
	for ($i = 0; $i < count($tauxRemise_lines); ++$i)
	{
		$tauxRemise_line = explode('<->', $tauxRemise_lines[$i]);
		for ($j = 0 ; $j < count($tauxRemise_line) ; ++$j)
		{
			$tauxRemise_tab[] = $tauxRemise_line[$j];
		}
	}
	$tauxRemise_update = serialize($tauxRemise_tab);
}
else
{
	$tauxRemise_update = '';
}
			
$user_login			= $user->login;
$save				= 0;
$code_ref			=
'15<=>Référence TC<->Libellé<->Référence Fournisseur<->color<->1<->2<->3<->4<->5<->6<->7<->Taux TVA<->Prix Fournisseur<->Marge<->Prix' .
'<_>474476607<->jdfgh<->fgkfgjghjgh<->sdg<->sdg<->sdg<->sdg<->sdg<->sdg<->sdg<->sdg<->1<-><-><->100' .
'<_>888886245<->Libefgjghllé<->fgkfghjgh<->liguy<->sdg<->sdg<->sdg<->liguy<->sdg<->sdg<->sdg<->2<-><-><->150' .
'<_>628640636<->fghkfgh<->fgkfghjgh<->ze<->sdg<->sdg<->sdg<->ze<->sdg<->sdg<->sdg<->1<-><->15<->';

$supplierInfos		= $suppliers[$advertiser];

//$id = addProduct($handle, $nom, $fastdesc, $advertiser, $familiesHidden, $alias, $keywords, $desc, $descd, $type1, $type2, $type3, $gen, $ind, $col, $productsHidden, $refSupplier, $price, $price2, $margeRemise, $idTVA, $contrainteProduit, $tauxRemise_update, $user_login, $save, '', '', $code_ref, $supplierInfos);
$id = 5921864;

if ($id)
{
// $type_a = '' | 'add' | 'edit' | 'backup' | 'add_adv' | 'edit_adv'
$type_a =  'add';
?>
<html>
<head>
	<title>test edit produit</title>
	<style type="text/css">
input.test {
	width: 500px; border: 1px solid #CCCCCC; font: 11px arial, helvetica, sans-serif; padding: 1px; margin: 0; background-color: #E0E0E0; text-align: left;
}
input.check {
	padding: 1px; margin: 0; background-color: #E0E0E0;
}
input.go {
	border: 1px solid #CCCCCC; font: bold 13px verdana, helvetica, sans-serif; padding: 2px 120px; background-color: #E0E0E0;
}

div.intitule {
	width: 120px; font: 11px arial, helvetica, sans-serif; padding: 0px 5px; margin: 0px 5px; float: left; border-bottom: 1px solid #CCCCCC;
}

div.title {
	width: 800px; font: bold 16px verdana, helvetica, sans-serif; padding: 5px 100px; margin: 0 auto; background-color: #D8D8F0; text-align: center;
}
div.line {
	width: 800px; font: 11px arial, helvetica, sans-serif; padding: 2px 100px; margin: 0 auto; background-color: #F0F0F0; text-align: left;
}

div.top {
	width: 800px; font: 12px verdana, helvetica, sans-serif; padding: 2px 100px; margin: 0 auto; background-color: #F0F0F0; text-align: left;
}

	</style>
</head>
<body>
	<form name="addProduct" method="post" action="edit.php?<?php print(session_name() . '=' . session_id() . '&type=' . $type_a . '&id=' . $id) ?>" class="formulaire" enctype="multipart/form-data">
		<div class="title">Procédure de test pour le fichier home\technico\secure\manager\products\edit.php</div>
		<div class="line">&nbsp;</div>
		<div class="top"><i>id : </i><b>'<?php echo $id ?>'</b></div>
		<div class="top"><i>type_a : </i><b>'<?php echo $type_a ?>'</b></div>
		<div class="line">&nbsp;</div>
		<div class="line"><div class="intitule">nom</div>			<input class="test" type="text" name="nom" value="<?php echo to_entities($nom) ?>"></div>
		<div class="line"><div class="intitule">fastdesc</div>		<input class="test" type="text" name="fastdesc" value="<?php echo to_entities($fastdesc) ?>"></div>
		<div class="line"><div class="intitule">idAdvertiser</div>	<input class="test" type="text" name="advertiser" value="<?php echo to_entities($advertiser) ?>"></div>
		<div class="line"><div class="intitule">family</div>		<input class="test" type="text" name="familiesHidden" value="<?php echo to_entities($familiesHidden) ?>"></div>
		<div class="line"><div class="intitule">alias</div>			<input class="test" type="text" name="alias" value="<?php echo to_entities($alias) ?>"></div>
		<div class="line"><div class="intitule">keywords</div>		<input class="test" type="text" name="keywords" value="<?php echo to_entities($keywords) ?>"></div>
		<div class="line"><div class="intitule">desc</div>			<input class="test" type="text" name="desc" value="<?php echo to_entities($desc) ?>"></div>
		<div class="line"><div class="intitule">descd</div>			<input class="test" type="text" name="descd" value="<?php echo to_entities($descd) ?>"></div>
		<div class="line"><div class="intitule">type1</div>			<input class="test" type="text" name="type1" value="<?php echo to_entities($type1) ?>"></div>
		<div class="line"><div class="intitule">type2</div>			<input class="test" type="text" name="type2" value="<?php echo to_entities($type2) ?>"></div>
		<div class="line"><div class="intitule">type3</div>			<input class="test" type="text" name="type3" value="<?php echo to_entities($type3) ?>"></div>
		<div class="line">&nbsp;</div>
		<div class="line"><div class="intitule">gen</div>			<input class="check" type="checkbox" name="gen"<?php echo $gen ? ' selected' : '' ?>></div>
		<div class="line"><div class="intitule">ind</div>			<input class="check" type="checkbox" name="ind"<?php echo $ind ? ' selected' : '' ?>></div>
		<div class="line"><div class="intitule">col</div>			<input class="check" type="checkbox" name="col"<?php echo $col ? ' selected' : '' ?>></div>
		<div class="line">&nbsp;</div>
		<div class="line"><div class="intitule">typeprice</div>		<input class="test" type="text" name="typeprice" value="<?php echo to_entities($typeprice) ?>"></div>
		<div class="line"><div class="intitule">refSupplier</div>	<input class="test" type="text" name="refSupplier" value="<?php echo to_entities($refSupplier) ?>"></div>
		<div class="line"><div class="intitule">price</div>			<input class="test" type="text" name="price" value="<?php echo to_entities($price) ?>"></div>
		<div class="line"><div class="intitule">price2</div>		<input class="test" type="text" name="price2" value="<?php echo to_entities($price2) ?>"></div>
		<div class="line"><div class="intitule">margeRemise</div>	<input class="test" type="text" name="marge" value="<?php echo to_entities($margeRemise) ?>"></div>
		<div class="line"><div class="intitule">margeRemise</div>	<input class="test" type="text" name="remise" value="<?php echo to_entities($margeRemise) ?>"></div>
		<div class="line"><div class="intitule">idTVA</div>			<input class="test" type="text" name="idTVA" value="<?php echo to_entities($idTVA) ?>"></div>
		<div class="line"><div class="intitule">cont. Produit</div>	<input class="test" type="text" name="contrainteProduit" value="<?php echo to_entities($contrainteProduit) ?>"></div>
		<div class="line"><div class="intitule">tauxRemise</div>	<input class="test" type="text" name="tauxRemise" value="<?php echo to_entities($tauxRemise) ?>"></div>
		<div class="line"><div class="intitule">code_ref</div>		<input class="test" type="text" name="code_ref" value="<?php echo to_entities($code_ref) ?>"></div>
		<div class="line">&nbsp;</div>

		<div class="line" style="text-align: center"><input class="go" type="submit" value="Valider" name="ok"></div>
		<div class="line">&nbsp;</div>

	</form>
</body>
</form>

<?php
}
?>