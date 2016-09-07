<?php
/**** Includes ****/
/* Alert Password */
define("ALERT_PASS_BECAREFUL", "Attention !");
define("ALERT_PASS_DESC", "Il est fortement conseillé de modifier votre mot de passe initial de connexion afin d'en définir un plus sécurisé !");
define("ALERT_PASS_EDIT", "Modifier votre mot de passe.");

/* Head */
define("TITLE", "Extranet annonceurs TECHNI-CONTACT");

/**** Secure ****/
/* Where */
define("WHERE_INDEX", "Accueil");
define("WHERE_COMMANDS", "Commandes");
define("WHERE_CONTACT", "Contacts");
define("WHERE_PRODUCTS_CARD", "Fiches Produits");
define("WHERE_STATS", "Statistiques");
define("WHERE_INFOS", "Coordonn&eacute;es");
define("WHERE_INVOICING", "Filtrage demandes"); // onglet "facturation" renomé en "Filtrage demandes"
define("WHERE_INVOICES_LIST", "Facturations");
define("WHERE_INVOICES", "Factures");

/* Global */
define("HEAD_HOMEPAGE", "Accueil");
define("HEAD_COMMAND_LIST", "Liste des commandes");
define("HEAD_PRODUCT_LIST", "Liste de vos produits");
define("TITLE_PRODUCTS_CARDS", "Fiches Produits");

/* Login */
define("LOGIN_TITLE", "Extranet annonceurs TECHNI-CONTACT - Merci de vous identifier");
define("LOGIN_WELCOME_MSG", "Bienvenue sur l'extranet annonceurs TECHNI-CONTACT.");
define("LOGIN_IDENT_TITLE", "Identification");
define("LOGIN_IDENT_ASK", "Veuillez vous identifier avant de poursuivre");
define("LOGIN_ERROR_INACTIVE", "Votre compte est actuellement inactif");
define("LOGIN_ERROR_IDENT", "Identifiant et / ou mot de passe invalide(s)");
define("LOGIN_IDENT", "nom d'utilisateur");
define("LOGIN_PASS", "mot de passe");
define("LOGIN_FORGET", "login / mot de passe oublié ?");
define("LOGIN_SECURITY_TITLE", "Informations Securit&eacute;");
define("LOGIN_SECURITY_DESC", "L'extranet annonceurs TECHNI-CONTACT est s&eacute;curis&eacute; selon les crit&egrave;res les plus &eacute;lev&eacute;s disponibles sur le march&eacute;. Afin de vous assurez que vous vous trouvez bien sur notre extranet, nous vous conseillons de v&eacute;rifier les quelques points suivants : 
<ul>
	<li>L'adresse web de la page sur laquelle vous vous trouvez est bien " . EXTRANET_URL . "login.html,</li>
	<li>Le Logo <img src=\"" . EXTRANET_URL . "ressourcesv3/images/ssl-ie.jpg\" alt=\"\" width=\"104\" height=\"19\" align=\"absmiddle\"> - si vous utilisez Internet Explorer
		- ou le logo <img src=\"" . EXTRANET_URL . "ressourcesv3/images/ssl-firefox.jpg\" alt=\"\" width=\"155\" height=\"19\" align=\"absmiddle\"> - si vous utilisez Firefox - est bien pr&eacute;sent,</li>
	<li>Aucun message d'avertissement concernant un probl&egrave;me de validit&eacute; du certificat SSL (origine, expiration, domaine d'application) n'est apparu &agrave; l'&eacute;cran.</li>
</ul>
Si l'un des points &eacute;nonc&eacute;s ci-dessus n'est pas respect&eacute; ou bien que ces r&egrave;gles sont absentes lors de l'affichage de la page d'identification vous &ecirc;tes probablement face &agrave; une tentative de piratage de votre compte utilisateur, interrompez tout proc&eacute;d&eacute; d'identification et prenez contact avec votre interlocuteur TECHNI-CONTACT.<br/>
<br/>
Note : il ne vous sera <u>jamais demand&eacute;</u> de communiquer votre mot de passe en dehors de cette page.");

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
define("INDEX_TITLE", "Accueil");
define("INDEX_COMPANY", "Société");
define("INDEX_WELCOME_MSG_SUPPLIER", "Bienvenue sur l'extranet fournisseurs TECHNI-CONTACT.");
define("INDEX_LOGOUT", "D&eacute;connexion");
define("INDEX_WELCOME_ADVERTISER", "Bienvenue");
define("INDEX_WELCOME_MSG_ADVERTISER", "sur l'extranet annonceurs TECHNI-CONTACT.");
define("INDEX_CONTACT_READ_PRE", "Vous avez <strong>");
define("INDEX_CONTACT_READ_0", "aucune demande de contact</strong> non lue");
define("INDEX_CONTACT_READ_1", "1 demande de contact</strong> non lue");
define("INDEX_CONTACT_READ_N", "demandes de contact</strong> non lues");
define("INDEX_NO_CONTACT", "aucune demande</strong>");

define("INDEX_COMMAND_READ_0", "Vous n'avez <strong>aucune commande</strong> non consultée.");
define("INDEX_COMMAND_READ_1", "Vous avez <strong>1 commande</strong> non consultée.");
define("INDEX_COMMAND_READ_N_1", "Vous avez <strong>");
define("INDEX_COMMAND_READ_N_2", "commandes</strong> non consultées.");
define("INDEX_COMMAND_PROCESSING_0", "n'avez <strong>aucune commande</strong> en cours de traitement.");// attente d'accusé reception
define("INDEX_COMMAND_PROCESSING_1", "avez <strong>1 commande</strong> en cours de traitement.");// attente d'accusé reception
define("INDEX_COMMAND_PROCESSING_N_1", "avez <strong>");
define("INDEX_COMMAND_PROCESSING_N_2", "commandes</strong> en cours de traitement.");// attente d'accusé reception
define("INDEX_NO_COMMAND", "aucune commande</strong>");

define("INDEX_MANAGE_COMMAND", "Gérez vos commandes");
define("INDEX_MANAGE_COMMAND_DESC", "<br/>
	Cette section vous permet de consulter les commandes effectu&eacute;es depuis le site TECHNI-CONTACT.<br/><br/>
	Vous y trouverez les coordonnées des clients, les r&eacute;f&eacute;rences des produits command&eacute;s et l'&eacute;tat d'avancement de chacune des commandes.");
define("INDEX_MANAGE_PRODUCTS", "G&eacute;rez vos fiches produits");
define("INDEX_MANAGE_PRODUCTS_DESC", "Cette section vous permet d'&eacute;diter, ajouter ou supprimer des produits de votre catalogue TECHNI-CONTACT Online.<br/><br/>
	Mettez en page intuitivement vos descriptions, modifier les images de vos produits, tenez &agrave; jour vos tarifs.");
define("INDEX_MANAGE_CONTACTS", "G&eacute;rez vos contacts clients");
define("INDEX_MANAGE_CONTACTS_DESC", "<br/>
	Cette section vous permet de consulter les demandes effectu&eacute;s par des clients ou des prospects sur le site de TECHNI-CONTACT.<br/><br/>
	Vous y trouverez leurs coordonn&eacute;es et le d&eacute;tail de toutes leurs demandes.");
define("INDEX_CONSULT_STATS", "Consultez vos statistiques");
define("INDEX_CONSULT_STATS_DESC", "Consultez vos statistiques sur le site TECHNi-CONTACT !<br/><br/>
	Des filtres par produit, par mois ou par jour vous permettent d'affiner vos analyses et d'&eacute;tudier l'impact de votre r&eacute;f&eacute;rencement TECHNI-CONTACT par rapport &agrave; vos concurrents.");
define("INDEX_INFORMATION", "Modifier vos coordonn&eacute;es et infos persos");
define("INDEX_INFORMATION_DESC", "Cette section vous permet de mettre à jour les données concernant votre entreprise.<br/><br/>
	Ces &eacute;l&eacute;ments seront communiqu&eacute;s aux clients lors des demandes de contacts.");

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
define("COMMANDS_REF", "Ref.");
define("COMMANDS_DATE", "Date");
define("COMMANDS_STATUS", "Statut");
define("COMMANDS_CONTENT", "Contenu de la commande");
define("COMMANDS_AMOUNT_WITHOUT_VAT", "Montant H.T.");
define("COMMANDS_AMOUNT_ALL_INCLUDED", "Montant T.T.C.");
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
define("REQUESTS_BLOC_TITLE", "Vos demandes de contact clients");
define("REQUESTS_BLOC_DESC", "Voici la liste des demandes de contact que les visiteurs de site TECHNI-CONTACT ont effectu&eacute; pour vos produits.<br/>
    Ces demandes sont class&eacute;es de la plus r&eacute;cente &agrave; la plus ancienne. Les demandes que vous n'avez pas encore consult&eacute;es sont inscrites <strong>en gras</strong>.");
define("REQUESTS_CONTACT_LIST", "Liste de vos demandes");
define("REQUESTS_CONTACT_NAME_AND_COMPANY", "nom et société");
define("REQUESTS_CONTACT_RELATED_PRODUCT", "produit concern&eacute;");
define("REQUESTS_PARENT_SUPPLIER", "fournisseur");

/* Request Detail */
define("REQUEST_DETAIL_TITLE", "Demande de contact");
define("REQUEST_DETAIL_HEAD_TITLE", "Détail d'une demande");
define("REQUEST_DETAIL_ERROR_ID", "Identifiant de demande incorrect");
define("REQUEST_DETAIL_BLOC_TITLE", "D&eacute;tails de la demande");
define("REQUEST_DETAIL_BLOC_CMD_TITLE", "D&eacute;tails de la commande");
define("REQUEST_DETAIL_SEE_PRODUCT", "Voir la fiche produit");
define("REQUEST_DETAIL_PRODUCT_DELETED", "Ce produit n'est plus disponible dans notre catalogue");
define("REQUEST_DETAIL_BLOC_DESC_1", "Cette page vous pr&eacute;sente le d&eacute;tail de la demande effectu&eacute;e pour le produit");
define("REQUEST_DETAIL_BLOC_DESC_2", "Vous y trouverez les coordonn&eacute;es du prospect, ainsi que des pr&eacute;cisions sur sa demande.");
define("REQUEST_DETAIL_CONTACT_INFOS", "Informations sur la demande");
define("REQUEST_DETAIL_CMD_INFOS", "Informations sur la commande");
define("REQUEST_DETAIL_PRODUCT", "Produit concern&eacute;");
define("REQUEST_DETAIL_MESSAGE", "Message facultatif");
define("REQUEST_DETAIL_CUSTOMER_INFOS", "Informations personnelles de l'internaute");

define("REQUEST_DETAIL_COMPANY_INFOS", "Informations sur son entreprise");
define("REQUEST_DETAIL_COMPANY_NAME", "Nom de la soci&eacute;t&eacute;");

/* Invoices_list */
define("INVOICES_LIST_TITLE", "Contacts");
define("INVOICES_LIST_HEAD_TITLE", "Liste des demandes facturées");
define("INVOICES_LIST_BLOC_TITLE", "Vos demandes de contact clients facturées");
define("INVOICES_LIST_BLOC_DESC", "Voici la liste des demandes de contact que les visiteurs de site TECHNI-CONTACT ont effectu&eacute; pour vos produits et ont été facturées.<br/>
    Ces demandes sont class&eacute;es de la plus r&eacute;cente &agrave; la plus ancienne. Les demandes que vous n'avez pas encore consult&eacute;es sont inscrites <strong>en gras</strong>.");
define("INVOICES_LIST_CONTACT_LIST", "Liste de vos demandes");
define("INVOICES_LIST_CONTACT_NAME_AND_COMPANY", "nom et société");
define("INVOICES_LIST_CONTACT_RELATED_PRODUCT", "produit concern&eacute;");
define("INVOICES_LIST_PARENT_SUPPLIER", "fournisseur");

/* Search */
define("SEARCH_TITLE", "Fiches Produits");
define("SEARCH_DELETE_CONFIRMATION", "Etes-vous sûr de vouloir supprimer ce produit ?");
define("SEARCH_RESULTS", "Résultat de votre recherche");
define("SEARCH_ERROR_LENGTH", "Merci de saisir au minimum 3 caractères avant de lancer votre recherche.");
define("SEARCH_ASK_DELETE", "Demander la suppression de ce produit");

/* Products */
define("PRODUCTS_JS_ASK_DELETE_PRODUCT", "Etes-vous sûr de vouloir supprimer ce produit ?");
define("PRODUCTS_PRODUCT_LIST_TITLE", "Votre catalogue en ligne de produits");
define("PRODUCTS_PRODUCT_LIST_DESC", "Voici la liste de vos fiches produit actuellement en ligne sur le site TECHNI-CONTACT.<br />
			Vous pouvez &eacute;diter leur contenu (description, image, tarif) ou demander leur suppression. Un op&eacute;rateur TECHNI-CONTACT validera alors votre demande avant publication.<br />
			Pour plus de précisions n'hésitez pas à consulter notre <a href=\"Guide_pour_les_fiches_produits.doc\" target=\"_blank\">guide fiches produits</a>.");
define("PRODUCTS_ADD_PRODUCT", "Ajouter un nouveau produit");
define("PRODUCTS_WAITING_PRODUCTS", "Vos produits en attente de validation");
define("PRODUCTS_SEARCH", "Recherche");
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
define("PRODUCT_FAMILIES_LINKED", "cat&eacute;gorie(s) s&eacute;lectionn&eacute;e(s)");
define("PRODUCT_LINK", "Lier");
// Body
define("PRODUCT_DETAIL", "D&eacute;tails de la fiche produit (* = champ obligatoire)");
define("PRODUCT_SEE_ONLINE", "Pr&eacute;visualiser en ligne");
define("PRODUCT_NAME_LABEL", "Nom (mot clé google)");
define("PRODUCT_NAME_DESC", "Inscrivez ici le nom de votre fiche produit. C'est cette d&eacute;nomination qui sera consid&eacute;r&eacute; par google comme mot cl&eacute;");
define("PRODUCT_FASTDESC_LABEL", "Description rapide");
define("PRODUCT_FASTDESC_DESC", "Il s'agit d'un sous-titre, qui permet de distinguer deux fiches produits comportant le m&ecirc;me nom principal.<br/>
	Par exemple : &quot;bac - <u>grand format</u>&quot; et &quot;bac - <u>petit format</u>&quot;.");
define("PRODUCT_KEYWORDS_LABEL", "Mots-cl&eacute;s (recherche interne)");
define("PRODUCT_KEYWORDS_DESC", "Il s'agit des termes relatifs &agrave; votre produit que les internautes sont susceptibles de taper sur notre moteur de recherche. Un mot-cl&eacute; ne comprend qu'un seul mot et non une expression. Vous pouvez en saisir jusqu'&agrave; 5.");
define("PRODUCT_FAMILY_LABEL", "Cat&eacute;gorie");
define("PRODUCT_FAMILY_SELECT", "Sélectionnez une catégorie");
define("PRODUCT_FAMILY_DESC", "Choisissez ici la cat&eacute;gorie du site dans laquelle votre fiche produit appara&icirc;tra en ligne, et cliquer sur &quot;Lier&quot;. Vous pouvez s&eacute;lectionner autant de cat&eacute;gories que vous le souhaitez.<br/>
			L'arborescence des catégories comporte 3 niveaux d'abstraction : vous ne pouvez lier un produit qu'aux catégories de niveau 3 (niveau d'abstraction le plus faible).");
define("PRODUCT_DESCC_LABEL", "Description du produit");
define("PRODUCT_DESCC_DESC", "Inscrivez ici la description de votre fiche produit.<br />
			Ce petit &eacute;diteur de texte vous permet de la mettre en forme (gras,  taille du texte, etc.).");
define("PRODUCT_DESCD_LABEL", "Description détaillée");
define("PRODUCT_DESCD_DESC", "Si vous le souhaitez, vous pouvez ajouter en plus de la description générale, une description détailée. Celle-ci s'affichera en bas de la fiche produit, si l'internaute souhaite avoir des détails complémentaires.");
define("PRODUCT_IMAGE_LABEL", "Image du produit");
define("PRODUCT_IMAGE_DESC", "Vous pouvez mettre en ligne ici l'image de votre produit. Celle-ci doit être au format *.jpg et respecter des contraintes de poids et de taille.");
define("PRODUCT_DOC_LABEL", "Document facultatif");
define("PRODUCT_DOC_DESC", "Ce champ vous permet de mettre &agrave; disposition des internautes un fichier word ou pdf, comme une fiche technique d&eacute;taill&eacute;e ou un mod&egrave;le de devis.");
// Supplier
define("PRODUCT_SUP_PUBLIC_PRICE_HELP", "Saisissez ici le prix public du produit tel qu'il appara&icirc;tra sur la boutique en ligne.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_SUP_BUY_PRICE_HELP", "Saisissez ici le prix factur&eacute; &agrave; techni-contact.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_SUP_PUBLIC_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont les prix public tels qu'ils appara&icirc;tront sur la boutique en ligne.<br/>");
define("PRODUCT_SUP_BUY_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont ceux qui seront factur&eacute;s &agrave; techni-contact.<br/>");
define("PRODUCT_SUP_CONTACT_HELP", "En choisissant d'&ecirc;tre contact&eacute; pour le prix, ce produit ne sera pas consid&eacute;r&eacute; comme &eacute;tant en vente en ligne.");
define("PRODUCT_PRICE_TYPE_LABEL", "Type prix");
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
define("PRODUCT_ADV_PRICE_HELP", "Saisissez ici le prix public du produit tel qu'il appara&icirc;tra sur la boutique en ligne.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_ADV_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont les prix public tels qu'ils appara&icirc;tront sur la boutique en ligne.<br/>");
define("PRODUCT_ADV_CONTACT_HELP", "Demandez &agrave; &ecirc;tre contact&eacute; directement pour tout renseignement sur ce produit.");
// body
define("PRODUCT_BOTTOM_NOTE", "note : ces modifications seront validées par un opérateur TECHNI-CONTACT avant leur mise en ligne");

/* Add Product */
define("ADD_PRODUCT_TITLE", "Ajouter un nouveau produit");
define("ADD_PRODUCT_HEAD_TITLE", "Ajout d'un nouveau produit");
define("ADD_PRODUCT_SUCCESS", "Produit enregistré avec succès. Il sera en ligne dès sa validation par un opérateur TECHNI-CONTACT");
define("ADD_PRODUCT_ERROR_MAX", "Vous avez atteint le nombre maximal " . NB_MAX . " de produits en attente de validation de création");
define("ADD_PRODUCT_ERROR", "Erreur interne lors de la création du produit");
define("ADD_PRODUCT_SUBMIT", "Soumettre la fiche produit");

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
define("W_PRODUCT_HEAD_TITLE", "En attente");
define("W_PRODUCT_CREATE_BLOC_TITLE", "En attente de validation de création");
define("W_PRODUCT_EDIT_BLOC_TITLE", "En attente de validation de modification");
define("W_PRODUCT_REJECTED_BLOC_TITLE", "Demandes rejetées au cours des 15 derniers jours");
define("W_PRODUCT_CREATING", "Création");
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
define("PRODUCT_CARD_REF_LABEL", "Libell&eacute;");
define("PRODUCT_CARD_REF_UNIT", "Unit&eacute;");
define("PRODUCT_CARD_REF_PRICE_WITHOUT_VAT", "Prix HT");
define("PRODUCT_CARD_REF_QUANTITY", "Qt&eacute;");

/* Stats */
define("STATS_TITLE", "Statistiques");
define("STATS_HEAD_TITLE", "Statistiques");
define("STATS_MONTH_DETAIL", "Détail du mois");
define("STATS_DETAIL", "Détail");
define("STATS_OF_MONTH", "du mois");
define("STATS_OF_YEAR", "de l'année");
define("STATS_YOUR_STATS_BLOC_TITLE", "Vos statistiques");
define("STATS_YOUR_STATS_BLOC_DESC", "Voici les statistiques de consultation de vos fiches produits sur le site <strong>TECHNI-CONTACT</strong>.<br />
    Cliquez sur un mois pour affiner vos r&eacute;sultats.");
define("STATS_CONFIG", "Param&eacute;trage");
define("STATS_YEAR", "ann&eacute;e");
define("STATS_MONTH", "mois");
define("STATS_EVERY_MONTH", "Tous les mois");
define("STATS_ALL_PRODUCTS", "Tous les produits");
define("STATS_STATS_BLOC_TITLE_", "Statistiques");
define("STATS_GLOBAL", "g&eacute;n&eacute;rales");
define("STATS_OF_PRODUCT", "du produit");
define("STATS_PRODUCTS_STATS", "Statistiques produits");

/* Infos */
define("INFOS_TITLE", "Coordonn&eacute;es");
define("INFOS_HEAD_TITLE", "Mise à jour de vos coordonn&eacute;es");
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

define("INFOS_BLOC_TITLE", "Modification de vos coordonn&eacute;es");
define("INFOS_BLOC_DESC", "Cette page vous permet de modifier les coordonn&eacute;es de votre entreprise et de g&eacute;rer les informations li&eacute;es &agrave; votre compte extranet TECHNI-CONTACT.");
define("INFOS_BLOC_NOTE", "Note : une mise à jour de vos coordonnées est actuellement en attente de validation Techni-Contact. Une nouvelle mise à jour de votre part remplacera la précédente.");
define("INFOS_ERROR", "Une ou plusieurs erreurs sont survenues lors de la validation");

define("INFOS_COMPANY_BLOC", "Coordonn&eacute;es de votre soci&eacute;t&eacute;");
define("INFOS_COMPANY_NAME", "Nom de l'entreprise");

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

define("INFOS_ADVERTISER_CONTACT_BLOC", "Relations annonceurs");
define("INFOS_ADVERTISER_CONTACT", "Personne &agrave; contacter");
define("INFOS_ADVERTISER_CONTACT_DESC", "Renseignez ici les nom et pr&eacute;nom de la personne que les internautes peuvent contacter");
define("INFOS_ADVERTISER_EMAIL_DESC", "Renseignez ici l'adresse email de la personne &agrave; contacter. Cette adresse email est &eacute;galement li&eacute;e &agrave; votre compte extranet (envoi d'alertes, de mot de passe perdu, etc.)");

define("INFOS_EXTRANET_BLOC", "Configuration accès extranet");
define("INFOS_EXTRANET_LOGIN", "LOGIN Extranet");
define("INFOS_EXTRANET_LOGIN_DESC", "Il s'agit du login utilis&eacute; pour vous connecter &agrave; cet extranet priv&eacute;. Par d&eacute;faut, il s'agit du nom de votre soci&eacute;t&eacute; (sans caract&egrave;res sp&eacute;ciaux).");
define("INFOS_EXTRANET_PASS", "Nouveau MOT DE PASSE");
define("INFOS_EXTRANET_PASS_DESC", "Laissez ce champ vide si vous ne souhaitez pas modifier votre mot de passe");
define("INFOS_EXTRANET_PASS_CHECK", "Confirmation  MOT DE PASSE");
define("INFOS_EXTRANET_PASS_CHECK_DESC", "Si vous avez d&eacute;fini un nouveau mot de passe, confirmez-le en le resaisissant ici.");
define("INFOS_BUTTON_VALIDATE", "Valider mes modifications");
define("INFOS_BOTTOM_NOTE", "note : ces modifications seront effectives apr&egrave;s validation par un opérateur Techni-Contact");

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

/* Invoices */
// TODO

// Common
define("COMMON_SUPPLIER", "Fournisseur");
define("COMMON_CONTACT_TYPE", "Type de demande");
define("COMMON_CONTACT_TYPE_ASK", "Demande d'informations");
define("COMMON_CONTACT_TYPE_TEL", "Demande de contact téléphonique");
define("COMMON_CONTACT_TYPE_ESTIMATE", "Demande de devis");
define("COMMON_CONTACT_TYPE_APPOINTMENT", "Demande de rendez-vous");
define("COMMON_NO_RESULT", "Aucun résultat");
define("COMMON_SEARCH", "Recherche");
define("COMMON_BUTTON_VALIDATE", "Valider");
define("COMMON_PRODUCT", "Produit");
define("COMMON_INVOICE_STATE", "Etat Facturation");
define("COMMON_DATE", "Date");
define("COMMON_TYPE", "Type");
define("COMMON_MOTIVE", "Motif");
define("COMMON_CATEGORY", "Cat&eacute;gorie");
define("COMMON_PRODUCT_NAME", "Nom du produit");
define("COMMON_PRICE_TYPE_ON_DEMAND", "Sur demande");
define("COMMON_PRICE_TYPE_ON_ESTIMATE", "Sur devis");
define("COMMON_PRICE_TYPE_CONTACT_US", "Nous contacter");
define("COMMON_PRICE_TYPE_REFS", "Références");
define("COMMON_PRICE_TYPE_SIMPLE_PRICE", "Prix simple");
define("COMMON_ERROR_VALIDATE", "Une ou plusieurs erreurs sont survenues lors de la validation");
define("COMMON_ASK_TEL_CONTACT", "demander un contact t&eacute;l&eacute;phonique");
define("COMMON_ASK_ESTIMATE", "demander un devis");
define("COMMON_GET_INFOS", "obtenir des informations");
define("COMMON_ASK_APPOINTMENT", "demander un rendez-vous");
define("COMMON_APPLY", "Appliquer");

define("REJECT_DATE", "Date de rejet");

define("COMMON_JANUARY", "janvier");
define("COMMON_FEBRUARY", "février");
define("COMMON_MARCH", "mars");
define("COMMON_APRIL", "avril");
define("COMMON_MAY", "mai");
define("COMMON_JUNE", "juin");
define("COMMON_JULY", "juillet");
define("COMMON_AUGUST", "août");
define("COMMON_SEPTEMBER", "septembre");
define("COMMON_OCTOBER", "octobre");
define("COMMON_NOVEMBER", "novembre");
define("COMMON_DECEMBER", "décembre");
define("COMMON_JAN", "jan");
define("COMMON_FEB", "fev");
define("COMMON_MAR", "mar");
define("COMMON_APR", "avr");
define("COMMON_MAY", "mai");
define("COMMON_JUN", "juin");
define("COMMON_JUL", "juil");
define("COMMON_AUG", "aou");
define("COMMON_SEP", "sept");
define("COMMON_OCT", "oct");
define("COMMON_NOV", "nov");
define("COMMON_DEC", "dec");

define("COMMON_MONDAY", "Lundi");
define("COMMON_TUESDAY", "Mardi");
define("COMMON_WEDNESDAY", "Mercredi");
define("COMMON_THURSDAY", "Jeudi");
define("COMMON_FRIDAY", "Vendredi");
define("COMMON_SATURDAY", "Samedi");
define("COMMON_SUNDAY", "Dimanche");
	
define("COMMON_ALL_M", "Tous");
define("COMMON_ALL_F", "Toutes");

define("INFOS_LAST_NAME", "Nom");
define("INFOS_FIRST_NAME", "Pr&eacute;nom");
define("INFOS_JOB", "Fonction");
define("INFOS_EMAIL", "Email");
define("INFOS_EMAIL_ADDRESS", "Adresse email");
define("INFOS_ADDRESS", "Adresse");
define("INFOS_COMPLEMENT", "Compl&eacute;ment adresse");
define("INFOS_CITY", "Ville");
define("INFOS_PC", "Code Postal");
define("INFOS_COUNTRY", "Pays");
define("INFOS_TEL1", "T&eacute;l&eacute;phone");
define("INFOS_TEL2", "T&eacute;l&eacute;phone 2");
define("INFOS_FAX1", "FAX");
define("INFOS_FAX2", "FAX 2");
define("INFOS_URL", "Site Internet");
define("INFOS_NUMBER_OF_EMPLOYEES", "Nb de salari&eacute;");
define("INFOS_ACTIVITY_SECTOR", "Secteur d'activit&eacute;");
define("INFOS_NAF_CODE", "Code NAF");
define("INFOS_SIREN_NUMBER", "SIRET");
define("WARNING_LITIGATION", "Suite &agrave; litige pour non paiement de facture, nous avons malheureusement d&ucirc; bloquer toutes vos nouvelles demandes de contact.<br />Afin de r&eacute;gulariser rapidement votre situation, nous vous invitons &agrave; contacter le service comptabilit&eacute; au 01 55 60 29 24 ou comptabilite@techni-contact.com")
?>