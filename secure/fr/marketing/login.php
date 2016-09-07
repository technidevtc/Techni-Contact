<?php 
	require_once('functions.php'); 
	//require_once('check_login.html');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="ISO-5591">
  <title>Login : Marketing Techni-contact.com</title>
  <meta name="description" content="Login : Marketing Techni-contact.com">
  
  <?php require_once('header_tags.php'); ?>
  
</head>
<body id="login_body" ng-app="ZTechnicoAppMarketing">
	<div id="login_container_global">
		<div id="login_header">
			<img src="<?php echo MARKETING_URL.'ressources/images/header-TC-logo.png'; ?>" alt="Techni-contact.com" title="Techni-contact.com" width="200" />
		</div>
		
		<div id="login_container_middle">
		
			<div id="login_middle_first">
				Marketing Manager
			</div>
			
			<div id="login_container_form">
				<form action="<?php echo MARKETING_URL.'check-login.php'?>" method="POST" onsubmit="">
					<div class="login_trow">
						Veuillez vous identifier avant de poursuivre : 
					</div>
					<div class="login_frow">
						<div class="login_left">
							<label for="flogin">
								Nom d'utilisateur :
							</label>
						</div>
						<div class="login_right">
							<input type="search" required="true" id="flogin" name="flogin" autocomplete="off" />
						</div>
					</div>
					
					<div class="login_frow">
						<div class="login_left">
							<label for="fpassword">
								Mot de passe :
							</label>
						</div>
						<div class="login_right">
							<input type="password" required="true" id="fpassword" name="fpassword" />
						</div>
					</div>
					
					
					<div class="login_frow">
						<div class="login_left">
							<input type="hidden" id="fos" name="fos" value="" />
							<input type="hidden" id="fnavigator" name="fnavigator" value="" />
							<input type="hidden" id="fusertime" name="fusertime" value="" />
							&nbsp;
						</div>
						<div class="login_right">
							<input type="submit" class="fsend" value="Se connecter" />
						</div>
					</div>
				</form>
			
			</div><!-- end div #login_container_form -->
			
			<div id="form_errors" class="form_errors">
				<?php
					$errors	= $_GET['c'];
					if(!empty($errors)){
						switch($errors){
						
							//Expired Session
							case '6515654':
								echo('- Session expir&eacute; !');
							break;
							
							//Key doesn't match
							case '3215687':
								echo('- Erreur, connexion automatique, merci de r&eacute;essayer !');
							break;
							
							//Login and password doesn't match
							case '7032698':
								echo('- Combinaison login et mot de passe incorrecte');
							break;
							
							//Empty Login or Password redirect to login form
							case '6521589':
								echo('- Combinaison login et mot de passe incorrecte');
							break;
							
							case '1387945':
								echo('- Votre compte a &eacute;t&eacute; d&eacute;sactiv&eacute;. Contactez l\'administrateur !');
							break;
						
							default :
								echo('- Erreur, Merci de r&eacute;essayer !');
							break;
						}//end switch
					}//end if(!empty($errors))
				?>
			
			</div>
			<script type="text/javascript">
				get_agent_infos();
			</script>
		</div><!-- end div #login_container_middle -->
		
	</div><!-- end div #login_container_global -->
</body>
</html>