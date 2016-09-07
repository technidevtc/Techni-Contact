<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();
/*
$offset = 0;
$limit = 1000;
do {
  set_time_limit(120);
  $res = $db->query('SELECT id, sent, sent_times FROM emails_historic WHERE sent > 1 LIMIT '.$limit.' OFFSET '.$offset, __FILE__, __LINE__);
  if ($db->numrows($res) == 0)
    break;
  while ($email = $db->fetchAssoc($res)) {
    $email['sent_times'] = unserialize($email['sent_times']);
    if (count($email['sent_times']) != $email['sent'])
      echo $email['id']." has sent=".$email['sent']." but has ".count($email['sent_times'])." timestamps\n<br/>";
    $email['sent_times'] = array($email['sent_times'][0]);
    $db->query('UPDATE emails_historic SET sent = 1, sent_times = '.serialize($email['sent_times']).' WHERE id = '.$email['id'], __FILE__, __LINE__);
  }
} while (true);
*/
/*
$id_start = 0;
$id_interval = (0xffffffff>>7)+1;
$id_end = $id_start+$id_interval;
do {
  set_time_limit(100);
  $res = $db->query('SELECT id, sent_times FROM emails_historic WHERE id >= '.$id_start.' AND id < '.$id_end, __FILE__, __LINE__);
  $count = $db->numrows($res);
  if ($count == 0)
    break;
  $updated = 0;
  while ($email = $db->fetchAssoc($res)) {
    $email['sent_times'] = unserialize($email['sent_times']);
    if ($email['sent_times']) {
      $email['sent_times'] = $email['sent_times'][0];
      $db->query('UPDATE emails_historic SET sent_times = '.$email['sent_times'].' WHERE id = '.$email['id'], __FILE__, __LINE__);
      $updated++;
    }
  }
  flog("Examined ".$count." rows from id <b>".$id_start."</b> to id <b>".$id_end."</b>: Updated <b>".$updated."</b> rows\n");
  $id_start += $id_interval;
  $id_end += $id_interval;
} while (true);
*/

$db->query('update emails_historic set sent_times = SUBSTRING(sent_times,12,10) WHERE sent_times like "a:1:{%"', __FILE__, __LINE__);