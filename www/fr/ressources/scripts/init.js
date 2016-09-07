$(document).ready(function() {
$(document).mousemove(function(e) {
	//alert(e.clientY);
	if(e.clientY <= 10){
	var alerted = localStorage.getItem('alerted') || '';
	if (alerted != 'yes') {
	$('#exit_content').modal({onOpen: modalOpen, onClose: simplemodal_close});
	//$('#exit_content').modal({onOpen: modalOpen, onClose: simplemodal_close});
	 /*$(document).mouseup(function (e){
	 var container = $("#simplemodal-container");
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			//simplemodal_close2(simplemodal_close);
			
		}
	});*/
	var id_commercial 	= $("#id_commercial").val();
	var id_proucts 		= $("#id_proucts").val();
	$.ajax({
				url: 'traking_ajax.php?id_commercial='+id_commercial+'&id_proucts='+id_proucts,
				type: 'GET',
				success:function(data){
					
				}
	});
	 localStorage.setItem('alerted','yes');
	}
}
});
});


function modalOpen (dialog) {
	dialog.overlay.fadeIn(0, function () {
		dialog.container.fadeIn(0, function () {
			dialog.data.hide().slideDown(0);
		});
	});
}


function simplemodal_close(dialog) {
	dialog.data.fadeOut('fast', function () {
		dialog.container.hide('fast', function () {
			dialog.overlay.slideUp('fast', function () {
				$.modal.close();
			});
		});
	});
}

setInterval(function(){
	localStorage.clear();
},10000*100000);