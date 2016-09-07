<?php if (DEBUG) $tab_microtime["page content"] = microtime(true); ?>

    <div class="zero"></div>
  </div><!-- /div#wrapper -->
  <div class="zero"></div>
</div><!-- /div#outer-frame -->
<div class="sep-35px"></div>

<div id="saved-products-list-dialog" title="Produits sauvegardés"></div>
    <div id="footer">
      <div id="footer-wrapper">
      
            <div id="footer-picto-links">
              <img src="<?php echo $res_url; ?>images/footer-picto-en-1-clic.png" alt="Recherche en 1 clic" />
              <img src="<?php echo $res_url; ?>images/footer-picto-conseil-d-expert.png" alt="Conseil d'experts" />
              <img src="<?php echo $res_url; ?>images/footer-picto-devis-personnalises.png" alt="Devis personnalisés" />
              <img src="<?php echo $res_url; ?>images/footer-picto-commandes-securisees.png" alt="Commandes sécurisées" />
              <img src="<?php echo $res_url; ?>images/footer-picto-a-votre-ecoute.png" alt="A votre écoute au 01 55 60 29 29" />
              <img src="<?php echo $res_url; ?>images/footer-picto-espaces-thematiques.png" alt="Espaces thématiques" />
            </div>

        <div id="footer-infos-cols">
          <div class="footer-infos-col">

            <div class="footer-infos-title"><span class="atseo about-us"></span></div>
            <ul>
              <li><a href="<?php echo URL; ?>nous.html">Qui sommes-nous ?</a></li>
              <li><a href="<?php echo URL; ?>cgv.html">CGV</a></li>
              <li><a href="<?php echo URL; ?>infoslegales.html">Mentions légales</a></li>
              <li><a href="<?php echo URL; ?>contact.html">Nous contacter</a></li>
              <li><a href="<?php echo URL; ?>partenaire.html">Devenir partenaires</a></li>
              <li><a href="<?php echo URL; ?>catalogues.html">Nos catalogues</a></li>
              <li><a href="<?php echo URL; ?>recrutement.html">Recrutement</a></li>
              <li><a href="<?php echo URL; ?>aide.html">Aide</a></li>
              <li><a href="<?php echo URL; ?>plan.html">Plan du site</a></li>
              <li><a href="<?php echo URL; ?>liens-partenaires.html">Quelques partenaires</a></li>
            </ul>

          </div>
          <div class="footer-infos-col">

            <div class="footer-infos-title">Vos garanties</div>
            <ul>
              <li>
                <div class="footer-logo-left">
                  <img src="<?php echo $res_url; ?>images/footer-logo-fevad.png" alt="fevad" style="padding-top: 3px" />
                </div>
                Membre de la fédération
                des entreprises de vente
                à distance
                <div class="clear"></div>
              </li>
              <li>
                <div class="footer-logo-left">
                  
                </div>
                Plus de 300&nbsp;000 entreprises nous font confiance
                  <div class="clear"></div>
              </li>
              <li>
                <div class="footer-logo-left">
                  <img alt="logo hook" src="<?php echo $res_url; ?>images/hook-grey.png">
                </div>
                <a target="_blank" href="http://www.hook-network.com">Façonné par Hook</a>
              </li>
            </ul>

          </div>
          <div class="footer-infos-col">

            <div class="footer-infos-title">Nos services</div>
            <ul>
              <li>Des experts à votre écoute</li>
              <li><span class="atseo ask-free-estimate"></span></li>
              <li>Nos équipes cherchent pour vous</li>
              <li>Nombreux modes de paiements</li>
              <li>
                <div id="footer-payment-means">
                  <img src="<?php echo $res_url; ?>images/footer-border-payment-left.png" alt="left-border" />
                  <img src="<?php echo $res_url; ?>images/footer-logo-visa.png" alt="visa" class="footer-payment-valign-middle" />
                  <img src="<?php echo $res_url; ?>images/footer-logo-cb.png" alt="cb" class="footer-payment-valign-middle" />
                  <img src="<?php echo $res_url; ?>images/footer-logo-mstrcrd.png" alt="mastercard" class="footer-payment-valign-middle" />
                  <img src="<?php echo $res_url; ?>images/footer-border-payment-right.png" alt="right-border" />
                </div>
              </li>
            </ul>

          </div>
          <div class="footer-infos-col">

            <div class="footer-infos-title">Vos avantages</div>
            <ul>
              <li>Un site pour tous vos achats</li>
              <li>Accompagnement personnalisé</li>
              <li>Espace de gestion gratuit</li>
              <li><span class="atseo custom-store"></span></li>
            </ul>
          </div>
          <div>
            <img src="<?php echo $res_url; ?>images/footer-logo-techni-contact.png" alt="Techni-contact" id="footer-logo-techni-contact" />
          </div>

        
        </div>

      </div><!-- /div#footer-wrapper -->
    </div><!-- /div#footer -->

    <div id="infos-dialog"></div>
	
<?php /* init js functionalities before any other js's */ ?>
  <script type="text/javascript">
    var mm = new HN.TC.MainMenu("header-menu", "header-submenu");
    var searchAC = new HN.TC.AutoCompletion($("div#header div#header-search div#header-search-form-zone input[name='search']").get(0));
		HN.TC.Init();
    var search_box = $('input[name=search]');
    $('table[class=auto-completion-box]').css({'position' : 'absolute', 'bottom' : '', 'width' : $('form[class=search] div').width()+4, 'z-index': 10});
    $('table[class=auto-completion-box]').offset({top : search_box.offset().top + 39, left :  $('input#header-search-input').offset().left});
    <?php if ($secure) { ?>HN.TC.SecureInit();<?php } ?>
	</script>
	
<?php 

	//Change on 31/12/2014 To include the global vars for iAdvize array
	try{
	//Get user email from the session (it he is logged
	$criteo_user_email	= $session->criteo_get_email_from_session_id();
	$email_Hashed		= md5($criteo_user_email);
	echo('<script type="text/javascript">');
		echo('var iadv_js_user_mail	="'.$email_Hashed.'";');
		echo("if(!iad_last_vue_product_1){ var iad_last_vue_product_1=''; }");
		echo("if(!iad_last_vue_product_2){ var iad_last_vue_product_2=''; }");
		echo("if(!iad_last_vue_product_3){ var iad_last_vue_product_3=''; }");
		echo("if(!idz_fiche_product_price){ var idz_fiche_product_price=''; }");
	echo('</script>');
	
	}catch(Exception $e){
		echo('Error: '.$e);
	}
	
 
	if (SHOW_TAGS) {
?>
  
  <?php /* js script tags */ ?>
	<!-- Criteo -->
 <?php if (!defined('SECURE') || defined('__PAGE_ORDER_CONFIRMED__')) : ?>
	<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
 <?php endif ?>
 <?php
	try{
	//Get user email from the session (it he is logged
	$criteo_user_email	= $session->criteo_get_email_from_session_id();
	$email_Hashed		= md5($criteo_user_email);
	}catch(Exception $e){
		echo('Error: '.$e);
	}
 ?>
 <?php if (defined('__HOME__')) : ?>
  <script type="text/javascript">
	window.criteo_q = window.criteo_q || [];
	window.criteo_q.push(
	{ event: "setAccount", account: 465 },
	{ event: "setSiteType", type: "d" },
	{ event: "setHashedEmail", email: ["<?php echo $email_Hashed; ?>"] },
	{ event: "viewHome" }
	);
  </script>
 <?php elseif (defined('PRODUCT_PAGE')) : ?>
  <!-- Fiche de produit -->
  <script type="text/javascript">

	window.criteo_q = window.criteo_q || [];
	window.criteo_q.push(
	{ event: "setAccount", account: 465 },
	{ event: "setSiteType", type: "d" },
	{ event: "setHashedEmail", email: ["<?php echo $email_Hashed; ?>"] },
	{ event: "viewItem", item: "<?php echo $pdt['id'] ?>" }
	);
  </script>
   <!-- Fin Fiche de produit -->
 <?php elseif (defined('__PAGE_LEAD__')) : ?>
  <script type="text/javascript">
	window.criteo_q = window.criteo_q || [];
	window.criteo_q.push(
		{ event: "setAccount", account: 465 },
		{ event: "setSiteType", type: "d" },
		{ event: "setHashedEmail", email: ["<?php echo $email_Hashed; ?>"] },
		{ event: "viewBasket", item: [
			<?php
				echo('{ id: "'.$pdt['id'].'", price: 6, quantity: 1}');
			?>
		]}
	);
  </script>
  <!-- Page recherche -->
 <?php elseif (defined('FAMILIES_PAGES') || defined('SEARCH')) : ?>
  <script type="text/javascript">
	window.criteo_q = window.criteo_q || [];
	window.criteo_q.push(
		{ event: "setAccount", account: 465 },
		{ event: "setSiteType", type: "d" },
		{ event: "setHashedEmail", email: ["<?php echo $email_Hashed; ?>"] },
		{ event: "viewList", item: <?php echo json_encode($criteo['pdt_ids']) ?> }
	);
  </script> 
   <!-- Fin page recherche -->
 <?php elseif (defined('__PANIER__')) : ?>
  <script type="text/javascript">
 
	window.criteo_q = window.criteo_q || [];
	window.criteo_q.push(
		{ event: "setAccount", account: 465 },
		{ event: "setSiteType", type: "d" },
		{ event: "setHashedEmail", email: ["<?php echo $email_Hashed; ?>"] },
		{ event: "viewBasket", item: [
			<?php
				$criteo_local_count = 0;
				while(!empty($criteo['pdt_ids'][$criteo_local_count])){
					//Printing every product with it's informations(Id, Price, Quantity) in a single row !
					echo('{ id: "'.$criteo['pdt_ids'][$criteo_local_count].'", price: '.$criteo['pdt_prices'][$criteo_local_count].', quantity: '.$criteo['pdt_quantities'][$criteo_local_count].'}');
				
					//Incrementing the loop variable
					$criteo_local_count++;
					
					//Test if we have more product in the queue 
					//We have to separate them with ","
					if(!empty($criteo['pdt_ids'][$criteo_local_count])){
						echo(', ');
					}
				}//end while
			?>
		]}
		
	);
  </script>
 <?php elseif (defined('__PAGE_LEAD_SUCCESS__') || defined('__PAGE_ORDER_CONFIRMED__')) : ?>
 
  <script type="text/javascript">
 	window.criteo_q = window.criteo_q || [];
	window.criteo_q.push(
		{ event: "setAccount", account: 465 },
		{ event: "setSiteType", type: "d" },
		{ event: "setHashedEmail", email: ["<?php echo $email_Hashed; ?>"] },
		<?php
			$criteo_transaction_id	= defined('__PAGE_LEAD_SUCCESS__') ? 'L'.$infos['MAINPRODUCT_GENERATEDCONTACTID'] : 'O'.$o['id'];
		?>
		{ 
			event: "trackTransaction", id: "<?php echo $criteo_transaction_id; ?>", 
			item: [
				<?php
					$criteo_local_count = 0;
					while(!empty($criteo['pdt_ids'][$criteo_local_count])){
						//Printing every product with it's informations(Id, Price, Quantity) in a single row !
						//Test if it's a Lead (devis) we force the price "11.5"
						if(defined('__PAGE_LEAD_SUCCESS__')){
							//Changes on 08/04/2015
							//Changes on 15/05/2015
							//$personnalized_price	= '11.50';
							$personnalized_price	= '5.5';
							$criteo_output_id		= "LA".$criteo['pdt_ids'][$criteo_local_count]; 
						}else{
							$personnalized_price	= $criteo['pdt_prices'][$criteo_local_count];
							//Add On 07/04/2015 Calculate the total Price HT and calculate 30%
							/*$personnalized_price		= '';
							$personnalized_price_temp	= $criteo['pdt_prices'][$criteo_local_count]*$criteo['pdt_quantities'][$criteo_local_count];
							$personnalized_price	= round(($personnalized_price_temp*30)/100, 2);*/
							
							//Changes on 15/05/2015
							$criteo_output_id		= "LF".$criteo['pdt_ids'][$criteo_local_count]; 
						}
						
						//Changes on 15/05/2015  $criteo_output_id
						echo('{ id: "'.$criteo_output_id.'", price: '.$personnalized_price.', quantity: '.$criteo['pdt_quantities'][$criteo_local_count].'}');
					
						//Incrementing the loop variable
						$criteo_local_count++;
						
						//Test if we have more product in the queue 
						//We have to separate them with ","
						if(!empty($criteo['pdt_ids'][$criteo_local_count])){
							echo(', ');
						}
					}//end while
				?>
			]
		}
	);
  </script>
 <?php endif ?>
  
	<!-- Eperflex -->
 <?php if (defined('PRODUCT_PAGE')) : ?>
  <img src="http://trk.email-reflex.com/tags/target.php?source=333&pid=<?php echo $pdt['id'] ?>" alt="" width="0" height="0" style="display: none;" />
 <?php elseif (defined('FAMILIES_PAGES')) : ?>
  <img src="http://trk.email-reflex.com/tags/categorie.php?source=333&cid=<?php echo $curCat['id'] ?>" alt="" width="0" height="0" style="display: none;" />
 <?php elseif (defined('__PAGE_LEAD_SUCCESS__') || defined('__PAGE_ORDER_CONFIRMED__')) : ?>
			  
	<img src="http://trk.email-reflex.com/tags/sell.php?source=333&amount=<?php echo defined('__PAGE_LEAD_SUCCESS__') ? '0' : $o['total_ht'] ?>&panierId=<?php echo defined('__PAGE_LEAD_SUCCESS__') ? 'L'.$infos['MAINPRODUCT_GENERATEDCONTACTID'] : 'O'.$o['id'] ?>" alt="" width="0" height="0" style="display: none;" />
	
	<!-- Integration 2eme tag 24/07/2014 12h44 -->
	<img src="http://trk.email-reflex.com/tags/sell.php?source=333&amount=<?php echo defined('__PAGE_LEAD_SUCCESS__') ? $infos['MAINPRODUCT_INCOME_TOTAL'] : $o['total_ht'] ?>&merchant_cart_id=<?php echo defined('__PAGE_LEAD_SUCCESS__') ? 'L'.$infos['MAINPRODUCT_GENERATEDCONTACTID'] : 'O'.$o['id'] ?>" alt="" width="0" height="0" style="display: none;" />
	
	<img src="http://trk.email-reflex.com/tags/exclude.php?source=333" alt="" width="0" height="0" style="display: none;" />

  <?php elseif (defined('__PANIER__') && !empty($eperflex)) : 
	foreach($criteo['pdt_ids'] as $value){
	$id_pdt .=  $value.',';
	}
	$id_pdt_panier = substr($id_pdt, 0, -1);
	
	foreach($criteo['pdt_quantities'] as $value_qty){
	$qty_pdt .=  $value_qty.',';
	}
	$qty_pdt_panier = substr($qty_pdt, 0, -1);
  ?>
  <img src="http://trk.email-reflex.com/tags/cart.php?source=333&cp=<?= $id_pdt_panier ?>&qt=<?= $qty_pdt_panier ?>" alt="" width="0" height="0" style="display: none;" />		
  <?php /*<img src="http://trk.email-reflex.com/tags/cart.php?source=333&cp=<?php echo json_encode($eperflex) ?>" alt="" width="0" height="0" style="display: none;" />*/ ?>
  <?php elseif (defined('__PAGE_LEAD__')) : 
	foreach($criteo['pdt_ids'] as $value){
	$id_pdt .=  $value.',';
	}
	$id_pdt_panier = substr($id_pdt, 0, -1);
	
	foreach($criteo['pdt_quantities'] as $value_qty){
	$qty_pdt .=  $value_qty.',';
	}
	$qty_pdt_panier = substr($qty_pdt, 0, -1);
  ?>
  <img src="http://trk.email-reflex.com/tags/cart.php?source=333&cp=<?= $id_pdt_panier ?>&qt=<?= $qty_pdt_panier ?>" alt="" width="0" height="0" style="display: none;" />		
 <?php endif ?>


  
  <!-- xiti -->
	<div id="xiti-logo">
		<script type="text/javascript">
		xtnv = document;           //affiliation frameset : document, parent.document ou top.document
		xtsd = <?php if ($secure) { ?>"https://logs"<?php } else { ?>"http://logi7"<?php } ?>;
		xtsite = "157945";
		xtn2 = "1";           //utiliser le numero du niveau 2 dans lequel vous souhaitez ranger la page
		xtpage = "";             //placer un libellé de page pour les rapports Xiti
		roimt = "";                 //valeur du panier pour ROI (uniquement pour les pages définies en transformation)
		roitest = false;             //à true uniquement si vous souhaitez effectuer des tests avant mise en ligne
		visiteciblee = false;          //à true pour les pages qui caractérisent une visite ciblée
		xtprm = "";           //Paramètres supplémentaires (optionnel)
		</script>
		<script type="text/javascript" src="<?php echo $res_url ?>scripts/xtroi.js"></script>
		<noscript>
		<?php if ($secure) { ?>
			<img width="1" height="1" alt="" src="https://logs.xiti.com/hit.xiti?s=157945&s2=&p=&di=&"/>
		<?php } else { ?>
			<img width="1" height="1" alt="" src="http://logi7.xiti.com/hit.xiti?s=157945&p=&roimt=&roivc=&"/>
		<?php } ?>
		</noscript>
	</div>
	
  <script type="text/javascript">
    var domainy = location.protocol == "https:" ? "https://static.getclicky.com" : "http://static.getclicky.com";
    document.write(unescape("%3Cscript src='" + domainy + "/133417.js' type='text/javascript'%3E%3C/script%3E"));
  </script>
  <noscript><p><img alt="Clicky" width="1" height="1" src="http://static.getclicky.com/133417-db17.gif" /></p></noscript>

 <?php if (defined("__HOME__")) { ?>
  <script src="http://nxtck.com/act.php?zid=16250"></script>
 <?php } ?>

 <?php if (defined("PRODUCT_PAGE")) { ?>
  <script src="http://nxtck.com/act.php?zid=16251;pid=<?php echo $pdt["id"] ?>"></script>
 <?php } ?>

 <?php if (defined("__PAGE_LEAD__")) : ?>
  <script src="http://nxtck.com/act.php?zid=16252"></script>
 <?php endif ?>
 
 <?php if (defined("__PAGE_LEAD_SUCCESS__")) : ?>
  <script src="http://nxtck.com/act.php?zid=16253;id=<?php echo ($infos['MAINPRODUCT_ADVERTISER_CATEGORY'] == __ADV_CAT_SUPPLIER__ ? "LF" : "LA") . $infos['MAINPRODUCT_GENERATEDCONTACTID'] ?>;mt=<?php 
	//echo sprintf('%.2f', $infos['MAINPRODUCT_INCOME_TOTAL']) 
	//Changes on 07/04/2015
	echo "6.2";
	?>"></script>
 <?php endif ?>
 
 <?php if (defined("__PANIER__")) { ?>
  <script src="http://nxtck.com/act.php?zid=16252"></script>
 <?php } ?>

 <?php if (defined("ORDER_STEP_1") || defined("ORDER_STEP_2") || defined("ORDER_STEP_3")) { ?>
  <script src="https://nxtck.com/act.php?zid=16252"></script>
 <?php } ?>

 <!-- Shopping Flux -->
 <?php if (defined('__PAGE_LEAD_SUCCESS__') || defined('__PAGE_ORDER_CONFIRMED__')) : ?>
  <script type="text/javascript">
    var sf = sf || [];
    sf.push(
      ['1255'],
      ['<?php echo defined('__PAGE_LEAD_SUCCESS__') ? 'L'.($infos["MAINPRODUCT_ADVERTISER_CATEGORY"] == __ADV_CAT_SUPPLIER__ ? 'F' : 'A').'_'.$infos['MAINPRODUCT_GENERATEDCONTACTID'] : $o['id'] ?>'],
      ['<?php echo defined('__PAGE_LEAD_SUCCESS__') ? '0' : $o['total_ht'] ?>']
    );

    (function() {
      var sf_script = document.createElement('script');
      sf_script.src = 'http://tag.shopping-flux.com/async.js';
      sf_script.setAttribute('async', 'true');
      document.documentElement.firstChild.appendChild(sf_script);
    })();
  </script>
 <?php endif ?>
 <!-- <script type="text/javascript" src="//tracking.shopping-flux.com/gg.js"></script>-->
 
 <?php if (defined("__404__")) { ?>
  <script type="text/javascript">
    var GOOG_FIXURL_LANG = '<?php echo DB_LANGUAGE ?>';
    var GOOG_FIXURL_SITE = '<?php echo URL ?>';
  </script>
  <script type="text/javascript" src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>
 <?php } ?>
  
  <?php } // end SHOW TAGS ?>

<?php /*if (DEBUG) {
  $tab_microtime["footer"] = microtime(true);
  $prev_ts = $tab_microtime["start"];
  foreach($tab_microtime as $step_name => $ts) {
    if ($step_name == "start") continue;
    print $step_name . " : <b>" . ($ts - $prev_ts)*1000 . "ms</b><br/>\n";
    $prev_ts = $ts;
  }
  print "Total : <b>" . ($tab_microtime["footer"] - $tab_microtime["start"])*1000 . "ms</b><br/>\n";
}*/ ?>



<?php

	//Start Code Add on 23/12/214 11h00m
	if (SHOW_TAGS) {
		//In case we are in th Lead success page we have to declare this global var
		//To use it in Google Manager Tags
		if(defined('__PAGE_LEAD_SUCCESS__')){
			//Google Tag manager Lead converted
			echo('<script type="text/javascript">');
				//echo('var js_global_var_page_lead_success_amount = "11.5"; ');
				echo('var js_global_var_page_lead_success_transaction_id = "'.$infos['MAINPRODUCT_GENERATEDCONTACTID'].'"; ');
			echo('</script>');
		}else if(defined('__PAGE_ORDER_CONFIRMED__')){
			//Google Tag manager Order converted
			
			//Variable for Google Tag manager (Transaction order amount)
			$google_tag_manager_amount_transaction	= 0;
			$criteo_local_count = 0;
			while(!empty($criteo['pdt_prices'][$criteo_local_count])){
				//Variable for Google Tag manager
				$google_tag_manager_amount_transaction = $google_tag_manager_amount_transaction + ($criteo['pdt_prices'][$criteo_local_count] * $criteo['pdt_quantities'][$criteo_local_count]);
				
				$criteo_local_count++;
			}
			
			//Frais port !
			//If The amount<300 add 8,50 €
			if($google_tag_manager_amount_transaction<300.001){
				$google_tag_manager_amount_transaction = $google_tag_manager_amount_transaction + 8.50; 
			}
			
			
			echo('<script type="text/javascript">');
				echo('var js_global_var_page_order_success_amount = "'.$google_tag_manager_amount_transaction.'"; ');
				echo('var js_global_var_page_order_success_transaction_id = "O'.$o['id'].'"; ');
			echo('</script>');
		}
?>

		<!-- Google Tag Manager -->
			<?php
				if(SHOW_TAGS){
					//Code Prod
					$google_tag_manager_id	= "GTM-WLCX8L";
					
				}else{
					//Code Preprod
					$google_tag_manager_id	= "GTM-M28QPZ";
				}	
			?>
			<noscript>
				<iframe src="//www.googletagmanager.com/ns.html?id=<?php echo $google_tag_manager_id; ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
			</noscript>
			<script>
				//To avoid the execution before the page load !
				$(document).ready(function(){ 
					(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
					new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
					j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
					'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
					})(window,document,'script','dataLayer','<?php echo $google_tag_manager_id; ?>');
				})
			</script>
		<!-- End Google Tag Manager -->
		
		<!-- Universal GA !-->
		<!--  Add 11/12/2015 10:15 FR -->
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-4217476-2', 'auto');
		  <?php if (defined('PRODUCT_PAGE')){ 
				if($pdt["adv_cat"] == '0') $type = "A";
				if($pdt["adv_cat"] == '1') $type = "F";
				if($pdt["adv_cat"] == '2') $type = "AF";
				if($pdt["adv_cat"] == '3') $type = "P";
				if($pdt["adv_cat"] == '4') $type = "AB";
				if($pdt["adv_cat"] == '5') $type = "LP";
		  ?>
			ga('set', 'contentGroup1', '<?= $type ?>');
			ga('set', 'contentGroup2', '<?= $pdt["adv_name"] ?>');
		  <?php }   ?>
		  ga('send', 'pageview');

		</script>

		<!-- Universal GA !-->
<?php
	}//End if show tag Google Tag Manager
	//End Code Add on 23/12/214 11h00m 
			if (isset($pdt_referent_commercial_id)) {
				if(!isset($_SESSION['comm_phone'])){
					$num_right =  $commercial_infos['phone'];				
				}else {
					$num_right = $_SESSION['comm_phone'];
				}
			}else{
					$num_right = $commercial_infos['phone'];
			}	
		
?>
 <script>
	$('#contact-crew-grey-text').html('<?= $num_right ?>');
</script>	
	<!--
	<script type="text/javascript" src="<?php echo $res_url ?>scripts/eltd-like.js"></script> 
	
	-->
	<script type="text/javascript" src="<?php echo $res_url ?>scripts/toolbar.js"></script> 
</body>
</html>
