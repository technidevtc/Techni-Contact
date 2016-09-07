function updateListe_filtre(){
	$.ajax({
			url: 'AJAX_table_stats_campgne.php',
			type: 'GET',
			beforeSend: function(){
				 $("#img-upload").show();
			},
			success:function(data){
				
				$('#loader_table').html(data);
			},
			complete: function(){
			 $("#img-upload").hide();
			}
	});
}

function updateListe_rapports(){
	var id_campagne = $("#id_campagne").val();
	$.ajax({
			url: 'AJAX_table_stats_rapport.php?id_campagne='+id_campagne,
			type: 'GET',
			beforeSend: function(){
				 $("#img-upload").show();
			},
			success:function(data){
				
				$('#loader_table').html(data);
			},
			complete: function(){
			 $("#img-upload").hide();
			}
	});
}

