function desactiver_reponse(id_question,etat){
	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=activer_question&id_question="+id_question+"&etat="+etat,
			   success: function(msg){
			   	//alert(msg);
				$("#etat_question"+id_question).html(msg);
			   }
	});	
}

function delete_reponse(id_reponse){
	var id_product = $("#id_product").val();
	if(confirm("Etes vous sur de supprimer cet reponse !")){
	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=delete_reponse&id_reponse="+id_reponse,
			   success: function(msg){
			   	document.location = './fiche_q_a.php?id_product='+id_product;
			   }
	});	
	}
}

$('.update_question').click(function(){
  var id = this.id;
  var question = $("#question_"+id).val();
  var textAreaString = question.replace(/\n\r/g,"<br />");
  var textAreaString = question.replace(/\n/g,"<br />");
  if(question != ''){
  	$.ajax({
		       type: "POST",
			   url: "./class.controller.php?action=update_question&question="+textAreaString+"&id_question="+id,
			   success: function(msg){
			   	 $("#ajax_question"+id).html('<strong>'+textAreaString+'</strong>');
				 $(".reveal-modal-bg").hide();
				 $(".reveal-modal").css("visibility", 'hidden');
				 $(".reveal-modal").css("top", '0');
				
			   }
	});
  }else {
	alert("Question est obligatoire!");
  }
});

$('.update_reponse').click(function(){
  var id = this.id;
  var reponse = $("#reponse_"+id).val();
  var textAreaString 	= reponse.replace(/\n\r/g,"<br />");
  var textAreaString    = reponse.replace(/\n/g,"<br />");
  if(reponse != ''){
  	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=update_reponse&reponse="+textAreaString+"&id_reponse="+id,
			   success: function(msg){
			   	$("#ajax_reponse"+id).html(textAreaString);
				 $(".reveal-modal-bg").hide();
				 $(".reveal-modal").css("visibility", 'hidden');
				 $(".reveal-modal").css("top", '0');
				
			   }
	});
  }else {
	alert("Reponse est obligatoire!");
  }
}); 

$('#create_question').click(function(){
	var question_create   = $("#question_create_qq").val();
	var pseudo_create     = $("#pseudo_create").val();
	var id_product_create = $("#id_product_create").val();
	var textAreaString 	  = question_create.replace(/\n\r/g,"<br />");
	var textAreaString    = question_create.replace(/\n/g,"<br />");
	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=create_question&question_create="+textAreaString+"&pseudo_create="+pseudo_create+"&id_product_create="+id_product_create,
			   success: function(msg){
			   	document.location = './fiche_q_a.php?id_product='+id_product_create;
				}
	});
});

$('.create_repondre_recent').click(function(){
	var id = this.id;
	var pseudo_reponse    = $("#pseudo_repondre_recent").val();
	var reponse           = $("#question_repondre_recent").val();
	var id_product_create = $("#id_product_create").val();
	var name_products     = $("#name_products").val();
	var textAreaString 	  = reponse.replace(/\n\r/g,"<br />");
	var textAreaString    = reponse.replace(/\n/g,"<br />");
	
	if(reponse != ''){
	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=create_reponse&reponse="+textAreaString+"&pseudo_reponse="+pseudo_reponse+"&id_question="+id+"&name_products="+name_products,
			   success: function(msg){
			   	document.location = './fiche_q_a.php?id_product='+id_product_create;
				}
	});
	}else {
		alert("Reponse est obligatoire!");
	}
});

$('.create_repondre_all').click(function(){
	var id = this.id;
	var pseudo_reponse    = $("#pseudo_repondre"+id).val();
	var reponse           = $("#question_repondre"+id).val();
	var id_product_create = $("#id_product_create").val();
	var name_products     = $("#name_products").val();
	var textAreaString 	  = reponse.replace(/\n\r/g,"<br />");
	var textAreaString    = reponse.replace(/\n/g,"<br />");
	if(reponse != ''){
	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=create_reponse&reponse="+textAreaString+"&pseudo_reponse="+pseudo_reponse+"&id_question="+id+"&name_products="+name_products,
			   success: function(msg){
			   	document.location = './fiche_q_a.php?id_product='+id_product_create;
				}
	});
	}else {
		alert("Reponse est obligatoire!");
	}
});


function send_mail(id_question){	
	$.ajax({
		type: "POST",
		url: "./class.controller.php?action=send_mail&id_question="+id_question,
		
		beforeSend: function(){
			$('#img-send-mail'+id_question).show();
		},
		
		success: function(msg){
			$(".result-ajax-send"+id_question).html(msg);
		},
		complete: function(){
			$('#img-send-mail'+id_question).hide();
		}
		
	});
}

function delete_question(id_question){
	var id_product = $("#id_product").val();
	if(confirm("Etes vous sur de supprimer cet reponse !")){
		$.ajax({
		type: "POST",
		url: "./class.controller.php?action=delete_question&id_question="+id_question,
		
		success: function(msg){
			document.location = './fiche_q_a.php?id_product='+id_product;	
		}
		
	});
	}
}



