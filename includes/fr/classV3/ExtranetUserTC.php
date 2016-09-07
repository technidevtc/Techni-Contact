<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       31 mai 2005 : = nouveau gestionnaire de rangs
                     + gestion session sécurisée avec contrôle adresse ip

 Fichier : /includes/classV2/ManagerUser.php4
 Description : Classe utilisateur manager

/=================================================================*/

session_name('extranetTC');
session_start();

/*$oldid = session_id();
session_regenerate_id();
unlink('/tmp/sess_' . $oldid);*/


define('CONTRIB',         0);
define('COMM',            1);
define('COMMADMIN',       2);
define('HOOK_NETWORK',    4);

class ExtranetUserTC
{
    /* id */
    var $id = -1;
    
    /* Login */
    var $login = '';
    
    /* Mot de passe (md5) */
    var $pass = '';
    
    /* Adresse ip */
    var $ip = '';

    /* Email */
    var $email = '';

    /* Rang utilisateur */
    var $rank = CONTRIB;

    /* Handle connexion */
    var $handle = NULL;


    /* Constructeur, effectue l'identification de l'utilisateur
       i : référence sur la connexion au SGBDR */
    function ExtranetUserTC(& $handle)
    {
        $this->handle = & $handle;
    }
    

    /* Identification utilisateur
       i : login
       i : pass
       o : true si identifié, false si erreur */
    function login($login = '', $pass = '')
    {
        $ret = false;

        if(isset($_SESSION['login']) && isset($_SESSION['pass']) && isset($_SESSION['ip']) && isset($_SESSION['id']) && $login == '')
        {

            if($result = & $this->handle->query("select email, rank from usersV2 where login = '" . $this->handle->escape($_SESSION['login']) . "' and pass = '" . $this->handle->escape($_SESSION['pass']) . "' and id = '" . $this->handle->escape($_SESSION['id']) . "'", __FILE__, __LINE__))
            {
                if($this->handle->numrows($result, __FILE__, __LINE__) == 1 && ($_SESSION['ip'] == $this->getIP() || $this->getIP() == SERVER_IP))
                {
                    $ret = true;
                     
                    $record      = & $this->handle->fetch($result);
                    $this->id    = & $_SESSION['id'];
                    $this->email = & $record[0];
                    $this->rank  = & $record[1];
                    $this->ip    = & $_SESSION['ip'];
                    $this->pass  = & $_SESSION['pass'];
                    $this->login = & $_SESSION['login'];


                }
                else
                {
                    // Données session falsifiées ou adresse ip a changé, on logge l'action !
					ManagerLog($this->handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Erreur de session : piratage des données d\'identification ou adresse IP invalide (Adresse courante : ' . $this->getIP() . ') sur l\'extranet');
					
                    @session_destroy();
                }

            }
        }
        else if($login != '')
        {
            $pass = & md5($pass);

            if($result = & $this->handle->query("select id, email, rank from usersV2 where login = '" . $this->handle->escape($login) . "' and pass = '" . $pass . "'", __FILE__, __LINE__))
            {
                $ip = & $this->getIP();

                if($this->handle->numrows($result, __FILE__, __LINE__) == 1)
                {
                    $ret = true;

                    $record      = & $this->handle->fetch($result);
                    $this->id    = & $record[0];
                    $this->email = & $record[1];
                    $this->rank  = & $record[2];
                    $this->ip    = & $ip;
                    $this->pass  = & $pass;
                    $this->login = & $login;

                    session_regenerate_id();

                    // Données de la session
                    $_SESSION['login'] = & $login;
                    $_SESSION['pass']  = & $pass;
                    $_SESSION['ip']    = & $ip;
                    $_SESSION['id']    = & $record[0];
                        
                    // Login réussi, on logge l'action !
					ManagerLog($this->handle, $record[0], $login, $pass, $ip, 'Identification de l\'utilisateur (' . $pass . ') sur l\'extranet');

                }
                else
                {               
                    // Login échoué, on logge l'action !
					ManagerLog($this->handle, 0, $login, $pass, $ip, 'Erreur lors de l\'identification sur l\'extranet - données soumises incorrectes');
                }

            }

        }
        
        return $ret;
    }

    
    
    /* Obtenir l'adresse IP utilisateur
       o : référence chaîne adresse ip */
    function & getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

}

?>
