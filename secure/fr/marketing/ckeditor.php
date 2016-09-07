
<script type="text/javascript" src="ressources/ckeditor/ckeditor.js"></script>

<textarea id="message_ckeditor" name="message_ckeditor" class="ckeditor" cols="80" rows="10" tabindex="1"><?php 

	//We use that for the Edit !
	if(!empty($content_get_message['object'])){
		echo(utf8_decode($content_get_message['content']));
	}

?></textarea>