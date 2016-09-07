$(function() {
	// Set the default dates
	var startDate	= Date.create().addDays(-6),	// 7 days ago
		endDate		= Date.create(); 				// today
	var range = $('#range');
	// Show the dates in the range input
	range.val(startDate.format('{dd}/{MM}/{yyyy}') + ' - ' + endDate.format('{dd}/{MM}/{yyyy}'));
	// Load chart
	
	 	 
	ajaxLoadChart(startDate,endDate);
	ajaxLoadChart_tx_deliveralite(startDate, endDate);
	ajaxLoadChart_tx_first_ouverture(startDate, endDate);
	ajaxLoadChart_tx_clic(startDate, endDate);
	ajaxLoadChart_tx_reactivite(startDate, endDate);
	email_send(startDate, endDate);
	tx_delivirabilite(startDate, endDate);
	tx_ouverture(startDate, endDate);
	updateListe_rapports(startDate, endDate);
	tx_de_click_blog(startDate, endDate);
	ajaxLoadChart_email_send(startDate, endDate);
	ajaxLoadchart_ouvertures_total(startDate, endDate);
	ajaxLoadchart_first_ouverture(startDate, endDate);
	ajaxLoadchart_nb_clics(startDate, endDate);
	ajaxLoadchart_nb_spam(startDate, endDate);
	date_html(startDate, endDate)
	range.daterangepicker({		
		startDate: startDate,
		endDate: endDate,		
		ranges: {  
            'Aujourd\'hui': ['today', 'today'],
            'Hier': ['yesterday', 'yesterday'],
            'Les 7 derniers jours': [Date.create().addDays(-6), 'today'],
            'Les 30 derniers jours': [Date.create().addDays(-29), 'today']
        }
	},function(start, end){	
		ajaxLoadChart(start, end);	
		ajaxLoadChart_tx_deliveralite(start, end);
		ajaxLoadChart_tx_first_ouverture(start, end);
		ajaxLoadChart_tx_clic(start, end);
		ajaxLoadChart_tx_reactivite(start, end);
		tx_de_click_blog(start, end);
		email_send(start, end);
		tx_delivirabilite(start, end);
		ajaxLoadchart_ouvertures_total(start, end);
		tx_ouverture(start, end);
		updateListe_rapports(start, end);
		ajaxLoadChart_email_send(start, end);
		ajaxLoadchart_first_ouverture(start, end);
		ajaxLoadchart_nb_spam(start, end);
		ajaxLoadchart_nb_clics(start, end);
		date_html(start, end);
		
	});	
	// The tooltip shown over the chart
	var tt = $('<div class="ex-tooltip">').appendTo('body'),	
	topOffset = -32;
	
	var tt_taux = $('<div class="ex-tooltip">').appendTo('body'),	
	topOffset_taux = -32;
	
	var data = {
		"xScale" : "ordinal",
		"yScale" : "linear",
		"main" : [{
			className : ".stats",
			"data" : []
		}]
	};
	
	var data2 = {
		"xScale" : "ordinal",
		"yScale" : "linear",
		"main" : [{
			className : ".stats2",
			"data" : []
		}]
	};

	
	var opts = {
		paddingLeft : 50,
		paddingTop : 20,
		paddingRight : 10,
		axisPaddingLeft : 25,
		tickHintX: 9, // How many ticks to show horizontally
		dataFormatX : function(x) {					
			return Date.create(x);
		},
		tickFormatX : function(x) {			
			return x.format('{dd}/{MM}');
		},	
		"mouseover": function (d, i) {
			var pos = $(this).offset();			
			tt.text(d.x.format('{dd}/{MM} ') + ': ' + d.y).css({				
				top: topOffset + pos.top,
				left: pos.left				
			}).show();
		},		
		"mouseout": function (x) {
			tt.hide();
		}
	};
	
	var opts_tx_deliv = {
		paddingLeft : 55,
		paddingTop : 25,
		paddingRight : 15,
		axisPaddingLeft : 30,
		tickHintX: 11, // How many ticks to show horizontally
		dataFormatX : function(x) {					
			return Date.create(x);
		},
		tickFormatX : function(x) {			
			return x.format('{dd}/{MM}');
		},	
		"mouseover": function (d, i) {
			var pos = $(this).offset();			
			tt_taux.text(d.x.format('{dd}/{MM}') + ': ' + d.y+'%').css({				
				top: topOffset_taux + pos.top,
				left: pos.left				
			}).show();
		},		
		"mouseout": function (x) {
			tt_taux.hide();
		}
	};	
	

	
	function ajaxLoadchart_nb_clics(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart_nb_clics.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/AJAX_nb_clics.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne
		}, function(data) {			
			var set = [];
			$.each(data, function() {  
				set.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			
						
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set
				}]
			};
			
			var chart_nb_clics		 = new xChart('bar', local_data, '#chart_nb_clics' , opts);	
			chart_nb_clics._resize();
		});
	}
	
	function ajaxLoadchart_nb_spam(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart_nb_spam.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}  
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/AJAX_nb_spam.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne
		}, function(data) {			
			var set = [];
			$.each(data, function() {  
				set.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			
			
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set
				}]
			};
			
			
			var chart_nb_spam 		= new xChart('bar', local_data, '#chart_nb_spam' , opts);	
		});
	}
	
	function ajaxLoadchart_first_ouverture(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart_first_ouverture.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/AJAX_first_ouverture.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne
		}, function(data) {			
			var set = [];
			$.each(data, function() {  
				set.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			

			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set
				}]
			};
			
			var chart_first_ouverture		 = new xChart('bar', local_data, '#chart_first_ouverture' , opts);	
			chart_first_ouverture._resize();
		});
	}
	
	
	function ajaxLoadchart_ouvertures_total(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart_ouvertures_total.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/AJAX_ouvertures_total.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne
		}, function(data) {			
			var set = [];
			$.each(data, function() {
				set.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			
			/*chart_ouvertures_total.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : set
				}]
			});*/
			
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set
				}]
			};
			
			
			var chart_ouvertures_total		= new xChart('bar', local_data, '#chart_ouvertures_total' , opts);	
			chart_ouvertures_total._resize();	
		});
	}
	
	function ajaxLoadChart_email_send(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/AJAX_nb_email_send.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne
		}, function(data) {
			
			var set_tx = [];
			$.each(data, function() {
				set_tx.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			
			
			
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set_tx
				}]
			};
			
			var chart 				 	 = new xChart('bar', local_data, '#chart_email_send' , opts);
			chart._resize();
			
		});
	}
	
		
	function ajaxLoadChart(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats1",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/ajax_aboutis.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne			
		}, function(data) {			
			var set_tx = [];
			$.each(data, function() {
				set_tx.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});	
			
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set_tx
				}]
			};
		
			var chart 				 	 = new xChart('bar', local_data, '#chart' , opts);
			
			chart._resize();
		});
	}
	
	
	function ajaxLoadChart_tx_deliveralite(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart_tx_deliveralite.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/ajax_deliverabilite.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne			
		}, function(data) {			
			var set = [];
			$.each(data, function() {
				set.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			
			/*chart_tx_deliveralite.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : set
				}]
			});*/
			
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set
				}]
			};
			
			
			//alert('After 2');
			
			var chart_tx_deliveralite	 = new xChart('bar', local_data, '#chart_tx_deliveralite' , opts_tx_deliv);	
			chart_tx_deliveralite._resize();
		});
	}
	
	function ajaxLoadChart_tx_first_ouverture(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart_tx_first_ouverture.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/ajax_first_ouverture.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne			
		}, function(data) {			
			var set = [];
			$.each(data, function() {
				set.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			
			/*chart_tx_first_ouverture.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : set
				}]
			});
			*/
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set
				}]
			};
			
			
			var chart_tx_first_ouverture = new xChart('bar', local_data, '#chart_tx_first_ouverture' , opts_tx_deliv);
		        chart_tx_first_ouverture._resize();			
			
		});
	}
	
	
	function ajaxLoadChart_tx_clic(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart_tx_clic.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/ajax_tx_clic.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne			
		}, function(data) {			
			var set = [];
			$.each(data, function() {
				set.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			
			/*chart_tx_clic.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : set
				}]
			});
			*/
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set
				}]
			};
			var chart_tx_clic			 = new xChart('bar', local_data, '#chart_tx_clic' , opts_tx_deliv);	
			
		});
	}
	
	function ajaxLoadChart_tx_reactivite(startDate,endDate) {
		// If no data is passed (the chart was cleared)
		var id_campagne = $("#id_campagne").val();
		if(!startDate || !endDate){
			chart_tx_reactivite.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : []
				}]
			});			
			return;
		}
		// Otherwise, issue an AJAX request
		$.getJSON('ajax_stats/ajax_reactivite.php', {			
			start:	startDate.format('{yyyy}-{MM}-{dd}'),
			end:	endDate.format('{yyyy}-{MM}-{dd}'),
			id_campagne: id_campagne			
		}, function(data) {			
			var set = [];
			$.each(data, function() {  
				set.push({
					x : this.label,
					y : parseInt(this.value, 10)
				});
			});			
			/*chart_tx_reactivite.setData({
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					data : set
				}]
			});
			*/
			var local_data	= {
				"xScale" : "ordinal",
				"yScale" : "linear",
				"main" : [{
					className : ".stats",
					"data" : set
				}]
			};
			
			var chart_tx_reactivite		 = new xChart('bar', local_data, '#chart_tx_reactivite' , opts_tx_deliv);	
		});
	}	
});

function email_send(startDate, endDate){
	var start = startDate.format('{yyyy}-{MM}-{dd}');
	var end   =	endDate.format('{yyyy}-{MM}-{dd}');
    var id_campagne = $("#id_campagne").val();
	$.ajax({
		url: 'ajax_stats/AJAX_Stats_Global.php?action_send=email_send&startDate='+start+'&endDate='+end+'&id_campagne='+id_campagne,
		type: 'GET',		
		success:function(data){			
			$("#blog-envoyer").html(data);
		}		
	});
}

function tx_delivirabilite(startDate, endDate){
	var start = startDate.format('{yyyy}-{MM}-{dd}');
	var end   =	endDate.format('{yyyy}-{MM}-{dd}');
    var id_campagne = $("#id_campagne").val();
	$.ajax({
		url: 'ajax_stats/AJAX_Stats_Global.php?action_send=tx_delivirabilite&startDate='+start+'&endDate='+end+'&id_campagne='+id_campagne,
		type: 'GET',		
		success:function(data){			
			$("#blog-deliverabilite").html(data);
		}		
	});
}

function tx_ouverture(startDate, endDate){
	var start = startDate.format('{yyyy}-{MM}-{dd}');
	var end   =	endDate.format('{yyyy}-{MM}-{dd}');
    var id_campagne = $("#id_campagne").val();
	$.ajax({
		url: 'ajax_stats/AJAX_Stats_Global.php?action_send=tx_ouverture&startDate='+start+'&endDate='+end+'&id_campagne='+id_campagne,
		type: 'GET',		
		success:function(data){			
			$("#blog-tx-ouverture").html(data);
		}		
	});
}

function tx_de_click_blog(startDate, endDate){
	var start = startDate.format('{yyyy}-{MM}-{dd}');
	var end   =	endDate.format('{yyyy}-{MM}-{dd}');
    var id_campagne = $("#id_campagne").val();
	// alert("aaa");
	$.ajax({
		url: 'ajax_stats/AJAX_Stats_Global.php?action_send=tx_de_click_blog&startDate='+start+'&endDate='+end+'&id_campagne='+id_campagne,
		type: 'GET',		
		success:function(data){			
			$("#blog-tx-click-blog").html(data);
		}		
	});
}

function date_html(startDate, endDate){
	var start = startDate.format('{dd}/{MM}/{yyyy}');
	var end   =	endDate.format('{dd}/{MM}/{yyyy}');
	$(".title-rapports").html("Rapport du "+start+" au "+end);
}

