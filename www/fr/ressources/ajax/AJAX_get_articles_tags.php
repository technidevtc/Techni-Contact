<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();

$action = $_GET['action'];

if($action == 'all' ){
	$sql_all = "SELECT ba.id as id_article,ba.article_title,ba.promo_image,content,promo_image,timestamp_created
				FROM   blog_tags_linked_articles btla,  blog_articles ba
				WHERE  btla.id_article = ba.id
				AND statut='1'
				GROUP BY ba.id
				ORDER BY article_title ASC ";
	$req_all =  mysql_query($sql_all);
	// echo $sql_all;
	while($data_all = mysql_fetch_object($req_all)){
		echo '<div class="global_articles">';
			if(empty($data_all->promo_image)){
				echo '<div style="float:left;margin-right:10px"><img src="ressources/images/empty_gallery.png" width="150px" /></div>';
			}else{
				echo'<div style="float:left;margin-right:10px"><img src="" width="150px" /></div>';
			}
			
		$sql_tags = "SELECT ba.name,ba.id,ba.ref_name
					 FROM blog_tags_names ba, blog_tags_linked_articles  btla
					 WHERE ba.id = btla.id_tag
					 AND btla.id_article='".$data_all->id_article."'
					 GROUP BY ba.id
					 ORDER BY ba.name ASC  ";
		$req_tags = mysql_query($sql_tags);
		$all_tag = "";
		while($data_tags = mysql_fetch_object($req_tags)){
			$all_tag .= $data_tags->name."-";
		}
		$all_tag = substr($all_tag, 0, -1);
    	echo'	<div style="overflow: hidden;">
					<h1 class="blue-title">'.$data_all->article_title.'</h1>
					<br />
					<div class="sec_blog">'.$data_all->content.'</div><br />
					<div><a href="">[Lire l\'article]</a></div><br />
					Publié le '.date("d/m/Y", strtotime($data_all->timestamp_created)).'  - Tags : '.$all_tag.' 
				</div>
			</div>';
	}
}else{
	$sql_all = "SELECT ba.id,ba.article_title,ba.promo_image,timestamp_created
				FROM   blog_tags_linked_articles btla,  blog_articles ba
				WHERE  btla.id_article = ba.id
				AND    btla.id_tag = '".$action."'
				AND statut='1'
				GROUP BY ba.id				
				ORDER BY article_title ASC ";
	$req_all =  mysql_query($sql_all);
	while($data_all = mysql_fetch_object($req_all)){
		echo '<div class="global_articles">';
			if(empty($data_all->promo_image)){
				echo '<div style="float:left;margin-right:10px"><img src="ressources/images/empty_gallery.png" width="150px" /></div>';
			}else{
				echo'<div style="float:left;margin-right:10px"><img src="" width="150px" /></div>';
			}
			
		$sql_tags = "SELECT ba.name,ba.id,ba.ref_name
					 FROM blog_tags_names ba, blog_tags_linked_articles  btla
					 WHERE ba.id = btla.id_tag
					 AND btla.id_article='".$data_all->id_article."'
					 GROUP BY ba.id
					 ORDER BY ba.name ASC  ";
		$req_tags = mysql_query($sql_tags);
		$all_tag = "";
		while($data_tags = mysql_fetch_object($req_tags)){
			$all_tag .= $data_tags->name."-";
		}
		$all_tag = substr($all_tag, 0, -1);
    	echo'	<div style="overflow: hidden;">
					<h1 class="blue-title">'.$data_all->article_title.'</h1>
					<br />
					<div class="sec_blog">'.$data_all->content.'</div><br />
					<div><a href="">[Lire l\'article]</a></div><br />
					Publié le '.date("d/m/Y", strtotime($data_all->timestamp_created)).'  - Tags : '.$all_tag.' 
				</div>
			</div>';
	}
}
?>  