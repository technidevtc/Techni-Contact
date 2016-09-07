<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 20 d�cembre 2004

 Fichier : /includes/managerV2/generator.php
 Description : G�n�rateurs d'�l�ments al�atoires

/=================================================================*/

/* G�n�rer un identifiant al�atoire
   o : r�f�rence mot de passe g�n�r� */
function & generatePassword($length = 8)
{
	$pass = '';
	for($i = 0; $i < $length; ++$i)
	{
		$type = mt_rand(1, 8);
		if ($type == 1) $pass .= mt_rand(0, 9);
		else if ($type > 1 && $type < 4) $pass .= chr(mt_rand(ord('A'), ord('Z')));
		else $pass .= chr(mt_rand(ord('a'), ord('z')));
	}
	return $pass;
}

/* G�n�rer un identifiant (compatibilit� id utilis�s dans me MDD except� pour les users & temppass)
   i : borne minimale
   i : borne maximale
   i : champ � tester
   i : nom table
   i : r�f handle connexion
   o : false ou id g�n�r� */
function generateID($min, $max, $field, $table, & $handle)
{
	do
	{
		$id = mt_rand($min, $max);
		$result = & $handle->query('select ' . $field . ' from ' . $table . ' where ' . $field . ' = \'' . $id . '\'', __FILE__, __LINE__);
	} while ($handle->numrows($result, __FILE__, __LINE__) > 0);
	return $id;
}

/* G�n�rer un identifiant (compatibilit� id utilis�s dans me MDD except� pour les users & temppass)
 * avec doctrine
   i : borne minimale
   i : borne maximale
   i : champ � tester
   i : nom table
   o : false ou id g�n�r� */
function doctrine_generateID($min, $max,  $class, $field = null)
{
  $instance = Doctrine_Core::getTable($class);
	do
	{
            $id = mt_rand($min, $max);
            if($field){
              $method = 'findBy'.ucfirst($field);
              $result = $instance->$method( $id );
            }else
              $result = $instance->find( $id );
	} while ($result->count());
	return $id;
}

/* G�n�rer un identifiant (compatibilit� id utilis�s dans me MDD except� pour les users & temppass)
   i : r�f handle connexion
   o : false ou id g�n�r� */
function generateIDTC(& $handle)
{
	do
	{
		$id = mt_rand(0,999999999);
		
		$result = & $handle->query("select idTC from products where idTC = " . $id, __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) > 0) continue;
		
		$result = & $handle->query("select idTC from products_add where idTC = " . $id, __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) > 0) continue;
		
		$result = & $handle->query("select idTC from products_add_adv where idTC = " . $id, __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) > 0) continue;
		
		$result = & $handle->query("select id from references_content where id = " . $id, __FILE__, __LINE__);
		if ($handle->numrows($result, __FILE__, __LINE__) > 0) continue;
		
		break;

	} while(true);
	
	return $id;
}

/* G�n�rer un identifiant al�atoire
  i : longueur identifiant
  i : champ � tester
  i : nom table
  i : r�f�rence handle connexion
  o : r�f�rence identifiant session */
function & generateSession($length, $field, $table, & $handle)
{
	$all = 'abcdefghijklmnopqrstuvwxyz0123456789';
	do
	{
		$session = '';
		for($i = 0; $i < $length; ++$i) $session .= $all[mt_rand(0, strlen($all) - 1)];
		$result = $handle->query('select ' . $field . ' from ' . $table . ' where ' . $field . ' = \'' . $session . '\'', __FILE__, __LINE__);

	} while($handle->numrows($result, __FILE__, __LINE__) >= 1);
	return $session;
}


?>
