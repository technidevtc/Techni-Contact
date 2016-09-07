<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/demandes/index.php
 Description : Demandes de PE des catalogues

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(isset($_GET['c']))
{
    if(!preg_match('/^[1-3]$/', $_GET['c']))
    {
        exit;
    }
    
    $c    = ($_GET['c'] == 1) ? 'gen'     : (($_GET['c'] == 2) ? 'ind'        : 'col');
    $name = ($_GET['c'] == 1) ? 'général' : (($_GET['c'] == 2) ? 'industries' : 'collectivités');

    if($result = & $handle->query('select timestamp, nom, prenom, fonction, societe, salaries, secteur, naf, siret, adresse, cadresse, cp, ville, pays, tel, fax, email, url from demandes where ' . $handle->escape($c) . ' = 1 order by timestamp desc', __FILE__, __LINE__))
    {
        header("Content-type: text/x-csv");
        header('Content-Disposition: attachement; filename="Demandes catalogues ' . $name . '.csv"');
       
        print('Date;Nom;Prénom;Fonction;Société;Nombre de salariés; Secteur d\'activité;Code NAF;Siret;Adresse postale;Complément adresse postale;Code postal;Ville;Pays;Téléphone;Fax;Adresse email;Adresse web');
        print("\n\n");

        if($handle->numrows($result, __FILE__, __LINE__) == 0)
        {
            print('Aucun résultat');
        }
        else
        {
            while($data = & $handle->fetch($result))
            {
                foreach($data as $k => $v)
                {
                    $v = str_replace(';', ' ', $v);
                    
                    if($k == 0)
                    {
                        $v = date('Y-m-d', $v);
                    }

                    if($k == 16) $v = strtolower($v);
                    print($v . ';');
                }

                print("\n");
            }
        }
    }

    exit;
}

else
{

    $title  = $navBar = 'Demandes de la prochaine édition des catalogues';
    require(ADMIN . 'head.php');



        if($result = & $handle->query('select id from demandes where gen = 1', __FILE__, __LINE__))
        {
            $nbg = $handle->numrows($result,  __FILE__, __LINE__);
    
            if($nbg == 0)
            {
                $nbg = 'Aucune';
            }
        }
        else
        {
            $nbg = 'Aucune';
        }

        $nbg .= ($nbg != 'Aucune' && $nbg > 1) ? ' demandes' : ' demande';


        if($result = & $handle->query('select id from demandes where ind = 1', __FILE__, __LINE__))
        {
            $nbi = $handle->numrows($result,  __FILE__, __LINE__);

            if($nbi == 0)
            {
                $nbi = 'Aucune';
            }
        }
        else
        {
            $nbi = 'Aucune';
        }

        $nbi .= ($nbi != 'Aucune' && $nbi > 1) ? ' demandes' : ' demande';


        if($result = & $handle->query('select id from demandes where col = 1', __FILE__, __LINE__))
        {
            $nbc = $handle->numrows($result,  __FILE__, __LINE__);

            if($nbc == 0)
            {
                $nbc = 'Aucune';
            }
        }
        else
        {
            $nbc = 'Aucune';
        }

        $nbc .= ($nbc != 'Aucune' && $nbc > 1) ? ' demandes' : ' demande';




?>
<script language="JavaScript">
<!--

function maj(o)
{
    if(o == '')
    {
        document.getElementById('cc').innerHTML = '<br>';
    }
    else
    {
        document.getElementById('cc').innerHTML = '<a href="index.php?c=' + o + '">Enregistrer la liste (clic droit puis enregistrer-sous)</a>';
    }
}


//-->
</script>
<div class="titreStandard">Exporter les demandes de catalogues au format CSV</div><br><div class="bg">
<?php

        if(isset($_GET['del']) && preg_match('/^[0-9]+$/', $_GET['del']) && intval($_GET['del']) <= 24)
        {
            $q = 'delete from demandes';
    
            if($_GET['del'] != 0)
            {
                $q .= ' where timestamp < ' . mktime(date('H'), date('i'), date('s'), date('m') - $_GET['del'], date('d'), date('Y'));
            }
    
            print('<div class="confirm">');
    
            if($handle->query($q, __FILE__, __LINE__))
            {
                if($_GET['del'] == 0)
                {
                    print('Ensemble des demandes supprimé avec succès');

                    ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Suppression de l\'ensemble des demandes de catalogues');

                }
                else
                {
                    print('Demandes datant de plus de ' . $_GET['del'] . ' mois supprimées avec succès.');

                    ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Suppression des demandes de catalogues datant de plus de ' . $_GET['del'] . ' mois');

                }
            }

            print('</div><br><br>');

        }  // fin suppression

?>
Sélection du catalogue : <select name="cat" onChange="maj(this.options[this.selectedIndex].value)">
<option value=""></option>
<option value="1">Catalogue général (<?php print($nbg) ?>)</option>
<option value="2">Catalogue industries (<?php print($nbi) ?>)</option>
<option value="3">Catalogue collectivités (<?php print($nbc) ?>)</option></select>
<br><br>
<div id="cc"><br></div>
<br><br>
<select name="del" onChange="if(this.options[this.selectedIndex].value != '' && confirm('Etes-vous sûr de vouloir effacer ces demandes ?')) goTo('index.php?<?php print(session_name() . '=' . session_id()) ?>&del=' + this.options[this.selectedIndex].value)">
<option value="">Effacer les demandes ...</option>
<?php

        for($i = 1; $i <= 24; ++$i)
        {
            print('<option value="' . $i . '"> ... datant de plus de ' . $i . ' mois</option>');
        }

?><option value="0">Toutes les demandes</option></select>
</div><?php


        require(ADMIN . 'tail.php');


}  // Action non visualisation

?>

