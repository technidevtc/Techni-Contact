// autocomplet : this function will be executed every time we change the text
function autocomplet() {
	var min_length = 0; // min caracters to display the autocomplete
	var keyword = $('#country_id').val();
	if (keyword.length >= min_length) {
		$.ajax({
			url: '../contacts/ajax_refresh.php',
			type: 'POST',
			data: {keyword:keyword},
			success:function(data){
				$('#country_list_id').show();
				$('#country_list_id').html(data);
			}
		});
	} else {
		$('#country_list_id').hide();
	}
}

function autocomplet_kpi() {
	var min_length = 0; // min caracters to display the autocomplete
	var keyword = $('#families_send').val();
	if (keyword.length >= min_length) {
		$.ajax({
			url: '../bi_kpi/ajax_refresh.php',
			type: 'POST',
			data: {keyword:keyword},
			success:function(data){
				$('#country_list_id').show();
				$('#country_list_id').html(data);
			}
		});
	} else {
		$('#country_list_id').hide();
	}
}


function set_item(item) {	
var res = item.replace(/\s/g,"");
	$('#country_id').val(res);
	$('#country_list_id').hide();
}

function set_item_kpi(item) {	
	$('#families_send').val(item);
	$('#country_list_id').hide();
}

function verify_email(){
	var emil = $('#country_id').val();
	var emailReg = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if( !emailReg.test(emil)) {
		alert('S\'il vous plaît entrez email valide');
		} else {
		var url = "/fr/manager/clients/?email="+emil;
		//$(location).attr('href',url);
		window.open(url, '_blank');
	}
	
	
}