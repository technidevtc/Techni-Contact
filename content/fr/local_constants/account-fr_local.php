<?php
/**** Secure/compte ****/
/* Common account */
define("ACCOUNT_COMMON_BACK_TO_ACCOUNT", "Retourner � mon compte");
define("ACCOUNT_COMMON_BACK_TO_IDENTIFICATION", "Retourner � la page d'identification");

/* compte */
define("ACCOUNT_MAIN_YOUR_ACCOUNT", "Votre compte utilisateur");
define("ACCOUNT_MAIN_WELCOME", "Bienvenue sur votre espace personnel");
define("ACCOUNT_MAIN_YOUR_ACCOUNT_NUMBER", "Votre num�ro client Techni-Contact");
define("ACCOUNT_MAIN_CUSTOMER_SINCE", "client depuis le");
define("ACCOUNT_MAIN_DESC_ACTIONS", "A partir de cette page, vous pouvez consulter et modifier toutes les donn�es de votre compte.");
define("ACCOUNT_MAIN_CHANGE_COORD", "Changer vos coordonn�es");
define("ACCOUNT_MAIN_CHANGE_PASS", "Changer votre mot de passe");
define("ACCOUNT_MAIN_YOUR_ESTIMATES", "Vos devis");
define("ACCOUNT_MAIN_YOUR_COMMANDS", "Vos commandes");
define("ACCOUNT_MAIN_LOGOUT", "Se d�connecter");

/* compte_coord */
define("ACCOUNT_COORD_ERROR_UPDATE", "- Erreur inattendue lors de la mise � jour de vos coordonn�es, l'id client n'existe pas");
define("ACCOUNT_COORD_ERROR_LOAD", "- Erreur inattendue lors du chargement de vos coordonn�es, l'id client n'existe pas");
define("ACCOUNT_COORD_SHIPPING_DIFFERENT", "Mon adresse de livraison est diff�rente de mon adresse de facturation");

/* compte_pass */
define("ACCOUNT_PASS_NOT_VALID", "- Le nouveau mot de passe saisie n'est pas valide. Vous devez saisir un mot de passe contenant entre 8 et 12 lettres ou chiffres");
define("ACCOUNT_PASS_CHECK_DIFFERENT", "- Le nouveau mot de passe et sa v�rification ne sont pas identique");
define("ACCOUNT_PASS_OLDPASS_NOT_VALID", "- Vous n'avez pas saisi le bon ancien mot de passe");
define("ACCOUNT_PASS_CHANGE_YOUR_PASS", "Changer votre mot de passe");
define("ACCOUNT_PASS_CHANGE_PASS_SUCCESSFUL", "Changement de votre mot de passe effectu� avec succ�s.");
define("ACCOUNT_PASS_CHANGE_MY_PASS", "Changer mon mot de passe");
define("ACCOUNT_PASS_YOUR_OLD_PASS", "Votre ancien mot de passe");
define("ACCOUNT_PASS_YOUR_NEW_PASS", "Votre nouveau mot de passe");
define("ACCOUNT_PASS_CHECK_PASS", "V�rification mot de passe");

/* compte_liste_devis */
define("ACCOUNT_ESTIMATE_LIST_YOUR_ESTIMATES", "Vos devis");
define("ACCOUNT_ESTIMATE_LIST_YOUR_RECORDED_ESTIMATES", "Voici la liste de vos devis enregistr�s");
define("ACCOUNT_ESTIMATE_LIST_NO_ESTIMATE", "Vous n'avez aucun devis d'enregistr�.");

/* compte_liste_commandes */
define("ACCOUNT_COMMAND_LIST_YOUR_COMMANDS", "Vos commandes");
define("ACCOUNT_COMMAND_LIST_YOUR_RECORDED_COMMANDS", "Voici la liste de vos commandes enregistr�es");
define("ACCOUNT_COMMAND_LIST_NO_COMMAND", "Vous n'avez aucune commande d'enregistr�e.");

/* devis */
define("ACCOUNT_ESTIMATE_ERROR_UPDATE_QUANTITY", "- Erreur lors de la mise � jour des quantit�s de produits : un doublon est pr�sent.<br/>");
define("ACCOUNT_ESTIMATE_YOUR_ESTIMATE_NUM", "Votre devis N�");
define("ACCOUNT_ESTIMATE_LAST_EDTITION", "Derni�re �dition le");
define("ACCOUNT_ESTIMATE_GENERATE_PDF", "G�n�rer le fichier PDF");
define("ACCOUNT_ESTIMATE_PLACE_ORDER", "Passer commande");
define("ACCOUNT_ESTIMATE_GO_TO_ESTIMATE_LIST", "Aller � la liste des devis");

/* compte_commande */
define("ACCOUNT_COMMAND_YOUR_COMMAND_NUM", "Votre commande N�");
define("ACCOUNT_COMMAND_ORDERED_THE", "Pass�e le");
define("ACCOUNT_COMMAND_GENERATE_PDF", "G�n�rer la facture en PDF");
define("ACCOUNT_COMMAND_GENERATE_ORDER_FORM", "G�n�rer le bon de commande");
define("ACCOUNT_COMMAND_GO_TO_COMMAND_LIST", "Aller � la liste des commandes");

/* creer_compte */
define("ACCOUNT_CREATE_ERROR_LOGIN_NOT_VALID", "- Login/Adresse email invalide");
define("ACCOUNT_CREATE_ERROR_LOGIN_EXIST", "- Un compte client ayant cette adresse email existe d�j�. <a href=\\"lost.html\\">Cliquez ici</a> si vous avez oubli� votre mot de passe");
define("ACCOUNT_CREATE_ERROR_PASS_NOT_VALID", "- Le mot de passe saisie n'est pas valide. Vous devez saisir un mot de passe contenant entre 8 et 12 lettres ou chiffres");
define("ACCOUNT_CREATE_ERROR_PASS_CHECK_DIFFERENT", "- Le mot de passe et sa v�rification ne sont pas identique");
define("ACCOUNT_CREATE_MAIL_SUBJECT", "Confirmation d'inscription");
define("ACCOUNT_CREATE_MAIL_TITLE", "Confirmation d'inscription");
define("ACCOUNT_CREATE_ACCOUNT_CREATION", "Cr�ation compte utilisateur");
define("ACCOUNT_CREATE_ERROR_ACCOUNT_CREATION", "Erreur lors de la cr�ation du compte. Si l'erreur se reproduit, veuillez contacter le webmaster.");
define("ACCOUNT_CREATE_CONTINUE_ORDER_PLACEMENT", "Continuer le processus de commande");
define("ACCOUNT_CREATE_ACCOUNT_ACCESS", "Acc�s � mon compte");
define("ACCOUNT_CREATE_YOUR_LOGIN_INFOS", "Vos identifiants de connexion");
define("ACCOUNT_CREATE_LOGIN", "Login (votre email)");
define("ACCOUNT_CREATE_PASS", "Mot de passe");
define("ACCOUNT_CREATE_PASS_CHECK", "Mot de passe (v�rification)");
define("ACCOUNT_CREATE_PASS_NOTE", "Note : Votre mot de passe doit contenir entre 8 et 12 lettes");
define("ACCOUNT_CREATE_YOUR_CONTACT_INFORMATION", "Vos coordonn�es");

/* login */
define("ACCOUNT_LOGIN_LOGIN_PASS_NOT_VALID", "Identifiant et / ou mot de passe invalide(s)");
define("ACCOUNT_LOGIN_IDENTIFICATION", "Identification");
define("ACCOUNT_LOGIN_NEW_CUSTOMER", "Nouveau client Techni-Contact ?");
define("ACCOUNT_LOGIN_CREATE_ACCOUNT", "Cr�er mon compte utilisateur");
define("ACCOUNT_LOGIN_ALREADY_CUSTOMER", "D�j� client Techni-Contact ?");
define("ACCOUNT_LOGIN_LOGIN", "Identifiant (email)");
define("ACCOUNT_LOGIN_PASS", "Mot de passe");
define("ACCOUNT_LOGIN_PASS_FORGOTTEN", "Mot de passe oubli� ?");
define("ACCOUNT_LOGIN_CLICK_HERE", "Cliquez ici");

/* lost */
define("ACCOUNT_LOST_PASS_SENDING_BACK_PROCESS", "Processus de renvoi de mot de passe");
define("ACCOUNT_LOST_SESSION_IDENTIFIER_EXPIRED", "Identifiant de session expir�.");
define("ACCOUNT_LOST_SESSION_IDENTIFIER_NOT_VALID", "Identifiant de session invalide.");
define("ACCOUNT_LOST_ERROR_EMAIL_NOT_VALID", "Merci de saisir une adresse email valide.");
define("ACCOUNT_LOST_MAIL_SUBJECT", "Mot de passe perdu");
define("ACCOUNT_LOST_MAIL_TITLE", "Mot de passe perdu");
define("ACCOUNT_LOST_MAIL_SENT", "Un e-mail vient d'�tre envoy� � l'adresse email");
define("ACCOUNT_LOST_ERROR_INTERNAL", "Une erreur interne est survenue lors de la proc�dure.");
define("ACCOUNT_LOST_ERROR_NO_USER", "Aucun compte utilisateur ne correspond aux donn�es entr�es.");
define("ACCOUNT_LOST_ENTER_YOUR_EMAIL_DESC", "Veuillez entrer dans le formulaire ci-dessous votre adresse �lectronique (qui correspond au login de votre compte client)");
define("ACCOUNT_LOST_ENTER_YOUR_EMAIL", "Entrez votre adresse e-mail");
define("ACCOUNT_LOST_EMAIL_LOST_NOTE", "<b>Note :</b> si vous avez perdu votre login, vous ne pourrez plus recevoir vos identifiants en cas d'oubli et devrez cr�er un nouveau compte (nouveau login), veillez donc � bien garder votre profil � jour.");
define("ACCOUNT_LOST_IDENTIFICATION_ACCESS", "Zugang zur Identifizierung");



?>