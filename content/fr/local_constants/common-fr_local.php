<?php
/**** Common ****/
define("COMMON_ERROR_UNINTENDED", "Une ou plusieurs erreurs inattendues sont survenues");
define("COMMON_ERROR_VALIDATE", "Une ou plusieurs erreurs sont survenues lors de la validation");
define("COMMON_ERROR_REPORT_US", "-> Si l'une des erreurs ci-dessus se reproduit, merci de nous contacter via email<br />");
define("COMMON_ERROR_LINE_PRODUCT_LOAD", "Erreur fatale lors du chargement du produit de la ligne n�");

define("COMMON_VALIDATE", "Valider");
define("COMMON_CANCEL", "Annuler");
define("COMMON_VALID", "valide");
define("COMMON_NOT_VALID", "non valide");
define("COMMON_RECALCULATE", "Recalculer");
define("COMMON_RESET", "R�initialiser");
define("COMMON_DELETE", "Supprimer");
define("COMMON_DEL", "Sup.");
define("COMMON_REMOVE", "Effacer");
define("COMMON_ADD_TO_CARD", "Ajouter au panier");
define("COMMON_EVAT", "HT");
define("COMMON_BUY", "Acheter");
define("COMMON_ADD", "Ajouter");
define("COMMON_WITHDRAW", "Retirer");
define("COMMON_SEND", "Envoyer");
define("COMMON_CLOSE", "Fermer");
define("COMMON_VALIDATE_DEMAND", "Valider ma demande");
define("COMMON_SEND_DEMAND", "Envoyer ma demande");

define("COMMON_ITEMS_RECAP", "R�capitulatif de vos articles");
define("COMMON_HELP_PHONE", "Besoin d'aide ? Contactez le 01 55 60 29 29");
define("COMMON_IDTC_REF", "R�f. TC");
define("COMMON_COMMMAND_NUM", "N� commande");
define("COMMON_ESTIMATE_ID", "ID du devis");
define("COMMON_CREATE_THE_M", "Cr�� le");
define("COMMON_CREATE_THE_F", "Cr��e le");
define("COMMON_MODIFIED_THE", "Modifi� le");
define("COMMON_TOTAL_WOVAT", "total HT");
//define("COMMON_TOTAL_WVAT", "total TTC");
define("COMMON_VALIDITY_STATE", "Etat validit�");
define("COMMON_STATUS", "statut");
define("COMMON_LABEL", "Libell�");
define("COMMON_DESCRIPTION", "D�signation");
define("COMMON_QTY", "Qt�.");
define("COMMON_UNIT", "Unit�");
define("COMMON_UP_EVAT", "P.U. HT");
define("COMMON_AMOUNT_EVAT", "MT HT");
define("COMMON_VAT", "Tva");
define("COMMON_EUROS_EVAT", "euros HT");
define("COMMON_UP_EURO_EVAT", "P.U. Euro HT");
define("COMMON_AMOUNT_EURO_EVAT", "MT Euro HT");
define("COMMON_EUROS", "euros");

define("COMMON_PREVIOUS_ITEMS_TOTAL_NOT_SUFFICIENT", "Le total des produits pr�c�dents n'est pas sufisant pour pouvoir passer commande");
define("COMMON_CURRENT_AMOUNT", "Montant actuel :");
define("COMMON_MINIMUM_AMOUNT", "Montant minimum :");
define("COMMON_COMMENTS", "Commentaires");
define("COMMON_PROMOTION_OF", "Promotion de");
define("COMMON_DISCOUNT_OF", "Remise de");
define("COMMON_DISCOUNT_FOR", "pour");
define("COMMON_SUBTOTAL_EVAT", "Sous-total HT :");
define("COMMON_SHIPPING_FEE_EVAT", "Frais de Port HT :");
define("COMMON_TOTAL_EVAT", "Total HT :");
define("COMMON_TOTAL_WVAT", "Total TTC :");
define("COMMON_BASE_EVAT", "Base � HT");
define("COMMON_RATE", "Taux");
define("COMMON_AMOUNT_VAT", "Montant TVA");
define("COMMON_TOTAL", "Total");

define("COMMON_NO_PRODUCT_TO_UPDATE", "Aucun produit � mettre � jour.");
define("COMMON_SOME_INVALID_QUANTITIES", "Certaines quantit�s sont invalides, impossible de mettre � jour.");

define("COMMON_PAYMENT_MEAN", "Mode de paiement");
define("COMMON_PAYMENT_STATUS", "Statut de traitement");

define("COMMON_AJAX_COMMENTS_NOT_IDENTIFIED", "Impossible de modifier le commentaire : vous n'�tes plus identifi�");
define("COMMON_AJAX_COMMENTS_CARD_EMPTY", "Impossible de modifier le commentaire : le panier est vide");
define("COMMON_AJAX_COMMENTS_ERROR_UPDATE", "Erreur fatale lors de la mise � jour du commentaire du produit n�");
define("COMMON_AJAX_COMMENTS_ERROR_EMPTY", "Erreur fatale : Aucun commentaire sp�cifi�");
define("COMMON_AJAX_COMMENTS_ERROR_IDTC", "Erreur fatale : Identifiant Techni-Contact du produit � modifier non sp�cifi�e");
define("COMMON_AJAX_SPTF_ERROR_INVALID_FRIEND_MAIL_1", "Adresse email");
define("COMMON_AJAX_SPTF_ERROR_INVALID_FRIEND_MAIL_2", "invalide");
define("COMMON_AJAX_SPTF_1_MAIL_SENT", "1 email a �t� envoy�");
define("COMMON_AJAX_SPTF_X_MAIL_SENT", "emails ont �t� envoy�s");
define("COMMON_AJAX_SPTF_X_MAIL_SENT_SUCCESSFULY", "avec succ�s");
define("COMMON_AJAX_SPTF_ERROR_LOADING_PRODUCT", "Erreur fatal lors de l'obtention des informations du produit");
define("COMMON_AJAX_SPTF_ERROR_FLOOD_PROTECTION", "Vous ne pouvez pas envoyer cette fiche produit � vos amis plus d'une fois toutes les 30s");
define("COMMON_AJAX_SPTF_ERROR_FLOOD_1S_REMAINING", "1s restante");
define("COMMON_AJAX_SPTF_ERROR_FLOOD_XS_REMAINING", "restantes");
define("COMMON_AJAX_SPTF_ERROR_FLOOD_CHECK", "Erreur fatale lors de la demande d'autorisation d'envoie des emails");
define("COMMON_AJAX_SPTF_NO_MAIL_SENT", "Aucun email n'a �t� envoy�");
define("COMMON_AJAX_SPTF_ERROR_INVALID_PRODUCT_ID", "Le num�ro identifiant produit n'est pas valide");
define("COMMON_AJAX_SPTF_ERROR_INVALID_FAMILY_ID", "Le num�ro identifiant famille n'est pas valide");
define("COMMON_AJAX_SPTF_ERROR_INVALID_OWN_MAIL", "votre adresse email n'est pas valide");
define("COMMON_AJAX_SPTF_MAIL_SUBJECT_1", "Un ami vous recommande le produit");
define("COMMON_AJAX_SPTF_MAIL_SUBJECT_2", "sur le site Techni-Contact");
define("COMMON_AJAX_SPTF_MAIL_TITLE_1", "Un ami vous recommande le produit");
define("COMMON_AJAX_SPTF_MAIL_TITLE_2", "sur le site Techni-Contact");


define("COMMON_INFORMATION_REQUEST", "Demande d'informations");
define("COMMON_PHONE_CONTACT_REQUEST", "Demande de contact t�l�phonique");
define("COMMON_ESTIMATE_REQUEST", "Demande de devis");
define("COMMON_FREE_ESTIMATE_REQUEST", "Demande de devis gratuit");
define("COMMON_APPOINTMENT_REQUEST", "Demande de rendez-vous");
define("COMMON_COMMAND_REQUEST", "Commande");
define("COMMON_REQUEST", "Demande");
define("COMMON_COMMAND", "Commande");
define("COMMON_ASK_TEL_CONTACT", "demander un contact t�l�phonique");
define("COMMON_ASK_ESTIMATE", "demander un devis");
define("COMMON_ASK_ESTIMATE_24H", "demander un devis en 24h");
define("COMMON_ASK_APPOINTMENT", "demander un rendez-vous");
define("COMMON_GET_INFOS", "obtenir des informations");


define("COMMON_PRODUCT_CARD_ESTIMATE_NEEDED", "Ce produit n�cessite une demande de devis avant toute commande");
define("COMMON_PRODUCT_CARD_DESCC", "Description du produit");
define("COMMON_PRODUCT_CARD_DESCD", "Description technique");
define("COMMON_PRODUCT_CARD_EAN", "Code EAN");
define("COMMON_PRODUCT_CARD_WARRANTY", "Garantie");
define("COMMON_PRODUCT_CARD_DELIVERY_TIME", "D�lais de livraison habituels");
define("COMMON_PRODUCT_CARD_SHIPPING_FEE", "Frais de port");
define("COMMON_PRODUCT_CARD_CONSTRAINT", "Montant minimum de commande");
define("COMMON_PRODUCT_CARD_DISCOUNT_RATE_FOR", "Taux de remise pour");
define("COMMON_PRODUCT_CARD_DISCOUNT_PRODUCTS", "produits");
define("COMMON_PRODUCT_CARD_UNIT", "Unit�");
define("COMMON_PRODUCT_CARD_PRICE", "Prix");
define("COMMON_PRODUCT_CARD_QUANTITY", "Quantit�");
define("COMMON_PRODUCT_CARD_WITHOUT_VAT", "HT");
define("COMMON_PRODUCT_CARD_REF", "R�f�rences");
define("COMMON_PRODUCT_CARD_ON_DEMAND", "sur demande");
define("COMMON_PRODUCT_CARD_UP_EVAT_FOR", "P.U. HT pour");
define("COMMON_PRODUCT_CARD_DOCS", "Documentation");

define("COMMON_PRICE_ON_DEMAND", "sur demande");
define("COMMON_PRICE_ON_ESTIMATE", "sur devis");
define("COMMON_PRICE_CONTACT_US", "nous contacter");





define("COMMON_STEPS_CARD", "Panier");
define("COMMON_STEPS_IDENTIFICATION", "Identification");
define("COMMON_STEPS_CONTACT_INFORMATION", "Coordonn�es");
define("COMMON_STEPS_RECAP", "R�capitulatif");
define("COMMON_STEPS_PAYMENT_MEAN", "Mode de paiement");
define("COMMON_STEPS_END", "FIN");

define("COMMON_JANUARY", "janvier");
define("COMMON_FEBRUARY", "f�vrier");
define("COMMON_MARCH", "mars");
define("COMMON_APRIL", "avril");
define("COMMON_MAY", "mai");
define("COMMON_JUNE", "juin");
define("COMMON_JULY", "juillet");
define("COMMON_AUGUST", "ao�t");
define("COMMON_SEPTEMBER", "septembre");
define("COMMON_OCTOBER", "octobre");
define("COMMON_NOVEMBER", "novembre");
define("COMMON_DECEMBER", "d�cembre");
define("COMMON_JAN", "jan");
define("COMMON_FEB", "fev");
define("COMMON_MAR", "mar");
define("COMMON_APR", "avr");
//define("COMMON_MAY", "mai");
define("COMMON_JUN", "juin");
define("COMMON_JUL", "juil");
define("COMMON_AUG", "aou");
define("COMMON_SEP", "sept");
define("COMMON_OCT", "oct");
define("COMMON_NOV", "nov");
define("COMMON_DEC", "dec");

define("COMMON_SEE_PDT_CARD", "Voir la fiche produit");




?>