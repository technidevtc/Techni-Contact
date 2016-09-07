<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = $navBar = 'Edition des fichiers divers';
require(ADMIN . 'head.php');

if($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK)
{
	print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{
	$files = array(
		"nous" => "Société (qui sommes nous)",
		"catalogues" => "Nos catalogues",
		"contact" => "Contactez nous",
		"aide" => "Aide",
		"premiere-visite" => "Première visite",
		"cgv" => "CGV",
		"infos-legales" => "Mentions légales",
		"recrutement" => "Recrutement",
                "liens-partenaires" => "Quelques partenaires"
            );
?>
<div class="titreStandard">Sélection du fichier à éditer</div><br/>
<div class="bg">
	<form method="get" action="index.php">
		<input type="hidden" name="<?php print(session_name()) ?>" value="<?php print(session_id()) ?>">
		Sélection du fichier :
		<select name="file">
		<?php foreach($files as $name => $desc) { ?>
			<option value="<?php echo $name ?>"><?php echo $desc ?></option>
		<?php } ?>
		</select>
		<input type="button" class="bouton" name="ok" onClick="this.form.submit(); this.disabled=true" value="Ok">
	</form>
</div>
<?php
  
	if (isset($_GET["file"]) && isset($files[$_GET["file"]])) {
		$filename = $_GET["file"];
		$file = MISC_INC.$filename.".dat";
		$name = "Edition du fichier ".$files[$filename];
		
		if(isset($_GET['reload'])) {
			copy($file.".bk", $file);
			ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], "Restauration fichier original divers " . $files[$filename]);
		}
	}

	if (isset($file)) {
?>
<br/>
<br/>
<div class="titreStandard">
	<?php print($name) ?> - <a href="index.php?file=<?php print($filename."&".session_name()."=".session_id()) ?>&reload=true">Recharger la version originale</a>
</div>
<br/>
<div class="bg">
<?php
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
      if (!$user->get_permissions()->has("m-admin--sm-misc-page-edit","e")) {
        print "<div class=\"confirm\">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>";
      }
      else {
        if($f = fopen($file, 'w')) {
          fputs($f, $_POST['content']);
          fclose($f);
          ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], "Mise à jour du fichier divers ".$files[$filename]);
          print('<div class="confirm">Fichier mis à jour avec succès.</div>');
        }
        else {
          print('<div class="confirm">Erreur lors de la mise à jour.</div>');
        }
      }
		}
		else {
?>
	<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
	<form method="post" action="index.php?file=<?php print($filename."&".session_name()."=".session_id()) ?>">
		<textarea name="content"><?php print(implode('', file($file))) ?></textarea>
		<script type="text/javascript">
function save(o)
{
	o.form.target = '_self';
	o.form.action = 'index.php?file=<?php print($filename."&".session_name()."=".session_id()) ?>';
	o.form.submit();
	o.form.prev.disabled = true;
	o.disabled = true;
}

function preview(o)
{
	o.form.target = '_blank';
	o.form.action = 'preview.php?file=<?php print($filename."&".session_name()."=".session_id()) ?>';
	o.form.submit();
}

var sBasePath = '<?php echo ADMIN_URL ?>editor/';
CKEDITOR.replace('content');
/*
var oFCKeditor = new FCKeditor('content');
    oFCKeditor.BasePath	= sBasePath;
    oFCKeditor.Height = 400;
    oFCKeditor.Config['CustomConfigurationsPath'] = '<?php echo ADMIN_URL ?>files/myconfig.js'  ;
    oFCKeditor.ReplaceTextarea();*/
		</script>
		<br/>
		<br/>
		<center>
			<input type="button" value="Valider" onClick="save(this)"> &nbsp; <input type="reset" value="Annuler"><br/>
			<br/>
			<input type="button" value="Prévisualiser le résultat" onClick="preview(this)" name="prev">
		</center>
	</form>
<?php
		}
?>
</div>
<?php
	}
}  // fin autorisation

require(ADMIN . 'tail.php');

?>
