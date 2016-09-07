<?php		
			
			//Start div .row
			echo('<div class="row">');
				echo('<br />');
				echo('<div class="infos_element_left" style="width: 80px;">');
					echo('<label for="infos_prenom_popup">Pr&eacute;nom</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right" style="float: right;">');
					echo('<input type="text" id="infos_prenom_popup" value="" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<br />');
				echo('<div class="infos_element_left" style="width: 80px;">');
					echo('<label for="infos_nom_popup">Nom</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right" style="float: right;">');
					echo('<input type="text" id="infos_nom_popup" value="" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div class="row">');
				echo('<br />');
				echo('<div class="infos_element_left" style="width: 80px;">');
					echo('<label for="infos_contact_email_popup">Email</label>');
					echo(' <span class="srequired">*</span>');
				echo('</div>');
				
				echo('<div class="infos_element_right" style="float: right;">');
					echo('<input type="text" id="infos_contact_email_popup" value="" />');
				echo('</div>');
			echo('</div>');
			//End div .row
			
			
			//Start div .row
			echo('<div id="infos_contacts_errors" class="row">');
			echo('</div>');
			//End div .row
?>