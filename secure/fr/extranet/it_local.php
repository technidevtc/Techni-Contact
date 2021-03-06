<?php
/**** Includes ****/
/* Alert Password */
define("ALERT_PASS_BECAREFUL", "Attenzione!");
define("ALERT_PASS_DESC", "E motlo consigliato di modificare la password iniziale per definirne una piu sicura!");
define("ALERT_PASS_EDIT", "Modificare la password.");

/* Head */
define("TITLE", "Extranet annonceurs TECHNI-CONTACT");

/**** Secure ****/
/* Where */
define("WHERE_INDEX", "Home");
define("WHERE_COMMANDS", "Commandes");
define("WHERE_CONTACT", "Contatti");
define("WHERE_PRODUCTS_CARD", "Schede prodotti");
define("WHERE_STATS", "Statistiche");
define("WHERE_INFOS", "Dati personali");
define("WHERE_INVOICING", "Fatturazione");
define("WHERE_INVOICES", "Factures");

/* Global */
define("HEAD_HOMEPAGE", "Accueil");
define("HEAD_COMMAND_LIST", "Liste des commandes");
define("HEAD_PRODUCT_LIST", "Lista dei vostri prodotti");
define("TITLE_PRODUCTS_CARDS", "Fiches Produits");

/* Login */
define("LOGIN_TITLE", "Extranet annonceurs TECHNI-CONTACT - Merci de vous identifier");
define("LOGIN_WELCOME_MSG", "Cenvenuto sul extranet inserzionista TECHNI-CONTACT.");
define("LOGIN_IDENT_TITLE", "Identificazione");
define("LOGIN_IDENT_ASK", "Volete identificarvi prima di proseguire");
define("LOGIN_ERROR_INACTIVE", "Votre compte est actuellement inactif");
define("LOGIN_ERROR_IDENT", "Identifiant et / ou mot de passe invalide(s)");
define("LOGIN_IDENT", "nome del utente");
define("LOGIN_PASS", "password");
define("LOGIN_FORGET", "ha dimenticato la password?");
define("LOGIN_SECURITY_TITLE", "Informazioni sicurezza");
define("LOGIN_SECURITY_DESC", "L'extranet inserzionista TECHNI-CONTACT e siccurissimo secondi i criteri piu elevati disponibili sul mercato. Per assicurarvi di essere sul nostro extranet, vi raccomandiamo di verificare i punti seguenti:
<ul>
	<li>L'indirizzio sul quale vi trovate e bene il seguente: " . EXTRANET_URL . "login.html,</li>
	<li>Il logoo <img src=\"" . EXTRANET_URL . "ressources/images/ssl-ie.jpg\" alt=\"\" width=\"104\" height=\"19\" align=\"absmiddle\"> - se utilizzate Internet Explorer
		- o il logo <img src=\"" . EXTRANET_URL . "ressources/images/ssl-firefox.jpg\" alt=\"\" width=\"155\" height=\"19\" align=\"absmiddle\"> - se utilizzate Internet Firefox - è presente,</li>
	<li>Nessun messaggio d'avvertimento riguardante un problema di validità del certificato SSL (origine, scadenza, campo d'applicazione) è apparso sullo schermo.</li>
</ul>
Se uno dei punti precedenti non è rispetato o che non appariscono le regole quando si apre la pagine d'identificazione, siete potenzialemente in pericolo di pirateria del vostro conto utilizzatore. Fermate il procedimento d'identificazione e contattate il vostro interlocutore TECHNI-CONTACT.<br/>
<br/>
Nota: non vi sarà mai richiesto di communicare la Password fuori di questa pagina");

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
define("INDEX_LOGOUT", "Deconnessione");
define("INDEX_WELCOME_ADVERTISER", "Benvenuto");
define("INDEX_WELCOME_MSG_ADVERTISER", "sul extranet inserzionista TECHNI-CONTACT.");
define("INDEX_CONTACT_READ_PRE", "Avete <strong>");
define("INDEX_CONTACT_READ_0", "0 domande di contatto</strong> non lette");
define("INDEX_CONTACT_READ_1", "1 domande di contatto</strong> non lette");
define("INDEX_CONTACT_READ_N", "domande di contatto</strong> non lette");
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
define("INDEX_MANAGE_PRODUCTS", "Gestire le vostre schede prodotti");
define("INDEX_MANAGE_PRODUCTS_DESC", "Questa sezione vi permette di pubblicare, aggiungere o togliere dei prodotti del vostro catalogo TECHNI-CONTACT Online.<br/><br/>
	Mettete in forma intuitivamente le vostre descizione, modificate le immagini dei vostri prodotti, tenete aggiornate le vostre tariffe.");
define("INDEX_MANAGE_CONTACTS", "Gestire i vostri contatti clienti");
define("INDEX_MANAGE_CONTACTS_DESC", "<br/>
	Questa sezione vi permette di consultare le domande effettuate dai clienti o prospetti sul sito di TECHNI-CONTACT.<br/><br/>
	Ci troverete i loro dati ed il dettaglio di tutte le loro domande.");
define("INDEX_CONSULT_STATS", "Consultate le vostre statistiche");
define("INDEX_CONSULT_STATS_DESC", "Consultate le vostre statistiche sul sito TECHNI-CONTACT!<br/><br/>
	Filtri per prodotto, per mese, per giorno vi permettono di affinare le vostre analisi e di studiare l'impatto dei vostri riferimenti TECHNI-CONTACT rispetto ai vosti concorrenti.");
define("INDEX_INFORMATION", "Modificare i vostri dati personali");
define("INDEX_INFORMATION_DESC", "Questa sezione vi permette l'aggiornamento dei dati riguardanti la vostra impresa.<br/><br/>
	Questi elementi saranno comunicati ai clienti in occazione delle richieste di contatti.");

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
define("REQUESTS_BLOC_TITLE", "Le vostre domande di contatto clienti");
define("REQUESTS_BLOC_DESC", "Ecco l'elenco delle domande di contatto che gli ospiti del sito TECHNI-CONTACT hanno effettuato per i vostri prodotti.<br/>
    Queste domande sono archivate dalla piu recente alla piu vecchia. Le domande che non avete ancora consultato sono scritte <strong>in grasso</strong>.");
define("REQUESTS_CONTACT_LIST", "Lista delle domande");
define("REQUESTS_CONTACT_NAME_AND_COMPANY", "nome e società");
define("REQUESTS_CONTACT_RELATED_PRODUCT", "prodotto coinvolto");

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
define("REQUEST_DETAIL_PRODUCT", "Prodotto coinvolto");
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
define("PRODUCTS_PRODUCT_LIST_TITLE", "Il vostro catalogo on line di prodotti ");
define("PRODUCTS_PRODUCT_LIST_DESC", "Ecco la lista delle vostre schede prodotti attualmente on line sul sito TECHNI-CONTACT.<br/>
	Potete pubblicare il loro contenuto (descrizione, immagine, tariffa) o chiedere la loro soppressione. Un operatore TECHNI-CONTACT convaliderà la vostra domanda prima della pubblicazione.<br/>
	Per avere piu precizioni non esitare a consultare la nostra <a href=\"Guide_pour_les_fiches_produits.doc\" target=\"_blank\">guida schede prodotti</a>.");
define("PRODUCTS_ADD_PRODUCT", "Aggiungere un nuovo prodotto");
define("PRODUCTS_WAITING_PRODUCTS", "I vostri prodotti in attesa di validazione");
define("PRODUCTS_SEARCH", "Ricerca");
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
define("PRODUCT_FAMILIES_LINKED", "categoria(e) selezionata(e)");
define("PRODUCT_LINK", "Legare");
// Body
define("PRODUCT_DETAIL", "Dettagli della scheda prodotto (* = dato obbligatorio)");
define("PRODUCT_SEE_ONLINE", "Previsualizzare in linea");
define("PRODUCT_NAME_LABEL", "Nome(parola chiave google)");
define("PRODUCT_NAME_DESC", "Iscrivere qui il nome della vostra scheda prodotto. È questa denominazione che sarà considerato da google come parola chiave.");
define("PRODUCT_FASTDESC_LABEL", "Descrizione veloce");
define("PRODUCT_FASTDESC_DESC", "Si tratta di un sottotitolo, che permette di distinguere due  schede prodotti che comportano lo stesso nome principale.<br/>
	Ad esempio: &quot;vasca - <u>grande formato</u>&quot; e &quot;vasca - <u>piccolo format</u>&quot;.");
define("PRODUCT_KEYWORDS_LABEL", "Parole chiave (ricerca interna)");
define("PRODUCT_KEYWORDS_DESC", "Si tratta dei termini relativi al vostro prodotto che i clienti sono suscettibili di scrivere sul nostro motore di ricerca. Una parola chiave comprende una parola unica e non un'espressione. Potete scriverne fino a 5.");
define("PRODUCT_FAMILY_LABEL", "Categoria");
define("PRODUCT_FAMILY_SELECT", "Selezionate una categoria");
define("PRODUCT_FAMILY_DESC", "Scegliete qui la categoria del sito nella quale la vostra scheda prodotto apparirà in linea, e cliccare \"su legare\". Potete scegliere altrettante categorie che lo desiderate.<br/>
	La classificazione delle categorie comporta 3 livelli d'astrazione: potete legare un prodotto soltanto alle categorie di livello 3 (livello d'astrazione più debole).");
define("PRODUCT_DESCC_LABEL", "Descrizione del prodotto");
define("PRODUCT_DESCC_DESC", "Scrivete qui la descrizione della vostra scheda prodotto.<br/>
	Questo editore di testo vi permette di metterla in forma (grasso, dimensione del testo, ecc.).");
define("PRODUCT_DESCD_LABEL", "Descrizione dettagliata");
define("PRODUCT_DESCD_DESC", "Se lo desiderate potete aggiungere alla descrizione generale, una descrizione dettagliata. Quella apparirà in fondo della scheda prodotto, se il cliente desidera avere dettagli complementari.");
define("PRODUCT_IMAGE_LABEL", "Immagine del prodotto");
define("PRODUCT_IMAGE_DESC", "Qui potete mettere in linea l'immagine del vostro prodotto. Quella deve essere al formato * jpg e rispettare costrizioni di peso e di taglia.");
define("PRODUCT_DOC_LABEL", "Documento facoltativo");
define("PRODUCT_DOC_DESC", "Questa zona vi permette di mettere a disposizione dei clienti un archivio Word o pdf, come una scheda tecnica dettagliata o un modello di preventivo.");
// Supplier
define("PRODUCT_SUP_PUBLIC_PRICE_HELP", "Saisissez ici le prix public du produit tel qu'il appara&icirc;tra sur la boutique en ligne.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_SUP_BUY_PRICE_HELP", "Saisissez ici le prix factur&eacute; &agrave; techni-contact.<br/>Si vous aviez des r&eacute;f&eacute;rences de saisies pour ce produit, elles seront effac&eacute;es lors de la validation.");
define("PRODUCT_SUP_PUBLIC_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont les prix public tels qu'ils appara&icirc;tront sur la boutique en ligne.<br/>");
define("PRODUCT_SUP_BUY_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont ceux qui seront factur&eacute;s &agrave; techni-contact.<br/>");
define("PRODUCT_SUP_CONTACT_HELP", "En choisissant d'&ecirc;tre contact&eacute; pour le prix, ce produit ne sera pas consid&eacute;r&eacute; comme &eacute;tant en vente en ligne.");
define("PRODUCT_PRICE_TYPE_LABEL", "Tipo di prezzo");
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
define("PRODUCT_ADV_PRICE_HELP", "Scrivete qui il prezzo pubblico del prodotto così come apparirà sul negozio in linea.<br/>Se aveste scritto riferimenti per questo prodotto, saranno cancellati dopo la validazione.");
define("PRODUCT_ADV_REF_HELP", "Saisissez ici les r&eacute;f&eacute;rences de votre produit en indiquant au moins une caract&eacute;ristique en plus du libel&eacute;.<br/>Les prix des r&eacute;f&eacute;rences sont les prix public tels qu'ils appara&icirc;tront sur la boutique en ligne.<br/>");
define("PRODUCT_ADV_CONTACT_HELP", "Demandez &agrave; &ecirc;tre contact&eacute; directement pour tout renseignement sur ce produit.");
// body
define("PRODUCT_BOTTOM_NOTE", "note : ces modifications seront validées par un opérateur TECHNI-CONTACT avant leur mise en ligne");

/* Add Product */
define("ADD_PRODUCT_TITLE", "Aggiungere un nuovo prodotto");
define("ADD_PRODUCT_HEAD_TITLE", "Ajout d'un nouveau produit");
define("ADD_PRODUCT_SUCCESS", "Produit enregistré avec succès. Il sera en ligne dès sa validation par un opérateur TECHNI-CONTACT");
define("ADD_PRODUCT_ERROR_MAX", "Vous avez atteint le nombre maximal " . NB_MAX . " de produits en attente de validation de création");
define("ADD_PRODUCT_ERROR", "Erreur interne lors de la création du produit");
define("ADD_PRODUCT_SUBMIT", "Sommettere la scheda prodotto");

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
define("W_PRODUCT_TITLE", "I vostri prodotti in attesa di validazione");
define("W_PRODUCT_HEAD_TITLE", "In attesa");
define("W_PRODUCT_CREATE_BLOC_TITLE", "In attesa di validazione di creazione");
define("W_PRODUCT_EDIT_BLOC_TITLE", "In attesa di validazione di modificazione");
define("W_PRODUCT_REJECTED_BLOC_TITLE", "DDomande respinte nel corso degli ultimi 15 giorni");
define("W_PRODUCT_CREATING", "Creazione");
define("W_PRODUCT_EDITING", "Modificazione");

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
define("PRODUCT_CARD_PRICE", "Prezzo");
define("PRODUCT_CARD_QUANTITY", "Quantité");
define("PRODUCT_CARD_WITHOUT_VAT", "HT");
define("PRODUCT_CARD_REF", "Riferimenti");
define("PRODUCT_CARD_REF_IDTC", "Rif. TC");
define("PRODUCT_CARD_REF_LABEL", "stesura");
define("PRODUCT_CARD_REF_UNIT", "Unit&eacute;");
define("PRODUCT_CARD_REF_PRICE_WITHOUT_VAT", "Prix HT");
define("PRODUCT_CARD_REF_QUANTITY", "Qt&eacute;");

/* Stats */
define("STATS_TITLE", "Statistiche");
define("STATS_HEAD_TITLE", "Statistiche");
define("STATS_MONTH_DETAIL", "Détail du mois");
define("STATS_DETAIL", "Détail");
define("STATS_OF_MONTH", "du mois");
define("STATS_OF_YEAR", "de l'année");
define("STATS_YOUR_STATS_BLOC_TITLE", "Le vostre statistiche");
define("STATS_YOUR_STATS_BLOC_DESC", "Ecco le statistiche di consultazione delle vostre schede prodotti sul sito <strong>TECHNI-CONTACT</strong>.<br/>
    Cliccate su un mese per affinare i vostri risultati.");
define("STATS_CONFIG", "Parametro");
define("STATS_YEAR", "anno");
define("STATS_MONTH", "mese");
define("STATS_EVERY_MONTH", "Tutti i mesi");
define("STATS_ALL_PRODUCTS", "Tutti i prodotti");
define("STATS_STATS_BLOC_TITLE_", "Statistiche");
define("STATS_GLOBAL", "generali");
define("STATS_OF_PRODUCT", "du produit");
define("STATS_PRODUCTS_STATS", "Statistiche prodotti");

/* Infos */
define("INFOS_TITLE", "Dati personali");
define("INFOS_HEAD_TITLE", "Aggiornamento dei vostri dati");
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

define("INFOS_BLOC_TITLE", "Modificazione dei vostri dati");
define("INFOS_BLOC_DESC", "Questa pagina vi permette di modificare i dati della vostra impresa e di gestire le informazioni legate al vostro conto extranet TECHNI-CONTACT.");
define("INFOS_BLOC_NOTE", "Note : une mise à jour de vos coordonnées est actuellement en attente de validation Techni-Contact. Une nouvelle mise à jour de votre part remplacera la précédente.");
define("INFOS_ERROR", "Une ou plusieurs erreurs sont survenues lors de la validation");

define("INFOS_COMPANY_BLOC", "Dati della vostra società");
define("INFOS_COMPANY_NAME", "Nome dell' impresa");

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

define("INFOS_ADVERTISER_CONTACT_BLOC", "Relazioni inserzionisti");
define("INFOS_ADVERTISER_CONTACT", "Persona da contattare");
define("INFOS_ADVERTISER_CONTACT_DESC", "Scrivete qui il nome e il cognome della persona da contattare");
define("INFOS_ADVERTISER_EMAIL_DESC", "Scrivete qui l' indirizzo email della da contattare Quest' indirizzo email è legato al vostro conto extranet (invio d' allarmi, password persa..)");

define("INFOS_EXTRANET_BLOC", "Configurazione accesso extranet");
define("INFOS_EXTRANET_LOGIN", "LOGIN Extranet");
define("INFOS_EXTRANET_LOGIN_DESC", "Si tratta del login utilizzato per collegarvi a quest'extranet privato. Per difetto, si tratta del nome della vostra società (senza caratteri speciali).");
define("INFOS_EXTRANET_PASS", "Nuova Password");
define("INFOS_EXTRANET_PASS_DESC", "Lasciate questa zona vuota se non desiderate cambiare la vostra password.");
define("INFOS_EXTRANET_PASS_CHECK", "Confirmazione Password");
define("INFOS_EXTRANET_PASS_CHECK_DESC", "Se avete definito una nuova password, confermate scrivandola di nuovo qui.");
define("INFOS_BUTTON_VALIDATE", "Convalidare le mie modificazioni");
define("INFOS_BOTTOM_NOTE", "nota: queste modificazione saranno effettive dopo validazione da parte di un operatore Techni-Contact");

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
define("COMMON_CONTACT_TYPE", "Tipo di domanda");
define("COMMON_CONTACT_TYPE_ASK", "Demande d'informations");
define("COMMON_CONTACT_TYPE_TEL", "Demande de contact téléphonique");
define("COMMON_CONTACT_TYPE_ESTIMATE", "Demande de devis");
define("COMMON_CONTACT_TYPE_APPOINTMENT", "Demande de rendez-vous");
define("COMMON_NO_RESULT", "Nessun risultato");
define("COMMON_SEARCH", "Ricerca");
define("COMMON_BUTTON_VALIDATE", "Entra");
define("COMMON_PRODUCT", "Prodotto");
define("COMMON_DATE", "Data");
define("COMMON_TYPE", "Tipo");
define("COMMON_MOTIVE", "Motif");
define("COMMON_CATEGORY", "Categoria");
define("COMMON_PRODUCT_NAME", "Nome del prodotto");
define("COMMON_PRICE_TYPE_ON_DEMAND", "Su domanda");
define("COMMON_PRICE_TYPE_ON_ESTIMATE", "Su preventivo");
define("COMMON_PRICE_TYPE_CONTACT_US", "Contattarci");
define("COMMON_PRICE_TYPE_REFS", "Références");
define("COMMON_PRICE_TYPE_SIMPLE_PRICE", "Prezzo semplice");
define("COMMON_ERROR_VALIDATE", "Une ou plusieurs erreurs sont survenues lors de la validation");
define("COMMON_ASK_TEL_CONTACT", "demander un contact t&eacute;l&eacute;phonique");
define("COMMON_ASK_ESTIMATE", "demander un devis");
define("COMMON_GET_INFOS", "obtenir des informations");
define("COMMON_ASK_APPOINTMENT", "demander un rendez-vous");
define("COMMON_APPLY", "Applicare");

define("COMMON_JANUARY", "gennaio");
define("COMMON_FEBRUARY", "febbraio");
define("COMMON_MARCH", "marzo");
define("COMMON_APRIL", "aprile");
define("COMMON_MAY", "maggio");
define("COMMON_JUNE", "giugno");
define("COMMON_JULY", "luglio");
define("COMMON_AUGUST", "agosto");
define("COMMON_SEPTEMBER", "settembre ");
define("COMMON_OCTOBER", "ottobre");
define("COMMON_NOVEMBER", "novembre");
define("COMMON_DECEMBER", "dicembre");
define("COMMON_JAN", "gen");
define("COMMON_FEB", "feb");
define("COMMON_MAR", "mar");
define("COMMON_APR", "apr");
define("COMMON_MAY", "mag");
define("COMMON_JUN", "giu");
define("COMMON_JUL", "lug");
define("COMMON_AUG", "ago");
define("COMMON_SEP", "set");
define("COMMON_OCT", "ott");
define("COMMON_NOV", "nov");
define("COMMON_DEC", "dic");

define("INFOS_LAST_NAME", "Cognome");
define("INFOS_FIRST_NAME", "Nome");
define("INFOS_JOB", "Funzione");
define("INFOS_EMAIL", "Email");
define("INFOS_EMAIL_ADDRESS", "Indirizzo email");
define("INFOS_ADDRESS", "Indirizzo");
define("INFOS_COMPLEMENT", "Complemento d'indirizzo");
define("INFOS_CITY", "Città");
define("INFOS_PC", "Codice Postale");
define("INFOS_COUNTRY", "Paese");
define("INFOS_TEL1", "Telefono");
define("INFOS_TEL2", "Telefono 2");
define("INFOS_FAX1", "FAX");
define("INFOS_FAX2", "FAX 2");
define("INFOS_URL", "Sito internet");
define("INFOS_NUMBER_OF_EMPLOYEES", "Numero di lavoratori");
define("INFOS_ACTIVITY_SECTOR", "Settore d'attività");
define("INFOS_NAF_CODE", "Codice NAF");
define("INFOS_SIREN_NUMBER", "SIRET");

?>