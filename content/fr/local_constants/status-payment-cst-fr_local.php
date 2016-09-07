<?php
/**** Status and payment constants (manager/online store/extranet) ****/
define("PAYMENT_MEAN_CB", "Carte Bancaire");
define("PAYMENT_MEAN_CB_TYPE_WAITING", "type en attente");
define("PAYMENT_MEAN_CB_CREDIT_CARD", "Carte Bleue");
define("PAYMENT_MEAN_CB_VISA", "Visa");
define("PAYMENT_MEAN_CB_AMERICAN_EXPRESS", "American Express");
define("PAYMENT_MEAN_CB_MASTERCARD", "Mastercard");
define("PAYMENT_MEAN_CHEQUE", "Ch�que");
define("PAYMENT_MEAN_CREDIT_TRANSFER", "Virement bancaire");
define("PAYMENT_MEAN_RECORDED_PAYMENT", "Paiement diff�r�");
define("PAYMENT_MEAN_CASH_ON_DELIVERY", "Contre-remboursement");
define("PAYMENT_MEAN_ADMINISTRATIVE_ORDER", "Mandat administratif");

define("STATUS_PAYMENT_BNP_CONFIRMATION_IN_ABEYANCE", "Attente confirmation BNP");
define("STATUS_PAYMENT_CHEQUE_IN_ABEYANCE", "Attente ch�que");
define("STATUS_PAYMENT_TRANSFER_IN_ABEYANCE", "Attente virement");
define("STATUS_PAYMENT_RECORDED_PAYMENT_TO_VALIDATE", "Paiement diff�r� � valider");
define("STATUS_PAYMENT_CASH_ON_DELIVERY_PAYMENT_TO_VALIDATE", "Paiement par contre-remboursement � valider");
define("STATUS_PAYMENT_ADMINISTRATIVE_ORDER_TO_VALIDATE", "Paiement par mandat administratif � valider");
define("STATUS_PAYMENT_PAID", "Pay�");
define("STATUS_PAYMENT_RECORDED_PAYMENT_VALIDATED", "Paiement diff�r� valid�");

define("STATUS_SUPPLIER_ORDER_PAYMENT_VALIDATION_IN_ABEYANCE", "Attente validation paiement");
define("STATUS_SUPPLIER_ORDER_RECEIVED_BUT_NOT_CONSULTED", "Commande re�ue non consult�e");
define("STATUS_SUPPLIER_ORDER_IN_PROCESS", "Commande en cours de traitement");
define("STATUS_SUPPLIER_ORDER_DISPATCHED", "Commande exp�di�e");

define("STATUS_CUSTOMER_ORDER_PAYMENT_VALIDATION_IN_ABEYANCE", "Attente validation paiement");
define("STATUS_CUSTOMER_ORDER_PROCESS_IN_ABEYANCE", "Commande en attente de traitement");
define("STATUS_CUSTOMER_ORDER_IN_PROCESS", "Commande en cours de traitement");
define("STATUS_CUSTOMER_ORDER_PARTLY_DISPATCHED", "Commande partiellement exp�di�e");
define("STATUS_CUSTOMER_ORDER_ENTIRELY_DISATCHED", "Commande exp�di�e");


?>