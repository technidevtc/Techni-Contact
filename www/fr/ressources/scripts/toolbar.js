var $ = jQuery.noConflict();
$(window).load(function(){
	var inputData = $("#inputData").val();
	
	if(inputData != 2){
		 $(".opener").show();
		 $('#toolbar').stop().animate({'bottom': '-326px'}, 200);
		 $('#toolbar .open').stop().animate({'bottom': '326px'}, 200);
		 $(".opened").css("bottom","326");
		setTimeout(function(){        
			$('#toolbar').animate({bottom: "0px"}); 
			$(".opener").hide();
			
			$(".opened").hide();		
		},6000);
		setTimeout(function(){        
			$('.shader-left').animate({right: "0"});
		},3340);
	}else{
				$('#toolbar').stop().animate({'bottom': '-326px'}, 200);
				$('#toolbar .open').stop().animate({'bottom': '326px'}, 200);		
	}
    // $('.image').addClass('load');
});
$(document).ready(function() {
	
    $("#toolbar #toolbar_button").click(function(e){
		
			$(".opener").show();
			
			$(".opened").show();
            $('#toolbar').stop().animate({'bottom': '-326px'}, 200);
            $('.shader-left').animate({right: "-160px"});
            setTimeout(function(){
                $('#toolbar .open').stop().animate({'bottom': '326px'}, 200);
            }, 200);
    });
    
    $("#toolbar .open").click(function(e){
		var inputData = $("#inputData").val();
		//if(inputData != 2){
			$(".opener").hide();
			
            $(this).stop().animate({'bottom': '0'}, 200);
            setTimeout(function(){
                $('#toolbar').stop().animate({'bottom': '0'}, 230);
            }, 180);
            setTimeout(function(){              
                $('.shader-left').animate({right: "0"});            
            },100);
		//}
    });
});