<?php
	require_once('functions.php'); 
	
	if(!empty($_SESSION['marketing_user_id'])){
		
		//Getting params
		$f_ps							= mysql_escape_string($_POST['f_ps']);
		$f_pp							= mysql_escape_string($_POST['f_pp']);


		//Building Query
		

		if(isset($f_pp)){
			$query_suite_param_limit2 	= $f_pp;
		}else{
			$f_pp						= 10;
			$query_suite_param_limit2 	= " 10";
		}
		
		
		if(isset($f_ps)){
			//$query_suite_param_limit1 	= $f_ps;
			$page		= $f_ps;
			if(strcmp($f_ps,1)==0){
				$query_suite_param_limit1 	= 0;
			}else{
				$f_ps--;
				$query_suite_param_limit1 	= $f_pp*$f_ps;
			}
		}else{
			$f_ps						= 0;
			$query_suite_param_limit1 	= " 0";
		}
		
		
		
		$query_suite_param_limit_ready	= " LIMIT ".$query_suite_param_limit1.", ".$query_suite_param_limit2;
		
		$res_get_users_cout = $db->query("SELECT 
												count(m_u.id) c
												
											FROM
												marketing_users m_u
											WHERE
												deleted='no'
												", __FILE__, __LINE__);
		
		$content_get_users_cout	= $db->fetchAssoc($res_get_users_cout);
		$total_count_results		= $content_get_users_cout['c'];
		
		if($total_count_results!=0){
		
		
			//Executing the query of users and loading the informations
			$res_get_users = $db->query("SELECT 
												m_u.id, 
												m_u.name,
												m_u.description,
												m_u.login,
												m_u.date_creation,
												active
												
											FROM
												marketing_users m_u
											WHERE
												deleted='no' 
											ORDER BY m_u.date_creation DESC 
											".$query_suite_param_limit_ready." ", __FILE__, __LINE__);
											
			echo('<div id="page_list_selector_container">');

				echo('<div id="page_list_selector_text">');
					echo('users par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p" onchange="users_get_more_now()">');
						echo('<option value="5"');
						if(strcmp($f_pp,5)==0){
							echo(' selected="true" ');
						}
						echo('>5</option>');
						
						echo('<option value="10"');
						if(strcmp($f_pp,10)==0){
							echo(' selected="true" ');
						}
						echo('>10</option>');
						
						echo('<option value="15"');
						if(strcmp($f_pp,15)==0){
							echo(' selected="true" ');
						}
						echo('>15</option>');
						
						echo('<option value="50"');
						if(strcmp($f_pp,50)==0){
							echo(' selected="true" ');
						}
						echo('>50</option>');
						
						echo('<option value="100"');
						if(strcmp($f_pp,100)==0){
							echo(' selected="true" ');
						}
						echo('>100</option>');
						
					echo('</select>');
					
				echo('</div>');//end div #page_list_selector_select
				
				
					
			echo('</div>');//end div #page_list_selector_container
											
			echo('<div class="table-responsive">');
				echo('<table class="table administration">');
					echo('<tr>');
						echo('<th style="width: 6%;">');
							echo('ID');
						echo('</th>');
						
						echo('<th style="width: 10%;">');
							echo('Date cr&eacute;ation');
						echo('</th>');
						
						echo('<th style="width: 12%;">');
							echo('Nom');
						echo('</th>');
						
						echo('<th style="width: 15%;">');
							echo('Login');
						echo('</th>');
						
						echo('<th style="width: 15%;">');
							echo('Etat');
						echo('</th>');
						
						
						echo('<th style="width: 8%;">');
							echo('Actions');
						echo('</th>');
						
					echo('</tr>');
				
			$modulo_local_loop	= 0;
			$modulo_local_class	= 'alt';
			
			$local_row_bold		= '';
			
			while($content_get_users	= $db->fetchAssoc($res_get_users)){
			
				//Calculating modulo to make a difference between the table rows !!
				if($modulo_local_loop%2){
					$modulo_local_class	= 'alt';
				}else{
					$modulo_local_class	= '';
				}
					
				$row_link	= MARKETING_URL.'administration-users-edit.php?id='.$content_get_users['id'];
				
				
				echo('<tr class="rs '.$modulo_local_class.'">');
					//echo('<td class="valign" onclick="javascript:open_link_blank(\'users_external_formid\', \''.$row_link.'\', \'_self\')">');
					echo('<td class="valign" onclick="javascript:open_link_self(\''.$row_link.'\')">');
					
					
						echo($content_get_users['id']);
							
						
					echo('</td>');
					
					echo('<td class="valign" onclick="javascript:open_link_self(\''.$row_link.'\')">');
						echo(date('d/m/Y H:i:s', strtotime($content_get_users['date_creation'])));
					echo('</td>');
					
					echo('<td onclick="javascript:open_link_self(\''.$row_link.'\')">');
						echo(ucfirst($content_get_users['name']));
					echo('</td>');
					
					echo('<td onclick="javascript:open_link_self(\''.$row_link.'\')">');
						echo($content_get_users['login']);
					echo('</td>');
					
					echo('<td onclick="javascript:open_link_self(\''.$row_link.'\')">');
						if(strcmp($content_get_users['active'],'yes')==0){
							echo('<img src="ressources/images/circle_green.png" width="20" alt="Activ&eacute;" title="Activ&eacute;" />');
						}else{
							echo('<img src="ressources/images/circle_red.png" width="20" alt="D&eacute;sactiv&eacute;" title="D&eacute;sactiv&eacute;" />');
						}
					echo('</td>');
					
					echo('<td class="valign cursor-default">');
						
						echo('<a href="'.$row_link.'" target="_self"><i class="fa fa-pencil"></i></a> ');

					echo('</td>');
					
				echo('</tr>');
				
				$modulo_local_loop++;
				
			}//End while
			
				echo('</table>');
			echo('</div>');
				
			echo('<div class="row" style="margin-left:0;">');
				/*
				echo('<div class="form-left">');
					echo('&nbsp;');
				echo('</div>');
				
				echo('<div class="form-middle">');
					
				echo('</div>');
				*/
				
				echo('<div class="users-bottom-pagination pagination">');
					
					echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Contact');
					if($total_count_results>1){
						echo("s");
					}
					echo("</div>");
					
					$total_count_copy	= $total_count_results;
					
					$nb_pages=1;
					while(($total_count_copy  > $f_pp) ){ 
						$nb_pages ++;
						$total_count_copy  = $total_count_copy  -$f_pp;
					}
					
					
					if($nb_pages>0){
						$count_pagination = 1;
						if($page>=2){
							$precedent = $page-1;
									echo('<div class="disabled"><a href="javascript:users_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
						}else{
							echo('<div class="disabled"><< Prec.</div>');
						}
						
						//echo(' [');

						if($page>10){
								$count_local=$page-5; 
								$count_pagination = $count_local+1;
						}else if($page==10){
								$count_local=5; 
								$count_pagination = $count_local+1;
						}else if($page==9){
								$count_local=4;
								$count_pagination = $count_local+1;
						}else if($page==8){
								$count_local=3;
								$count_pagination = $count_local+1;
						}else if($page==7){
								$count_local=2;
								$count_pagination = $count_local+1;
						}else if($page==6){
								$count_local=1;
								$count_pagination = $count_local+1;    
						}else{
								$count_local=0;
								$count_pagination = $count_local+1;
						}

						if($count_pagination >2){		
							echo('<a title="page num&eacute;ro 1" href="javascript:users_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
						}

						$count_local_stop=$page+4;

						while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
							if($count_pagination==$page){
								echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
							}else{
									echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:users_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
							}
							$count_pagination++;
							$count_local++;
						}

						if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
								echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:users_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
						}
						
						//echo('] ');
						if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
							$suivant = $page +1;
								echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:users_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
						}
					}
					
					// Fin pagination
					
				echo('</div>');
				
				
			echo('</div>');//end div .row	
				
										
		}else{
			echo("Aucune information &agrave; afficher !");
		}//end else if global count 	if($total_count_results!=0)

	}else{
		echo('<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href="login.php">Merci de vous reconnecter.</a></strong>');
	}
?>