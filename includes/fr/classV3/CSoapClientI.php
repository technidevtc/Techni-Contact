<?php

/**
 * Improved Soap Client
 * - Can handle multipart message (SwA)
 *
 * @author b3ha
 * @version 1.0
 */
class SoapClientI extends SoapClient
{
  const CRLF  = "\r\n";
  // Boundary hyphens
  const BHYP  = "--";

  /**
   * All the parts
   *
   * @var mPart
   */
  private $mParts = array();
  /**
   * Part index of the envelope. mParts[envelopeIndex] is the envelope content
   *
   * @var mixed index is an integer if its a multipart message
   */
  private $envI = NULL;

  /**
   * Construct
   *
   * @param mixed  $wsdl
   * @param array $options
   */
  public function __construct($wsdl, array $options = array()) {
    // We need to set the trace option on, to get the response headers
    $options['trace'] = 1;

    parent::__construct($wsdl, $options);
  }

  /**
   * @return bool
   */
  public function isMultiParted() {
    return isset($this->mParts[0]) ? true : false;
  }

  /**
   * Get all the parts
   *
   * @return array
   */
  public function getParts() {
    return $this->mParts;
  }

  /**
   * Get a part
   *
   * @param int $index
   * @return mPart
   */
  public function getPart($index) {
    if ( ! isset($this->mParts[$index]))
      throw new OutOfBoundsException("Theres no index={$index} part!");
    return $this->mParts[$index];
  }

  /**
   * Part index of the envelope. It returns NULL if its not a multiparted msg
   *
   * @return mixed
   */
  public function getEnvI() {
    return $this->envI;
  }

  /**
   * (PHP 5 <= 5.0.1)
   * Performs a SOAP request
   * @link http://php.net/manual/en/soapclient.dorequest.php
   * @param string $request
   * The XML SOAP request.
   *
   * @param string $location
   * The URL to request.
   *
   * @param string $action
   * The SOAP action.
   *
   * @param int $version
   * The SOAP version.
   *
   * @param int $one_way [optional]
   * If one_way is set to 1, this method returns nothing.
   * Use this where a response is not expected.
   *
   * @return string The XML SOAP response.
   */
  public function __doRequest($request, $location, $action, $version, $one_way = 0) {
    $response = parent::__doRequest($request,$location,$action,$version,$one_way);

    $boundary = $start = array();
    // Is it a multipart response?
    if (preg_match('/Content-Type: Multipart\/Related;.*type="((text\/)|(application\/xop\+))xml";/i',$this->__getLastResponseHeaders()) === 1
      && preg_match('/boundary="(.*)"/Ui',$this->__getLastResponseHeaders(),$boundary) === 1
      && preg_match('/start="(.*)"/Ui',$this->__getLastResponseHeaders(),$start) === 1)
    {
      
      // Parts mining
      $parts = explode(self::CRLF.self::BHYP.$boundary[1], $response);
      if (isset($parts[0]) && empty($parts[0]))
        array_shift($parts);
      
      // Through the parts
      foreach ($parts as $part) {
        // Is it over?
        if (($part[0].$part[1]) === self::BHYP)
          break;

        // New part
        $this->mParts[] = new mPart();
        $n_parts        = count($this->mParts) - 1;

        // Important informations start position
        $startp = $part[0].$part[1] === self::CRLF ? 2 : 0;
        // Headers end position
        $h_endp = strpos($part, self::CRLF.self::CRLF, 0);

        // Actual part's header string line by line
        foreach (explode(self::CRLF,substr($part, $startp, $h_endp - 2)) as $h_line) {
          list($headerName, $headerValue) = explode(': ', $h_line, 2);
          $this->mParts[$n_parts]->header[$headerName] = $headerValue;
        }

        // This is the envelope, so set the response
        if ($this->mParts[$n_parts]->header['Content-ID'] === $start[1]) {
          $this->envI                     = $n_parts;
          $this->mParts[$n_parts]->isEnv  = TRUE;

          $response = $this->mParts[$n_parts]->content = substr($part, $h_endp + 4);
        
        // Its not the soap envelope
        } else {
          // Get actual part's content
          switch ($this->mParts[$n_parts]->header['Content-Transfer-Encoding']) {
            case 'base64':
              $this->mParts[$n_parts]->content = base64_decode(substr($part, $h_endp + 4));
              break;
            case 'binary':
            default:
              $this->mParts[$n_parts]->content = substr($part, $h_endp + 4);
              break;
          }
        }
      }
    }

    return $response;
  }
}

/**
 * A part of a multipart message
 */
class mPart
{
  /**
   * @var array
   */
  public $header      = array();
  /**
   * @var string
   */
  public $content     = '';
  /**
   * Am i the envelope?
   *
   * @var boolean
   */
  public $isEnv       = FALSE;
}

# eof
