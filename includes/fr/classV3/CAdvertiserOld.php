<?php


session_name('manager');
session_start();

define('CONTRIB',         0);
define('COMM',            1);
define('COMMADMIN',       2);
define('HOOK_NETWORK',    4);

require_once(ADMIN."logs.php");

class AdvertiserOld extends BaseObject {

  protected $IdMax = 999999999;
  protected $permissions = null;

	protected static $_tables = array(
		array(
			"name" => "advertisers",
			"key" => "id",
			"fields" => array(
				"id" => 0,
				"idCommercial" => 0,
				"timestamp" => 0,
				"nom1" => "",
				"nom2" => "",
				"adresse1" => "",
				"adresse2" => "",
				"ville" => "",
				"cp" => "",
				"pays" => "",
				"delai_livraison" => "",
				"prixPublic" => 0,
				"margeRemise" => 0.0,
				"peuChangerTaux" => 0,
				"arrondi" => 1,
				"idTVA" => 1,
				"contraintePrix" => 0.0,
				"contact" => "",
				"email" => "",
				"url" => "",
				"tel1" => "",
				"tel2" => "",
				"fax1" => "",
				"fax2" => "",
				"pcontact" => "",
				"ncontact" => "",
				"econtact" => "",
				"critere" => 1,
				"typecout" => 0,
				"debfacturation" => "0000-00-00",
				"cout" => 0.0,
				"finabonnement" => "0000-00-00",
				"actif" => 1,
				"ref_name" => "",
				"parent" => 0,
				"category" => 0,
				"create_time" => 0,
				"contacts" => "",
				"from_web" => 0,
				"cc_foreign" => 1,
				"cc_intern" => 1,
				"cc_noPrivate" => 0,
				"mod_prices" => "",
				"help_show" => 0,
				"help_msg" => "",
				"show_infos_online" => 0,
				"shipping_fee" => "",
				"warranty" => "",
				"catalog_code" => "",
				"notRequiredFields" => "",
				"customFields" => "",
				"noLeads2in" => "",
				"noLeads2out" => "",
				"ic_reject" => "",
				"ic_active" => "",
				"ic_fields" => "",
				"ic_extranet" => "",
				"is_fields" => "",
				"noLeads2out" => "",
				"auto_reject_threshold" => "",
                                "litigation_time" => 0
			)
		),
		array(
			"name" => "extranetusers",
			"join" => "inner",
			"key" => "id",
			"fields" => array(
				"id" => 0,
				"login" => "",
				"pass" => "",
				"c" => 0,
				"webpass" => ""
			),
		)
	);

	protected static $_linkedTables = array(
		array(
			"linkname" => "advertisers",
			"name" => "advertiserslinks",
			"key" => "idAdvertiser",
			"fields" => array(
				"idAdvertiser" => 0,
				"idAdvertiserLinked" => 0
			),
		)
	);

  public static function get() {
    $args = func_get_args();
    return BaseObject::get(self::$_tables, $args);
  }

  public static function delete($id) {
    return BaseObject::delete($id, self::$_tables);
  }

  public function __construct($args = null) {
    $this->tables = self::$_tables;
	parent::__construct($args);
  }

	public function __destruct() {}



}



//
//class Advertiser extends BaseTable {
//
//	protected $IdMax = 65535;
//
//	protected static $_tables = array(
//		array(
//			"name" => "advertisers",
//			"key" => "id",
//			"fields" => array(
//				"id" => 0,
//				"idCommercial" => 0,
//				"timestamp" => 0,
//				"nom1" => "",
//				"nom2" => "",
//				"adresse1" => "",
//				"adresse2" => "",
//				"ville" => "",
//				"cp" => "",
//				"pays" => "",
//				"delai_livraison" => "",
//				"prixPublic" => 0,
//				"margeRemise" => 0.0,
//				"peuChangerTaux" => 0,
//				"arrondi" => 1,
//				"idTVA" => 1,
//				"contraintePrix" => 0.0,
//				"contact" => "",
//				"email" => "",
//				"url" => "",
//				"tel1" => "",
//				"tel2" => "",
//				"fax1" => "",
//				"fax2" => "",
//				"pcontact" => "",
//				"ncontact" => "",
//				"econtact" => "",
//				"critere" => 1,
//				"typecout" => 0,
//				"debfacturation" => "0000-00-00",
//				"cout" => 0.0,
//				"finabonnement" => "0000-00-00",
//				"actif" => 1,
//				"ref_name" => "",
//				"parent" => 0,
//				"category" => 0,
//				"create_time" => 0,
//				"contacts" => "",
//				"from_web" => 0,
//				"cc_foreign" => 1,
//				"cc_intern" => 1,
//				"cc_noPrivate" => 0,
//				"mod_prices" => "",
//				"help_show" => 0,
//				"help_msg" => "",
//				"show_infos_online" => 0,
//				"shipping_fee" => "",
//				"warranty" => "",
//				"catalog_code" => "",
//				"notRequiredFields" => "",
//				"customFields" => ""
//			)
//		),
//		array(
//			"name" => "extranetusers",
//			"join" => "inner",
//			"key" => "id2",
//			"fields" => array(
//				"id2" => 0,
//				"login" => "",
//				"pass" => "",
//				"c" => 0,
//				"webpass" => ""
//			),
//		)
//	);
//
//	protected static $_linkedTables = array(
//		array(
//			"linkname" => "advertisers",
//			"name" => "advertiserslinks",
//			"key" => "idAdvertiser",
//			"fields" => array(
//				"idAdvertiser" => 0,
//				"idAdvertiserLinked" => 0
//			),
//		)
//	);
//
//
//	// Fields w/ default values in table advertisers : usefull to create/load/save
//	public static function get() {
//		$arg_list = func_get_args();
//		return parent::get(self::$_tables, $arg_list);
//	}
//
//	public static function delete($id) {
//		return parent::delete($id, self::$_tables, self::$_linkedTables);
//	}
//
//	public function __construct($id = null) {
//		$this->tables = self::$_tables;
//		$this->linkedTables = self::$_linkedTables;
//		parent::__construct($id);
//	}
//
//	/*public function __destruct() {
//		parent::__destruct();
//	}*/
//
//	public function save() {
//		if (!$this->altered)
//			return false;
//		$this->timestamp = time();
//		if (!$this->existsInDB) {
//			$this->create_time = time();
//			$this->login = str_replace(" ", "", strtolower($this->nom1));
//			$this->pass = rand(100000, 999999);
//
//			$webpass = "";
//			$all = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
//			$len = strlen($all);
//			for ($i = 0; $i < 32; $i++)
//				$webpass .= $all[mt_rand(0,$len)];
//			$this->webpass = $webpass;
//		}
//		parent::save();
//		return true;
//	}
//
//
//}

?>
