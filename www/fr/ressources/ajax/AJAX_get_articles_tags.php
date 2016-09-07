<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();

$action = $_GET['action'];

if($action == 'all' ){
	$sql_all = "SELECT ba.id as id_article,ba.article_title,ba.promo_image,content,timestamp_created,ref_name
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
				echo '<div style="float:left;margin-right:10px"><img src="'.URL.'ressources/images/empty_gallery.png" width="150px" /></div>';
			}else{
				echo'<div style="float:left;margin-right:10px"><img src="'.URL.'ressources/images/blog/'.$data_all->promo_image.'" width="150px" /></div>';
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
		$desc    = substr($data_all->content, 0, 400);
		$url_article = URL.'blog/'.$data_all->id_article.'/'.$data_all->ref_name.'.html';
    	echo'	<div style="overflow: hidden;">
					<a href="'.$url_article.'"><h2 class="blue-title title-h">'.$data_all->article_title.'</h2></a>
					<br />
					<div class="sec_blog">'.$desc.'...</div><br />
					<div class="suite-article"><a href="'.$url_article.'" >[Lire l\'article]</a></div><br />
					Publié le '.date("d/m/Y", strtotime($data_all->timestamp_created)).'  - Tags : '.$all_tag.' 
				</div>
			</div>';
	}
}

if($action == 'save_send_mail' ){
	$email = $_GET['email'];
	$sql_check  = "SELECT id FROM blog_send_mail WHERE email='".$email."' ";
	$req_check  =  mysql_query($sql_check);
	$rows_check =  mysql_num_rows($req_check);
	
	if($rows_check > 0){
		echo 	'<span style="color:red">Oups! L\'adresse '.$email.'  est déjà inscrite à la mailing list ;-) </span><br /><br /> ';
	}else{
		$sql_insert  =  "INSERT INTO `blog_send_mail` (`id`, `email`, `date_create`) VALUES (NULL, '$email', NOW())";
		mysql_query($sql_insert);
		echo '<strong>Merci pour votre inscription.<br> Nous vous souhaitons de bonnes lectures</strong><br /><br />';
	}
}
?>  