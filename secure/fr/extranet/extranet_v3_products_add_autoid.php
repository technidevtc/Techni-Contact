<?php
	require_once('extranet_v3_functions.php'); 
	
	//Call the file of class => GenerateID
	require_once(ADMIN.'generator.php');
	

	if(!empty($_SESSION['extranet_user_id'])){
	
	
		$idProduct = generateID(1, 16777215, 'id', 'products', $db);
		
		
		$db->query("insert into products_add_adv 
					(id, idAdvertiser, idTC,
					name, fastdesc, type,
					families, keywords, descc,
					descd, timestamp, reject,
					refSupplier, price, price2,
					unite, marge, idTVA, 
					delai_livraison, contrainteProduit, tauxRemise,
					ref, ean, warranty, 
					title_tag, meta_desc_tag, shipping_fee,
					productsLinked, video_code)
					
					values(".$idProduct.", ".$_SESSION['extranet_user_id'].", 0,
					'##########', 'Insertion automatique Ajout piece jointe ".date('Y-m-d h:i:s')."', 'c',
					'0', '', '',
					'', ".time().", 0,
					'', '', '', 
					1, 0, 1,
					'', 0, '',
					'', '', '',
					'', '', '',
					'', '')", __FILE__, __LINE__);
					
					
		//Second Query for the table "products_extranet_history"
		//Small Query other fields will be automaticaly filled
		$db->query("insert into products_extranet_history 
					(id_demande, p_add_adv___id, p__idAdvertiser,
					date_demande, user_operation)
					
					values(NULL, ".$idProduct.", ".$_SESSION['extranet_user_id'].",
					NOW(), 'Brouillon ajout')", __FILE__, __LINE__);
		
					
		echo $idProduct;
		
	
	}else{
		echo('-1');
	}
	
	
?>