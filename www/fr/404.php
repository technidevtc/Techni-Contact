<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

define("__404__", true);

header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
header("Status: 404 Not Found");

require(SITE."head.php");
?>
					<div class="blocks-left">
						<div class="error-404">
							ERREUR 404<br/>
							Cette page n'existe pas
						</div>
					</div>
					
<?php 
require(SITE."blocks-right.php");
require(SITE."foot.php");
exit();
?>