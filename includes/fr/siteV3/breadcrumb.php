<div id="breadcrumb-bar">
  <?php if(empty ($pageName)){
    $customPage = true;
    $orderCustom = false;


    switch($_SERVER['SCRIPT_NAME']){
      case '/lead.html':
      case '/lead2.html':
      case '/lead3.html':
      case '/lead-a.html':
      case '/lead-f.html':
      case '/lead-a2.html':
      case '/lead-f2.html':
      case '/lead-a3.html':
      case '/lead-f3.html':
      case '/lead-success.html':
        $pageName = 'Demande de devis';
      break;

    case '/partenaire.html':
    case '/nous.html':
    case '/aide.html':
    case '/liens-partenaires.html':
    case '/cgv.html':
    case '/plan.html':
    case '/index-produits.html':
    case '/recrutement.html':
    case '/liste-produits-sauvegardes.html':
        $pageName = $breadcrumb_label;
      break;

    case '/fr/compte/index.html':
      $pageName = 'Tableau de bord';
      $accountCustom = '';
      break;

    case '/suppliers.html' :
      $pageName = 'Liste des produits du fournisseur n°'.$supplierID;
      break;

    case '/fr/compte/infos.html':
    case '/fr/compte/infos-modify.html':
      $pageName = 'Mes coordonnées';
      $accountCustom = 'infos';
      break;

    case '/fr/compte/requests-list.html':
      $pageName = 'Mes demandes';
      $accountCustom = 'demandes';
      break;

    case '/fr/compte/order-list.html':
    case '/fr/compte/order.html':
      $pageName = 'Mes commandes';
      $accountCustom = 'order';
      break;

    case '/fr/compte/lead-list.html':
    case '/fr/compte/lead.html':
      $pageName = 'Mes demandes';
      $accountCustom = 'demandes';
      break;

    case '/fr/compte/pdfestimate-list.html':
      case '/fr/compte/pdfestimate.html':
      $pageName = 'Mes devis';
      $accountCustom = 'devis';
      break;

    case '/fr/compte/saved-products-list.html':
      $pageName = 'Mes produits sauvegardés';
      $accountCustom = 'sauvegardes';
      break;

      case '/panier.html':
        $pageName = 'orderProcess';
        $orderCustom = 'basket';
      break;

      case '/fr/commande/order-step1.html':
      case '/fr/commande/estimate-step1.html':
        $pageName = 'orderProcess';
        $orderCustom = 'identification';
      break;

      case '/fr/commande/order-step2.html':
      case '/fr/commande/estimate-step2.html':
        $pageName = 'orderProcess';
        $orderCustom = 'delivery';
      break;

      case '/fr/commande/order-step3.html':
      case '/fr/commande/estimate-step3.html':
        $pageName = 'orderProcess';
        $orderCustom = 'payment';
      break;

      case '/fr/commande/order-confirmed.html':
        $pageName = 'orderProcess';
        $orderCustom = 'confirmation';
      break;

      default:
        $pageName = '';
        $customPage = false;
      break;
    }
  }

   if(!empty ($pageName)): ?>
  <div id="breadcrumb">
    <?php
    $homeButton = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
    $bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';
    $bcCat1=$bcCat2=$bcCat3='';
    if ($pageName == 'home') {
      $bcHtml = $homeButton;
    } else {
      $jsonLdItems = [];
      $bcHtml = '<a href="'.URL.'">'.$homeButton.'</a>';
      if (isset($cat1)) {
        $bcCat1 = $bcSeparator.$cat1['name'];
        $jsonLdItems[] = ['@id' => Utils::get_family_fo_url($cat1['ref_name']), 'name' => $cat1['name']];
        if (isset($cat2) && $catTree->length >= 2){
          $bcCat1 = $bcSeparator.'<a href="'.Utils::get_family_fo_url($cat1['ref_name']).'">'.$cat1['name'].'</a>';
          $bcCat2 = $bcSeparator.$cat2['name'];
          $jsonLdItems[] = ['@id' => Utils::get_family_fo_url($cat2['ref_name']), 'name' => $cat2['name']];
          if (isset($cat3) && $catTree->length >= 3){
            $bcCat2 = $bcSeparator.'<a href="'.Utils::get_family_fo_url($cat2['ref_name']).'">'.$cat2['name'].'</a>';
            $bcCat3 = $bcSeparator.$cat3['name'];
            $jsonLdItems[] = ['@id' => Utils::get_family_fo_url($cat3['ref_name']), 'name' => $cat3['name']];
          }
        }
      } elseif($pdt) {
        if ($customPage) {
          $bcCat1 = $bcSeparator.$pageName;
        } else {
          $jsonLdItems = [];
          $cat3Button = '<div id="breadcrumb-cat3-button">
            <img src="'.$res_url.'images/breadcrumb-button-left.png" alt="" class="fl" />
              <span>Voir rayon '.$pdt["cat3_name"].'</span>
              <img src="'.$res_url.'images/breadcrumb-button-right.png" alt="" class="fr" />
              <div class="zero"></div>
            </div>';
          $bcHtml = $cat3Button.$bcHtml;
          $pdt["cat3_ref_name"];
          $bcCat1 = $bcSeparator.$pdt["cat1_name"];
          $jsonLdItems[] = ['@id' => Utils::get_family_fo_url($pdt['cat1_ref_name']), 'name' => $pdt['cat1_name']];
          if ($pdt["cat2_name"]) {
            $bcCat1 = $bcSeparator.'<a href="'.URL.'familles/'.$pdt["cat1_ref_name"].'.html">'.$pdt["cat1_name"].'</a>';
            $bcCat2 = $bcSeparator.$pdt["cat2_name"];
            $jsonLdItems[] = ['@id' => Utils::get_family_fo_url($pdt['cat2_ref_name']), 'name' => $pdt['cat2_name']];
            if ($pdt["cat3_name"]) {
              $bcCat2 = $bcSeparator.'<a href="'.URL.'familles/'.$pdt["cat2_ref_name"].'.html">'.$pdt["cat2_name"].'</a>';
              $bcCat3 = $bcSeparator.$pdt["cat3_name"];
              $jsonLdItems[] = ['@id' => Utils::get_family_fo_url($pdt['cat3_ref_name']), 'name' => $pdt['cat3_name']];
              if ($pdt["name"]) {
                $bcCat3 = $bcSeparator.'<a href="'.URL.'familles/'.$pdt["cat3_ref_name"].'.html">'.$pdt["cat3_name"].'</a>';
                //$bcCat3 .= $bcSeparator.$pdt["name"];
              }
            }
          }
        }
      } elseif($customPage) {
        if (!empty ($orderCustom))
          $bcHtml = '<div class="breadcrumb-order-process breadcrumb-'.$orderCustom.'"></div>';
        elseif(!empty ($accountCustom))
          $bcCat1 .= $bcSeparator.'<a href="'.URL.'fr/compte/index.html">Mon espace personnel</a>'.$bcSeparator.$pageName;
        else
          $bcCat1 = $bcSeparator.$pageName;
      }
    }
    $bcHtml .= $bcCat1.$bcCat2.$bcCat3;
    echo $bcHtml;

    if (!empty($jsonLdItems)) {
      $jsonLdBreadcrumb = [
        '@context' => 'http://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => []
      ];
      foreach ($jsonLdItems as $i => $jsonLdItem) {
        $jsonLdBreadcrumb['itemListElement'][] = [
          '@type' => 'ListItem',
          'position' => $i+1,
          'item' => $jsonLdItem
        ];
      }
      echo '<script type="application/ld+json">'.json_encode($jsonLdBreadcrumb).'</script>';
    }
    ?>
  </div>
  <?php endif; ?>


  <?php

	if($_SERVER['SCRIPT_NAME'] == '/guides-achat.html'){

		echo '<div id="breadcrumb">';
		$homeButton  = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
		$bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';

		$bcHtml 	 = '<a href="'.URL.'">'.$homeButton.'</a>';

		$nclient_Html  = '<a href="'.URL.'guides-achat/nos-guides.html">Guides d’achat Techni-Contact </a>';
		echo $bcHtml.$bcSeparator.$nclient_Html.$bcSeparator.$data_guide->title_h;
		echo '</div>';

	}
	if($_SERVER['SCRIPT_NAME'] == '/nos-guides.html'){
		echo '<div id="breadcrumb">';
		$homeButton  = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
		$bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';

		$bcHtml 	 = '<a href="'.URL.'">'.$homeButton.'</a>';

		$nclient_Html  = 'Guides d’achat Techni-Contact ';
		echo $bcHtml.$bcSeparator.$nclient_Html;
		echo '</div>';
	}


	if($_SERVER['SCRIPT_NAME'] == '/utilisateur-details.html'){

		$url_explode = explode('-',$_SERVER['REQUEST_URI']);
		$explode_name_Societe  = explode('.',$url_explode['2']);


		$sql_client = "SELECT societe FROM annuaire_client WHERE client_id='".$url_explode['1']."' ";
		$req_client = mysql_query($sql_client);
		$data_client= mysql_fetch_object($req_client);
		$lettre  = substr($data_client->societe, 0, 1);


		echo '<div id="breadcrumb">';
		$homeButton  = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
		$bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';

		$bcHtml 	 = '<a href="'.URL.'">'.$homeButton.'</a>';
		$nclient_Html= '<a href="'.URL.'index-utilisateurs.html">Nos clients</a>';
		$lettreHtml  = '<a href="'.URL.'index-utilisateurs.html?lettre='.ucfirst(strtolower($lettre)).'">'.Ucfirst($lettre).'</a>';
		echo $bcHtml.$bcSeparator.$nclient_Html.$bcSeparator.$lettreHtml.$bcSeparator.$data_client->societe;
		echo '</div>';

	}

	if($_SERVER['SCRIPT_NAME'] == '/index-utilisateurs.html'){

		echo '<div id="breadcrumb">';
		$homeButton  = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
		$bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';

		$bcHtml 	 = '<a href="'.URL.'">'.$homeButton.'</a>';
		$nclient_Html= 'Nos clients';
		//$lettreHtml  = '<a href="'.URL.'index-utilisateurs.html?lettre='.ucfirst(strtolower($lettre)).'">'.Ucfirst($lettre).'</a>';
		echo $bcHtml.$bcSeparator.$nclient_Html;
		echo '</div>';

	}

	if($_SERVER['SCRIPT_NAME'] == '/fiche-utilisateur-survey.html'){
		//$url_params  =  $_SERVER[REQUEST_URI];

		echo '<div id="breadcrumb">';
		$homeButton  = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
		$bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';

		$bcHtml 	 = '<a href="'.URL.'">'.$homeButton.'</a>';
		$nclient_Html= 'Présentez gratuitement votre activité sur Techni-Contact';

		echo $bcHtml.$bcSeparator.$nclient_Html;
		echo '</div>';

	}
	
	if($_SERVER['SCRIPT_NAME'] == '/blog.html'){
		//$url_params  =  $_SERVER[REQUEST_URI];

		echo '<div id="breadcrumb">';
		$homeButton  = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
		$bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';

		$bcHtml 	 = '<a href="'.URL.'">'.$homeButton.'</a>';
		$nclient_Html= 'Blog';

		echo $bcHtml.$bcSeparator.$nclient_Html;
		echo '</div>';

	}
	
	if($_SERVER['SCRIPT_NAME'] == '/detail-article.html'){
		//$url_params  =  $_SERVER[REQUEST_URI];

		echo '<div id="breadcrumb">';
		$homeButton  = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
		$bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';

		$bcHtml 	 = '<a href="'.URL.'">'.$homeButton.'</a>';
		$blog_Html= '<a href="'.URL.'blog">Blog</a>';
		
		$url_page 	=  $_SERVER['REQUEST_URI'];
		$url_expode = explode('/',$url_page);
		$id_article =  $url_expode[2];
		
		$sql_articles = " SELECT id ,article_title,promo_image,content,timestamp_created,ref_name
							FROM blog_articles
						  WHERE id ='".$id_article."'";
		$req_articles  =  mysql_query($sql_articles);
		$data_articles =  mysql_fetch_object($req_articles);

		echo $bcHtml.$bcSeparator.$blog_Html.$bcSeparator.$data_articles->article_title;
		echo '</div>';
	}
	
	if($_SERVER['SCRIPT_NAME'] == '/article_tag_detail.html'){
		//$url_params  =  $_SERVER[REQUEST_URI];

		echo '<div id="breadcrumb">';
		$homeButton  = '<img src="'.$res_url.'images/breadcrumb-home-logo.png" alt="Home" class="breadcrumb-home-logo" /> Accueil';
		$bcSeparator = '<span class="breadcrumb-grey-text"> | </span>';

		$bcHtml 	 = '<a href="'.URL.'">'.$homeButton.'</a>';
		$blog_Html= '<a href="'.URL.'blog">Blog</a>';
		
		$url_page 	=  $_SERVER['REQUEST_URI'];
		$url_expode = explode('/',$url_page);
		$id_tag =  $url_expode[3];
		
		$sql_articles = " SELECT id ,name
							FROM blog_tags_names
						  WHERE id ='".$id_tag."'";
		$req_articles  =  mysql_query($sql_articles);
		$data_articles =  mysql_fetch_object($req_articles);

		echo $bcHtml.$bcSeparator.$blog_Html.$bcSeparator.$data_articles->name;
		echo '</div>';
	}
	
	?>



  <script type="text/javascript">
    $('#breadcrumb-cat3-button').click(function(){
      window.location = $('#breadcrumb a:eq(3)').attr('href');
    });
  </script>
</div>
<div class="zero"></div>
