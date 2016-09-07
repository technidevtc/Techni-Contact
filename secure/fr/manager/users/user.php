<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}


$title = "Gestion des utilisateurs";
$navBar = "<a href=\"users.php\" class=\"navig\">Gestion des utilisateurs</a> &raquo; Détail d'un utilisateur";
require(ADMIN."head.php");

$fntl = BOFunctionality::get("order by name asc"); // FuncTioNaLiTyList

$newUser = false;
$error = array();
if (isset($_POST["id"])) {
  $id = preg_match("/^[1-9]?[0-9]*$/", $_POST["id"]) ? $_POST["id"] : null;

  
  $u = new BOUser($id);
  $perms = $u->get_permissions();
  if (empty($id))
    $newUser = true;
    
  if (!$user->get_permissions()->has("m-admin--sm-users","e") || ($id == 0 && $user->id != 0)) { // only HN can edit HN's rights
    $error["rights"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
  } else {
    $gmailInfos = $u->getGmailInfos();
    
    $u->name = isset($_POST["name"]) ? substr(trim($_POST["name"]), 0, 255) : "";
    $u->login = isset($_POST["login"]) ? substr(trim($_POST["login"]), 0, 255) : "";
    $pass = isset($_POST["pass"]) ? trim($_POST["pass"]) : "";
    $u->email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $gmailLogin = isset($_POST["gmailLogin"]) ? trim($_POST["gmailLogin"]) : "";
    $gmailPass = isset($_POST["gmailPass"]) ? trim($_POST["gmailPass"]) : "";
    $u->phone = isset($_POST["phone"]) ? trim($_POST["phone"]) : "";
    $u->help_msg = isset($_POST["help_msg"]) ? trim($_POST["help_msg"]) : "";
    $u->email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $u->active = isset($_POST["active"]) ? 1 : 0;
    
    if ($u->name == "") $error["name"] = true;
    if ($u->login == "") $error["login"] = true;
    if ($u->email == "") $error["email"] = true;
    
    if ($pass != "" || $u->pass == "") { // pass check if present or no pass already set
      if (!preg_match("/^(?=.{6,})[\w@#$%^&+-=]+$/", $pass)) {
        $error["pass"] = true;
      } else {
        $u->pass = md5($pass);
        $u->setGmailInfos($gmailLogin, $gmailPass);
      }
    } elseif ($gmailInfos['login'] != $gmailLogin || $gmailInfos['pass'] != $gmailPass) {
      $u->setGmailInfos($gmailLogin, $gmailPass);
    }
    
	//Add or remove functionnalities
    foreach($fntl as $fnt) {
		//Read
		if (isset($_POST["fnt_r_".$fnt["id"]])) $perms->add($fnt["id"], "r");
		else $perms->remove($fnt["id"], "r");
		
		//Edit
		if (isset($_POST["fnt_e_".$fnt["id"]])) $perms->add($fnt["id"], "e");
		else $perms->remove($fnt["id"], "e");
		
		//Delete
		if (isset($_POST["fnt_d_".$fnt["id"]])) $perms->add($fnt["id"], "d");
		else $perms->remove($fnt["id"], "d");
		
		//Lock
		if (isset($_POST["fnt_l_".$fnt["id"]])) $perms->add($fnt["id"], "l");
		else $perms->remove($fnt["id"], "l");
		
		//Export
		if (isset($_POST["fnt_x_".$fnt["id"]])) $perms->add($fnt["id"], "x");
		else $perms->remove($fnt["id"], "x");
		
    }
    
    if (empty($error)) {
      $u->save();
      $id = $u->id;
      $newUser = false;
      $gmailInfos = $u->getGmailInfos();
      //header("Location: users.php");
      //exit();
    }
		if(!empty($_GET['id'])){
			$id_user = $_GET['id'];
		}else {
			$sql_max  = "SELECT MAX(id) as total FROM bo_users ";
			$req_max  = mysql_query($sql_max);
			$data_max = mysql_fetch_object($req_max);
			$id_user  = $data_max->total;
		}
		
		if(isset($_POST["appels_commerciale"])){
			$appels_commerciale = $_POST["appels_commerciale"];
		}else $appels_commerciale = '';
		
		if(isset($_POST["nbr_jrs_relance"])){
			$nbr_jrs_relance = $_POST["nbr_jrs_relance"];
		}else $nbr_jrs_relance = '';
		if(isset($_POST["nbr_jrs_apres_expedition"])){
			$nbr_jrs_apres_expedition = $_POST["nbr_jrs_apres_expedition"];
		}else $nbr_jrs_apres_expedition = ''; 
		
		if(isset($_POST["phone_popup"])){
			$phone_popup = $_POST["phone_popup"];
		}else $phone_popup = '';
		
		 
		if(isset($_POST["phone_tracking_google"])){
			$phone_tracking_google = $_POST["phone_tracking_google"];
		}else $phone_tracking_google = '';
		
		if(isset($_POST["phone_tracking_catalogue"])){
			$phone_tracking_catalogue = $_POST["phone_tracking_catalogue"];
		}else $phone_tracking_catalogue = '';
		
	$sql_update  = "UPDATE  `bo_users` SET  
						`appels_commerciale` 	   =  '$appels_commerciale',
						`nbr_jrs_relance`    	   =  '$nbr_jrs_relance',
						`nbr_jrs_apres_expedition` =  '$nbr_jrs_apres_expedition',
						`tel_rentention_pop_up`    =  '$phone_popup',
						`tel_g_shopping`    	   =  '$phone_tracking_google',
						`tel_familiies_catalogs`   =  '$phone_tracking_catalogue'
				    WHERE `id` ='".$id_user."'";
	mysql_query($sql_update);
	
  }

} else {
  $id = preg_match("/^[1-9]?[0-9]*$/", $_GET["id"]) ? $_GET["id"] : (strtolower($_GET["id"]) == "new" ? "new" : "");
  if ($id == "new") {
    $u = new BOUser();
    $u->create();
    $u->save();
    $newUser = true;
  } else {
    if ($id == "") { // only HN can edit HN's rights
      header("Location: ".ADMIN_URL."users/users.php");
      exit();
    } else {
      $u = new BOUser($id);
      $gmailInfos = $u->getGmailInfos();
    }
    
  }
  $perms = $u->get_permissions();
}

?>
<link type="text/css" rel="stylesheet" href="HN.css"/>
<link type="text/css" rel="stylesheet" href="users.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.DialogBox.blue.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.MISM.blue.css">
<?php if (isset($error["rights"])) { ?><div class="error"><?php echo $error["rights"] ?></div><?php } ?>
<script type="text/javascript" src="AJAXclasses.js"></script>
<script type="text/javascript" src="AJAXmodules.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>
<script type="text/javascript" src="users.js"></script>
<script type="text/javascript">
var errorFields = <?php echo json_encode(array_keys($error)) ?>;
</script>
<div class="titreStandard">Détail d'un utilisateur</div>
<div class="bg" id="user-detail">
  <div class="section">
    <div class="title">Edition <?php if($newUser) { ?>du <u>nouvel</u> <?php } else { ?>de l'<?php } ?>utilisateur <span class="item-id"><?php echo $u->id ?></span></div>
    <form name="msForm" method="post" action="user.php?id=<?php echo $u->id ?>" autocomplete="off">
    <input type="hidden" name="id" value="<?php echo $u->id ?>"/>
    <input type="hidden" name="itemString" value=""/>
	
     <?php 
		$sql_users  = "SELECT appels_commerciale,nbr_jrs_relance,nbr_jrs_apres_expedition,tel_rentention_pop_up ,tel_g_shopping,tel_familiies_catalogs
					   FROM bo_users 
					   WHERE id='".$_GET['id']."' ";
		$req_users  =  mysql_query($sql_users);
		$data_users =  mysql_fetch_object($req_users);
	  
	  ?>
	<table id="ms-form" cellspacing="0" cellpadding="0">
      <tbody>
      <tr><td><label for="name">Nom :</label></td><td><input type="text" name="name" size="25" value="<?php echo $u->name ?>" class="edit"/></td></tr>
      <tr><td><label for="login">Login :</label></td><td><input type="text" name="login" size="25" value="<?php echo $u->login ?>" class="edit"/></td></tr>
      <tr><td><label for="pass">Mot de passe :</label></td><td><input type="password" name="pass" size="25" value="" class="edit"/> (vide = pas de changement)</td></tr>
      <tr><td><label for="email">Email :</label></td><td><input type="text" name="email" size="40" value="<?php echo $u->email ?>" class="edit"/></td></tr>
      <tr><td><label for="gmailLogin">Login <b><i>Gmail</i></b> :</label></td><td><input type="text" name="gmailLogin" size="40" value="<?php echo $gmailInfos['login'] ?>" class="edit"/></td></tr>
      <tr><td><label for="gmailPass">Mot de passe <b><i>Gmail</i></b> :</label></td><td><input type="password" name="gmailPass" size="25" value="<?php echo htmlspecialchars($gmailInfos['pass']) ?>" class="edit"/></td></tr>
      <tr><td><label for="phone">Téléphone :</label></td><td><input type="text" name="phone" size="25" value="<?php echo $u->phone ?>" class="edit"/></td></tr>
	  <tr><td><label for="phone_popup">Tel pop up rétention :</label></td><td><input type="text" name="phone_popup" size="25" value="<?= $data_users->tel_rentention_pop_up ?>" class="edit"/></td></tr>
	  
	  <!--  Ajouter deux champs dans la BDD   tel_familiies_catalogs / tel_g_shopping Le 12/04/2016 09:26    -->
	  <tr><td><label for="phone_tracking_google">Tel tracking Google :</label></td><td><input type="text" name="phone_tracking_google" size="25" value="<?= $data_users->tel_g_shopping ?>" class="edit"/></td></tr>
	  
	  <tr><td><label for="phone_tracking_catalogue">Tel tracking catalogues PDD cat :</label></td><td><input type="text" name="phone_tracking_catalogue" size="25" value="<?= $data_users->tel_familiies_catalogs ?>" class="edit"/></td></tr>
	  <!--    Fin    -->
	  
      <tr><td><label for="help_msg">Message d'aide :</label></td><td><textarea name="help_msg" rows="3" cols="40"><?php echo $u->help_msg ?></textarea></td></tr>
      <tr><td><label for="active">Active :</label></td><td><input type="checkbox" name="active"<?php if ($u->active) { ?> checked="checked"<?php } ?>/></td></tr>
	 
	  
	  <tr>
		<td><label for="help_msg">Inclure dans la pile d'appels commerciale  :</label></td>
		<?php 
		if($data_users->appels_commerciale == 1){ ?>
		<td>Oui : <input type="radio" name="appels_commerciale" value="1" checked />
		    Non : <input type="radio" name="appels_commerciale" value="0"  />
		</td>
		<?php }else { ?>
		<td>Oui : <input type="radio" name="appels_commerciale" value="1" />
		    Non : <input type="radio" name="appels_commerciale" value="0" checked />
		</td>
		<?php } ?>
	  </tr>
	  
	  <tr>
		<td><label for="help_msg">Nb jours avant relance :</label></td>
		<td><input type="text" name="nbr_jrs_relance" value="<?= $data_users->nbr_jrs_relance ?>" size="25"  /></td>
	  </tr>
	  
	  <tr>
		<td><label for="help_msg">Nb jours après date d'expédition :</label></td>
		<td><input type="text" name="nbr_jrs_apres_expedition" value="<?= $data_users->nbr_jrs_apres_expedition ?>" size="25"  /></td>
	  </tr>
	  
	  
      <tr><td><label>Date de création :</label></td><td class="text"><?php echo ($newUser ? " - " : date("Y-m-d à H:i:s", $u->create_time)) ?></td></tr>
      <tr><td><label>Dernière modification :</label></td><td class="text"><?php echo ($newUser ? " - " : date("Y-m-d à H:i:s" ,$u->timestamp)) ?></td></tr>
      </tbody>
    </table>
    <br/>
    <fieldset class="fieldset">
      <legend>Permissions</legend>
      <table cellspacing="0" cellpadding="0" class="fnt-list">
        <thead>
          <th style="width: 450px">Menu</th><th class="rights">droits<br/>Lect./Mod./Supp./Verr./Exp.</th>
        </thead>
        <tbody>
         <?php foreach($fntl as $fnt) { ?>
          <tr>
            <td><?php echo $fnt["desc"] ?></td>
            <td class="rights">
              <input type="checkbox" name="fnt_r_<?php echo $fnt["id"] ?>" <?php if ($perms->has($fnt["id"],"r")) { ?>checked="checked"<?php } ?>/>
              <input type="checkbox" name="fnt_e_<?php echo $fnt["id"] ?>" <?php if ($perms->has($fnt["id"],"e")) { ?>checked="checked"<?php } ?>/>
              <input type="checkbox" name="fnt_d_<?php echo $fnt["id"] ?>" <?php if ($perms->has($fnt["id"],"d")) { ?>checked="checked"<?php } ?>/>
			  <input type="checkbox" name="fnt_l_<?php echo $fnt["id"] ?>" <?php if ($perms->has($fnt["id"],"l")) { ?>checked="checked"<?php } ?>/>
			  <input type="checkbox" name="fnt_x_<?php echo $fnt["id"] ?>" <?php if ($perms->has($fnt["id"],"x")) { ?>checked="checked"<?php } ?>/>
            </td>
          </tr>
         <?php } ?>
        </tbody>
      </table>
    </fieldset>
    <br/>
    <input type="submit" class="bouton" value="Enregistrer les modifications"/>
    </form>
  </div>
</div>
<?php
require(ADMIN."tail.php");
?>
