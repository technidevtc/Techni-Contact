function get_agent_infos(){

	var OSName	= "unknown OS";
	if (navigator.appVersion.indexOf("Win")!=-1) OSName="Windows";
	if (navigator.appVersion.indexOf("Mac")!=-1) OSName="MacOS";
	if (navigator.appVersion.indexOf("X11")!=-1) OSName="UNIX";
	if (navigator.appVersion.indexOf("Linux")!=-1) OSName="Linux";

	//Browser
	var user_browser	= 'Uknown';
	
	var nVer = navigator.appVersion;
	var nAgt = navigator.userAgent;
	var browserName  = navigator.appName;
	var fullVersion  = ''+parseFloat(navigator.appVersion); 
	var majorVersion = parseInt(navigator.appVersion,10);
	var nameOffset,verOffset,ix;

	// In Opera, the true version is after "Opera" or after "Version"
	if ((verOffset=nAgt.indexOf("Opera"))!=-1) {
	 browserName = "Opera";
	 fullVersion = nAgt.substring(verOffset+6);
	 if ((verOffset=nAgt.indexOf("Version"))!=-1) 
	   fullVersion = nAgt.substring(verOffset+8);
	}
	// In MSIE, the true version is after "MSIE" in userAgent
	else if ((verOffset=nAgt.indexOf("MSIE"))!=-1) {
	 browserName = "Microsoft Internet Explorer";
	 fullVersion = nAgt.substring(verOffset+5);
	}
	// In Chrome, the true version is after "Chrome" 
	else if ((verOffset=nAgt.indexOf("Chrome"))!=-1) {
	 browserName = "Chrome";
	 fullVersion = nAgt.substring(verOffset+7);
	}
	// In Safari, the true version is after "Safari" or after "Version" 
	else if ((verOffset=nAgt.indexOf("Safari"))!=-1) {
	 browserName = "Safari";
	 fullVersion = nAgt.substring(verOffset+7);
	 if ((verOffset=nAgt.indexOf("Version"))!=-1) 
	   fullVersion = nAgt.substring(verOffset+8);
	}
	// In Firefox, the true version is after "Firefox" 
	else if ((verOffset=nAgt.indexOf("Firefox"))!=-1) {
	 browserName = "Firefox";
	 fullVersion = nAgt.substring(verOffset+8);
	}
	// In most other browsers, "name/version" is at the end of userAgent 
	else if ( (nameOffset=nAgt.lastIndexOf(' ')+1) < 
			  (verOffset=nAgt.lastIndexOf('/')) ) 
	{
	 browserName = nAgt.substring(nameOffset,verOffset);
	 fullVersion = nAgt.substring(verOffset+1);
	 if (browserName.toLowerCase()==browserName.toUpperCase()) {
	  browserName = navigator.appName;
	 }
	}
	// trim the fullVersion string at semicolon/space if present
	if ((ix=fullVersion.indexOf(";"))!=-1)
	   fullVersion=fullVersion.substring(0,ix);
	if ((ix=fullVersion.indexOf(" "))!=-1)
	   fullVersion=fullVersion.substring(0,ix);

	majorVersion = parseInt(''+fullVersion,10);
	if (isNaN(majorVersion)) {
	 fullVersion  = ''+parseFloat(navigator.appVersion); 
	 majorVersion = parseInt(navigator.appVersion,10);
	}

	user_browser = 'Browser name  = '+browserName;
	user_browser += ' Full version  = '+fullVersion;
	user_browser += ' Major version = '+majorVersion;
	user_browser += ' Navigator.appName = '+navigator.appName;
	user_browser += ' Navigator.userAgent = '+navigator.userAgent;
	

	var user_time								= Math.round(+new Date()/1000);;
	document.getElementById('fos').value		= OSName;
	document.getElementById('fnavigator').value	= user_browser;
	document.getElementById('fusertime').value	= user_time;
}


//Detect which version of IE !
function isIE () {
  var myNav = navigator.userAgent.toLowerCase();
  return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
}


function calculate_user_count_draft(){

	$.ajax({
		url: 'extranet_v3_products_calculate_count_draft.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				//beforeSendHandler,
			},
		success: function(data) {
				
				if(data!=''){
					document.getElementById('user_draft_count').innerHTML=' ('+data+')';
					$("#user_draft_count").toggle( "slow", function() {
					
						if (isIE () == 7 || isIE () == 8 || isIE () == 9 || isIE () == 10) {
							document.getElementById('user_draft_count').style.display='block';
						}else{
							document.getElementById('user_draft_count').style.display='-webkit-inline-box';
						}
						
						// Animation complete.
						

						/*
						//Write
						$("#user_draft_count").css("outline", "0px solid #000000").animate({
							'outline-width': '2'
						}, 100);
						
						//Clear
						$("#user_draft_count").css("outline", "2px solid #000000").animate({
							'outline-width': '0'
						}, 200);
						
						//Write
						$("#user_draft_count").css("outline", "0px solid #000000").animate({
							'outline-width': '2'
						}, 100);
						
						//Clear
						$("#user_draft_count").css("outline", "2px solid #000000").animate({
							'outline-width': '0'
						}, 200);
						
						//Write
						$("#user_draft_count").css("outline", "0px solid #000000").animate({
							'outline-width': '2'
						}, 100);
						
						//Clear
						$("#user_draft_count").css("outline", "2px solid #000000").animate({
							'outline-width': '0'
						}, 200);
						*/
						
						if(document.URL=='http://secure-test.techni-contact.com/fr/extranet/extranet-v3-home.html' || document.URL=='https://secure-test.techni-contact.com/fr/extranet/extranet-v3-home.html' || 
						document.URL=='https://secure.techni-contact.com/fr/extranet/extranet-v3-home.html' || 
						document.URL=='http://secure.techni-contact.com/fr/extranet/extranet-v3-home.html'){
							//Start it for the first time !
							$("#user_draft_count").css("outline", "rgba(0, 0, 0, 1) solid 2px");
							//Write
							$("#user_draft_count").animate({
								//"border": "none",
								"outline-offset": "20px",
								"outline-color": "transparent"
							}, 700).promise().done(function(){
								
								//Start it again twice !
								
								$("#user_draft_count").css("outline-offset", "1px");
								$("#user_draft_count").css("outline", "rgba(0, 0, 0, 1) solid 2px");
								
								$("#user_draft_count").animate({
								//"border": "none",
								"outline-offset": "20px",
								"outline-color": "transparent"
								}, 700).promise().done(function(){
								
									//Start it for the third time !
									
									$("#user_draft_count").css("outline-offset", "1px");
									$("#user_draft_count").css("outline", "rgba(0, 0, 0, 1) solid 2px");
									
									$("#user_draft_count").animate({
									//"border": "none",
									"outline-offset": "20px",
									"outline-color": "transparent"
									}, 700);
								
								});
								
							});
						
						}//end if test home page !
						
						
					});
				}			
				
			},
		error: function() {
				//errorHandler,
			},
		// Form data
		//data: formData,
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});

}


function calculate_user_products_count_pending(){

	$.ajax({
		url: 'extranet_v3_products_calculate_products_count_pending.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				//beforeSendHandler,
			},
		success: function(data) {
				
				if(data!=''){
					document.getElementById('user_products_pending_count').innerHTML=' ('+data+')';
					$("#user_products_pending_count").toggle( "slow", function() {
					
						if (isIE () == 7 || isIE () == 8 || isIE () == 9 || isIE () == 10) {
							document.getElementById('user_products_pending_count').style.display='block';
						}else{
							document.getElementById('user_products_pending_count').style.display='-webkit-inline-box';
						}
						
					
						// Animation complete.
						
						/*
						$("#user_products_pending_count").css("outline", "rgba(0, 0, 0, 1) solid 2px");
						//Write
						$("#user_products_pending_count").animate({
							//"border": "none",
							"outline-offset": "20px",
							"outline-color": "transparent"
						}, 700);
						*/
						
						/*
						//Start it for the first time !
						$("#user_products_pending_count").css("outline", "rgba(0, 0, 0, 1) solid 2px");
						//Write
						$("#user_products_pending_count").animate({
							//"border": "none",
							"outline-offset": "20px",
							"outline-color": "transparent"
						}, 700).promise().done(function(){
							
							//Start it again twice !
							
							$("#user_products_pending_count").css("outline-offset", "1px");
							$("#user_products_pending_count").css("outline", "rgba(0, 0, 0, 1) solid 2px");
							
							$("#user_products_pending_count").animate({
							//"border": "none",
							"outline-offset": "20px",
							"outline-color": "transparent"
							}, 700).promise().done(function(){
							
								//Start it for the third time !
								
								$("#user_products_pending_count").css("outline-offset", "1px");
								$("#user_products_pending_count").css("outline", "rgba(0, 0, 0, 1) solid 2px");
								
								$("#user_products_pending_count").animate({
								//"border": "none",
								"outline-offset": "20px",
								"outline-color": "transparent"
								}, 700);
							
							});
							
						});
						*/
						
					});
				}			
				
			},
		error: function() {
				//errorHandler,
			},
		// Form data
		//data: formData,
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});

}

$( document ).ready(function() {
	calculate_user_count_draft();
	calculate_user_products_count_pending();
});


	//ctrl	=> 17
	//Alt 	=> 18
	//Maj	=> 20
	//Num Verr. => 144 
	//Windows => 92
	//Arrows => (Left 37, Right 39, Up 38, Down 40)
	//Escape => 27
	//Page Down => 34
	//Page Up	=> 33
	//Insert => 45
	//End => 35
	//Arrow => 36
	//PrintScreen => 44
	//Stop defil => 145
	//Pause => 19

	var keycode_avoid = ["17", "18" ,"20", "144", "92", "37", "39", "38", "40", "27", "34", "33", "45", "35", "36", "44", "145", "19"];
	
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

//Functions to open a link in a popup using Javascript (Row of a Table)
function open_link_blank(formid,link,target){

	//Function work correctly but if we have the parameters 
	//The form do not consider it "ex: page.html?id=XXX&name=XXXX  => it will be page.html?"
	//So we have to build a dynamic parameters into form
		
	if(document.getElementById(formid)){
	
		//First of all we have to flush the content of the form
		document.getElementById(formid).innerHTML	= '';
	
		//Detect if we have a parameters
		if(link.indexOf("?")!='-1'){
		
			//In that case we know that we have parameters 
			//So we start building the parameters dynamicly
			//But we need to take only the parameters part (not all the URL)
			
			var url_full 			= link.split("?");
			
			var url_parameters 		= url_full[1].split("&");
			var local_loop			= 0;
			var local_input			= '';
			var local_input_details = '';
			
			while(url_parameters[local_loop]){				
				local_input_details = url_parameters[local_loop].split("=");
				//Separating of the name and value
				local_input	= '<input type="hidden" name="'+local_input_details[0]+'" value="'+local_input_details[1]+'" />';
				
				//Including this element to the form
				document.getElementById(formid).innerHTML	+= local_input;
				
				//Incrementing the variable loop
				local_loop++;
			}//end while
		
		}//end detect of parameters
		
		//Changing the action of the form
		document.getElementById(formid).action	 =link;
		
		//Changing the target of the form
		document.getElementById(formid).target	 =target;
		
		//Send form
		document.getElementById(formid).submit();
	}//End if
	
}//End function open_link_blank

function open_link_self(link){
	window.location	 = link;
}

//Parsing the url params
function qs(qsParm) {
	var query = window.location.search.substring(1);
	var parms = query.split('&');
	for (var i=0; i<parms.length; i++) {
		var pos = parms[i].indexOf('=');
		if (pos > 0) {
			var key = parms[i].substring(0,pos);
			var val = parms[i].substring(pos+1);
			qsParm[key] = val;
			//alert(key+" ** "+val);
		}
	}
}