<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 15 février 2007

 Mises à jour :

 Fichier : /secure/manager/stats/index.php
 Description : Index des statistiques
 
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = "Ecran d'accueil des remises, promotions et contraintes";
$navBar = "Accueil Remises/Promotions/Contraintes";

header("Content-Type: text/html; charset=utf-8");

require(ADMIN . 'head.php');
$lastpage = 100;
$page = 24;

define("AJAX_RESSOURCES_PATH", "../AJAXressources/others/");
/*
Home
	Discounts
	Promotions
	Constraints
	
--------------------------------------------------------------------------------
- Main window : 3 tabs
--------------------------------------------------------------------------------

- Discounts tab
--------------------------------------------------------------------------------
  Note : Discounts are Supplier based. For a discount on a single product, you still have to choose a supplier, then just make this discount "apply on" range on the desired product

  ** Main window **
  / Fields /
  ID discount : Unique ID for the discount (Unsigned Integer)
  Supplier Name : Advertiser's Name (string)
  Discount Trigger : Trigger for the discount to be applied (constant) - ex : AMOUNT (0), QUANTITY (1)
  Trigger Threshold : Value for the trigger - ex : 500 (€), 25 (quantity)
  Apply on : application domain for the discount (constant) - ex : ALL_PRODUCTS (0), SPECIFIED_PRODUCTS (0)
  Apply on values : Values for the apply on type (text) - ex : "384684,68468357,684681,684684"
  Priority : Priority in relation to the constraints, that is to say before or after calculation (constant) - ex : BEFORE (0), AFTER (1)

  / Behaviour /
  button "new discount" onclick = Open "Edit discount" window
  row onclick = Open "Edit discount" window
  *****

  ** New/Edit discount Window **
  / Fields /
  ID discount = field not editable
  Supplier Name = pop a window to choose the supplier
  Discount Trigger = combo box - values = predefined values
  Trigger Threshold = edit field
  Apply on = combo box - values = predefined values
  Apply on values = pop a window to choose the objects

  / Behaviour /
  For the supplier, a window is popped, showing the list of suppliers alphabeticely ordered
  if DEFINED_PRODUCTS is set, a window is popped, showing the list of the choosen products for this supplier
  *****


- Constraints tab
--------------------------------------------------------------------------------
  Note : Constraint are supplier based. Behaves pretty much like the discounts section

  ** Main window **
  / Fields /
  ID constraint : Unique ID for the constraint (Unsigned Integer)
  Supplier Name : Advertiser's Name (string)
  Constraint Type : Type of the constraint (constant) - ex : MIN_AMOUNT (0), MIN_QUANTITY (1)
  Constraint Value : the value corresponding to the type (float) - ex : 15.5 (€), 50 (quantity), 255.15 (grammes of something)
  Apply on : application domain for the constraint (constant) - ex : ALL_PRODUCTS (0), SPECIFIED_PRODUCTS (1)
  Apply on values : Values for the apply on type (text) - ex : "384684,68468357,684681,684684"

  / Behaviour /
  button "new constraint" onclick = Open "Edit constraint" window
  row onclick = Open "Edit constraint" window
  *****

  ** Edit constraint Window **
  / Fields /
  ID constraint = field not editable
  Supplier Name = pop a window to choose the supplier
  Constraint Type = combo box - values = predefined values
  Constraint Value = edit field
  Apply on = combo box - values = predefined values
  Apply on values = pop a window to choose the objects

  / Behaviour /
  For the supplier, a window is popped, showing the list of suppliers alphabeticely ordered
  if DEFINED_PRODUCTS is set, a window is popped, showing the list of the choosen products for this supplier
  *****


- Promotions tab
--------------------------------------------------------------------------------
  Note : Promotions are product based

  ** Main window **
  / Fields /
  ID Promotion : Unique ID for the promotion (Unsigned Integer)
  Promotion Type : Type of the promotion (constant) - ex : RELATIVE (0), FIXED (1), DELIVERY_FEE (2)
  Promotion Value : the value corresponding to the type (float) - ex : 10.5 (%), 100 (€), 100 (%)
  Create Date : Creation Date of this promotion (timestamp)
  Start Date : When this promotion starts (timestamp)
  End Date : When it ends (timestamp)
  End Trigger : Special trigger which end this promotion (constant) - ex : PRODUCT (0), COMMANDS (1)
  End Trigger Value : Value corresponding to the end trigger (float) - ex : 100
  End Trigger Current Value : Current value of the end trigger (float) - ex : 53
  Apply on : Application domain for the promotion (constant) - ex : ALL (0), SUPPLIERS (1), FAMILIES (2), SPECIFIED_PRODUCTS (3)
  Apply on values : Values for the apply on type (text) - ex : "", "1651,35319,35357,1981", "12,354,56,87", "68768168,3576846846,196835435,3573549,354357"
  // Apply for : Customers for who this promotion is available (text) - ex : "357354,6846843,387687,849816,3876"
  Picture : Picture to show on the products page for this promotion (string)
  Code : Code to enter for this promotion, done at the basket page (string)
  Active : if this promotion is currently active

  / Behaviour /
  button "new promotion" onclick = Open "Edit promotion" window
  row onclick = Open "Edit Promotion" window
  *****

  ** Edit Promotion Window **
  / Fields /
  ID Promotion = field not editable
  Promotion Type = combo box - values = predefined values
  Promotion Value = edit field
  Create Date = edit field
  Start Date = edit field
  End Date = edit field
  End Trigger = combo box - values = predefined values
  End Trigger Value = edit field
  End Trigger Current Value = edit field
  Apply on = combo box - values = predefined values
  Apply on values = pop a window to choose the objects
  Picture = edit field
  Code = edit field
  Active = toggle link

  / Behaviour /
  For the supplier, a window is popped, showing the list of suppliers alphabeticely ordered
  if DEFINED_PRODUCTS is set, a window is popped, showing the list of the choosen products for this supplier
  *****

*/

/* Discounts Constants */
define("DISC_TYPE_AMOUNT", 0);
define("DISC_TYPE_QUANTITY", 1);
define("DISC_APPLY_ALL_PRODUCTS", 0);
define("DISC_APPLY_SPECIFIED_PRODUCTS", 1);
define("DISC_PRIORITY_BEFORE", 0);
define("DISC_PRIORITY_AFTER", 1);

$discount_type_list = array(
	DISC_TYPE_AMOUNT => "à partir du montant",
	DISC_TYPE_QUANTITY => "à partir de la quantité"
);
$discount_apply_list = array(
	DISC_APPLY_ALL_PRODUCTS => "tous les produits",
	DISC_APPLY_SPECIFIED_PRODUCTS => "les produits spécifiés"
);
$discount_priority_list = array(
	DISC_PRIORITY_BEFORE => "avant les contraintes",
	DISC_PRIORITY_AFTER => "après les contraintes"
);

/* Promotions Constants */
define("PROM_TYPE_RELATIVE", 0);
define("PROM_TYPE_FIXED", 1);
define("PROM_TYPE_DELIVERY_FEE", 2);
define("PROM_APPLY_ALL", 0);
define("PROM_APPLY_SPECIFIED", 1);
define("PROM_END_TRIGGER_PRODUCT", 0);
define("PROM_END_TRIGGER_COMMANDS", 1);
define("PROM_ACTIVE_NO", 0);
define("PROM_ACTIVE_YES", 1);

$promotion_type_list = array(
	PROM_TYPE_RELATIVE => "Remise en %",
	PROM_TYPE_FIXED => "Montant fixe retiré"
);
$promotion_apply_list = array(
	PROM_APPLY_ALL => "tout le catalogue",
	PROM_APPLY_SPECIFIED => "fournisseurs/produits spécifiés"
);
$promotion_end_trigger_list = array(
	PROM_END_TRIGGER_PRODUCT => "quantité de produits",
	PROM_END_TRIGGER_COMMANDS => "quantité de commandes"
);
$promotion_active_list = array(
	PROM_ACTIVE_NO => "Non",
	PROM_ACTIVE_YES => "Oui"
);
?>
<div class="titreStandard">Accueil Remises/Promotions/Contraintes</div>
<br />
<div class="bg" style="position: relative">
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>discounts-constraints/HN.css"/>
<style type="text/css">
/* Discounts Tab */
#DT-JSTable table { min-width: 900px; }
#DT-JSTable table .column-edit { min-width: 40px; text-align: center; }
#DT-JSTable table .column-edit .check { float: left; }
#DT-JSTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(cog.png) 2px 0px no-repeat; }
#DT-JSTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(cancel.png) 2px 0px no-repeat; }
#DT-JSTable table .column-0 { min-width: 70px; text-align: center; }
#DT-JSTable table .column-1 { min-width: 130px; text-align: center; }
#DT-JSTable table .column-2 { min-width: 70px; text-align: center; }
#DT-JSTable table .column-3 { min-width: 110px; text-align: center; }
#DT-JSTable table .column-4 { min-width: 70px; text-align: center; }
#DT-JSTable table .column-5 { min-width: 130px; text-align: center; }
#DT-JSTable table .column-6 { min-width: 130px; text-align: center; }
#DT-JSTable table .column-7 { min-width: 110px; text-align: center; }

#DT-DEDBShad { z-index: 1; position: absolute; top: -50px; left: 55px; width: 394px; height: 316px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#DT-DEDB { z-index: 2; position: absolute; top: -55px; left: 50px; width: 390px; height: 312px; visibility: hidden; background: #f1efef url(../ressources/images/block-bg-gray-h500.gif) 0px 0px repeat-y; border: 2px solid #205683 }
#DT-DEDB-bg { position: relative; height: 250px; }
.DT-choose-button { cursor: pointer; float: right; margin-right: 5px }

#DT-DEDB label { clear: both; display: block; float: left; width: 150px; height: 25px }
#DT-DEDB span { display: block; float: left; width: 175px }
#DT-DEDB input { display: block; float: left; width: 190px; border: 1px solid #d0d0d0; }
#DT-DEDB select { display: block; float: left; width: 190px }
#DT-DEDB .glimpse { font: normal 9px tahorm, arial, sans-serif }

#DT-ChooseSupplier { z-index: 3; width: 530px; position: absolute; top: 50px; left: 45px; }

#DT-MPSDBShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 724px; min-height: 244px; visibility: hidden; background: #000000; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#DT-MPSDB { z-index: 4; position: absolute; top: 60px; left: 75px; width: 720px; min-height: 240px; visibility: hidden; border: 2px solid #205683 }

/* Promotions Tab */
#PT-JSTable table { min-width: 900px; }
#PT-JSTable table .column-edit { min-width: 40px; text-align: center; }
#PT-JSTable table .column-edit .check { float: left; }
#PT-JSTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(cog.png) 2px 0px no-repeat; }
#PT-JSTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(cancel.png) 2px 0px no-repeat; }
#PT-JSTable table .column-0 { min-width: 70px; text-align: center; }
#PT-JSTable table .column-1 { min-width: 130px; text-align: center; }
#PT-JSTable table .column-2 { min-width: 110px; text-align: center; }
#PT-JSTable table .column-3 { min-width: 40px; text-align: center; }
#PT-JSTable table .column-4 { min-width: 110px; text-align: center; }
#PT-JSTable table .column-5 { min-width: 180px; text-align: center; }
#PT-JSTable table .column-6 { min-width: 110px; text-align: center; }
#PT-JSTable table .column-7 { min-width: 110px; text-align: center; }

#PT-PEDBShad { z-index: 1; position: absolute; top: -50px; left: 55px; width: 419px; height: 416px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#PT-PEDB { z-index: 2; position: absolute; top: -55px; left: 50px; width: 415px; height: 412px; visibility: hidden; background: #f1efef url(../ressources/images/block-bg-gray-h500.gif) 0px 0px repeat-y; border: 2px solid #205683 }
#PT-PEDB-bg { position: relative; height: 350px; }
.PT-choose-button { cursor: pointer; float: right; margin-right: 5px }

#PT-PEDB label { clear: both; display: block; float: left; width: 180px; height: 25px }
#PT-PEDB span { display: block; float: left; width: 170px }
#PT-PEDB select { display: block; float: left; width: 185px }
#PT-PEDB input[type="text"] { display: block; float: left; width: 185px; border: 1px solid #d0d0d0; }
#PT-PEDB input[type="checkbox"] { float: left; width: auto; margin: 0; padding: 0 }
#PT-PEDB input[type="button"] { float: right; width: 113px; margin: 0 5px; font: 11px normal verdana, arial, sans-serif }
#PT-PEDB #PT-apply-buttons { clear: both; padding: 0 0 5px 0 }

#PT-ChooseSupplier { z-index: 3; width: 620px; position: absolute; top: 50px; left: 45px; }

#PT-MPSDBShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 724px; min-height: 244px; visibility: hidden; background: #000000; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#PT-MPSDB { z-index: 4; position: absolute; top: 60px; left: 75px; width: 720px; min-height: 240px; visibility: hidden; border: 2px solid #205683 }

</style>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
</script>
<link type="text/css" rel="stylesheet" href="tc.tabs.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.DialogBox.blue.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.MISM.blue.css">
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ui.core.min.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ui.tabs.min.js"></script>

<script type="text/javascript" src="Classes.js"></script>
<script type="text/javascript" src="AJAXclasses.js" ></script>
<script type="text/javascript" src="AJAXmodules.js" ></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>

<div id="tabs-menu" class="dark">
  <ul>
    <li><a href="#tab-discounts">Remises</a></li>
    <!--<li><a href="#tab-constraints">Contraintes</a></li>-->
    <li><a href="#tab-promotions">Promotions</a></li>
  </ul>
  
  <div id="tab-discounts">
    <div id="DT-DEDB">
      <div id="DT-DEDB-bg" class="window_bg">
        <label>ID : </label><span id="DT-id"></span>
				<label>Fournisseur : </label><span id="DT-advName"></span><img class="DT-choose-button" src="cog.png" onclick="DT.ShowAdvertiserSearchWindow()"/>
				<input id="DT-idAdvertiser" type="hidden" value="" style="display: none"/>
				<label>Valeur en %age : </label><input id="DT-value" type="text" value=""/>
				<label>Condition d'application : </label>
					<select id="DT-type">
						<?php foreach($discount_type_list as $k => $v) { ?>
						<option value="<?php echo $k ?>"><?php echo $v ?></option>
						<?php } ?>
					</select>
				<label>Valeur du seuil : </label><input id="DT-type_value" type="text" value=""/>
				<label>S'applique sur : </label>
					<select id="DT-apply" onchange="DT.ApplyOnChange()">
						<?php foreach($discount_apply_list as $k => $v) { ?>
						<option value="<?php echo $k ?>"><?php echo $v ?></option>
						<?php } ?>
					</select>
				<label>Liste : </label><span id="DT-apply_value-label" class="glimpse"></span><img class="DT-choose-button" src="cog.png" onclick="DT.MISM.SetSelectedItems($('#DT-apply_value').val()); DT.MPSDB.Show();"/>
				<input id="DT-apply_value" type="hidden" value="" style="display: none"/>
				<label>Priorité : </label>
					<select id="DT-priority">
						<?php foreach($discount_priority_list as $k => $v) { ?>
						<option value="<?php echo $k ?>"><?php echo $v ?></option>
						<?php } ?>
					</select>
				<label>Date de Création : </label><span id="DT-create_time"></span>
				<label>Date de Modification : </label><span id="DT-timestamp"></span>
      </div>
    </div>
		
		<div class="window-silver" id="DT-ChooseSupplier" style="display: none;">
			<div id="DT-main_menu" class="tab_menu"></div>
			<div class="menu-below"></div>
			<div class="main">
				<div id="DT-Suppliers">
					<div id="DT-search_menuS" class="search_menu"></div>
					<div class="body">
						<div class="colg">
							<div class="col-title" onmousedown="grab(document.getElementById('DT-ChooseSupplier'))">Liste des fournisseurs</div>
							<ul class="list" id="DT-listS">
							</ul>
						</div>
						<div class="colc">
							<div class="col-title" onmousedown="grab(document.getElementById('DT-ChooseSupplier'))">Informations</div>
							<div class="infos">
								<div id="DT-infosS"></div>
								<input type="button" class="button button1" value="Valider" onclick="DT.SetAdvertiserName(DT.ElementListS);"/>
								<input type="button" class="button button2" value="Annuler" onclick="DT.HideAdvertiserSearchWindow();"/>
							</div>
						</div>
						<div class="zero"></div>
					</div>
				</div>
				<div id="DT-Advertisers">
					<div id="DT-search_menuA" class="search_menu"></div>
					<div class="body">
						<div class="colg">
							<div class="col-title" onmousedown="grab(document.getElementById('DT-ChooseSupplier'))">Liste des annonceurs</div>
							<ul class="list" id="DT-listA">
							</ul>
						</div>
						<div class="colc">
							<div class="col-title" onmousedown="grab(document.getElementById('DT-ChooseSupplier'))">Informations</div>
							<div class="infos">
								<div id="DT-infosA"></div>
								<input type="button" class="button button1" value="Valider" onclick="DT.SetAdvertiserName(DT.ElementListA);"/>
								<input type="button" class="button button2" value="Annuler" onclick="DT.HideAdvertiserSearchWindow();"/>
							</div>
						</div>
						<div class="zero"></div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="DT-MPSDB" class="MISM"></div>
		
    <div id="DT-PageSwitcher1"></div>
    <div class="zero"></div>
    <div id="DT-JSTable"></div>
    <div id="DT-PageSwitcher2"></div>
    <div class="zero"></div>
		
		<div id="PerfReqLabelDiscounts" style="float: right"></div>
		<button class="bouton" onclick="DT.addDiscount()">Ajouter une remise</button>
  </div>

  <!--<div id="tab-constraints">contraintes</div>-->

  <div id="tab-promotions">
    <div id="PT-PEDB">
      <div id="PT-PEDB-bg" class="window_bg">
        <label>ID : </label><span id="PT-id"></span>
				
				<label>Type : </label>
				<select id="PT-type">
					<?php foreach($promotion_type_list as $k => $v) { ?>
					<option value="<?php echo $k ?>"><?php echo $v ?></option>
					<?php } ?>
				</select>
				<label>Valeur pour le type : </label><input id="PT-type_value" type="text" value=""/>
				
				<label>S'applique sur : </label>
				<select id="PT-apply" onchange="PT.ApplyOnChange()">
					<?php foreach($promotion_apply_list as $k => $v) { ?>
					<option value="<?php echo $k ?>"><?php echo $v ?></option>
					<?php } ?>
				</select>
				<div id="PT-apply-buttons">
					<input type="button" value="Fournisseurs" onclick="PT.ShowAdvertiserSearchWindow();"/>
					<!--<input type="button" value="Familles" onclick=""/>-->
					<input type="button" value="Produits" onclick="PT.MISM.SetSelectedItems($('#PT-apply_value_P').val()); PT.MPSDB.Show();"/>
					<div class="zero"></div>
				</div>
				<input id="PT-apply_value_A" type="hidden" value="" style="display: none"/>
				<input id="PT-apply_value_C" type="hidden" value="" style="display: none"/>
				<input id="PT-apply_value_P" type="hidden" value="" style="display: none"/>
				
				<label>Condition de fin : </label>
				<select id="PT-end_trigger">
					<?php foreach($promotion_end_trigger_list as $k => $v) { ?>
					<option value="<?php echo $k ?>"><?php echo $v ?></option>
					<?php } ?>
				</select>
				<label>Valeur de la condition de fin :</label><input id="PT-end_trigger_value" type="text" value=""/>
				<label>Valeur courante de la condition :</label><input id="PT-end_trigger_current" type="text" value=""/>
				
				<label>Code promotionnel éventuel</label><input id="PT-code" type="text" value=""/>
				<!--<label>Image à afficher</label><input id="PT-picture" type="text" value=""/>-->
				
				<label>Date de début de validité</label><input id="PT-start_time" type="text" value=""/>
				<label>Date de fin de validité</label><input id="PT-end_time" type="text" value=""/>
				<label>Activer</label><input id="PT-active" type="checkbox" value=""/>
				
				<label>Date de Création : </label><span id="PT-create_time"></span>
				<label>Date de Modification : </label><span id="PT-timestamp"></span>
      </div>
    </div>
		
		<div class="window-silver MASM" id="PT-ChooseSupplier" style="display: none;">
			<div id="PT-main_menu" class="tab_menu"></div>
			<div class="menu-below"></div>
			<div class="main">
				<div id="PT-Suppliers">
					<div id="PT-search_menuS" class="search_menu"></div>
					<div class="body">
						<div class="colg">
							<div class="col-title" onmousedown="grab(document.getElementById('PT-ChooseSupplier'))">Liste des fournisseurs</div>
							<ul class="list" id="PT-listS">
							</ul>
						</div>
						<div class="colc">
							<div class="col-title" onmousedown="grab(document.getElementById('PT-ChooseSupplier'))">Fournisseurs sélectionnés</div>
							<ul class="slist" id="PT-slistS"></ul>
						</div>
						<div class="cold">
							<div class="col-title" onmousedown="grab(document.getElementById('PT-ChooseSupplier'))">Informations</div>
							<div class="infos">
								<div id="PT-infosS"></div>
								<input type="button" class="button button1" value="Annuler" onclick="PT.HideAdvertiserSearchWindow();"/>
								<input type="button" class="button button2" value="Valider" onclick="PT.SetAdvertiserName(PT.SSL);"/>
							</div>
						</div>
						<div class="zero"></div>
					</div>
				</div>
				<div id="PT-Advertisers">
					<div id="PT-search_menuA" class="search_menu"></div>
					<div class="body">
						<div class="colg">
							<div class="col-title" onmousedown="grab(document.getElementById('PT-ChooseSupplier'))">Liste des annonceurs</div>
							<ul class="list" id="PT-listA">
							</ul>
						</div>
						<div class="colc">
							<div class="col-title" onmousedown="grab(document.getElementById('PT-ChooseSupplier'))">Annonceurs sélectionnés</div>
							<ul class="slist" id="PT-slistA"></ul>
						</div>
						<div class="cold">
							<div class="col-title" onmousedown="grab(document.getElementById('PT-ChooseSupplier'))">Informations</div>
							<div class="infos">
								<div id="PT-infosA"></div>
								<input type="button" class="button button1" value="Annuler" onclick="PT.HideAdvertiserSearchWindow();"/>
								<input type="button" class="button button2" value="Valider" onclick="PT.SetAdvertiserName(PT.SAL);"/>
							</div>
						</div>
						<div class="zero"></div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="PT-MPSDB" class="MISM"></div>
		
    <div id="PT-PageSwitcher1"></div>
    <div class="zero"></div>
    <div id="PT-JSTable"></div>
    <div id="PT-PageSwitcher2"></div>
    <div class="zero"></div>
		
		<div id="PerfReqLabelPromotions" style="float: right"></div>
		<button class="bouton" onclick="PT.addPromotion()">Ajouter une promotion</button>
  </div>

</div>
<br />
<script type="text/javascript">
$('#tabs-menu ul:first').tabs({ });
/** TAB DISCOUNTS
*******************************************************************************/
var DT = {};

DT.discount_type_list = <?php echo json_encode($discount_type_list) ?>;
DT.discount_apply_list = <?php echo json_encode($discount_apply_list) ?>;
DT.discount_priority_list = <?php echo json_encode($discount_priority_list) ?>;

/* Multiple Products Selection */

// Multiple Products Selection Dialog Box (MPSDB)
DT.MISM = new HN.Mods.MISM("DT-MPSDB");
DT.MISM.Build();

DT.MPSDB = new HN.Mods.DialogBox("DT-MPSDB");
DT.MPSDB.setTitleText("Choisir une liste de produits");
DT.MPSDB.setMovable(true);
DT.MPSDB.showCancelButton(true);
DT.MPSDB.showValidButton(true);
DT.MPSDB.setValidFct(function() {
	var apply_value_label = DT.MISM.GetSelectedItems();
	if (apply_value_label.length > 25) apply_value_label = apply_value_label.substr(0,22) + "...";
	$("#DT-apply_value-label").text(apply_value_label);
	$("#DT-apply_value").val(DT.MISM.GetSelectedItems());
	DT.MPSDB.Hide();
});
DT.MPSDB.setShadow(true);
DT.MPSDB.Build();

/* Adv choose window */
DT.ShowAdvertiserSearchWindow = function () { document.getElementById('DT-ChooseSupplier').style.display = 'block'; };
DT.HideAdvertiserSearchWindow = function () { document.getElementById('DT-ChooseSupplier').style.display = 'none'; };
DT.SetAdvertiserName = function (ElementList) {
	if (ElementList.SelectedObject)
	{
		$("#DT-advName").text(ElementList.SelectedObject.firstChild.nodeValue);
		$("#DT-idAdvertiser").val(ElementList.SelectedObject.ElementID);
	}
	DT.HideAdvertiserSearchWindow();
};

DT.MenuTabs = new TabList(
	"DT-main_menu",
	function (tc, layerID) {
		for (var t in tc)
		{
			if (t == layerID) document.getElementById(layerID).style.display = "block";
			else document.getElementById(t).style.display = "none";
		}
	},
	{ "DT-Suppliers" : "Fournisseur", "DT-Advertisers" : "Annonceurs" }
);
DT.MenuTabs.Draw();
DT.MenuTabs.tc["DT-Suppliers"].onclick();

DT.SearchMenuA = new SearchMenu("DT-search_menuA",
	{"0-9" : function () {
			DT.ElementListAHandle.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape('[_0-9]'));
			document.getElementById('DT-infosA').innerHTML = "Choisissez un annonceur";
		},
	"[A-Z]" : function (letter) {
			DT.ElementListAHandle.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape(letter));
			document.getElementById('DT-infosA').innerHTML = "Choisissez un annonceur";
		}
	}, "span");
DT.ElementListA = new ElementList("DT-listA", "li", function(id) { DT.InfosAHandle.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + id); });
DT.ElementListAHandle = new AJAXHandle(function(xhr) { DT.ElementListProcessResponse(DT.ElementListA, xhr); }, "PerfReqLabelDiscounts");
DT.InfosAHandle = new AJAXHandle(function(xhr) { DT.InfosProcessResponse("DT-infosA", xhr); }, "PerfReqLabelDiscounts");
DT.SearchMenuA.Draw();

DT.SearchMenuS = new SearchMenu("DT-search_menuS",
	{"0-9" : function () {
			DT.ElementListSHandle.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape('[_0-9]'));
			document.getElementById('DT-infosS').innerHTML = "Choisissez un fournisseur";
		},
	"[A-Z]" : function (letter) {
			DT.ElementListSHandle.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape(letter));
			document.getElementById('DT-infosS').innerHTML = "Choisissez un fournisseur";
		}
	}, "span");
DT.ElementListS = new ElementList("DT-listS", "li", function(id) { DT.InfosSHandle.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + id); });
DT.ElementListSHandle = new AJAXHandle(function(xhr) { DT.ElementListProcessResponse(DT.ElementListS, xhr); }, "PerfReqLabelDiscounts");
DT.InfosSHandle = new AJAXHandle(function(xhr) { DT.InfosProcessResponse("DT-infosS", xhr); }, "PerfReqLabelDiscounts");
DT.SearchMenuS.Draw();

DT.ElementListProcessResponse = function(el, xhr)
{
	el.Clean();
	el.Clear();
	
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	if (mainsplit[0] == '') // Pas d'erreur
	{
		var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);
		for (var i = 0; i < outputs.length-1; i++)
		{
			var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
			if (outputID.length == 2)
			{
				el.Add(outputID[0],outputID[1]);
			}
		}
		el.Draw();
	}
	else
	{
		document.getElementById(el.id).innerHTML = mainsplit[0];
	}
}

DT.InfosProcessResponse = function(id, xhr)
{
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	if (mainsplit[0] == '') // Pas d'erreur
	{
		document.getElementById(id).innerHTML = mainsplit[1];
	}
	else
	{
		document.getElementById("PerfReqLabelDiscounts").innerHTML = mainsplit[0];
	}
	
}

/* Discount Edit Window */
DT.ApplyOnChange = function()
{
	switch (parseInt($("#DT-apply").val()))
	{
		case <?php echo DISC_APPLY_ALL_PRODUCTS ?> : 
			$("#DT-apply_value-label").text("<?php echo $discount_apply_list[DISC_APPLY_ALL_PRODUCTS] ?>");
			break;
		case <?php echo DISC_APPLY_SPECIFIED_PRODUCTS ?> :
			var apply_value_label = $("#DT-apply_value").val();
			if (apply_value_label.length > 25) apply_value_label = apply_value_label.substr(0,22) + "...";
			$("#DT-apply_value-label").text(apply_value_label);
			break;
		default : break;
	}
}

DT.AJAXHandleObject = {
	type : "GET",
	url: "<?php echo AJAX_RESSOURCES_PATH ?>Discounts.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#PerfReqLabelDiscounts").text(textStatus);
	},
	success: function (rjo, textStatus) {
		if (rjo.error) $("#PerfReqLabelDiscounts").text(rjo.error);
		else
		{
			switch(rjo.action)
			{
				case "get" :
					$("#DT-id").text(rjo.data.id);
					$("#DT-advName").text(rjo.data.advName);
					$("#DT-idAdvertiser").val(rjo.data.idAdvertiser);
					$("#DT-type").val(rjo.data.type);
					$("#DT-type_value").val(parseFloat(rjo.data.type_value));
					$("#DT-value").val(parseFloat(rjo.data.value));
					$("#DT-apply").val(rjo.data.apply);
					var apply_value_label = rjo.data.apply_value;
					if (apply_value_label.length > 25) apply_value_label = apply_value_label.substr(0,22) + "...";
					$("#DT-apply_value-label").text(apply_value_label);
					$("#DT-apply_value").val(rjo.data.apply_value);
					$("#DT-priority").val(rjo.data.priority);
					$("#DT-create_time").text(GetDateYMDHM(new Date(parseInt(rjo.data.create_time)*1000)));
					$("#DT-timestamp").text(GetDateYMDHM(new Date(parseInt(rjo.data.timestamp)*1000)));
					
					$("#PerfReqLabelDiscounts").text("");
					DT.DEDB.action = "alter";
					DT.DEDB.Show();
				break;
				
				case "alter" :
					var rowData = [
						parseInt(rjo.data.id),
						rjo.data.advName,
						rjo.data.value,
						DT.discount_type_list[parseInt(rjo.data.type)],
						rjo.data.type_value,
						DT.discount_apply_list[parseInt(rjo.data.apply)],
						DT.discount_priority_list[parseInt(rjo.data.priority)],
						GetDateYMDHM(new Date(parseInt(rjo.data.create_time)*1000))
					];
					DT.DLMT.AlterRow(DT.DLMT.getRowByIndex(rowData[0], 0), rowData);
					DT.DEDB.Hide();
				break;
				
				case "add" :
					var rowData = [[
						parseInt(rjo.data.id),
						rjo.data.advName,
						rjo.data.value,
						DT.discount_type_list[parseInt(rjo.data.type)],
						rjo.data.type_value,
						DT.discount_apply_list[parseInt(rjo.data.apply)],
						DT.discount_priority_list[parseInt(rjo.data.priority)],
						GetDateYMDHM(new Date(parseInt(rjo.data.create_time)*1000))
					]];
					DT.DLMT.AddRow(rowData);
					DT.DLMT.Refresh();
					DT.DLPS1.setLastPage(DT.DLMT.getLastPage()); DT.DLPS1.Refresh();
					DT.DLPS2.setLastPage(DT.DLMT.getLastPage()); DT.DLPS2.Refresh();
					DT.DEDB.Hide();
				break;
				
				case "delete" :
					document.location.href = document.location.href;
				break;
					
				default: break;
			}
		}
	}
};

DT.addDiscount = function () {
	$("#DT-id").text("Nouvelle remise");
	$("#DT-advName").text(" - ");
	$("#DT-idAdvertiser").val(61049);
	$("#DT-type").val(0);
	$("#DT-type_value").val(0);
	$("#DT-value").val(0);
	$("#DT-apply").val(0);
	$("#DT-apply_value-label").text("<?php echo $discount_apply_list[DISC_APPLY_ALL_PRODUCTS] ?>");
	$("#DT-apply_value").val(0);
	$("#DT-priority").val(0);
	$("#DT-create_time").text(" - ");
	$("#DT-timestamp").text(" - ");
	
	DT.DEDB.action = "add";
	DT.DEDB.Show();
};

// Discount Edition Dialog Box
DT.DEDB = new HN.Mods.DialogBox("DT-DEDB");
DT.DEDB.action = "alter";
DT.DEDB.setTitleText("Editer une Remise");
DT.DEDB.setMovable(true);
DT.DEDB.showCancelButton(true);
DT.DEDB.showValidButton(true);
DT.DEDB.setValidFct( function() {
	DT.AJAXHandleObject.url = "<?php echo AJAX_RESSOURCES_PATH ?>Discounts.php?action=" + DT.DEDB.action +
		"&id=" + $("#DT-id").text() +
		"&idAdvertiser=" + $("#DT-idAdvertiser").val() +
		"&type=" + $("#DT-type").val() +
		"&type_value=" + $("#DT-type_value").val() +
		"&value=" + $("#DT-value").val() +
		"&apply=" + $("#DT-apply").val() +
		"&apply_value=" + $("#DT-apply_value").val() +
		"&priority=" + $("#DT-priority").val();
	$.ajax(DT.AJAXHandleObject);
} );
DT.DEDB.setShadow(true);
DT.DEDB.Build();

/* Discount List Main Table */
DT.DLMT = new HN.Mods.JSTable();
DT.DLMT.setID("DT-JSTable");
DT.DLMT.setClass("CommonTable");
DT.DLMT.setHeaders(["ID", "Fournisseur", "Valeur", "Condition", "Seuil", "S'applique sur", "priorité", "date de création"]);
DT.DLMT.setColIndex([0]);
DT.DLMT.setInitialData([
<?php
$res = & $handle->query("
	select
		d.id, a.nom1 as adv_name, d.type, d.type_value, d.value,
		d.apply, d.apply_value, d.priority, d.create_time
	from
		discounts d, advertisers a
	where
		d.idAdvertiser = a.id
	order by
		d.create_time desc", __FILE__, __LINE__, false);
$nbd = $handle->numrows($res, __FILE__, __LINE__);
for ($i = 0; $i < $nbd; $i++)
{
	$rec = & $handle->fetchAssoc($res);
	//if (strlen($rec['apply_value']) > 30) $rec['apply_value'] = substr($rec['apply_value'], 0, 27) . "...";
	echo '	[' . $rec['id'] . ', "' . $rec['adv_name'] . '", "' . $rec['value'] . '", "' . $discount_type_list[(int)$rec['type']] . '", "' . $rec['type_value'] . '", "' . $discount_apply_list[(int)$rec['apply']] . '", "' . $discount_priority_list[(int)$rec['priority']] . '", "' . date("Y/m/d H:i", $rec['create_time']) . '"]' . ($i < ($nbd-1) ? "," : "") . "\n";
}

?>
]);
DT.DLMT.setColumnCount(8);
DT.DLMT.setMultiPage(true);
DT.DLMT.setRowCount(20);
DT.DLMT.setCurrentPage(1);
DT.DLMT.setRowFct( {
	onmouseover : function() { this.style.backgroundColor = "#CCCCCC"; },
	onmouseout : function() { this.style.backgroundColor = ""; }
} );
DT.DLMT.setEditTools({
	edit : {
		element : "div",
		attributes : {
			onclick : function() {
				DT.AJAXHandleObject.url = "<?php echo AJAX_RESSOURCES_PATH ?>Discounts.php?action=get&id="+this.parentNode.parentNode.cc[0].textvalue;
				$.ajax(DT.AJAXHandleObject);
			},
			alt : "Editer cette remise",
			title : "Editer cette remise"
		}
	},
	del : {
		element : "div",
		attributes : {
			onclick : function() {
				DT.AJAXHandleObject.url = "<?php echo AJAX_RESSOURCES_PATH ?>Discounts.php?action=delete&id="+this.parentNode.parentNode.cc[0].textvalue;
				$.ajax(DT.AJAXHandleObject);
			},
			alt : "Supprimer cette remise",
			title : "Supprimer cette remise"
		}
	}
});
DT.DLMT.Refresh();

/* Discount List Page Switchers */
DT.DLPS1 = new HN.Mods.PageSwitcher();
DT.DLPS1.setID("DT-PageSwitcher1");
DT.DLPS1.setCurrentPage(1);
DT.DLPS1.setLastPage(DT.DLMT.getLastPage());
DT.DLPS1.setTriggerFct( function(page) { DT.DLMT.setCurrentPage(page); DT.DLPS2.setCurrentPage(page); DT.DLMT.Refresh(); DT.DLPS2.Refresh(); } );
DT.DLPS1.Refresh();

DT.DLPS2 = new HN.Mods.PageSwitcher();
DT.DLPS2.setID("DT-PageSwitcher2");
DT.DLPS2.setCurrentPage(1);
DT.DLPS2.setLastPage(DT.DLMT.getLastPage());
DT.DLPS2.setTriggerFct( function(page) { DT.DLMT.setCurrentPage(page); DT.DLPS1.setCurrentPage(page); DT.DLMT.Refresh(); DT.DLPS1.Refresh(); } );
DT.DLPS2.Refresh();

/** TAB CONSTRAINTS
*******************************************************************************/
var PT = {};

PT.promotion_type_list = <?php echo json_encode($promotion_type_list) ?>;
PT.promotion_apply_list = <?php echo json_encode($promotion_apply_list) ?>;
PT.promotion_end_trigger_list = <?php echo json_encode($promotion_end_trigger_list) ?>;
PT.promotion_active_list = <?php echo json_encode($promotion_active_list) ?>;

/* Multiple Products Selection */

// Multiple Products Selection Dialog Box (MPSDB)
PT.MISM = new HN.Mods.MISM("PT-MPSDB");
PT.MISM.Build();

PT.MPSDB = new HN.Mods.DialogBox("PT-MPSDB");
PT.MPSDB.setTitleText("Choisir une liste de produits");
PT.MPSDB.setMovable(true);
PT.MPSDB.showCancelButton(true);
PT.MPSDB.showValidButton(true);
PT.MPSDB.setValidFct(function() {
	$("#PT-apply_value_P").val(PT.MISM.GetSelectedItems());
	PT.MPSDB.Hide();
});
PT.MPSDB.setShadow(true);
PT.MPSDB.Build();

/* Adv choose window */
PT.ShowAdvertiserSearchWindow = function () { document.getElementById('PT-ChooseSupplier').style.display = 'block'; };
PT.HideAdvertiserSearchWindow = function () { document.getElementById('PT-ChooseSupplier').style.display = 'none'; };
PT.SetAdvertiserName = function (ElementList)
{
	var eidlist = "";
	for (eid in ElementList.ei) eidlist += eid + ",";
	eidlist = eidlist.substr(0,eidlist.length-1);
	
	$("#PT-apply_value_A").val(eidlist);
	PT.HideAdvertiserSearchWindow();
};

PT.MenuTabs = new TabList(
	"PT-main_menu",
	function (tc, layerID) {
		for (var t in tc)
		{
			if (t == layerID) document.getElementById(layerID).style.display = "block";
			else document.getElementById(t).style.display = "none";
		}
	},
	{ "PT-Suppliers" : "Fournisseur", "PT-Advertisers" : "Annonceurs" }
);
PT.MenuTabs.Draw();
PT.MenuTabs.tc["PT-Suppliers"].onclick();

/* Tab Advertisers
*******************************************************************************/
PT.ALH = new AJAXHandle(function(xhr) { PT.ElementListProcessResponse(PT.AL, xhr); }, "PerfReqLabelPromotions");
PT.AIH = new AJAXHandle(function(xhr) { PT.InfosProcessResponse("PT-infosA", xhr); }, "PerfReqLabelPromotions");

PT.SearchMenuA = new SearchMenu("PT-search_menuA",
	{"0-9" : function () {
			PT.ALH.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape('[_0-9]'));
			document.getElementById('PT-infosA').innerHTML = "Choisissez un annonceur";
		},
	"[A-Z]" : function (letter) {
			PT.ALH.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape(letter));
			document.getElementById('PT-infosA').innerHTML = "Choisissez un annonceur";
		}
	}, "span");

// Advertisers List
PT.AL = new MultipleElementList("PT-listA", "li", {
	onmousover: function () { if (PT.AL.SelectedObject != this) this.className = 'over'; },
	onmouseout: function () { if (PT.AL.SelectedObject != this) this.className = ''; },
	onmousedown: function () {
		if (PT.AL.SelectedObject) PT.AL.SelectedObject.className = '';
		PT.AL.SelectedObject = this;
		this.className = 'selected';
		PT.AIH.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + this.ElementID);
	},
	ondblclick: function () {
		var imgd = document.createElement("img");
		imgd.src = "cross_12x12.png";
		imgd.alt = "delete";
		imgd.onclick = function () { PT.SAL.Delete(this.parentNode); PT.SAL.Draw(); }
		PT.SAL.Add(this.ElementID, this.firstChild.nodeValue, imgd);
		PT.SAL.Draw();
	}
});

// Selected Advertisers List
PT.SAL = new MultipleElementList("PT-slistA", "li", {
	onmousover: function () { if (PT.SAL.SelectedObject != this) this.className = 'over'; },
	onmouseout:function () { if (PT.SAL.SelectedObject != this) this.className = ''; },
	onmousedown: function () {
		if (PT.SAL.SelectedObject) PT.SAL.SelectedObject.className = '';
		PT.SAL.SelectedObject = this;
		this.className = 'selected';
		PT.AIH.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + this.ElementID);
	},
	ondblclick: function () {
		PT.SAL.Delete(this);
		PT.SAL.Draw();
	}
});
PT.SearchMenuA.Draw();

PT.AL.selectList = PT.SAL;

/* Tab Suppliers
*******************************************************************************/
PT.SLH = new AJAXHandle(function(xhr) { PT.ElementListProcessResponse(PT.SL, xhr); }, "PerfReqLabelPromotions");
PT.SIH = new AJAXHandle(function(xhr) { PT.InfosProcessResponse("PT-infosS", xhr); }, "PerfReqLabelPromotions");

PT.SearchMenuS = new SearchMenu("PT-search_menuS", {
	"0-9" : function () {
			PT.SLH.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape('[_0-9]'));
			document.getElementById('PT-infosS').innerHTML = "Choisissez un fournisseur";
	},
	"[A-Z]" : function (letter) {
			PT.SLH.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape(letter));
			document.getElementById('PT-infosS').innerHTML = "Choisissez un fournisseur";
	}
}, "span");

// Suppliers List
PT.SL = new MultipleElementList("PT-listS", "li", {
	onmousover: function () { if (PT.SL.SelectedObject != this) this.className = 'over'; },
	onmouseout: function () { if (PT.SL.SelectedObject != this) this.className = ''; },
	onmousedown: function () {
		if (PT.SL.SelectedObject) PT.SL.SelectedObject.className = '';
		PT.SL.SelectedObject = this;
		this.className = 'selected';
		PT.SIH.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + this.ElementID);
	},
	ondblclick: function () {
		var imgd = document.createElement("img");
		imgd.src = "cross_12x12.png";
		imgd.alt = "delete";
		imgd.onclick = function () { PT.SSL.Delete(this.parentNode); PT.SSL.Draw(); }
		PT.SSL.Add(this.ElementID, this.firstChild.nodeValue, imgd);
		PT.SSL.Draw();
	}
});

// Selected Suppliers List
PT.SSL = new MultipleElementList("PT-slistS", "li", {
	onmousover: function () { if (PT.SSL.SelectedObject != this) this.className = 'over'; },
	onmouseout:function () { if (PT.SSL.SelectedObject != this) this.className = ''; },
	onmousedown: function () {
		if (PT.SAL.SelectedObject) PT.SSL.SelectedObject.className = '';
		PT.SSL.SelectedObject = this;
		this.className = 'selected';
		PT.AIH.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + this.ElementID);
	},
	ondblclick: function () {
		PT.SSL.Delete(this);
		PT.SSL.Draw();
	}
});
PT.SearchMenuS.Draw();

PT.SL.selectList = PT.SSL;

/* AJAX Response Functions
*******************************************************************************/
PT.ElementListProcessResponse = function (el, xhr) {
	el.Clean();
	el.Clear();
	
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	if (mainsplit[0] == '') // Pas d'erreur
	{
		var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);
		for (var i = 0; i < outputs.length-1; i++)
		{
			var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
			if (outputID.length == 2)
			{
				var imga = document.createElement("img");
				imga.src = "arrow_right_14x12.png";
				imga.alt = "add";
				imga.onclick = function() {
					var imgd = document.createElement("img");
					imgd.src = "cross_12x12.png";
					imgd.alt = "delete";
					imgd.onclick = function() { el.selectList.Delete(this.parentNode); el.selectList.Draw(); }
					el.selectList.Add(this.parentNode.ElementID, this.parentNode.firstChild.nodeValue, imgd);
					el.selectList.Draw();
				}
				el.Add(outputID[0], outputID[1], imga);
			}
		}
		el.Draw();
	}
	else
	{
		document.getElementById(el.id).innerHTML = mainsplit[0];
	}
}

PT.InfosProcessResponse = function (id, xhr) {
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	if (mainsplit[0] == '') // Pas d'erreur
		document.getElementById(id).innerHTML = mainsplit[1];
	else
		document.getElementById("PerfReqLabelPromotions").innerHTML = mainsplit[0];
}

/* Promotion Edit Window */
PT.ApplyOnChange = function()
{
	switch (parseInt($("#PT-apply").val()))
	{
		case <?php echo PROM_APPLY_ALL ?> : 
			document.getElementById("PT-apply-buttons").style.display = "none";
			break;
		case <?php echo PROM_APPLY_SPECIFIED ?> :
			document.getElementById("PT-apply-buttons").style.display = "block";
			break;
		default : break;
	}
}

PT.AJAXHandleObject = {
	type : "GET",
	url: "<?php echo AJAX_RESSOURCES_PATH ?>Promotions.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#PerfReqLabelPromotions").text(textStatus);
	},
	success: function (rjo, textStatus) {
		if (rjo.error) $("#PerfReqLabelPromotions").text(rjo.error);
		else
		{
			switch(rjo.action)
			{
				case "get" :
					$("#PT-id").text(rjo.data.id);
					$("#PT-type").val(rjo.data.type);
					$("#PT-type_value").val(parseFloat(rjo.data.type_value));
					$("#PT-apply").val(rjo.data.apply);
					if (rjo.data.apply != <?php echo PROM_APPLY_ALL ?>)
						document.getElementById("PT-apply-buttons").style.display = "block";
					else
						document.getElementById("PT-apply-buttons").style.display = "none";
					
					// advID1, advID2, ..., advIDn ; catID1, catID2, ..., catIDn ; pcatID1, pdtID11, pdtID12, ..., pdtID1n | pcatID2, pdtID21, pdtID22, ..., pdtID2n | ... | pcatIDn, pdtIDn1, ...
					var avb = rjo.data.apply_value.split(";"); // Apply values blocks
					$("#PT-apply_value_A").val(avb[0]);
					$("#PT-apply_value_C").val(avb[1]);
					$("#PT-apply_value_P").val(avb[2]);
					
					$("#PT-end_trigger").val(rjo.data.end_trigger);
					$("#PT-end_trigger_value").val(rjo.data.end_trigger_value);
					$("#PT-end_trigger_current").val(rjo.data.end_trigger_current);
					$("#PT-code").val(rjo.data.code);
					$("#PT-picture").val(rjo.data.picture);
					$("#PT-start_time").val(GetDateYMDHM(new Date(parseInt(rjo.data.start_time)*1000)));
					$("#PT-end_time").val(GetDateYMDHM(new Date(parseInt(rjo.data.end_time)*1000)));
					$("#PT-active").get(0).checked = rjo.data.active == 1 ? true : false;
					$("#PT-create_time").text(GetDateYMDHM(new Date(parseInt(rjo.data.create_time)*1000)));
					$("#PT-timestamp").text(GetDateYMDHM(new Date(parseInt(rjo.data.timestamp)*1000)));
					
					$("#PerfReqLabelPromotions").text("");
					PT.PEDB.action = "alter";
					PT.PEDB.Show();
				break;
				
				case "alter" :
					if (rjo.data.apply_value.length > 30) rjo.data.apply_value = rjo.data.apply_value.substr(0,27) + "...";
					var rowData = [
						parseInt(rjo.data.id),
						PT.promotion_type_list[parseInt(rjo.data.type)],
						PT.promotion_apply_list[parseInt(rjo.data.apply)],
						PT.promotion_end_trigger_list[parseInt(rjo.data.end_trigger)],
						rjo.data.code,
						GetDateYMDHM(new Date(parseInt(rjo.data.start_time)*1000)),
						GetDateYMDHM(new Date(parseInt(rjo.data.end_time)*1000)),
						PT.promotion_active_list[rjo.data.active],
						GetDateYMDHM(new Date(parseInt(rjo.data.create_time)*1000))
					];
					PT.PLMT.AlterRow(PT.PLMT.getRowByIndex(rowData[0], 0), rowData);
					PT.PEDB.Hide();
				break;
				
				case "add" :
					if (rjo.data.apply_value.length > 30) rjo.data.apply_value = rjo.data.apply_value.substr(0,27) + "...";
					var rowData = [[
						parseInt(rjo.data.id),
						PT.promotion_type_list[parseInt(rjo.data.type)],
						PT.promotion_apply_list[parseInt(rjo.data.apply)],
						PT.promotion_end_trigger_list[parseInt(rjo.data.end_trigger)],
						rjo.data.code,
						GetDateYMDHM(new Date(parseInt(rjo.data.start_time)*1000)),
						GetDateYMDHM(new Date(parseInt(rjo.data.end_time)*1000)),
						PT.promotion_active_list[rjo.data.active],
						GetDateYMDHM(new Date(parseInt(rjo.data.create_time)*1000))
					]];
					PT.PLMT.AddRow(rowData);
					PT.PLMT.Refresh();
					PT.PLPS1.setLastPage(PT.PLMT.getLastPage()); PT.PLPS1.Refresh();
					PT.PLPS2.setLastPage(PT.PLMT.getLastPage()); PT.PLPS2.Refresh();
					PT.PEDB.Hide();
				break;
				
				case "delete" :
					document.location.href = document.location.href;
				break;
					
				default: break;
			}
		}
	}
};

PT.addPromotion = function () {
	$("#PT-id").text("Nouvelle promotion");
	$("#PT-type").val(0);
	$("#PT-type_value").val(0);
	$("#PT-apply").val(0);
	document.getElementById("PT-apply-buttons").style.display = "none";
	$("#PT-apply_value_A").val(0);
	$("#PT-apply_value_C").val(0);
	$("#PT-apply_value_P").val(0);
	$("#PT-end_trigger").val(0);
	$("#PT-end_trigger_value").val(0);
	$("#PT-end_trigger_current").val(0);
	$("#PT-code").val(0);
	$("#PT-picture").val(0);
	var pdate = new Date;
	$("#PT-start_time").val(GetDateYMDHM(pdate));
	pdate.setDate(pdate.getDate()+7);
	$("#PT-end_time").val(GetDateYMDHM(pdate));
	$("#PT-active").val(true);
	$("#PT-create_time").text(" - ");
	$("#PT-timestamp").text(" - ");
	
	PT.PEDB.action = "add";
	PT.PEDB.Show();
};

// Promotion Edition Dialog Box
PT.PEDB = new HN.Mods.DialogBox("PT-PEDB");
PT.PEDB.action = "alter";
PT.PEDB.setTitleText("Editer une Promotion");
PT.PEDB.setMovable(true);
PT.PEDB.showCancelButton(true);
PT.PEDB.showValidButton(true);
PT.PEDB.setValidFct( function() {
	PT.AJAXHandleObject.url = "<?php echo AJAX_RESSOURCES_PATH ?>Promotions.php?action=" + PT.PEDB.action +
		"&id=" + $("#PT-id").text() +
		"&type=" + $("#PT-type").val() +
		"&type_value=" + $("#PT-type_value").val() +
		"&apply=" + $("#PT-apply").val() +
		"&apply_value=" + [$("#PT-apply_value_A").val(), $("#PT-apply_value_C").val(), $("#PT-apply_value_P").val()].join(";") +
		"&end_trigger=" + $("#PT-end_trigger").val() +
		"&end_trigger_value=" + $("#PT-end_trigger_value").val() +
		"&end_trigger_current=" + $("#PT-end_trigger_current").val() +
		"&code=" + $("#PT-code").val() +
		"&picture=" + $("#PT-picture").val() +
		"&start_time=" + GetUnixDate($("#PT-start_time").val()) +
		"&end_time=" + GetUnixDate($("#PT-end_time").val()) +
		"&active=" + ($("#PT-active").get(0).checked ? 1 : 0);
	$.ajax(PT.AJAXHandleObject);
} );
PT.PEDB.setShadow(true);
PT.PEDB.Build();

/* Promotion List Main Table */
PT.PLMT = new HN.Mods.JSTable();
PT.PLMT.setID("PT-JSTable");
PT.PLMT.setClass("CommonTable");
PT.PLMT.setHeaders(["ID", "Type", "S'applique sur", "Condition de Fin", "Code", "Débute le", "Se termine le", "actif", "date de création"]);
PT.PLMT.setColIndex([0]);
PT.PLMT.setInitialData([
<?php
$res = & $handle->query("
	select
		p.id, p.type, p.apply, p.end_trigger, p.code,
		p.start_time, p.end_time, p.active, p.create_time
	from
		promotions p
	order by
		p.create_time desc", __FILE__, __LINE__, false);
$nbp = $handle->numrows($res, __FILE__, __LINE__);
for ($i = 0; $i < $nbp; $i++)
{
	$rec = & $handle->fetchAssoc($res);
	echo '	[' . (int)$rec['id'] . ', "' . $promotion_type_list[(int)$rec['type']] . '", "' . $promotion_apply_list[(int)$rec['apply']] . '", "' . $promotion_end_trigger_list[(int)$rec['end_trigger']] . '", "' . $rec['code'] . '", "' . date("Y/m/d H:i", $rec['start_time']) . '", "' . date("Y/m/d H:i", $rec['end_time']) . '", "' . $promotion_active_list[(int)$rec['active']] . '", "' . date("Y/m/d H:i", $rec['create_time']) . '"]' . ($i < ($nbp-1) ? "," : "") . "\n";
}

?>
]);
PT.PLMT.setColumnCount(9);
PT.PLMT.setMultiPage(true);
PT.PLMT.setRowCount(20);
PT.PLMT.setCurrentPage(1);
PT.PLMT.setRowFct( {
	onmouseover : function() { this.style.backgroundColor = "#CCCCCC"; },
	onmouseout : function() { this.style.backgroundColor = ""; }
} );
PT.PLMT.setEditTools({
	edit : {
		element : "div",
		attributes : {
			onclick : function() {
				PT.AJAXHandleObject.url = "<?php echo AJAX_RESSOURCES_PATH ?>Promotions.php?action=get&id="+this.parentNode.parentNode.cc[0].textvalue;
				$.ajax(PT.AJAXHandleObject);
			},
			alt : "Editer cette promotion",
			title : "Editer cette promotion"
		}
	},
	del : {
		element : "div",
		attributes : {
			onclick : function() {
				PT.AJAXHandleObject.url = "<?php echo AJAX_RESSOURCES_PATH ?>Promotions.php?action=delete&id="+this.parentNode.parentNode.cc[0].textvalue;
				$.ajax(PT.AJAXHandleObject);
			},
			alt : "Supprimer cette promotion",
			title : "Supprimer cette promotion"
		}
	}
});
PT.PLMT.Refresh();

/* Promotion List Page Switchers */
PT.PLPS1 = new HN.Mods.PageSwitcher();
PT.PLPS1.setID("PT-PageSwitcher1");
PT.PLPS1.setCurrentPage(1);
PT.PLPS1.setLastPage(PT.PLMT.getLastPage());
PT.PLPS1.setTriggerFct( function(page) { PT.PLMT.setCurrentPage(page); PT.PLPS2.setCurrentPage(page); PT.PLMT.Refresh(); PT.PLPS2.Refresh(); } );
PT.PLPS1.Refresh();

PT.PLPS2 = new HN.Mods.PageSwitcher();
PT.PLPS2.setID("PT-PageSwitcher2");
PT.PLPS2.setCurrentPage(1);
PT.PLPS2.setLastPage(PT.PLMT.getLastPage());
PT.PLPS2.setTriggerFct( function(page) { PT.PLMT.setCurrentPage(page); PT.PLPS1.setCurrentPage(page); PT.PLMT.Refresh(); PT.PLPS1.Refresh(); } );
PT.PLPS2.Refresh();

// Return a Date with the format YYYY/MM/DD "string" HH:MM
function GetDateYMDHM (dateO)	{
	var dhs = (arguments[1]) ? arguments[1] + " " : "";
	return dateO.getFullYear() + "/" +
				(dateO.getMonth() < 9 ? "0" : "") + (dateO.getMonth()+1) + "/" +
				(dateO.getDate() < 10 ? "0" : "") + dateO.getDate() +" " + dhs +
				(dateO.getHours() < 10 ? "0" : "") + dateO.getHours() + ":" +
				(dateO.getMinutes() < 10 ? "0" : "") + dateO.getMinutes();
}

// Take a YMDh(m)(s) string date and return a UNIX timestamp (since 1/1/1970 00:00:00)
function GetUnixDate (dateYMDhms) {
	var dp = dateYMDhms.split(" "); // Date Parts
	var date = dp[0].split("/");
	var time = dp[dp.length-1].split(":");
	var dateO = new Date(
		parseInt(date[0],10), // Year YYYY
		parseInt(date[1],10)-1, // Month MM
		parseInt(date[2],10), // Day DD
		parseInt(time[0],10), // Hours hh
		time[1] ? parseInt(time[1],10) : 00, // Minutes mm
		time[2] ? parseInt(time[2],10) : 00 // Seconds ss
	);
	return dateO.getTime()/1000;
}

</script>
</div>
<div id="log"></div>
<?php

require(ADMIN . 'tail.php');

?>
