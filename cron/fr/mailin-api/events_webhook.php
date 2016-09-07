<?php
	    $rest_json = file_get_contents("webhook.php");
	    $str = json_decode($rest_json, true);
	    if($str['event'] == 'request'){ 
	    	echo 'request';
	      // Insert the code you want to execute when event is equal to request.
	      // For example, send email or do some entries in database, etc.
	    }
	    else if($str['event'] == 'delivered'){
	    echo 'delivered';
	      // Insert the code you want to execute when event is equal to delivered.
	      // For example, send email or do some entries in database, etc.
	    }
?>