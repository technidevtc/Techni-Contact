<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
	$o["error"] = "Votre session a expirée, veuillez vous vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$db = DBHandle::get_instance();
$o = array();
switch($_GET["action"]) {
	case "set":
		if (!$user->get_permissions()->has("m-admin--sm-contact-form","e")) {
      $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		if (!isset($_GET["value0"])) { $o["error"] = "At least one valid value is needed"; break; }
		
		$i = 0; $o["data"] = array();
		$offset = $_GET["value0"];
		while (isset($_GET["value".$i])) {
			if (!isset($_GET["text".$i]) || !isset($_GET["emails".$i])) break;
			$emails = explode(",", $_GET["emails".$i]);
			$emails_f = array();
			foreach($emails as $email) {
				$email = trim($email);
				if (preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $email))
					$emails_f[] = $email;
			}
			$o["data"][$i+$offset] = array("text" => $_GET["text".$i], "emails" => implode(",",$emails_f));
			$i++;
		}
		
		if (empty($o["data"])) { $o["error"] = "No valid data found"; break; }
		
		$dom = new DomDocument("1.0", "utf-8");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->load(XML_FORM_CONTENT);
		$xPath = new DOMXPath($dom);
		$form = $xPath->query("//forms/form[attribute::name=\"main_contact\"]");
		if ($form->length > 0) {
			$form = $form->item(0);
			$subjects = $xPath->query("child::subjects", $form);
			if ($subjects->length == 0) {
				$subjects = $dom->createElement("subjects");
				$form->appendChild($subjects);
			}
			else {
				$subjects = $subjects->item(0);
				$subjects->nodeValue = "";
			}
			foreach($o["data"] as $value => $text_emails) {
				$option = $dom->createElement("option");
				$option->setAttribute("value", utf8_encode($value));
				$option->setAttribute("text", utf8_encode($text_emails["text"]));
				$option->setAttribute("emails", utf8_encode($text_emails["emails"]));
				$subjects->appendChild($option);
			}
			$dom->save(XML_FORM_CONTENT);
		}
		else {
			$o["error"] = "XML file is not valid : changes couldn't be saved";
		}

		break;
		
	default:
		$o["error"] = "Action type missing";
		break;
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>