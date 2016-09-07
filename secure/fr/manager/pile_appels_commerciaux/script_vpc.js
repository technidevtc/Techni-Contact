function affiche_popup(){
		$( "#example-popup" ).addClass( "visible");
	}
	
	function show_vpc_table(){
		var joinabilite = $("#joinabilite-selector").val();
		var appel 		= $("#appel-selector").val();
		var users 		= $("#comm-selector").val();
		$.ajax({
				url: 'AJAX_table_vpc.php?joinabilite='+joinabilite+'&appel='+appel+'&users='+users,
				type: 'GET',
				success:function(data){
					$('#result_forms').html(data);
				}
		});
	}
	
	function performance_ajax(){
		var access_droit = $("#access_droit").val();
		var id_users = $("#id_users").val();
		$.ajax({
				url: 'AJAX_vpc/AJAX_action_vpc.php?action=performance&access_droit='+access_droit+'&id_users='+id_users,
				type: 'GET',
				success:function(data){
					$('#performances_ajax').html(data);
				}
		});
	}
	
	$(document).ready(function() {
		performance_ajax();
	});
	
	$(document).ready(function() {
		show_vpc_table();
	});
	
	setInterval(function(){
		show_vpc_table();
		//$("#appel-selector").val($("#target option:first").val());
		//$("#joinabilite-selector").val($("#target option:first").val());
	},30000);
	
	setInterval(function(){
		 performance_ajax();
	},10000);
	
	function updateListe_filtre(){
		var joinabilite = $("#joinabilite-selector").val();
		var appel 		= $("#appel-selector").val();
		var users 		= $("#comm-selector").val();

			$.ajax({
					url: 'AJAX_table_vpc.php?joinabilite='+joinabilite+'&appel='+appel+'&users='+users,
					type: 'GET',
					success:function(data){
						$('#result_forms').html(data);
					}
			});
	}