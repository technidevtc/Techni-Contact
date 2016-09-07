<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 15 juillet 2005

 Fichier : /secure/manager/elogs.php
 Description : Consultation des logs d'action extranet d'un jour donné

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
define('EXTRANET', INCLUDES . 'extranet/');

require(EXTRANET . 'logs.php');


$title = $navBar = 'Consultations des logs extranet';
require_once(ADMIN . 'head.php');


if($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{
    if(isset($_POST['d']) && preg_match('/^[0-9]{1,2}$/', $_POST['d']) && isset($_POST['m']) && preg_match('/^[0-9]{1,2}$/', $_POST['m']) && isset($_POST['y']) && preg_match('/^[0-9]{4}$/', $_POST['y']) && checkdate($_POST['m'], $_POST['d'], $_POST['y']))
    {
        $begin = mktime(0, 0, 0, $_POST['m'], $_POST['d'], $_POST['y']);
        $logs  = & Eday($handle, $begin, mktime(0, 0, 0, $_POST['m'], $_POST['d'] + 1, $_POST['y']));
    }
    else
    {
        $logs = false;
    }
    
?>
<div class="titreStandard">Consultation des logs extranet du <?php print(date('d/m/Y', $begin)) ?></div><br>
<div class="bg">
<?php
    
    $formnav = '<form method="post" action="elogs.php?' . session_name() . '=' . session_id() . '">Voir toutes les actions extranet du <select name="d">';
    for($i = 1; $i <= 31; ++$i)
    {
        $sel = ($i == $_POST['d']) ? 'selected' : '';
        $formnav .= '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
    }

    $formnav .= '</select> <select name="m">';
    for($i = 1; $i <= 12; ++$i)
    {
        $sel = ($i == $_POST['m']) ? 'selected' : '';
        $formnav .= '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
    }

    $formnav .= '</select> <select name="y">';
    for($i = 2005; $i <= date('Y'); ++$i)
    {
        $sel = ($i == $_POST['y']) ? 'selected' : '';
        $formnav .= '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
    }

    $formnav .= '</select> <input type="button" value="Go" onClick="this.form.submit(); this.disabled = true"></form>';
    
    print($formnav);


    if(count($logs) > 0)
    {
      
        print('<table border=1 cellspacing=1 cellpadding=1><tr><td class="intitule"><font size="1"><center>Date</center></font></td><td class="intitule"><font size="1"><center>Session</center></font></td><td class="intitule"><font size="1"><center>Action</center></font></td></tr>');

        for($i = 0; $i < count($logs); ++$i)
        {
            print('<tr><td><font size="1"><center>'.date('d/m/Y H:i:s', $logs[$i][0]).'</center></font></td><td class="intitule"><font size="1"><center>' . to_entities($logs[$i][1]) . '</center></font></td><td class="intitule"><font size="1"><center>' . $logs[$i][2] . '</center></font></td></tr>');
        }

        print('</table>');

    }
    else
    {
        print('<div class="error">Aucun enregistrement</div>');
    }
    
    print('<br>' . $formnav . '</div>');


}  // fin autorisation

require(ADMIN . 'tail.php');

?>
