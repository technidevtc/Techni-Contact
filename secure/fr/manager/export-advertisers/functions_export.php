<?php

	function clean_export($string){
		//Decode special chars returned from the database (é à ..)
		$string		= utf8_decode($string);
		//Decode encoded chars from the database (Wysiwyg editor..) & clean html tags
		//$string		= html_entity_decode($string);
		//$string		= strip_tags($string);
		
		$string		= str_replace("\r"," - ",$string);
		$string		= str_replace("\n\r"," - ",$string);
		$string		= str_replace("\r\n"," - ",$string);
		$string		= str_replace("\n"," - ",$string);
		
		$string		= str_replace(";",",",$string);
		return $string;
	}
	
	function clean_export_description($string){
		//Decode special chars returned from the database (é à ..)
		//$string		= utf8_decode($string);
		//Decode encoded chars from the database (Wysiwyg editor..) & clean html tags
		$string		= html_entity_decode($string);
		$string		= utf8_decode($string);
		$string		= strip_tags($string);
		
		$string		= str_replace("\r"," - ",$string);
		$string		= str_replace("\n\r"," - ",$string);
		$string		= str_replace("\r\n"," - ",$string);
		$string		= str_replace("\n"," - ",$string);
		
		$string		= str_replace(";",",",$string);
		return $string;
	}
	
	/*function clean_tableau_prix($string){
		$string 		= unserialize($string);
	
		$return_string	= "Type: ".$string[0];
		$return_string	.= " Matiere: ".$string[1];
		$return_string	.= " Taille: ".$string[2];
		$return_string	.= " Coloris: ".$string[3];
		$return_string	.= " Condition de vente: ".$string[4];
		$return_string		= str_replace(";",",",$return_string);
		return $return_string;
	}*/
	
	function clean_references_arrays($references_header, $content){
		if(!empty($references_header) && !empty($content)){
			$string						= ' ';
			$content_array				= mb_unserialize($content);
			
			$references_header_array	= mb_unserialize($references_header);
			$column_start				= 3;
			// -1 to have the index column_start=> -5 to eliminate the end of array
			$column_stop				= count($references_header_array)-5;
			//We will reduce the first columns
			$column_stop				= $column_stop - $column_start;
			
			$local_loop					= 0;
			while(!empty($references_header_array[$column_start]) &&  $local_loop!=$column_stop){
				
				$string		.= '-'.$references_header_array[$column_start].': '.$content_array[$local_loop].' ';
				
				//Local_loop for the content_array (value)
				$local_loop++;
				//column_start for the header (name)
				$column_start++;
			}
			
			$string	= clean_export($string);
			return $string;
		}
	}
	
	/*function serialize_fix_callback($match) {
		return 's:'.strlen($match[2]);
	} 

	function mb_unserialize($s){
		$s = preg_replace_callback('!(?<=^|;)s:(\d+)(?=:"(.*?)";(?:}|a:|s:|b:|d:|i:|o:|O:|N;))!s','serialize_fix_callback',$s);
		return unserialize($s);
	}*/

?>