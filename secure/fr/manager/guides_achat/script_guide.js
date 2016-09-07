var iMaxFilesize = 524288; // 0.5MB
var oTimer = 0;
var sResultFileSize = '';

function fileSelected(photo_type) {
   if(photo_type=='photo_facade'){
    // get selected file element
    var oFile = document.getElementById('photo_facade').files[0];
   }
   var rFilter = /^(image\/jpg|image\/gif|image\/jpeg|image\/png)$/i;
    if (! rFilter.test(oFile.type)) {
        $( "warnsize" ).append( "Votre fichier n&aposest pas une image accept&eacute;e, choisir: .jpg, gif ou png." );
        //document.getElementById('error').style.display = 'block';
        return;
    }
    // little test for filesize
    if (oFile.size > iMaxFilesize) {
        $( "warnsize" ).append( "l&apos;image d&eacute;passe la taille de 500ko !!!" );
        //document.getElementById('warnsize').style.display = 'block';
        return;
    }
    if(photo_type=='photo_facade'){
       // get preview element
        var oImage = document.getElementById('preview_facade');
   } 
    var oReader = new FileReader();
        oReader.onload = function(e){
        // e.target.result contains the DataURL which we will use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // binding onload event
            // we are going to display some custom image information here
            sResultFileSize = bytesToSize(oFile.size);         
        };
    };
    oReader.readAsDataURL(oFile);   
}


	function show_vpc_table(){
		$.ajax({
				url: 'AJAX_table_guide.php',
				type: 'GET',
				success:function(data){
					$('#result_forms').html(data);
				}
		});
	}	
	
	function show_vpc_table_famille(){
		$.ajax({
				url: 'AJAX_table_famille.php',
				type: 'GET',
				success:function(data){
					$('#result_forms').html(data);
				}
		});
	}	
		
	function check_famille_first(id_famille){
		
		var result="";
		$.ajax({
				url: 'controller/class.controller.php?action=check_famille_first&id_famille='+id_famille,
				type: 'GET',
				success:function(data){
					
					$("#first-famile").html("<strong>Famille 1 : </strong>"+data);
				}
		});		
	}
	
	function get_id_famille_first(id_famille){
		var result="";
		$.ajax({
				url: 'controller/class.controller.php?action=get_id_famille_first&id_famille='+id_famille,
				type: 'GET',
				success:function(data){
					$("#id_first_famille").val(data);
				}
		});		
	}
	
	function set_item(item,id) {	
	var id_famille = $("#id_famille").val();
	var Resultat = id_famille.indexOf(id);
	
	new_item = item.replace(/'/g,"");
	
	if(Resultat != '-1'){
		alert("Vous avez déjà sélectionné cette famille ("+item+")");
	}else{
	var res = item.replace(/\s/g,"");
		check_famille_first(id);
		get_id_famille_first(id);
		
		$.ajax({
				url: 'controller/class.controller.php?action=autocomplate_dynamic&id_famille='+id,
				type: 'GET',
				success:function(data){
					$("#first_famille").val(data);
				}
		});
		var nbr_families   = $("#nbr_families").val();
		
		var sum_int = 1;
		nbr_families_final = parseInt(nbr_families)+sum_int;
		$("#nbr_families").val(nbr_families_final);
		$('#familles_list_id').hide();
		var id_famille  = $("#id_famille").val();
		var id_famille_plus = id_famille.concat("-"+id);
		$("#id_famille").val(id_famille_plus);
		$(".right-dynamic-familles").show();
		$(".right-dynamic-familles").append('<div id="famille_dynamic_'+id+'" style="overflow: hidden;margin-bottom:7px;"><div><div style="float: left;">'+item+'</div><div style="float: right;" ><img src="images/delete-icon-ie6.png" onclick="delete_div(\''+id+'\',\''+new_item+'\')" style="cursor: pointer;"/></div><br /></div></div>');
		}
	}
	
	function autocomplet() {
		var first_famille = $("#first_famille").val();
		
		var min_length = 0; // min caracters to display the autocomplete
		var keyword = $('#familles_id').val();
		if (keyword.length >= min_length) {
			$.ajax({
				url: 'ajax_refresh.php',
				type: 'POST',
				data: {keyword:keyword,first_famille:first_famille},
				success:function(data){
					$('#familles_list_id').show();
					$('#familles_list_id').html(data);
				}
			});
		} else {
			$('#familles_list_id').hide();
		}
	}
	
	$(document).click(function(event) { 
    if(!$(event.target).closest('#familles_list_id').length && !$(event.target).is('#familles_list_id')) {
        if($('#familles_list_id').is(":visible")) {
            $(familles_list_id).hide();
        }
    }        
	});
	
	$( "#familles_id" ).click(function() {
		$( "#familles_list_id" ).show();
	});
		
	
	function delete_div(id,item){
		var id_famille  = $("#id_famille").val();
		var nbr_families   = $("#nbr_families").val();
		
		
		if(confirm("Etes vous sûr de supprimer la famille : "+item+" ?")){
			text = id_famille.replace("-"+id, "");
			$("#id_famille").val(text);
			if(nbr_families > 0){
				nbr_families_final = parseInt(nbr_families)-1;
				$("#nbr_families").val(nbr_families_final);
				nbr_families   = $("#nbr_families").val();
			if(parseInt(nbr_families) == 0){
				$("#first-famile").empty();
				$("#id_famille").val('');
				$("#first_famille").val('');
				$("#id_first_famille").val('');
				$(".right-dynamic-familles").hide();
			}
			} 
				
			$("#famille_dynamic_"+id).remove();
		}
	}
	
	function delete_div_update(id,item){
		var nbr_families    = $("#nbr_families").val();
		var id_famille  = $("#id_famille").val();
		if(confirm("Etes vous sur de supprimer la famille : "+item)){
			var delete_families = $("#delete_families").val();
			var id_famille_plus = delete_families.concat("-"+id);
			$("#delete_families").val(id_famille_plus);
			
			text = id_famille.replace("-"+id, "");
			$("#id_famille").val(text);
			
			if(nbr_families > 0){
				nbr_families_final = parseInt(nbr_families)-1;
				$("#nbr_families").val(nbr_families_final);
				nbr_families   = $("#nbr_families").val();
			if(parseInt(nbr_families) == 0){
				$("#first-famile").empty();
				$("#id_famille").val('');
				$("#first_famille").val('');
				$("#id_first_famille").val('');
				$(".right-dynamic-familles").hide();
			}
			} 
				
			$("#famille_dynamic_"+id).remove();
		}
	}
	
	
	
	function delete_guide(id){
		if(confirm("Etes vous sûr de supprimer ce guide d\'achat ? ")){
			$.ajax({
				url: 'controller/class.controller.php?action=delete_guide&id='+id,
				type: 'GET',
				success:function(data){
					window.location.href = "index.php?delete_guide=success";
				}
			});
		}
	}
	
function charger_visuel_popup(name,id){
		$.ajax({
				url: 'controller/class.controller.php?action=name_families&name='+name+'&id='+id,
				type: 'GET',
				success:function(data){
					$("#result_families").html(data);
				}
		});	
		$("#charger-visuel" ).addClass( "visible");
}
function charger_familie_popup(id){
$.ajax({
		url: 'controller/class.controller.php?action=charger_familie_popup&name='+name+'&id='+id,
		type: 'GET',
		success:function(data){
			$("#result_families").html(data);
		}
});	
		$("#charger-visuel" ).addClass( "visible");
$("#charger-visuel" ).addClass( "visible");
}

function delete_img(name,id){
	if(confirm("Etes vous sûr de supprimer la photo de la famillie : "+name+" ?")){
			$.ajax({
				url: 'controller/class.controller.php?action=delete_img&id='+id,
				type: 'GET',
				success:function(data){
					window.location.href = "gestion-familles.php";
				}
			});
		}	
}