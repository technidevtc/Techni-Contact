<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/advertisers/search.php
 Description : Recherche interne des annonceurs

/=================================================================*/


if(!isset($_GET['name']) || strlen($search = urldecode($_GET['name'])) < 3)
{
    exit;
}

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN . 'advertisers.php');

$title = $navBar = 'Rechercher un annonceur';

require(ADMIN . 'head.php');

?><div class="titreStandard">Résultat de votre recherche sur les termes <b><?php print(to_entities($search)) ?></b> - <a href="javascript:window.close()">Fermer cette page</a></div>
<br><div class="bg"><?php

$c = & searchAdvertiser($handle, $search);

if(count($c) == 0)
{
    print('<div class="confirm">Aucun résultat</div>');
}
else
{
    print('<ul>');

    foreach($c as $v)
    {
        print('<li>' . to_entities($v));
    }
    
    print('</ul>');
}

print('</div>');

require(ADMIN . 'tail.php');

?>

