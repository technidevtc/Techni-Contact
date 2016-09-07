<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$inputs = filter_input_array(INPUT_POST, array(
  'zoneId' => FILTER_VALIDATE_INT,
  'controller' => FILTER_SANITIZE_ENCODED,
  'path' => array(
    'filter' => FILTER_SANITIZE_ENCODED,
    'flags'  => FILTER_REQUIRE_ARRAY
   ),
  'params' => array(
    'filter' => FILTER_SANITIZE_ENCODED,
    'flags'  => FILTER_REQUIRE_ARRAY
   )
));

$idList = Nuukik::get($inputs['zoneId'], $inputs['controller'], $inputs['path'], $inputs['params']);

if (is_string($idList)) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  $o = $idList;
} else {
  $o = Utils::get_pdts_infos($idList['pdtIdList'], $idList['idTCList'], 'simple-block');
}

print json_encode($o);

