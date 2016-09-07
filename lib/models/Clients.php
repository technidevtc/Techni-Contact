<?php

/**
 * Clients
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Clients extends BaseClients
{
  public function setUp() {
    parent::setUp();
    $this->hasMany('Contacts as leads', array(
        'local' => 'login',
        'foreign' => 'email'
      )
    );
    $this->hasMany('Estimate as estimates', array(
        'local' => 'id',
        'foreign' => 'client_id'
      )
    );
    $this->hasMany('Order as orders', array(
        'local' => 'id',
        'foreign' => 'client_id'
      )
    );
    $this->hasMany('Invoice as invoices', array(
        'local' => 'id',
        'foreign' => 'client_id'
      )
    );
    $this->hasOne('Advertisers as advertiser', array(
        'local' => 'id',
        'foreign' => 'client_id'
      )
    );
    $this->hasMany('ClientsAdresses as addresses', array(
        'local' => 'id',
        'foreign' => 'client_id'
      )
    );
    $this->hasMany('ClientsContacts as contacts', array(
        'local' => 'id',
        'foreign' => 'client_id'
      )
    );
  }
  
  public function construct() {
    $this->mapValue('titre_text', "");
  }
  
  public function postHydrate($event) {
    $data = $event->data;
    if (isset($data['email']))
      $data['email'] = empty($data['email']) ? (empty($data['login']) ? "" : $data['login']) : $data['email'];
    if (isset($data['titre']))
      $data['titre_text'] = self::getTitleText($data['titre']);
    $event->data = $data;
  }
  
  public function preSave() {
    $this->tel_match = self::getTelMatchString($this->tel1.'_'.$this->tel2);
  }
  
  public static $cbf2caf = array( // client billing fields to client address fields, usefull for code clarity
    'titre' => 'titre',
    'nom' => 'nom',
    'prenom' => 'prenom',
    'societe' => 'societe',
    'tel1' => 'tel1',
    'fax1' => 'fax1',
    'adresse' => 'adresse',
    'complement' => 'complement',
    'cp' => 'cp',
    'ville' => 'ville',
    'pays' => 'pays',
    'infos_sup' => 'infos_sup'
  );
  public static $cdf2caf = array( // client delivery fields to client address fields
    'titre_l' => 'titre',
    'nom_l' => 'nom',
    'prenom_l' => 'prenom',
    'societe_l' => 'societe',
    'tel2' => 'tel1',
    'fax2' => 'fax1',
    'adresse_l' => 'adresse',
    'complement_l' => 'complement',
    'cp_l' => 'cp',
    'ville_l' => 'ville',
    'pays_l' => 'pays',
    'infos_sup_l' => 'infos_sup'
  );

  public function setNewDefaultAddress($type = ClientsAdresses::TYPE_DELIVERY) {
    if (!count($this->addresses)) // nothing to do, should never happen
      return false;
    
    foreach ($this->addresses as $ca) {
      if ($ca->type_adresse == $type && $ca->num == 0) {
        $cca = $ca;
        break;
      }
    }
    if (!isset($cca)) {
      // this should also never happen if the function is called correctly, but just in case, search the first address of the same type
      foreach ($this->addresses as $ca) {
        if ($ca->type_adresse == $type) {
          $cca = $ca;
          break;
        }
      }
      // still nothing ? just get the first address
      if (!isset($cca))
        $cca = $this->addresses->getFirst();
      
      // make sure this one now has the num 0
      $cca->num = 0;
    }
    
  /**
   * historicaly strange herited logic :
   * - the default fields were the billing ones, but also the delivery ones when coord_livraison was at 0
   * - in this case, the "_l" fields often had the same value as the normal ones
   * - if coord_livraison was at 1, then the "_l" fields were for the delivery address, and the normal fields for the billing address
   *
   * here is the updated logic taking into account this behaviour to avoid any problem with the all the other old code :
   * 
   * if we set a new delivery address
   *   we always set the "_l" fields to the new address
   *   if coord_livraison is at 0, we set also set the normal fields to the new address
   * if we set a new billing address
   *   if coord_livraison is at 0, we copy the normal fields to the "_l" ones, set the normal ones to the new address, and set coord_livraison to 1
   *   else, if it's at 1, we set the normal fields to the new address
   */
    
    if ($type == ClientsAdresses::TYPE_DELIVERY) {
      foreach (self::$cdf2caf as $cf => $af)
        $this->$cf = $cca->$af;
      if ($this->coord_livraison == 0) {
        foreach (self::$cbf2caf as $cf => $af)
          $this->$cf = $cca->$af;
      }
    } elseif ($type == ClientsAdresses::TYPE_BILLING) {
      if ($this->coord_livraison == 0) {
        foreach (self::$cdf2caf as $cf => $af)
          $this->$cf = $this->$cf;
        foreach (self::$cbf2caf as $cf => $af)
          $this->$cf = $cca->$af;
        $this->coord_livraison = 1;
      } else {
        foreach (self::$cbf2caf as $cf => $af)
          $this->$cf = $cca->$af;
      }
    }
    
  }
  
  public function genTempAuthToken() {
    $this->genId('web_id');
    // intercalate the timestamp every 3 char with an offset of 1 in the token, just to not have it completly clear in the url
    $web_id = $this->web_id;
    $time = (string)time();
    for ($k=0, $l=strlen($time); $k<$l; $k++)
      $web_id[$k*3+1] = $time[$k];
    $this->web_id = $web_id;
  }
  
  public static function getTempAuthTokenTime($token) {
    $time = "";
    for ($k=1, $l=strlen($token); $k<$l; $k+=3)
      $time .= $token[$k];
    return (int)substr($time,0,strlen((string)time()));
  }

  const TITLE_M = 1;
  const TITLE_MME = 2;
  const TITLE_MLLE = 3;
  public static $titleList = array(
    self::TITLE_M => "M.",
    self::TITLE_MME => "Mme",
    self::TITLE_MLLE => "Mlle"
  );
  public static function getTitleText($const) {
    return isset(self::$titleList[$const]) ? self::$titleList[$const] : "";
  }
  
  public static function getTitleSelectTag($id = null){
    $html = '<select name="titre">';
    foreach (self::$titleList as $k=> $v){
      $selected = $id==$k?' selected="selected"':'';
      $html .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
    }
    $html .= '</select>';
    return $html;
  }
  
  public static function getTelMatchString($telString) {
    
    // delete '-', '.', '/', '\', '|' = sanitize probable legit numbers
    $telString = preg_replace('/[-\>\.\/\\\\|]/', '', $telString);
    
    // put a '_' after a group of at least 8 contiguous digits (not counting brackets), to avoid any further concatenation (because variable length lookbehing does not work)
    $telString = preg_replace('/(?=[\d()]{10,})(\d*\(\d*\)\d*)\s+/', '$1_', $telString); // 8+ digits with 2 brackets
    $telString = preg_replace('/(\d{8,})\s+/', '$1_', $telString); // 8+ digits only
    
    // only delete spaces between groups of less than 8 digits or brackets with a variable lookahead
    $telString = preg_replace('/(?<=[\d\(\)])\s+(?=[\d\(\)]{1,7}([^\d\(\)]|$))/', '', $telString);
    
    // protect the groups of 8+ digits again
    $telString = preg_replace('/(?=[\d()]{10,})(\d*\(\d*\)\d*)\s+/', '$1_', $telString);
    $telString = preg_replace('/(\d{8,})\s+/', '$1_', $telString);
    
    // remove any left space to capture possible 8+ digits numbers preceded by less a less than 8 digits or brackets one
    $telString = preg_replace('/\s+/', '', $telString);
    
    // capture what looks like phone numbers
    preg_match_all('/(?:(\d*)(?:\((\d+)\)))?(\d+)/', $telString, $telMatch, PREG_SET_ORDER);
    
    // in case we have something like XX(YY)XXXX, write 2 numbers : XXXXXX and YYXXXX
    // else, if the number is less than 8 digits long, ignore it
    $telList = array();
    foreach ($telMatch as $telGroup) {
      if ($telGroup[1] != "" || $telGroup[2] != "") {
        if ($telGroup[1] != "")
          $telList[] = $telGroup[1].$telGroup[3];
        if ($telGroup[2] != "")
          $telList[] = $telGroup[2].$telGroup[3];
      } elseif (strlen($telGroup[3]) >= 8) {
        $telList[] = $telGroup[3];
      }
    }
    $telMatchString = implode(' ', $telList);
    
    return $telMatchString;
  }

}