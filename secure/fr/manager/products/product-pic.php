<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");
require_once(ADMIN."products.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

$error = "";
if (!$user->login()) {
	$message = "Votre session a expirée";
}
else {
	$type = isset($_GET["type"]) ? $_GET["type"] : "";
	$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
	
	if (is_uploaded_file($_FILES["image"]['tmp_name'])) {
		if (empty($id) || !($data = & loadProduct($handle, $id, $type))) {
			$message = "Identifiant produit incorrect";
		}
		else {
			$message = "ok";
			if ($type == "adv") $dir = PRODUCTS_IMAGE_ADV_INC;
			else $dir = PRODUCTS_IMAGE_INC;
			
			if (uploadAndProceedImage("image", $id, $dir)) {
				$message = "Upload effectuée avec succés";
			}
			else {
				$message = "Erreur lors de la copie de l'image produit";
			}
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Manager Techni-Contact : Upload d'images produits</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style type="text/css">
body { margin: 0; padding: 0 }
form { margin: 0; padding: 0 }
.zero { clear: both }
.wrapper { height: 60px; padding: 10px; font: normal 12px arial, helvetica, sans-serif; background: #fcfcfc url(../ressources/images/block-bg-grey-150.gif) repeat-x }
.message { margin: 10px 0 0 0; font-weight: bold; font-size: 13px }
</style>
</head>
<body>
<div class="wrapper">
	<form name="product-pic" method="post" action="product-pic.php?id=<?php echo $id ?><?php echo (!empty($type) ? "&type=".$type : "") ?>" enctype="multipart/form-data">
		<div>
			<input type="file" name="image" size="40" style="float: left"/>
			<input type="submit" value="uploader" style="float: right"/>
			<div class="zero"></div>
		</div>
	</form>
	<div class="message"><?php echo $message ?></div>
</div>
</body>
</html>