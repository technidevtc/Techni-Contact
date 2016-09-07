<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN  . 'logs.php');

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
    header('Location: ' . SECURE_URL);
    exit;
}



function & loadProduct(& $handle, $id, $type)
{
    $ret = false;

    switch($type)
    {
        case 'backup'  : $letter = 'b'; $table = 'products_add'; break;
        case 'add'     : $letter = 'c'; $table = 'products_add'; break;
        case 'edit'    : $letter = 'm'; $table = 'products_add'; break;
        case 'add_adv' : $letter = 'c'; $table = 'products_add_adv'; break;
        case 'edit_adv': $letter = 'm'; $table = 'products_add_adv'; break;
    }

    if(($result = $handle->query('select *, (select category from advertisers where id = idAdvertiser) as adv_cat, (select MIN(price) from references_content where tabl.id = idTC) as ref_price from ' . $table . ' tabl where type = \'' . $letter . '\' and id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
         $ret = & $handle->fetchAssoc($result);
         //$ret = & $handle->fetch($result);
    }
    return $ret;
}


if(!isset($_GET['id']) || !isset($_GET['type']) ||
   !preg_match('/^[0-9]+$/', $_GET['id']) || ($_GET['type'] != 'backup' && $_GET['type'] != 'add' && $_GET['type'] != 'add_adv' && $_GET['type'] != 'edit' && $_GET['type'] != 'edit_adv') ||
   !($p = & loadProduct($handle, $_GET['id'], $_GET['type'])))
{
    header('Location: ' . URL);
    exit;
}


function cleanDescHtml($str){
  $descc3 = preg_replace("`<font .*>`siU", '', $str);
  $descc2 = preg_replace("`</font.*>`siU", '', $descc3);
  $descc = preg_replace("`style=\".*\"`siU", '', $descc2);
  return $descc;
}


// Charger les réf si nécessaire

if($p['price'] == 'ref')
{
	$tab_ref_cols = array();
	$tab_ref_lines = array();

	$letter_ref = ($_GET['type'] == 'add') ? 'c' : (($_GET['type'] == 'backup') ? 'b' : 'm'); 
	
    if($result = & $handle->query('select ref from products_add where id = \'' . $handle->escape($_GET['id']) . '\' and type = \'' . $letter_ref . '\'', __FILE__, __LINE__))
	{
	    $data_ref = & $handle->fetch($result);
		$ref = $data_ref[0];
		
		// Séparer nb colonnes du reste
		$nbCols_all_tab = explode('<=>', $ref);		
						
		// Séparer liste colonnes et liste valeur chaque ligne
		$cols_lines_tab = explode('<_>', $nbCols_all_tab[1]);
	
		// Obtenir liste colonnes
		$elts = explode('<->', $cols_lines_tab[0]);
					
		for($i = 0; $i < $nbCols_all_tab[0]; ++$i)
		{ 
			$tab_ref_cols[] = $elts[$i];
		}
			
		for($i = 1; $i < count($cols_lines_tab); ++$i)
		{
			$elts = explode('<->', $cols_lines_tab[$i]);
			$ref_content = array();
					
			for($j = 0; $j < count($elts); ++$j)
			{
				$ref_content[] = $elts[$j];
			}
			
			$tab_ref_lines[] = $ref_content;
								
		}
	}	

}

// Fin chargement références


define('PRODUCTS', true);

$title      = $p['name'];
$meta_desc = $meta_desc = '';

require(SITE . 'head.php');

$i_dir_inc = ($_GET['type'] == 'add_adv' || $_GET['type'] == 'edit_adv') ? PRODUCTS_IMAGE_ADV_INC : PRODUCTS_IMAGE_INC;
$f_dir_inc = ($_GET['type'] == 'add_adv' || $_GET['type'] == 'edit_adv') ? PRODUCTS_FILES_ADV_INC : PRODUCTS_FILES_INC;

$i_url_inc = ($_GET['type'] == 'add_adv' || $_GET['type'] == 'edit_adv') ? 'see_adv_files.php?' . session_name() . '=' . session_id() . '&type=image&id=' . $_GET['id'] : PRODUCTS_IMAGE_URL .'card/'. $_GET['id'] . '-1.jpg';
$f_url_inc = ($_GET['type'] == 'add_adv' || $_GET['type'] == 'edit_adv') ? 'see_adv_files.php?' . session_name() . '=' . session_id() . '&type=file&file=' : PRODUCTS_FILE_URL;

// set if product is set as default estimate
$saleable = true;
if($p['price'] == 'sur demande' || $p['price'] == 'sur devis' || $p['price'] == 'nous contacter' || $p['price'] === 0)
    $saleable = false;

?> 
<style>
  .localisation{position: relative; top: -25px}
</style>
<div class="white-bg">
               <!--<img id="bandeau" src="<?php print(URL) ?>images/bandeaux/b_<?php print($data[0]) ?>_fr.jpg" alt="" />-->
               <div class="localisation">Vous êtes ici : <a href="<?php print(URL) ?>">Accueil</a> &raquo; Prévisualisation d'une fiche produit</div>
               <div class="product-page">
                <div class="product-page-title">
                  <h1 class="bigger-blue-title"><?php echo to_entities($p['name']) ?></h1>
                  <h2 class="medium-blue-title"><?php echo to_entities($p['fastdesc']) ?></h2>
                </div>
                 
                 
                 <div id="product-sheet" class="product-page-main-zone">
            <div class="product-page-pic-zone fl">
              <div class="product-page-pdt-code">Code fiche produit : <span class="color-blue"><?php echo $p['id'] ?></span></div>
              <div class="product-page-picture">
                <img data-index="0" class="vmaib" alt="<?php echo $p['name'] ?>" src="<?php echo is_file($i_dir_inc .'zoom/'. $_GET['id'] . '-1.jpg') ? $i_url_inc : PRODUCTS_IMAGE_URL.'no-pic-card.gif' ?>"><div class="vsma"></div>
              </div>
                          </div>
            
                       <div class="product-page-text-zone fr">
              <div class="product-page-main-zone-desc">
                  <?php if (($saleable) || ($p['adv_cat'] == __ADV_CAT_SUPPLIER__ )) { ?>
                  <div class="conseils-d-experts">
                  <img alt="Bénéficiez du conseil d'experts sur ce produit" src="<?php echo $res_url; ?>images/picto-supplier.png" class="vmaib fl">
                  <span class="fr">Bénéficiez du conseils<br />d'experts sur ce produit</span>
                  </div>
                <?php } else { ?>
                  <div class="plusieurs-devis">
                  <img alt="Gagnez du temps en recevant plusieurs devis" src="<?php echo $res_url; ?>images/picto-advertiser.png" class="vmaib fl">
                  <span class="fr">Gagnez du temps,<br /> en recevant plusieurs devis</span>
                  </div>
                <?php } ?>
              <div class="blue-small-title margin-top-5">Description</div>
                <h3 class="product-paragraph">
                  <?php echo htmlentities(substr(preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($p["descc"], FILTER_SANITIZE_STRING), ENT_QUOTES)),0,220))."..."; ?>
                  <a class="color-blue" href="#product-desc">Lire la suite</a>
                </h3>
              </div>
                         
              <div class="product-page-main-zone-left fl">
                <?php if ($saleable) { ?>
                  <div class="cat3-checked-line"><img alt="check" src="<?php echo $res_url; ?>images/green-check.png">Frais de port: <?php echo $p["shipping_fee"]; ?></div>
                  <div class="cat3-checked-line"><img alt="check" src="<?php echo $res_url; ?>images/green-check.png">Commande minimum: <?php echo ($p["adv_min_amount"] > 0 ? sprintf("%.0f", $p["adv_min_amount"])."€" : "non"); ?></div>
                  <div class="cat3-checked-line"><img alt="check" src="<?php echo $res_url; ?>images/green-check.png">Livraison: <?php echo $p["delivery_time"]; ?></div>
                  <div class="cat3-checked-line"><img alt="check" src="<?php echo $res_url; ?>images/green-check.png">Garantie: <?php echo $p["warranty"]; ?></div>
                  <div class="zero"></div>
                <?php } ?>
                
                <?php if($p["docs_count"] > 0 ||  !empty ($p['average_note'])): ?>
                <div class="blue-small-title product-page-notation-docs-links blue-smaller-title ">Avis / Doc</div>
                <?php endif; ?>
                <?php if(!empty ($p['average_note'])) {
                  echo '<div class="cat3-checked-line"><img src="'.URL.'ressources/images/picto-avis.png" alt="picto-avis" /> Avis client ';
                  showStarRater($p['average_note']);
                  echo ' <a class="color-blue" href="#block-product-notation">Plus d\'infos</a></div>';
                } ?>
                <?php if ($p["docs_count"] > 0) { ?>
                  <div class="cat3-checked-line">
                    <img src="<?php echo $res_url; ?>images/picto-docs.png" alt="picto-docs" />
                    Documentation produit  <a class="color-blue" href="#block-product-document">Plus d'infos</a>
                  </div>
                <? } ?>
              </div>

              
             <div class="product-page-main-zone-right fr">
                <?php //var_dump($p["adv_cat"], $saleable, $p_set_as_estimate, $p["hasPrice"]); ?>
               <?php if ($saleable) : ?>
                    <?php if ($p["ref_count"] > 1) : // faire affichage tableau prix en calque pour mise au panier ?>
                    <div class="product-page-price">
                      à partir de : <div><?php echo sprintf("%.2f",$p["price"])."€ HT"; ?></div>
                    </div>
                    <div class="product-page-action">
                      <a class="btn-cart-add-big-pink" href="<?php echo $p["cart_add_url"]; ?>"></a><br />
                      <a href="" class="btn-esti-ask ask-estimate-link"><img alt="" src="<?php echo $res_url; ?>images/puce-estimate-small.png" class="fl">Demander un devis</a>
                      <div class="zero"></div>
                      <?php /*<a href="<?php echo $url.'pdf/commande-fax/'.$p["cat3_id"].'-'.$p['id']; ?>"><div class="puce puce-7"></div>Commander par FAX</a>*/ ?>
                      <div class="zero"></div>
                      <div class="savedProductsListZone_<?php echo $p['id'] ?>">
                        <?php $productList = new ProductsSavedList();
                        if(true == false) : ?>
                          <div class="puce puce-9"></div><span class="color-green">Produit sauvegardé</span>
                          <a href="" class="color-blue">Voir liste</a>
                        <?php else : ?>
                          <a href="" class="btn-users-product-list"><div class="puce puce-5"></div>Sauvegarder ce produit</a>
                          <div class="zero"></div>
                        <?php endif; ?>
                      </div>
                    </div>

                    <?php else : // one ref ?>
                    <div class="product-page-price">
                      <div><?php echo sprintf("%.2f",$p["price"])."€ HT"; ?></div>
                    </div>
                    <div class="product-page-action">
                      <a class="btn-cart-add-small-single" href="<?php echo $p["cart_add_url"]; ?>"></a><br />
                      <a href="" class="btn-esti-ask ask-estimate-link"><img alt="" src="<?php echo $res_url; ?>images/puce-estimate-small.png" class="fl">Demander un devis</a><br />
                      <div class="zero"></div>
                      <?php /*<a href="<?php echo url.'pdf/commande/'.$p['web_id']; ?>"><div class="puce puce-7"></div>Commander par FAX</a><br />*/ ?>
                      <div class="zero"></div>
                      <div class="savedProductsListZone_<?php echo $p['id'] ?>">
                        <?php $productList = new ProductsSavedList();
                        if(true == false) : ?>
                          <div class="puce puce-9"></div><span class="color-green">Produit sauvegardé</span>
                          <a href="" class="color-blue">Voir liste</a>
                        <?php else : ?>
                      <a href="" class="btn-users-product-list"><div class="puce puce-5"></div>Sauvegarder ce produit</a>
                      <div class="zero"></div>
                       <?php endif; ?>
                      </div>
                    </div>
                    <?php endif // ref count ?>
                
               <?php else : // not saleable ?>
                <?php if ($p["hasPrice"]) : ?>
                <div class="product-page-esti">
                  Prix indicatif : <div><?php echo sprintf("%.2f",$p["price"])."€ HT"; ?></div>
                </div>
                <div class="product-page-action">
                  <a href="<?php echo $p["cart_add_url"]; ?>" class="btn-esti-ask-orange" data-adv-type="<?php echo $p["adv_cat"]; ?>"></a><br />
                  <div class="savedProductsListZone_<?php echo $p['id'] ?>">
                        <?php $productList = new ProductsSavedList();
                        if(true == false) : ?>
                          <div class="puce puce-9"></div><span class="color-green">Produit sauvegardé</span>
                          <a href="" class="color-blue">Voir liste</a>
                        <?php else : ?>
                          <a href="" class="btn-users-product-list"><div class="puce puce-5"></div>Sauvegarder ce produit</a>
                  <div class="zero"></div>
                  <?php endif; ?>
                      </div>
                </div>
                <?php else : // no price ?>
                <div class="product-page-esti">
                  Prix :<div> sur devis</div>
                </div>
                <div class="product-page-action">
                  <a href="<?php echo $p["cart_add_url"]; ?>" class="btn-esti-ask-orange"  data-adv-type="<?php echo $p["adv_cat"]; ?>"></a><br />
                  <div class="savedProductsListZone_<?php echo $p['id'] ?>">
                        <?php $productList = new ProductsSavedList();
                        if(true == false) : ?>
                          <div class="puce puce-9"></div><span class="color-green">Produit sauvegardé</span>
                          <a href="" class="color-blue">Voir liste</a>
                        <?php else : ?>
                          <a href="" class="btn-users-product-list"><div class="puce puce-5"></div>Sauvegarder ce produit</a>
                  <div class="zero"></div>
                  <?php endif; ?>
                      </div>
                </div>
                <?php endif // has price ?>
               <?php endif // saleable ?>
              </div>
              
              
              <div class="zero"></div>
              <div class="product-page-reseaux-sociaux">
                <!-- FB like button -->
                <iframe scrolling="no" frameborder="0" allowtransparency="true" style="border:none; overflow:hidden; width:90px; height:21px; padding: 2px 0 0 12px; float: left" src="http://www.facebook.com/plugins/like.php?locale=fr_FR&amp;app_id=249257591754502&amp;href=http://www.techni-contact.com/&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21"></iframe>
                <!-- FB like button -->
                <a class="option option-print fl" onclick="pageTracker._trackEvent('Lien', 'Lien-fiche-produit', 'Imprimer-fiche-produit');" href="">
                </a>
                                <!-- AddThis Button BEGIN -->
                <a href="http://www.addthis.com/bookmark.php?v=300&amp;pubid=technicontact" class="addthis_button"><img style="border:0" alt="Partager avec un collegue" src="http://test.techni-contact.com/ressources/images/btn-share-single.png"></a>
                <script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
                <script src="http://s7.addthis.com/js/300/addthis_widget.js#pubid=technicontact" type="text/javascript"></script>
                <!-- AddThis Button END -->

                
              </div>
            </div>
                       <div class="zero"></div>
          </div>
                 <div class="product-page-shadow"></div>
                 
           <div class="product-page-bottom-right-col fr">
              <div class="grey-block product-page-bottom-desc"><a name="product-desc"></a>
                <div class="product-page-top-arrow"></div>
                <div class="blue-title">Description</div>
                <h4 class="block-pdt-title">Description du produit <strong><?php echo $p["name"]; ?></strong>:</h4>
                <br/>
                <?php echo cleanDescHtml($p["descc"]); ?>
                <div class="zero"></div>
                <?php if(!empty ($p["descd"]))echo '<br/><br/>'.cleanDescHtml($p["descd"]); ?>
                <div class="zero"></div>
               <?php if (!empty($p["adv_catalog_code"])) { ?>
                <br/>
                <br/>
                <div id="pdt-catalog" class="block-pdt-title">Catalogue produit interactif</div>
                <?php echo $p["adv_catalog_code"]; ?>
               <?php } ?>
               <?php if (!empty($p["video_code"])) { ?>
                <br/>
                <br/>
                <div id="pdt-video" class="block-pdt-title">Vidéo de présentation</div>
                <?php echo $p["video_code"]; ?>
               <?php } ?>
               <?php if ($p["docs_count"] > 0) { ?>
               <div class="zero"></div>
                <div class="pdt-docs">
                  <br/>
                  <div class="block-pdt-title" id="block-product-document">Documentation complémentaire</div>
                   <?php foreach($p["docs"] as $doc) if ($doc["uploaded"] == 2) { ?>
                  <h4 class="pdt-doc-link">
                    <a class="color-blue" href="<?php echo PRODUCTS_FILES_URL.$p["id"]."-".$doc["num"]."-".$doc["filename"].".pdf"; ?>" data-doc-location="<?php echo PRODUCTS_FILES_URL.$p["id"]."-".$doc["num"]."-".$doc["filename"].".pdf"; ?>">
                      <img src="<?php echo $res_url; ?>images/picto-doc-big.png"/>
                        <?php echo $doc["title"]; ?>
                    </a>
                  </h4>
                   <?php } ?>
                   <br/>
                </div>
               <?php } ?>
                <div class="zero"></div>
              </div><!-- /.product-page-bottom-desc -->
              </div>   
                 
             <!--    
                 
               <div id="hook"> 
                   <div class="titreBloc">Fiche du produit</div>
                   <div class="centre">
                       <div class="haut"></div>
                       <div class="ficheProduit">
<?php
if(is_file($i_dir_inc . $_GET['id'] . '.jpg'))
{
    print('                           <div class="imgProduit"><IMG src="'. $i_url_inc . '" alt="' . to_entities($p['name']) . '"></div>' . "\n");
}

?>                         <div class="liensContact">
                               <a class="contactProduit" href="#">demander un contact téléphonique</a><br />
                               <a class="contactProduit" href="#">demander un devis</a><br />
                               <a class="contactProduit" href="#">commander</a><br />
                               <a class="contactProduit" href="#">obtenir des informations</a><br />
                           </div>
                           <div class="sousTitreBloc">Description :</div>
                           <div class="contenuBloc">
<?php print('                               ' .  $p['descc'] . "\n") ?>
                           </div>
<?php

if(!empty($p['descd']))
{
    print('                           <div class="sousTitreBloc">Description technique :</div><div class="contenuBloc"> ' . $p['descd'] . '</div>' . "\n");
}

?>
                           <div class="sousTitreBloc">Prix / Références :</div>
                           <div class="contenuBloc">
<?php
if($p['price'] != 'sur demande' && $p['price'] != 'sur devis' && $p['price'] != 'nous contacter' && $p['price'] != 'ref' && $p['price'] != 0)
{
    print('                               ' . $p['price'] . ' euros.' . "\n");
}
else if($p['price'] == 'ref')
{
    print('<br /><div class="ref"><TABLE border="0" cellpadding="0" cellspacing="0"><TR class="ref-titre">');
	
	for($i = 0; $i < count($tab_ref_cols); ++$i)
	{
	    print('<TD nowrap><center>' . to_entities($tab_ref_cols[$i]) . '</center></TD>' . "\n");
	}
	
	print('</TR>');
	
	for($i = 0; $i < count($tab_ref_lines); ++$i)
	{
	    print('<TR>');
		
		for($j = 0; $j < count($tab_ref_lines[$i]); ++$j)
		{
		    if(trim($tab_ref_lines[$i][$j]) == '')
			{
			    $tab_ref_lines[$i][$j] = '-';
			}
		
		     print('<TD nowrap><center>' . to_entities($tab_ref_lines[$i][$j]) . '</center></TD>' . "\n");
		}
		
		
		print('</TR>');
	
	}
	
	
	print('</TABLE></div>');
	
	for($i = 0; $i < ceil(1.5 * (count($tab_ref_lines) + 1)); ++$i)
	{
	    print('<br />');
	}
	
}
else if($p['price'] == '0')
{
    print('                               sur demande.' . "\n");
}
else
{
    print('                               ' . $p['price'] . "\n");
}

?>
                           </div>
<?php


$d1 = $d2 = $d3 = false;

if(is_file($f_dir_inc. $_GET['id'] . '-1.doc'))
{
    $d1 = 'doc';
}
else if(is_file($f_dir_inc . $_GET['id'] . '-1.pdf'))
{
    $d1 = 'pdf';
}

if(is_file($f_dir_inc . $_GET['id'] . '-2.doc'))
{
    $d2 = 'doc';
}
else if(is_file($f_dir_inc . $_GET['id'] . '-2.pdf'))
{
    $d2 = 'pdf';
}

if(is_file($f_dir_inc . $_GET['id'] . '-3.doc'))
{
    $d3 = 'doc';
}
else if(is_file($f_dir_inc . $_GET['id'] . '-3.pdf'))
{
    $d3 = 'pdf';
}


if($d1 || $d2 || $d3)
{

    print('                           <div class="sousTitreBloc">Documentation :</div><div class="contenuBloc">');
    if($d1)
    {
        print('<a href="' . $f_url_inc . $_GET['id'] .'-1.' . $d1 . '"><img src="' . URL . 'images/' . $d1 . '.gif" width="50" height="49" align="absmiddle"></a> &nbsp;');
    }
    if($d2)
    {
        print('<a href="' . $f_url_inc . $_GET['id'] .'-2.' . $d2 . '"><img src="' . URL . 'images/' . $d2 . '.gif" width="50" height="49" align="absmiddle"></a> &nbsp;');
    }
    if($d3)
    {
        print('<a href="' . $f_url_inc . $_GET['id'] .'-3.' . $d3 . '"><img src="' . URL . 'images/' . $d3 . '.gif" width="50" height="49" align="absmiddle"></a>');
    }

    print('</div>' . "\n");
    
}

?>-->
                       </div>
                       <div class="miseAZero"></div>
                       <div class="bas"></div>
                   </div>
               </div>
                 </div>
               </div><!-- .white-bg -->
            </div>
            <div class="spacer">
                &nbsp;
            </div>
<div class="zero"></div>
<?php

require(SITE . 'tail.php');

?>
