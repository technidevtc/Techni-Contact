<?php

		require('Mailin.php');
		 
		$mailin = new Mailin('t.henryg@techni-contact.com', 'MnYUwd05CQZy8aWh');
		$id = 735;
	    var_dump($mailin->get_webhook($id));
		echo 'aaaa';
?>