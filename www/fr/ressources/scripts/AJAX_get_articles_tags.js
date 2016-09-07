function specs_tags_article(action){	
	$.ajax({
			url: 'ressources/ajax/AJAX_get_articles_tags.php?action='+action,
			type: 'GET',
			success:function(data){
				
				$("#result_articles_tags").html(data).slideDown("slow");
				 // $('#result_articles_tags').fadeOut('normal')html(data);
				 $("#result_articles_tags").hide().fadeIn(1000);//animating
				
			}
	});
}


function send_mail_blog(){
	var email = $("#email-send-article").val();
	var reg = new RegExp('^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$', 'i');
	if(reg.test(email)){
		
		$.ajax({
			url: 'http://www.techni-contact.com/ressources/ajax/AJAX_get_articles_tags.php?action=save_send_mail&email='+email,
			type: 'GET',
			success:function(data){	
			
				$("#form-input").hide(); 
				$("#result_email_send").show();
				  
				$("#result_email_send").html(data); 
				setTimeout(function() { 
					$("#result_email_send").hide(); 
				}, 5000);
				
				$("#result_email_send").html(data); 
				setTimeout(function() { 
					$("#form-input").show();
					$("#email-send-article").val('');
				}, 5001);
			}
		});
	}else{
		alert('Email invalide !');
	}
	
}


var CheminComplet = document.location.href;
var CheminRepertoire  = CheminComplet.substring( 0 ,CheminComplet.lastIndexOf( "/" ) );
var NomDuFichier     = CheminComplet.substring(CheminComplet.lastIndexOf( "/" )+1 );

var blog = CheminComplet.indexOf("blog");

if(blog > 0 ){
	$( "#my-account" ).remove();
	$( "#my-basket" ).remove();
	$( ".right-col-fixed" ).remove();
}
