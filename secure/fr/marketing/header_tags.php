
	<meta name="author" content="Techni-contact.com">
	<link rel="icon" type="image/png" href="favicon.png">

	
<?php 

	//JS
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/js/jquery.min.js"></script>');
	
	//FONTS
	/*echo('
	<link href="'.MARKETING_URL.'ressources/fonts/open_sans.css" rel="stylesheet" type="text/css" />');
	*/
	/*echo('
	<link href="'.MARKETING_URL.'ressources/css/font-awesome.min.css" rel="stylesheet" type="text/css" />');
	*/
	
	
	//CSS	
	echo('
	<link href="'.MARKETING_URL.'ressources/css/fontawesome_v_4.3.0/css/font-awesome.css" rel="stylesheet" type="text/css" />');
	
	//Start less after the call of the first file css
	//To force the less for the new navigators !
	echo('
	<link href="'.MARKETING_URL.'ressources/css/less/style_container.less" rel="stylesheet/less" media="all" />'); 
	
	echo('
	<script type="text/javascript">less = {}; less.env =\'development\';</script>');
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/css/less.js"></script>');
	
	
	
	
	//JS
	
	//Load that for not IE

	
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/js/scripts.js"></script>');
	
	//Angular Application 
	
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/js/angular.min.js"></script>');
	
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/js/app.js"></script>');	
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/js/directives.js"></script>');
	

	//Date Picker
	echo('
	<link rel="stylesheet" href="'.MARKETING_URL.'ressources/css/datepicker/themes/smoothness/jquery-ui.css" />');
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/js/datepicker/ui/jquery-ui.min.js"></script>');

	
	
	//Fancybox
	//Zoom in the pictures of a product
	
	/*
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/js/fancybox/jquery.fancybox.js?v=2.1.5"></script>');
	echo('
	<script type="text/javascript" src="'.MARKETING_URL.'ressources/js/fancybox/jquery.fancybox.pack.js?v=2.1.5"></script>');
	
	echo('
	<link rel="stylesheet" href="'.MARKETING_URL.'ressources/css/fancybox/jquery.fancybox.css" />');
	*/
	
	//Analytics Tags !
	//require_once('marketing_analytics_tags.html');


?>