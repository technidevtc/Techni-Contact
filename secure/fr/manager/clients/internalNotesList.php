<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 11 mars 2011

 Mises à jour :
 * AS IT IS REQUIRED THROUGH AJAX, THIS FILE MUST BE ENCODED IN UTF-8 *

 Fichier : /secure/manager/clients/internalNotesList.php
 Description : Affichage de la liste des notes internes d'un client

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

//require(ICLASS . 'Command.php');
//require(ADMIN  . 'statut.php');
$handle = DBHandle::get_instance();

$clientID = empty ($clientID) && $_GET['customerID'] ? $_GET['customerID'] : '';
if ($clientID != '') {
  if (preg_match('/^\d+$/',$clientID)) {
    $clientInfos = new CustomerUser($handle,$clientID);
    if ($clientInfos === false) {
      $error = true;
      $errorstring .= "- Il n'existe pas de client ayant pour num&eacute;ro identifiant ".$clientID."<br />\n";
      exit;
    } else {
      $page = 'client';
    }
  } else {
    $error = true;
    $errorstring .= "- Le num&eacute;ro d'identifiant client est invalide<br />\n";
    exit;
  }
}else
  exit;
?>
<style>
  #bloc-IMOrderDetail { border: 1px solid #CCCCCC; font-family: Arial, Helvetica, sans-serif; width : 580px;}
  .bloc-IM-titre { font-size: 12px; font-weight: bold; color: #000; background-color: #E5E1D8; padding: 5px; }
  .bloc-IM-content {padding: 10px;}

  .bloc { border: 1px solid #CCCCCC; font-family: Arial, Helvetica, sans-serif; }
  .bloc-titre2 { font-size: 12px; font-weight: bold; color: #000000; background-color: #E5E1D8; padding: 5px; }
    conversations
  .conversation { width: 99%; font: normal 11px arial, helvetica, sans-serif }
  .conversation .title, .conversation h2 { position: relative; top: 1px; left: 0; float: left; height: 14px; padding: 2px 10px 1px; font: normal 11px arial, helvetica, sans-serif; color: #b00000; border: 1px solid #7f7f7f; background: url(../ressources/images/conversation-title-bg.png) repeat-x; margin-bottom: 0px}
  .conversation ul { clear: both; margin: 0; padding: 0; list-style-type: none; border: 1px solid #4d4d4d }
  .conversation ul.grey { background: #f2f2f2 }
  .conversation ul.white { background: #ffffff }
  .conversation li { margin: 0; padding: 3px 5px; border-top: 1px solid #c6c6c6 }
  .conversation li.first { border-top: 0 }

  .show_attachment_links{display: none}
  .show_messenger_attachment{float: left; margin-left: 2px}
</style>
<br />
<div class="bg">
	<?php
		//Load Email Client to pass it By Javascript
	?>

	<!-- Internal notes and messenger -->
	<div class="module_internal_notes">
		<button id="module_internal_notes_item-cart-show-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note"></span> Laisser une note</button>
		<button id="module_internal_notes_item-cart-add-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note-add"></span> Poster la note</button>
		<button id="module_internal_notes_item-cart-cancel-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note-delete"></span> Annuler</button>
		<div id="module_internal_notes_item-cart-note" class="block note">
			<div>Laisser une note :</div>
			<textarea></textarea>
			<div class="attachments">
				<button id="module_internal_notes_add-msn-attachment" type="button" class="btn ui-state-default ui-corner-all">Ajouter une pièce jointe</button>
				Formats autoris&eacute;s : PDF, Document Word ou image '.jpg'
				<ul id="module_internal_notes_item-cart-attachment-list" class="attachment-list">
				</ul>
			</div>
		</div>
		<div class="zero"></div>
		<div id="module_internal_notes_item-cart-notes">
			<div class="block fold-block folded">
				<div class="title">
					Notes internes liées à cette facture
					<span class="icon-fold folded">+</span>
					<span class="icon-fold unfolded">-</span>
				</div>
				<div class="messages fold-content">
					<ul>
					</ul>
				</div>
			</div>
		</div>
		<br />
	

	<!-- attachment dialog box -->
		<div id="module_internal_notes_upload-msn-attachment-db" title="Ajouter une pièce jointe" class="db">
			<form name="loadDoc" method="post" action="" enctype="multipart/form-data">
				<input type="hidden" name="action" value="load-doc" /> 
				<input type="hidden" name="supplier" value="" />
				<!-- <input type="hidden" name="cmdId" value="<?php echo $o['id'] ?>" /> -->
				Nom : <input type="text" name="module_internalnotes_aliasPjMessFileName"	id="module_internalnotes_aliasPjMessFileName" value="" />
				<br />
				<br />
				Sélectionnez le document à cette facture (PDF, Document Word ou image '.jpg')<br />
				<br />
				<input type="file" name="module_internalnotes_pjMessFile"  id="module_internalnotes_pjMessFile" accept="application/pdf, application/msword, image/jpeg" />
				
				<br />
				<img id="module_internal_notes_upload_img_loading" class="loading-gif" src="<?php echo EXTRANET_URL ?>ressources/images/lightbox-ico-loading.gif" />
			</form>
		</div>
	</div>
	<!-- End attachment dialog box -->


	<!-- latest additions -->
	<?php
			
			$internal_notes = Array();
			$res = $handle->query(" SELECT
										`id`, `login` 
									FROM 
										`clients` 
									WHERE `id`=	".$clientID."", __FILE__, __LINE__);

			$internal_notes = $handle->fetchAssoc($res);
			
	?>
	<input type="hidden" id="module_internal_notes_hidden_global_id" value="<?php echo $internal_notes['login']; ?>" />
	<input type="hidden" id="module_internal_notes_hidden_attachments_id" value="" />
	<input type="hidden" id="module_internal_notes_hidden_global_id_for_attachment_pending" value="<?php echo $internal_notes['id']; ?>" />
	
	<script type="text/javascript">
		<?php
			if(!empty($internal_notes['login'])){
				echo("module_internal_notes_init_internal_notes('".$internal_notes['login']."')");
			}else{
				echo("document.getElementById('module_internal_notes_item-cart-notes').InnerHTML='Notes: Erreur r\351cup\351ration Email Client !';");
			}
		?>
	</script>

	<!-- End Internal notes and messenger -->
	
	
</div>