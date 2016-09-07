<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");
//require_once(ADMIN."mini-store.php");//require_once(ADMIN."products.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

$error = "";
if (!$user->login()) {
	$message = "Votre session a expirée";
}
else {
	$type = isset($_POST["type"]) ? $_POST["type"] : "";
	$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
	if (is_uploaded_file($_FILES["image"]['tmp_name'])) {
          $ms = Doctrine_Query::create()->select()->from('MiniStores')->where('id = ?',$id)->fetchOne();
		if (empty($id) || empty($ms['id'])) {
			$message = "Identifiant mimi-boutique incorrect";
		}elseif (empty($type)) { $message = "Type d'image non sélectionné";}
		else {
                  switch($type){
                    case 'home':
                      $dir = MSPP_HOME;
                      $width = 771;
                      $height = 266;
                      break;
                    case 'vignette':
                      $dir = MSPP_VIGN;
                      $width = 198;
                      $height = 66;
                      break;
                    case 'espace':
                      $dir = MSPP_ESPA;
                      $width = 187;
                      $height = 147;
                      break;
                  }
			$message = "ok";
			/*if ($type == "adv") $dir = PRODUCTS_IMAGE_ADV_INC;
			else $dir = PRODUCTS_IMAGE_INC;*/
                  if(is_file($dir.$id.'.jpg'))
                          $message = "L'image ".$type." de cette mini boutique est déjà uploadée";
                  else
			if (upload("image",'jpg', $id, $width, $height, $dir)) { 
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
.message { margin: 10px 0 0 0; font-weight: bold; font-size: 13px ; color: red}
.margin-right-30{margin-right: 30px}
</style>
</head>
<body>
<div class="wrapper">
	<form name="mini-store-pic" method="post" action="mini-store-pics.php?id=<?php echo $id ?><?php echo (!empty($type) ? "&type=".$type : "") ?>" enctype="multipart/form-data">
		<div>
			<input type="file" name="image" size="40" style="float: left"/>
                        <input type="submit" value="uploader" style="float: right"/><br />
			<div class="zero"></div>
                        <label for="home" class="fl">Home : </label><input type="radio" id="home" name="type" value="home" class="fl margin-right-30"/>
                        <label for="vignette" class="fl">Vignette : </label><input type="radio" id="vignette" name="type" value="vignette" class="fl margin-right-30"/>
                        <label for="espace" class="fl">Espace thématique : </label><input type="radio" id="espace" name="type" value="espace" class="fl"/>
                        <div class="zero"></div>
		</div>
	</form>
	<div class="message"><?php echo $message ?></div>
</div>
</body>
</html>