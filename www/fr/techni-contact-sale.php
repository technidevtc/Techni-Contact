<?php
// Your organization ID
//
$organization = "1287012";
// Your checksum code
//
$checksumCode = "702235887";
// Value of the sale.
// Leave as "0.00" if not applicable.
//
$orderValue = "0.00";
// Currency of the sale.
// Leave as "EUR" if not applicable.
//
$currency = "EUR";
// Event ID
//
$event = "123899";
// Event type:
// true = Sale
// false = Lead
//
$isSale = true;
// Encrypted connection on this page:
// true = Yes (https)
// false = No (http)
//
$isSecure = true;
// Here you must specify a unique identifier for the transaction.
// For a sale, this is typically the order number.
//
$orderNumber = "xxxxxxxx";
// If you do not use the built-in session functionality in PHP, modify
// the following expressions to work with your session handling routines.
//
$tduid = "";
if (!empty($_SESSION["TRADEDOUBLER"]))
$tduid = $_SESSION["TRADEDOUBLER"];
// OPTIONAL: You may transmit a list of items ordered in the reportInfo
// parameter. See the implementation manual for details.
//
$reportInfo = "";
$reportInfo = urlencode($reportInfo);
/***** IMPORTANT: *****/
/***** In most cases, you should not edit anything below this line. *****/
/***** Please consult with TradeDoubler before modifying the code. *****/

if (!empty($_COOKIE["TRADEDOUBLER"]))
$tduid = $_COOKIE["TRADEDOUBLER"];
if ($isSale)
{
$domain = "tbs.tradedoubler.com";
$checkNumberName = "orderNumber";
}
else
{
$domain = "tbl.tradedoubler.com";
$checkNumberName = "leadNumber";
$orderValue = "1";
}
$checksum = "v04" . md5($checksumCode . $orderNumber . $orderValue);
if ($isSecure)
$scheme = "https";
else
$scheme = "http";
$trackBackUrl = $scheme . "://" . $domain . "/report"
. "?organization=" . $organization
. "&amp;event=" . $event
. "&amp;" . $checkNumberName . "=" . $orderNumber
. "&amp;checksum=" . $checksum
. "&amp;tduid=" . $tduid
. "&amp;reportInfo=" . $reportInfo;
if ($isSale)
{
$trackBackUrl
.= "&amp;orderValue=" . $orderValue
. "&amp;currency=" . $currency;
}
echo "<img src=\"" . $trackBackUrl . "\" alt=\"\" style=\"border: none\" />";
?>