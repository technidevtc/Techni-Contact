function isAlreadyPickedVPC(id_lign,tel_pass,id_client,email_client,url_admin){
	var tel_pass      = tel_pass;
	var id_client 	  = id_client;
	var email_client  = email_client;
	var id_lign  	  = id_lign;
	var url_admin	  = url_admin;
	
	$(document).ready(function() {
		$.ajax({
				url: 'AJAX_verify_busy_vpc.php?id_ligne='+id_lign+'&action=campagne',
				type: 'GET',
				success:function(data){
					if(data == 'ok'){
						process_call_ligne(tel_pass,id_lign,id_client,url_admin);
					}else {
						alert(data);
					}
				}
		});
	});	
}

function isAlreadyPickedVPC_relance(id_lign,tel_pass,id_estimate,email_client,url_admin){
	var tel_pass      = tel_pass;
	var id_estimate   = id_estimate;
	var email_client  = email_client;
	var id_lign  	  = id_lign;
	var url_admin	  = url_admin;

	
	$(document).ready(function() {
		$.ajax({
				url: 'AJAX_verify_busy_vpc.php?id_ligne='+id_lign+'&action=relance',
				type: 'GET',
				success:function(data){
					if(data == 'ok'){
						process_call_ligne_relance(tel_pass,id_lign,id_estimate,url_admin);
					}else {
						alert(data);
					}
				}
		});
	});	
}

function isAlreadyPickedVPC_feedback(id_lign,tel_pass,id_order,email_client,url_admin){
	var tel_pass      = tel_pass;
	var id_order      = id_order;
	var email_client  = email_client;
	var id_lign  	  = id_lign;
	var url_admin	  = url_admin;
	
	$(document).ready(function() {
		$.ajax({
				url: 'AJAX_verify_busy_vpc.php?id_ligne='+id_lign+'&action=feedback',
				type: 'GET',
				success:function(data){
					if(data == 'ok'){
						process_call_ligne_feedback(tel_pass,id_lign,id_order,url_admin);
					}else {
						alert(data);
					}
				}
		});
	});	
}

function isAlreadyPickedVPC_rdv(id_lign,tel_pass,client_id,estimate_id,email_client,url_admin){
	var tel_pass      = tel_pass;
	var client_id     = client_id;
	var estimate_id   = estimate_id;
	var email_client  = email_client;
	var id_lign  	  = id_lign;
	var url_admin	  = url_admin;
	
	$(document).ready(function() {
		$.ajax({
				url: 'AJAX_verify_busy_vpc.php?id_ligne='+id_lign+'&action=rdv',
				type: 'GET',
				success:function(data){
					if(data == 'ok'){
						process_call_ligne_rdv(tel_pass,id_lign,client_id,estimate_id,url_admin);
					}else {
						alert(data);
					}
				}
		});
	});	
}

function isAlreadyPickedVPC_Requalif(id_lign,tel_pass,id_contact,email_client,url_admin){
	var tel_pass      = tel_pass;
	var id_contact     = id_contact;
	var email_client  = email_client;
	
	var id_lign  	  = id_lign;
	var url_admin	  = url_admin;
	
	$(document).ready(function() {
		$.ajax({
				url: 'AJAX_verify_busy_vpc.php?id_ligne='+id_lign+'&action=requalif',
				type: 'GET',
				success:function(data){
					if(data == 'ok'){
						process_call_ligne_Requalif(tel_pass,id_lign,id_contact,url_admin);
					}else {
						alert(data);
					}
				}
		});
	});	
}


function process_call_ligne_Requalif(tel,id_ligne,id_contact,url_admin){
		var joinabilite = $("#joinabilite-selector").val();
		var appel 		= $("#appel-selector").val();
		var users 		= $("#comm-selector").val();
		document.location.href = 'tel:'+tel;
        window.setTimeout(function(){
          location.href= url_admin+'supplier-leads/lead-detail.php?id='+id_contact+'&idCall='+id_ligne+'&params=display_bars&joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
        }, 100);
}

function process_call_ligne_rdv(tel,id_ligne,client_id,estimate_id,url_admin){
	var tel_pass      = tel_pass;
	var id_contact     = id_contact;
	var email_client  = email_client;
	
	var joinabilite = $("#joinabilite-selector").val();
		var appel 		= $("#appel-selector").val();
		var users 		= $("#comm-selector").val();
	document.location.href = 'tel:'+tel;
        window.setTimeout(function(){
		  if(  (client_id != '0') && (client_id != '')){
			location.href= url_admin+'clients/?idClient='+client_id+'&idCall='+id_ligne+'&params=display_bars&type=rdv&joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
	      }else if( (estimate_id != '0') && (estimate_id != '') ){
			location.href= url_admin+'estimates/estimate-detail.php?id='+estimate_id+'&idCall='+id_ligne+'&params=display_bars&joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
		  }
		}, 100);
}

function process_call_ligne(tel,id_ligne,id_client,url_admin){
		var joinabilite = $("#joinabilite-selector").val();
		var appel 		= $("#appel-selector").val();
		var users 		= $("#comm-selector").val();
		
		document.location.href = 'tel:'+tel;
        window.setTimeout(function(){
          location.href= url_admin+'clients/?idClient='+id_client+'&idCall='+id_ligne+'&params=display_bars&joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
        }, 100);
}

function process_call_ligne_relance(tel,id_ligne,id_estimate,url_admin){
		var joinabilite = $("#joinabilite-selector").val();
		var appel 		= $("#appel-selector").val();
		var users 		= $("#comm-selector").val();
		
		document.location.href = 'tel:'+tel;
        window.setTimeout(function(){
          location.href= url_admin+'estimates/estimate-detail.php?id='+id_estimate+'&idCall='+id_ligne+'&params=display_bars&joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
        }, 100);
}

function process_call_ligne_feedback(tel,id_ligne,id_order,url_admin){
		var joinabilite = $("#joinabilite-selector").val();
		var appel 		= $("#appel-selector").val();
		var users 		= $("#comm-selector").val();
		
		
		document.location.href = 'tel:'+tel;
        window.setTimeout(function(){
          location.href= url_admin+'orders/order-detail.php?id='+id_order+'&idCall='+id_ligne+'&params=display_bars&joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
        }, 100);
}

