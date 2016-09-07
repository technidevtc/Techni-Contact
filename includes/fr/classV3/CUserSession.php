<?php

class UserSession
{
	
	function __construct() {
		session_name("customer");
		if (isset($_COOKIE["session_id"])) {
			session_id($_COOKIE["session_id"]);
		}
		else {
			session_regenerate_id();
		}
		session_start();
		
    if (isset($_SESSION["logged"])) {
			if (($_SESSION['loggedIP'] != $this->getUserIP() && $this->getUserIP() != SERVER_IP) || $_SESSION['loggedURL'] != URL)
				$this->logout();
			else {
				//$this->cmd_devis = isset($_SESSION['cmd_devis']) ? $_SESSION['cmd_devis'] : 0;
				// if IP has changed
				//$this->logout();
				//@session_destroy();
			}
		} elseif (isset($_GET['token']) && preg_match('`[a-z0-9]{32}`', $_GET['token'])) {
      $tokenTime = Clients::getTempAuthTokenTime($_GET['token']);
      if ($tokenTime >= time()-48*3600) { // max 48h validity
        $client_id = Doctrine_Query::create()
          ->select('id')
          ->from('Clients')
          ->where('web_id = ?', $_GET['token'])
          ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
        if (!empty($client_id))
          $this->login($client_id);
      }
    }
		
    // init tracking var
    if (!isset($_SESSION["tracking"]))
      $_SESSION["tracking"] = array();
		setCookie("session_id",  session_id(), time() + 24 * 3600 * 30, "/", DOMAIN);
	}
	
	public function __set($name, $value) {
		$_SESSION[$name] = $value;
	}

	public function __get($name) {
		if (array_key_exists($name, $_SESSION)) {
			return $_SESSION[$name];
		}
		return null;
	}
  
  public function __isset($key) {
    return isset($_SESSION[$key]);
  }
  
  public function __unset($key) {
    unset($_SESSION[$key]);
  }
	
	public function getID() {
		return session_id();
	}
	
	public function login($userID) {
		$_SESSION["logged"] = true;
		$_SESSION["loggedIP"] = $this->getUserIP();
		$_SESSION["loggedURL"] = URL;
		$_SESSION["userID"] = $userID;
    $psl = new ProductsSavedList();
    $psl->setAsLogged();
	}
	
	public function logout() {
		unset(
			$_SESSION["logged"],
			$_SESSION["loggedIP"],
			$_SESSION["loggedURL"],
			$_SESSION["userID"]);
	}
	
	public function track($page,$data) {
    if (!isset($_SESSION["tracking"][$page]))
      $_SESSION["tracking"][$page] = array();
    array_unshift($_SESSION["tracking"][$page],$data);
    if (count($_SESSION["tracking"][$page]) > 10)
      array_pop($_SESSION["tracking"][$page]);
  }

  public function seenProductAdd($pdtId) {
    if (!isset($_SESSION["seenProducts"]))
      $_SESSION["seenProducts"][] = $pdtId;
    elseif(!in_array($pdtId, $_SESSION["seenProducts"]))
      array_unshift($_SESSION["seenProducts"],$pdtId);
    if (count($_SESSION["seenProducts"]) > 15)
      array_pop($_SESSION["seenProducts"]);
  }
  /*
	public function setPageAfterLogin($page) { $_SESSION["pageAfterLogin"] = $page; }
	public function getPageAfterLogin() { return isset($_SESSION["pageAfterLogin"]) ? $_SESSION["pageAfterLogin"] : null; }
	public function setUserID($userID) { $_SESSION["userID"] = $userID; }
	public function getUserID() { return isset($_SESSION["userID"]) ? $_SESSION["userID"] : null; }
	*/
	
	public function getUserIP() {
		return $_SERVER["REMOTE_ADDR"];
	}
	
	//Change Start on 19/12/2014 
	public function criteo_get_email_from_session_id(){
		$user_email	= "";
		if(!empty($_SESSION["userID"])){
			$client_email = Doctrine_Query::create()
						  ->select('email')
						  ->from('Clients')
						  ->where('id = ?', $_SESSION["userID"])
						  ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
			if (!empty($client_email)){
				$user_email = $client_email;
			}
		}//end if
		
		return $user_email;
		
	}//end function
	
	//Change End on 19/12/2014 
}
