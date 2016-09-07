<div id="right-col">
 
 <?php if ($pageName != 'orderProcess' && $pageName != 'Demande de devis') : ?>
  <?php
    $savedProducts = new ProductsSavedList();
    $nbrSavedProducts = $savedProducts->count();
    if ($session->logged) {
      $commandesExists = Doctrine_Query::create()
            ->select('id')
            ->from('Order')
            ->where('client_id = ?', $session->userID)
            ->count();

      $demandesExists = Doctrine_Query::create()
            ->select('id')
            ->from('Contacts')
            ->where('email = ?', $session->userEmail)
            ->count();
      
      $panierExists = Doctrine_Query::create()
            ->select('id')
            ->from('Paniers')
            ->where('idClient = ?', $session->userID)
            ->andWhere('estimate > ?', 0)
            ->count();
      if(!$panierExists)
        $panierExists = Doctrine_Query::create()
            ->select('id')
            ->from('Estimate')
            ->where('client_id = ?', $session->userID)
            ->count();
      
      if (!isset($user))
        $user = new CustomerUser($db, $session->userID);
      $mslt = Doctrine_Query::create() // mini store list from typology
        ->select('ms.id, ms.ref_name')
        ->from('MiniStores ms')
        ->innerJoin('ms.activity_sector_surqualifications ass')
        ->where('ass.qualification = ?', $user->secteur_qualifie)
        ->fetchArray();
      foreach ($mslt as $k => &$ms) {
        $ms['pic'] = MiniStores::getPic($ms['id'], 'vignette');
        $ms['url'] = MiniStores::getUrl($ms['id'], $ms['ref_name']);
        if (empty($ms['pic']))
          unset($mslt[$k]);
      }
      unset($ms);
    }
  ?>
  <div id="my-account" class="col-right-arrowed-block">
    <img src="<?php echo $res_url; ?>images/right-col-block-top.png" alt="" />
    <div id="my-account-title">
      <img src="<?php echo $res_url; ?>images/right-col-myaccount-logo.png" alt="" /><span>Mon compte</span>
    </div>
    <div class="col-right-arrowed-block-center">
      <div id="right-col-myaccount-zone">
       <?php if (!$session->logged) : ?>
        <div class="right-col-myaccount-button fl" style="left: 6px" onClick="javascript:HN.TC.ShowLoginForm('show');">S'identifier</div>
        <div class="right-col-myaccount-button fr" style="right: 6px" onClick="javascript:HN.TC.ShowCreateAccountForm('show');">S'inscrire</div>
         <?php if ($nbrSavedProducts) : ?>
          <div class="clear"></div>
          <br />
          <a class="color-blue" href="<?php echo URL ?>liste-produits-sauvegardes.html">Mes produits sauvegardés</a><br />
         <?php endif; // endif not orderprocess and not devis ?>
       <?php else : // logged ?>
          <br />Bonjour <?php echo $session->userFirstName; ?> <?php echo $session->userName; ?>,<br /> comment allez vous aujourd'hui ?<br />
         <?php if ($commandesExists) : ?>
          <a class="color-blue" href="<?php echo COMPTE_URL; ?>order-list.html">Voir mes commandes</a><br />
         <?php endif ?>
         <?php if ($demandesExists) : ?>
          <a class="color-blue" href="<?php echo COMPTE_URL; ?>lead-list.html">Voir mes demandes</a><br />
         <?php endif ?>
         <?php if ($panierExists) : ?>
          <a class="color-blue" href="<?php echo COMPTE_URL; ?>pdfestimate-list.html">Voir mes devis</a>
         <?php endif ?>
         <?php if ($nbrSavedProducts) : ?>
          <div class="clear"></div>
          <a class="color-blue" href="<?php echo URL ?>liste-produits-sauvegardes.html">Mes produits sauvegardés</a><br />
         <?php endif ?>
         <?php if (!empty($mslt)) : ?>
          <div class="mini-stores">
            <div class="title">Ceci devrait vous intéresser</div>
            <ul>
             <?php foreach ($mslt as $ms) : ?>
              <li><a href="<?php echo $ms['url'] ?>"><img src="<?php echo $ms['pic'] ?>" alt="" /></a></li>
            <?php endforeach ?>
            </ul>
          </div>
         <?php endif ?>
          <div class="right-col-myaccount-button fr" style="right: 6px" onClick="javascript:HN.TC.Logout()">Déconnexion</div>
       <?php endif // end logged ?>
        <div class="clear"></div><br />
        <div id="myaccount-create-account-form-dialog" title="Créer mon compte">
          <form method="post" action="" name="create_account_dialog_form" id="quick_account_form">
            <div>
              <div class="left">
                <div class="account-advantages vmaib">
                  <div class="title">Le compte est gratuit et vous permet :</div>
                  <ul>
                    <li>De sauvegarder les produits dont vous avez besoin</li>
                    <li>De demander des devis plus rapidement</li>
                    <li>D'échanger avec nos experts et nos partenaires</li>
                    <li>De facilement gérer vos devis et vos commandes</li>
                  </ul>
                </div>
                <div class="vsma"></div>
              </div>
              <ul class="right">
                <li>
                  <label for="nom">Nom <span class="blue-title">*</span></label>
                  <input id="qac-field_nom" name="nom" type="text" maxlength="255" class="edit-qac form-lead" value="<?=(isset($_COOKIE["nom"]) && $show) ? $_COOKIE["nom"] : $infos["nom"]?>"/>
                  <div class="form-lead-error-wrapper">
                    <div class="leadform_ok" id="qac-ok_nom"></div>
                    <div class="leadform_error" id="qac-error_nom"></div>
                  </div>
                  <div class="zero"></div>
                </li>
                
                <li>
                  <label for="prenom">Prénom <span class="blue-title">*</span></label>
                  <input id="qac-field_prenom" name="prenom" type="text" maxlength="255" class="edit-qac form-lead" value="<?=(isset($_COOKIE["prenom"]) && $show) ? $_COOKIE["prenom"] : $infos["prenom"]?>"/>
                  <div class="form-lead-error-wrapper">
                    <div class="leadform_ok" id="qac-ok_prenom"></div>
                    <div class="leadform_error" id="qac-error_prenom"></div>
                  </div>
                  <div class="zero"></div>
                </li>

                <li>
                  <label for="societe">Société<?php echo (isset($notReqFields["societe"])) ? " (optionnel)" : ' <span class="blue-title">*</span>'; ?></label>
                  <input id="qac-field_societe" name="societe" type="text" maxlength="255" class="edit-qac form-lead" value="<?=(isset($_COOKIE["societe"]) && $show) ? $_COOKIE["societe"] : $infos["societe"]?>"/>
                  <div class="form-lead-error-wrapper">
                    <div class="leadform_ok" id="qac-ok_societe"></div>
                    <div class="leadform_error" id="qac-error_societe"></div>
                  </div>
                  <div class="zero"></div>
                </li>

                <li>
                  <label for="email">Email <span class="blue-title">*</span></label>
                  <input id="qac-field_email" name="email" type="text" maxlength="255" class="edit-qac form-lead" value="<?=(isset($_COOKIE["email"]) && $show) ? $_COOKIE["email"] : $infos["email"]?>"/>
                  <div class="form-lead-error-wrapper">
                    <div class="leadform_ok" id="qac-ok_email"></div>
                    <div class="leadform_error" id="qac-error_email"></div>
                  </div>
                  <div class="zero"></div>
                </li>
                
                <li>
                  <label for="pass">Mot de passe <span class="blue-title">*</span></label>
                  <input id="qac-field_pass" name="pass" type="password" maxlength="255" class="edit-qac form-lead" />
                  <div class="form-lead-error-wrapper">
                    <div class="leadform_ok" id="qac-ok_pass"></div>
                    <div class="leadform_error" id="qac-error_pass"></div>
                  </div>
                  <div class="zero"></div>
                </li>

                <li>
                  <label for="pass2">Confirmation mot de passe <span class="blue-title">*</span></label>
                  <input id="qac-field_pass2" name="pass2" type="password" maxlength="255" class="edit-qac form-lead" />
                  <div class="form-lead-error-wrapper">
                    <div class="leadform_ok" id="qac-ok_pass2"></div>
                    <div class="leadform_error" id="qac-error_pass2"></div>
                  </div>
                  <div class="zero"></div>
                </li>
              </ul>
              <div class="zero"></div>
              <div class="btn-create-account"<?php if(!TEST): ?> onClick="_gaq.push(['_trackEvent', 'Bloc login', 'Validation', 'Validation inscription rapide']);"<?php endif; ?>></div>
              <div class="zero"></div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <a href="<?php echo URL ?>panier.html" id="my-basket" class="col-right-arrowed-block">
    <img src="<?php echo $res_url; ?>images/right-col-block-top.png" alt="" />
    <div id="my-basket-title">
      <img src="<?php echo $res_url; ?>images/right-col-basket-logo.png" alt="" /> <span>Mon Panier</span>
    </div>
    <?php $cart->calculateCart(); ?>
    <div class="col-right-arrowed-block-center">
      <div id="col-right-basket-info">
      <?php printf("%2d", $cart->itemCount); ?> article<?php echo ($cart->itemCount > 1 ? "s" : "");?>  ..................... <?php printf('%.4d', $cart->totalHT); ?>€ HT
      </div>
    </div>
  </a>
 <?php endif; // not order process ?>
 
 <?php if ($pageName != 'home') : ?>
  <div class="right-col-fixed">
 <?php endif ?>
    <div id="right-col-contact-us" class="right-col">
      <div id="right-col-contact-crew">
        <?php //when the operator sexual differenciation can be made, other css classes can be used for picture: contact-woman, contact-neutral ?>
        <div class="contact-picture contact-<?php echo $commercial_infos['gender']; // initiated in head.php ?> fr"></div>
        <div id="contact-crew-text">
          <?php echo $commercial_infos['help_msg']; // initiated in head.php ?>
        </div>
      </div>
    </div>
    <div class="zero"></div>
   <?php if ($pageName == 'Demande de devis') : ?>
    <div id="right-col-links" class="right-col">
      <img src="<?php echo $res_url.'images/theytrustus/nous_les_accompagnons.jpg'; ?>" alt="Nous les accompagnons" />
    </div>
   <?php else : // not "demande de devis" ?>
    <div id="right-col-links" class="right-col">
      <ul>
        <li><a href="javascript:showReassuranceDialog(1);"<?php if(!TEST): ?> onClick="_gaq.push(['_trackEvent', 'Pop_up_informatif', 'Ouverture pop up', 'Notre valeur ajoutée']);"<?php endif; ?>><img alt="" src="<?php echo $res_url; ?>images/right-col-link-logo-1.png" />Notre valeur ajoutée</a></li>
        <li><a href="javascript:showReassuranceDialog(2);"<?php if(!TEST): ?> onClick="_gaq.push(['_trackEvent', 'Pop_up_informatif', 'Ouverture pop up', 'Comment commander ?']);"<?php endif; ?>><img alt="" src="<?php echo $res_url; ?>images/right-col-link-logo-2.png" />Comment commander ?</a></li>
        <li><a href="javascript:showReassuranceDialog(3);"<?php if(!TEST): ?> onClick="_gaq.push(['_trackEvent', 'Pop_up_informatif', 'Ouverture pop up', 'Vos moyens de paiement']);<?php endif; ?>"><img alt="" src="<?php echo $res_url; ?>images/right-col-link-logo-3.png" />Vos moyens de paiement</a></li>
      </ul>
    </div>
    
    <?php if ($pageName != 'orderProcess') : ?>
    <div id="right-col-expert-space" class="right-col">
      <a href="<?php echo URL.'espace-thematique.html'?>"><img alt="" src="<?php echo $res_url; ?>images/right-col-link-expert-space.png" />Nos espaces thématiques</a>
    </div>
    <?php endif ?>
   <?php endif // page "demande de devis" or not ?>
    
    <?php if ($pageName == 'orderProcess') : ?>
      <div id="right-col-links" class="right-col">
      <ul>
        <li><a <?php if(!TEST): ?>onClick="_gaq.push(['_trackEvent', 'Page panier', 'Gestion panier', 'Impression BDC fax - colonne droite']);" <?php endif; ?>href="<?php echo PDF_URL."order-fax.php?cart_id=".$cart->id; ?>" target="_blank"><div class="puce puce-2"></div>Commander par FAX</a></li>
        <li><a href="<?php echo COMMANDE_URL; ?>estimate-step1.html" class="color-orange"><div class="puce puce-4"></div>Éditer un devis</a></li>
      </ul>
      <img src="<?php echo $res_url.'images/theytrustus/nous_les_accompagnons.jpg'; ?>" alt="Nous les accompagnons" />
    </div>
    <?php endif // order process ?>
    
   <?php if ($pageName == 'home') : ?>
    <div id="right-col-search" class="right-col">
      <div id="right-col-search-block">
        <img alt="Nos équipes cherchent pour vous" src="<?php echo $res_url; ?>images/right-col-search-block-header.png" />
        <div id="right-col-search-block-center">
          <form id="right-col-search-block-form" action="recherche-equipe-formulaire" name="search_ask_form">
            <ul>
              <li><input type="text" id="field_nom" name="nom" value="Nom" class="edit" /></li>
              <li><input type="text" id="field_prenom" name="prenom" value="Prénom" class="edit" /></li>
              <li><input type="text" id="field_societe" name="societe" value="Société / organisation" class="edit" /></li>
              <li><input type="text" id="field_telephone" name="telephone" value="Téléphone" class="edit" /></li>
              <li><input type="text" id="field_email" name="email" value="E-mail" class="edit" /></li>
              <li><textarea name="message" id="field_message" class="edit">Que cherchez-vous?</textarea></li>
              <li><div class="btn-send-search-ask"></div></li>
            </ul>
            <div class="search-ask-form-error" id="error_message"></div>
          </form>
        </div>
        <img class="right-col-search-block-footer" alt="footer" src="<?php echo $res_url; ?>images/right-col-search-block-footer.png" />
      </div>
    </div>
    <script type="text/javascript">
      $(function () {
          var ERRORS_TEXT = new Array();
  ERRORS_TEXT["telephone"] = "Merci de renseigner votre téléphone.";
  ERRORS_TEXT["nom"] = "Merci de renseigner votre nom.";
  ERRORS_TEXT["prenom"] = "Merci de renseigner votre prénom.";
  ERRORS_TEXT["email"] = "Merci de renseigner votre Email. Ex: xxxx@domaine.com";
  ERRORS_TEXT["societe"] = "Merci de renseigner le nom de votre société.";
  ERRORS_TEXT["message"] = "Merci de saisir votre demande.";
      
  var search_ask_form = $("form[name='search_ask_form']");
  
          $(".edit").focus(function() {
              $('.search-ask-form-error').hide();

              if ($(this).hasClass("badInfos") == true) {
                $(this).removeClass("badInfos");
              }
              switch($(this).attr('name')){
                case 'nom':
                  if($(this).val() == 'Nom')$(this).val('');
                  break;
                case 'prenom':
                  if($(this).val() == 'Prénom')$(this).val('');
                  break;
                case 'societe':
                  if($(this).val() == 'Société / organisation')$(this).val('');
                  break;
                case 'telephone':
                  if($(this).val() == 'Téléphone')$(this).val('');
                  break;
                case 'email':
                  if($(this).val() == 'E-mail')$(this).val('');
                  break;
                case 'message':
                  if($(this).val() == 'Que cherchez-vous?')$(this).val('');
                  break;
              }
              
            })

          $(".btn-send-search-ask", search_ask_form).click(function(){
            submited = true;
            // Ajax request to valid form
            $('.search-page-form-find-product input#field_societe').removeAttr('disabled');
            var formData = search_ask_form.serialize();
            $.ajax({
              type: "POST",
              data: formData,
              url: 'form-demande-recherche-produit.html?origin='+search_ask_form.attr("action"),
              success: function(data) {
                data = data.replace(/^\s*|\s*$/,"");
                if (data == 'SearchOk') {
                  /*var nom = $('input#field_nom').val();
                  var prenom = $('input#field_prenom').val();
                  $('#right-col-search-block-center').fadeOut(1000, function(){
                    $('#right-col-search-block-center').html('<div class="right-col-search-success">'+nom+' '+prenom+', merci pour votre confiance, nos conseiller démarrent dès à présent leurs recherche. Si un produit en catalogue répond à votre recherche nous ne manquerons pas de reprendre contact avec vous.</div>').fadeIn(1000);
                  })*/
                  document.location.href = "rechercher-demande-envoyee.html";
                  /*Prénom Nom, merci pour votre confiance, nos conseiller démarrent dès à présent leurs recherche. Si un produit en catalogue répond à votre recherche nous ne manquerons pas de reprendre contact avec vous. */
                }
                else {
                  if($('.search-page-form-find-product input#field_societe').val() == 'Particulier' && $('.search-page-form-find-product input#is_individual').attr('checked') == 'checked')
                    $('.search-page-form-find-product input#field_societe').attr('disabled', 'disabled');
                  $('.badInfos').removeClass('badInfos');
                  data = data.replace(/^\s*|\s*$/,"");
                  var errors = data.split('|');
                  $('.leadform_ok').html('');
                  var i = 0;
                  var html = '<div class="search-ask-error-arrow-right"></div>Merci de renseigner ces champs.';
                  for (i=0; i<errors.length; i++) {
                    var errorContent = '';
                    if (ERRORS_TEXT[errors[i]]) {
                      errorContent = ERRORS_TEXT[errors[i]];
                    }
                    else {
                      errorContent = "Merci de renseigner " + errors[i];
                    }
                    html += '<br /> - '+(errorContent == '' ? $("#error_"+errors[i]).prevAll('label:first').text().replace(' *', ''): errorContent);
                    $("#field_"+errors[i]).addClass("badInfos");
                  }
                  $('.search-ask-form-error').html(html);
                  //$('.search-ask-form-error').css({left: 300-parseInt($('.search-ask-form-error').css('width'))})
                  $('.search-ask-form-error').show();
                  //self.location.href="#nom_label";
                }
              }
            });
            return false;
          });

          $('.search-page-form-find-product input#is_individual')
            .mouseenter(function(){$('.search-page-form-find-product .label-individual').show()})
            .mouseleave(function(){$('.search-page-form-find-product .label-individual').hide()})
            .live('click', function(){
              if($(this).attr('checked') == 'checked')
                $('.search-page-form-find-product input#field_societe').attr('disabled','disabled').attr('value','Particulier');
            else
                $('.search-page-form-find-product input#field_societe').removeAttr('disabled');
            });
        });
    </script>
   <?php endif // home ?>
   
    <?php 
    if (defined('FAMILIES_PAGES')) {
      if ($catTree->length == 2) {
        $msWhereQueryText = 'msa.categoryID = ?';
        $msWhereQueryVal = array($cat2['id']);
      } elseif ($catTree->length == 3) {
        $msWhereQueryText = 'msa.categoryID = ?';
        $msWhereQueryVal = array($cat3['id']);
      }
    } elseif (defined('PRODUCT_PAGE')) {
      $msWhereQueryText = '(msa.categoryID = ? OR msa.categoryID = ? OR msa.productID = ?)';
      $msWhereQueryVal = array($pdt['cat2_id'], $pdt['cat3_id'], $pdt['id']);
    }
    
    if (isset($msWhereQueryText)) {
      $msl = Doctrine_Query::create()
        ->select('ms.*')
        ->from('MiniStores ms')
        ->leftJoin('ms.mini_stores_application msa')
        ->where('standalone != 1')
        ->andWhere($msWhereQueryText, $msWhereQueryVal)
        ->fetchArray();
    }
    if (!empty($msl)) : ?>
    <div class="<?php echo $pageName == 'liste_categories'? 'espace-thematique-links' : 'mini-stores-list';?>">
      <ul>
     <?php foreach($msl as $k => $ms) { 
       $stop = false;
       if(!empty($mslt))
       foreach($mslt as $mslt2)
         if($mslt2['id'] == $ms['id'])
           $stop = true;
       
       if($stop)
         continue;
       $ms['pic'] = MiniStores::getPic($ms['id'], 'vignette');
        $ms['url'] = MiniStores::getUrl($ms['id'], $ms['ref_name']);
        if (empty($ms['pic']))
          unset($msl[$k]);
       
       ?>
       <?php if (isset($ms['pic'])) : ?>
        <li class="thumbnail"><a href="<?php echo $ms['url']; ?>"><?php echo '<img src="'.$ms['pic'].'" alt="'.$ms["name"].'" />' ?></a></li>
        <?php else : ?>
        <li><a href="<?php echo $ms['url']; ?>"><?php echo $ms['name'] ?></a></li>
       <?php endif ?>
     <?php } ?>
      </ul>
    </div>
    <div class="zero"></div>
    <?php endif // liste_produits or liste_categories ?>
    
    <?php if ($pageName == 'home') : ?>
    <div id="right-col-partner-link" class="right-col-bottom-link right-col">
      <div  class="right-col-bottom-link">
      <a href="<?php echo URL ?>partenaire.html" class=""><img alt="Devenez partenaire TECHNI-CONTACT" src="<?php echo $res_url; ?>images/right-col-partner-link.png" /></a>
        </div>
    </div>
    <?php endif // home ?>
  
 <?php if ($pageName != 'home') : ?>
  </div>
 <?php endif ?>
  
</div>
