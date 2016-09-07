<?php

class Email extends BaseObject {
  
  protected $IdMax = 999999999;
  protected $headers, $content;
  protected $built = false;
  protected $attachments = array();
  protected $fromGmail = true;
  protected $gmailLogin = NULL;
  protected $gmailPass = NULL;
  
  public static $_tables = array(
    array(
      "name" => "emails",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "email" => "",
        "subject" => "",
        "headers" => "",
        "template" => "",
        "data" => array(),
        "timestamp" => 0
      )
    )
  );
  
  public static function get() {
    $args = func_get_args();
    return BaseObject::get(self::$_tables, $args);
  }
  
  public static function delete($id) {
    return BaseObject::delete($id, self::$_tables);
  }
  
  public function __construct($args = null) {
    $this->tables = self::$_tables;
    parent::__construct($args);
    if ($this->existsInDB)
      $this->build();
  }

  public function create($data = null) {
    parent::create($data);
    $this->built = false;
  }
  
  public function load() {
    $r = parent::load();
    $this->fields["data"] = mb_unserialize($this->fields["data"]);
    $this->built = false;
    return $r;
  }
  
  public function save() {
    
    $this->fields["data"] = serialize($this->fields["data"]);
    $r = parent::save();
    $this->fields["data"] = mb_unserialize($this->fields["data"]);
    
    return $r;
  }
  
  public function get_headers() {
    return $this->headers;
  }
  
  public function get_content() {
    return $this->content;
  }
  
  public function build() {
    if (empty($this->fields["email"])
     || empty($this->fields["subject"])
     || ($templateContent = file_get_contents(MISC_INC."emails_content/".$this->fields["template"].".dat")) === false) {
      $this->built = false;
    }
    else {
      // temp : converting string in email to UTF-8
      $this->headers = $this->fields["headers"]."MIME-Version: 1.0\nContent-type: text/html; charset=UTF-8\n";
      
      // filenames must always be of the form recipient-origin-object.dat like 'user-fo_account-your-password.dat"
      // trec/tori/tobj = template recipient/origin/object
      list($trec, $tori, $tobj) = explode("-",$this->fields["template"],3);
      switch ($trec) {
        case "user":
        case "user_mob":
        case "partner":
        case "advertiser":
        case "prospect":
        case "customer":
        case "fax" :
        case "tc":
          $this->content = file_get_contents(MISC_INC."emails_content/header_".$trec.".dat");
          if (empty($this->fields["data"]) || !is_array($this->fields["data"]))
            $this->content .= $templateContent;
          else{
            // mail custom according to daytime
            if(date('h',time()) >= 0 && date('h',time()) < 12){
              $this->fields["data"]['HELLO_TERM'] = 'Bonjour';
              $this->fields["data"]['DAYTIME_TERM'] = 'journée';
            }elseif(date('h',time()) >= 12 && date('h',time()) < 18){
              $this->fields["data"]['HELLO_TERM'] = 'Bonjour';
              $this->fields["data"]['DAYTIME_TERM'] = 'après-midi';
            }elseif(date('h',time()) >= 18 && date('h',time()) < 0){
              $this->fields["data"]['HELLO_TERM'] = 'Bonsoir';
              $this->fields["data"]['DAYTIME_TERM'] = 'soirée';
            }

            $this->content .= str_replace(array_keys($this->fields["data"]), array_values($this->fields["data"]), $templateContent);
          }
          $this->content .= file_get_contents(MISC_INC."emails_content/footer_".$trec.".dat");
          break;
        default:
          $this->content = "";
      }
      if (!empty($this->content))
        $this->built = true;
      else
        $this->built = false;
    }
    
    return $this->built;
  }
  
  public function setFromGmail($val = true, $gmailLogin = NULL, $gmailPass = NULL) {
    $this->fromGmail = !!$val;
    $this->gmailLogin = $gmailLogin;
    $this->gmailPass = $gmailPass;
    return $this;
  }
  
  public function addAttachment($file) {
    $this->attachments[] = $file;
  }
  
  public function send($email = "") {
    if (!empty($email))
      $this->fields["email"] = $email;
    
    if (!$this->built)
      $this->build();
    
    if (!DEBUG) {
      /*if (!mail($this->fields["email"], $this->fields['subject'], $this->content, $this->headers))
        return false;*/
      require_once('class.phpmailer.php');
      $mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
      $mail->CharSet = 'UTF-8';
      
      try {
        if ($this->fromGmail) {
          $mail->IsSMTP();
          $mail->SMTPAuth   = true;
          $mail->SMTPSecure = "tls";
          
          $gmailLogin = $this->gmailLogin;
          $gmailPass = $this->gmailPass;
          if (!empty($gmailLogin) && !empty($gmailPass)) {
            $mail->Host       = "smtp.gmail.com";
            $mail->Port       = 587;
            $mail->Username   = $gmailLogin;
            $mail->Password   = $gmailPass;
          } else {
            // change on 11/03/2016 17:01 FR
			
			$mail->Host       = "smtp-relay.sendinblue.com";
            $mail->Port       = 587;
            $mail->Username   = "t.henryg@techni-contact.com";
            $mail->Password   = "MnYUwd05CQZy8aWh";
			
			/*$mail->Host       = "smtp.mandrillapp.com";
            $mail->Port       = 587;
            $mail->Username   = "technicontact@techni-contact.com";
            $mail->Password   = "5tvZpWjDtIBaL8TcWKN9cQ";
			*/
          }
        }
        
        if (!TEST) {
          $emails = explode(',', $this->fields['email']);
          foreach ($emails as $email)
            $mail->AddAddress($email);
        } else {
          $mail->AddAddress("t.henryg@techni-contact.com");
        }
        
        // headers analysis
        $headers = preg_split('/\R/', $this->headers);
        foreach ($headers as $hl) {
          if (!empty($hl)) {
            preg_match('/([^:]+)\s*:\s*([^<]+)(<([^>]+)>)?/', $hl, $matches);
            $cmd = $matches[1];
            $addresses = isset($matches[4]) ? $matches[4] : $matches[2];
            $addresses = explode(',', $addresses);
            $name = isset($matches[4]) ? $matches[2] : "";
            switch (strtolower($cmd)) {
              case 'from':
                $mail->SetFrom($addresses[0], $name);
                break;
              case 'reply-to':
                $mail->AddReplyTo($addresses[0], $name);
                break;
              case 'cc':
                if (!TEST) {
                  foreach ($addresses as $address)
                    $mail->AddCC($address, $name);
                } else {
                  $mail->AddCC("t.henryg@techni-contact.com");
                }
                break;
              case 'bcc':
                if (!TEST) {
                  foreach ($addresses as $address)
                    $mail->AddBCC($address, $name);
                } else {
                  $mail->AddBCC("t.henryg@techni-contact.com");
                }
                break;
            }
          }
        }
        
        $mail->Subject = $this->fields['subject'];
        $mail->MsgHTML($this->content);
        foreach ($this->attachments as $file)
          $mail->AddAttachment($file);
        $mail->Send();
      } catch (phpmailerException $e) {
        flog($e);
        return false;
      } catch (Exception $e) {
        flog($e);
        return false;
      }
    }
    else {
      flog(str_repeat('=',80)."\n".
          ($this->fromGmail ? "FROM GMAIL\n".str_repeat('-',40)."\n" : "").
          $this->headers.
          str_repeat('-',40)."\n".
          $this->fields["email"]."\n".
          str_repeat('-',40)."\n".
          $this->fields['subject']."\n".
          str_repeat('-',40)."\n".
          print_r($this->attachments, true)."\n".
          str_repeat('-',40)."\n".
          $this->content."\n"
      );
      //if (!mail('frederic@hook-network.com', $this->fields["subject"], $this->content, $this->headers))
      //  return false;
    }
    
    $this->save();
    return true;
  }
  
}
