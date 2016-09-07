<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require_once(ICLASS."ManagerUser.php");
require_once(ADMIN."products.php");

$handle = DBHandle::get_instance();
$user = new BOUser();
$db = DBHandle::get_instance();

$errormessage = "";
if (!$user->login()) {
	$errormessage = "Votre session a expirée";
}
else {
	$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
	if (empty($id))
		$errormessage = "Identifiant produit incorrect";
	
	$type = isset($_GET["type"]) ? $_GET["type"] : "";
	if ($type == "adv") $dir = PRODUCTS_FILES_ADV_INC;
	else $dir = PRODUCTS_FILES_INC;
	
	if (!isset($_GET["num"]) || !preg_match("/^[0-9]+$/", $_GET["num"]))
		$errormessage = "Numéro de document non spécifié";
	
	if (empty($errormessage)) {
		$num = (int)$_GET["num"];
		
		$res = $db->query("SELECT id FROM products WHERE id = ".$id, __FILE__, __LINE__);
		if ($db->numrows($res, __FILE__, __LINE__) == 0) {
			$errormessage = "Identifiant produit incorrect";
		}
		else {
			if (is_uploaded_file($_FILES["doc"]["tmp_name"])) {
				$errormessage = "Document upload avec succés";
				copy($_FILES["doc"]["tmp_name"], $dir.$id."-".$num.".pdf");
				$upload_success = true;
				$filesize = filesize($dir.$id."-".$num.".pdf");
			}
			elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
				$errormessage = "Erreur lors de la copie du document";
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
.errormessage { margin: 10px 0 0 0; font-weight: bold; font-size: 13px }
</style>
<?php if (isset($upload_success)) { ?>
<script type="text/javascript">top.pdm.setUploaded(<?php echo $num ?>, 1, <?php echo $filesize ?>);</script>
<?php } ?>
</head>
<body>
<div class="wrapper">
	<form name="product-pic" method="post" action="product-doc.php?id=<?php echo $id ?><?php echo (!empty($type) ? "&type=".$type : "") ?>&num=<?php echo $num ?>" enctype="multipart/form-data">
		<div>
			<input type="file" name="doc" size="40" style="float: left"/>
			<input type="submit" value="uploader" style="float: right"/>
			<div class="zero"></div>
		</div>
	</form>
	<div class="errormessage"><?php echo $errormessage ?></div>
</div>
</body>
</html>