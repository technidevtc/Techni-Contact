<form action="#" method="POST" onsubmit="return messages_load_list_show();">
	<input type="hidden" name="fps" id="fps" value="1" />
	<input type="hidden" name="fpp" id="fpp" value="10" />
	<input type="hidden" name="table_order" id="table_order" value="" />
				
	<div class="row">
		<div class="form-left" style="padding-top: 6px; width: 40%;">
			<label for="f_search"></label>
			<div class="form-element-right" style="padding: 5px 0 0 1px;">
				<input type="search" id="f_search" name="f_search" value="" alt="Rechercher un message" class="form-control" placeholder="Rechercher un message" style="margin-top: 3px;" onkeyup="messages_name_div_autocomplete()" autocomplete="off" />
				
				<img id="messages_search_autosuggest_loading" src="ressources/images/loader.gif" />
				
				<input type="button" value="Go" class="btn btn-primary" style="padding:3px 10px 2px 10px; margin-left: 6px; margin-top: 3px;" title="Filtrer" onclick="messages_load_list_show()" />
			</div>
		</div><!-- end .form-left -->
		
		
		<div class="form-middle" style="width:280px;">
	
		</div><!-- end .form-middle -->
		
		
		<div class="form-right">
			<input type="button" id="messages_btn_export" value="Exporter" onclick="javascript:open_link_blank('messages_external_formid', 'messages-list-export.php', '_blank');" class="btn btn-primary" />
		</div>
		
	</div><!-- end div .row -->

</form>

<div id="messages_search_autosuggest" class="row" style="display: none;">
	<span id="messages_search_autosuggest_filter_close">
		<img src="ressources/images/icons/cross.png" alt="Fermer" title="Fermer" onclick="messages_search_hide_autosuggest();" />
	</span>
	<div id="messages_search_autosuggest_content"></div>
</div>