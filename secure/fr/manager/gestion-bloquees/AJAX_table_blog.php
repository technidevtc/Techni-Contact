<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}
$user = new BOUser();

require_once(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$db = DBHandle::get_instance();
$user = $userChildScript = new BOUser();
if (!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}
$userPerms = $user->get_permissions();
// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];
?>
	<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
	<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="js/spool_vpc_script.js"></script>
	<!--<script type="text/javascript" language="javascript" src="js/dataTables.fixedHeader.js"></script>-->
	<script type="text/javascript" language="javascript" class="init">
		$("#rdvDb").remove();		
		$(document).ready(function () {
			var table = $('#example').DataTable();
			//new $.fn.dataTable.FixedHeader(table);
		});
	</script>
<table id="example" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php
			echo '<th>Tag d’appartenance </th>';
			echo '<th>Titre du post  </th>';
			echo '<th>Date de création  </th>';
			echo '<th>Date de mise à jour</th>';
			echo '<th>Etat de l’article </th>
				  <th>Action  </th>';
	  ?>
    </tr>
    </thead>
    <tbody>	
	<?php
	$sql_guides  = "SELECT `id`,article_title,timestamp_created,timestamp_updated,statut
				    FROM `blog_articles`  ";
    $req_guides  =  mysql_query($sql_guides);
	$tag_appart  =  ""; 
	while($data_guides  =  mysql_fetch_object($req_guides)){
		$sql_tags = "SELECT btn.name
					  FROM blog_tags_linked_articles btla, blog_tags_names btn 
					  WHERE id_article='".$data_guides->id."'
					  AND btla.id_tag = btn.id ";
		$req_tags =  mysql_query($sql_tags);
		while($data_tags = mysql_fetch_object($req_tags)){
			$tag_appart .= $data_tags->name." / ";
		}

		
		/*$sql_familie  =  "SELECT name FROM families_fr WHERE id='".$data_guides->id_famille_parent."' ";
		$req_families =   mysql_query($sql_familie);
		$data_families=   mysql_fetch_object($req_families);
		
		$sql_count  = "SELECT id 
					   FROM guides_linked_familles
					   WHERE id_guide='".$data_guides->id."' ";
		$req_count  =  mysql_query($sql_count);
		$rows_count =  mysql_num_rows($req_count);
		*/
		echo '<tr>';
			echo '<td>'.$tag_appart.'</td>';
			echo '<td>'.$data_guides->article_title.'</td>';
			echo '<td>'.date("d/m/Y H:i", strtotime($data_guides->timestamp_created)).'</td>';
			
			if($data_guides->timestamp_updated == "0000-00-00 00:00:00"){
				echo '<td> - </td>';
			}else{
			echo '<td>'.date("d-m-Y H:i", strtotime($data_guides->timestamp_updated)).'</td>';
			}
			
			if($data_guides->statut == "0"){
				echo '<td>Brouillon </td>';
			}else{
			echo '<td>Publié</td>';
			}
			//'.URL.'guides-achat/'.$data_guides->id.'-'.$data_guides->ref_name.'.html
			echo '<td>
			<span><a href="" target="_blink">Voir</a></span> |
			<span><a href="edit-article.php?id='.$data_guides->id.'">Modifier</a></span> | 
			<span onclick="delete_article('.$data_guides->id.')">Supprimer</span></td>';			
		echo ' </tr>';	
		$tag_appart = "";
    }
	?>	   
    </tbody>
  </table>




  
