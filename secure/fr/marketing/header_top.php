<div class="h_elements">
	<div id="header_left">
		<!--
		<div class="hl_picture">
			<img src="<?php echo MARKETING_URL.'ressourcesv3/images/header-TC-logo.png'; ?>" alt="Logo Covepro" title="Logo Covepro" width="100" height="30" />
		</div>
		-->
		<div class="hl_hello_name">
			Bienvenue 
			<?php echo $_SESSION['marketing_user_name']; ?>
		</div>
		
		<br />
		<div class="hr_disconnect">
			<a href="<?php echo MARKETING_URL.'disconnect.php'; ?>">
				<i class="fa fa-sign-out">
					Se d&eacute;connecter
				</i>
			</a>
		</div>
	</div>
	
	<div id="header_right">
		<div class="hr_technico">
			<img src="<?php echo MARKETING_URL.'ressources/images/header-TC-logo.png'; ?>" alt="Techni-contact.com" title="Techni-contact.com" width="200" />
		</div>
		<br />
		<div class="hr_technico_mmanager">
			Marketing manager
		</div>
		
	</div>
</div>