<?php

class Nuukik {
  public static function get($zoneId, $controller, $path, $params = array()) {
    if (!is_array($params))
      $params = array();
    
    $baseURL = 'http://prod01.nuukik.com/reco-api/';
    $tenantId = '12';
    $authToken = 'w4ufasuY';
    
    $requestUrl = $baseURL.'tenants/'.$tenantId.'/zones/'.$zoneId.'/'.$controller.'/'.implode('/', $path);
    //echo "call : ".$requestUrl."\n";
    $timestamp = sprintf('%d',round(microtime(true)*1000));
    //echo "timestamp :".sprintf("%d",$timestamp)."\n";
    $params = array_merge($params, array('username' => $tenantId, 'timestamp' => $timestamp));
    $requestSignature = 'GET '.$requestUrl.$timestamp;
    $salt = 'someSalt';
    $encryptedAuthToken = hash('sha256', hash('sha256', $authToken, true).$salt, true);
    $requestSignatureHash = base64_encode(hash_hmac('sha256', $requestSignature, $encryptedAuthToken, true));
    $protectedRequestSignatureHash = str_replace(array('+', '/', '='), array('-', '_', ), $requestSignatureHash);

    $requestToSend = $requestUrl.'?'.Utils::array_implode('=','&',$params);
    //echo "call : ".$requestToSend."\n";
    $ch = curl_init($requestToSend);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Nuukik '.$protectedRequestSignatureHash));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 400);
    //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $inputs['params']);
    $dataRecoApi = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    //echo "requestToSend :".$requestToSend."\n";
    //echo "Authorization: Nuukik :".$protectedRequestSignatureHash."\n";
    //echo "http status code : ".$httpCode."\n";
    //echo "response         : ".$dataRecoApi."\n";

    $refIdList = json_decode($dataRecoApi);
    //$httpCode = 200;
    //$refIdList = json_decode('[{"productBiz":"11352234"},{"productBiz":"16350442"},{"productBiz":"9359425"},{"productBiz":"5685575"},{"productBiz":"12247322"}]');
    //$refIdList = json_decode('[{"productBiz":"5023413"},{"productBiz":"14172177"},{"productBiz":"7292635"},{"productBiz":"2178628"},{"productBiz":"11446270"}]');
    
    
    if ($httpCode === 200 && is_array($refIdList)) {
      $pdtIdList = $idTCList = array();
      foreach ($refIdList as $refId) {
        if (isset($refId->productBiz))
          $pdtIdList[] = $refId->productBiz;
        elseif (isset($refId->articleBiz))
          $idTCList[] = $refId->articleBiz;
      }
      return array('pdtIdList' => $pdtIdList, 'idTCList' => $idTCList);
    } else {
      return "Fatal error: Nuukik connection failed ".($refIdList !== NULL ? "(code ".($refIdList->code ?: "null").")" : "(http code ".$httpCode.")");
    }
  }
  
  public static function get_mail_html($pdtList, $linkTracking = ""){
    $s = "";
    if (!empty($pdtList)) {
      $s .= "<table style=\"width: 158px; border-collapse: collapse; border: solid 1px #c7c7c7\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">".
              "<tr><td style=\"font: normal 14px georgia,serif; color: #0071bc; text-align: center\">Vous aimerez aussi</td></tr>";
      foreach ($pdtList as $pdt) {
        $s .= "<tr>".
                "<td style=\"font: normal 12px arial,sans-serif; text-align: center; vertical-align: middle\">".
                  "<a href=\"".$pdt['url'].$linkTracking."\" target=\"_blank\" style=\"text-decoration: none\">".
                    "<img src=\"".$pdt['pic']."\" alt=\"\" /><br />".
                    "<span style=\"font: normal 12px arial,sans-serif; color: #000000\">".$pdt['name']."</span><br />".
                    "<span style=\"font: normal 12px georgia,serif; color: #0071bc\">".$pdt['price']."</span>".
                  "</a>".
                "</td>".
              "</tr>";
      }
      $s .= "</table>";
    }
    return $s;
  }
 
}