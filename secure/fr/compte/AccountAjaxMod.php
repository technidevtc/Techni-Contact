<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . 'CUserSession.php');
require(ICLASS . 'CCustomerUser.php');

$handle = DBHandle::get_instance();
$session = & new UserSession($handle);
$user = & new CustomerUser($handle, $session->userID);

header("Content-Type: text/plain; charset=utf-8");

$o = array();
if (!$session->logged){
	$o["error"] = "Session expirée, veuillez réactualiser la page !";
}
else {
	if (isset($_GET['action'])) {
		
		switch ($_GET['action']) {
			
			case "edit" :
				if (isset($_GET['field']) && isset($_GET['data'])) {
					$field = trim($_GET['field']);
					$data = substr(trim($_GET['data']), 0, 255);
                                        $data2 = filter_input(INPUT_GET, 'data2', FILTER_SANITIZE_STRING);

					switch($field) {
						case "email" : 
							if ($data == $user->login) {
								$o["data"] = $user->login;
							}
							elseif (!preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $data)) {
								$o["error"] = "Adresse Email invalide";
							}
							elseif (CustomerUser::getCustomerIdFromLogin($data, $handle)) {
								$o["error"] = "Adresse Email déjà utilisée";
							}
							else {
								$user->login = $user->email = $data;
								$user->save();
								$o["data"] = $user->login;
							}
							break;
							
						case "pass" :
							if (!preg_match('/^[[:alnum:]]{8,12}$/', $data) || $data != $data2) {
								$o["error"] = $data != $data2 ? 'Erreur de confirmation de mot de passe' : "Mot de passe non valide";
							}
							else {
								$user->pass = md5($data);
								$user->save();
								$o["data"] = str_pad("", strlen($data), "*");
							}
							break;
							
						default :
							$o["error"] = "Erreur Fatale";
							break;
					}
				}
				else {
					$o["error"] = "Erreur Fatale";
				}
				break;
				
			default :
				$o["error"] = "Erreur Fatale";
				break;
		}
	}
	else {
		$o["error"] = "Erreur Fatale";
	}
}

print json_encode($o);
