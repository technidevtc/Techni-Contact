<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$upload_ok = false;
try {
  $user = new BOUser();
  if (!$user->login())
    throw new Exception("Votre session a expirée.");
  
  $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
  if (empty($id))
    throw new Exception("Identifiant ordre fournisseur invalide.");
  
  $so = Doctrine_Query::create()
      ->select('*')
      ->from('SupplierOrder')
      ->where('id = ?', $id)
      ->fetchOne();
  if (!isset($so->id))
    throw new Exception("L'ordre fournisseur n° ".$id." n'existe pas.");
  
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($so['cancellation'] == 0) {
      if (!empty($_FILES['arcFile']['name'])) {
        if (is_uploaded_file($_FILES['arcFile']['tmp_name'])) {
          if (isset($boValidMimeTypes[$_FILES['arcFile']['type']])) {
            $filename = $so->sup_id."-".$so->order_id."_".trim($_FILES['arcFile']['name']);
            //$filename = mb_convert_encoding($so->sup_id."-".$so->order_id."_".trim($_FILES['arcFile']['name']), 'ISO-8859-1', 'UTF-8');
            if (@move_uploaded_file ($_FILES['arcFile']['tmp_name'], PDF_ARC.$filename)) {
              $so->arc = $filename;
              $so->arc_time = time();
              $so->setProcessingStatus(SupplierOrder::PROCESSING_STATUS_ARC_RECEIVED);
              $upload_ok = true;
            } else
              throw new Exception("Erreur lors de l'écriture de l'ARC.");
          } else
            throw new Exception("Document PDF non valide.<br/>Type uploadé : ".$_FILES['arcFile']['type']);
        } else
          throw new Exception("Erreur lors de l'upload du fichier.");
      }
    } else
      throw new Exception("Cet ordre fournisseur a été annulé.");
  }
  $message = "";
  
} catch (Exception $e) {
  $message = $e->getMessage();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Manager Techni-Contact : ARC Ordre fournisseur</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style type="text/css">
body { margin: 0; padding: 0 }
form { margin: 0; padding: 0 }
.zero { clear: both }
.wrapper { padding: 10px; font: normal 12px arial, helvetica, sans-serif }
.message { margin: 10px 0 0 0; font-weight: bold; font-size: 13px }
</style>
<?php if ($upload_ok) : ?>
<script type="text/javascript">
  var o = parent.order || parent.supplier_order;
  o.onArcLinked();
</script>
<?php endif ?>
</head>
<body>
<div class="wrapper">
	<form name="so-arc" method="post" action="<?php echo ADMIN_URL ?>ressources/iframes/supplier-order-arc.php?id=<?php echo $id ?>" enctype="multipart/form-data">
		<div>
			<input type="file" name="arcFile" size="40" accept="application/pdf"/>
		</div>
	</form>
  <div class="message"><?php echo $message ?></div>
</div>
</body>
</html>