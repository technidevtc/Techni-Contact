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