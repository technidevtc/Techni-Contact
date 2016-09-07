<?php
	//Activated menu element 
	
	$actual_url_array		= explode('?', basename($_SERVER['REQUEST_URI']));
	$actual_url				= $actual_url_array[0];
	
	//The category of users to block
	$category_block_menu	= array(3, 4, 5);

	switch($actual_url){
	
		//Segments
		case 'my-segments.php':
		case 'segments-edit.php':
			$global_left_menu = 'menu_segments_elemen1';
		break;
		
		case 'segments-create.php':
			$global_left_menu = 'menu_segments_elemen2';
		break;
		
		case 'segments-export.php':
			$global_left_menu = 'menu_segments_elemen3';
		break;
		  
		
		/*
		//To active the parent one
		case 'segments.php':
			$global_left_menu = 'menu_segments_';
		break;*/
		
		

		//Administration
		case 'administration.php':
			$global_left_menu = 'menu_administration';
		break;
		
		case 'administration-users-create.php':
			$global_left_menu = 'menu_administration';
		break;
		
		case 'administration-users-edit.php':
			$global_left_menu = 'menu_administration';
		break;
		
		
		//Messages
		case 'my-messages.php':
			$global_left_menu = 'menu_messages';
		break;
		
		case 'create-message.php':
			$global_left_menu = 'menu_messages_elemen1';
		break;
		
		case 'edit-message.php':
			$global_left_menu = 'menu_messages';
		break;
		
		
		//Campaigns
		case 'my-campaigns.php':
			$global_left_menu = 'menu_campaigns';
		break;
		
		case 'create-campaign.php':
			$global_left_menu = 'menu_campaigns_elemen1';
		break;
		
		case 'edit-campaign.php':
			$global_left_menu = 'menu_campaigns';
		break;
		
		
		//Base Emails
		case 'base-email.php':
			$global_left_menu = 'menu_base_email';
		break;
		
		case 'fiche-email-base-email.php':
			$global_left_menu = 'menu_base_email_fiche';
		break;
		
		
		//Statistiques 
		case 'stats-globales.php':
			$global_left_menu = 'menu_base_statistiques_globales';
			
		break;
		
		
		
		
		case 'stats-campagnes.php':
			$global_left_menu = 'menu_base_statistiques_campagnes';
			
		break;
		
		
		//Home
		default :
			$global_left_menu = 'home';
		break;
	
	}
	
?>

<nav id="left_menu" role="navigation">
	<ul id="sidebar">

		<li <?php if(strcmp($global_left_menu,'home')=='0'){ echo('class="active"');} ?>>
			<a href="<?php echo MARKETING_URL; ?>">
				<i class="fa fa-home">
				</i>
				<span>
					Tableau de bord
				</span>
			</a>
		</li>
		
		<?php
		if(strpos($content_get_user_page_permissions['content'],'#1#')!==FALSE){
		?>
		<li <?php if(strpos($global_left_menu, "menu_segments")!==false){ echo('class="active"');} ?>>
			<a href="<?php echo MARKETING_URL.'my-segments.php'; ?>">
				<i class="fa fa-dashboard">
				</i>
				<span>
					Segments
				</span>
			</a>
			
			<ul>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#1#')!==FALSE){
				?>
					<li <?php if(strcmp($global_left_menu,'menu_segments_elemen1')=='0'){ echo('class="active"');} ?>>
						<a href="<?php echo MARKETING_URL.'my-segments.php'; ?>">
							<span>Mes segments</span>
						</a>
					</li>
				<?php
				}
				?>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#2#')!==FALSE){
				?>
				<li <?php if(strcmp($global_left_menu,'menu_segments_elemen2')=='0'){ echo('class="active"');} ?>>
					<a href="<?php echo MARKETING_URL.'segments-create.php'; ?>">
						<span>Cr&eacute;er un segment</span>
					</a>
				</li>
				<?php
				}
				?>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#3#')!==FALSE){
				?>
				<li <?php if(strcmp($global_left_menu,'menu_segments_elemen3')=='0'){ echo('class="active"');} ?>>
					<a href="<?php echo MARKETING_URL.'segments-export.php'; ?>">
						<span>Exporter segment</span>
					</a>
				</li>
				<?php
				}
				?>
			</ul>
		</li>
		<?php
		}
		?>
		
		
		<?php
		if(strpos($content_get_user_page_permissions['content'],'#8#')!==FALSE){
		?>
		<li <?php if(strpos($global_left_menu, "menu_messages")!==false){ echo('class="active"');} ?>>
			<a href="<?php echo MARKETING_URL.'my-messages.php'; ?>">
				<i class="fa fa-envelope">
				</i>
				<span>
					Messages
				</span>
			</a>
			
			<ul>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#8#')!==FALSE){
				?>
					<li <?php if(strcmp($global_left_menu,'menu_messages')=='0'){ echo('class="active"');} ?>>
						<a href="<?php echo MARKETING_URL.'my-messages.php'; ?>">
							<span>Mes messages</span>
						</a>
					</li>
				<?php
				}
				?>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#9#')!==FALSE){
				?>
				<li <?php if(strcmp($global_left_menu,'menu_messages_elemen1')=='0'){ echo('class="active"');} ?>>
					<a href="<?php echo MARKETING_URL.'create-message.php'; ?>">
						<span>Cr&eacute;er un message</span>
					</a>
				</li>
				<?php
				}
				?>
			</ul>
		</li>
		<?php
		}
		?>
		
		
		<?php
		if(strpos($content_get_user_page_permissions['content'],'#12#')!==FALSE){
		?>
		<li <?php if(strpos($global_left_menu, "menu_campaigns")!==false){ echo('class="active"');} ?>>
			<a href="<?php echo MARKETING_URL.'my-campaigns.php'; ?>">
				<i class="fa fa-server">
				</i>
				<span>
					Campagnes
				</span>
			</a>
			
			<ul>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#12#')!==FALSE){
				?>
					<li <?php if(strcmp($global_left_menu,'menu_campaigns')=='0'){ echo('class="active"');} ?>>
						<a href="<?php echo MARKETING_URL.'my-campaigns.php'; ?>">
							<span>Mes campagnes</span>
						</a>
					</li>
				<?php
				}
				?>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#13#')!==FALSE){
				?>
				<li <?php if(strcmp($global_left_menu,'menu_campaigns_elemen1')=='0'){ echo('class="active"');} ?>>
					<a href="<?php echo MARKETING_URL.'create-campaign.php'; ?>">
						<span>Cr&eacute;er une campagne</span>
					</a>
				</li>
				<?php
				}
				?>
			</ul>
		</li>
		<?php
		}
		?>
		
		
		
		<?php
		if(strpos($content_get_user_page_permissions['content'],'#16#')!==FALSE){
		?>
		<li <?php if(strpos($global_left_menu, "menu_base_email")!==false){ echo('class="active"');} ?>>
			<a href="<?php echo MARKETING_URL.'base-email.php'; ?>">
				<i class="fa fa-database">
				</i>
				<span>
					Base Email
				</span>
			</a>
			
			<ul>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#16#')!==FALSE){
				?>
					<li <?php if(strcmp($global_left_menu,'menu_base_email')=='0'){ echo('class="active"');} ?>>
						<a href="<?php echo MARKETING_URL.'base-email.php'; ?>">
							<span>Mes adresses email</span>
						</a>
					</li>
				<?php
				}
				?>
			</ul>
		</li>
		<?php
		}
		?>
		
		
		<?php
		if(strpos($content_get_user_page_permissions['content'],'#4#')!==FALSE){
		?>
		<li <?php if(strcmp($global_left_menu,'menu_administration')=='0'){ echo('class="active"');} ?>>
			<a href="administration.php">
				<i class="fa fa-users">
				</i>
				<span>
					Administration
				</span>
			</a>
		</li>
		<?php
		}
		?>
		
		<?php
		if(strpos($content_get_user_page_permissions['content'],'#19#')!==FALSE){
		?>
		<li <?php if(strpos($global_left_menu, "menu_base_email_globales_camp")!==false){ echo('class="active"');} ?>>
			<a href="<?php echo MARKETING_URL.'stats-globales.php'; ?>">
				<i class="fa fa-bar-chart">
				</i>
				<span>
					Statistiques
				</span>
			</a>
			
			<ul>
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#19#')!==FALSE){
				?>
					<li <?php if(strcmp($global_left_menu,'menu_base_statistiques_globales')=='0'){ echo('class="active"');} ?>>
						<a href="<?php echo MARKETING_URL.'stats-globales.php'; ?>">
							<span>Stats globales</span>
						</a>
					</li>
				<?php
				}
				?>
				
				<?php
				if(strpos($content_get_user_page_permissions['content'],'#20#')!==FALSE){
				?>
					<li <?php if(strcmp($global_left_menu,'menu_base_statistiques_campagnes')=='0'){ echo('class="active"');} ?>>
						<a href="<?php echo MARKETING_URL.'stats-campagnes.php'; ?>">
							<span>Stats campagnes</span>
						</a>
					</li>
				<?php
				}
				?>
			</ul>
		</li>
		<?php
		}
		?>
		
		
	</ul>
</nav>