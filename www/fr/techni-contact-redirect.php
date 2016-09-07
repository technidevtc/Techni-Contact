<?php
// Default landing page
//
$defaultUrl = "http://www.techni-contact.com";
// The domain under which this script is installed
//
$domain = "techni-contact.com";
if (!empty($_GET["tduid"]))
{
$cookieDomain = "." . $domain;
setcookie("TRADEDOUBLER", $_GET["tduid"],
(time() + 3600 * 24 * 365), "/", $cookieDomain);
// If you do not use the built-in session functionality in PHP, modify
// the following expression to work with your session handling routines.
//
$_SESSION["TRADEDOUBLER"] = $_GET["tduid"];
}
if (empty($_GET["url"]))
$url = $defaultUrl;
else
$url = urldecode(substr(strstr($_SERVER["QUERY_STRING"], "url"), 4));
header("Location: " . $url);
?>