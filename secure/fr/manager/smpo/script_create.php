<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$title = $navBar = "Liste des scripts";
require(ADMIN."head.php");


$callSpool = CallsSpool::resetDailyAbsence();

if (!$userPerms->has($fntByName["m-smpo--sm-script-product"], "re")) {
  ?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php } else {

  $type_relation = '';
  $id_relation = '';

  if(!empty($_GET['type_relation']) && !empty($_GET['id_relation'])){
    $script = ScriptProduct::get(array('id_relation = '.$_GET['id_relation'], 'type_relation = '.$_GET['type_relation']));
    
    if(!empty($script) && count($script == 1)){
      $desc = $script[0]['content'];
      $type_relation = $script[0]['type_relation'];
      $id_relation = $script[0]['id_relation'];
    }
  }
  
  $bindTypeList = ScriptProduct::getRelationTypeList();
//  var_dump($_POST);
  if(!empty($_POST))
    if( !empty ($_POST['type_relation']) && !empty ($_POST['id_relation']) && !empty ($_POST['desc']) ){

      try {
        $script = new ScriptProduct($_POST['id_relation'], $_POST['type_relation']);
        $script->setData(array ('content'=> $_POST['desc'])); // , 'timestamp' => time()
        
        if(!$script->save())
          $errorstring = 'Erreur à l\'enregistrement';
        else
          $info = 'Script correctement mis à jour';
      } catch (Exception $exc) {
        if(preg_match('/Duplicate entry \'[1-9]{1}[0-9]{0,8}-[0-'.count($bindTypeList).']\' for key 2/', $exc->getMessage()))
           $errorstring = 'L\'identifiant '.$_POST['id_relation'].' de la relation '.$bindTypeList[$_POST['type_relation']].' existe déjà. <br />
             Retournez à la liste des scripts pour le modifier';
        else{
          $errorMessage = $exc->getMessage();
          $errorstring = !empty($errorMessage) ? $errorMessage : 'Erreur à l\'enregistrement';
        }
      }
      $desc = $_POST['desc'];
      $type_relation = $_POST['type_relation'];
      $id_relation = $_POST['id_relation'];
    }else
      $errorstring = 'Toutes les informations ne sont pas renseignées';


  ?>

<link rel="stylesheet" type="text/css" href="smpo.css" />
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script type="text/javascript" src="../ref/global.js"></script>
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="../ckfinder/ckfinder.js"></script>

<div class="titreStandard">Liste des scripts</div>
<br/>
<div class="blocka">
  <a href="script_product.php">Retour à la liste</a>
</div>
<div class="zero"></div>
<div class="section">
    <div style="color: #FF0000" id="show_error_message"><?php if (!empty($errorstring)) echo $errorstring ?></div>
    <?php if(!empty($info)){?>
    <div class="inf" style="height: 3em"><span style="margin-left : 50px; position : relative; top: 15px" ><?php echo $info ?></span></div>
    <br />
    <?php } ?>
	<div class="block">
          Merci de d'indiquer l'ID de la famille ou du partenaire à lier au script et le contenu de ce dernier.<br />
        Les scripts peuvent être liés à des familles 2 ou 3. Il peuvent aussi être liés à des partenaires.<br />
         Les scripts des familles 3 sont affichés prioritairement à ceux de leur famille 2 parente.<br />
        Si le script est rattaché à un partenaire, il devient alors prioritaire
        <br />
        <br />
        <br />
        <form method="post" action="#">
        <table>
         <tr><td class="intitule">Type de rattachement :</td><td class="intitule">
             <select class="champstexte" name="type_relation">
               <?php 
               foreach($bindTypeList as $id => $bindType)
                 echo '<option value="'.$id.'" '.(!empty($type_relation) && $type_relation == $id ? 'selected="selected"' : '').'>'.$bindType.'</option>';
                ?>
             </select>
           </td></tr>
         <tr><td class="intitule">ID famille ou ID partenaire à lier :</td><td class="intitule"><input type="text" class="champstexte" name="id_relation" size="40" maxlength="255" value="<?php print(to_entities($id_relation)) ?>"></td></tr>

        </table>

        <textarea name="desc"><?php print(str_replace(array("</script>"), array("</scr\" + \"ipt>"), $desc)) ?></textarea>
        <script type="text/javascript">
        <!--
        CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
        var editor = CKEDITOR.replace('desc');
        CKFinder.setupCKEditor( editor, '../ckfinder/' );
        //-->
        </script>
        <br />
          <button>Valider</button>
        </form>
    </div>
</div>
<script type="text/javascript">
   <!--

  var AJAXHandle = {
	type : "GET",
	url: "AJAX_calls-liste.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
                var tbody = $("#content-list");
			tbody.empty();
                        tbody.append( '<tr class="tr-new"><td class="date" colspan="9"> '+textStatus+'</td></tr>');
	},
	success: function (data, textStatus) {
            var tbody = $("#content-list");
			tbody.empty();
                        
                        if(data.error){
                          tbody.append( '<tr class="tr-new"><td class="date" colspan="9" style="color : red"> '+data.error+'</td></tr>');
                        }
                        else if(data.reponses == 'vide'){
                          tbody.append( '<tr class="tr-new"><td class="date" colspan="9"> Aucun appel dans la file d\'attente. </td></tr>');
                        }else{
                              for (i = 0; i < data.reponses.length; i++)
                              {

                                      // tr type
                                      var tr = '';
                                        tr = '<tr class="tr-normal" onmouseover="this.className=\'tr-hover\'" onmouseout="this.className=\'tr-normal\'" onclick="isAlreadyPickedUp('+data.reponses[i].id+', \''+data.reponses[i].id_client+'\', \''+data.reponses[i].id_lead+'\', \''+data.reponses[i].tel+'\');\" >';

                                      // date format
                                      var date = new Date(data.reponses[i].timestamp*1000);
                                      var year = date.getFullYear();
                                      var month = date.getMonth()+1;
                                      month = month.toString();
                                      if(month.length !=2){month = '0'+month};
                                      var day = date.getDate().toString();
                                      if(day.length !=2){day = '0'+day};
                                      var hours = date.getHours().toString();
                                      if(hours.length !=2){hours = '0'+hours};
                                      var minutes = date.getMinutes().toString();
                                      if(minutes.length !=2){minutes = '0'+minutes};
                                      var seconds = date.getSeconds().toString();
                                      if(seconds.length !=2){seconds = '0'+seconds};
                                      date = day+'/'+month+'/'+year+' '+hours+':'+minutes;

                                      // date format
                                      var dateLead = new Date(data.reponses[i].dateLead*1000);
                                      var year = dateLead.getFullYear();
                                      var month = dateLead.getMonth()+1;
                                      month = month.toString();
                                      if(month.length !=2){month = '0'+month};
                                      var day = dateLead.getDate().toString();
                                      if(day.length !=2){day = '0'+day};
                                      var hours = dateLead.getHours().toString();
                                      if(hours.length !=2){hours = '0'+hours};
                                      var minutes = dateLead.getMinutes().toString();
                                      if(minutes.length !=2){minutes = '0'+minutes};
                                      var seconds = dateLead.getSeconds().toString();
                                      if(seconds.length !=2){seconds = '0'+seconds};
                                      dateLead = day+'/'+month+'/'+year+' '+hours+':'+minutes;

                                      var nom_client = data.reponses[i].nom+' '+data.reponses[i].prenom;

                                        tbody.append(
                                                tr +
                                                "	<td class=\"date\">"+date+"</td>" +
                                                "	<td class=\"date\">"+dateLead+"</td>" +
                                                "	<td class=\"date\">"+data.reponses[i].societe+"</td>" +
                                                "	<td class=\"date\">"+nom_client+"</td>" +
                                                "	<td class=\"date\">"+data.reponses[i].fonction+"</td>" +
                                                "	<td class=\"produit\">" + data.reponses[i].secteur + "</td>" +
                                                "	<td class=\"type\">" +data.reponses[i].salaries+  "</td>" +
                                                "	<td class=\"date\">"+data.reponses[i].product_name+"</td>" +
                                                "	<td class=\"nombre\">" +data.reponses[i].nbrCalls+"</td>" +
                                                "	<td class=\"nombre\"><img src=\"../ressources/icons/telephone.png\" /></td>" +
                                                "</tr>");
                                       $('#nbTotalAppels').html(data.nbrTotalCalls);

                              }

                        }
	}
  };

 var AJAXHandleProcessCall = {
	type : "GET",
	url: "AJAX_process-call.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
            $('#show_error_message').text(textStatus);
	},
	success: function (data, textStatus) {
          if(data.error){
            $('#show_error_message').text(data.error);
          }

          if(data.result == 'ok'){
            document.location.href = 'dial:'+this.tel;

            window.setTimeout(location.href= '<?php echo ADMIN_URL ?>contacts/lead-create.php?idClient='+this.idClient+'&idLead='+this.idLead+'&idCall='+this.idCall , 100);

          }
        }
  };

//-->
</script>
<?php } // end if permission ?>
<?php require(ADMIN."tail.php") ?>