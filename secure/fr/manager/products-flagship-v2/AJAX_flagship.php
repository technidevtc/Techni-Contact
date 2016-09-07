<?php 
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	$db = DBHandle::get_instance();
	
	$sqlPdt  =  "SELECT idProduct , `order` as oo
					FROM  products_flagship
				  ORDER BY `order`  ";
	$reqPdt  =   mysql_query($sqlPdt);
	$i=1;
	$j=1;
	echo '<div id="dragdiv">';
	echo '<ul  id="allItems" runat="server" class="slides"> ';
	while($dataPdt  =  mysql_fetch_object($reqPdt)){
		$sqlPdtDetail  =  " SELECT pf.name , nom1 , category
								FROM products_fr pf , advertisers aa
							WHERE pf.id='".$dataPdt->idProduct."'
							AND pf.idAdvertiser = aa.id ";
		$reqPdtDetail  =  mysql_query($sqlPdtDetail);
		$dataPdtDetail =  mysql_fetch_object($reqPdtDetail);
		
		if($dataPdtDetail->category  == 0 ) $cat = "Annonceur";
		if($dataPdtDetail->category  == 1 ) $cat = "Fournisseur";
		if($dataPdtDetail->category  == 2 ) $cat = "Annonceur non facturé";
		if($dataPdtDetail->category  == 3 ) $cat = "Prospect";
		if($dataPdtDetail->category  == 4 ) $cat = "Annonceur bloqué";
		if($dataPdtDetail->category  == 5 ) $cat = "Litige de paiement";
		
		echo '<li  id="'.$dataPdt->oo.'" class="floatDiv" >';
			echo '<input type="hidden" id="product'.$dataPdt->oo.'" value="'.$dataPdt->idProduct.'" />';
			echo '<div class="img-flag"><img src="'.SECURE_RESSOURCES_URL.'images/produits/thumb_small/'.$dataPdt->idProduct.'-1.jpg" /></div>';
			echo '<div>'.$dataPdtDetail->name.'</div><br />';
			echo '<div>'.$dataPdtDetail->nom1.'</div>';
			echo '<div>'.$cat.'</div>';
			echo '<div style="float: right;cursor: pointer;" onclick="deletePdfFlag('.$dataPdt->idProduct.')"><img src="img/cross.png"  /></div>';
		echo '</li>';
	if($i == 4){
		echo '<div class="sauteLigne"></div>';
	$i=0;
	}
	$i++;
	$j++;
	}
	echo '</ul>';
	echo '</div>';

?>

<script>
$(function() {
            $("#dragdiv li,#dropdiv li").draggable({
				appendTo: "body",
                helper: "clone",
                cursor: "move",
                revert: "invalid"
            });
			
			
			
			
			// alert(currentId);
            initDroppable($("#dropdiv li,#dragdiv li"));
            var arraySwappedElement = [];
			
			
            function initDroppable($elements) {
				
				$elements.droppable({
					
                    activeClass: "ui-state-default",
                    hoverClass: "ui-drop-hover",
                    accept: ":not(.ui-sortable-helper)",
					
                    over: function(event, ui) {
                        var $this = $(this);
					},
                    drop: function(event, ui) {
					
					/*var itemId = item.attr("id");
					var destId = item.parent().attr("id");
					var sourceId = event.target.id;
					alert('"' + itemId + '" was dragged from "' + sourceId + '" to "' + destId + '"');						
					*/
					var currentId = ui.draggable.attr('id');
					var nextID	  = this.id;
						
                        var $this = $(this);
                        var li1 = $('<li class="floatDiv" id="'+currentId+'">' + ui.draggable.html() + '</li>')
                        var linew1 = $(this).after(li1);
						
                        var li2 = $('<li class="floatDiv" id="'+nextID+'">' + $(this).html() + '</li>')
                        var linew2 = $(ui.draggable).after(li2);
                        
						// ToDo: You need to cheak whether same element is already exist or not.
                        arraySwappedElement.push(ui.draggable.html());
                        $(ui.draggable).remove();
                        $(this).remove();
						
						var idProductParent = $("#product"+currentId).val();
						var idProductNext = $("#product"+nextID).val();
						
						$.ajax({
							url: 'AJAX_GragChange.php?orderchange1='+currentId+'&idPdtCurrent='+idProductParent+'&orderchange2='+nextID+'&idProductNext='+idProductNext,
							type: 'GET',
							success:function(data){
								getAllPdtflag();
							}
						});
						
                        initDroppable($("#dropdiv li,#dragdiv li"));
                        $("#dragdiv li,#dropdiv li").draggable({
                            appendTo: "body",
                            helper: "clone",
                            cursor: "move",
                            revert: "invalid"
                        });
                    }
				
					
                });
            }
       
	});
	
</script>

<style>
	.floatDiv{
		
		margin-right: 25px;
		margin-bottom: 20px;
		padding: 7px;
		border: 1px solid #ccc;
		height: 160px;
		width: 170px;
		text-align: center;
		
	}
	
	.sauteLigne {
		clear: both;
	}
	
	#allItems li{
		float:left;
	}

.ui-drop-hover{border:2px solid #bbb;}
#dragdiv li,#dropdiv li{border:1px solid #bbb;}
#dropdiv li{padding-left:10px;}
#maindiv{width:500px;height:350px;border:2px solid #bbb;}
#allItems,#Ul1{list-style:none;}


</style>