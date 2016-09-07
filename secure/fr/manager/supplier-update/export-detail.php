<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	$title = $navBar = "MAJ Fournisseurs";
	$db = DBHandle::get_instance();
	$user = new BOUser();

	require(ADMIN.'head.php');
	require_once 'supplier_update_functions.php';
?>
<?php 
	if ($user->get_permissions()->has("m-prod--sm-maj-fournisseurs", "r")){	
?>
		<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>supplier-update/supplier-update.css" />
		<script type="text/javascript" src="<?php echo ADMIN_URL ?>supplier-update/supplier-update.js"></script>
		<div id="mmf_import_results">
		
			<div class="titreStandard">D&eacute;tails op&eacute;ration</div>
				<div class="bg">
			
					<?php
						$id_operation	= mysql_real_escape_string($_GET['id']);
						
						if(empty($id_operation)){
						
							echo('Op&eacute;ration inexistante ! <a href="/fr/manager/supplier-update/index.php">Retour</a>');
						
						}else{
						
							require_once('import_results.php');
				
						}//End else if empty($id_operation)
				
				?>
			</div><!-- end div .bg -->
		</div><!-- end div #mmf_import_results -->
		

		
<?php 
	}else{ 
?>
		<div class="bg" style="position: relative">
			<h2>Vous n'avez pas les droits adéquats pour réaliser cette opération.</h2>
		</div>
<?php
	}
?>
<?php require(ADMIN.'tail.php') ?>