<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();
header("Content-Type: text/plain; charset=utf-8");

$email = $_GET['email'];


 
			$sql_service = "SELECT fonction_service FROM clients WHERE login='$email' ";
			$req_service = mysql_query($sql_service);
			$data_service= mysql_fetch_object($req_service);
			if($data_service->fonction_service == 'Maire' ) $selected_true1=" selected ";
			if($data_service->fonction_service == 'Elu municipal / Adjoint au maire' ) $selected_true2=" selected='true' ";
			if($data_service->fonction_service == 'Service Technique / Maintenance' ) $selected_true3=" selected='true' ";
			if($data_service->fonction_service == 'Service Achats' ) $selected_true4=" selected='true' ";
			if($data_service->fonction_service == 'Service Sports' ) $selected_true5=" selected='true' ";
			if($data_service->fonction_service == 'Service Communication' ) $selected_true6=" selected='true' ";
			if($data_service->fonction_service == 'Service Urbanisme' ) $selected_true7=" selected='true' ";
			if($data_service->fonction_service == 'Service RH' ) $selected_true8=" selected='true' ";
			if($data_service->fonction_service == 'Service Travaux' ) $selected_true9=" selected='true' ";
			
			echo '<label for="service">Service :</label>';
			echo '<select id="service" name="service">
            <option value=""> - </option>
            <option value="Maire" '.$selected_true1.'>Maire</option>
            <option value="Elu municipal / Adjoint au maire" '.$selected_true2.'>Elu municipal / Adjoint au maire</option>
            <option value="Service Technique / Maintenance" '.$selected_true3.'>Service Technique / Maintenance</option>
            <option value="Service Achats" '.$selected_true4.'>Service Achats</option>
            <option value="Service Sports" '.$selected_true5.'>Service Sports</option>
            <option value="Service Communication" '.$selected_true6.'>Service Communication</option>
            <option value="Service Urbanisme" '.$selected_true7.'>Service Urbanisme</option>
            <option value="Service RH" '.$selected_true8.'>Service RH</option>
            <option value="Service Travaux" '.$selected_true9.'>Service Travaux</option>
          </select>';

?>


