<?php
/* 
 * OD 05/01/2011
 */

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$dateStart = mktime(0,0,0,1,1,2004);

$db = DBHandle::get_instance();
$thisMonth = date('m');
$thisYear = date('Y');
$firstDayPreviousMonth = mktime(0, 0, 0, $thisMonth-1, 1, $thisYear);
$numberDaysPreviousMonth = date("t", $firstDayPreviousMonth);
$lastDayPreviousMonth = mktime(23, 59, 59, $thisMonth-1, $numberDaysPreviousMonth, $thisYear);

/**
 * get all advertisers who have leads credited on this month
 */
$query = 'select c.idAdvertiser from contacts c
          left join advertisers a on c.idAdvertiser = a.id ' .
          ' where c.credited_on > ' . $firstDayPreviousMonth . ' and c.credited_on < ' . $lastDayPreviousMonth .
          ' and (a.category = 0 or a.category = 5)'.
          ' group by c.idAdvertiser';

$result = $db->query($query, __FILE__, __LINE__ );

while ($advertisers = $db->fetch($result)) {
  /**
   * for every advertiser concerned, let's get the total income for the month
   */
  $CA = 0;
  $CAPos = 0;

  $query = 'select id, invoice_status, income, income_total, timestamp, credited_on, parent from contacts
    where ((timestamp > ' . $firstDayPreviousMonth . ' and timestamp < ' . $lastDayPreviousMonth . ')' .
    ' or (credited_on > ' . $firstDayPreviousMonth . ' and credited_on < ' . $lastDayPreviousMonth . ' ))' .
    ' and idAdvertiser = ' . $advertisers[0] .
    ' order by timestamp desc ';

  $result = $db->query($query, __FILE__, __LINE__ );

  $leads = array();

  while ($lead = $db->fetchAssoc($result)) {
    $leads[] = $lead;
    if ($lead["invoice_status"] & __LEAD_CHARGEABLE__ || $lead["invoice_status"] & __LEAD_CHARGED__){

        $CA += $lead["income_total"];
        $CAPos += $lead["income_total"];

    }elseif ($lead["invoice_status"] & __LEAD_CREDITED__){

        if( $lead['credited_on'] >=  $firstDayPreviousMonth && $lead['credited_on'] <=  $lastDayPreviousMonth){
          $CA -= $lead["income_total"];
        }
        if( $leal['parent'] > 0 ){
          $CA -= $lead["income"];
        }
    }
  }
  /**
   * if total income is negative, we report credited leads on next month until total income comes positive or equals 0
   */
  $CANeg = 0;
  foreach ($leads as $lead) {

    if ($lead["invoice_status"] & __LEAD_CREDITED__){
      $CANeg += $lead["income"];

      $creditedDay = date('d', $lead["credited_on"]);
      $creditedMonth = date('m', $lead["credited_on"]);
      $creditedYear = date('Y', $lead["credited_on"]);
      $nextCreditDay = mktime(0, 0, 0, $creditedMonth+1, $creditedDay, $creditedYear);

      $query = 'update contacts set credited_on = ' . $nextCreditDay . ' where id = ' . $lead['id'];

      if($CANeg >= $CAPos)
        $db->query($query, __FILE__, __LINE__ );

     }
   }
}
?>
