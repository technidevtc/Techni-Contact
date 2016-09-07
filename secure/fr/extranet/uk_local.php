<?php
/**** Includes ****/
/* Alert Password */
define("ALERT_PASS_BECAREFUL", "Be careful !");
define("ALERT_PASS_DESC", "It is highly advised to modify your initial password in order to define a safer one.");
define("ALERT_PASS_EDIT", "Modify your password.");

/* Head */
define("TITLE", "Extranet annonceurs TECHNI-CONTACT");

/**** Secure ****/
/* Where */
define("WHERE_INDEX", "Home");
define("WHERE_COMMANDS", "Commands");
define("WHERE_CONTACT", "Contacts");
define("WHERE_PRODUCTS_CARD", "Product Sheet");
define("WHERE_STATS", "Statistics");
define("WHERE_INFOS", "Personal data");
define("WHERE_INVOICING", "Invoicing");
define("WHERE_INVOICES", "Factures");

/* Global */
define("HEAD_HOMEPAGE", "Home");
define("HEAD_COMMAND_LIST", "List of your products");
define("HEAD_PRODUCT_LIST", "List of your products");
define("TITLE_PRODUCTS_CARDS", "Product sheet");

/* Login */
define("LOGIN_TITLE", "Advertisers' extranet TECHNI-CONTACT - Please login");
define("LOGIN_WELCOME_MSG", "Welcome to the advertisers' extranet");
define("LOGIN_IDENT_TITLE", "Identification");
define("LOGIN_IDENT_ASK", "Please identify yourself before continuing");
define("LOGIN_ERROR_INACTIVE", "Votre compte est actuellement inactif");
define("LOGIN_ERROR_IDENT", "Identifiant et / ou mot de passe invalide(s)");
define("LOGIN_IDENT", "user name");
define("LOGIN_PASS", "password");
define("LOGIN_FORGET", "Forgot your login/password?");
define("LOGIN_SECURITY_TITLE", "Security informations");
define("LOGIN_SECURITY_DESC", "TECHNI-CONTACT advertisers' extranet is protected according to the highest criteria available on the market. In order to ensure you that you do feel well on our extranet, we advise you to check the few following points: 
<ul>
	<li>The Web address of the page on which you are is well: " . EXTRANET_URL . "login.html,</li>
	<li>The logo <img src=\"" . EXTRANET_URL . "ressources/images/ssl-ie.jpg\" alt=\"\" width=\"104\" height=\"19\" align=\"absmiddle\"> - if you are using Internet Explorer
		- or the logo <img src=\"" . EXTRANET_URL . "ressources/images/ssl-firefox.jpg\" alt=\"\" width=\"155\" height=\"19\" align=\"absmiddle\"> - if you are using Firefox - is indeed present,</li>
	<li>No warning message concerning a problem of SSL certificate validity (origin, expiry, applicability) appeared on the screen.</li>
</ul>
If one of the points stated above is not respected or if these rules are missing during the display of the identification page, you are facing during the display of the identification page, you are facing an attempt at hacking of your account user, stop every process of identification and contact your TECHNI-CONTACT interlocutor.<br/>
<br/>
Note: it will <u>never</u> be asked you to communicate your password except on this page.");

/* Lost */
define("LOST_TITLE", "Extranet annonceurs TECHNI-CONTACT - Oubli de votre mot de passe");
define("LOST_HEAD_TITLE", "Rappel du mot de passe");
define("LOST_BLOC_TITLE", "Oubli de votre mot de passe");
define("LOST_OPERATION_SUCCESS_1", "Opération achevée, votre login est");
define("LOST_OPERATION_SUCCESS_2", "et votre nouveau mot de passe est");
define("LOST_ERROR", "Une erreur interne est survenue lors de la procédure.");
define("LOST_ERROR_SESSION", "Identifiant de session invalide ou expiré.");
define("LOST_EMAIL_SENT", "Un e-mail vient d'être envoyé à l'adresse");
define("LOST_ERROR_ACCOUNT", "Aucun compte utilisateur ne correspond à l'adresse email saisie.");
define("LOST_ERROR_EMAIL", "Adresse email invalide");
define("LOST_EMAIL_ASK", "Veuillez saisir votre adresse email afin de recevoir un nouveau mot de passe");
define("LOST_EMAIL", "adresse email");
define("LOST_NO_EMAIL", "Si vous n'avez pas d'adresse email prenez contact avec votre interlocuteur TECHNI-CONTACT.");

/* Index */
define("INDEX_TITLE", "Home");
define("INDEX_COMPANY", "Company");
define("INDEX_WELCOME_MSG_SUPPLIER", "Welcome to the advertisers' extranet TECHNI-CONTACT.");
define("INDEX_LOGOUT", "Logout");
define("INDEX_WELCOME_ADVERTISER", "Welcome");
define("INDEX_WELCOME_MSG_ADVERTISER", "to the advertisers' extranet TECHNI-CONTACT.");
define("INDEX_CONTACT_READ_PRE", "You have <strong>");
define("INDEX_CONTACT_READ_0", "no unread contact request</strong>");
define("INDEX_CONTACT_READ_1", "1 unread contact request</strong>");
define("INDEX_CONTACT_READ_N", "unread contact requests</strong>");
define("INDEX_NO_CONTACT", "no contact request</strong>");

define("INDEX_COMMAND_READ_0", "Vous n'avez <strong>aucune commande</strong> non consultée et");
define("INDEX_COMMAND_READ_1", "Vous avez <strong>1 commande</strong> non consultée et");
define("INDEX_COMMAND_READ_N_1", "Vous avez <strong>");
define("INDEX_COMMAND_READ_N_2", "commandes</strong> non consultées et");
define("INDEX_COMMAND_PROCESSING_0", "n'avez <strong>aucune commande</strong> en cours de traitement.");
define("INDEX_COMMAND_PROCESSING_1", "avez <strong>1 commande</strong> en cours de traitement.");
define("INDEX_COMMAND_PROCESSING_N_1", "avez <strong>");
define("INDEX_COMMAND_PROCESSING_N_2", "commandes</strong> en cours de traitement.");
define("INDEX_NO_COMMAND", "aucune commande</strong>");

define("INDEX_MANAGE_COMMAND", "Gérez vos commandes");
define("INDEX_MANAGE_COMMAND_DESC", "<br/>
	Cette section vous permet de consulter les commandes effectu&eacute;es depuis le site TECHNI-CONTACT.<br/><br/>
	Vous y trouverez les coordonnées des clients, les r&eacute;f&eacute;rences des produits command&eacute;s et l'&eacute;tat d'avancement de chacune des commandes.");
define("INDEX_MANAGE_PRODUCTS", "Dealing with your product sheet");
define("INDEX_MANAGE_PRODUCTS_DESC", "This section enables you to publish, add or to remove products of your Online TECHNI-CONTACT catalogue.<br/><br/>
	Define the page-setting intuitively your descriptions, modify the images of your products, keep your tariffs up to date.");
define("INDEX_MANAGE_CONTACTS", "Dealing with your customer contacts");
define("INDEX_MANAGE_CONTACTS_DESC", "<br/>
	This section enables you to consult the requests carried out by customers or prospects on the TECHNI-CONTACT website.<br/><br/>
	You will find there their personal data and the detail of all their requests.");
define("INDEX_CONSULT_STATS", "Consult your statistics");
define("INDEX_CONSULT_STATS_DESC", "Consult your statistics on the TECHNi-CONTACT website!<br/><br/>
	Filters by product, per days or month enable you to refine your analyses and to study the impact of your TECHNI-CONTACT referencing compared to your competitors.");
define("INDEX_INFORMATION", "Modify your personal data and information");
define("INDEX_INFORMATION_DESC", "This section enables you to update the data concerning your company.<br/><br/>
	These elements will be communicated to the customers at the time of the  contact requests.");

/* Commands */
define("COMMANDS_TITLE", "Commandes");
define("COMMANDS_SEARCH_ERROR_REF", "- La référence saisie de la commande à rechercher est invalide<br/>\n");
define("COMMANDS_SEARCH_ERROR_DATE", "- Le format de la date de recherche saisie est invalide<br/>\n");
define("COMMANDS_SEARCH_ERROR_TYPE", "- Ce type de recherche n'existe pas.<br/>\n");
define("COMMANDS_FILTER_ERROR_MONTH", "- Le mois choisi pour filtrage est invalide<br/>\n");
define("COMMANDS_FILTER_ERROR_STATUS", "- Le statut choisi pour filtrage est invalide<br/>\n");
define("COMMANDS_YOURS", "Vos commandes");
define("COMMANDS_DESCRIPTION", "Voici la liste des commandes effectuées sur le site TECHNI-CONTACT.<br/>
	Ces commandes sont classées de la plus récente à la plus ancienne. Les
	commandes dont vous n'avez pas encore accusé réception sont <strong>en gras</strong>.");
define("COMMANDS_LIST", "Liste de vos commandes");
define("COMMANDS_REF", "ref.");
define("COMMANDS_DATE", "date");
define("COMMANDS_STATUS", "statut");
define("COMMANDS_CONTENT", "contenu de la commande");
define("COMMANDS_AMOUNT_WITHOUT_VAT", "Montant H.T.");
define("COMMANDS_SEARCH", "Rechercher une commande");
define("COMMANDS_SEARCH_BY_REF", "par référence");
define("COMMANDS_SEARCH_BY_DATE", "par date (JJ/MM/AAAA)");
define("COMMANDS_SEARCH_BY_COMPANY", "par société");
define("COMMANDS_SEARCH_BUTTON", "Rechercher");
define("COMMANDS_FILTER", "Filtre d'affichage des commandes");
define("COMMANDS_FILTER_BY_MONTH", "par mois");
define("COMMANDS_FILTER_BY_STATUS", "par statut");
define("COMMANDS_FILTER_BY_STATUS_ALL", "tous les statuts");
define("COMMANDS_FILTER_BY_STATUS_NOT_RECEIPTED", "non consultées");
define("COMMANDS_FILTER_BY_STATUS_PROCESSING", "en cours de traitement");
define("COMMANDS_FILTER_BY_STATUS_SHEEPED", "envoyées");
define("COMMANDS_FILTER_BUTTON", "OK");

/* Command */
define("COMMAND_TITLE", "Commandes");
define("COMMAND_HEAD_TITLE", "Commande n°");
define("COMMAND_COMMAND_N", "Commande n°");
define("COMMAND_RECEIPT_NOTICE", "J'accuse réception de cette commande");
define("COMMAND_RECEIPT_NOTICE_COMMENT", "Afin d'accéder aux informations relatives à cette commande, nous demandons à nos fournisseurs d'en accuser réception. Cela nous permet d'informer le client que sa commande est en cours de traitement.");
define("COMMAND_REPORT_SHIP", "Signaler comme expédiée");
define("COMMAND_GENERATE_DELIVERY_ORDER", "Générer le bon de livraison");
define("COMMAND_GENERATE_DELIVERY_ORDER_COMMENT", "Cette commande est actuellement<strong> en cours de traitement</strong>. Dès que celle-ci est expédiée, veuillez le signaler en cliquant sur le lien ci-joint.");
define("COMMAND_DETAIL", "Détail de la commande");
define("COMMAND_REGENERATE_DELIVERY_ORDER", "Réémettre le bon de livraison");
define("COMMAND_REPORTED_AS_SHIPPED", "Cette commande est signalée comme <strong>expédiée</strong>.");
define("COMMAND_DETAIL_RECALL", "Rappel du détail de la commande");
define("COMMAND_DETAIL_REF", "ref.");
define("COMMAND_DETAIL_LINK", "Fiche");
define("COMMAND_DETAIL_PRODUCT", "Produit");
define("COMMAND_DETAIL_QUANTITY", "Qté");
define("COMMAND_DETAIL_AMOUNT_WITHOUT_VAT", "Montant H.T.");
define("COMMAND_DETAIL_AMOUNT_WITH_VAT", "Montant Net");
define("COMMAND_DETAIL_SEE_ONLINE", "voir en ligne");
define("COMMAND_DETAIL_SEE_IMPOSSIBLE", "indisponible");
define("COMMAND_DETAIL_DISCOUNT", "Remise de");
define("COMMAND_DETAIL_DISCOUNT_FOR", "pour");
define("COMMAND_SHEEPING_FEE", "Frais de port");
define("COMMAND_TOTAL_TO_CHARGE", "Total à facturer à TECHNI-CONTACT");

/* Requests */
define("REQUESTS_TITLE", "Contacts");
define("REQUESTS_HEAD_TITLE", "Liste des demandes");
define("REQUESTS_BLOC_TITLE", "Your requests for customer contacts");
define("REQUESTS_BLOC_DESC", "Here is the list osf the contact requests that the visitors of the TECHNI-CONTACT website have carried out for your products.<br/>
	These requests are classified from the more recent to the oldest. The requests which you have not consulted yet are written in <strong>bold type</strong>.");
define("REQUESTS_CONTACT_LIST", "List of your requests");
define("REQUESTS_CONTACT_NAME_AND_COMPANY", "name and company");
define("REQUESTS_CONTACT_RELATED_PRODUCT", "related product");

/* Request Detail */
define("REQUEST_DETAIL_TITLE", "Demande de contact");
define("REQUEST_DETAIL_HEAD_TITLE", "Détail d'une demande");
define("REQUEST_DETAIL_ERROR_ID", "Identifiant de demande incorrect");
define("REQUEST_DETAIL_BLOC_TITLE", "D&eacute;tails de la demande");
define("REQUEST_DETAIL_SEE_PRODUCT", "Voir la fiche produit");
define("REQUEST_DETAIL_PRODUCT_DELETED", "Ce produit n'est plus disponible dans notre catalogue");
define("REQUEST_DETAIL_BLOC_DESC_1", "Cette page vous pr&eacute;sente le d&eacute;tail de la demande effectu&eacute;e pour le produit");
define("REQUEST_DETAIL_BLOC_DESC_2", "Vous y trouverez les coordonn&eacute;es du prospect, ainsi que des pr&eacute;cisions sur sa demande.");
define("REQUEST_DETAIL_CONTACT_INFOS", "Informations sur la demande");
define("REQUEST_DETAIL_PRODUCT", "Related product");
define("REQUEST_DETAIL_MESSAGE", "Message facultatif");
define("REQUEST_DETAIL_CUSTOMER_INFOS", "Informations personnelles de l'internaute");

define("REQUEST_DETAIL_COMPANY_INFOS", "Informations sur son entreprise");
define("REQUEST_DETAIL_COMPANY_NAME", "Nom de la soci&eacute;t&eacute;");

/* Search */
define("SEARCH_TITLE", "Fiches Produits");
define("SEARCH_DELETE_CONFIRMATION", "Etes-vous sûr de vouloir supprimer ce produit ?");
define("SEARCH_RESULTS", "Résultat de votre recherche");
define("SEARCH_ERROR_LENGTH", "Merci de saisir au minimum 3 caractères avant de lancer votre recherche.");
define("SEARCH_ASK_DELETE", "Demander la suppression de ce produit");

/* Products */
define("PRODUCTS_JS_ASK_DELETE_PRODUCT", "Etes-vous sûr de vouloir supprimer ce produit ?");
define("PRODUCTS_PRODUCT_LIST_TITLE", "Your online products catalogue");
define("PRODUCTS_PRODUCT_LIST_DESC", "Here the list of your product sheets currently on line on  the TECHNI-CONTACT website.<br/>
	You can publish their contents (description, image, tariff) or ask for their suppression. A TECHNI-CONTACT operator will then validate your request before the publication.<br/>
	For more details do not hesitate to consult our <a href=\"Guide_pour_les_fiches_produits.doc\" target=\"_blank\">product sheets guide</a>.");
define("PRODUCTS_ADD_PRODUCT", "Add a new product");
define("PRODUCTS_WAITING_PRODUCTS", "Your products pending to be validated");
define("PRODUCTS_SEARCH", "Search");
define("PRODUCTS_ASK_DELETE", "Demander la suppression de ce produit");

/* Add & Edit Product */
define("PRODUCT_ERROR_NAME", "- Vous n'avez pas saisi le nom du produit<br/>");
define("PRODUCT_ERROR_NAME_LETTER", "- Le nom du produit doit débuter par une lettre / chiffre");
define("PRODUCT_ERROR_FASTDESC", "- Vous n'avez pas saisi la description rapide du produit<br/>");
define("PRODUCT_ERROR_FAMILY", "- Vous devez lier au moins une catégorie au produit<br/>");
define("PRODUCT_ERROR_DESC", "- Vous n'avez pas saisi la description du produit<br/>");
// PriceType 0
define("PRODUCT_ERROR_CONSTRAINT", "- La contrainte de quantité de produit saisie est incorrecte<br/>");
define("PRODUCT_ERROR_REFSUPPLIER", "- La référence du produit n'a pas été saisie<br/>\n");
define("PRODUCT_ERROR_PRICE", "- Le format du prix du produit est invalide<br/>\n");
define("PRODUCT_ERROR_PRICE2", "- Le format du prix public du produit est invalide<br/>\n");
define("PRODUCT_ERROR_UNIT", "- Le format de l'unité est incorrect<br/>\n");
define("PRODUCT_ERROR_VAT", "- Le taux de TVA n'existe pas<br/>");
// PriceType 4 Supplier
define("PRODUCT_ERROR_SUP_ATTRIBUT", "- Le tableau de références doit contenir au moins 1 propriété en plus du libellé, de la référence, de l'unité, du taux de TVA, et du prix<br/>");
define("PRODUCT_ERROR_SUP_NB_COLS", "- Nombre de colonnes erroné / modification Libellé, Référence, Unité, Taux TVA, Prix interdite<br/>");
define("PRODUCT_ERROR_LABEL_COL_P", "- Libellé colonne");
define("PRODUCT_ERROR_LABEL_COL_S", "du tableau de références non saisi<br/>");
define("PRODUCT_ERROR_LINE_1", "- Le tableau de références doit comporter au moins 1 ligne<br/>");
define("PRODUCT_ERROR_LINE_PRE", "- La ligne");
define("PRODUCT_ERROR_LINE_FORMAT", "du fichier / tableau ignorée car son format est incorrect (les lignes suivantes sont automatiquement décallées, vous pouvez rajouter la ligne &agrave; la main)<br/>");
define("PRODUCT_ERROR_LINE_NUM", "du tableau de références ne possède pas de numéro de référence Techni-Contact valide. Veuillez prévenir votre webmaster si cette erreur survient à nouveau<br/>");
define("PRODUCT_ERROR_LINE_LABEL", "du tableau de références ne possède pas de libellé<br/>");
define("PRODUCT_ERROR_LINE_REFSUPPLIER", "du tableau de références ne possède pas de référence fournisseur<br/>");
define("PRODUCT_ERROR_LINE_DATA", "du tableau de références ne possède aucune donnée caractérisant le libellé et le prix<br/>");
define("PRODUCT_ERROR_LINE_UNIT", "du tableau de références n'a pas d'unité valide<br/>");
define("PRODUCT_ERROR_LINE_VAT", "du tableau de références n'a pas de taux de TVA valide<br/>");
define("PRODUCT_ERROR_SUP_LINE_PRICE", "du tableau de références n'a pas de prix valide<br/>");
define("PRODUCT_ERROR_LINE_1_OK", "- Le tableau de références doit comporter au moins 1 ligne valide<br/>");
// PriceType 4 Advertiser
define("PRODUCT_ERROR_ADV_ATTRIBUT", "- Le tableau de références doit contenir au moins 1 propriété en plus de l'identifiant Techni-Contact, du libellé et du prix<br/>");
define("PRODUCT_ERROR_ADV_NB_COLS", "- Nombre de colonnes erroné / modification libellé ou prix interdite<br /");
define("PRODUCT_ERROR_ADV_LINE_PRICE", "du tableau de références n'a pas de prix valide, laissez le champ vide si vous ne souhaitez ne pas en préciser<br/>");
// HTML //
// JS
define("PRODUCT_LINK_ERRORJS_FAMILY", "Merci de sélectionner une catégorie avant de la lier au produit.");
define("PRODUCT_LINK_ERRORJS_LEVEL", "Vous ne pouvez lier un produit qu'aux catégories de niveau 3.");
define("PRODUCT_LINK_ERRORJS_EXIST", "Cette catégorie est déjà présente dans la liste des catégories liées au produit.");
define("PRODUCT_LINK_PROCESSING", "Liaison en cours");
define("PRODUCT_LINK_ERRORJS_DATA", "Une erreur est survenue : impossible de récupérer les données de la catégorie.");
define("PRODUCT_FAMILIES_LINKED", "selected category");
define("PRODUCT_LINK", "Bind");
// Body
define("PRODUCT_DETAIL", "Details of the product sheet (* = obligatory field)");
define("PRODUCT_SEE_ONLINE", "Visualize online");
define("PRODUCT_NAME_LABEL", "Name ( google keyword)");
define("PRODUCT_NAME_DESC", "Register here the name of your product sheet. It is this denomination which will be considered by google as a keyword.");
define("PRODUCT_FASTDESC_LABEL", "Short description");
define("PRODUCT_FASTDESC_DESC", "It is a subtitle, which makes it possible to distinguish two product sheets comprising the same principal name.<br/>
	For example: &quot;box - <u>large size</u>&quot; and &quot;box - <u>small size</u>&quot;.");
define("PRODUCT_KEYWORDS_LABEL", "Keywords (internal search)");
define("PRODUCT_KEYWORDS_DESC", "They are the terms relative to your product which the internet users are likely to type on our search engine. A key word only includes one word and not an expression. You can capture some up to 5.");
define("PRODUCT_FAMILY_LABEL", "Category");
define("PRODUCT_FAMILY_SELECT", "Select a category");
define("PRODUCT_FAMILY_DESC", "Choose here the category of the website in which your product sheet will appear online, and to click on \"Bind\". You can select as many categories as you wish.<br/>
	The tree structure of the categories consists of 3 levels of abstraction: you can only bind a product to the categories of level 3 (lowest level of the abstraction).");
define("PRODUCT_DESCC_LABEL", "Product description");
define("PRODUCT_DESCC_DESC", "Write here the description of your product sheet.<br/>
	This small text editor allows you to shape it (boldtype, size of the text, etc).");
define("PRODUCT_DESCD_LABEL", "Detailed description");
define("PRODUCT_DESCD_DESC", "If you wish it, you can add in addition to the general description,  a detailed description. This one will appear at the end of the product sheet, if the internet user wants  to have further details.");
define("PRODUCT_IMAGE_LABEL", "Image of the product");
define("PRODUCT_IMAGE_DESC", "You can put on line here the image of your product. This one must be with the format * jpg and respect constraints of weight and size.");
define("PRODUCT_DOC_LABEL", "Optional document");
define("PRODUCT_DOC_DESC", "This field enables you to put at the internet users' disposal, a word or pdf file like a detailed worksheet or a model of estimate.");
// Supplier
define("PRODUCT_SUP_PUBLIC_PRICE_HELP", "Saisissez ici le prix public du produit tel qu'il appara&icirc;tra sur la boutique en ligne.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_SUP_BUY_PRICE_HELP", "Saisissez ici le prix factur&eacute; &agrave; techni-contact.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_SUP_PUBLIC_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont les prix public tels qu'ils appara&icirc;tront sur la boutique en ligne.<br/>");
define("PRODUCT_SUP_BUY_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont ceux qui seront factur&eacute;s &agrave; techni-contact.<br/>");
define("PRODUCT_SUP_CONTACT_HELP", "En choisissant d'&ecirc;tre contact&eacute; pour le prix, ce produit ne sera pas consid&eacute;r&eacute; comme &eacute;tant en vente en ligne.");
define("PRODUCT_PRICE_TYPE_LABEL", "Type of price");
define("PRODUCT_REFSUPPLIER_LABEL", "Votre référence");
define("PRODUCT_REFSUPPLIER_DESC", "Saisissez ici la référence interne du produit.");
define("PRODUCT_UNIT_LABEL", "Unité");
define("PRODUCT_UNIT_DESC", "Il s'agit du nombre de produits par vente <i>(par défaut 1)</i>.<br/>
	Par exemple, si le produit est un lot de 6 chaises ne pouvant être vendues séparemment, alors l'unité sera de 6.");
define("PRODUCT_VAT_LABEL", "Taux de TVA");
define("PRODUCT_VAT_DESC", "Sélectionnez ici le taux de TVA du produit.");
define("PRODUCT_DELIVERY_TIME_LABEL", "Délai de livraison");
define("PRODUCT_DELIVERY_TIME_DESC", "Saisissez ici le délai de livraison moyen pour ce produit s'il diffère de votre délai de livraison par défaut.<br/>
	Votre délai de livraison moyen par défaut");
define("PRODUCT_CONSTRAINT_LABEL", "Contrainte produit");
define("PRODUCT_CONSTRAINT_DESC", "Nombre de produit minimum pour que la vente puisse être prise en compte <i>(par défaut 0)</i>.<br/>
	Si vous n'acceptez une commande de ce produit que si la quantité doit dépasser les 50 unités, entrez donc 50 comme contrainte.");
// Advertiser
define("PRODUCT_ADV_PRICE_HELP", "Capture here the public price of the product such as it will appear on the online shop.<br/>If you had already captured references for this product, they will be deleted at the time of the validation.");
define("PRODUCT_ADV_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont les prix public tels qu'ils appara&icirc;tront sur la boutique en ligne.<br/>");
define("PRODUCT_ADV_CONTACT_HELP", "Demandez &agrave; &ecirc;tre contact&eacute; directement pour tout renseignement sur ce produit.");
// body
define("PRODUCT_BOTTOM_NOTE", "note: these modifications will be confirmed by an TECHNI-CONTACT operator before their setting on line.");

/* Add Product */
define("ADD_PRODUCT_TITLE", "Add a new product");
define("ADD_PRODUCT_HEAD_TITLE", "Ajout d'un nouveau produit");
define("ADD_PRODUCT_SUCCESS", "Produit enregistré avec succès. Il sera en ligne dès sa validation par un opérateur TECHNI-CONTACT");
define("ADD_PRODUCT_ERROR_MAX", "Vous avez atteint le nombre maximal " . NB_MAX . " de produits en attente de validation de création");
define("ADD_PRODUCT_ERROR", "Erreur interne lors de la création du produit");
define("ADD_PRODUCT_SUBMIT", "Submit the product sheet");

/* Product Detail */
define("EDIT_PRODUCT_TITLE", "Editer un produit");
define("EDIT_PRODUCT_HEAD_TITLE", "Editer un produit");
define("EDIT_PRODUCT_ERROR_ID", "Identifiant de produit incorrect.");
define("EDIT_PRODUCT_ERROR_EDIT_DELETE", "Vous ne pouvez éditer un produit en attente de suppression.");
define("EDIT_PRODUCT_SUCCESS", "Modification du produit enregistrée avec succès. Elle sera en ligne dès sa validation par un opérateur TECHNI-CONTACT");
define("EDIT_PRODUCT_ERROR_MAX", "Vous avez atteint le nombre maximal (" . NB_MAX . ") de produits en attente de validation de modification");
define("EDIT_PRODUCT_ERROR", "Erreur interne lors de la modification du produit : vous avez probablement déjà fait une demande de modification pour ce produit, mais elle a été rejetée");
define("EDIT_PRODUCT_SUBMIT", "Soumettre les modifications de la fiche produit");

/* Delete Product */
define("PRODUCT_DEL_TITLE", "Demande de suppression");
define("PRODUCT_DEL_HEAD_TITLE", "Demande de suppression");
define("PRODUCT_DEL_ERROR_ID", "Identifiant de produit incorrect.");
define("PRODUCT_DEL_ERROR_ALREADY", "Ce produit est déjà en attente de suppression.");
define("PRODUCT_DEL_OK", "Demande de suppression du produit enregistrée avec succès.");
define("PRODUCT_DEL_ERROR", "Erreur interne lors de la demande de suppression du produit.");

/* W Products */
define("W_PRODUCT_TITLE", "Fiches Produits en attente de validation");
define("W_PRODUCT_HEAD_TITLE", "Pending");
define("W_PRODUCT_CREATE_BLOC_TITLE", "Pending to be validated for creation");
define("W_PRODUCT_EDIT_BLOC_TITLE", "Pending to be validated modification");
define("W_PRODUCT_REJECTED_BLOC_TITLE", "Rejected requests during the last 15 days");
define("W_PRODUCT_CREATING", "Creation");
define("W_PRODUCT_EDITING", "Modification");

/* PP Product */
define("PP_PRODUCT_NEW_REF", "nouvelle r&eacute;f&eacute;rence");
define("PP_PRODUCT_SEE_ONLINE", "Pr&eacute;visualisation d'une fiche produit");
define("PRODUCT_CARD_ZOOM", "zoom");
define("PRODUCT_CARD_ESTIMATE_NEEDED", "Ce produit nécessite une demande de devis avant toute commande");
define("PRODUCT_CARD_DESCC", "description du produit");
define("PRODUCT_CARD_DESCD", "description technique");
define("PRODUCT_CARD_DELIVERY_TIME", "d&eacute;lais de livraison habituels");
define("PRODUCT_CARD_CONSTRAINT", "Nombre de produit minimum à commander");
define("PRODUCT_CARD_UNIT", "Unit&eacute;");
define("PRODUCT_CARD_PRICE", "Prix");
define("PRODUCT_CARD_QUANTITY", "Quantité");
define("PRODUCT_CARD_WITHOUT_VAT", "HT");
define("PRODUCT_CARD_REF", "R&eacute;f&eacute;rences");
define("PRODUCT_CARD_REF_IDTC", "R&eacute;f. TC");
define("PRODUCT_CARD_REF_LABEL", "Label");
define("PRODUCT_CARD_REF_UNIT", "Unit&eacute;");
define("PRODUCT_CARD_REF_PRICE_WITHOUT_VAT", "Prix HT");
define("PRODUCT_CARD_REF_QUANTITY", "Qt&eacute;");

/* Stats */
define("STATS_TITLE", "Statistics");
define("STATS_HEAD_TITLE", "Statistics");
define("STATS_MONTH_DETAIL", "Détail du mois");
define("STATS_DETAIL", "Détail");
define("STATS_OF_MONTH", "");
define("STATS_OF_YEAR", "de l'année");
define("STATS_YOUR_STATS_BLOC_TITLE", "Your statistics");
define("STATS_YOUR_STATS_BLOC_DESC", "Here is the statistics of consultation of your product sheets on the <strong>TECHNI-CONTACT</strong> website.<br/>
    Click over one month to refine your results.");
define("STATS_CONFIG", "Parameters setting");
define("STATS_YEAR", "year");
define("STATS_MONTH", "month");
define("STATS_EVERY_MONTH", "Every month");
define("STATS_ALL_PRODUCTS", "All products");
define("STATS_STATS_BLOC_TITLE_", "Statistics");
define("STATS_GLOBAL", ": General");
define("STATS_OF_PRODUCT", "of the product");
define("STATS_PRODUCTS_STATS", "Statistics of the products");

/* Infos */
define("INFOS_TITLE", "Personal data");
define("INFOS_HEAD_TITLE", "Update your personal data");
define("INFOS_ERROR_ADRESS", "- Vous n'avez pas saisi l'adresse<br/>");
define("INFOS_ERROR_CITY", "- Vous n'avez pas saisi le nom de la ville<br/>");
define("INFOS_ERROR_PC", "- Vous n'avez pas saisi le code postal<br/>");
define("INFOS_ERROR_COUNTRY", "- Vous n'avez pas saisi le nom du pays<br/>");
define("INFOS_ERROR_DELIVERY_TIME", "- Vous n'avez pas saisi le délai de livraison moyen<br/>");
define("INFOS_ERROR_DISCOUNT", "- Vous n'avez pas saisi le taux de remise<br/>");
define("INFOS_ERROR_DISCOUNT_NOT_VALID", "- Le taux de remise saisi est invalide <br/>");
define("INFOS_ERROR_CONSTRAINT", "- La contrainte de prix saisie est invalide <br/>");
define("INFOS_ERROR_EMAIL", "- Vous avez saisie une adresse e-mail invalide<br/>");
define("INFOS_ERROR_EMAIL_USED", "- L'adresse email saisie est déjà utilisée<br/>");
define("INFOS_ERROR_URL", "- Adresse du site web invalide<br/>");
define("INFOS_ERROR_TEL", "- Vous n'avez pas saisi le numéro de téléphone <br/>");
define("INFOS_ERROR_FAX", "- Vous n'avez pas saisi le numéro de fax <br/>");
define("INFOS_ERROR_LOGIN", "- Vous n'avez pas saisi le login extranet<br/>");
define("INFOS_ERROR_LOGIN_LENGTH", "- Le login extranet doit faire au minimum 3 caractères<br/>");
define("INFOS_ERROR_LOGIN_EXIST", "- Le Login extranet saisi est déjà utilisé<br/>");
define("INFOS_ERROR_PASS_LENGTH", "- Le nouveau mot de passe extranet doit faire au minimum 6 caractères<br/>");
define("INFOS_ERROR_PASS_SAME", "- Le nouveau mot de passe et sa confirmation doivent être identiques<br/>");
define("INFOS_UPDATE_SUCCESS", "Coordonnées mises à jour avec succès. Cette mise à jour ne sera toutefois effective qu'après validation des éléments par un opérateur Techni-Contact.");
define("INFOS_ERROR_UPDATE", "Erreur interne lors de la mise à jour des coordonnées.");

define("INFOS_BLOC_TITLE", "Modify your personal data");
define("INFOS_BLOC_DESC", "This page enables you to modify the data of your company and to deal with information related to your TECHNI-CONTACT extranet account.");
define("INFOS_BLOC_NOTE", "Note : une mise à jour de vos coordonnées est actuellement en attente de validation Techni-Contact. Une nouvelle mise à jour de votre part remplacera la précédente.");
define("INFOS_ERROR", "Une ou plusieurs erreurs sont survenues lors de la validation");

define("INFOS_COMPANY_BLOC", "Your company data");
define("INFOS_COMPANY_NAME", "Name of the company");

define("INFOS_SUPPLIER_BLOC", "Configuration fournisseur");
define("INFOS_SUPPLIER_DELIVERY_TIME", "D&eacute;lai de livraison habituel");
define("INFOS_SUPPLIER_DELIVERY_TIME_DESC", "Renseignez ici le d&eacute;lai de livraison habituel de vos produits");
define("INFOS_SUPPLIER_CONSTRAINT", "Contrainte de prix");
define("INFOS_SUPPLIER_CONSTRAINT_DESC", "Renseignez ici le montant minimale d'une commande pour que vous la preniez en compte");
define("INFOS_SUPPLIER_DISCOUNT", "Taux de remise");
define("INFOS_SUPPLIER_DISCOUNT_DESC", "Renseignez ici le taux de remise en %age que vous permettez à Techni-Contact sur chacun des produits que vous proposez en prix Public");

define("INFOS_SUPPLIER_CONTACT_BLOC", "Relations Techni-Contact");
define("INFOS_SUPPLIER_CONTACT", "Personne &agrave; contacter");
define("INFOS_SUPPLIER_CONTACT_DESC", "Renseignez ici les nom et pr&eacute;nom de la personne que Techni-Contact peut contacter");
define("INFOS_SUPPLIER_EMAIL_DESC", "Renseignez ici l'adresse email de la personne &agrave; contacter. Cette adresse email est &eacute;galement li&eacute;e &agrave; votre compte extranet (envoi d'alertes, de mot de passe perdu, etc.)");

define("INFOS_ADVERTISER_CONTACT_BLOC", "Advertisers relations");
define("INFOS_ADVERTISER_CONTACT", "Person to be contacted");
define("INFOS_ADVERTISER_CONTACT_DESC", "Write here the name and first name of the person that the internet users can contact.");
define("INFOS_ADVERTISER_EMAIL_DESC", "Write here the email address of the person to be contacted. This email address is also related to your extranet account (sending of alarms, lost password , etc.).");

define("INFOS_EXTRANET_BLOC", "Configuration extranet access");
define("INFOS_EXTRANET_LOGIN", "Extranet LOGIN");
define("INFOS_EXTRANET_LOGIN_DESC", "It is the login used to connect you to this private extranet. Otherwise, it is the name of your company (without special characters).");
define("INFOS_EXTRANET_PASS", "New PASSWORD");
define("INFOS_EXTRANET_PASS_DESC", "Leave this field empty if you do not wish to modify your password.");
define("INFOS_EXTRANET_PASS_CHECK", "Confirmation of your PASSWORD");
define("INFOS_EXTRANET_PASS_CHECK_DESC", "If you have defined a new password, confirm it by writting it here again.");
define("INFOS_BUTTON_VALIDATE", "Confirm my modifications");
define("INFOS_BOTTOM_NOTE", "note: these modifications will be effective after a validation by an Techni-Contact operator.");

/* Invoicing */
define("INVOICING_TITLE", "Facturation");
define("INVOICING_HEAD_TITLE", "Personnalisation de vos critères de facturation");
define("INVOICING_UPDATE_SUCCESS", "Critères de facturation mises à jour avec succès.");
define("INVOICING_UPDATE_ERROR", "Erreur lors de la mise à jour de la liste des départements acceptés.");
define("INVOICING_LOAD_ERROR", "Erreur lors du chargement de vos paramètres");
define("INVOICING_NO_RIGHTS", "Vous n'avez pas les droits d'édition de vos critères de facturation.");
define("INVOICING_BLOC_TITLE", "Modification de vos critères de facturation");
define("INVOICING_BLOC_DESC", "Cette page vous permet de modifier vos critères de facturation.");
define("INVOICING_ERROR", "Une ou plusieurs erreurs sont survenues lors de la validation");
define("INVOICING_PC", "Liste des départements facturables");
define("INVOICING_PC_DESC", "Veuillez entrer la liste des départements séparés par des barres verticales | . Exemple: 75|77|78 .<br/>Note : Champ vide = tous les départements sont acceptés.");

// Common
define("COMMON_SUPPLIER", "Fournisseur");
define("COMMON_CONTACT_TYPE", "Type of request");
define("COMMON_CONTACT_TYPE_ASK", "Demande d'informations");
define("COMMON_CONTACT_TYPE_TEL", "Demande de contact téléphonique");
define("COMMON_CONTACT_TYPE_ESTIMATE", "Demande de devis");
define("COMMON_CONTACT_TYPE_APPOINTMENT", "Demande de rendez-vous");
define("COMMON_NO_RESULT", "No result");
define("COMMON_SEARCH", "Recherche");
define("COMMON_BUTTON_VALIDATE", "Confirm");
define("COMMON_PRODUCT", "Product");
define("COMMON_DATE", "Date");
define("COMMON_TYPE", "Type");
define("COMMON_MOTIVE", "Motif");
define("COMMON_CATEGORY", "Category");
define("COMMON_PRODUCT_NAME", "Name of the product");
define("COMMON_PRICE_TYPE_ON_DEMAND", "On request");
define("COMMON_PRICE_TYPE_ON_ESTIMATE", "On estimate");
define("COMMON_PRICE_TYPE_CONTACT_US", "Contact us");
define("COMMON_PRICE_TYPE_REFS", "References");
define("COMMON_PRICE_TYPE_SIMPLE_PRICE", "Simple price");
define("COMMON_ERROR_VALIDATE", "Une ou plusieurs erreurs sont survenues lors de la validation");
define("COMMON_ASK_TEL_CONTACT", "demander un contact t&eacute;l&eacute;phonique");
define("COMMON_ASK_ESTIMATE", "demander un devis");
define("COMMON_GET_INFOS", "obtenir des informations");
define("COMMON_ASK_APPOINTMENT", "demander un rendez-vous");
define("COMMON_APPLY", "Apply");

define("COMMON_JANUARY", "January");
define("COMMON_FEBRUARY", "February");
define("COMMON_MARCH", "March");
define("COMMON_APRIL", "April");
define("COMMON_MAY", "May");
define("COMMON_JUNE", "June");
define("COMMON_JULY", "July");
define("COMMON_AUGUST", "August");
define("COMMON_SEPTEMBER", "September");
define("COMMON_OCTOBER", "October");
define("COMMON_NOVEMBER", "November");
define("COMMON_DECEMBER", "December");
define("COMMON_JAN", "jan");
define("COMMON_FEB", "feb");
define("COMMON_MAR", "mar");
define("COMMON_APR", "apr");
define("COMMON_MAY", "may");
define("COMMON_JUN", "jun");
define("COMMON_JUL", "jul");
define("COMMON_AUG", "aug");
define("COMMON_SEP", "sep");
define("COMMON_OCT", "oct");
define("COMMON_NOV", "nov");
define("COMMON_DEC", "dec");

define("INFOS_LAST_NAME", "Last name");
define("INFOS_FIRST_NAME", "First name");
define("INFOS_JOB", "Function");
define("INFOS_EMAIL", "Email");
define("INFOS_EMAIL_ADDRESS", "Adresse email");
define("INFOS_ADDRESS", "Address");
define("INFOS_COMPLEMENT", "complement");
define("INFOS_CITY", "City");
define("INFOS_PC", "Postal Code");
define("INFOS_COUNTRY", "Country");
define("INFOS_TEL1", "Phone Number");
define("INFOS_TEL2", "Phone Number 2");
define("INFOS_FAX1", "FAX");
define("INFOS_FAX2", "FAX 2");
define("INFOS_URL", "Internet website");
define("INFOS_NUMBER_OF_EMPLOYEES", "Employees number");
define("INFOS_ACTIVITY_SECTOR", "Activity Branch");
define("INFOS_NAF_CODE", "NAF code");
define("INFOS_SIREN_NUMBER", "SIRET");

?>