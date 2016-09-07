<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$title = $navBar = "Liste des scripts";
require(ADMIN."head.php");


if (!$userPerms->has($fntByName["m-smpo--sm-script-product"], "re")) {
  ?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php } else { 

  $bindTypeList = ScriptProduct::getRelationTypeList();
  define('NB', 30);
  ?>

<link rel="stylesheet" type="text/css" href="smpo.css" />
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">

<div class="titreStandard">Liste des scripts</div>
<br/>

<input type="hidden" name="page" value="" />
<input type="hidden" name="formerpage" value="" />
<input type="hidden" name="sort" value="" />
<input type="hidden" name="lastsort" value="" />
<input type="hidden" name="sortway" value="" />

<div class="section">
	<div style="color: #FF0000" id="show_error_message"><?php if (!empty($errorstring)) echo $errorstring ?></div>
	<div class="block">
          <div class="blocka">
            <a href="script_create.php">Ajouter un script</a>
          </div>
           <div class="zero"></div>
           <br />
          <div class="fl" style="width : 650px">
            <div class="fl">
            <span class="label" style="width : 210px">ID famille, nom famille ou partenaire : </span><input type="text" name="idRelation" id="idRelation" value="" />
            </div>
            <div class="fr">
            <span class="label" style="width : 140px">Type de rattachement : </span>
            <select style="vertical-align: top" name="typeRelation"  id="typeRelation">
              <option value=""> - </option>
              <?php
               foreach($bindTypeList as $id => $bindType)
                 echo '<option value="'.$id.'" '.(!empty($type_relation) && $type_relation == $id ? 'selected="selected"' : '').'>'.$bindType.'</option>';
                ?>
            </select>
            <button value="Ok" onClick="getListe()">Ok</button>
            </div>
            <div class="zero"></div>
          </div>
          
          <div class="zero"></div>
	<div class="listing" style="float: right"></div>

	<br />
	<table id="item-list" class="item-list" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th style="width : 60px"></th>
                                <th style="width : 140px"><a href="javascript: scriptsSort('date')">Date création</a></th>
				<th><a href="javascript: scriptsSort('type')">Type rattachement</a></th>
				<th><a href="javascript: scriptsSort('name')">Nom</a></th>
				<th style="width : 140px"><a href="javascript: scriptsSort('id')">ID</a></th>
				<th style="width : 140px"><a href="javascript: scriptsSort('nb_prod')">Nb produits</a></th>
				<th style="width : 70px"></th>
			</tr>
		</thead>
                <tbody id="scripts-list">
                  <tr class="tr-new"><td class="date" colspan="9"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>
                </tbody>
	</table>
        <br />
	<div class="listing"></div>
        </div>
</div>
<script type="text/javascript">
   <!--

  var AJAXHandle = {
	type : "GET",
	url: "AJAX_script-product.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
                var tbody = $("#scripts-list");
			tbody.empty();
                        tbody.append( '<tr class="tr-new"><td class="date" colspan="9"> '+textStatus+'</td></tr>');
	},
	success: function (data, textStatus) {
            var tbody = $("#scripts-list");
			tbody.empty();
                        
                        if(data.error){
                          tbody.append( '<tr class="tr-new"><td class="date" colspan="9" style="color : red"> '+data.error+'</td></tr>');
                        }
                        else if(data.reponses == 'vide'){
                          tbody.append( '<tr class="tr-new"><td class="date" colspan="9"> Pas de script correspondant. </td></tr>');
                        }else{
                              for (i = 0; i < data.reponses.length; i++)
                              {
                                      // tr type
                                      var tr = '';
                                        tr = '<tr class="tr-normal" onmouseover="this.className=\'tr-hover\'"  onmouseout="this.className=\'tr-normal\'" >';

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

                                      var nom_client = data.reponses[i].nom+' '+data.reponses[i].prenom;

                                        tbody.append(
                                                tr +
                                                "	<td class=\"nombre\"><a href=\"script_create.php?id_relation="+data.reponses[i].id_relation+"&type_relation="+data.reponses[i].type_relation+"\"><img src=\"../ressources/icons/application_form_edit.png\" /></a></td>" +
                                                "	<td class=\"date\">"+date+"</td>" +
                                                "	<td class=\"date\">"+data.reponses[i].relation_name+"</td>" +
                                                "	<td class=\"date\">"+data.reponses[i].family_name+"</td>" +
                                                "	<td class=\"date\">"+data.reponses[i].id_relation+"</td>" +
                                                "	<td class=\"produit\">" + data.reponses[i].nb_products + "</td>" +
                                                "	<td class=\"nombre\"><img src=\"../ressources/icons/cross.png\" onClick=\"deleteScript("+data.reponses[i].id_relation+", "+data.reponses[i].type_relation+")\" /></td>" +
                                                "</tr>");
                                       $('#nbTotalAppels').html(data.nbrTotalCalls);

                              }

                                            if(data.pagination){
                            var divPagination = $(".listing");
                                divPagination.empty();
                                var visible1 ;
                                var visible2 ;
                                var visible3 ;
                                var visible4 ;
                                var page = parseInt(data.pagination['page']) ;
                                var lastpage = parseInt(data.pagination['lastpage']) ;
                                if(page > 2){visible1 = 'visible'}else{visible1 = 'hidden'};
                                if(page > 1){visible2 = 'visible'}else{visible2 = 'hidden'};
                                if(page < lastpage){visible3 = 'visible'}else{visible3 = 'hidden'};
                                if(page < lastpage-1){visible4 = 'visible'}else{visible4 = 'hidden'};
                                var html = "<span style=\"visibility: "+visible1+"\"><a href=\"javascript: gotoPage(1)\">&lt;&lt;</a></span> "+
					"<span style=\"visibility: "+visible2+"\"><a href=\"javascript: gotoPage("+(page-1)+")\">&lt;</a> ... |</span> "+
					"<span style=\"visibility: "+visible2+"\"><a href=\"javascript: gotoPage("+(page-1)+")\">"+(page-1)+"</a> |</span> "+
					"<span class=\"listing-current\">"+page+"</span> "+
					"<span style=\"visibility: "+visible3+"\">| <a href=\"javascript: gotoPage("+(page+1)+")\">"+(page+1)+"</a></span> "+
					"<span style=\"visibility: "+visible3+"\">| ... <a href=\"javascript: gotoPage("+(page+1)+")\">&gt;</a></span> "+
					"<span style=\"visibility: "+visible4+"\"><a href=\"javascript: gotoPage("+lastpage+")\">&gt;&gt;</a></span> ";

                                divPagination.append(html);

                                $('input[name=page]')[0].value = page;
                                $('input[name=formerpage]')[0].value = data.pagination['formerpage'];
                                $('input[name=sort]')[0].value = data.pagination['sort'];
                                $('input[name=lastsort]')[0].value = data.pagination['lastsort'];
                                $('input[name=sortway]')[0].value = data.pagination['sortway'];
                         }

                        }
	}
  };

 var AJAXHandleProcessCall = {
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
            document.location.href = 'tel:'+this.tel;

            window.setTimeout(location.href= '<?php echo ADMIN_URL ?>contacts/lead-create.php?idClient='+this.idClient+'&idLead='+this.idLead+'&idCall='+this.idCall , 100);

          }
        }
  };


  function getListe(){

    var date  = new Date();

    var tbody = $("#scripts-list");

    var idRelation = $('input[name=idRelation]').val();
    var typeRelation = typeof $('#typeRelation').val() !== 'undefined' ? $('#typeRelation').val() : '';

    var NB = $('input[name=NB]').val();
    var page = $('input[name=page]').val();
//    var lastpage = $('input[name=lastpage]').val();
    var formerpage = $("input[name=formerpage]").val();
    var sort = $('input[name=sort]').val();
    var lastsort = $('input[name=lastsort]').val();
    var sortway = $('input[name=sortway]').val();

    AJAXHandle.data = "&idRelation="+idRelation+"&typeRelation="+typeRelation+'&sort='+sort+'&sortway='+sortway+'&lastsort='+lastsort+'&page='+page+'&formerpage='+formerpage+'&NB='+<?php echo NB ?>;
    AJAXHandle.type = 'GET';
    $.ajax(AJAXHandle);

  }

  function deleteScript(idRelation, typeRelation){

    AJAXHandle.data = "idRelation="+idRelation+"&typeRelation="+typeRelation+"&action=delete";
    AJAXHandle.type = 'POST';
    $.ajax(AJAXHandle);

  }

// pagination
function gotoPage(page)
{
  if (!isNaN(page = parseInt(page)))
  {
    $('input[name=page]').val(page);
    getListe();
  }
}

function scriptsSort(order){
  $('input[name=sort]').val(order);
  $('input[name=lastsort]').val(order);

  getListe();
}
//pagination

  getListe();
  
//-->
</script>
<?php } // end if permission ?>
<?php require(ADMIN."tail.php") ?>