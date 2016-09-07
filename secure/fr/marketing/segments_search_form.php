<form action="#" method="POST" onsubmit="return segments_load_list_show();">
	<input type="hidden" name="fps" id="fps" value="1" />
	<input type="hidden" name="fpp" id="fpp" value="10" />
	<input type="hidden" name="table_order" id="table_order" value="" />
				
	<div class="row">
		<div class="form-left" style="padding-top: 6px; width: 40%;">
			<label for="f_search"></label>
			<div class="form-element-right" style="padding: 5px 0 0 1px;">
				<input type="search" id="f_search" name="f_search" value="" alt="Date d&eacute;but" title="Rechercher un segment" class="form-control" placeholder="Rechercher un segment" style="margin-top: 3px;" onkeyup="segment_name_div_autocomplete()" autocomplete="off" />
				
				<img id="segments_search_autosuggest_loading" src="ressources/images/loader.gif" />
				
				<input type="button" value="Go" class="btn btn-primary" style="padding:3px 10px 2px 10px; margin-left: 6px; margin-top: 3px;" title="Filtrer" onclick="segments_load_list_show()" />
			</div>
		</div><!-- end .form-left -->
		
		
		<div class="form-middle" style="width:280px;">
			
			<div class="form-element-right" style="padding: 5px 0 0 1px;">
				<label for="f_type_indifferent">
					Indiff&eacute;rent
				</label>
				<input type="radio" id="f_type_indifferent" name="f_type" value="" style="margin-top: 3px;" checked="true" />
			</div>
			
			<div class="form-element-right" style="padding: 5px 0 0 1px;">
				<label for="f_search_statique">
					Statique
				</label>
				<input type="radio" id="f_search_statique" name="f_type" value="statique" style="margin-top: 3px;" />
			</div>
			
			<div class="form-element-right" style="padding: 5px 0 0 1px;">
				<label for="f_search_dynamique">
					Dynamique
				</label>
				<input type="radio" id="f_search_dynamique" name="f_type" value="dynamique" style="margin-top: 3px;" />
			</div>
			
		</div><!-- end .form-middle -->
		
		
		<div class="form-right">
			<input type="button" id="segments_btn_export" value="Exporter" onclick="javascript:open_link_blank('segments_external_formid', 'segments-list-export.php', '_blank');" class="btn btn-primary" />
		</div>
		
	</div><!-- end div .row -->

</form>

<div id="segments_search_autosuggest" class="row" style="display: none;">
	<span id="segments_search_autosuggest_filter_close">
		<img src="ressources/images/icons/cross.png" alt="Fermer" title="Fermer" onclick="segments_search_hide_autosuggest();" />
	</span>
	<div id="segments_search_autosuggest_content"></div>
</div>