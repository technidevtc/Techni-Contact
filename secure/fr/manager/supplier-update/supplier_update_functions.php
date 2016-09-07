<?php

	function mmf_convert_excel_params_columns_to_number($param){
		switch($param){
			case 'a':
				return 1;
			break;
			
			case 'b':
				return 2;
			break;
			
			case 'c':
				return 3;
			break;
			
			case 'd':
				return 4;
			break;
			
			case 'e':
				return 5;
			break;
			
			case 'f':
				return 6;
			break;
			
			case 'g':
				return 7;
			break;
		}	
	}
	
	function mmf_clean_reference($reference){
		$reference	= str_replace(" ","",$reference);
		$reference	= str_replace("-","",$reference);
		$reference	= str_replace("_","",$reference);
		$reference	= str_replace("*","",$reference);
		$reference	= str_replace("/","",$reference);
		$reference	= str_replace("+","",$reference);
		$reference	= str_replace("{","",$reference);
		$reference	= str_replace("}","",$reference);
		$reference	= str_replace("=","",$reference);
		$reference	= str_replace("#","",$reference);
		$reference	= str_replace("&","",$reference);
		$reference	= str_replace('\'',"",$reference);
		$reference	= str_replace("'","",$reference);
		$reference	= str_replace('"',"",$reference);
		$reference	= str_replace("\"","",$reference);
		$reference	= str_replace("(","",$reference);
		$reference	= str_replace(")","",$reference);
		$reference	= str_replace(",","",$reference);
		$reference	= str_replace(".","",$reference);
		$reference	= str_replace(";","",$reference);
		$reference	= str_replace("[","",$reference);
		$reference	= str_replace("]","",$reference);
		$reference	= str_replace("!","",$reference);
		$reference	= str_replace("?","",$reference);
		$reference	= str_replace(":","",$reference);
		$reference	= str_replace("<","",$reference);
		$reference	= str_replace(">","",$reference);
		$reference	= delete_special_chars($reference);
		return $reference;
	}
	
	function delete_special_chars($string){
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}
	
	
	function mmf_percent_difference_price($num_amount, $num_total) {
		$count1 = $num_amount / $num_total;
		$count2 = $count1 * 100;
		$count = number_format($count2, 0);
		return $count;
	}
	
	function mmf_round_up( $value, $precision ) { 
		$pow = pow ( 10, $precision ); 
		return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow; 
	} 
	
	function mmf_calculate_marge_or_remise($price, $type, $percent){
	
		if(strcmp($type,'marge')=='0'){
			//Calcul new price from the marge percent
			$new_price	= $price / (1 - $percent/100);
			//$new_price	= mmf_round_up($new_price, 2);	=> Round Upper
			$new_price	= round($new_price, 2);
			return $new_price;
		}else{
			//Calcul new price from the remise percent
			$new_price	= $price - (($price*$percent)/100);
			//$new_price	= mmf_round_up($new_price, 2);	=> Round Upper
			$new_price	= round($new_price, 2);	
			return $new_price;
		}
	}


?>