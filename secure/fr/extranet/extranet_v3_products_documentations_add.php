<?php
.....

function uploadDoc($field, $name, $dir, $type) {
  if(is_uploaded_file($_FILES[$field]['tmp_name']))
    copy($_FILES[$field]['tmp_name'], $dir.$name.$type);
}

......

?>