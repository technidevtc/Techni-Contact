<?php

// col headers
$headers = array(
  'ID Article',
  'ID Produit',
  'Stock',
  'Prix',
  'Devise',
  'URL Image'
);
fputcsv($fh, $headers, ';');

set_time_limit(120); // query could be long, preventing timeout

//Modification on 06/08/2014 Add condition to exclude products that have ([products > as_estimate =1] OR [advertisers > as_estimate = 1])
//First modification in INNER JOIN advertisers => AND a.as_estimate!=1
//Second modification in INNER JOIN => AND p.as_estimate!=1
$sth = $db->prepare("
  SELECT
    rc.id,
    rc.price,
    p.id AS pdt_id,
    pfr.ref_name AS pdt_ref_name,
	
	p.as_estimate AS pdt_as_estimate,
	a.as_estimate AS a_as_estimate
	
  FROM references_content rc
  INNER JOIN products p ON p.id = rc.idProduct AND p.as_estimate!=1
  INNER JOIN products_fr pfr ON pfr.id = p.id AND pfr.active = 1 AND pfr.deleted = 0
  INNER JOIN advertisers a ON a.id = p.idAdvertiser AND a.actif = 1 AND a.category = ".__ADV_CAT_SUPPLIER__." AND a.as_estimate!=1
  INNER JOIN (
    SELECT idProduct, idFamily
    FROM products_families
    ORDER BY orderFamily ASC
  ) pff ON pff.idProduct = p.id
  WHERE 
	rc.vpc = 1 
  AND 
	rc.deleted = 0	
  GROUP BY rc.id");
$sth->execute();
while ($ref = $sth->fetch(PDO::FETCH_ASSOC)) {

	//Modification on 06/08/2014
	//Put 0 in price for the products that have 
	//([products > as_estimate =1] OR [advertisers > as_estimate = 1])
	if(strcmp($ref["pdt_as_estimate"],'1')!==0 && strcmp($ref["a_as_estimate"],'1')!==0){
		$refValues = array(
			$ref['id'],
			$ref['pdt_id'],
			'1',
			trim(preg_replace('`,`', '.', $ref['price'])),
			'€',
			Utils::get_pdt_pic_url($ref['pdt_id'], 'card', 1, $ref['pdt_ref_name'])
		  );
	}else{
		$refValues = array(
			$ref['id'],
			$ref['pdt_id'],
			'1',
			'0',
			'€',
			Utils::get_pdt_pic_url($ref['pdt_id'], 'card', 1, $ref['pdt_ref_name'])
		  );
	}//end else if strcmp
  
  fputcsv($fh, $refValues, ';');
}

$sth = $db->prepare("
  SELECT
    p.id,
    p.idTC,
    pfr.ref_name
  FROM products p
  INNER JOIN products_fr pfr ON pfr.id = p.id AND pfr.active = 1 AND pfr.deleted = 0
  INNER JOIN advertisers a ON a.id = p.idAdvertiser AND a.actif = 1
  INNER JOIN (
    SELECT idProduct, idFamily
    FROM products_families
    ORDER BY orderFamily ASC
  ) pff ON pff.idProduct = p.id
  GROUP BY p.id");
$sth->execute();
while ($pdt = $sth->fetch(PDO::FETCH_ASSOC)) {
  $pdtValues = array(
    $pdt['idTC'],
    $pdt['id'],
    '1',
    '0',
    '€',
    Utils::get_pdt_pic_url($pdt['id'], 'card', 1, $pdt['ref_name'])
  );
  fputcsv($fh, $pdtValues, ';');
}
