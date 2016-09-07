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
  if(question != ''){
  	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=update_question&question="+question+"&id_question="+id,
			   success: function(msg){
			   	$("#ajax_question"+id).html('<strong>'+question+'</strong>');
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
  if(reponse != ''){
  	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=update_reponse&reponse="+reponse+"&id_reponse="+id,
			   success: function(msg){
			   	$("#ajax_reponse"+id).html(reponse);
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
	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=create_question&question_create="+question_create+"&pseudo_create="+pseudo_create+"&id_product_create="+id_product_create,
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
	if(reponse != ''){
	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=create_reponse&reponse="+reponse+"&pseudo_reponse="+pseudo_reponse+"&id_question="+id+"&name_products="+name_products,
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
	if(reponse != ''){
	$.ajax({
		      type: "POST",
			   url: "./class.controller.php?action=create_reponse&reponse="+reponse+"&pseudo_reponse="+pseudo_reponse+"&id_question="+id+"&name_products="+name_products,
			   success: function(msg){
			   	document.location = './fiche_q_a.php?id_product='+id_product_create;
				}
	});
	}else {
		alert("Reponse est obligatoire!");
	}
});




