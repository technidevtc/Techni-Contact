<form action="#" method="POST" onsubmit="return base_emails_load_list_show();">
	<input type="hidden" name="fps" id="fps" value="1" />
	<input type="hidden" name="fpp" id="fpp" value="10" />
	<input type="hidden" name="table_order" id="table_order" value="" />
				
	<div class="row">
		<div class="form-left" style="padding-top: 6px; width: 40%;">
			<label for="f_search"></label>
			<div class="form-element-right" style="padding: 5px 0 0 1px;">
				<input type="search" id="f_search" name="f_search" value="" alt="Rechercher une adresse mail" class="form-control" placeholder="Rechercher une adresse mail" onkeyup="base_emails_name_div_autocomplete()" style="margin-top: 3px;" autocomplete="off" />
				
				<img id="base_emails_search_autosuggest_loading" src="ressources/images/loader.gif" />
				
				<input type="button" value="Go" class="btn btn-primary" style="padding:3px 10px 2px 10px; margin-left: 6px; margin-top: 3px;" title="Filtrer" onclick="base_emails_load_list_show()" />
			</div>
			
			<div id="base_emails_search_autosuggest" class="row" style="display: none;">
				<span id="base_emails_search_autosuggest_filter_close">
					<img src="ressources/images/icons/cross.png" alt="Fermer" title="Fermer" onclick="base_emails_search_hide_autosuggest();" />
				</span>
				<div id="base_emails_search_autosuggest_content"></div>
			</div>
			
		</div><!-- end .form-left -->
		
		
		<div class="form-middle" style="width:280px;">
	
		</div><!-- end .form-middle -->
		
		
		<div class="form-right">
			<input type="button" id="base_emails_btn_export" value="Exporter" onclick="javascript:open_link_blank('base_emails_external_formid', 'base-emails-list-export.php', '_blank');" class="btn btn-primary" />
		</div>
		
	</div><!-- end div .row -->
	
	
	<div class="row">
		<div class="form-left" style="padding-top: 6px; width: 40%;">
			<label for="f_blacklist_area">D&eacute;sinscrire des adresses</label>
			<br />
			<div class="form-element-right" style="padding: 5px 0 0 1px;">
				<textarea id="f_blacklist_area" name="f_blacklist_area" cols="31" rows="8"></textarea>
				<br />
				<select id="f_blacklist_select" name="f_blacklist_select">
					<?php
					
						$res_get_motifs_query	= "SELECT
														id, 
														label 
													FROM
														marketing_base_email_motifs
													ORDER BY label";
						
						$res_get_motifs = $db->query($res_get_motifs_query, __FILE__, __LINE__);
						while($content_get_motifs	= $db->fetchAssoc($res_get_motifs)){
							echo('<option value="'.$content_get_motifs['id'].'">'.utf8_decode($content_get_motifs['label']).'</option>');
						}
					?>
				</select>
				
				<input type="button" id="base_emails_btn_blacklist" value="OK" onclick="blacklist_address()" class="btn btn-primary" />
			</div>
		</div><!-- end .form-left -->
		
		
		<div class="form-middle" style="width:280px;">
			
		</div><!-- end .form-middle -->
		
		
		<div class="form-right">
			<!--<input type="button" id="base_emails_btn_export" value="Exporter" onclick="javascript:open_link_blank('base_emails_external_formid', 'base_emails-list-export.php', '_blank');" class="btn btn-primary" />-->
		</div>
		
	</div><!-- end div .row -->
	
	

</form>