<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 18 juin 2005

 Fichier : /secure/manager/newsletter/left.php
 Description : Colonne gauche newsletter

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$handle = DBHandle::get_instance();


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/global.css">
</head>
<body topmargin="5" marginheight="5">
<script language="JavaScript">
<!--

function go(id, name, f, ref)
{
	parent.main.choix.location.href = 'purpose.php?id=' + id + '&name=' + name + '&f=' + f + '&ref=' + ref;
}

//-->
</script>
<?php

if(!isset($_GET['type']) || ($_GET['type'] != 1 && $_GET['type'] != 2))
{
    print('<div class="bg" align="center"><b>Recherche par...</b><p align="left">');
    print('<a href="left.php?lettre=0&type=1">&middot; Annonceurs</a><br>');
    print('<a href="left.php?lettre=0&type=2">&middot; Liste Alphab&eacute;tique</a> &nbsp; </p></div>');
}
else if($_GET['type'] == 2 && isset($_GET['lettre']) && preg_match('/^[0a-z]$/', $_GET['lettre']))
{
    print('<div class="bg" align="center"><b>Recherche alphabétique [<a href="left.php">retour</a>]</b>');
    print('<br><br><div align="center"><a href="left.php?type=2&lettre=0">0-9</a>');

    for($i = ord('a'); $i <= ord('z'); ++$i)
    {
        print(' - <a href="left.php?type=2&lettre=' . chr($i) . '">' . strtoupper(chr($i)) . '</a> ');
    }

    if($_GET['lettre'] == '0')
    {
        
        $lettre = '0-9';
        $pattern = 'REGEXP(\'^[0-9]\')';
        
    }
    else
    {
        $lettre = strtoupper($_GET['lettre']);
        $pattern = 'like \'' . $lettre . '%\'';
    }
    

    print('<br><br><b> - ' . $lettre . ' - </b></div><br><br><p align="left">');
    
    $query = 'select p.id, pfr.name, pf.idFamily, pfr.ref_name, pfr.fastdesc from products_fr pfr, products p, products_families pf, advertisers a where p.id = pfr.id and pf.idProduct = p.id and pfr.name ' . $pattern . ' and p.idAdvertiser = a.id and a.actif = 1 group by p.id order by pfr.name';

    if($result = $handle->query($query))
    {
        if($handle->numrows($result) == 0)
        {
            print('Aucun résultat');
        }
        else
        {
            $liste = array();

            while($record = & $handle->fetch($result))
            {
                if(in_array($record[0], $liste))
                {
                    continue;
                }

                $liste[] = $record[0];

                $d = (strlen($record[1]) > 30) ? substr($record[1], 0, 27) . '...' : $record[1];

                print('&middot; <a href="javascript:go(\'' . $record[0] . '\', \'' . urlencode($record[1])  . '\', \'' . $record[2]. '\', \'' . $record[3] . '\')">' . $d . '</a> <a href="' . URL . 'produits/' . $record[2] . '-' . $record[0] . '-' . $record[3] . '.html" target="_blank"><img src="' . ADMIN_URL . 'images/web.gif" border="0"></a><br>');

            }

        }
    }
    
    print('</p></div>');
}
else
{
    print('<div class="bg" align="center"><b>Recherche par annonceur [<a href="left.php">retour</a>]</b><br><br><select onChange="document.location.href=\'left.php?type=1&id=\' + this.options[this.selectedIndex].value;"><option value="">Liste des Annonceurs</option>');

    $query = 'select id, nom1 from advertisers where actif = 1 order by nom1';

    if($result = $handle->query($query))
    {
        while($record = & $handle->fetch($result))
        {
            print('<option value="' . $record[0] . '">' . $record[1] . '</option>');

        }
    }

    print('</select>');

    if(isset($_GET['id']) && preg_match('/^[0-9]{1,5}$/', $_GET['id']))
    {

        $query = 'select p.id, pfr.name, pf.idFamily, pfr.ref_name, pfr.fastdesc, a.nom1 from products_fr pfr, products p, products_families pf, advertisers a where p.id = pfr.id and pf.idProduct = p.id and a.actif = 1 and a.id = \'' . $_GET['id'] . '\' and a.id = p.idAdvertiser group by p.id order by pfr.name';

        if($result = $handle->query($query))
        {
            if($handle->numrows($result) == 0)
            {
                print('<br><br>Aucun résultat');
            }
            else
            {
                $liste = array();
                $i = 0;

                while($record = & $handle->fetch($result))
                {
                     if($i == 0)
                     {
                         print('<br><br><center> - <b>' . $record[5] . ' - </b></center><br><br><p align="left">');
                         $i = 1;
                     }

                    if(in_array($record[0], $liste))
                    {
                        continue;
                    }

                    $liste[] = $record[0];

                    $d = (strlen($record[1]) > 30) ? substr($record[1], 0, 27) . '...' : $record[1];

                    print('&middot; <a href="javascript:go(\'' . $record[0] . '\', \'' . urlencode($record[1])  . '\', \'' . $record[2]. '\', \'' . $record[3] . '\')">' . $d . '</a> <a href="' . URL . 'produits/' . $record[2] . '-' . $record[0] . '-' . $record[3] . '.html" target="_blank"><img src="' . ADMIN_URL . 'images/web.gif" border="0"></a><br>');

                }
                
                if($i == 1)
                {
                    print('</p>');
                }

            }   
        }

    }

    print('</div>');
}


?>
</body></html>
