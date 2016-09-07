<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$user = new BOUser();

if (!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}

$emailID = isset($_GET["emailID"]) ? (int)$_GET["emailID"] : 0;
if (!$emailID) {
  echo "Invalide email ID";
  exit();
}

$email_ver = isset($_GET["email_ver"]) ? (float)$_GET["email_ver"] : 1.0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Manager Techni-Contact | Emails</title>
</head>
<body>
<?php
if ($email_ver >= 2 && $email_ver < 3) {
  $mail = new Email($emailID);
  echo to_entities($mail->get_headers())."<hr/>\n".$mail->get_content();
} elseif ($email_ver >= 1 && $email_ver < 2) {
   $res = $db->query("SELECT content FROM emails_historic WHERE id = ".$emailID, __FILE__, __LINE__);
  list($content) = $db->fetch($res, __FILE__, __LINE__);
  echo $content;
}
?>
</body>
</html>