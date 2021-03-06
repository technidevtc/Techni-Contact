<?php
/**** Includes ****/
/* Alert Password */
define("ALERT_PASS_BECAREFUL", "¡Cuidado!");
define("ALERT_PASS_DESC", "Le aconsejamos que modifique su contraseña inicial de conexión para definir una más segura!");
define("ALERT_PASS_EDIT", "Modificar su contraseña.");

/* Head */
define("TITLE", "Extranet annonceurs TECHNI-CONTACT");

/**** Secure ****/
/* Where */
define("WHERE_INDEX", "Inicio");
define("WHERE_COMMANDS", "Commandes");
define("WHERE_CONTACT", "Contactos");
define("WHERE_PRODUCTS_CARD", "Fichas de productos");
define("WHERE_STATS", "Estadísticas");
define("WHERE_INFOS", "Datos");
define("WHERE_INVOICING", "Facturación");
define("WHERE_INVOICES", "Factures");

/* Global */
define("HEAD_HOMEPAGE", "Inicio");
define("HEAD_COMMAND_LIST", "Liste des commandes");
define("HEAD_PRODUCT_LIST", "Lista de sus productos");
define("TITLE_PRODUCTS_CARDS", "Fichas de productos");

/* Login */
define("LOGIN_TITLE", "Extranet annonceurs TECHNI-CONTACT - Merci de vous identifier");
define("LOGIN_WELCOME_MSG", "Bienvenido sobre el extranet de los anunciantes TECHNI-CONTACT.");
define("LOGIN_IDENT_TITLE", "indentificación");
define("LOGIN_IDENT_ASK", "Indentifiquese par continuar");
define("LOGIN_ERROR_INACTIVE", "Votre compte est actuellement inactif");
define("LOGIN_ERROR_IDENT", "Identifiant et / ou mot de passe invalide(s)");
define("LOGIN_IDENT", "nombre de usuario");
define("LOGIN_PASS", "contraseña");
define("LOGIN_FORGET", "¿Ha olvidado su contraseña?");
define("LOGIN_SECURITY_TITLE", "Información de Seguridad");
define("LOGIN_SECURITY_DESC", "El extranet de TECHNI-CONTACT está asegurado según los criterios los más elevados disponibles en el mercado. Para asegurarse de que se sienta bien sobre nuestro extranet, le aconsejamos verifique los puntos siguientes:
<ul>
	<li>La dirección de la página web sobre la cual se encuentre está bien " . EXTRANET_URL . "login.html,</li>
	<li>El logo <img src=\"" . EXTRANET_URL . "ressources/images/ssl-ie.jpg\" alt=\"\" width=\"104\" height=\"19\" align=\"absmiddle\"> - si usted utiliza Internet Explorer
		- o el logo <img src=\"" . EXTRANET_URL . "ressources/images/ssl-firefox.jpg\" alt=\"\" width=\"155\" height=\"19\" align=\"absmiddle\"> - si usted utiliza Firefox, es bien presente,</li>
	<li>Ningún mensaje de advertencia relativo a un problema de validez del certificado SSL (origen, expiración, campo de aplicación) ha aparecido en la pantalla.</li>
</ul>
Si uno de los puntos enunciados más arriba no se cumple o si estas normas no están presentadas en la página de indentificación usted se encuentra seguramente frente a un intento de pirateo de su cuenta de usuario, interrumpa todo proceso de identificación y póngase en contacto con su interlocutor Techni-Contact.<br/>
<br/>
Notificación: nunca se le pedirá introducir su contraseña fuera de esta página.");

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
define("INDEX_LOGOUT", "Desconexión");
define("INDEX_WELCOME_ADVERTISER", "Bienvenido");
define("INDEX_WELCOME_MSG_ADVERTISER", "sobre el extranet de los anunciantes TECHNI-CONTACT");
define("INDEX_CONTACT_READ_PRE", "¡Usted tiene <strong>");
define("INDEX_CONTACT_READ_0", "0 solicitude de contacto</strong> no leídas");
define("INDEX_CONTACT_READ_1", "1 solicitude de contacto</strong> no leídas");
define("INDEX_CONTACT_READ_N", "solicitudes de contactos</strong> no leídas");
define("INDEX_NO_CONTACT", "aucune demande</strong>");

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
define("INDEX_MANAGE_PRODUCTS", "Gestión de sus fichas de productos");
define("INDEX_MANAGE_PRODUCTS_DESC", "Esta sección le permite editar, añadir o suprimir productos de su catálogo TECHNI-CONTACT Online.<br/><br/>
	Compagina usted sus descripciones intuitivamente, modifique las imágenes de sus productos, actualize sus tarifas.");
define("INDEX_MANAGE_CONTACTS", "Gestión de sus contactos de clientes");
define("INDEX_MANAGE_CONTACTS_DESC", "<br/>
	Esta sección le permite consultar las peticiones realizadas por clientes o clientes potenciales en el sitio de TECHNI-CONTACT.<br/><br/>
	Usted encontrará en este sitio sus datos y el detalle de todas sus peticiones.");
define("INDEX_CONSULT_STATS", "Consultar sus estadísticas");
define("INDEX_CONSULT_STATS_DESC", "¡Consulte usted sus estadísticas en el sitio de TECHNI-CONTACT!<br/><br/>
	Filtros para cada producto, para cada mes o para cada día le permitte refinar sus análisis y estudiar el impacto de su referencia TECHNI-CONTACT con respecto a sus competidores.");
define("INDEX_INFORMATION", "Modificar sus datos e informaciones personales");
define("INDEX_INFORMATION_DESC", "Esta sección le permite actualizar los datos relativos a su empresa.<br/><br/>
	Estos elementos les serán comunicados a los clientes durante las peticiones de contactos.");

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
define("REQUESTS_TITLE", "Contactos");
define("REQUESTS_HEAD_TITLE", "Liste des demandes");
define("REQUESTS_BLOC_TITLE", "Sus peticiones de contactos de clientes");
define("REQUESTS_BLOC_DESC", "Aquí está la lista de las peticiones de contactos que los visitantes del sitio de TECHNI-CONTACT han realizado para sus productos.<br/>
	Estas peticiones están clasificadas de la más reciente a la más viejas. Las peticiones que usted aún no ha consultado están inscritas <strong>en negrita</strong>.");
define("REQUESTS_CONTACT_LIST", "Lista de sus peticiones");
define("REQUESTS_CONTACT_NAME_AND_COMPANY", "nombre y sociedad");
define("REQUESTS_CONTACT_RELATED_PRODUCT", "tipo de producto");

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
define("REQUEST_DETAIL_PRODUCT", "Tipo de producto");
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
define("PRODUCTS_PRODUCT_LIST_TITLE", "Su catálogo online de productos");
define("PRODUCTS_PRODUCT_LIST_DESC", "Aquí está la lista de sus fichas de productos actualment online sobre el sitio de TECHNI-CONTACT.<br/>
			Usted pueda editar su contenido (descripción, imagén, tarifa) o pedir su supresión. Un operador TECHNI-CONTACT validará entonces du petición antes de la publicación.<br/>
			Para mayor información, consultar nuestra <a href=\"Guide_pour_les_fiches_produits.doc\" target=\"_blank\">guía de fichas</a>.");
define("PRODUCTS_ADD_PRODUCT", "Añadir un nuevo producto");
define("PRODUCTS_WAITING_PRODUCTS", "Sus productos en espera de validación");
define("PRODUCTS_SEARCH", "Buscar");
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
define("PRODUCT_FAMILIES_LINKED", "categoría(s) seleccionada(s)");
define("PRODUCT_LINK", "Ligar");
// Body
define("PRODUCT_DETAIL", "Detalles de la ficha de producto ( *=campo obligatorio)");
define("PRODUCT_SEE_ONLINE", "Previzualizar online");
define("PRODUCT_NAME_LABEL", "nombre (palabra clave google)");
define("PRODUCT_NAME_DESC", "Inscriba aquí el nombre de su ficha de productos. Será esta denominación que estará considerada como palabra clave por Google");
define("PRODUCT_FASTDESC_LABEL", "Descripción rápida");
define("PRODUCT_FASTDESC_DESC", "Se trata de un subtítulo, que permite distinguir dos fichas de productos conllevando el mismo nombre principal.<br/>
	Por ejemplo: &quot; recipiente de - <u>gran tamaño</u>&quot; y &quot;recipiente de - <u>pequeño tamaño</u>&quot;");
define("PRODUCT_KEYWORDS_LABEL", "Palabra clave (busca interna)");
define("PRODUCT_KEYWORDS_DESC", "Se trata de los términos relativo a su producto que los internautas susceptibles de picar en nuestro motor de busca. Una palabra clave contiene una sola palabra y no una frase. Se puede escribir hasta 5 palabras.");
define("PRODUCT_FAMILY_LABEL", "Categorías");
define("PRODUCT_FAMILY_SELECT", "Seleccionar una categoría");
define("PRODUCT_FAMILY_DESC", "Escoga aquí la categoría del sitio en la cual su ficha de productos aparecerá online, y haga un clic sobre \"ligar\". Usted puede seleccionar tantas categorías como lo desea.<br/>
	El árbol de directorios contiene 3 niveles de abstracción: se puede sólo ligar un producto con las categorías de nivel 3 (nivel de abstracción el más bajo).");
define("PRODUCT_DESCC_LABEL", "Descripción del productot");
define("PRODUCT_DESCC_DESC", "Registre aquí la descripción de su ficha de productos.<br/>
	Este pequeño editor de textos le permite compaginarla  (negrita, tamaño de los carácteres, ect.)");
define("PRODUCT_DESCD_LABEL", "Descripción detallada");
define("PRODUCT_DESCD_DESC", "Si usted lo desee, puede añadir una descripción detallada además de la descripción general. Esta descripción  figura al bajo de la ficha de productos, si el internauta desee obtener mayores detalles.");
define("PRODUCT_IMAGE_LABEL", "Imagén del producto");
define("PRODUCT_IMAGE_DESC", "Usted puede poner online aquí la imagén de su producto Esta imagen debe ser en el formato *jpg y respetar los fastidios de peso y de tamaño.");
define("PRODUCT_DOC_LABEL", "Documentos facultativos");
define("PRODUCT_DOC_DESC", "Este campo le permite poner a disposición de los internautas un fichero word o pdf, como una ficha técnica detallada o un modelo de presupuesto.");
// Supplier
define("PRODUCT_SUP_PUBLIC_PRICE_HELP", "Saisissez ici le prix public du produit tel qu'il appara&icirc;tra sur la boutique en ligne.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_SUP_BUY_PRICE_HELP", "Saisissez ici le prix factur&eacute; &agrave; techni-contact.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_SUP_PUBLIC_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont les prix public tels qu'ils appara&icirc;tront sur la boutique en ligne.<br/>");
define("PRODUCT_SUP_BUY_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont ceux qui seront factur&eacute;s &agrave; techni-contact.<br/>");
define("PRODUCT_SUP_CONTACT_HELP", "En choisissant d'&ecirc;tre contact&eacute; pour le prix, ce produit ne sera pas consid&eacute;r&eacute; comme &eacute;tant en vente en ligne.");
define("PRODUCT_PRICE_TYPE_LABEL", "Tipo de precios");
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
define("PRODUCT_ADV_PRICE_HELP", "Registre aquí el precio público del producto tal como aparecerá en la tienda online. Si usted tenga referencias registradas para este productos, ellas estarán borradas durante la validación.");
define("PRODUCT_ADV_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont les prix public tels qu'ils appara&icirc;tront sur la boutique en ligne.<br/>");
define("PRODUCT_ADV_CONTACT_HELP", "Demandez &agrave; &ecirc;tre contact&eacute; directement pour tout renseignement sur ce produit.");
// body
define("PRODUCT_BOTTOM_NOTE", "notificación: estas modificaciones estarán validadas por un operador de TECHNI-CONTACT antes de ponerlas online");

/* Add Product */
define("ADD_PRODUCT_TITLE", "Añadir un nuevo producto");
define("ADD_PRODUCT_HEAD_TITLE", "Ajout d'un nouveau produit");
define("ADD_PRODUCT_SUCCESS", "Produit enregistré avec succès. Il sera en ligne dès sa validation par un opérateur TECHNI-CONTACT");
define("ADD_PRODUCT_ERROR_MAX", "Vous avez atteint le nombre maximal " . NB_MAX . " de produits en attente de validation de création");
define("ADD_PRODUCT_ERROR", "Erreur interne lors de la création du produit");
define("ADD_PRODUCT_SUBMIT", "Someter la ficha de productos");

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
define("W_PRODUCT_TITLE", "Sus productos en espera de validación");
define("W_PRODUCT_HEAD_TITLE", "En espera");
define("W_PRODUCT_CREATE_BLOC_TITLE", "En espera de validación para la creación");
define("W_PRODUCT_EDIT_BLOC_TITLE", "En espera de validación para la modificación");
define("W_PRODUCT_REJECTED_BLOC_TITLE", "Peticiones rechazadas durante los 15 días pasados");
define("W_PRODUCT_CREATING", "Creación");
define("W_PRODUCT_EDITING", "Modificación");

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
define("PRODUCT_CARD_PRICE", "Precio");
define("PRODUCT_CARD_QUANTITY", "Quantité");
define("PRODUCT_CARD_WITHOUT_VAT", "HT");
define("PRODUCT_CARD_REF", "R&eacute;f&eacute;rences");
define("PRODUCT_CARD_REF_IDTC", "R&eacute;f. TC");
define("PRODUCT_CARD_REF_LABEL", "Redacción");
define("PRODUCT_CARD_REF_UNIT", "Unit&eacute;");
define("PRODUCT_CARD_REF_PRICE_WITHOUT_VAT", "Prix HT");
define("PRODUCT_CARD_REF_QUANTITY", "Qt&eacute;");

/* Stats */
define("STATS_TITLE", "Estadísticas");
define("STATS_HEAD_TITLE", "Estadísticas");
define("STATS_MONTH_DETAIL", "Détail du mois");
define("STATS_DETAIL", "Détail");
define("STATS_OF_MONTH", "du mois");
define("STATS_OF_YEAR", "de l'année");
define("STATS_YOUR_STATS_BLOC_TITLE", "Sus estadísticas");
define("STATS_YOUR_STATS_BLOC_DESC", "Aquí están las estadísticas de consulta de sus fichas de productos sobre el sitio de <strong>TECHNI-CONTACT</strong>.<br/>
    Haga clic en un mes para refinar sus resultados.");
define("STATS_CONFIG", "Parametrizar");
define("STATS_YEAR", "año");
define("STATS_MONTH", "mes");
define("STATS_EVERY_MONTH", "Todos los meses");
define("STATS_ALL_PRODUCTS", "Todos los productos");
define("STATS_STATS_BLOC_TITLE_", "Estadísticas");
define("STATS_GLOBAL", "generales");
define("STATS_OF_PRODUCT", "du produit");
define("STATS_PRODUCTS_STATS", "Estadísticas de productos");

/* Infos */
define("INFOS_TITLE", "Datos");
define("INFOS_HEAD_TITLE", "Actualizar sus datos");
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

define("INFOS_BLOC_TITLE", "Modificar sus datos");
define("INFOS_BLOC_DESC", "Esta página le permite modificar los datos de su empresa y gestionar las informaciones relativo a su cuenta extranet TECHNI-CONTACT.");
define("INFOS_BLOC_NOTE", "Note : une mise à jour de vos coordonnées est actuellement en attente de validation Techni-Contact. Une nouvelle mise à jour de votre part remplacera la précédente.");
define("INFOS_ERROR", "Une ou plusieurs erreurs sont survenues lors de la validation");

define("INFOS_COMPANY_BLOC", "Datos de su empresa");
define("INFOS_COMPANY_NAME", "Nombre de la empresa");

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

define("INFOS_ADVERTISER_CONTACT_BLOC", "Relaciones con anunciantes");
define("INFOS_ADVERTISER_CONTACT", "Persona de contacto");
define("INFOS_ADVERTISER_CONTACT_DESC", "Escriba aquí el nombre y apellido de la persona a quién los internautas pueden contactar");
define("INFOS_ADVERTISER_EMAIL_DESC", "Escriba aquí la dirección e-mail de la personna de contacto. Esta dirección e-mail está también relacionada con su cuenta extranet ( envío de avisos, de contraseñas olvidadas, ect.)");

define("INFOS_EXTRANET_BLOC", "Configuración del acceso a internet");
define("INFOS_EXTRANET_LOGIN", "LOGIN Extranet");
define("INFOS_EXTRANET_LOGIN_DESC", "Se trata del login utilizado para que se conecte a este extranet privado. Por defecto, se trata del nombre de su sociedad ( sin carácteres especiales).");
define("INFOS_EXTRANET_PASS", "Nueva contraseña");
define("INFOS_EXTRANET_PASS_DESC", "Deje usted este espacio vacío si no quiera modificar su contraseña.");
define("INFOS_EXTRANET_PASS_CHECK", "Confirmación de contraseña");
define("INFOS_EXTRANET_PASS_CHECK_DESC", "Si usted haga definido una nueva contraseña, confirmelo picandolo de nuevo aquí.");
define("INFOS_BUTTON_VALIDATE", "Validar mis modificaciones");
define("INFOS_BOTTOM_NOTE", "notificación: estas modificaciones serán efectivas después validación por un operador de TECHNI-CONTACT");

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
define("COMMON_CONTACT_TYPE", "Tipo de petición");
define("COMMON_CONTACT_TYPE_ASK", "Demande d'informations");
define("COMMON_CONTACT_TYPE_TEL", "Demande de contact téléphonique");
define("COMMON_CONTACT_TYPE_ESTIMATE", "Demande de devis");
define("COMMON_CONTACT_TYPE_APPOINTMENT", "Demande de rendez-vous");
define("COMMON_NO_RESULT", "Ningún resultado");
define("COMMON_SEARCH", "Recherche");
define("COMMON_BUTTON_VALIDATE", "Validar");
define("COMMON_PRODUCT", "Produit");
define("COMMON_DATE", "Fecha");
define("COMMON_TYPE", "Tipo");
define("COMMON_MOTIVE", "Motif");
define("COMMON_CATEGORY", "Categoría");
define("COMMON_PRODUCT_NAME", "Nombre del producto");
define("COMMON_PRICE_TYPE_ON_DEMAND", "A petición");
define("COMMON_PRICE_TYPE_ON_ESTIMATE", "Sobre presupuesto");
define("COMMON_PRICE_TYPE_CONTACT_US", "Contáctanos");
define("COMMON_PRICE_TYPE_REFS", "Referencias");
define("COMMON_PRICE_TYPE_SIMPLE_PRICE", "Precio simple");
define("COMMON_ERROR_VALIDATE", "Une ou plusieurs erreurs sont survenues lors de la validation");
define("COMMON_ASK_TEL_CONTACT", "demander un contact t&eacute;l&eacute;phonique");
define("COMMON_ASK_ESTIMATE", "demander un devis");
define("COMMON_GET_INFOS", "obtenir des informations");
define("COMMON_ASK_APPOINTMENT", "demander un rendez-vous");
define("COMMON_APPLY", "Aplicar");

define("COMMON_JANUARY", "enero");
define("COMMON_FEBRUARY", "febrero");
define("COMMON_MARCH", "marzo");
define("COMMON_APRIL", "abril");
define("COMMON_MAY", "mayo");
define("COMMON_JUNE", "junio");
define("COMMON_JULY", "julio");
define("COMMON_AUGUST", "agosto");
define("COMMON_SEPTEMBER", "septiembre");
define("COMMON_OCTOBER", "octubre");
define("COMMON_NOVEMBER", "noviembre");
define("COMMON_DECEMBER", "diciembre");
define("COMMON_JAN", "ene");
define("COMMON_FEB", "feb");
define("COMMON_MAR", "mar");
define("COMMON_APR", "abr");
define("COMMON_MAY", "may");
define("COMMON_JUN", "jun");
define("COMMON_JUL", "jul");
define("COMMON_AUG", "ago");
define("COMMON_SEP", "sept");
define("COMMON_OCT", "oct");
define("COMMON_NOV", "nov");
define("COMMON_DEC", "dic");

define("INFOS_LAST_NAME", "Apellido");
define("INFOS_FIRST_NAME", "Nombre");
define("INFOS_JOB", "Cargo");
define("INFOS_EMAIL", "Email");
define("INFOS_EMAIL_ADDRESS", "Dirección e-mail");
define("INFOS_ADDRESS", "Dirección");
define("INFOS_COMPLEMENT", "Complemento de dirección");
define("INFOS_CITY", "Ciudad");
define("INFOS_PC", "Código postal");
define("INFOS_COUNTRY", "País");
define("INFOS_TEL1", "Teléfono");
define("INFOS_TEL2", "Teléfono 2");
define("INFOS_FAX1", "FAX");
define("INFOS_FAX2", "FAX 2");
define("INFOS_URL", "Sitio web");
define("INFOS_NUMBER_OF_EMPLOYEES", "Número de los asalariados");
define("INFOS_ACTIVITY_SECTOR", "Sector de actividad");
define("INFOS_NAF_CODE", "Código NAF");
define("INFOS_SIREN_NUMBER", "SIRET");

?>