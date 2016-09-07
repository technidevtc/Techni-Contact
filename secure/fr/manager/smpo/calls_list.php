<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$title = $navBar = "Liste des appels";
require(ADMIN."head.php");

//define('DAILY_NB_UNANSWERED_CALLS', 3); // defines the number of unanswered call per day

$callSpool = CallsSpool::resetDailyAbsence();

if (!$userPerms->has($fntByName["m-smpo--sm-call-list"], "re")) {
  ?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php } else { 
  
	// Préparation liste des fonctions
	$n = $pc = 0; $pl = array(); // Post List
	if ($fh = fopen(MISC_INC . "list_post.csv","r")) {
		while (($datacsv = fgetcsv($fh, 128, ";")) !== false) $pl[$n++] = $datacsv;
		$pc = $n - 1; // Post Count -> La 1ère ligne est l'intitulé des colonnes
		fclose($fh);
	}
	
	// Préparation liste des tailles salariales
	$n = $nec = 0; $nel = array(); // Number of Employee List
	if ($fh = fopen(MISC_INC . "list_number-of-employees.csv","r")) {
		while (($datacsv = fgetcsv($fh, 64, ";")) !== false) $nel[$n++] = $datacsv[0];
		$nec = $n - 1; // Number of Employee Count -> La 1ère ligne est l'intitulé des colonnes
		fclose($fh);
	}

	// Préparation liste des secteurs d'activité
        $activity_sectors = Doctrine_Core::getTable('ActivitySector');
        $activity_sectorsList = $activity_sectors->findAll();
  
  ?>

<link rel="stylesheet" type="text/css" href="smpo.css" />
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script type="text/javascript" src="leads.js"></script>
<div class="titreStandard">Liste des appels</div>
<br/>
<div class="section">
  <div style="color: #FF0000" id="show_error_message"><?php if (!empty($errorstring)) echo $errorstring ?></div>
  <div class="block">
    <?php if ($userPerms->has($fntByName["m-smpo--sm-spool-filter-options"], "r")) { ?>
    <div class="title">Options de filtrage</div>
    <div class="text">
      <fieldset class="selector">
        <legend>Fonction :</legend>
        <select id="fonction-selector" name="fonction" class="edit"  onChange="updateListe()">
              <option value=""> - </option>
            <?php for ($i = 1; $i <= $pc; $i++) {
                if(strpos($pl[$i][1], '-----------') === false){?>
              <option value="<?php if(!empty($pl[$i][0])) echo $pl[$i][0]; else echo$pl[$i][1] ?>"<?php if (( $infos["fonction"] == $pl[$i][0]) && $pl[$i][0] != '') { ?> selected="selected"<?php } ?>><?php echo htmlentities($pl[$i][1]) ?></option>
              <?php }
            }?>
        </select>
      </fieldset>
      <fieldset class="selector">
        <legend>Taille salariale :</legend>
        <select id="salaries-selector" name="salaries" class="edit"  onChange="updateListe()">
              <option value=""> - </option>
            <?php for ($i = 1; $i <= $nec; $i++) { ?>
              <option value="<?php echo $nel[$i] ?>"<?php if ((isset($_COOKIE["salaries"]) && $show && $_COOKIE["salaries"] == htmlentities($nel[$i])) || $infos["salaries"] == $nel[$i]) { ?> selected="selected"<?php } ?>><?php echo htmlentities($nel[$i]) ?></option>
            <?php } ?>
        </select>
      </fieldset>
      <fieldset class="selector">
        <legend>Secteur :</legend>
        <select id="secteur-selector" name="secteur" class="edit" onChange="updateListe()">
              <option value=""> - </option>
              <?php
          if(!empty($activity_sectorsList))
            foreach ($activity_sectorsList as $activity_sector) { ?>
            <option value="<?php echo $activity_sector['sector'] ?>"><?php echo to_entities($activity_sector['sector']) ?></option>
          <?php } ?>
        </select>
      </fieldset>
      <fieldset class="check-box">
        <legend>Contacts passés depuis :</legend>
        <select id="placement-time-selector"  onChange="updateListe()">
          <option value=""> - </option>
          <option value="15mn">- 15 minutes</option>
          <option value="30mn">- 30 minutes</option>
          <option value="1h">- 1 heure</option>
          <option value="2h">- 2 heures</option>
          <option value="3h">- 3 heures</option>
          <option value="1j">- 1 jour</option>
          <option value="2j">- 2 jours</option>
          <option value="3j">- 3 jours</option>
        </select>
      </fieldset>
      <fieldset class="check-box">
        <legend>Appels en absence :</legend>
        <label for="unanswered">Oui</label> <input type="checkbox" name="unanswered" id="unanswered" value="1"  onChange="updateListe()" />
      </fieldset>
      <fieldset class="check-box">
        <legend>Nb appels affichés :</legend>
        <select id="shown-call-count"  onChange="updateListe()">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="50">50</option>
        </select/>
      </fieldset>
        <span id="nbTotalAppels" style="margin-left: 20px"></span> appels restants
    </div>
    <?php }else{ ?>
    <div class="title">Nombre d'appels restants</div>
    <span id="nbTotalAppels" style="margin-left: 20px"></span> appels restants
    <?php } ?>
  </div>


  <br />
  <table id="item-list" class="item-list" cellspacing="0" cellpadding="0">
    <thead>
      <tr>
      <th style="width : 60px">Date/Heure</th>
      <th style="width : 60px">Date lead</th>
      <th>Société</th>
      <th>Prénom Nom</th>
      <th style="width : 140px">Fonction</th>
      <th style="width : 140px">Secteur</th>
      <th style="width : 70px">Taille</th>
      <th>Produit demandé</th>
      <th style="width : 90px">Nb d'appels en absence</th>
      <th style="width : 30px"></th>
    </tr>
    </thead>
    <tbody id="content-list">
      <tr class="tr-new"><td class="date" colspan="9"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>
    </tbody>
  </table>

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
                          $('#nbTotalAppels').html(0);
                        }else{
                              for (i = 0; i < data.reponses.length; i++)
                              {
//                                  if(data.reponses[i].nbrDailyCalls < <?php echo DAILY_NB_UNANSWERED_CALLS ?>){
                                      // tr type
                                      var tr = '';
                                      var rdvExists = data.reponses[i].rdvExists ? ' rdvExists' : '';
                                        tr = '<tr class="tr-normal'+rdvExists+'" onmouseover="this.className=\'tr-hover\'" onmouseout="this.className=\'tr-normal'+rdvExists+'\'" onclick="isAlreadyPickedUp('+data.reponses[i].id+', \''+data.reponses[i].id_client+'\', \''+data.reponses[i].id_lead+'\', \''+data.reponses[i].tel+'\');\" >';

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
                                                "	<td class=\"nombre\">" +data.reponses[i].calls_count+"</td>" +
                                                "	<td class=\"nombre\"><img src=\"../ressources/icons/telephone.png\" /></td>" +
                                                "</tr>");
                                       $('#nbTotalAppels').html(data.nbrTotalCalls);

//                                  }
                              }

                        }
	}
  };

  var AJAXHandleProcessCall = {
    type: "GET",
    url: "AJAX_process-call.php",
    dataType: "json",
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      $('#show_error_message').text(textStatus);
    },
    success: function (data, textStatus) {
      if (data.error) {
        $('#show_error_message').text(data.error);
      }
      if (data.result == "ok") {
        var me = this;
        document.location.href = 'tel:'+me.tel;
        window.setTimeout(function(){
          location.href= '<?php echo ADMIN_URL ?>contacts/lead-create.php?idClient='+me.idClient+'&idLead='+me.idLead+'&idCall='+me.idCall;
        }, 100);
      }
    }
  };

  var lastUpdateListTime = 0;
  function updateListe(){

    var date  = new Date();
    var updateListeTO = date.getTime() - lastUpdateListTime;
    if (updateListeTO < 500)
      updateListeTO = 500 - updateListeTO;
    else
      updateListeTO = 0;
      
    setTimeout(function(){

      var tbody = $("#content-list");

      var NB = $('input[name=NB]').val();
      var page = $('input[name=page]').val();
      var lastpage = $('input[name=lastpage]').val();
      var sort = $('input[name=sort]').val();
      var lastsort = $('input[name=lastsort]').val();
      var sortway = $('input[name=sortway]').val();
      var fonction = typeof $('#fonction-selector').val() !== 'undefined' ? $('#fonction-selector').val() : '';
      var salaries = typeof $('#salaries-selector').val() !== 'undefined' ? $('#salaries-selector').val() : '';
      var secteur = typeof $('#secteur-selector').val() !== 'undefined' ? $('#secteur-selector').val() : '';
      var placement_time = typeof $('#placement-time-selector').val() !== 'undefined' ? $('#placement-time-selector').val() : '';
      var shown_call_count = typeof $('#shown-call-count').val() !== 'undefined' ? $('#shown-call-count').val() : '';
      var unanswered = $('#unanswered').attr('checked') ? 1 : 0;

      AJAXHandle.data = "fonction="+fonction+"&salaries="+salaries+"&secteur="+secteur+"&placement-time="+placement_time+"&unanswered="+unanswered+"&shown-call-count="+shown_call_count;
      $.ajax(AJAXHandle);
      
      lastUpdateListTime = date.getTime();
    },updateListeTO);
  }

  var AJAXHandleTestCall = {
    type: "GET",
    url: "AJAX_process-call.php",
    dataType: "json",
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      $('#show_error_message').text(textStatus);
    },
    success: function (data, textStatus) {
      if (data.error) {
        $('#show_error_message').text(data.error);
      }
      if (data.result == 'testNok') {
        updateListe();
        alert('Ce contact est déjà en cours d\'appel');
      }
      else if(data.result == 'testOk') {
        makeCall(this.idCall, this.idClient, this.idLead, this.tel);
      }
    }
  };

  function isAlreadyPickedUp(idCall, idClient, idLead, tel){
      clearInterval(timer);
      AJAXHandleTestCall.data = "id_call="+idCall+"&testPickedUp=1";
      AJAXHandleTestCall.idClient = idClient;
      AJAXHandleTestCall.idCall = idCall;
      AJAXHandleTestCall.idLead = idLead;
      AJAXHandleTestCall.tel = tel;
      $.ajax(AJAXHandleTestCall);
    }

  function makeCall(idCall, idClient, idLead, tel){
     AJAXHandleProcessCall.data = "id_call="+idCall+"&setPickedUp=1";
     AJAXHandleProcessCall.idClient = idClient;
     AJAXHandleProcessCall.idCall = idCall;
     AJAXHandleProcessCall.idLead = idLead;
     AJAXHandleProcessCall.tel = tel;
     $.ajax(AJAXHandleProcessCall);
  }

  updateListe();
  timer = setInterval('updateListe();', 5000);
//-->
</script>
<?php } // end if permission ?>
<?php require(ADMIN."tail.php") ?>