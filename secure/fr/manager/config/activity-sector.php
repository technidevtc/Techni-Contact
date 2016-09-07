<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 31 mai 2011

 Fichier : /manager/config/activity-sector.php
 Description : Interface de gestion des secteurs d'activités

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = 'Gestion des secteurs qualifiés';

require(ADMIN . 'head.php');

if (!$userChildScript->get_permissions()->has("m-admin--sm-activity-sector","re")) {
  print "Vous n'avez pas les droits adéquats pour réaliser cette opération";
  exit();
}

$q = Doctrine_Query::create()
    ->from('ActivitySector as')
    ->leftJoin('as.Surqualifications ass');

$ActivitySector = $q->fetchArray();

$activity_sectors = $activity_sectorsList = $ActivitySector;
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $activity_sectors);
foreach($activity_sectors as &$sectorList){
  if(is_array($sectorList) && !empty($sectorList))
  foreach($sectorList as &$surqualificationList){
    if(is_array($surqualificationList) && !empty($surqualificationList))
    foreach($surqualificationList as $index => &$surqualification)
    {
      $surqualification['qualification'] = preg_replace('/\r\n|\n\r|\n|\r/', '', $surqualification['qualification']) ;
      $surqualification['qualification'] = htmlspecialchars($surqualification['qualification'], ENT_QUOTES) ;
      $surqualification['keywords'] = preg_replace('/\r\n|\n\r|\n|\r/', '', $surqualification['keywords']) ;
      $surqualification['keywords'] = htmlspecialchars($surqualification['keywords'], ENT_QUOTES) ;
    }
  }
}
$jsonedActivitySectorList = json_encode($activity_sectors);

?>
<div class="titreStandard"><?php echo $title ?></div>
<br />
<style type="text/css">
#affiche_qualification, #affiche_keywords{display: inline-block; vertical-align: top; margin-left: 40px}
#saveKeywords{display: block}

.item-list-table { width: 450px; font: normal 10px verdana, helvetica, sans-serif; border: 1px solid #000000; border-collapse: collapse; background-color: #ffffff }
.item-list-table th { height: 17px; padding: 1px; text-align: center; background: #f0f0f0; vertical-align: middle }
.item-list-table td { height: 17px; padding: 1px; text-align: center; border: 1px solid #000000; border-width: 1px 0; vertical-align: middle }
.item-list-table .tree { width: 20px }
.item-list-table .attr .name-value { text-align: left }
.item-list-table .attr-value .name-value { text-align: left; text-indent: 20px }
.item-list-table .usedCount { width: 110px }
.item-list-table .actions { width: 96px; text-align: center }
.item-list-table .actions .icon { display: inline-block; width: 16px; height: 16px; margin: 0 4px; background-repeat: no-repeat; cursor: pointer }
.item-list-table .actions .icon-keywords { background: url(table_add.png) }
.item-list-table .actions .icon-del { background: url(table_delete.png) }
.item-list-table tr.scat1 { background: #ffffff }
.item-list-table tr.selem1 { display: none; background: #f8f8f8 }
.item-list-table tr.selem1.odd { background: #f8f8f8 }
.item-list-table tr:hover { background: #cccccc!important }
.item-list-table input{border: none}
btn{vertical-align:top}

.qualification{width: 270px; text-align: center}
.naf{width: 40px; text-align: center}
</style>

<?php if($error){ ?>
<div id="ProductGetError" class="error" style="height:30px"><span style="position: relative; top: 8px"><?php echo $error ?></span></div>
<?php }?>
<div class="bg" style="position:relative">
  <form name="activity_sector" method="post">
    <select name="sector" style="vertical-align:top">
      <option>Choisir un secteur</option>
      <?php
      if(!empty ($activity_sectorsList))
        foreach($activity_sectorsList as $activity_sector){
          echo '<option value="'.$activity_sector['id'].'">'.$activity_sector['sector'].'</option>';
        }
      ?>
    </select>
    <div id="affiche_qualification"></div>
    <div id="affiche_keywords"></div>
  </form>
</div>

 <script type="text/javascript">
  <!--
  // surqualification secteurs d'activite
  var sector_list = $.parseJSON('<?php echo $jsonedActivitySectorList ?>');
  
  $('select[name=sector]').change(function(){
    $('input[name=sauve]').remove();
    if(!isNaN(parseInt($('select[name=sector]').val())-1)){
      var table = '<table id="surqualification" class="item-list-table"><thead><th>Surqualification</th><th>NAF</th><th></th></thead><tbody>';
      $.each(sector_list[parseInt($('select[name=sector]').val())-1].Surqualifications, function(){
        table += '<tr id="surqualif-'+this.id+'"><td><input type="text" name="qualif-'+this.id+'"  class="qualification" value="'+this.qualification+'" /><td><input type="text" name="naf-'+this.id+'"  class="naf" value="'+this.naf+'" /></td></td><td class="actions"><div class="icon icon-del" title="supprimer"></div><div class="icon icon-keywords" title="keywords"></div></td></tr>';
      })
      table += '</tbody></table><input type="button" name="add" value="Ajouter"  class="btn ui-state-default ui-corner-all" /><input type="button" name="sauve" value="Sauvegarder" class="btn ui-state-default ui-corner-all" />'
      $('#affiche_qualification').html(table);
    }

    $('input[name=add]').click(function(){
      $('#surqualification tbody').append('<tr id="surqualif-new"><td><input type="text" name="qualif-new" class="qualification" maxlength="255" value="" /></td><td><input type="text" maxlength="6" name="naf-new" class="naf" value="" /></td><td class="actions"></td></tr>');
    });

    $('input[name=sauve]').click(function(){
      var qualifList = [];
      $('.qualification').each(function(){
        var qualifId = $(this).attr('name').split('qualif-');
        qualifId = qualifId[1];
        var Naf = $('.naf[name=naf-'+qualifId+']').val();
        var qualif = [];
        qualif.push({id: qualifId, qualification: $(this).val(), naf : Naf}) ;
        qualifList.push(qualif);
      });
      $.ajax({
        async: true,
        cache: false,
        data: {"params":[{"action":"save","qualifList": qualifList, "sector": $('select[name=sector] option:selected').val()}]},
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Erreur lors de l'enregistrement"); },
        success: function (data, textStatus) { 
          alert("Enregistrement effectué avec succés");
        },
        timeout: 10000,
        type: "POST",
        url: "AJAX_activity-sector.php"
      });
      getSurqualification($('select[name=sector] option:selected').val());
      return false;
    });

    $('#affiche_keywords').empty();
    getSurqualification($('select[name=sector] option:selected').val());
  });

$('.icon-del').live(
  'click', function(){
  var confirmation = confirm('Êtes-vous sûr de vouloir supprimer cette surqualification?');
  if(confirmation){
    var qualifId = $(this).parent().parent().attr('id').split('surqualif-');
    qualifId = qualifId[1];
    $.ajax({
      async: true,
      cache: false,
      data: {"params":[{"action":"delete","qualifId": qualifId}]},
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Erreur lors de la suppression"); },
      success: function (data, textStatus) {
        alert("Suppression effectuée avec succés");
      },
      timeout: 10000,
      type: "POST",
      url: "AJAX_activity-sector.php"
    });
    getSurqualification($('select[name=sector] option:selected').val());
  }
  
  return false;
});

$('.icon-keywords').live(
  'click', function(){
    var qualifId = $(this).parent().parent().attr('id').split('surqualif-')
    qualifId = qualifId[1];
    $.ajax({
        async: true,
        cache: false,
        data: {"params":[{"action":"getKeywords", "qualifId": qualifId}]},
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Echec de récupération des données"); },
        success: function (data, textStatus) {
          var keywords = data.keywords ? data.keywords : '';
          var html = '<div>Liste des mots clés de '+$('input[name=qualif-'+qualifId+']').val()+'</div><textarea name="keywords" cols="50" rows="8" id="qualif-'+qualifId+'">'+keywords+'</textarea><input id="saveKeywords" class="btn ui-state-default ui-corner-all" type="button" value="Sauvegarder" />';
          $('#affiche_keywords').empty().html(html);
        },
        timeout: 10000,
        type: "GET",
        url: "AJAX_activity-sector.php"
      });
  return false;
});

$('#saveKeywords').live(
  'click', function(){
    var qualifId = $('textarea[name=keywords]').attr('id').split('qualif-')
    qualifId = qualifId[1];
    $.ajax({
        async: true,
        cache: false,
        data: {"params":[{"action":"saveKeywords", "qualifId": qualifId, "keywords": $('textarea[name=keywords]').val()}]},
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Echec de l'enregistrement des mots clés"); },
        success: function (data, textStatus) {
          alert('Mots clés enregistrés avec succès')
        },
        timeout: 10000,
        type: "POST",
        url: "AJAX_activity-sector.php"
      });
  return false;
});

function getSurqualification(sector){

  $.ajax({
        async: true,
        cache: false,
        data: {"params":[{"action":"getSurqualification", "sector": sector}]},
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Echec de récupération des données"); },
        success: function (data, textStatus) {
            if(data.retour){
              var html = '';
              $.each(data.retour, function(){
                html += '<tr id="surqualif-'+this.id+'"><td><input type="text" name="qualif-'+this.id+'"  class="qualification" maxlength="255" value="'+this.qualification+'" /><td><input type="text" name="naf-'+this.id+'" maxlength="6" class="naf" value="'+this.naf+'" /></td></td><td class="actions"><div class="icon icon-del" title="supprimer"></div><div class="icon icon-keywords" title="keywords"></div></td></tr>';
              })
              $('#surqualification tbody').empty().html(html);
            }
        },
        timeout: 10000,
        type: "GET",
        url: "AJAX_activity-sector.php"
      });
}

  //-->
</script>

<?php

require(ADMIN . 'tail.php');

?>


