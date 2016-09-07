

	function show_vpc_table(){
		$.ajax({
				url: 'AJAX_table_guide.php',
				type: 'GET',
				success:function(data){
					$('#result_forms').html(data);
				}
		});
	}	
	
	
	function set_item(item,id) {	
	var id_famille = $("#id_famille").val();

	new_item = item.replace(/'/g,"");
		
	var res = item.replace(/\s/g,"");
		
		$.ajax({
				url: 'controller/class.controller.php?action=views_blog&id_famille='+id+'&name='+item,
				type: 'GET',
				success:function(data){
					$("#familles_id").val(item);
					$("#blog_famille").html(data);
					
				}
		});		
		
	}
	
	function autocomplet() {				
		var min_length = 0; // min caracters to display the autocomplete
		var keyword = $('#familles_id').val();
		if (keyword.length >= min_length) {
			$.ajax({
				url: 'ajax_refresh.php',
				type: 'POST',
				data: {keyword:keyword},
				success:function(data){
					$('#familles_list_id').show();
					$('#familles_list_id').html(data);
				}
			});
		} else {
			$('#familles_list_id').hide();
		}
	}

	function enabled_families(id){
		if(confirm('Etes-vous sûr d\'activer l\'envoi de mails aux commerciaux ?')){
			$.ajax({
				url: 'controller/class.controller.php?action=enabled_families&id_famille='+id,
				type: 'GET',
				success:function(data){
					show_families_table();
				}
			});	
		}
	}
	
	function disabled_families(id){
		if(confirm('Etes-vous sûr de désactiver l\'envoi de mails aux commerciaux ?')){
			$.ajax({
				url: 'controller/class.controller.php?action=disabled_families&id_famille='+id,
				type: 'GET',
				success:function(data){
					// alert(data);
					show_families_table();
				}
			});
		}
	}
	
	function show_families_table(){
		$.ajax({
				url: 'AJAX_table_famillies.php',
				type: 'GET',
				success:function(data){
					$('#result_forms-table').html(data);
				}
		});
	}
	
	$(document).click(function(event) { 
    if(!$(event.target).closest('#familles_list_id').length && !$(event.target).is('#familles_list_id')) {
        if($('#familles_list_id').is(":visible")) {
            $(familles_list_id).hide();
        }
    }        
	});