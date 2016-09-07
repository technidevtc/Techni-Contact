<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

	28 mars 2006 : + options fournisseurs

 Fichier : /secure/manager/advertisers/edit_wait.php
 Description : Coord en attente de validation de modif
/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN."advertisers.php");

$title  = 'Base de données des annonceurs et fournisseurs';
$navBar = '<a href="index.php?SESSION" class="navig">Base de données des annonceurs et fournisseurs</a> &raquo; Validation de coordonnées extranet';
require_once(ADMIN."head.php");
?>

<?php if($user->rank == CONTRIB) { ?>
<div class="bg">
	<div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div>
</div>
<?php } else { $advertisers = & displayWaitAdvExt($handle) ?>

<div class="titreStandard">Coordonnées extranet en attente de validation</div><br>
<div class="bg">
<?php
if(count($advertisers) > 0) {
    $prev = '';   $open = false;

    print('<ul>');

    foreach($advertisers as $v)
    {

        $open = true;

        if($prev != ($md = date('d/m/Y', $v[2])))
        {
            if($prev == '')
            {
                print('<li>');
            }
            else
            {
                print('</ul><br><li>');
            }

            print('<u>Demandes de modification du ' . $md . ' :</u><br><br><ul>');

            $prev = $md;
        }

        print('<li><a href="edit.php?type=edit_adv&id=' . $v[0] . '&' . session_name() .'=' . session_id() . '">' . to_entities($v[1]) .'</a>');


    }

    if($open)
    {
        print('</ul>');
    }

    print('</ul>');
}
else
{
    print('<div class="confirm">Aucune coordonnée extranet en attente de validation</div>');
}

?></div><br><br>
<?php

}  // fin accès

require(ADMIN . 'tail.php');

?>
