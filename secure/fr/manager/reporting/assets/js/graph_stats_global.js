 

	
	// Set the default dates
	var startDate	= Date.create().addDays(-6),	// 7 days ago
		endDate		= Date.create(); 				// today
	var range = $('#range');
	// Show the dates in the range input
	range.val(startDate.format('{dd}/{MM}/{yyyy}') + ' - ' + endDate.format('{dd}/{MM}/{yyyy}'));
	// Load chart
	
	
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
	});	
	// The tooltip shown over the chart
	var tt = $('<div class="ex-tooltip">').appendTo('body'),	
	topOffset = -32;
	
	var data = {
		"xScale" : "ordinal",
		"yScale" : "linear",
		"main" : [{
			className : ".stats",
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
			tt.text(d.x.format('{dd}/{MM} ') + ': ' + d.y + ' SAV').css({				
				top: topOffset + pos.top,
				left: pos.left				
			}).show();
		},		
		"mouseout": function (x) {
			tt.hide();
		}
	};
	
	function ajaxLoadChart(startDate,endDate,typeG) {
		// If no data is passed (the chart was cleared)
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
		var startDate = $("#startDate").val();
		var endDate   = $("#endDate").val();
		$.getJSON('Ajax_sav/statsGraph/AJAX_statsGlobal.php', {			
			start:	startDate,
			end  :	endDate,
			type :  typeG
		}, function(data) {			
			var set_tx = [];
			$.each(data, function() {
				set_tx.push({
					x : this.label,
					y : parseInt(this.value)
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
			var chart 		= new xChart('bar', local_data, '#chart' , opts);
			chart._resize();			
		});
	}	