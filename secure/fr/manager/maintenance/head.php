<?php

// new emails
$emails_new = Email::get('order by timestamp desc', 'limit 20');

// old emails
$emails_old = array();
$res = $db->query('SELECT * FROM emails_historic ORDER BY sent_time DESC LIMIT 20 OFFSET 0', __FILE__, __LINE__);
while ($email = $db->fetchAssoc($res))
  $emails_old[] = $email;

?>
<style type="text/css">
#server-test { position: fixed; bottom: 0px; z-index: 20001; width: 100%; height: 17px; margin: 0; padding: 2px 10px; background: #d73834 url(<?php echo MAINTENANCE_URL ?>test-header-bg.gif) repeat-y; border-top: 1px solid #cccccc }
#server-test .header-left { float: left; width: 550px; height: 17px; font: bold 14px verdana, arial, sans-serif; color: #ffffff; text-align: left }
#server-test .header-left button { position: relative; top: -1px; font-weight: bold; font-size: 10px; color: #a1030a; border: 0; background: #fff }
#server-test .header-right { float: right; width: 250px; height: 17px; font: bold 11px verdana, arial, sans-serif; color: #ffffff }
#server-test .header-right a { display: block; float: right; padding: 0 5px; font: bold 11px verdana, arial, sans-serif; color: #ffffff; text-decoration: none }
#server-test .header-right a:hover { text-decoration: underline }
#server-test .header-right .test_separator { display: block; float: right; padding: 0 5px; }

.test-emails { display: none; position: absolute; top: 0px; right: 0px; z-index: 20000; width: 720px; min-height: 300px; padding: 10px; background: #ffffff }
 .test-emails table { border: 1px solid #000000; border-collapse: collapse; font: normal 11px arial, sans-serif; color: #000000 }
 .test-emails th { padding: 2px; border-width: 1px; border-color: #000000; border-style: solid; background: #1a197c; color: #ffffff }
 .test-emails tr { cursor: pointer }
 .test-emails td { padding: 2px; border-width: 1px; border-color: #000000; border-style: solid }
 .test-emails a { display: block; float: right; margin: 0 20px 5px; font: bold 12px arial, sans-serif; color: #1a197c }
</style>
  <div id="test_emails_new" class="test-emails">
    <table>
      <thead>
      <tr>
        <th style="width: 100px">Date d'envoi</th>
        <th style="width: 200px">Adresses d'envoi</th>
        <th style="width: 300px">Sujet</th>
        <th style="width: 100px">Modèle</th>
      </tr>
      </thead>
      <tbody>
     <?php foreach ($emails_new as $email) : ?>
      <tr class="test-email" onclick="window.open('<?php echo MAINTENANCE_URL."show_mail_content.php?email_ver=2&emailID=".$email['id'] ?>', 'email_<?php echo $email['id'] ?>')" onmouseover="this.style.backgroundColor='#e8e8f8'" onmouseout="this.style.backgroundColor='#ffffff'">
        <td><?php echo date('d/m/Y H:i:s', $email['timestamp']) ?></td>
        <td><?php echo implode("<br/>", explode(",", $email['email'])) ?></td>
        <td><?php echo $email['subject'] ?></td>
        <td><?php echo $email['template'] ?></td>
      </tr>
     <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <div id="test_emails_old" class="test-emails">
    <table>
      <thead>
      <tr>
        <th style="width: 100px">Dates d'envoi</th>
        <th style="width: 200px">Adresses d'envoi</th>
        <th style="width: 300px">Sujet</th>
        <th style="width: 100px">Type de contenu</th>
      </tr>
      </thead>
      <tbody>
     <?php foreach ($emails_old as $email) : ?>
      <tr class="test-email" onclick="window.open('<?php echo MAINTENANCE_URL."show_mail_content.php?emailID=".$email['id'] ?>', 'email_<?php echo $email['id'] ?>')" onmouseover="this.style.backgroundColor='#e8e8f8'" onmouseout="this.style.backgroundColor='#ffffff'">
        <td><?php echo date('d/m/Y H:i:s', $email['sent_time']) ?></td>
        <td><?php echo $email['email'] ?></td>
        <td><?php echo $email['subject'] ?></td>
        <td><?php echo $email['content_type'] ?></td>
      </tr>
     <?php endforeach ?>
      </tbody>
    </table>
  </div>
<div id="server-test">
  <div class="header-left"><button id="ui-resizer-modified">Resize UI</button> TECHNI-CONTACT TEST SERVER (IP <?php echo $_SERVER['SERVER_ADDR'] ?>) MASTER</div>
  <div class="header-right">
    <a href="show old emails" onclick="$('#test_emails_old').toggle(); return false">Old emails</a>
    <span class="test_separator">|</span>
    <a href="show new emails" onclick="$('#test_emails_new').toggle(); return false">New emails</a>
  </div>
</div>
<script>
  var btn = document.getElementById("ui-resizer-modified");
  btn.onclick = function(){
    (function(d){if(self!=top||d.getElementById('toolbar')&&d.getElementById('toolbar').getAttribute('data-resizer'))return false;d.write('<!DOCTYPE HTML><html style="opacity:0;"><head><meta charset="utf-8"/></head><body><a data-viewport="320x480" data-icon="mobile">Mobile (e.g. Apple iPhone)</a><a data-viewport="320x568" data-icon="mobile" data-version="5">Apple iPhone 5</a><a data-viewport="600x800" data-icon="small-tablet">Small Tablet</a><a data-viewport="768x1024" data-icon="tablet">Tablet (e.g. Apple iPad 2-3rd, mini)</a><a data-viewport="1280x800" data-icon="notebook">Widescreen</a><a data-viewport="1920×1080" data-icon="tv">HDTV 1080p</a><scr'+'ipt src="https://secure-test.techni-contact.com/fr/ressources/scripts/resizer.min.js"></scr'+'ipt></body></html>')})(document);
  };
  
</script>
