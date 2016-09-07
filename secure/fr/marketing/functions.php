<?php
	session_name('marketing');
	session_start();
	
	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}
	
	function client_ip(){
		$ip		=	'';
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	//Instantiate the call of the DB
	$db = DBHandle::get_instance();
	
	
	function separate_every_three_chars_right_to_left($string){
		//This function is for the separation of every three caracters !
		
		$string_final =  number_format($string, 0, '.', ' ');
		$string_final =  str_replace(',', ' ', $string_final);
		
		return $string_final;
	}
	
	function separate_every_three_chars_left_to_right($string){
		//This function is for the separation of every three caracters !
		
		//To get the string length
		$string_length	= strlen($string);
		$string_length++;
		//The string to return
		$string_final	= '';
		//The position in the loop (incrementing with 3 every time)
		$string_local_position	= '0';
		
		while($string_length>0){
			
			$string_final	.=substr($string, $string_local_position, 3).' ';
			
			$string_local_position	= $string_local_position +3;
			$string_length 			= $string_length-3;
			
		}//End while
		
		return $string_final;
	}
	
	
	//For the Export
	function decode_export($string){
		return utf8_decode($string);
	}
	
	function normalize_export_csv($string){
		$string	= str_replace(";","#", $string);
		$string	= str_replace("\n"," ", $string);
		$string	= str_replace("\r"," ", $string);
		return $string;
	}

?>
