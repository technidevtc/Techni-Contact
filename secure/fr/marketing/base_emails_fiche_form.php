<form>
	<input type="hidden" name="fps" id="fps" value="1" />
	<input type="hidden" name="fpp" id="fpp" value="10" />
	<input type="hidden" name="table_order" id="table_order" value="" />
	
	
	<div class="row" style="display:block; width:100%;">
		<div class="form-right">
		<input type="button" id="base_emails_btn_export" value="Exporter" onclick="javascript:open_link_blank('base_emails_external_formid', 'base-emails-operations-list-export.php?email_id=<?php echo $email_id; ?>', '_blank');" class="btn btn-primary" style="float: right; width: 100px; margin-right: 14px;" />
	</div>
		
	</div><!-- end div .row -->
</form>
<div id="base_email_top_element">
	&nbsp;
</div>
<br />