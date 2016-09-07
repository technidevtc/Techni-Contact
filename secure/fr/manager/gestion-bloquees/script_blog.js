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
			url: 'AJAX_table_blog.php',
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
	
function charger_tag_popup(){
	$.ajax({
		url: 'controller/class.controller.php?action=charger_tag_popup',
		type: 'GET',
		success:function(data){
			$("#result_tag").html(data);
		}
	});	
		$("#charger-visuel" ).addClass( "visible");
		$("#charger-visuel" ).addClass( "visible");
}

function create_tag(){
	var name = $("#name_tag").val();
	if(name != "" ){
		$.ajax({
		url: 'controller/class.controller.php?action=create_tag&name='+name,
		type: 'GET',
		success:function(data){
			$("#result_tag").html(data);
			$("#name_tag").val('');
		}
	});
	}
}

function delete_tag(id){
	if(confirm("Etes vous sûr de supprimer le Tag ?")){
		$.ajax({
				url: 'controller/class.controller.php?action=delete_tag&id='+id,
				type: 'GET',
				success:function(data){
				$("#result_tag").html(data);	
				}
			});
	}
}

function delete_article(id){
	if(confirm("Etes vous sûr de supprimer cette article ?")){
		$.ajax({
				url: 'controller/class.controller.php?action=delete_article&id='+id,
				type: 'GET',
				success:function(data){
					window.location.href = "index.php?delete_article=success";
				}
			});
	}
}


function double_click(id){
	
$(document).on('dblclick', '#name-tag_sty'+id, function () {
	$("#name_tag_"+id).hide();
	$("#value_tag_"+id).show();
	
	var value_id  =  $("#keyUp-enter").val();
	if(value_id.indexOf(id) < 0){
		var value_id_plus = value_id.concat("-"+id);
		$("#keyUp-enter").val(value_id_plus);
	}
});

}
  
function double_click_add_article(id){
	$(document).on('dblclick', '#dbl_clicl_event_'+id, function () {
		var value_txt  =  $("#des_label_"+id).text();
		var value_id  =  $("#keyUp-enter").val();
		if(value_id.indexOf(id) < 0){
			var value_id_plus = value_id.concat("-"+id);
			$("#keyUp-enter").val(value_id_plus);
			$("#tag_selected").append('<div style="overflow: hidden;margin-bottom: -3px;" id="delete_tag_html_'+id+'"><div style="float:left;margin-top: 5px;" class="des_label">'+value_txt+'</div><div style=" float: right;    margin-top: -6px;"><img src="images/supprimer-vide-ordures-corbeille-corbeille-icone-5257-96.png" width="35px" onclick="delete_tag_html('+id+')" style="cursor: pointer;" /></div></div>');
		}
	});
}



function delete_tag_html(id){
	var value_id  =  $("#keyUp-enter").val();
	text = value_id.replace("-"+id, "");
	$("#keyUp-enter").val(text);	
	$("#delete_tag_html_"+id).remove();
}

$(document).keypress(function(event) {
 var key = event.which;
 if(key == 13){
	var id_key	 = $("#keyUp-enter").val();
	var myArray  = id_key.split('-');
	for(var i=0;i<myArray.length;i++){
		if(myArray[i].length  != 0 ){			
			var name = $("#value_tag_"+myArray[i]).val();
			var id = myArray[i];
			$.ajax({
				url: 'controller/class.controller.php?action=update_tag&id='+myArray[i]+'&name='+name,
				type: 'GET',
				success:function(data){
					$("#result_tag").html(data);
					$("#keyUp-enter").val('');
					/*$("#value_tag_"+id).val(name);
					$("#name_tag_"+id).html(name);
					$("#value_tag_"+id).hide();
					$("#name_tag_"+id).show();*/
				}
			});
		}
    }
  }
});