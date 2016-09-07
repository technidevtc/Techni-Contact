<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	header('Location: '.ADMIN_URL.'login.html');
	exit();
}

$newMiniStore = false;
$error = array();
if (isset($_POST['id'])) {
  $id = preg_match("/^[1-9]?[0-9]*$/", $_POST['id']) ? $_POST['id'] : null;
  $ms = new MiniStore($id);
  if (empty($id)) $newMiniStore = true;
  
  if (!$user->get_permissions()->has("m-mark--sm-mini-stores","e")) {
    $error["rights"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
  } else {
    $ms->name = isset($_POST['name']) ? substr(trim($_POST['name']), 0, 255) : "";
    $ms->fastdesc = isset($_POST['fastdesc']) ? substr(trim($_POST['fastdesc']), 0, 255) : "";
    $ms->desc = isset($_POST['desc']) ? trim($_POST['desc']) : "";
    $ms->desc_listing = isset($_POST['desc_listing']) ? trim($_POST['desc_listing']) : "";
    $ms->type = isset($_POST['type']) ? trim($_POST['type']) : "";
    $ms->active = isset($_POST['active']) ? 1 : 0;
    $ms->standalone = isset($_POST['standalone']) ? 1 : 0;
    if (isset($_POST['home']) && $_POST['home']) {
        if ($ms->home == 0) {
          $maxHomeIndent = Doctrine_Query::create()
            ->select('MAX(home)')
            ->from('MiniStores')
            ->fetchArray();
          if ($maxHomeIndent[0]['MAX'] < __MAX_MINI_STORE_HOME__)
            $ms->home = $maxHomeIndent[0]['MAX']+1;
          else
            $alertMess = 'Le nombre maximal de mini boutiques autorisée ('.__MAX_MINI_STORE_HOME__.') pour la présentation en home page est atteint.';
        }
    } else {
        if (!$newMiniStore) {
          if ($ms->home != 0) {
            $listHomedMS = Doctrine_Query::create()
              ->update('MiniStores')
              ->set('home', 'home - 1')
              ->where('home > ?', $ms->home)
              ->execute();
          }
        }
        $ms->home = 0;
      }
    $ms->espace_thematique = isset($_POST['espace_thematique']) ? 1 : 0;
    
    if ($ms->name == "") $error['name'] = true;
    $ms->ref_name = Utils::toDashAz09($ms->name);
    if ($ms->fastdesc == "") $error['fastdesc'] = true;
    if ($ms->type != "cat") $ms->type = "pdt";

    if (!$ms->updateItems($_POST['itemString'])) $error['itemString'] = true;
    
    if (empty($error)) {
      $ms->save();
      $id = $ms->id;
      $newMiniStore = false;
      
      $mini_store = Doctrine_Query::create()
        ->select('m.id, mass.id, mas.id')
        ->from('MiniStores m')
        ->leftJoin('m.activity_sector_surqualifications mass')
        ->leftJoin('mass.ActivitySector mas')
        ->where('m.id = ?', $id)
        ->fetchOne();
      
      // index the new id's
      $new_assl = array();
      foreach ($_POST['marketing_links'] as $ml) {
        $new_assl[$ml['surqualification']] = true;
      }
      
      // index the old id's
      $old_assl = array();
      foreach ($mini_store->activity_sector_surqualifications as $ass)
        $old_assl[$ass->id] = true;
      
      // remove the deleted id's
      foreach ($mini_store->activity_sector_surqualifications as $k => $ass) {
        if (!isset($new_assl[$ass->id]))
          $mini_store->activity_sector_surqualifications->remove($k);
      }
      
      // create and calculate the new ones
      $new_ass = array_keys(array_diff_key($new_assl, $old_assl));
      $mini_store->link('activity_sector_surqualifications', $new_ass);
      
      //pp($mini_store->toArray());
      $mini_store->save();
      $marketing_links = $_POST['marketing_links'];
      //pp($_POST['marketing_links']);
      //header("Location: mini-stores.php");
      //exit();
    }
  }

} else { // no POST['id']
  $id = preg_match("/^[1-9]?[0-9]*$/", $_GET['id']) ? $_GET['id'] : (strtolower($_GET['id']) == "new" ? "new" : "");
  if ($id == "new") {
    $ms = new MiniStore();
    $ms->create();
    $ms->save();
    $newMiniStore = true;
  } else {
    if ($id == "") {
      header("Location: ".ADMIN_URL."mini-stores/mini-stores.php");
    } else {
      $ms = new MiniStore($id);
      $mini_store = Doctrine_Query::create()
        ->select('m.id, mass.id, mas.id')
        ->from('MiniStores m')
        ->leftJoin('m.activity_sector_surqualifications mass')
        ->leftJoin('mass.ActivitySector mas')
        ->where('m.id = ?', $id)
        ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
      $marketing_links = array();
      foreach ($mini_store['activity_sector_surqualifications'] as $ass)
        $marketing_links[] = array('sector' => $ass['ActivitySector']['id'], 'surqualification' => $ass['id']);
    }
  }
}

$q = Doctrine_Query::create()
  ->select('id, name, home as rang')
  ->from('MiniStores')
  ->where('active = 1')
  ->andWhere('standalone = 0')
  ->andWhere('home != 0')
  ->orderBy('home ASC')
  ->fetchArray();
$MSHomeSort = json_encode($q);

// activity sector with surqualification
// order = put "Autre" activities at the end of the list
$asList = Doctrine_Query::create()
  ->select('as.id, as.sector, ass.id, ass.qualification')
  ->from('ActivitySector as')
  ->innerJoin('as.Surqualifications ass')
  ->orderBy('as.sector ASC, ass.qualification ASC')
  ->fetchArray();
$asListByAsId = $asListOthers = array();
foreach ($asList as $as) {
  if (strtolower(substr($as['sector'],0,5)) != "autre")
    $asListByAsId[$as['id']] = $as;
  else
    $asListOthers[] = $as;
}
foreach ($asListOthers as $as) // array_merge change back indexes to numeric so we append it manually
  $asListByAsId[$as['id']] = $as;

$title = ($newMiniStore ? "Ajout d'une nouvelle mini-boutique" : "Détail de la mini-boutique &laquo ".$ms->name." &raquo")." (".$ms->id.")";
require(ADMIN."head.php");
?>
<link type="text/css" rel="stylesheet" href="HN.css"/>
<link type="text/css" rel="stylesheet" href="mini-store.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.DialogBox.blue.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.MISM.blue.css">
<?php if (isset($error["rights"])) { ?><div class="error"><?php echo $error["rights"] ?></div><?php } ?>
<script type="text/javascript" src="AJAXclasses.js"></script>
<script type="text/javascript" src="AJAXmodules.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>
<script type="text/javascript" src="mini-store.js"></script>
<script type="text/javascript">
var errorFields = <?php echo json_encode(array_keys($error)) ?>;
var defaultType = "<?php echo $ms->type ?>";
var itemsAsJSON = <?php echo $ms->getItemsAsJSON() ?>;
var MSHomeSort = <?php echo $MSHomeSort ?>;
<?php if(!empty($alertMess)) echo 'alert(\''.$alertMess.'\');'; ?>
</script>
<div class="bg">
  <a href="<?php echo ADMIN_URL; ?>mini-stores/mini-stores.php">&lt;&lt; Retourner à la liste des mini boutiques</a><br />
  <br />
  <form name="msForm" method="post" action="mini-store.php?id=<?php echo $ms->id ?>">
  <div class="section">
    <input type="hidden" name="id" value="<?php echo $ms->id ?>"/>
    <input type="hidden" name="itemString" value="<?php echo $ms->getItemsAsString() ?>"/>
    <div class="line"><label for="name">Nom :</label></td><td><input type="text" name="name" value="<?php echo $ms->name ?>" class="edit"/></div>
    <div class="line"><label>Nom référence :</label></td><td class="text"><?php echo $ms->ref_name ?></div>
    <div class="line"><label>URL de la mini-boutique :</label></td><td class="text"><?php echo URL."miniboutiques/".$ms->id."-".$ms->ref_name.".html" ?></div>
    <div class="line"><label for="fastdesc">Description simple :</label></td><td><input type="text" name="fastdesc" value="<?php echo $ms->fastdesc ?>" class="edit"/></div>
    <div class="line"><label for="desc">Description complète (HTML) :</label></td><td><textarea name="desc" rows="7"><?php echo $ms->desc ?></textarea></div>
    <div class="line"><label for="desc_listing">Texte de description<br />listing Espace thématique :</label></td><td><textarea name="desc_listing" rows="7"><?php echo $ms->desc_listing ?></textarea></div>
    <div class="line"><label for="type">Type :</label></td><td><input type="radio" name="type" value="pdt"<?php if (empty($ms->type) || $ms->type == "pdt") { ?> checked="checked"<?php } ?>/>Produits<input type="radio" name="type" value="cat"<?php if ($ms->type == "cat") { ?> checked="checked"<?php } ?>/>Familles</div>
    
    <div id="pic-selection">
      <div id="PPSDB">
        <iframe src="<?php echo ADMIN_URL ?>mini-stores/mini-store-pics.php?id=<?php echo $_GET["id"] ?><?php echo ($_GET["type"]=="add_adv" || $_GET["type"]=="edit_adv") ? "&type=adv" : "" ?>" width="100%" frameborder="0" height="80"></iframe>
      </div>
      <div class="intitule">Images mini store</div>
      <div id="box-pic" class="grey-block"></div>
      <a href="#" class="btn-red fl" id="btn-add-pic">Ajouter une image mini-store</a>
      <div class="zero"></div>
    </div>
    
    <table class="ms-form" cellspacing="0" cellpadding="0">
      <tbody>
        <tr><td><label for="active">Active :</label></td><td><input type="checkbox" name="active"<?php if ($ms->active) { ?> checked="checked"<?php } ?>/></td></tr>
        <tr><td><label for="home">Home :</label></td><td><input class="fl" type="checkbox" name="home"<?php if ($ms->home) { ?> checked="checked"<?php } ?>/> <div class="fr ui-state-default padding-2" id="ms_carrousel_sort">Ordre d'affichage carrousel</div><input type="hidden" name="homeSortListString" value=""/></td></tr>
        <tr><td><label for="standalone">Ad hoc :</label></td><td><input type="checkbox" name="standalone"<?php if ($ms->standalone) { ?> checked="checked"<?php } ?>/></td></tr>
        <tr><td><label for="espace_thematique">Espace thématique :</label></td><td><input type="checkbox" name="espace_thematique"<?php if ($ms->espace_thematique) { ?> checked="checked"<?php } ?>/></td></tr>
        <tr><td><label>Date de création :</label></td><td class="text"><?php echo ($newMiniStore ? "" : date("Y/m/d à H:i:s", $ms->create_time)) ?></td></tr>
        <tr><td><label>Dernière modification :</label></td><td class="text"><?php echo ($newMiniStore ? "" : date("Y/m/d à H:i:s" ,$ms->edit_time)) ?></td></tr>
      </tbody>
    </table>
    <br/>
    
    <div id="products-selection-section">
      <input type="hidden" name="pdtListString" value=""/>
      <div class="title">Produits sélectionnés</div>
      <div class="error" id="pdtListInvalid" style="display: none"></div>
      <table class="item-list-table" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th class="catID">ID Famille</th>
            <th class="id">ID Produit</th>
            <th class="name">Nom Produit</th>
            <th class="actions"></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <input id="btn-select-products" class="bouton" type="button" value="Editer la liste des produits"/>
      <input id="btn_input_products" class="bouton" type="button" value="Saisir une liste de produits"/>
    </div>
    
    <div id="categories-selection-section">
      <input type="hidden" name="catListString" value=""/>
      <label>Familles sélectionnées</label>
      <div class="error" id="catListInvalid" style="display: none"></div>
      <table class="item-list-table" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th class="id">ID</th>
            <th class="name">Familles</th>
            <th class="actions"></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <input id="btn-add-category" class="bouton" type="button" value="Ajouter une famille"/>
    </div>
    <br />
    
    <div>
      <input type="hidden" name="marketing_link" value="" />
      <label>Liaison marketing</label>
      <ul id="marketing-link-list" class="select-list">
      </ul>
      <button id="btn-add-marketing-link" class="bouton">Ajouter une typologie utilisateur</button>
    </div>
    <br/>
    
    <input type="submit" class="bouton" value="Enregistrer les modifications"/>
  </div>
  </form>
</div>
<div id="CSDB"></div>
<div id="MPSDB"></div>
<div id="prod_fast_populating_dialog" title="Copier une liste de produits dans le champ ci-dessous"></div>
<div id="home_carrousel_sort_dialog" title="Ordre d'affichage carrousel">
  <div id="carrousel-selection-section">
    <div class="title">Mini boutiques sélectionnées</div>
    <div class="error" id="mshListInvalid" style="display: none"></div>
      <table class="item-list-table" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th class="id">ID</th>
            <th class="name">Mini boutique</th>
            <th class="actions"></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <input id="btn-carrousel-sort" class="bouton" type="button" value="Enregistrer"/>
  </div>
</div>
<script type="text/javascript">
  if (!HN.TC) HN.TC = {};
  if (!HN.TC.BO) HN.TC.BO = {};
  $(function(){
    $("#btn-add-pic").click(function(){
      $('#PPSDB div.message').html('');
      HN.TC.BO.PPSDB.Show();
      return false;
    });
    HN.TC.BO.PPSDB = new HN.Mods.DialogBox("PPSDB");
    HN.TC.BO.PPSDB.setTitleText("Choisir une image mini store (JPEG)");
    HN.TC.BO.PPSDB.setMovable(true);
    HN.TC.BO.PPSDB.showCancelButton(true);
    HN.TC.BO.PPSDB.showValidButton(true);
    HN.TC.BO.PPSDB.setValidFct(function() {
      HN.TC.BO.refreshMiniStoreImages(<?php echo $_GET["id"] ?>);
      HN.TC.BO.PPSDB.Hide();
    });
    //HN.TC.BO.PPSDB.setShadow(true);
    HN.TC.BO.PPSDB.Build();
    
    //PPB = new HN.Mods.PreviewPictureBox("PPB");
    
    var deleteMiniStoreImage = function (msID, type) {
      /*var s = "";
      if (arguments.length < 2) s += "1";
      else for (var i = 1; i < arguments.length; i++) s += (i>1?",":"") + arguments[i];*/
      $.ajax({
        async: true,
        cache: false,
        data: "action=delpics&msID="+msID+"&type="+type,
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading MiniStores Data"); },
        success: function (data, textStatus) {
          if (data.msID)
            HN.TC.BO.refreshMiniStoreImages(data.msID);
        },
        type: "GET",
        url: "AJAX_mini-store-pics.php"
      });
    };
    
    HN.TC.BO.refreshMiniStoreImages = function (msID) {
      $.ajax({
        async: true,
        cache: false,
        data: "action=getpics&msID="+msID,
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading MiniStores Data"); },
        success: function (data, textStatus) {
          var ul = document.createElement("ul");
          $(data.pics).each(function(index){
            var li = document.createElement("li");
            $(li).attr('id','pic_nbr'+(i+1));
            var div = document.createElement("div");
            div.className = "pic-block";
            //div.srcZoom = data.pics.home+"?t="+(new Date).getTime();;
            li.appendChild(div);
              var img = document.createElement("img");
              img.src = data.pics[index]['url']+"?t="+(new Date).getTime();
              img.alt = "";
              //img.onclick = function(){ alert(this.parentNode.srcZoom); /*PPB.show(data.pics[i].zoom); */};
              div.appendChild(img);
              var a = document.createElement("a");
              a.setAttribute("href", "#"+data.msID+"-"+data.pics[index]['type']);
              a.className = "btn-red btn-pic-del";
              a.innerHTML = "supprimer";
              a.onclick = function(){
                var parts = this.getAttribute("href").split("-");
                var msID = parts[0].substr(1, this.href.length);
                var type = parts[1];
                deleteMiniStoreImage(msID, type);
                return false;
              };
              ul.appendChild(li);
              div.appendChild(a);
          });
          $("#box-pic").empty().get(0).appendChild(ul);
          var divClear = document.createElement("div");
          divClear.className = "zero";
          $("#box-pic").get(0).appendChild(divClear);
          /*$("#box-pic ul").attr('id', 'sortable');
          $( "#sortable" ).sortable({
                  revert: true,
                  update: function(){
                    $('#btn-reorder').show();
                  }
          });*/
        },
        type: "GET",
        url: "AJAX_mini-store-pics.php"
      });
    };
    HN.TC.BO.refreshMiniStoreImages(<?php echo $_GET["id"] ?>);
  });
  
  $('input[name=standalone],input[name=espace_thematique]').click(function(){
    if($(this).attr('name') == 'standalone')
      $('input[name=espace_thematique]').removeAttr('checked');
    else if($(this).attr('name') == 'espace_thematique')
      $('input[name=standalone]').removeAttr('checked');
  })
  
  $('#home_carrousel_sort_dialog').dialog({
    width: 600,
    autoOpen: false,
    modal: true,
    draggable: true,
    resizable: true
  });
 
  $('#ms_carrousel_sort').click(function(){
    $.ajax({
      async: true,
      cache: false,
      data: "action=getorder",
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading MiniStores Data"); },
      success: function (data, textStatus) {
            /*  if (data.msOrder)
                      ILThomeSort.setData(data.msOrder);*/
      },
      type: "GET",
      url: "AJAX_mini-store-home-sort.php"
    })
    
    HN.TC.BO.MS.Init('homeSort');
    
    $('#btn-carrousel-sort').live('click', function(){
      var MSNewOrder = [];
      $('#home_carrousel_sort_dialog #carrousel-selection-section table.item-list-table tbody td.id').each(function(index){
        MSNewOrder[index] = $(this).text();
      })
      $.ajax({
        async: true,
        cache: false,
        data: "action=reorder&idList="+MSNewOrder,
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading MiniStores Data"); },
        success: function (data, textStatus) {
                if (data.data)
                        alert(data.data);
        },
        type: "GET",
        url: "AJAX_mini-store-home-sort.php"
      });
    })
  });
  
  // marketing link
  var asListByAsId = <?php echo json_encode($asListByAsId) ?>,
      marketing_links = <?php echo json_encode($marketing_links) ?>,
      $mll = $("#marketing-link-list");
  
  function add_marketing_link(as_id, ass_id) {
    var i = $mll.children("li").length,
        html = "<li>"+
                 "<select name=\"marketing_links["+i+"][sector]\">";
    for (var asi in asListByAsId)
      html += "<option value=\""+asi+"\">"+asListByAsId[asi].sector+"</option>";
    html += "</select>"+
            "<select name=\"marketing_links["+i+"][surqualification]\"></select>"+
            "<div class=\"icon delete\" title=\"Supprimer cette typologie\"></div>"+
          "</li>";
    $mll.append(html);
    var $as = $mll.find("li:last-child > select:first-child");
    if (as_id)
      $as.val(as_id);
    $as.change();
    if (ass_id)
      $as.next().val(ass_id);
  }
  
  $("#btn-add-marketing-link").on("click", function(){
    add_marketing_link();
    return false;
  });
  
  $mll.on("change keyup", "li > select:first-child", function(){
    var $as = $(this),
        as = asListByAsId[$as.val()],
        html = "";
    for (var assi=0; assi<as.Surqualifications.length; assi++) {
      var ass = as.Surqualifications[assi];
      html += "<option value=\""+ass.id+"\">"+ass.qualification+"</option>";
    }
    $as.next().html(html);
  });
  $mll.on("click", "li > .delete", function(){
    $(this).closest("li").remove();
  });
  
  for (var k=0; k<marketing_links.length; k++)
    add_marketing_link(marketing_links[k]['sector'], marketing_links[k]['surqualification']);
  
</script>

<?php require(ADMIN."tail.php"); ?>
