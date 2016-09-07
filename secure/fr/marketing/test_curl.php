<?php
curl_setopt($curlhandle, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'rw+');
curl_setopt($curlHandle, CURLOPT_STDERR, $verbose);
?>
