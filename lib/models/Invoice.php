<?php

/**
 * Invoice
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Invoice extends BaseInvoice
{
  public function setUp() {
    parent::setUp();
    $this->hasMany('InvoiceLine as lines', array(
        'local' => 'id',
        'foreign' => 'invoice_id'
      )
    );
    $this->hasOne('Clients as client', array(
        'local' => 'client_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Advertisers as main_supplier', array(
        'local' => 'main_sup_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Contacts as lead', array(
        'local' => 'lead_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Estimate as estimate', array(
        'local' => 'estimate_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Order as order', array(
        'local' => 'order_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Invoice as invoice', array(
        'local' => 'invoice_rid',
        'foreign' => 'rid'
      )
    );
    $this->hasOne('Invoice as credit_note', array(
        'local' => 'rid',
        'foreign' => 'invoice_rid'
      )
    );
    $this->hasOne('BoUsers as created_user', array(
        'local' => 'created_user_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('BoUsers as updated_user', array(
        'local' => 'updated_user_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('BoUsers as issued_user', array(
        'local' => 'issued_user_id',
        'foreign' => 'id'
      )
    );
  }
  
  public function construct() {
    $this->mapValue('stotal_ht', 0);
    $this->mapValue('total_tva', 0);
    $this->mapValue('fdp_tva', 0);
    $this->mapValue('type_text', "");
    $this->mapValue('the_type', "");
    $this->mapValue('recipients_mail_list', "");
  }
  
  public function postHydrate($event) {
    $data = $event->data;
    $data['stotal_ht'] = $data['total_ht'] - $data['fdp_ht'];
    $data['total_tva'] = $data['total_ttc'] - $data['total_ht'];
    $data['fdp_tva'] = $data['fdp_ttc'] - $data['fdp_ht'];
    $data['type_text'] = self::getTypeText($data['type']);
    $data['the_type'] = ($data['type'] == self::TYPE_INVOICE ? "la " : "l'") . strtolower(self::$typeList[$data['type']]);
    $event->data = $data;
  }
  
  public function preInsert($event) {
    global $user;
    $this->created = time();
    if ($user instanceof BoUser)
      $this->created_user_id = $user->id;
  }
  
  protected $noCascadingRelations;
  
  public function preSave($event) {
    global $user;
    
    $this->updated = time();
    
    if (empty($this->web_id))
      $this->genId('web_id');
    
    if ($user instanceof BoUser) {
      $this->updated_user_id = $user->id;
      if ($this->updated_user_id != $user->id) {
        $this->updated_user_id = $user->id;
        if (isset($this->updated_user))
          $this->refreshRelated('updated_user');
      }
    }
    
    $mv = $this->getModified(true); // modified values
    
    if ($this->status == self::STATUS_VALIDATED && isset($mv['issued']) && $mv['issued'] > 0) // do not allow issued time modification on validated invoices/credit notes
      $this->issued = $mv['issued'];
    
    // update suppliers orders when at least one of the lines was modified
    if ($this->lines->isModified()) {
      $this->updateSupplierPart();
    }
    
    // prevents from updating user account from here
    $this->noCascadingRelations = array();
    if (isset($this->created_user)) $this->noCascadingRelations[] = 'created_user';
    if (isset($this->updated_user)) $this->noCascadingRelations[] = 'updated_user';
    if (isset($this->issued_user))  $this->noCascadingRelations[] = 'issued_user';
    foreach ($this->noCascadingRelations as $relation)
      $this->clearRelated($relation);
  }
  
  public function postSave($event) {
    foreach ($this->noCascadingRelations as $relation)
      $this->refreshRelated($relation);
  }
  
  public function updateSupplierPart() {
    // compute some vars
    $so_infos = array();
    foreach ($this->lines as $line) {
      if (!isset($so_infos[$line->sup_id]))
        $so_infos[$line->sup_id] = array("total_ht" => 0, "total_a_ht" => 0, "total_a_ttc" => 0);
      $so_infos[$line->sup_id]['total_ht'] += $line->total_ht + $line->et_total_ht;
    }
    
    // set the main supplier id
    $main_sup_id = 0;
    $max_total_ht = 0;
    foreach($so_infos as $sup_id => $infos)
      if ($infos['total_ht'] > $max_total_ht)
        $main_sup_id = $sup_id;
    $this->main_sup_id = $main_sup_id;
  }
  
  public function updateWithLines($data) {
    
    // only update supplier comment
    if (!empty($data['lines'])) {
      foreach ($data['lines'] as &$line) {
        if (!empty($line['pdt_ref_id'])) {
          $line['pdt_ref']['label_long'] = $line['desc'];
        } else {
          $line['pdt_ref'] = array(
            'idProduct' => $line['pdt_id'],
            'sup_id' => $line['sup_id'],
            'label' => $line['desc'],
            'label_long' => $line['desc'],
            'refSupplier' => $line['sup_ref'],
            'price' => $line['pu_ht'],
            'price2' => $line['pau_ht'],
            'ecotax' => $line['et_ht'],
            'marge' => round(1 - $line['pau_ht'] / $line['pu_ht'],3),
            'vpc' => $line['vpc']
          );
        }
      }
    }
    
    $this->synchronizeWithArray($data);
    
    $this->calculate();
    $this->save();
    
    return $this->toArray();
  }
  
  public function calculate() {
    $hasTva = !in_array($this->activity, self::$activityNoTvaList);
    $this->stotal_ht = $this->total_tva = 0;
    foreach ($this->lines as $line) {
      
      // rounding entries to be sure
      $line->pau_ht = round($line->pau_ht, 6);
      $line->pu_ht = round($line->pu_ht, 6);
      $line->et_ht = round($line->et_ht, 6);
      $line->quantity = round($line->quantity);
      $line->total_a_ht = $line->pau_ht * $line->quantity;
      $line->total_ht_pre = $line->pu_ht * $line->quantity;
      
      $dpMul = (100-($line->discount+$line->promotion))/100;
      $tvaMul = Tva::getRate($line->tva_code)/100; // using Tva:getRate to avoid sql queries fetching the tva relation
      
      $line->total_ht = round($line->total_ht_pre * $dpMul, 6);
      $line->total_tva = $hasTva ? round($line->total_ht_pre * $tvaMul * $dpMul, 6) : 0;
      $line->total_ttc = round($line->total_ht + $line->total_tva, 6);
      $line->et_total_ht = $line->et_ht * $line->quantity;
      
      $et_total_tva = round($line->et_total_ht * $tvaMul, 6);
      
      $this->stotal_ht += $line->total_ht + $line->et_total_ht;
      $this->total_tva += $line->total_tva + $et_total_tva;
    }
    
    $this->fdp_ht = round($this->fdp_ht, 6); // rounding fdp entry
    $this->fdp_tva = $hasTva ? round($this->fdp_ht * Tva::getRate(1)/100, 6) : 0;
    $this->fdp_ttc = $this->fdp_ht + $this->fdp_tva;
    
    $this->total_ht = round($this->stotal_ht + $this->fdp_ht, 2);
    $this->total_tva = round($this->total_tva + $this->fdp_tva, 2);
    $this->total_ttc = $this->total_ht + $this->total_tva;
  }
  
  public function validate($autoSave = true, $sendMail = true, $listMailsDestinataires = null) {
    if ($this->status == self::STATUS_NOT_VALIDATED) {
      global $user;
      $latest_rid = Doctrine_Query::create()
          ->select("rid")
          ->from("Invoice")
          ->where("type = ?", $this->type)
          ->orderBy("rid DESC")
          ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
      if ($this->type == self::TYPE_INVOICE) {
        $this->status = self::STATUS_VALIDATED;
        $this->issued = time();
        switch ($this->activity) {
          case self::ACTIVITY_VPC_COMPTANT:
          case self::ACTIVITY_VPC_EXPORT_COMPTANT:
          case self::ACTIVITY_VPC_INTRA_COMPTANT:
            $this->due_date = $this->issued;
            break;
          case self::ACTIVITY_VPC_DIFFERE:
          case self::ACTIVITY_VPC_EXPORT_DIFFERE:
          case self::ACTIVITY_VPC_INTRA_DIFFERE:
          case self::ACTIVITY_ANNONCEUR:
          case self::ACTIVITY_ANNONCEUR_EXPORT:
          case self::ACTIVITY_ANNONCEUR_INTRA:
            $this->due_date = $this->issued + 29*86400; // 29 days
            break;
          default:
            $this->due_date = $this->issued;
        }
        if ($user instanceof BoUser)
          $this->issued_user_id = $user->id;
      }
      else {
        $this->status = self::STATUS_VALIDATED;
        $this->issued = time();
        if ($user instanceof BoUser)
          $this->issued_user_id = $user->id;
      }
      $this->rid = $latest_rid + 1;
      if ($autoSave)
        $this->save();
      if ($sendMail)
        $this->sendMail(self::STATUS_NOT_VALIDATED, $this->type, $listMailsDestinataires);
      return $this->toArray();
    }
  }
  
  public function setMultipleRecipients($mailList = null){

      if(!empty($mailList)){
        $listDesti = explode(', ', $mailList);
        foreach($listDesti as $mailDesti)
          if(preg_match('/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/', $mailDesti))
            $testedListDesti[] = $mailDesti;
      }
      if(!empty($testedListDesti))
        $listdestimultiple = implode (', ', $testedListDesti);
      
      $listDestiMultiple = !empty($listdestimultiple) ? ', '.$listdestimultiple : '';

      $this->recipients_mail_list = $listDestiMultiple;
  }

  public function getMailSendInfos($mailType) {
    $mailSendInfos = array();
    switch (strtolower($mailType)) {
      case 'invoiceissuedmail':
        switch ($this->website_origin) {
          case 'MOB':
            $mailSendInfos['subject'] = "Votre facture Mobaneo n°".$this->rid;
            $mailSendInfos['headers'] = "From: Mobaneo - Service comptabilité <comptabilite@mobaneo.com>\r\n".
              "Reply-To: Mobaneo - Service comptabilité <comptabilite@mobaneo.com>\r\n";
            $mailSendInfos['template'] = 'user_mob-bo_invoices-invoice-issued';
            $mailSendInfos['FO_URL'] = $website_origin_url_list[WEBSITE_ORIGIN_MOBANEO];
            break;
          case 'TC':
          default:
            $mailSendInfos['subject'] = "Votre facture Techni-Contact n°".$this->rid;
            $mailSendInfos['headers'] = "From: Service comptabilité – Techni-Contact <comptabilite@techni-contact.com>\r\n".
              "Reply-To: Service comptabilité – Techni-Contact <comptabilite@techni-contact.com>\r\n";
            $mailSendInfos['template'] = 'user-bo_invoices-invoice-issued';
            $mailSendInfos['FO_URL'] = URL;
            break;
        }
        break;
      case 'creditnoteissuedmail':
        switch ($this->website_origin) {
          case 'MOB':
            $mailSendInfos['subject'] = "Votre avoir Mobaneo n°".$this->rid;
            $mailSendInfos['headers'] = "From: Mobaneo - Service comptabilité <comptabilite@mobaneo.com>\r\n".
              "Reply-To: Mobaneo - Service comptabilité <comptabilite@mobaneo.com>\r\n";
            $mailSendInfos['template'] = 'user_mob-bo_invoices-credit-note-issued';
            $mailSendInfos['FO_URL'] = $website_origin_url_list[WEBSITE_ORIGIN_MOBANEO];
            break;
          case 'TC':
          default:
            $mailSendInfos['subject'] = "Votre avoir Techni-Contact n°".$this->rid;
            $mailSendInfos['headers'] = "From: Service comptabilité – Techni-Contact <comptabilite@techni-contact.com>\r\n".
              "Reply-To: Service comptabilité – Techni-Contact <comptabilite@techni-contact.com>\r\n";
            $mailSendInfos['template'] = 'user-bo_invoices-credit-note-issued';
            $mailSendInfos['FO_URL'] = URL;
            break;
        }
        break;
    }
    return $mailSendInfos;
  }
  
  public function sendMail($status = null, $type = null, $listMailsDesti = null) {
    if (!isset($status))
      $status = $this->status;
    if (!isset($type))
      $type = $this->type;
    if ($listMailsDesti)
      $this->setMultipleRecipients ($listMailsDesti);
    if ($status == self::STATUS_NOT_VALIDATED) {
      if ($type == self::TYPE_INVOICE) {
        // invoice internal note
        $in = new InternalNotes();
        $in->id_reference = $this->id;
        $in->context = InternalNotes::INVOICE;
        $in->content = "Facture n°".$this->rid." envoyé au client le ".date("d/m/Y à H:i:s", $this->issued);
        $in->save();
        
        // order internal note
        if ($this->order_id) {
          $in = new InternalNotes();
          $in->id_reference = $this->order_id;
          $in->context = InternalNotes::CLIENT_COMMAND;
          $in->content = "Facture n°".$this->rid." envoyé au client le ".date("d/m/Y à H:i:s", $this->issued);
          $in->save();
        }
        
        // estimate internal note
        if ($this->estimate_id) {
          $in = new InternalNotes();
          $in->id_reference = $this->estimate_id;
          $in->context = InternalNotes::ESTIMATE;
          $in->content = "Facture n°".$this->rid." envoyée au client le ".date("d/m/Y à H:i:s", $this->issued);
          $in->save();
        }
      }
      elseif($type == self::TYPE_CREDIT_NOTE) {
        // invoice internal note
        $in = new InternalNotes();
        $in->id_reference = $this->id;
        $in->context = InternalNotes::INVOICE;
        $in->content = "Avoir n°".$this->rid." relatif à la commande ".$this->order_id." envoyé au client le ".date("d/m/Y à H:i:s", $this->issued);
        $in->save();
        
        // order internal note
        if ($this->order_id) {
          $in = new InternalNotes();
          $in->id_reference = $this->order_id;
          $in->context = InternalNotes::CLIENT_COMMAND;
          $in->content = "Avoir n°".$this->rid." relatif à la commande ".$this->order_id." envoyé au client le ".date("d/m/Y à H:i:s", $this->issued);
          $in->save();
        }
      }
    }
    elseif($status == self::STATUS_VALIDATED) {
      if ($type == self::TYPE_INVOICE) {
        // invoice internal note
        $in = new InternalNotes();
        $in->id_reference = $this->id;
        $in->context = InternalNotes::INVOICE;
        $in->content = "Facture n°".$this->rid." de ".$this->total_ttc." € renvoyée au client le ".date("d/m/Y à H:i:s");
        $in->save();
        
        // order internal note
        if ($this->order_id) {
          $in = new InternalNotes();
          $in->id_reference = $this->order_id;
          $in->context = InternalNotes::CLIENT_COMMAND;
          $in->content = "Facture n°".$this->rid." de ".$this->total_ttc." € renvoyée au client le ".date("d/m/Y à H:i:s");
          $in->save();
        }
        
        // estimate internal note
        if ($this->estimate_id) {
          $in = new InternalNotes();
          $in->id_reference = $this->estimate_id;
          $in->context = InternalNotes::ESTIMATE;
          $in->content = "Facture n°".$this->rid." de ".$this->total_ttc." € renvoyée au client le ".date("d/m/Y à H:i:s");
          $in->save();
        }
      }
      elseif($type == self::TYPE_CREDIT_NOTE) {
        // invoice internal note
        $in = new InternalNotes();
        $in->id_reference = $this->id;
        $in->context = InternalNotes::INVOICE;
        $in->content = "Avoir n°".$this->rid." de ".$this->total_ttc." € renvoyé au client le ".date("d/m/Y à H:i:s");
        $in->save();
        
        // order internal note
        if ($this->order_id) {
          $in = new InternalNotes();
          $in->id_reference = $this->order_id;
          $in->context = InternalNotes::CLIENT_COMMAND;
          $in->content = "Avoir n°".$this->rid." de ".$this->total_ttc." € renvoyé au client le ".date("d/m/Y à H:i:s");
          $in->save();
        }
      }
    }
    if ($type == self::TYPE_INVOICE) {
      //Changes on 08/12/214
      //Test the invoice > activity to show the sentence or not !
      if (strcmp($this->activity,'10') == 0 || strcmp($this->activity,'11') == 0 || strcmp($this->activity,'12') == 0 || strcmp($this->activity,'20') == 0 || strcmp($this->activity,'21') == 0 || strcmp($this->activity,'30') == 0 || strcmp($this->activity,'255') == 0) {
        $invoice_due_message_first_message = "";
      } else {
        $invoice_due_message_first_message ="<br/>Nous avons le plaisir de vous vous informer que votre commande est en cours de traitement.<br />";
      }
      $mailSendInfos = $this->getMailSendInfos('InvoiceIssuedMail');
      $mail = new Email(array(
        'email' => $this->email.$this->recipients_mail_list,
        'subject' => $mailSendInfos['subject'],
        'headers' => $mailSendInfos['headers'],
        'template' => $mailSendInfos['template'],
        'data' => array(
          'FO_URL' => $mailSendInfos['FO_URL'],
          'CLIENT_FIRSTNAME' => $this->prenom,
          'CLIENT_LASTNAME' => $this->nom,
          'INVOICE_ID' => $this->rid,
          'INVOICE_TOTAL_TTC' => $this->total_ttc,
          'INVOICE_PDF_LINK' => URL."pdf/facture/".$this->web_id,
          'INVOICE_DUE_MESSAGE_FIRST_MESSAGE' => $invoice_due_message_first_message,
          'INVOICE_DUE_MESSAGE' => $this->activity == self::ACTIVITY_VPC_COMPTANT || $this->activity == self::ACTIVITY_VPC_EXPORT_COMPTANT ? "Cette facture a bien été réglée." : "Pour rappel, cette facture est payable au ".date("d/m/Y", $this->due_date)
        )
      ));
      $web_id = $this->web_id;
      $type = $this->type;
      require WWW_PATH.'pdf/invoice.php';
      $mail->addAttachment(PDF_INVOICE.self::getTypeText($this->type)." ".$this->rid.".pdf");
      $mail->send();
    }
    elseif($type == self::TYPE_CREDIT_NOTE) {
      $mailSendInfos = $this->getMailSendInfos('CreditNoteIssuedMail');
      $mail = new Email(array(
        'email' => $this->email.$this->recipients_mail_list,
        'subject' => $mailSendInfos['subject'],
        'headers' => $mailSendInfos['headers'],
        'template' => $mailSendInfos['template'],
        'data' => array(
          'FO_URL' => $mailSendInfos['FO_URL'],
          'CLIENT_FIRSTNAME' => $this->prenom,
          'CLIENT_LASTNAME' => $this->nom,
          'CREDIT_NOTE_ID' => $this->rid,
          'INVOICE_ID' => $this->invoice->rid,
          'CREDIT_NOTE_PDF_LINK' => URL."pdf/avoir/".$this->web_id
        )
      ));
      $web_id = $this->web_id;
      $type = $this->type;
      require WWW_PATH.'pdf/invoice.php';
      $mail->addAttachment(PDF_INVOICE.self::getTypeText($this->type)." ".$this->rid.".pdf");
      $mail->send();
    }
    return 1;
  }
  
  public function importFromClient($client_id = null) {
    $this->client_id = $client_id ? $client_id : $this->client_id;
    $this->refreshRelated('client');
    if (!isset($this->client))
      return false;
    
    $c = $this->client;
    $this->titre = $c->titre;
    $this->nom = $c->nom;
    $this->prenom = $c->prenom;
    $this->societe = $c->societe;
    $this->adresse = $c->adresse;
    $this->cadresse = $c->complement;
    $this->cp = $c->cp;
    $this->ville = $c->ville;
    $this->pays = $c->pays;
    $this->tel = $c->tel1;
    $this->fax = $c->fax1;
    $this->tva_intra = $c->tva_intra;
    $this->salaries = $c->nb_salarie;
    $this->secteur = $c->secteur_activite;
    $this->qualification = $c->secteur_qualifie;
    $this->naf = $c->code_naf;
    $this->siret = $c->num_siret;
    $this->email = $c->email;
    $this->url = $c->url;
    $this->delivery_infos = $c->infos_sup;
    $this->website_origin = $c->website_origin;
    if ($c->coord_livraison == 0) {
      $this->titre2 = $this->titre;
      $this->nom2 = $this->nom;
      $this->prenom2 = $this->prenom;
      $this->societe2 = $this->societe;
      $this->adresse2 = $this->adresse;
      $this->cadresse2 = $this->cadresse;
      $this->ville2 = $this->ville;
      $this->cp2 = $this->cp;
      $this->pays2 = $this->pays;
      $this->tel2 = $this->tel;
      $this->fax2 = $this->fax;
    }
    else {
      $this->titre2 = $c->titre_l;
      $this->nom2 = $c->nom_l;
      $this->prenom2 = $c->prenom_l;
      $this->societe2 = $c->societe_l;
      $this->adresse2 = $c->adresse_l;
      $this->cadresse2 = $c->complement_l;
      $this->cp2 = $c->cp_l;
      $this->ville2 = $c->ville_l;
      $this->pays2 = $c->pays_l;
      $this->tel2 = $c->tel2;
      $this->fax2 = $c->fax2;
    }
    $this->code = $c->code;
    
    return $this->client_id;
  }
  
  public function generateCreditNote($dataArray = null) {
    if ($this->type == self::TYPE_INVOICE && $this->status == self::STATUS_VALIDATED) {
      if (!$dataArray)
        $dataArray = $this->getNoIdData();
        
      $cn = new Invoice();
      $cn->fromArray($dataArray);
      $cn->status = self::STATUS_NOT_VALIDATED;
      $cn->type = self::TYPE_CREDIT_NOTE;
      $cn->rid = 0;
      $cn->web_id = '';
      $cn->issued = 0;
      $cn->issued_user_id = 0;
      $cn->due_date = 0;
      $cn->waiting_info_status = 0;
      $cn->invoice_rid = $this->rid;
      $cn->save();
      
      return $cn->id;
    }
    return 0;
  }
  
  public function autoSetClientCode() {
    if ($this->payment_mean == self::PAYMENT_MEAN_PAYPAL)
      $this->code = "9PAYPAL";
    else
      $this->code = $this->client->code;
  }
  
  private function getNoIdData() {
    if (!isset($this->lines))
      $this->refreshRelated('lines');
    
    $dataArray = $this->toArray();
    // deleting id's
    unset($dataArray['id']);
    foreach($dataArray['lines'] as &$line) {
      unset($line['id'], $line['invoice_id']);
    }
    
    return $dataArray;
  }
  
  const TYPE_INVOICE = 0;
  const TYPE_CREDIT_NOTE = 1;
  public static $typeList = array(
    self::TYPE_INVOICE => "Facture",
    self::TYPE_CREDIT_NOTE => "Avoir"
  );
  public static function getTypeText($const) {
    return isset(self::$typeList[$const]) ? self::$typeList[$const] : "";
  }
  
  const STATUS_NOT_VALIDATED = 0;
  const STATUS_VALIDATED = 1;
  public static $statusList = array(
    self::STATUS_NOT_VALIDATED => "Non validé",
    self::STATUS_VALIDATED => "Validé"
  );
  public static function getStatusText($const) {
    return isset(self::$statusList[$const]) ? self::$statusList[$const] : "";
  }
  
  const ACTIVITY_VPC_COMPTANT = 0;
  const ACTIVITY_VPC_DIFFERE = 1;
  const ACTIVITY_VPC_EXPORT_COMPTANT = 2;
  const ACTIVITY_VPC_EXPORT_DIFFERE = 3;
  const ACTIVITY_VPC_INTRA_COMPTANT = 4;
  const ACTIVITY_VPC_INTRA_DIFFERE = 5;
  const ACTIVITY_ANNONCEUR = 10;
  const ACTIVITY_ANNONCEUR_EXPORT = 11;
  const ACTIVITY_ANNONCEUR_INTRA = 12;
  const ACTIVITY_CATALOG = 20;
  const ACTIVITY_CATALOG_INTRA = 21;
  const ACTIVITY_LOCATION_FICHIERS = 30;
  const ACTIVITY_LOCATION_BANNIERE_PUB = 255;
  public static $activityList = array(
    self::ACTIVITY_VPC_COMPTANT => "VPC comptant",
    self::ACTIVITY_VPC_DIFFERE => "VPC différé",
    self::ACTIVITY_VPC_EXPORT_COMPTANT => "VPC export comptant",
    self::ACTIVITY_VPC_EXPORT_DIFFERE => "VPC export différé",
    self::ACTIVITY_VPC_INTRA_COMPTANT => "VPC intra comptant",
    self::ACTIVITY_VPC_INTRA_DIFFERE => "VPC intra différé",
    self::ACTIVITY_ANNONCEUR => "Annonceur",
    self::ACTIVITY_ANNONCEUR_EXPORT => "Annonceur export",
    self::ACTIVITY_ANNONCEUR_INTRA => "Annonceur intra",
    self::ACTIVITY_CATALOG => "Catalogue",
    self::ACTIVITY_CATALOG_INTRA => "Catalogue intra",
    self::ACTIVITY_LOCATION_FICHIERS => "Location fichiers",
    self::ACTIVITY_LOCATION_BANNIERE_PUB => "Location bannière pub"
  );
  public static function getActivityText($const) {
    return isset(self::$activityList[$const]) ? self::$activityList[$const] : "";
  }
  public static $activityNoFdpList = array( // no fdp
    self::ACTIVITY_ANNONCEUR,
    self::ACTIVITY_ANNONCEUR_EXPORT,
    self::ACTIVITY_ANNONCEUR_INTRA,
    self::ACTIVITY_CATALOG,
    self::ACTIVITY_CATALOG_INTRA,
    self::ACTIVITY_LOCATION_FICHIERS,
    self::ACTIVITY_LOCATION_BANNIERE_PUB
  );
  public static $activityNoTvaList = array( // no tva
    self::ACTIVITY_VPC_EXPORT_COMPTANT,
    self::ACTIVITY_VPC_EXPORT_DIFFERE,
    self::ACTIVITY_VPC_INTRA_COMPTANT,
    self::ACTIVITY_VPC_INTRA_DIFFERE,
    self::ACTIVITY_ANNONCEUR_EXPORT,
    self::ACTIVITY_ANNONCEUR_INTRA,
    self::ACTIVITY_CATALOG_INTRA
  );
  public static $activityTvaIntraList = array( // tva intra
    self::ACTIVITY_VPC_INTRA_COMPTANT,
    self::ACTIVITY_VPC_INTRA_DIFFERE,
    self::ACTIVITY_ANNONCEUR_INTRA,
    self::ACTIVITY_CATALOG_INTRA
  );
  
  const PAYMENT_MEAN_BC_TBD = 0;
  const PAYMENT_MEAN_BC_CB = 1;
  const PAYMENT_MEAN_BC_VISA = 2;
  const PAYMENT_MEAN_BC_MASTERCARD = 3;
  const PAYMENT_MEAN_BC_AMEX = 4;
  const PAYMENT_MEAN_PAYPAL = 5;
  const PAYMENT_MEAN_CHEQUE = 10;
  const PAYMENT_MEAN_VIREMENT = 20;
  const PAYMENT_MEAN_DIFFERE = 30;
  const PAYMENT_MEAN_CB = 40;
  const PAYMENT_MEAN_MANDAT = 50;
  const PAYMENT_BANKER_ORDER = 60;
  const PAYMENT_BILL_OF_EXCHANGE = 70;
  public static $paymentMeanList = array(
    self::PAYMENT_MEAN_BC_TBD => "Carte Bancaire (type en attente)",
    self::PAYMENT_MEAN_BC_CB => "Carte Bancaire (Carte Bleue)",
    self::PAYMENT_MEAN_BC_VISA => "Carte Bancaire (Visa)",
    self::PAYMENT_MEAN_BC_MASTERCARD => "Carte Bancaire (Mastercard)",
    self::PAYMENT_MEAN_BC_AMEX => "Carte Bancaire (American Express)",
    self::PAYMENT_MEAN_PAYPAL => "Paypal",
    self::PAYMENT_MEAN_CHEQUE => "Chèque",
    self::PAYMENT_MEAN_VIREMENT => "Virement bancaire",
    self::PAYMENT_MEAN_DIFFERE => "Paiement différé",
    self::PAYMENT_MEAN_CB => "Contre-remboursement",
    self::PAYMENT_MEAN_MANDAT => "Mandat administratif",
    self::PAYMENT_BANKER_ORDER => "Prélèvement",
    self::PAYMENT_BILL_OF_EXCHANGE  => "Lettre de change"
  );
  public static function getPaymentMeanText($const) {
    return isset(self::$paymentMeanList[$const]) ? self::$paymentMeanList[$const] : "";
  }
  
  const PAYMENT_MODE_AT_ORDER = 1;
  const PAYMENT_MODE_30_DAYS_INVOICING = 2;
  const PAYMENT_MODE_50_ORDER_50_INVOICING = 3;
  const PAYMENT_MODE_MONEY_ORDER = 4;
  public static $paymentModeList = array(
    self::PAYMENT_MODE_AT_ORDER => "A la commande",
    self::PAYMENT_MODE_30_DAYS_INVOICING => "30 jours date de facture",
    self::PAYMENT_MODE_50_ORDER_50_INVOICING => "50% à la commande 50% à réception",
    self::PAYMENT_MODE_MONEY_ORDER => "Mandat administratif",
  );
  public static function getPaymentModeText($const) {
    return isset(self::$paymentModeList[$const]) ? self::$paymentModeList[$const] : "";
  }

}