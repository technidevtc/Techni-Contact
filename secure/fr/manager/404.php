<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = $navBar = "Not found";

header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
header("Status: 404 Not Found");

require(ADMIN."head.php");
?>
ERREUR 404<br/>Cette page n'existe pas
<?php
require(ADMIN."tail.php");
