<?php
	require_once('extranet_v3_functions.php'); 
	require_once(ADMIN.'logo.php');

	$id_fiche		= mysql_escape_string($_POST['idf']);
	$operation		= mysql_escape_string($_POST['operation']);
	
	$message 		= 'Erreur, merci de r&eacute;essayer !';
	
	if(!empty($_SESSION['extranet_user_id']) && !empty($id_fiche)){

		//Declaration of the variables folders
		
		//$dir_INCLUDES 	= PRODUCTS_IMAGE_INC;
		$dir_INCLUDES 		= PRODUCTS_IMAGE_ADV_INC;
		
		//Dir url Linux && Windows to have the possibility to view to pictures
		//Because Windows can not make a shortcut references 
		//"ex: ../../" => "c://folder/folder/.."
		if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
			//$dir_URL 		= 'http://local-pictures-products.techni-contact.com/';
			$dir_URL 		= 'http://local-pictures-products-adv.techni-contact.com/';
		}else{
			//$dir_URL 		= PRODUCTS_IMAGE_URL;
			$dir_URL 		= PRODUCTS_IMAGE_ADV_URL;
		}

		$num	= 1;


		//If the product has pictures
		//We duplicate them to products_adv (to not change on prod pictures)
		
		if(strcmp($operation,'new')==0){
		
			$num = 1;
			//Deleting the existing pictures on the "products_adv"
			while(is_file($dir_INCLUDES."zoom/".$id_fiche."-".$num.".jpg")){
			
				$fileName = $id_fiche."-".$num.".jpg";
				unlink($dir_INCLUDES."zoom/".$fileName);
				unlink($dir_INCLUDES."card/".$fileName);
				unlink($dir_INCLUDES."thumb_big/".$fileName);
				unlink($dir_INCLUDES."thumb_small/".$fileName);
			
				$num++;
			}//end while
			
			//Copying the pictures on "products" to "products_adv" folder
			$num = 1;
			while (is_file(PRODUCTS_IMAGE_INC."zoom/".$id_fiche."-".$num.".jpg")) {
				$fileName = $id_fiche."-".$num.".jpg";
				copy(PRODUCTS_IMAGE_INC."zoom/".$fileName, PRODUCTS_IMAGE_ADV_INC."zoom/".$fileName);
				copy(PRODUCTS_IMAGE_INC."card/".$fileName, PRODUCTS_IMAGE_ADV_INC."card/".$fileName);
				copy(PRODUCTS_IMAGE_INC."thumb_big/".$fileName, PRODUCTS_IMAGE_ADV_INC."thumb_big/".$fileName);
				copy(PRODUCTS_IMAGE_INC."thumb_small/".$fileName, PRODUCTS_IMAGE_ADV_INC."thumb_small/".$fileName);
				$num++;
			}
			$num = 1;
		
		}//end if
		
		if (!is_file($dir_INCLUDES."zoom/".$id_fiche."-".$num.".jpg")) {
			
		}//end if
	
	
		
		//Start loading pictures (pending and duplicated from the prod !)
		
		echo('<div id="product_pictures_uploaded_change_order_msg"></div>');
		echo('<span id="product_pictures_uploaded_change_flag" style="display: none;">1</span>');
		echo('<ul id="product_pictures_uploaded_ul">');
			while (is_file($dir_INCLUDES."thumb_small/".$id_fiche."-".$num.".jpg")) {
		
				echo('<li id="ppictures_list_'.$num.'" ');
					if($num==1){
					
						echo('class="product_pictures_first">');
							echo('<div class="picture_add_title">Photo principale</div>');
							echo('<a rel="gallery" class="fancybox" href="'.$dir_URL.'zoom/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" title="Photo principale">');
								echo('<img src="'.$dir_URL.'thumb_big/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" alt="Photo principale" title="Photo principale" />');
							echo('</a>');
							
					}else{
					
						echo('class="product_pictures_other">');
							echo('<div class="picture_add_title">Photo num&eacute;ro '.$num.'</div>');
							
							echo('<a rel="gallery" class="fancybox" href="'.$dir_URL.'zoom/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" title="Photo num&eacute;ro '.$num.'">');
								echo('<img src="'.$dir_URL.'thumb_small/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" alt="Photo num&eacute;ro '.$num.'" title="Photo num&eacute;ro '.$num.'" style="float:left;"/>');
							echo('</a>');
							
					}
					//echo('<img src="'.$dir_URL.'thumb_small/'.$id_fiche.'-'.$num.'.jpg?r='.time().'" alt="Image num&eacute;ro '.$num.'" title="Image num&eacute;ro '.$num.'" />');
					
					echo('<img src="ressourcesv3/icons/cross.png" alt="Supprimer" title="Supprimer" class="delete_listner" onclick="javascript:products_delete_this_picture(\''.$id_fiche.'\',\''.$num.'\', \'edit\');" />');
				echo('</li>');	
			
				$num++;
			}//end while
		
		echo('</ul>');
		

	}else{
		echo('-1');
	}//end if empty
	
	//echo('<br />End loading !');
	
?>