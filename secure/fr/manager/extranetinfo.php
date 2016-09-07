<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 juin 2005

 Fichier : /secure/manager/extranetinfo.php
 Description : Cs des connexions extranet

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN . 'users.php');


$title = $navBar = 'Connexions à l\'extranet';
require_once(ADMIN . 'head.php');

if($result = & $handle->query("select count(id) from products_fr where active = 1", __FILE__, __LINE__))
	list($nbProducts) = $handle->fetch($result);
else
	$nbProducts = "-";


if($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN)
	$logs = & last15($handle);

if($user->rank != CONTRIB)
{
	if($result = & $handle->query("select id from products_add where type = 'c'", __FILE__, __LINE__))
	{
		if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
		{
			$add = '<br /> - <a href="products/add_wait.php?' . $sid . '">' . $nb . ' en attente de validation de création</a>';
		}
	}

	if($result = & $handle->query("select id from products_add where type = 'm'", __FILE__, __LINE__))
	{
		if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
		{
			$mod = '<br /> - <a href="products/edit_wait.php?' . $sid . '">' . $nb . ' en attente de validation de modification</a>';
		}
	}

	$extranet = '';
	$from_extranet = false;
	
	//Query changed on 14/11/2014 to ignore the records starting with 10x"#"
	if($result = & $handle->query("SELECT 
										id 
									FROM 
										products_add_adv
									WHERE 
										type = 'c'
									AND
										reject = 0
									AND
										name not like '##########%'", __FILE__, __LINE__))
	{
		if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
		{
			$from_extranet = true;
			$extranet = '<br /> - Extranet : <a href="products/add_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de création</a>';
		}
	}

	if($result = & $handle->query("select id from products_add_adv where type = 'm' and reject = 0", __FILE__, __LINE__))
	{
		if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
		{
			if(!$from_extranet)
			{
				//                $extranet .= '<br>Extranet :';
				$from_extranet = true;
			}
			$extranet .= '<br /> - Extranet : <a href="products/edit_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de modification</a>';
		}
	}

	if($result = & $handle->query("select id from sup_requests", __FILE__, __LINE__))
	{
		if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
		{
			if(!$from_extranet)
			{
				//              $extranet .= '<br>Extranet :';
			}
			$extranet .= '<br /> - Extranet : <a href="products/sup_wait.php?' . $sid . '">' . $nb . ' en attente de validation de suppression</a>';
		}
	}

	$extranet_a = '';
	$from_extranet_a = false;
	if($result = & $handle->query("select id from advertisers_adv", __FILE__, __LINE__))
	{
		if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
		{
			$from_extranet_a = true;
			$extranet_a = '<br /> - Extranet : <a href="advertisers/edit_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de modification</a>';
		}
	}
}


?>

<div class="column">
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
              <div class="portlet-header ui-widget-header">Données extranet</div>
              <div class="portlet-content">
                <p>
                  <?php echo $add .' &nbsp; '. $mod .' &nbsp; '. $del . $extranet  ?>
                </p>
              </div>
            </div>
          </div>

<div class="titreStandard">Annonceurs s'étant déjà connectés à leur extranet</div><br>
<div class="bg">
Les annonceurs suivants se sont déjà connectés à leur extranet :
<br>
<ul>
<?php


if($r = & $handle->query('select a.nom1 from advertisers a, extranetusers e where e.c = 1 and e.id = a.id order by a.nom1', __FILE__, __LINE__))
{
    $i = 1;
    while($rec = & $handle->fetch($r))
    {
        print('<li> ' . $i++ . ') ' . to_entities($rec[0]));
    }

}


?>
</ul>
</div>
<?php

require(ADMIN . 'tail.php');

?>
