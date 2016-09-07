<?php
/*================================================================/

	Techni-Contact V2 - MD2I SAS
	http://www.techni-contact.com

	Auteur : Hook Network SARL - http://www.hook-network.com
	Date de création : 20 décembre 2004

	Fichier : /secure/manager/config.php
	Description : Fichier de configuration global de l'application Web

/=================================================================*/

$tab_microtime = array();
$tab_microtime["start"] = microtime(true);

define("DEBUG", true);
define("TEST", true);
if (defined("PREVIEW") || TEST) {
	define("SHOW_TAGS", false);
}
else {
	define("SHOW_TAGS", true);
}


/******************************************************************************
** Local Constants
******************************************************************************/

define('DB_LANGUAGE', 'fr');

/******************************************************************************
** Global Constants and variables
******************************************************************************/

define('VERSION', '1.3');
define('SERVER_IP', isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : "");
define('TIME_ORIGIN', 1109631600); // 03/01/2005 (2005 march 1)

// Language list (easily extendable)
// DB_LANGUAGE constant _must_ be one of the following keys
$language_list = array(
	"fr" => array("name" => "fran&ccedil;ais",	"name_fr" => "fran&ccedil;ais",	"country" => "France",			"country_fr" => "France",		"domain" => "www"),
	"uk" => array("name" => "english",			"name_fr" => "anglais",			"country" => "United Kingdom",	"country_fr" => "Royaume-uni",	"domain" => "uk"),
	"de" => array("name" => "deutsch",			"name_fr" => "allemand",		"country" => "Deutschland",		"country_fr" => "Allemagne",	"domain" => "de"),
	"es" => array("name" => "espaniol",			"name_fr" => "espagnol",		"country" => "Espa&ntilde;a",	"country_fr" => "Espagne",		"domain" => "es"),
	"it" => array("name" => "italiano",			"name_fr" => "italien",			"country" => "Italia",			"country_fr" => "Italie",		"domain" => "it")
);

// URL
define('DOMAIN',             'techni-contact.com');
define('SUBDOMAIN',          'test');
define('FULL_DOMAIN',        SUBDOMAIN.'.'.DOMAIN);
define('URL',                'http://'.FULL_DOMAIN.'/');
define('SECURE_SUBDOMAIN',   'secure-test');
define('SECURE_FULL_DOMAIN', SECURE_SUBDOMAIN.'.'.DOMAIN);
define('SECURE_URL',         'https://'.SECURE_FULL_DOMAIN.'/'.DB_LANGUAGE.'/');

define('STATS_URL',  'http://stats.'.DB_LANGUAGE.'.techni-contact.com/');
define('ADMIN_URL',   SECURE_URL . 'manager/');
define('EXTRANET_URL',SECURE_URL . 'extranet/');
define('COMPTE_URL'  ,SECURE_URL . 'compte/');
define('COMMANDE_URL',SECURE_URL . 'commande/');
define('SECURE_RESSOURCES_URL', SECURE_URL . 'ressources/');
define('PDF_URL'     ,URL . 'pdf/');
define('PDF_DL_URL'  ,URL . 'telecharger-pdf/');
define('PDF_URL_ARC' ,ADMIN_URL.'ressources/PDF_ARC/');

//Marketing
define('MARKETING_URL', 'http://marketing.techni-contact.com/');
define('MARKETING_LIMITATION_GROUPES','2');
define('MARKETING_LIMITATION_FIELDS','12');

// State vars
define("IS_BO", isset($_SERVER['REQUEST_URI']) ? preg_match("/^\/".DB_LANGUAGE."\/manager\//",$_SERVER['REQUEST_URI']) : true);
define("IS_BOP", !IS_BO ? preg_match("/^\/".DB_LANGUAGE."\/extranet\//",$_SERVER['REQUEST_URI']) : false);
define("IS_FO", !IS_BO && !IS_BOP);

// DIRECTORIES PATH
define("BASE_PATH",     dirname(__FILE__)."/");
define("SECURE_PATH",   BASE_PATH."secure/".DB_LANGUAGE."/");
define("WWW_PATH",      BASE_PATH."www/".DB_LANGUAGE."/");
define("INCLUDES_PATH", BASE_PATH."includes/".DB_LANGUAGE."/");
define("LIB_PATH",      BASE_PATH."lib/");
define("LIB_VENDOR_PATH", LIB_PATH."vendor/");
define("DOCTRINE_MODEL_PATH", LIB_PATH."models/");
define("CONTENT_PATH",  BASE_PATH."content/".DB_LANGUAGE."/");
define("XML_PATH",      BASE_PATH."xml/".DB_LANGUAGE."/");
define("CSV_PATH",      BASE_PATH."csv/".DB_LANGUAGE."/");
define("CRON_PATH",     BASE_PATH."cron/".DB_LANGUAGE."/");
define("STATS_PATH",    BASE_PATH."stats/".DB_LANGUAGE."/");
define("JSON_PATH",     BASE_PATH."json/".DB_LANGUAGE."/");
define('LOGS',          BASE_PATH."logs/".DB_LANGUAGE."/");
define('ICLASS',        INCLUDES_PATH."classV3/");
define('CONTROLLER',    INCLUDES_PATH."controllers/");
define('ADMIN',         INCLUDES_PATH."managerV3/");
define('EXTRANET',      INCLUDES_PATH."extranetV3/");
define('SITE',          INCLUDES_PATH."siteV3/");
define('SECURE_SITE',   INCLUDES_PATH."secure_site/");
define('PDF2HTML',      INCLUDES_PATH."/html2pdf/");

define('PDF_PATH', INCLUDES_PATH."files/pdf/");
define('PDF_ARC', PDF_PATH."PDF_ARC/");
define('PDF_ESTIMATE', PDF_PATH."PDF_ESTIMATE/");
define('PDF_ORDER', PDF_PATH."PDF_ORDER/");
define('PDF_INVOICE', PDF_PATH."PDF_INVOICE/");
define('ADMIN_UPLOAD_DIR', INCLUDES_PATH."files/manager-uploads/");
define('BO_UPLOAD_DIR', ADMIN_URL."ressources/bo-uploads/");
define('MINI_STORE_PICS_PATH', INCLUDES_PATH.'files/images/mini-boutique/');
define('MSPP_HOME', MINI_STORE_PICS_PATH.'home/');
define('MSPP_VIGN', MINI_STORE_PICS_PATH.'vignette/');
define('MSPP_ESPA', MINI_STORE_PICS_PATH.'espace/');
define('URL_RESS_MS', URL.'ressources/images/mini-boutique/');
define('URL_MSPP_HOME', URL_RESS_MS.'home/');
define('URL_MSPP_VIGN', URL_RESS_MS.'vignette/');
define('URL_MSPP_ESPA', URL_RESS_MS.'espace/');
define('__MAX_MINI_STORE_HOME__', 4);

define('SQL_LOG_FILE', LOGS."mysql-error.log");
define('PHP_LOG_FILE', "/data/log/php.log");

// Old for compatibility
define("INCLUDES", BASE_PATH."includes/".DB_LANGUAGE."/");

// Stats
define("FIRST_YEAR_STATS", mktime(0,0,0,1,1,2008)); // 01/01/2008 00:00

// Config XML
define("XML_FORM_CONTENT", XML_PATH . "form-content.xml");

// Categories XML
define("XML_CATEGORIES_MENU", XML_PATH . "categories-menu.xml");
define("XML_CATEGORIES_SIMPLE", XML_PATH . "categories-simple.xml");
define("XML_CATEGORIES_ALL", XML_PATH . "categories-all.xml");
define("XML_KEY_PREFIX", "_");

// Categories XML
define("JSON_CATEGORIES_MENU", JSON_PATH.'categories-menu.js');

// Categories constants
define("CAT3_PDT_COUNT_PER_PAGE", 100);
define("CAT3_PREVIEW_PDT_SELECTION_COUNT", 3);
define("CAT3_PDT_PIC_COUNT", 1);
define("CAT2_CAT3_MENU_COUNT", 5);

// parametre public
define("PUB_HEADER", "large");
define("PUB_PAVE", "pave");

// paramètre client
define('CLIENT_MAX_ADDRESS_BY_TYPE', 5);

// Weight

// Maintenance/Test
define("MAINTENANCE_URL", ADMIN_URL."maintenance/");
define("MAINTENANCE_PATH", SECURE_PATH."manager/maintenance/");
define("EMAIL_URL", WWW_PATH."/email.html");

// Logos annonceurs
define('ADVERTISERS_LOGOS_INC', INCLUDES_PATH . 'files/images/logos-annonceurs/');
define('ADVERTISERS_LOGOS_URL', URL . 'images/annonceurs/logos/');

// Images produits
define('PRODUCTS_IMAGE_DFT', URL."ressources/images/pdt-sample-01.jpg");
define('PRODUCTS_IMAGE_INC', INCLUDES_PATH."files/images/products/");
define('PRODUCTS_IMAGE_URL', URL."ressources/images/produits/");
define('PRODUCTS_IMAGE_SECURE_DFT', SECURE_URL."ressources/images/pdt-sample-01.jpg");
define('PRODUCTS_IMAGE_SECURE_URL', SECURE_URL."ressources/images/produits/");
define('PRODUCTS_IMAGE_ADV_INC', INCLUDES_PATH."files/images/products_adv/");
define('PRODUCTS_IMAGE_ADV_URL', SECURE_URL."ressources/images/produits_adv/");

// Fichiers pfd & doc fiches produits
define('PRODUCTS_FILES_INC', INCLUDES_PATH."files/data/products/");
define('PRODUCTS_FILES_URL', URL."docs/produits/");
define('PRODUCTS_FILES_SECURE_URL', SECURE_URL."docs/produits/");
define('PRODUCTS_FILES_ADV_INC', INCLUDES_PATH."files/data/products_adv/");
define('PRODUCTS_FILES_ADV_URL', SECURE_URL."docs/produits_adv/");

// Couvertures Catalogues
define('CAT_INC', INCLUDES_PATH . 'files/images/catalogues/');
define('CAT_URL', URL . 'images/catalogues/');

// Bibliothèque d'images
define('BIBLI_INC', INCLUDES_PATH . 'files/images/bibli/');
define('BIBLI_URL', URL . 'images/bibli/');

// Contenu fichier divers
define('MISC_INC', CONTENT_PATH);
define('LANG_LOCAL_INC', CONTENT_PATH . "local_constants/");

define('BECOME_ADVERTISER_MAIL'   , 'f.stumm@techni-contact.com');
define('BECOME_ADVERTISER_MAIL_CC', 'e.verry@techni-contact.com');

define('RECEIVE_CATS_MAIL'   , 'info@techni-contact.com');

define('SEND_MAIL',      'web@techni-contact.com');
define('SEND_MAIL_NAME', 'Service client Techni-Contact');

define('MAIL_ADVERTISER', 'info@techni-contact.com');
define('MAIL_NEWSLETTER', 'newsletter@techni-contact.com');

// N°ID Techni-Contact par défaut
define("__ID_TECHNI_CONTACT__", 61049);
define("__ID_TECHNI_CONTACT_BOUSER__", 29059);

// Nombre de niveau de famille par défaut
define("__MAX_DEPTH__", 3);

// TVA par défaut
define('__FPD_IDTVA_DFT__', 1);
define('DFT_TVA_CODE', 1);

// Catégorie d'annonceurs
define("__ADV_CAT_ADVERTISER__", 0);
define("__ADV_CAT_SUPPLIER__", 1);
define("__ADV_CAT_ADVERTISER_NOT_CHARGED__", 2);
define("__ADV_CAT_PROSPECT__", 3);
define("__ADV_CAT_BLOCKED__", 4);
define("__ADV_CAT_LITIGATION__", 5);

$adv_cat_list = array(
	__ADV_CAT_ADVERTISER__ => array("name" => "Annonceur", "desc" => "Annonceur Techni-Contact", "pre" => "de l'Annonceur", "acronym" => "A"),
	__ADV_CAT_SUPPLIER__ => array("name" => "Fournisseur", "desc" => "Fournisseur Techni-Contact", "pre" => "du Fournisseur", "acronym" => "F"),
	__ADV_CAT_ADVERTISER_NOT_CHARGED__ => array("name" => "Annonceur non facturé", "desc" => "Annonceur Techni-Contact non facturé", "pre" => "de l'Annonceur non facturé", "acronym" => "AF"),
	__ADV_CAT_PROSPECT__ => array("name" => "Prospect", "desc" => "Annonceur Techni-Contact", "pre" => "du Prospect", "acronym" => "P"),
	__ADV_CAT_BLOCKED__ => array("name" => "Annonceur bloqué", "desc" => "Annonceur Techni-Contact Bloqué", "pre" => "de l'Annonceur bloqué", "acronym" => "AB"),
	__ADV_CAT_LITIGATION__ => array("name" => "Litige de paiement", "desc" => "Annonceur Techni-Contact en litige de paiement", "pre" => "de l'Annonceur en litige de paiement", "acronym" => "LP")
);

// Common cst for the contacts generation
define("__CONTACT_COUNT_SOLELY_COUNTRY__", "FRANCE");
define("__CONTACT_COUNT_REGEXP_JOB_EXLUDE", "/^.?TAGIAIR.*$/");
define("__CONTACT_PRICE__", 15);

// Leads status (bitwise)
define("__LEAD_CHARGED__", 0x1); // if the lead is charged = 1
define("__LEAD_REJECTED__", 0x2); // if it is rejected = 2
define("__LEAD_DOUBLET__", 0x4); // if it is a doublet = 4
define("__LEAD_VISIBLE__", 0x8); // if the advertiser can see it's personal information = 8
define("__LEAD_REJECTABLE__", 0x10); // if the advertiser can reject it = 16
define("__LEAD_REJECTION_REFUSED__", 0x20); // if the lead has been refused for a rejection = 32
define("__LEAD_FORFEIT__", 0x40); // the advertiser's lead has a forfeit = 64
define("__LEAD_CHARGEABLE__", 0x80); // the lead is not yet charged = 128
define("__LEAD_CREDITED__", 0x100); // the lead was charged then rejected, it is credited over the next month = 256
define("__LEAD_DISCHARGED__", 0x200); // the lead was charged then rejected then paid back = 512

define("__LEAD_INVOICE_STATUS_NOT_CHARGED__", 0); // = 0
define("__LEAD_INVOICE_STATUS_CHARGED__", __LEAD_CHARGED__ | __LEAD_VISIBLE__ | __LEAD_REJECTABLE__); // = 25
define("__LEAD_INVOICE_STATUS_CHARGEABLE__", __LEAD_CHARGEABLE__ | __LEAD_VISIBLE__ | __LEAD_REJECTABLE__); // = 152
define("__LEAD_INVOICE_STATUS_CHARGED_PERMANENT__", __LEAD_CHARGED__ | __LEAD_VISIBLE__); // = 9
define("__LEAD_INVOICE_STATUS_REJECTED__", __LEAD_REJECTED__); // = 2
define("__LEAD_INVOICE_STATUS_REJECTED_WAIT__", __LEAD_CHARGED__ | __LEAD_REJECTED__ | __LEAD_VISIBLE__ | __LEAD_REJECTABLE__); // = 27
define("__LEAD_INVOICE_STATUS_CHARGEABLE_REJECTED_WAIT__", __LEAD_CHARGEABLE__ | __LEAD_REJECTED__ | __LEAD_VISIBLE__ | __LEAD_REJECTABLE__); // = 154
define("__LEAD_INVOICE_STATUS_REJECTED_REFUSED__", __LEAD_CHARGED__ | __LEAD_VISIBLE__ | __LEAD_REJECTION_REFUSED__); // = 41
define("__LEAD_INVOICE_STATUS_DOUBLET__", __LEAD_DOUBLET__ | __LEAD_VISIBLE__); // = 12
define("__LEAD_INVOICE_STATUS_IN_FORFEIT__", __LEAD_FORFEIT__ | __LEAD_VISIBLE__); // = 72
define("__LEAD_INVOICE_STATUS_CREDITED__", __LEAD_REJECTED__ |__LEAD_CREDITED__); // = 258
define("__LEAD_INVOICE_STATUS_DISCHARGED__", __LEAD_REJECTED__ | __LEAD_DISCHARGED__); // = 514

$lead_invoice_status_list = array(
  __LEAD_INVOICE_STATUS_NOT_CHARGED__ => "non facturé",
  __LEAD_INVOICE_STATUS_CHARGED__ => "facturé",
  __LEAD_INVOICE_STATUS_CHARGEABLE__ => "facturable",
  __LEAD_INVOICE_STATUS_CHARGED_PERMANENT__ => "facturé",
  __LEAD_INVOICE_STATUS_REJECTED__ => "rejeté",
  __LEAD_INVOICE_STATUS_REJECTED_WAIT__ => "demande de rejet en attente de validation",
  __LEAD_INVOICE_STATUS_CHARGEABLE_REJECTED_WAIT__ => "demande de rejet en attente de validation",
  __LEAD_INVOICE_STATUS_REJECTED_REFUSED__ => "rejet refusé",
  __LEAD_INVOICE_STATUS_DOUBLET__ => "doublon non facturé",
  __LEAD_INVOICE_STATUS_IN_FORFEIT__ => "compris dans le forfait",
  __LEAD_INVOICE_STATUS_CREDITED__ => "rejeté - déduit de la période de ",
  __LEAD_INVOICE_STATUS_DISCHARGED__ => "rejeté - avec avoir "
);

// Lead Processing Status
define("__LEAD_P_STATUS_NOT_PROCESSED__", 1);
define("__LEAD_P_STATUS_PROCESSED__", 2);
define("__LEAD_P_STATUS_NOT_PROCESSABLE__", 3);
$lead_processing_status_list = array(
  __LEAD_P_STATUS_NOT_PROCESSED__ => "à traiter",
  __LEAD_P_STATUS_PROCESSED__ => "envoyé",
  __LEAD_P_STATUS_NOT_PROCESSABLE__ => "intraitable"
);

// Constantes pour communication AJAX
define("__MAIN_SEPARATOR__", "<_main_separator_eMhv7g5tsVq0LlG2>");
define("__ERROR_SEPARATOR__", "<_error_separator_WKQ2d5F57BQ8na45>");
define("__ERRORID_SEPARATOR__", "<_errorID_separator_sv23lD4XvFYkG3Fx>");
define("__OUTPUT_SEPARATOR__", "<_output_separator_p85ji19H1FVpP0Zl>");
define("__OUTPUTID_SEPARATOR__", "<_outputID_separator_97ryC71mLF7De7u9>");
define("__DATA_SEPARATOR__", "<_data_separator_m883aylf5cF8JOSR>");
define("__DATA_SEPARATOR2__", "<_data_separator2_SFnG7K6UK6JBdo2v>");

// Import and Import Product Constants
// Import type constants
define('__IMPT_PDT__', 0x1);
define('__IMPT_UPDT_SUPPLIER__', 0x2);

define('__IMPORT_TYPE__', __IMPT_PDT__ | __IMPT_UPDT_SUPPLIER__);

// N = Not Valid = 100 ; V = Valid = 10 ; F = Finalized = 1;
// ex: VF = 10 + 100 = 110
define("__I_NVF__", 111);
define("__I_NV__", 110);
define("__I_NF__", 101);
define("__I_N__", 100);
define("__I_VF__", 011);// = 9
define("__I_V__", 010); // = 8
define("__I_F__", 001);
define("__I_0__", 000);

define("__IP_NOT_VALID__", 0);
define("__IP_NOT_VALID_UPDATE__", 1);
define("__IP_VALID__", 10);
define("__IP_VALID_UPDATE__", 11);
define("__IP_FINALIZED__", 20);
define("__IP_FINALIZED_UPDATE__", 21);

define("__DEFAULT_PRICE__", "sur demande");

// Discounts/Promotions/Constraint Constants
/* Discounts Constants */
define("DISC_TYPE_AMOUNT", 0);
define("DISC_TYPE_QUANTITY", 1);
define("DISC_APPLY_ALL_PRODUCTS", 0);
define("DISC_APPLY_SPECIFIED_PRODUCTS", 1);
define("DISC_PRIORITY_BEFORE", 0);
define("DISC_PRIORITY_AFTER", 1);

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


// Flood protection for the "send this product's sheet to a friend" online option
define("__MAIL_FLOOD_PROTECTION_TIME__", 30);

// period during which a lead is rejectable 2592000 seconds = 30 days
define("__REJECT_ALLOWANCE_DURATION__", 2592000);

// Messenger, defines all possible context and actions
define('__MSGR_CTXT_SUPPLIER_TC_ORDER__', 0x1); // 1 context : between TC & supplier, action : orders
define('__MSGR_CTXT_SUPPLIER_TC_LEAD__', 0x2); // 2 context : between TC & supplier, action : leads
define('__MSGR_CTXT_CUSTOMER_TC_LEAD__', 0x4); // 4  context : between TC & customer, action : leads
define('__MSGR_CTXT_CUSTOMER_TC_CMD__', 0x8); // 8 context : between TC & customer, action : commandes
define('__MSGR_CTXT_CUSTOMER_TC_DEVIS_PDF__', 0x10); // 16 context : between TC & customer, action : devis PDF
define('__MSGR_CTXT_CUSTOMER_ADVERTISER_LEAD__', 0x20); // 32 context : between customer & advertiser, action : leads, both ways
define('__MSGR_CTXT_CUSTOMER_TC_ESTIMATE__', 0x40); // 64 context : between TC & customer, action : estimates
define('__MSGR_CTXT_CUSTOMER_TC_INVOICE__', 0x80); // 128 context : between TC & customer, action : invoices
define('__MSGR_CTXT_ORDER_CMD__', __MSGR_CTXT_SUPPLIER_TC_ORDER__ | __MSGR_CTXT_CUSTOMER_TC_CMD__); // 9

// defines messenger users types
define('__MSGR_USR_TYPE_ADV__', 1); // context : between TC & supplier, action : orders
define('__MSGR_USR_TYPE_INT__', 2); // context : between TC & supplier, action : leads
define('__MSGR_USR_TYPE_BOU__', 3);

// defines threshold price(in euros) for product as estimate
define('__THRESHOLD_PRICE_FOR_ESTIMATE__', 5000);

// defines regex
define('REGEX_TEL', '/^[0-9+-.\/ ()]{5,30}$/'); // http://www.hook-network.com/storm/tasks/2012/11/05/modification-de-la-structure-dun-num%C3%A9ro-de-t%C3%A9l%C3%A9phone

// Be2Bill
define('BE2BILL_URL', 'https://secure-test.be2bill.com/front/form/process');
define('BE2BILL_IDENTIFIER', 'TECHNI CONTACT');
define('BE2BILL_PASSWORD', ',t-9&ed?DfT:vTx/');

// Avis verifies
define('AVIS_VERIFIES_ID_WEBSITE', '4493aa3e-76e9-d9a4-75f9-40b949e1bed0');
define('AVIS_VERIFIES_SECURE_KEY', 'd8b65446-e4c6-bbf4-d12d-4f04e17a7fc8');
define('AVIS_VERIFIES_DELAI_AVANT_EMISSION_AVIS', 19);
define('AVIS_VERIFIES_URL', 'http://www.avis-verifies.com/index.php');
function AC_encode_base64($sData) {
  $sBase64 = base64_encode($sData);
  return strtr($sBase64, '+/', '-_');
}
function AC_decode_base64($sData) {
  $sBase64 = strtr($sData, '-_', '+/');
  return base64_decode($sBase64);
}

// websites
define('WEBSITE_ORIGIN_TC', 'TC');
define('WEBSITE_ORIGIN_MOBANEO', 'MOB');
$website_origin_list = array(
  WEBSITE_ORIGIN_TC => "Techni-Contact",
  WEBSITE_ORIGIN_MOBANEO => "Mobaneo"
);
$website_origin_url_list = array(
  WEBSITE_ORIGIN_TC => URL,
  WEBSITE_ORIGIN_MOBANEO => "http://www.mobaneo.com/"
);

/******************************************************************************
** File Upload
******************************************************************************/

// credential, file prefix, final directory params according to context
// no credential = allow from all
$uploadContextData = array(
  // page from which we are allowed to upload with default directory
  'estimates-estimate-detail' => array('credential' => 'm-comm--sm-estimates', 'file_prefix' => 'doc-com-', 'dir' => 'estimates/'),
  'orders-order-detail' => array('credential' => 'm-comm--sm-orders', 'file_prefix' => 'doc-com-', 'dir' => 'commandes/'),
  'supplier-orders-supplier-order-detail' => array('credential' => 'm-comm--sm-partners-orders', 'file_prefix' => 'doc-com-', 'dir' => 'supplier-orders/'),
  // temp messenger post linked files
  'lead-tmppjmess' => array('file_prefix' => 'tmppjmess-com-', 'dir' => 'leads/'),
  'estimate-tmppjmess' => array('credential' => 'm-comm--sm-estimates', 'file_prefix' => 'tmppjmess-com-', 'dir' => 'estimates/'),
  'order-tmppjmess' => array('credential' => 'm-comm--sm-orders', 'file_prefix' => 'tmppjmess-com-', 'dir' => 'commandes/'),
  'supplier-order-tmppjmess' => array('credential' => 'm-comm--sm-partners-orders', 'file_prefix' => 'tmppjmess-com-', 'dir' => 'supplier-orders/'),
  // messenger post linked files
  'lead-pjmess' => array('file_prefix' => 'contact-pjmess-com-', 'dir' => 'messenger/'),
  'estimate-pjmess' => array('credential' => 'm-comm--sm-estimates', 'file_prefix' => 'estimate-pjmess-com-', 'dir' => 'messenger/'),
  'order-pjmess' => array('credential' => 'm-comm--sm-orders', 'file_prefix' => 'order-pjmess-com-', 'dir' => 'messenger/'),
  'supplier-order-pjmess' => array('credential' => 'm-comm--sm-partners-orders', 'file_prefix' => 'supplier-order-pjmess-com-', 'dir' => 'messenger/'),



  //###Module InternalNotes With Attachments !
  //'credential' from table bo_fonctionalities	#	'file_prefix' Is the prefix for the uploaded file	#  'dir'  Is the directory of upload !

  // Page Production => Gestion partenaires
  'production-fournisseur-pjmess' => array('credential' => 'm-prod--sm-partners', 'file_prefix' => 'fournisseur-tmppjmess', 'dir' => 'internal_notes_fournisseurs/'),
  //Page Commercial => Gestion des commandes
  'order-internalnotes-pjmess' => array('credential' => 'm-comm--sm-orders', 'file_prefix' => 'tmppjmess-com-', 'dir' => 'internal_notes_commandes/'),
  //Page Commercial => Gestion des ordres fournisseurs
  'supplier-orders-internalnotes-pjmess' => array('credential' => 'm-comm--sm-partners-orders', 'file_prefix' => 'doc-com-', 'dir' => 'internal_notes_supplier-orders/'),
  //Page Commercial => Gestion des devis
  'estimate-internalnotes-tmppjmess' => array('credential' => 'm-comm--sm-estimates', 'file_prefix' => 'estimate-tmppjmess-com-', 'dir' => 'internal_notes_estimates/'),
  //Page Commercial => Gestion des factures et avoirs
  'invoice-internalnotes-tmppjmess' => array('credential' => 'm-comm--sm-invoices', 'file_prefix' => 'invoice-tmppjmess-com-', 'dir' => 'internal_notes_invoices/'),
  //Page Commercial => Rechercher un client
  'clients-internalnotes-tmppjmess' => array('credential' => 'm-comm--sm-customers', 'file_prefix' => 'client-tmppjmess-com-', 'dir' => 'internal_notes_clients/'),
  //Page Production => MAJ Fournisseurs
  'production-fournisseurs-update-price' => array('credential' => 'm-prod--sm-maj-fournisseurs', 'file_prefix' => 'fournisseurs-tmppjmess-com-', 'dir' => 'fournisseurs_updateprice_products/'),



);
$upCtxPre = IS_BO ? "bo-" : (IS_BOP ? "bop-" : (IS_FO ? "fo-" : ""));

// list of required files mime types
$boValidMimeTypes = array(
  'application/pdf' => 'pdf',                  // .pdf
  'application/x-pdf' => 'pdf',
  'application/acrobat' => 'pdf',
  'applications/vnd.pdf' => 'pdf',
  'text/pdf' => 'pdf',
  'text/x-pdf' => 'pdf',
  'application/download' => 'pdf',             // .pdf for ff
  'application/x-download' => 'pdf',           // "

  'application/msword' => 'doc',              // .doc
  'application/doc' => 'doc',
  'appl/text' => 'doc',
  'application/vnd.msword' => 'doc',
  'application/vnd.ms-word' => 'doc',
  'application/winword' => 'doc',
  'application/word' => 'doc',
  'application/x-msw6' => 'doc',
  'application/x-msword' => 'doc',

  'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx', // .docx

  'image/jpeg' => 'jpg',                       // .jpg and .jpeg
  'image/jpg' => 'jpg',
  'image/jp_' => 'jpg',
  'image/jpe_' => 'jpg',
  'application/jpg' => 'jpg',
  'application/x-jpg' => 'jpg',
  'image/pjpeg' => 'jpg',
  'image/pipeg' => 'jpg',
  'image/vnd.swiftview-jpeg' => 'jpg',
  'image/x-xbitmap' => 'jpg',

  'application/postscript' => 'eps',          // .eps encapsuled postscript
  'application/eps' => 'eps',
  'application/x-eps' => 'eps',
  'image/eps' => 'eps',
  'image/x-eps' => 'eps',

  'image/photoshop' => 'psd',                 // .psd
  'image/x-photoshop' => 'psd',
  'image/psd' => 'psd',
  'application/photoshop' => 'psd',
  'application/psd' => 'psd',
  'zz-application/zz-winassoc-psd' => 'psd',

  'application/octet-stream' => 'psd',

  'csv' => 'csv',								//Excel
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
  'application/vnd.ms-excel' => 'xls'

);

/******************************************************************************
** Doctrine bootstrap
******************************************************************************/
require_once(LIB_VENDOR_PATH.'doctrine/Doctrine.php');
spl_autoload_register(array('Doctrine', 'autoload'));
spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
$manager = Doctrine_Manager::getInstance();
$conn = Doctrine_Manager::connection('mysql://technico:os2GL72yOF6wBl6m@localhost/technico-test','doctrine');
$conn->setCharset('utf8');
$conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
$manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
$manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
//$manager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
Doctrine_Core::loadModels(DOCTRINE_MODEL_PATH);


/******************************************************************************
** Auto Id Record
** Base Doctrine Record Class modified to have an auto generate Id when saving
******************************************************************************/
abstract class Auto_Id_Record extends Doctrine_Record
{

  // generate a random id for a specified field
  public function genId($id_field = null) {
    if (!isset($id_field))
      $id_field = $this->_table->getIdentifier();

    $cols = $this->_table->getColumns();
    if (!isset($cols[$id_field]))
      throw new Exception("ID gen failed : invalid column name ".$id_field);

    switch ($cols[$id_field]['type']) {
      case 'integer':

        //$bits = ($cols[$id_field]['length'] << 3) - 1 - !$cols[$id_field]['unsigned'];
        if (isset($this->maxId))
          $max = $this->maxId;
        else {
          $bits = ($cols[$id_field]['length'] << 3) - 2 - !$cols[$id_field]['unsigned'];
          $max = (((1 << $bits)-1) << 1) + 1;
        }
        //$min = ($max >> 8) + 1;
        $min = ($max >> 7) + 1;
        $this->$id_field = mt_rand($min,$max);

        break;

      case 'string':
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $li = strlen($chars) - 1;
        $s = '';
        for ($i=0; $i<$cols[$id_field]['length']; $i++)
          $s .= $chars[mt_rand(0, $li)];
        $this->$id_field = $s;
        break;
      default:
        throw new Exception("ID gen failed : invalid column type ".$cols[$id_field]['type']);
    }
  }

  // generate a random id for a specified field that is currently free in the DB
  public function genFreeId($id_field = null) {
    if (!isset($id_field))
      $id_field = $this->_table->getIdentifier();
    $tableOptions = $this->_table->getOptions();
    $dbh = $this->_table->getConnection()->getDbh(); // get PDO for fastest query
    do $this->genId($id_field);
    while ($dbh->query('SELECT 1 FROM `'.$tableOptions['tableName'].'` WHERE `'.$id_field.'` = \''.$this->$id_field.'\'')->rowCount());
  }

  public function save(Doctrine_Connection $conn = null) {
    $id = $this->_table->getIdentifier();
    if (is_scalar($id)) { // auto gen and id only for unique identifier
      if (empty($this->$id))
        $this->genId($id);
      // avoid a SELECT request by just trying to insert directly
			try {
        parent::save($conn);
      } catch (Exception $e) {
        $tc = 10; // auto retry 10 tries if there is a collision
        while (isset($e) && $e->getCode() == 23000 && $tc--) { // 23000 = identifier already exists
          unset($e);
          if (is_scalar($id))
            $this->genId($id);
          try { parent::save($conn); }
          catch (Exception $e) {}
        }
				if (isset($e))
          throw new Exception($e);

      }
    } else {
      parent::save($conn); // default behavior
    }
  }
}

/******************************************************************************
** Form Exception
** Return the list of not valid fields as an array with getErrors()
******************************************************************************/
class FormException extends Exception {
  protected $errorStack = array();

  public function __construct($errorStack) {
    parent::__construct();
    $this->errorStack = $errorStack;
  }

  public function getErrors() {
    $errors = array();
    foreach ($this->errorStack as $fn => $ec)
      $errors[$fn] = $ec;
    return $errors;
  }
}


/******************************************************************************
** Old autoload
******************************************************************************/
function __autoload($class_name) {
  require_once ICLASS . "C" . $class_name . ".php";
}
spl_autoload_register("__autoload");

/******************************************************************************
** Classes that may be reflected to get its constants
******************************************************************************/
$reflectable_classes = array(
  "MiniStores" => 1,
  "Estimate" => 1,
  "Invoice" => 1,
  "Order" => 1,
  "SupplierOrder" => 1,
  "InternalNotes" => 1
);

/******************************************************************************
** Common Global Functions
******************************************************************************/

function pp($o) {
	print "<pre style=\"margin: 0; padding: 0; font: 10px/10px normal lucida console, arial, sans-serif; white-space: pre\">".print_r($o,true)."</pre>";
}
function flog($text, $file = "debug.log") {
  $flog = fopen(LOGS.$file, 'a+');
  if ($flog) {
    if (is_scalar($text))
      fwrite($flog, $text."\n");
    else
      fwrite($flog, print_r($text, true)."\n");
    fclose($flog);
  }
}
function tlog($s, $time = true){
  global $flog;
  fwrite($flog, ($time ? date("Y-m-d H:i:s")." " : "").$s);
}

// temp functions for transition to UTF-8
function serialize_fix_callback($match) {
  return 's:'.strlen($match[2]);
}
function mb_unserialize($s){
  $s = preg_replace_callback('!(?<=^|;)s:(\d+)(?=:"(.*?)";(?:}|a:|s:|b:|d:|i:|o:|O:|N;))!s','serialize_fix_callback',$s);
  return unserialize($s);
}
function to_entities($s) {
  return htmlentities($s, ENT_COMPAT | ENT_XHTML, "UTF-8");
}

// Ignorer les procédures d'abandon en cours de requête
ignore_user_abort(true);

// Supprimer les magic quotes si nécessaire

function array_stripslashes(&$array) {
  foreach ($array as $key => &$val) {
    if (is_array($val))
      array_stripslashes($val);
    else
      $val = stripslashes($val);
  } unset($val);
}

if (get_magic_quotes_gpc()) {
  array_stripslashes($_GET);
  array_stripslashes($_POST);
  array_stripslashes($_COOKIE);
}

/* maintenance */
/*
if ($_SERVER["REMOTE_ADDR"] != "82.241.48.219" &&  $_SERVER["REMOTE_ADDR"] != "79.85.5.60" && $_SERVER["REMOTE_ADDR"] != "94.23.202.91" && $_SERVER["REMOTE_ADDR"] != "93.12.144.94") {
	include(WWW_PATH."maintenance.php");
	exit();
}
*/
