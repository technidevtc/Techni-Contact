<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 26 sept 2005

 Fichier : /secure/manager/google.php
 Description : Test mc google

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$title = $navBar = 'Google';
require_once(ADMIN . 'head.php');


if($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{

?>
<div class="titreStandard">Test requête Google DCs </div><br>
<div class="bg">
<script language="JavaScript" type="text/JavaScript">
<!--

function go()
{
    n = 0;

    document.getElementById('216.239.37').setAttribute("src", "http://216.239.37.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('216.239.39').setAttribute("src", "http://216.239.39.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('216.239.53').setAttribute("src", "http://216.239.53.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('216.239.57').setAttribute("src", "http://216.239.57.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('216.239.59').setAttribute("src", "http://216.239.59.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('216.239.63').setAttribute("src", "http://216.239.63.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('64.233.161').setAttribute("src", "http://64.233.161.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('64.233.167').setAttribute("src", "http://64.233.167.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('64.233.171').setAttribute("src", "http://64.233.171.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('64.233.179').setAttribute("src", "http://64.233.179.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('64.233.183').setAttribute("src", "http://64.233.183.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('64.233.185').setAttribute("src", "http://64.233.185.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('64.233.187').setAttribute("src", "http://64.233.187.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('64.233.189').setAttribute("src", "http://64.233.189.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('66.102.7').setAttribute("src", "http://66.102.7.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('66.102.9').setAttribute("src", "http://66.102.9.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
    document.getElementById('66.102.11').setAttribute("src", "http://66.102.11.104/search?q="+document.f.q.value+"&hl=fr&meta="+document.f.meta.value+"&start="+n);
} 


//-->
</script><form name="f" action="javascript:go()">
Rechercher : <input class="champstexte" type="text" name="q" size="50"> <input type="button" onClick="go()" value="OK" class="bouton">
<br>
Rechercher dans : <select name="meta"><option value="" selected>tout le web</option><option value="lr%3Dlang_fr">pages francophones</option></select>
</form><hr width="50%" size="1">
<table width="800">
<tr><td width=100><center><a href="http://216.239.37.104/" target="_blank">216.239.37</a><br>www-va</center></td><td><iframe name="216.239.37" id="216.239.37" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://216.239.39.104/" target="_blank">216.239.39</a><br>www-dc</center></td><td><iframe name="216.239.39" id="216.239.39" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://216.239.53.104/" target="_blank">216.239.53</a><br>www-in</center></td><td><iframe name="216.239.53" id="216.239.53" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://216.239.57.104/" target="_blank">216.239.57</a><br>www-cw</center></td><td><iframe name="216.239.57" id="216.239.57" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://216.239.59.104/" target="_blank">216.239.59</a><br>www-gv</center></td><td><iframe name="216.239.59" id="216.239.59" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://216.239.63.104/" target="_blank">216.239.63</a></center></td><td><iframe name="216.239.63" id="216.239.63" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://64.233.161.104/" target="_blank">64.233.161</a></center></td><td><iframe name="64.233.161" id="64.233.161" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://64.233.167.104/" target="_blank">64.233.167</a></center></td><td><iframe name="64.233.167" id="64.233.167" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://64.233.171.104/" target="_blank">64.233.171</a></center></td><td><iframe name="64.233.171" id="64.233.171" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://64.233.179.104/" target="_blank">64.233.179</a></center></td><td><iframe name="64.233.179" id="64.233.179" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://64.233.183.104/" target="_blank">64.233.183</a></center></td><td><iframe name="64.233.183" id="64.233.183" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://64.233.185.104/" target="_blank">64.233.185</a></center></td><td><iframe name="64.233.185" id="64.233.185" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://64.233.187.104/" target="_blank">64.233.187</a></center></td><td><iframe name="64.233.187" id="64.233.187" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://64.233.189.104/" target="_blank">64.233.189</a></center></td><td><iframe name="64.233.189" id="64.233.189" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://66.102.7.104/"   target="_blank">66.102.7</a><br>www-mc</center></td><td><iframe name="66.102.7" id="66.102.7" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://66.102.9.104/"   target="_blank">66.102.9</a><br>www-lm</center></td><td><iframe name="66.102.9" id="66.102.9" width="100%" height="400"></iframe></td></tr>
<tr><td width=100><center><a href="http://66.102.11.104/"  target="_blank">66.102.11</a><br>www-kr</center></td><td><iframe name="66.102.11" id="66.102.11" width="100%" height="400"></iframe></td></tr>
</table>
</div><?php


}  // fin autorisation

require(ADMIN . 'tail.php');

?>
