<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$path_parts = pathinfo(__FILE__);
$files = scandir($path_parts['dirname']);
$files_count = count($files);

for ($i = 0; $i < $files_count; $i++) {
  if ($files[$i] != $path_parts['basename'] && $files[$i] != "." && $files[$i] != ".." && strpos($files[$i], "XML_") === 0) {
    include $files[$i];
	}
}
