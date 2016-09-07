<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

// Loading XML
$dom = new DomDocument();
$dom->validateOnParse = true;
$dom->load(XML_CATEGORIES_ALL);
$xPath = new DOMXPath($dom);

// Globals stats
$cat0 = $xPath->query("parent::categories",$dom->getElementById(XML_KEY_PREFIX."0"))->item(0);
$stats_key = explode("|",$xPath->query("child::stats_key",$cat0)->item(0)->nodeValue);
$stats = explode("|",$xPath->query("child::stats",$cat0)->item(0)->nodeValue);
for($sk = 0, $slen = count($stats); $sk < $slen; $sk++) $global[$stats_key[$sk]] = $stats[$sk];

$cat1List = $xPath->query("child::category", $cat0);

//$catTree = $xPath->query("ancestor-or-self::category",$dom->getElementById($curCategory));

$title = $navBar = "Sélection des produits à afficher en priorité par famille";
require(ADMIN."head.php");


?>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.DialogBox.blue.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.MISM.blue.css">
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>


<div class="titreStandard">Sélection des produits à afficher en priorité par famille</div>
<br />
<div class="categories">
	<div id="pdt_flagship" class="pdt-list-block" style="padding: 10px;">
		<div class="title">Rajouter un produits phares : </div><div class="edit"></div><div class="zero"></div>
		<input type="text" id="addProduct" value="" /> <span onclick="addPdt()" style="cursor: pointer;"><img src="img/add.png" /></span>
	</div>
	
	<div id="pdt_delata"></div>
	<div id="pdt_flagshipAll"></div>
</div>


<style>

.slides {
    
}


.slide-placeholder {
    background: #DADADA;
    position: relative;
}
.slide-placeholder:after {
    content: " ";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 15px;
    background-color: #FFF;
}


</style>


<div id="MPSDB"></div>
<script>
function getAllPdtflag(){
	// alert("here");
	$.ajax({
		url: 'AJAX_flagship.php',
		type: 'GET',
		success:function(data){
			$('#pdt_flagshipAll').html(data);
		}
	});	
}
	
	function addPdt(){
		var idPdt = $("#addProduct").val();
		if(idPdt !=''){
			$.ajax({
				url: 'AJAX_checkPdt.php?idPdt='+idPdt,
				type: 'GET',
				success:function(data){
					// $('#test').html(data);
					
					if(data == 0){
						alert(" 0 résultat pour `"+idPdt+"`");
					}else if(data == 1){
						getAllPdtflag();
					}else if(data == 2){
						alert("Vous avez déjà selectionné ce produit ! ");
					}
				}
			});
		}
	}
	
	$('#addProduct').keyup(function(e) {
      if(e.keyCode == 13) {
            var idPdt = $("#addProduct").val();
		$.ajax({
			url: 'AJAX_checkPdt.php?idPdt='+idPdt,
			type: 'GET',
			success:function(data){
				// $('#test').html(data);
				
				if(data == 0){
					alert(" 0 résultat pour `"+idPdt+"`");
				}else if(data == 1){
					getAllPdtflag();
				}else if(data == 2){
					alert("Vous avez déjà selectionné ce produit ! ");
				}
			}
		});
       }
	});
	
function deletePdfFlag(idPdt){
	$.ajax({
		url: 'AJAX_Deleteflagship.php?idPdt='+idPdt,
		type: 'GET',
		success:function(data){
			getAllPdtflag();
		}
	});		
}
getAllPdtflag();	

</script>
<?php
require(ADMIN."tail.php");
?>
