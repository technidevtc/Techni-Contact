<?php
function showStarRater($note){
  $note = (int) $note;
  define('MAX_STAR_RATE', 10);
  define('NB_STARS', MAX_STAR_RATE/2);
  if($note < 0)
    $note = 0;
  
  if($note > MAX_STAR_RATE)
    $note = MAX_STAR_RATE;

  $full_stars = floor($note/2);
  $half_stars = $note%2;
  $empty_stars = 5-($full_stars+$half_stars);

  $html = '<ul class="star-rating">';
  for($a=1; $a<=NB_STARS;$a++){
    if($a <= $full_stars)
      $html .= '<li class="star-full"></li>';
    elseif($half_stars){
      $html .= '<li class="star-half"></li>';
      $half_stars -= 1;
    }else
      $html .= '<li class="star-empty"></li>';
  }
  $html .= '</ul>';
  echo $html;
}
?>
