<?php
/*================================================================/

	Techni-Contact V3 - MD2I SAS
	http://www.techni-contact.com

	Auteur : Hook Network SARL - http://www.hook-network.com
	Date de création : 21 Juillet 2011


	Fichier : /includes/managerV3/rdv.php
	Description : gestion des prises de rdv des commerciaux

/=================================================================*/
?>

<div id="rdvDb" title="Enregistrer un rendez-vous" class="db">
  <form name="setRDV" method="post" action="">
    <div class="line">Veuillez sélectionner la date et l'heure à laquelle le client souhaite être rappelé.</div>
    <div class="line">
      <label>Le : </label><input type="text" name="appointment_date" class="datepicker" />
      <label>À :</label>
      <select name="appointment_hours">
       <?php for ($a=0; $a<=23; $a++) : ?>
        <option value="<?php echo $a ?>"><?php echo $a ?></option>
       <?php endfor ?>
      </select> <label>HH</label>
      <select name="appointment_minutes">
       <?php for ($a=0; $a<=59; $a++) : ?>
        <option value="<?php echo $a ?>"><?php echo $a ?></option>
       <?php endfor ?>
      </select> <label>MM</label>
    </div>
    <div class="line">
      <label>Commentaire :</label>
      <textarea name="appointment_comment" cols="50" rows="4"></textarea>
    </div>
    <div class="line">
      <label>Attribuer à :</label>
      <input type="text" name="appointment_operator" />
    </div>
  </form>
</div>

<script type="text/javascript">
var rdvDateFilter = null,
    triggerRdvTO,
    rdvSourceLabels = <?php echo json_encode(Rdv::$_source_label) ?>;

function triggerRdv(){
  $.ajax({
    type: "GET",
    data: "action=getList&relationType=all",
    dataType: "json",
    url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_rdv.php",
    cache: false,
    success: function(data) {
      if (data.error) {
        $("#rdvLayerList ul").html("<li><b>Erreur à la recherche des rendez-vous : "+data.error+"</b></li>");
      } else {
        $("#rdvLayerList ul").html("");
        var rdvShown = 0;
        if (data.reponse == "liste vide") {
          rdvShown = 0;
        } else {
          $.each(data.reponse, function(index){
            if (!rdvDateFilter || (this.timestamp_call >= rdvDateFilter && this.timestamp_call < rdvDateFilter+86400)) {
              rdvShown++;
              var origin = "";
              this.type_relation = this.type_relation|0;
              switch (this.type_relation) {
                case 1:
                case 2:
                  origin = this.id_campaign != 0 ? "la campagne : "+this.nom_campaign : "la pile d'appels";
                  break;
                case 3:
                case 4:
                case 5:
                  origin = rdvSourceLabels[this.type_relation];
                  break;
              }
              $("<li>").html(
                "<span class=\"icon telephone\" data-action=\"call\"></span>"+
                "<b>À rappeler "+HN.TC.get_formated_datetime(this.timestamp_call, " à ")+"</b><br />"+
                "<b>"+this.coordInfo.societe+"</b><span class=\"icon status-online\" data-action=\"goto-client\"></span><br />"+
                this.coordInfo.prenom+" "+this.coordInfo.nom+"<br />"+
                (this.type_relation === 5 ? "Devis n°"+this.id_relation+" <span class=\"icon eye\" data-action=\"goto-estimate\"></span><br />" : "")+
                "Commentaire : <pre>"+HN.toEntities(this.comment)+"</pre><br />"+
                "Créé par "+this.nom_operator+", issu de "+origin
              ).data("rdv", this).appendTo("#rdvLayerList ul");
            }
          });
        }
        if (rdvShown === 0) {
          $("#rdvLayerList ul").append("<li><b>Aucun RDV n'est actuellement prévu</b></li>");
          $("#rdvHelperLayer").hide();
        } else {
          var nextRdv = data.reponse[0];
          $("#rdvHelperLayer").show().html(data.reponse.length+" RDV restant(s) - Prochain RDV : "+nextRdv.coordInfo.societe+" le "+HN.TC.get_formated_datetime(nextRdv.timestamp_call, " à "));
        }
      }
    }
  });
  clearTimeout(triggerRdvTO);
  triggerRdvTO = setTimeout(triggerRdv, 60000);
}

function makeRdvCall(tel, idRdv, loginClient, type_relation, id_relation, id_campaign, id_call){
  deleteRDV(idRdv).done(function(){
    //window.open("dial:"+tel, "_blank");
    var ids = "",
        url = "<?php echo ADMIN_URL ?>";
    ids += id_campaign != 0 ? "&idCampaign="+id_campaign : "";
    ids += id_call != 0 ? (id_campaign == 0 ? "&idCall="+id_call : "&idCallCampaign="+id_call) : "";
    switch (type_relation|0) {
      case 1:
        url += "contacts/lead-create.php?idClient="+loginClient+"&idLead="+id_relation+ids;
        break;
      case 2:
        url += "contacts/lead-create.php?idClient="+loginClient+ids;
        break;
      case 3:
      case 4:
        url += "clients/index.php?idClient="+loginClient+ids;
        break;
      case 5:
        url += "estimates/estimate-detail.php?id="+id_relation+ids;
        break;
    }
    location.href = url;
  });
}

function deleteRDV(idRdv){
  return $.ajax({
    type: "POST",
    data: { idRDV: idRdv, action: "deleteRDV" },//"idRDV="+idRdv+"&action=deleteRDV",
    dataType: "json",
    url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_rdv.php",
    success: function(data) {
      if (data.error) {
        $("#errorRdv").html("Erreur à la suppression du rendez-vous : "+data.error+" ");
      }
    },
    error: function(data){
      $("#errorRdv").html("Erreur à la suppression du rendez-vous : "+data.error+" ");
    }
  });
}

$(document).ready(function(){
  
  // init datepicker
  $("#rdvLayerFilterInput").datepicker($.datepicker.regional['fr']);
  
  // layer filter button
  $("#rdvLayerFilterBtn").on("click", function(){
    rdvDateFilter = HN.TC.get_timestamp($("#rdvLayerFilterInput").val());
    if (!isNaN(rdvDateFilter))
      triggerRdv();
    else
      rdvDateFilter = null;
  });
  
  // layer actions buttons
  $("#rdvLayerList ul").on("click", "li [data-action]", function(){
      var rdv = $(this).closest("li").data("rdv");
      switch ($(this).data("action")) {
        case "call":
          makeRdvCall(rdv.coordInfo.tel, rdv.id, rdv.coordInfo.login, rdv.type_relation, rdv.id_relation, rdv.id_campaign, rdv.id_call);
          break;
        case "goto-client":
          location.href = "<?php echo ADMIN_URL ?>clients/index.php?idClient="+rdv.id_relation;
          break;
        case "goto-estimate":
          location.href = "<?php echo ADMIN_URL ?>estimates/estimate-detail.php?id="+rdv.id_relation;
          break;
      }
    })
  
  // tab toggle
  $("#rdvLayerOnglet").toggle(function(){
      $("#rdvLayer").animate({"left": "+="+$("#rdvLayerList").width()+"px"}, "fast");
    },
    function(){
      $("#rdvLayer").animate({"left": "-="+$("#rdvLayerList").width()+"px"}, "fast");
    }
  );
  
  // rdv db
  $("#rdvDb").dialog({
    width: 500,
    autoOpen: false,
    modal: true,
    buttons: {
      "Annuler": function(){
        $(this).dialog("close");
      },
      "Créer RDV": function(){
        var $db = $(this),
            vars = $db.data("vars"),
            $inputs = $db.find("input, select, textarea"),
            date = $inputs.filter("[name='appointment_date']").val(),
            hours = $inputs.filter("[name='appointment_hours']").val(),
            minutes = $inputs.filter("[name='appointment_minutes']").val(),
            comment = $inputs.filter("[name='appointment_comment']").val(),
            operator = $inputs.filter("[name='appointment_operator']").val();
        
        if (date === "") {
          alert("La date doit être renseignée");
          return false;
        }
        var rdvTime = HN.TC.get_date_object(date);
        if (hours !== "")
          rdvTime.setHours(hours);
        if (minutes !== "")
          rdvTime.setMinutes(minutes);
          
        if (vars.clientId !== "") {
          $.ajax({
            type: "POST",
            data: {
              timestamp: HN.TC.get_timestamp(rdvTime),
              action: "createRDV",
              relationType: vars.relationType,
              relationId: vars.relationId,
              callId: vars.callId|0,
              campaignId: vars.campaignId|0,
              commentaire: comment,
              idClient: vars.clientId,
              operator: operator
            },
            dataType: "json",
            url: HN.TC.ADMIN_URL+"ressources/ajax/AJAX_rdv.php",
            success: function(data) {
              if (data.error) {
                alert("Erreur à la création du rendez-vous : "+data.error);
              } else {
                $db.dialog("close");
                triggerRdv();
                if (vars.onSuccess) {
                  vars.onSuccess();
                }
              }
            }
          });
        } else {
          alert("Ce devis n'a aucun client d'attribuer: impossible de créer le rendez-vous");
          return false;
        }
      }
    }
  });
  
  // rdv db datepicker
  $("#rdvDb input[name='appointment_date']").datepicker($.datepicker.regional['fr']).datepicker("option", "minDate", 0);
  
  // rdv fb operator autocomplete field
  var rdvDbOperatorACF = new HN.TC.AutoCompleteField({
    field: "#rdvDb input[name='appointment_operator']",
    feedFunc: function(val, cb){
      var q = HN.TC.AjaxQuery.create()
        .select("bou.id, bou.name")
        .from("BoUsers bou")
        .where("bou.active = ?", 1);
      if ($.isNumeric(val)) {
        var id_iv = HN.TC.AjaxQuery.getQueryNumIntervals("bou.id", val, HN.TC.AjaxQuery.BOU_MAX_ID);
        q.andWhere(id_iv[0]+" OR bou.name like ?", $.merge(id_iv[1], [val+"%"]));
      } else {
        q.andWhere("bou.name like ?", [val+"%"]);
      }
      q.limit(10).execute(function(data){
        var cb_data = [];
        for (var i=0; i<data.length; i++)
          cb_data.push([data[i].id, data[i].name]);
        cb(cb_data);
      });
    },
    colToGet: 1
  });
  $("#rdvDb").data("operatorACF", rdvDbOperatorACF);
  
  // init layer
  triggerRdv();
});
</script>
