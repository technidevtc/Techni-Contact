<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$inputs = filter_input_array(INPUT_GET, array(
    "id" => array("filter" => FILTER_VALIDATE_INT, "options" => array("min_range" => 1, "max_range" => 0xffffff)),
    "format" => FILTER_SANITIZE_STRING,
    "num" => array("filter" => FILTER_VALIDATE_INT, "options" => array("min_range" => 1, "max_range" => 9))
  )
);

$valid_format = array("card", "thumb_big", "thumb_small", "zoom");

if ($inputs["id"] && in_array($inputs["format"], $valid_format) && $inputs["num"]) {
  $path = PRODUCTS_IMAGE_INC .$inputs["format"]."/".$inputs["id"]."-".$inputs["num"].".jpg";
  header("Content-type: image/jpg");
  if (is_file($path))
    @imagejpeg(imagecreatefromjpeg($path));
  else
    @imagegif(imagecreatefromgif(PRODUCTS_IMAGE_INC ."no-pic-".$inputs["format"].".gif"));
}
