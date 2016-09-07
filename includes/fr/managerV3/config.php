<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 4 avril 2006
 
Mises à jour :

 Fichier : /includes/managerV2/config.php
 Description : Fonction manipulation variable de configuration par défaut

/=================================================================*/

function displayConfigs($handle) {
  $result = $handle->query("SELECT config_name, config_desc, config_value FROM config", __FILE__, __LINE__ );
  $ret = array();
  while ($record = $handle->fetch($result)) {
    $ret[$record[0]][0] = $record[1];
    $ret[$record[0]][1] = $record[2];
  }

  return $ret;
}

function getConfig($handle, $config_name) {
  $ret = false;

  $result = $handle->query("SELECT config_value FROM config WHERE config_name = '".$handle->escape($config_name)."'", __FILE__, __LINE__ );
  if ($handle->numrows($result, __FILE__, __LINE__) == 1)
    list($ret) = $handle->fetch($result);
  
  return $ret;
}

function setConfig($handle, $config_name, $config_value) {
  $result = $handle->query("UPDATE config SET config_value = '".$handle->escape($config_value)."' WHERE config_name = '".$handle->escape($config_name)."'",  __FILE__, __LINE__ );
  return true;
}

?>
