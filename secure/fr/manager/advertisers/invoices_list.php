<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();

$title = $navBar = "Facturation annonceurs";
require(ADMIN."head.php");

$yearS = isset($_GET['yearS']) ? (int)trim($_GET['yearS']) : date("Y");
$monthS = isset($_GET['monthS']) ? (int)trim($_GET['monthS']) : date("m");

$now = time();
$thisMonth = date('m', $now);
$thisYear = date('Y', $now);
$dateStart = mktime(0, 0, 0, $thisMonth, 1, $thisYear);
$numberDaysInMonth = (int) date("t", $dateStart);
$dateEnd = mktime(0, 0, 0, $thisMonth, $numberDaysInMonth, $thisYear);

?>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script type="text/javascript" src="invoices_list.js"></script>
<div class="titreStandard">Extracts des facturations annonceurs</div>
<br/>
  <div class="block">
           <div class="text">
                  <form action="../extracts/invoice_advertisers.php" method="post" style="float: right">
                          <div>
                                  <input type="hidden" id="DateBegin" name="DateBegin" value="<? echo date("d/m/Y", $dateStart) ?>" />
                                  <input type="hidden" id="DateEnd" name="DateEnd" value="<? echo date("d/m/Y", $dateEnd) ?>" />
                                  <input type="submit" value="Télécharger l'extract en xls" />
                          </div>
                  </form>
                  <form name="LeadList" action="leads.php" method="get">
                            <div id="DateFilter">
                                  <fieldset class="date-picker">
                                          <legend>Choix de la période de facturation :</legend>
                                          Année : <select name="yearS" id="YearID" onchange="FillMonthOptions(this.id, 'MonthID', 'DayID');"></select>
                                          Mois : <select name="monthS" id="MonthID" onchange="FillDayOptions('YearID', this.id, 'DayID');"></select>
                                          <input type="hidden" name="dayS" id="DayID" value="<?php echo $numberDaysInMonth ?>">
                                          <br/>
                                  </fieldset>

                          </div>
                  </form>
          </div>
  </div>
  <script type="text/javascript">
        FillYearOptions('YearID', 'MonthID', 'DayID');
	SetDateOptions('YearID', 'MonthID', 'DayID', '<?php echo $thisYear ?>','<?php echo $thisMonth ?>','<?php echo $numberDaysInMonth ?>');
        FillInterval('<?php echo $thisYear ?>','<?php echo $thisMonth ?>','<?php echo $numberDaysInMonth ?>');
  </script>
<?php require(ADMIN."tail.php") ?>
