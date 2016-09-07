<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
if (TEST)
  exit();

require_once(ADMIN."logs.php");

$user = new BOUser();

if (!$user->login()) {
	echo "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login.";
	exit();
}

$file = "http://www.google.com/alerts/feeds/11169016124630468081/16160012459767503368";

// Crée et charge le flux
$rss = new DOMDocument();
$rss->load($file);

// Récupère toutes les entrées
$entriesList = $rss->getElementsByTagName("entry");

// Parcoure les entrées
for ($i=0; $i<$entriesList->length && $i < 15; ++$i) {
	$entry = $entriesList->item($i);

	$title = $entry->getElementsByTagName("title");
	$title = $title->item(0)->firstChild->textContent;

	$link = $entry->getElementsByTagName("link");
	$link = $link->item(0)->getAttribute("href");

	$content = $entry->getElementsByTagName("content")->item(0)->firstChild->textContent;

	echo "<a href=\"".$link."\" target=\"_blank\">".$title."</a><br/>";
}
?>
