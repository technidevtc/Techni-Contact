<LINK REL="STYLESHEET" TYPE="text/css" HREF="ressources/wysiwyg/css/stylesheet.css" />
<LINK REL="STYLESHEET" TYPE="text/css" HREF="ressources/wysiwyg/css/stylesheet_commands.css" />


<script src="ressources/wysiwyg/js/advanced.js"></script>
<script src="ressources/wysiwyg/js/wysihtml5-0.3.0.min.js"></script>


<form>
	<div id="message_toolbar" style="display: none;">
		<ul class="message_commands">
			<li data-wysihtml5-command="bold" title="CTRL+B"></li>
			<li data-wysihtml5-command="italic" title="CTRL+I"></li>
			<li data-wysihtml5-command="createLink"></li>
			<li data-wysihtml5-command="insertImage"></li>
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h1"></li>
			<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2"></li>
			<li data-wysihtml5-command="insertUnorderedList"></li>
			<li data-wysihtml5-command="insertOrderedList"></li>
			
			
			<li data-wysihtml5-command-group="foreColor" class="fore-color" title="Color the selected text">
				<ul>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="silver" href="javascript:;" unselectable="on"></li>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="gray" href="javascript:;" unselectable="on"></li>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="maroon" href="javascript:;" unselectable="on"></li>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="red" href="javascript:;" unselectable="on"></li>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="purple" href="javascript:;" unselectable="on"></li>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="green" href="javascript:;" unselectable="on"></li>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="olive" href="javascript:;" unselectable="on"></li>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="navy" href="javascript:;" unselectable="on"></li>
					<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="blue" href="javascript:;" unselectable="on"></li>
				</ul>
			</li>
		  
			
			
			
			<li data-wysihtml5-command="insertSpeech">speech</li>
			<li data-wysihtml5-action="change_view"></li>

			<div data-wysihtml5-dialog="createLink" style="display: none;">
				<label>
					Link:
					<input data-wysihtml5-dialog-field="href" value="http://">
				</label>
				<a data-wysihtml5-dialog-action="save">OK</a>
				&nbsp;
				<a data-wysihtml5-dialog-action="cancel">Cancel</a>
			</div>

			<div data-wysihtml5-dialog="insertImage" style="display: none;">
				<label>
					Image:
					<input data-wysihtml5-dialog-field="src" value="http://">
				</label>
				<label>
					Align:
					<select data-wysihtml5-dialog-field="className">
						<option value="">default</option>
						<option value="wysiwyg-float-left">left</option>
						<option value="wysiwyg-float-right">right</option>
					</select>
				</label>
				<!-- <input type="text" width & height -->
				<a data-wysihtml5-dialog-action="save">OK</a>
				&nbsp;
				<a data-wysihtml5-dialog-action="cancel">Cancel</a>
			</div>

		</ul>
	</div>
	<textarea id="message_textarea" placeholder="Enter text ..."></textarea>
	<br />
	<input type="reset" value="Vider l'&eacute;diteur !" style="margin-top: 5px;" />
</form>

<script>
  var editor = new wysihtml5.Editor("message_textarea", {
    toolbar:      "message_toolbar",
    stylesheets:  "ressources/wysiwyg/css/editor_in_page.css",
    parserRules:  wysihtml5ParserRules
  });
  
  /*var log = document.getElementById("log");
  editor
    .on("load", function() {
      log.innerHTML += "<div>load</div>";
    })
    .on("focus", function() {
      log.innerHTML += "<div>focus</div>";
    })
    .on("blur", function() {
      log.innerHTML += "<div>blur</div>";
    })
    .on("change", function() {
      log.innerHTML += "<div>change</div>";
    })
    .on("paste", function() {
      log.innerHTML += "<div>paste</div>";
    })
    .on("newword:composer", function() {
      log.innerHTML += "<div>newword:composer</div>";
    })
    .on("undo:composer", function() {
      log.innerHTML += "<div>undo:composer</div>";
    })
    .on("redo:composer", function() {
      log.innerHTML += "<div>redo:composer</div>";
    });*/
</script>