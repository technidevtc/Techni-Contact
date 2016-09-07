<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 10/05/2011

 Mises à jour :

 Fichier : /secure/manager/smpo/load_campaign.php
 Description : Création des campagnes et chargement des fichiers

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$title = 'Cr&eacute;ation de campagnes d\'appels';
$navBar = 'Liste des campagnes';

require(ADMIN . 'head.php');
$lastpage = 100;
$page = 24;

if (!$userChildScript->get_permissions()->has("m-smpo--sm-campaign","r")) {
  print "Vous n'avez pas les droits ad&eacute;quats pour r&eacute;aliser cette op&eacute;ration";
  require(ADMIN."tail.php");
  exit();
}

?>
<div class="titreStandard"><?php echo $title ?></div>
<br />
<div class="bg" style="position: relative">
<link href="HN.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
.blocka {float: left}
.legend-group { font: bold 11px Tahoma, Helvetica, sans-serif; border: 1px solid #000000; padding: 10px; display: inline; float: right }
.legend-label { padding: 2px 10px 2px 15px; font-variant: small-caps; border: 1px solid #000000; }
.not-valid { background-color: #FFFFFF; }
.valid { background-color: #D0FFFF; }
.valid-update { background-color: #E0E0FF; }
.finalized { background-color: #B0FFB0; }
.cancelled { background-color: #FFB0B0; }

#ImportsTable table { min-width: 1000px; }
#ImportsTable table tr.status-nvf { background-color: #FFFFFF; }
#ImportsTable table tr.status-nv { background-color: #FFFFD0; }
#ImportsTable table tr.status-nf { background-color: #FFD0FF; }
#ImportsTable table tr.status-n { background-color: #FFD0D0; }
#ImportsTable table tr.status-vf { background-color: #D0FFFF; }
#ImportsTable table tr.status-v { background-color: #D0FFD0; }
#ImportsTable table tr.status-f { background-color: #D0D0FF; }
#ImportsTable table tr.status-0 { background-color: #FFB0B0; }

#ImportsTable table .column-edit { min-width: 30px; text-align: center; }
#ImportsTable table .column-edit .check { float: left; }
#ImportsTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat; }
#ImportsTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat; }
#ImportsTable table .column-0 { min-width: 150px; text-align: center; }
#ImportsTable table .column-1 { min-width: 130px; text-align: center; }
#ImportsTable table .column-2 { min-width: 130px; text-align: center; }
#ImportsTable table .column-3 { min-width: 80px; text-align: center; }
#ImportsTable table .column-4 { min-width: 80px; text-align: center; }

#ImportWindowShad { z-index: 1; position: absolute; top: -50px; left: 55px; width: 424px; height: 127px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#ImportWindow { z-index: 2; position: absolute; top: -55px; left: 50px; width: 420px; height: 123px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }
#ImportWindowBG { height: 58px; }

#label_file					{ top: 45px; left: 20px; }
#import_file				{ top: 45px; left: 120px; }
#label_advertiser			{ top: 80px; left: 20px; }
#import_advertiser			{ top: 80px; left: 120px; width: 190px; }
#import_advertiser_button	{ top: 80px; left: 314px; width: 82px; }

#Choose-adv { z-index: 3; width: 530px; position: absolute; top: 50px; left: 45px; }
.window-silver { padding: 5px; font: normal 11px Tahoma, Arial, Helvetica, sans-serif; }
.window-silver a { color: #000000; font-weight: normal; }
.window-silver a:hover { font-weight: normal; }

.tab_menu { height: 24px; padding: 0 5px 0 5px; position: relative; top: 1px; }

.tab_menu .tab { float: left; width: 118px; text-align: center; cursor: default; }

.tab_menu .tab_lb_i, .tab_menu .tab_lb_a, .tab_menu .tab_lb_s, .tab_menu .tab_rb_i, .tab_menu .tab_rb_a, .tab_menu .tab_rb_s, .tab_menu .tab_lb_c, .tab_menu .tab_rb_c  { float: left; width: 4px; height: 23px; }
.tab_menu .tab_lb_i { background : url(tab-left-border.gif) repeat-x; }
.tab_menu .tab_lb_a { background : url(tab-active-left-border.gif) repeat-x; }
.tab_menu .tab_rb_i { background : url(tab-right-border.gif) repeat-x; }
.tab_menu .tab_rb_a { background : url(tab-active-right-border.gif) repeat-x; }
.tab_menu .tab_lb_s { height: 24px; background : url(tab-active-left-border.gif) repeat-x;}
.tab_menu .tab_rb_s { height: 24px; background : url(tab-active-right-border.gif) repeat-x; }
.tab_menu .tab_lb_c { height: 24px; background : url(tab-active-left-border.gif) repeat-x;}
.tab_menu .tab_rb_c { height: 24px; background : url(tab-active-right-border.gif) repeat-x; }

.tab_menu .tab_bg_i, .tab_menu .tab_bg_a, .tab_menu .tab_bg_s, .tab_menu .tab_bg_c  { height: 17px; float: left; width: 90px; text-align: left; color: #000000; padding: 6px 10px 0px 10px; white-space: nowrap; }
.tab_menu .tab_bg_i { background: url(tab-bg.gif) repeat-x; }
.tab_menu .tab_bg_a { background: url(tab-active-bg.gif) repeat-x; }
.tab_menu .tab_bg_s { height: 18px; background: url(tab-active-bg.gif) repeat-x; font-weight: bold; }
.tab_menu .tab_bg_c { height: 18px; background: url(tab-active-bg.gif) repeat-x; font-weight: bold; }

.menu-below { border: 1px solid #808080; height: 2px; font-size: 0; border-bottom: none; background-color: #D8D4CD; }
.main { border: 1px solid #808080; background-color: #DEDCD6; }

.search_menu { width: 516px; cursor: default; padding: 3px 6px; border-bottom: 1px solid #808080; display: block; float: left}
.search_menu span { border: 1px solid #DEDCD6; padding: 2px 5px; outline: none; }
.search_menu span.over { border-color: #FFFFFF #808080 #808080 #FFFFFF; }
.search_menu span.down { border-color: #808080 #FFFFFF #FFFFFF #808080; }
.search_menu span.selected { border-color: #808080 #FFFFFF #FFFFFF #808080; }

.body { padding: 2px 4px; background-color: #DEDCD6; border-top: 1px solid #FFFFFF; clear: left}
.body .colg { float: left; width: 258px; margin-right: 5px; }
.body .colc { float: left; width: 257px; }
.body .col-title { cursor: default; font-weight: bold; margin: 2px; }
.body .colg .list { width: 252px; height: 298px; background-color: #FFFFFF; border: 2px inset #808080; margin: 0; padding: 1px; list-style-type: none; overflow: auto; }
.body .colg .list li { cursor: default; white-space: nowrap; }
.body .colg .list li.over { background-color: #316AC5; color: #FFFFFF; }
.body .colg .list li.selected { background-color: #0C266C; color: #FFFFFF; }

.body .colc .infos { position: relative; height: 290px; background-color: #FFFFFF; border: 2px inset #808080; padding: 5px; }

.body .colc .select_label { padding: 0 5px 0 20px; }
.body .colc input.button { top: 275px; left: 5px; width: 243px; }

.DB-bg  { display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: #000000; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=40)"; filter: alpha(opacity=40); opacity:.40 }
.DB { display: none; position: absolute; padding: 10px; font: small-caps bold 13px tahoma, arial, sans-serif; color: #000000; text-align: center; border: 1px solid #cccccc; background: #f4faff }
#LoadFileDB { left: 20px; top: 50px; width: 900px; z-index: 1}
#loadCampaign{width: 700px;}

</style>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
</script>
<script src="Classes.js" type="text/javascript"></script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<div id="PerfReqImports" class="PerfReqLabel"></div>
<div id="ProductGetError" class="InfosError">
<?php
if (isset($_GET['error'])) {
	switch($_GET['error']) {
    case "permissions": print "Erreur Fatale : vous n'avez pas les droits ad&eacute;quats pour r&eacute;aliser cette op&eacute;ration."; break;
    case "file"       : print "Erreur Fatale lors du chargement du fichier<br/>"; break;
    case "fileext"    : print "Erreur Fatale : type de l'extension du fichier non reconnu<br/>"; break;
    case "advertiser" : print "Erreur Fatale : L'annonceur/fournisseur n'est pas reconnu<br/>"; break;
    case "colstitle"  : print "Erreur Fatale : Plusieurs colonnes sont manquantes dans le fichier XLS pour qu'il puisse &ecirc;tre reconnu comme valide<br/>"; break;
    case "importType" : print "Erreur Fatale : type d'import non d&eacute;fini<br />."; break;
    default : print "<br/>"; break;
	}
}
else {
  print "<br/>";
}

/**
 * file upload
 */
if (isset($_POST['load-campaign']) && $_POST['load-campaign'] == 1 && isset($_POST['nomCampaign']) && !empty($_FILES['campaignFile'])) {
  
  if (!$userChildScript->get_permissions()->has("m-smpo--sm-campaign","e")) {
    print "Vous n'avez pas les droits ad&eacute;quats pour r&eacute;aliser cette op&eacute;ration";
    exit();
  }

  if (empty ($_POST['nomCampaign']))
    $errorstring .= '- Nom de la campagne incorrect<br />';

	//Code changed on 19/11/2014 to read the extension 
	//because the file type from the HTTP header doesn't work !
	$extension_name_temp 	= $_FILES['campaignFile']['name'];
	$extension_name 		= end((explode(".", $extension_name_temp))); # extra () to prevent notice

	
  if (is_uploaded_file($_FILES['campaignFile']['tmp_name'])) {
  
	//Code changed on 19/11/2014 to read the extension because 
	//All the files uploaded shows "$_FILES['campaignFile']['type']" => "application/octet-stream"
    /*if ($_FILES['campaignFile']['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
     || $_FILES['campaignFile']['type'] == 'application/vnd.ms-excel'
     || $_FILES['campaignFile']['type'] == 'text/csv') {*/
      
	  if ($_FILES['campaignFile']['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
     || $_FILES['campaignFile']['type'] == 'application/vnd.ms-excel'
     || $_FILES['campaignFile']['type'] == 'text/csv'
	 || strcmp($extension_name,'xls')==0) {
	 
      $campaign = new Campaign(array(
        "nom" => $_POST['nomCampaign'],
        "timestamp" => time())
      );
      $campaign->save();

      require_once '../import/Excel/reader.php';

      if (empty( $errorstring)) {
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP1252');
        $data->read($_FILES['campaignFile']['tmp_name']);

        error_reporting(E_ALL ^ E_NOTICE);

		//Comment add on 19/11/2014
		//This loop is to find the position of the email column
        $hi = array(); // Header Index
        for ($j=1; $j<=$data->sheets[0]['numCols']; $j++) {
          $header = Utils::toDashAz09($data->sheets[0]['cells'][1][$j]);

		 //Code commented on 19/11/2014 
         /* switch($header) {
            case "email":
              $hi['email'] = $j;
              break;
          }*/
		  //Code add on 19/11/2014 to check if this column has a valid email 
		  //Automatically it is a column email
		  if(filter_var($data->sheets[0]['cells'][1][$j], FILTER_VALIDATE_EMAIL)){
		  //if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $data->sheets[0]['cells'][1][$j])) {
			$hi['email'] = $j;
		  }
		  
          $colError = false;
          if (!isset($hi['email'])) {
            $colError = true;
          }
        }//End first Loop on the columns
		
		//Code commented on 19/11/2014 
		//After the upload => Test for the type of file uploaded
		//Get the column number wher is the email
		//Loop for the file rows 
		//Test of the eligibility and calling the CallsCampaign with the id last created and the row mail
        for ($i=2; $i<=$data->sheets[0]['numRows']; $i++) {
          $email = trim($data->sheets[0]['cells'][$i][$hi['email']]);
          // deactivation of eligibility test if wanted
          $eligibilityTest = (!empty ($_POST['eligibilityTest']) && $_POST['eligibilityTest'] == true) ? true : CallsCampaign::isEligible($email);

         
          if(!empty($email) && $eligibilityTest == true){		  
            $callC = new CallsCampaign(array(
              "id_campaign" => $campaign->id,
              "id_client" => $email
            ));
            $callC->save();			
          }//End if test
        }//End for loop
      }
    }
    else {
      $errorstring .= "- Type de fichier incorrect<br />";
    }
  }
  else {
    $errorstring .= "- Probl&egrave;me au chargement du fichier<br />";
  }
}
?>
</div>

<div class="blocka"><a href="javascript: ShowLoadPopup()">Cr&eacute;er une campagne</a></div>
<input type="hidden" name="sort" value="" />
<input type="hidden" name="lastsort" value="" />
<input type="hidden" name="sortway" value="" />
<div class="zero"></div>
<table id="liste_campagnes" class="liste_cmd" border="0" cellpadding="2" cellspacing="0" width="100%">
                <thead>
                        <tr class="tr-titre">
                          <th></th>
                                <th style="min-width:90px;"><a href="javascript: campaignSort('date')">Date cr&eacute;ation</a></th>
                                <th width="250"><a href="javascript: campaignSort('name')">Nom</a></th>
                                <th width="100">Total &eacute;ligibles</th>
                                <th width="100">Nb appels aboutis</th>
                                <th style="min-width:90px;">Nb appels en absence</th>
                                <th>Tx joignabilit&eacute;</th>
                                <th style="min-width:150px;">Nb contacts transfo.</th>
                                <th>Tx transfo</th>
                                <th width="200"><a href="javascript: campaignSort('status')">Statut</a></th>
                                <th width="80"><a href="javascript: campaignSort('callsLeft')">Nb &agrave; joindre</a></th>
                        </tr>
                </thead>
                <tbody id="campaign-list">
                  <tr class="tr-new"><td class="date" colspan="11"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>
                </tbody>
        </table>

<div class="DB-bg"></div>
<div id="LoadFileDB" class="DB">
  <div class="error" id="uploadError"></div><br />
  <form name="loadNewCampaign" method="post" action="" enctype="multipart/form-data">
  Nom de la campagne :
  <input type="text" name="nomCampaign" value=""/>
  <input type="hidden" name="load-campaign" value="1"/><br/>
  <br/>
  S&eacute;lectionnez le fichier &agrave; charger <br/>
   <br/>
  <input type="file" name="campaignFile"  accept="application/xls" /><br/>
   <br/>
   <input type="checkbox" name="eligibilityTest" id="eligibilityTest" <?php echo $_POST['eligibilityTest'] == true ? 'checked="checked"' : '' ?> /> <i><label for="eligibilityTest">D&eacute;sactiver la restriction d'&eacute;ligibilit&eacute;</label> : les contacts pourront &ecirc;tre int&eacute;gr&eacute;s<br />
   m&ecirc;me s'ils ont d&eacute;j&agrave; &eacute;t&eacute; int&eacute;gr&eacute;s &agrave; une campagne d'appels il y a moins d'un mois </i><br/>
   <br/>
   <br/>
   <input type="button" value="Annuler"/> &nbsp; &nbsp; <input type="button" value="Charger"/>
  </form>
</div>
<script type="text/javascript">
  $("#uploadError").hide();
<?php if($errorstring){
  echo 'ShowLoadPopup();';
  echo '$("#uploadError").html(\''.$errorstring.'\');$("#uploadError").show();';
}
if(!empty ($_POST['nomCampaign'])){
  echo '$("input[name=\'nomCampaign\']").val(\''.$_POST['nomCampaign'].'\');';
}
?>
  
//CQDB = Charge load file Dialog Box
  $("#LoadFileDB input[type='button']:first").click(function(){
  $("div.DB-bg").hide();
  $("#LoadFileDB").hide();
  });
  $("#LoadFileDB input[type='button']:last").click(function(){
  $("form[name='loadNewCampaign']").submit();
  });

function ShowLoadPopup(){
  $("#uploadError").html('');
  $("#uploadError").hide();
  $("div.DB-bg").show();
  $("#LoadFileDB").show();
}

var AJAXHandle = {
      url: "AJAX_campaign-liste.php",
      dataType: "json",
      	error: function (XMLHttpRequest, textStatus, errorThrown) {
                var tbody = $("#campaign-list");
			tbody.empty();
                        tbody.append( '<tr class="tr-new"><td class="date" colspan="11"> '+textStatus+'</td></tr>');
	},
	success: function (data, textStatus) {
            var tbody = $("#campaign-list");
              tbody.empty();

              if(data.error){
                tbody.append( '<tr class="tr-new"><td class="date" colspan="11" style="color : red"> '+data.error+'</td></tr>');
              }
              else if(data.reponses == 'vide'){
                tbody.append( '<tr class="tr-new"><td class="date" colspan="11"> Aucune campagne disponible. </td></tr>');
              }else if(data.reponses){

                    for (i = 0; i < data.reponses.length; i++)
                    {
                      // tr type
                      var tr = '';
//                                if(data.reponses[i].error){
//                                  tr = '<tr class="tr-cancelled">';
//                                } else if(data.reponses[i].statut_traitement < 2){
//                                  tr = '<tr class="tr-new" onmouseover="this.className=\'tr-newhover\'" onmouseout="this.className=\'tr-new\'">';
//                                } else{
                        tr = '<tr class="tr-normal" onmouseover="this.className=\'tr-hover\'" onmouseout="this.className=\'tr-normal\'">';
//                                }
          var deleteLink = data.reponses[i].status_value == 0 ? "	<td class=\"date\"><a href=\"javascript:return false;\" onclick=\"if(confirm('Attention, vous allez supprimer une campagne! Confirmez-vous votre choix?'))deleteCampaign("+data.reponses[i].id+");\"><img src=\"../../ressources/images/error.jpg\" alt=\"Supprimer la campagne\" /></a></td>" : '<td></td>';

                      if(data.reponses[i].error){
                         tbody.append(tr + deleteLink+
                            "	<td colspan=\"11\">"+data.reponses[i].error+"</td></tr>");
                      }else{
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

                        tbody.append(
                          tr +
                           deleteLink+
                          "	<td class=\"date\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+date+"</td>" +
                          "	<td class=\"date\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].nom+"</td>" +
                          "	<td class=\"date\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].nbCallsTotal+"</td>" +
                          "	<td class=\"date\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].nbCallsMade+"</td>" +
                          "	<td class=\"date\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].nbCallsInAbsence+"</td>" +
                          "	<td class=\"nombre\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].callsMadeRate+" %</td>" +
                          "	<td class=\"type\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].nbLeadsMade+"</td>" +
                          "	<td class=\"date\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].leadsMadeRate+" %</td>" +
                          "	<td class=\"nombre\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].status_title+"</td>" +
                          "	<td class=\"nombre\" onclick=\"document.location='campaign_spool.php?idCampaign="+data.reponses[i].id+"'\">"+data.reponses[i].nbCallsToDo+"</td>" +
                          "</tr>");
                      }
                    }
              }

//                        if(data.pagination){
//                            var divPagination = $(".listing");
//                                divPagination.empty();
//                                var visible1 ;
//                                var visible2 ;
//                                var visible3 ;
//                                var visible4 ;
//                                var page = parseInt(data.pagination['page']) ;
//                                var lastpage = parseInt(data.pagination['lastpage']) ;
//                                if(page > 2){visible1 = 'visible'}else{visible1 = 'hidden'};
//                                if(page > 1){visible2 = 'visible'}else{visible2 = 'hidden'};
//                                if(page < lastpage){visible3 = 'visible'}else{visible3 = 'hidden'};
//                                if(page < lastpage-1){visible4 = 'visible'}else{visible4 = 'hidden'};
//                                var html = "<span style=\"visibility: "+visible1+"\"><a href=\"javascript: gotoPage(1)\">&lt;&lt;</a></span> "+
//					"<span style=\"visibility: "+visible2+"\"><a href=\"javascript: gotoPage("+(page-1)+")\">&lt;</a> ... |</span> "+
//					"<span style=\"visibility: "+visible2+"\"><a href=\"javascript: gotoPage("+(page-1)+")\">"+(page-1)+"</a> |</span> "+
//					"<span class=\"listing-current\">"+page+"</span> "+
//					"<span style=\"visibility: "+visible3+"\">| <a href=\"javascript: gotoPage("+(page+1)+")\">"+(page+1)+"</a></span> "+
//					"<span style=\"visibility: "+visible3+"\">| ... <a href=\"javascript: gotoPage("+(page+1)+")\">&gt;</a></span> "+
//					"<span style=\"visibility: "+visible4+"\"><a href=\"javascript: gotoPage("+lastpage+")\">&gt;&gt;</a></span> ";
//
//                                divPagination.append(html);
//
//                                $('input[name=page]')[0].value = page;
//                                $('input[name=lastpage]')[0].value = lastpage;
                                $('input[name=sort]')[0].value = data.pagination['sort'];
                                $('input[name=lastsort]')[0].value = data.pagination['lastsort'];
                                $('input[name=sortway]')[0].value = data.pagination['sortway'];
//
//                        }

	}
};

function campaignList(){
// AJAXHandle.data = "idCommande="+idCommande+"&idSupplier="+idSupplier;
AJAXHandle.type = 'GET';
    $.ajax(AJAXHandle);
}

function deleteCampaign(idCampaign){
  AJAXHandle.data = "supprime_campagne=1&campaignID="+idCampaign;
  AJAXHandle.type = 'POST';
    $.ajax(AJAXHandle);
}

function campaignSort(order){
  var sortway = $("input[name=sortway]").val();
  var lastsort = $("input[name=lastsort]").val();
  AJAXHandle.data = 'sort='+order+'&sortway='+sortway+'&lastsort='+lastsort;
  campaignList();
}

campaignList();
</script>
</div>
<?php

require(ADMIN . 'tail.php');

?>
