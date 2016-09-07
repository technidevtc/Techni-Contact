<?php
if(!defined('PREVIEW')) require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();

$client_id  =  mysql_real_escape_string($_GET['client_id']);
$societe    =  mysql_real_escape_string($_GET['societe']); 
$secteur    =  mysql_real_escape_string($_GET['secteur']);
$nom  		=  mysql_real_escape_string($_GET['nom']);
$prenom		=  mysql_real_escape_string($_GET['prenom']);
$adresse	=  mysql_real_escape_string(htmlentities($_GET['adresse']));
$email		=  mysql_real_escape_string($_GET['email']);
$tel		=  mysql_real_escape_string($_GET['tel']);
$cp			=  mysql_real_escape_string($_GET['cp']);
$ville		=  mysql_real_escape_string($_GET['ville']);
$pays		=  mysql_real_escape_string($_GET['pays']);

$sql_update = "UPDATE  `annuaire_client` SET  
			    `societe` =  '$societe',
				`secteur` =  '$secteur',
				`nom` 	  =  '$nom',
				`prenom`  =  '$prenom',
				`adresse` =  '$adresse',
				`email`   =  '$email',
				`tel`     =  '$tel',
				`cp`      =  '$cp',
				`ville`   =  '$ville',
				`pays`    =  '$pays'
   			   WHERE  `client_id` =$client_id";
mysql_query($sql_update);

						   echo '<div class="epace_bottom">
									<label><strong>Société : </strong></label>
									<span class="margin-form">'.$societe.'</span>
									<span id="update_forms_societe" class="margin-form"><input type="text" id="societe" value="'.$societe.'" /></span>
								</div>
							
								<div class="epace_bottom">
									<label><strong>Secteur : </strong></label>
									<span class="margin-form">'.$secteur.'</span>
									<span id="update_forms_secteur" class="margin-form">
									<select id="secteur" style="width: 150px;">';
							
											$sql_sector = "SELECT sector FROM activity_sector";
											$req_sector = mysql_query($sql_sector);
											while($data_sector = mysql_fetch_object($req_sector)){
												if($data_sector->sector == $secteur) $selected=" selected='true' ";
												else $selected=" ";
												echo '<option value="'.$data_sector->sector.'" '.$selected.' >'.$data_sector->sector.'</option>';
											}
										
							echo '</select>
									</span>
								</div>';
								
								
						   echo '<div class="epace_bottom">
									<label><strong>Nom     : </strong></label>
									<span class="margin-form">'.$nom.'</span>
									<span id="update_forms_nom" class="margin-form"><input type="text" id="nom" value="'.$nom.'" /></span>
								 </div>
								
								<div class="epace_bottom">
									<label><strong>Prénom  : </strong></label>
									<span class="margin-form">'.$prenom.'</span>
									<span id="update_forms_prenom" class="margin-form"><input type="text" id="prenom" value="'.$prenom.'" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Email   : </strong></label>
									<span class="margin-form">'.$email.'</span>
									<span id="update_forms_email" class="margin-form"><input type="text" id="email" value="'.$email.'" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Tel     : </strong></label>
									<span class="margin-form">'.$tel.'</span>
									<span id="update_forms_tel" class="margin-form"><input type="text" id="tel" value="'.$tel.'" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Adresse  : </strong></label>
									<span class="margin-form">'.$adresse.'</span>
									<span id="update_forms_adresse" class="margin-form"><input type="text" id="adresse" value="'.$adresse.'" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Code Postal : </strong></label>
									<span class="margin-form">'.$cp.'</span>
									<span id="update_forms_cp" class="margin-form"><input type="text" id="cp" value="'.$cp.'" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Ville   : </strong></label>
									<span class="margin-form">'.$ville.'</span>
									<span id="update_forms_ville" class="margin-form"><input type="text" id="ville" value="'.$ville.'" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Pays    : </strong></label>
									<span class="margin-form">'.$pays.'</span>
									<span id="update_forms_pays" class="margin-form"><input type="text" id="pays" value="'.$pays.'" /></span>
								</div>';


?>