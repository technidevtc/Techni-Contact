<?php

class JsonRpcClient {

  private $url;

  public function __construct($url) {
    $this->url = $url;
  }

  private function generateId() {
    $chars = array_merge(range("A","Z"),range("a","z"),range(0,9));
    $id = "";
    for ($c = 0; $c < 16; ++$c)
      $id .= $chars[mt_rand(0,count($chars) - 1)];
    return $id;
  }

  public function __call($method,$params) {
    $id = $this->generateId();
    
    if (is_array($params) && count($params) == 1)
      $params = $params[0];
    
    $options = array(
      "http" => array(
        "method" => "POST",
        "content" => json_encode(array(
            "method" => $method,
            "params" => $params,
            "id" => $id
          )
        )
      )
    );
    $ctx = stream_context_create($options);
    
    $response = json_decode(file_get_contents($this->url,false,$ctx));

    if ($response->id != $id)
      throw new Exception("Incorrect JSON-RPC response ID");

    if (isset($response->error))
      throw new Exception($response->error->message,$response->error->code);
    else if (isset($response->result))
      return $response->result;
    else
      throw new Exception("Error in the JSON-RPC response");
  }

}

?>
