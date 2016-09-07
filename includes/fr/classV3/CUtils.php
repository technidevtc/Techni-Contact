<?php

// setLocale(LC_ALL, "fr_FR"); include LC_NUMERIC :/
setLocale(LC_COLLATE, "fr_FR");
setLocale(LC_CTYPE, "fr_FR");
setLocale(LC_MONETARY, "fr_FR");
setLocale(LC_TIME, "fr_FR");
setLocale(LC_MESSAGES, "fr_FR");

class Utils {

  /* DEPRECATED
  protected static $transliteration_list = array(
    'Æ' => 'AE', 'æ' => 'ae',
    'Œ' => 'OE', 'œ' => 'oe',
    'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
    'Þ' => 'B', 'ß' => 'B', 'þ' => 'b',
    '©' => 'C', 'Ç' => 'C', '¢' => 'c', 'ç' => 'c',
    'Ð' => 'D', 'ð' => 'd',
    '€' => 'E', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
    'ƒ' => 'f',
    'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
    '£' => 'L',
    'Ñ' => 'N', 'ñ' => 'n',
    'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
    'Š' => 'S', 'š' => 's', '§' => 'S',
    'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
    '×' => 'x',
    '¥' => 'Y', 'Ý' => 'Y', 'Ÿ' => 'Y', 'ý' => 'y', 'ÿ' => 'y',
    'Ž' => 'Z', 'ž' => 'z',
    '°' => '', 'º' => '',
    '©' => '', '®' => '', '™' => ''
  );

  public static function toASCII($string) { // noAccent
    global $transliteration_list;
    $string = strtr($string, $transliteration_list);
    return $string;
  }
  */

  public static function toASCII($string) { // noAccent & no_entities
    $string = str_replace(array('©','®','™','´'), array('','','','\''), $string); // special cases
    $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string); // use built-in php transliteration functionality
    $string = strtolower(str_replace('?', '', $string)); // remove ?'s
    return $string;
  }

  public static function toDashAz09($string) { // Google & no_space_entities
    $string = self::toASCII($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string); // remove any char that is not a-z 0-9 or -
    $string = preg_replace(array('/^-{1,}/', '/-{1,}$/', '/-{2,}/'), array('', '', '-'), $string); // get rid of -'s at the beginning or the end of the string + multiple adjacent -'s
    return $string;
  }

  public static function noDiphthong($string) {
    return str_replace(array("Æ", "æ", "Œ", "œ"), array("AE", "ae", "OE", "oe"), $string);
  }

  public static function get_singular($word) {
    return self::is_plural($word) ? substr($word, 0, strlen($word)-1) : $word;
  }

  public static function is_plural($word) {
    return (($c = substr($word, -1)) == "s" || $c == "x") ? true : false;
  }

  public static function word_results($nb_results) {
    return $nb_results." résultat".($nb_results > 1 ? "s" : "");
  }

  public static function sanitize_tel($string) {
    return preg_replace("/[^0-9-]/", "", trim($string));
  }

  public static function get_multiword_search_sql_pattern($q) {
    $terms = preg_split("`\s|-`", self::noDiphthong($q));

    for ($i=0, $l=count($terms); $i < $l; $i++) {
      if (Utils::is_plural($terms[$i]))
        $terms[$i] = (strlen($terms[$i]) > 2 ? "+" : "").Utils::get_singular($terms[$i])."* <<".$terms[$i]."*";
      else
        $terms[$i] = (strlen($terms[$i]) > 2 ? "+" : "").$terms[$i]."*";
    }

    return implode(" ", $terms);
  }


  const SALT = "E~1B[d++.8Dg9}lMuw.0T~,YA|;g;TbpK9&ywa,-VHbo$]ke9.L@^:(}A@88ung";

  public static function encrypt($plaintext, $key) {
    $key = hash('sha256', $key.self::SALT, true);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
    return base64_encode($iv.$ciphertext);
  }

  public static function decrypt($ciphertext_base64, $key) {
    $key = hash('sha256', $key.self::SALT, true);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

    $ciphertext = base64_decode($ciphertext_base64);
    $iv = substr($ciphertext, 0, $iv_size);
    $ciphertext = substr($ciphertext, $iv_size);
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $ciphertext, MCRYPT_MODE_CBC, $iv), "\0");
  }

  public static function get_pdt_pic_path($pdt_id, $format = 'thumb_small', $num = 1) {
    return is_file(PRODUCTS_IMAGE_INC.$format.'/'.$pdt_id.'-'.$num.'.jpg') ?
            PRODUCTS_IMAGE_INC.$format.'/'.$pdt_id.'-'.$num.'.jpg' :
            PRODUCTS_IMAGE_INC.'no-pic-'.$format.'.gif';
  }

  public static function get_pdt_pic_url_list($pdt_id, $ref_name = null, $https = false) {
    $i = 1;
    $pics = array();
    do {
      $pics[$i-1]['card'] =        self::get_pdt_pic_url($pdt_id, 'card', $i, $ref_name, $https);
      $pics[$i-1]['thumb_big'] =   self::get_pdt_pic_url($pdt_id, 'thumb_big', $i, $ref_name, $https);
      $pics[$i-1]['thumb_small'] = self::get_pdt_pic_url($pdt_id, 'thumb_small', $i, $ref_name, $https);
      $pics[$i-1]['zoom'] =        self::get_pdt_pic_url($pdt_id, 'zoom', $i, $ref_name, $https);
      $i++;
    } while (is_file(PRODUCTS_IMAGE_INC."zoom/".$pdt_id."-".$i.".jpg"));
    return $pics;
  }

  public static function get_pdt_pic_url($pdt_id, $format = 'thumb_small', $num = 1, $ref_name = null, $https = false) {
    return ($https ? PRODUCTS_IMAGE_SECURE_URL : PRODUCTS_IMAGE_URL).$format.'/'.($ref_name?$ref_name.'-':'').$pdt_id.'-'.$num.'.jpg';
  }

  public static function get_secure_pdt_pic_url($pdt_id, $format = 'thumb_small', $num = 1, $ref_name = null) {
    return self::get_pdt_pic_url($pdt_id, $format, $num, $ref_name, true);
  }

  public static function get_secure_dft_pdt_pic_url($format = 'thumb_small') {
    return PRODUCTS_IMAGE_SECURE_URL.'no-pic-'.$format.'.gif';
  }

  public static function get_pdt_fo_url($pdt_id, $pdt_rn, $cat_id) {
    return URL.'produits/'.$cat_id.'-'.$pdt_id.'-'.$pdt_rn.'.html';
  }

  public static function get_family_fo_url($fam_rn, $facet_rn = '', $facet_line_rn = '') {
    return URL.
      'familles'.(!empty($facet_rn)?'-f':'').'/'.$fam_rn.
      (!empty($facet_rn) ? '/'.$facet_rn : '').
      (!empty($facet_line_rn) ? '/'.$facet_line_rn : '').
      '.html';
  }

  public static function get_dial_html($tel) {
    return empty($tel) ? "" : "<a href=\"tel:".to_entities($tel)."\">".to_entities($tel)." <span class=\"icon telephone\"></span></a>";
  }

  private static $stopwords = array(
    'a', 'à', 'á',
    'en',
    'de',
    'pour'
  );
  public static function filter_stopwords($string) {
    return preg_replace('/\s+('.implode('|',self::$stopwords).')\s+/'," ", $string);
  }

  public static function filter_limit_word_count($string, $count, $ignoreStopwords = false) {
    $words = preg_split('/\s+/', $string);
    if (count($words) > $count) {
      if ($ignoreStopwords) {
        for ($k=0; $k<$count; $k++) {
          if (in_array($words[$k], self::$stopwords))
            $count++;
        }
      }
      $words = array_slice($words, 0, $count);
    }
    return implode(' ', $words);
  }

  public static function array_implode($pairGlue, $glue, $array) {
    if (!is_array($array))
      return $array;

    $string = array();

    foreach ($array as $key => $val) {
      if (is_array($val))
        $val = implode(',', $val);
      $string[] = $key.$pairGlue.$val;
    }
    return implode($glue, $string);
  }

  public static function array_find($predicate, $array) {
    for ($i=0, $l=count($array); $i<$l; $i++) {
      $value = $array[$i];
      if ($predicate($value, $i, $array))
        return $value;
    }
    return false;
  }

  public static function array_find_index($predicate, $array) {
    for ($i=0, $l=count($array); $i<$l; $i++) {
      $value = $array[$i];
      if ($predicate($value, $i, $array))
        return $i;
    }
    return false;
  }

  public static function is_ajax_requested(){
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
      return true;
    else
      return false;
  }

  public static function sortDbInPlace(&$_data /*$name, $order, $mode*/) {
    $_argList = func_get_args();
    array_shift($_argList);
    if (empty($_data))
      return $_data;
    $_max = count($_argList);
    $_params = array();
    $_cols = array();
    $_rules = array();
    for ($_i = 0; $_i < $_max; $_i += 3) {
      $_name = (string) $_argList[$_i];
      if (!in_array($_name, array_keys(reset($_data)))) {
        continue;
      }
      if (!isset($_argList[($_i + 1)]) || is_string($_argList[($_i + 1)])) {
        $_order = SORT_ASC;
        $_mode = SORT_REGULAR;
        $_i -= 2;
      } else if (3 > $_argList[($_i + 1)]) {
        $_order = SORT_ASC;
        $_mode = $_argList[($_i + 1)];
        $_i--;
      } else {
        $_order = $_argList[($_i + 1)] == SORT_ASC ? SORT_ASC : SORT_DESC;
        if (!isset($_argList[($_i + 2)]) || is_string($_argList[($_i + 2)])) {
          $_mode = SORT_REGULAR;
          $_i--;
        } else {
          $_mode = $_argList[($_i + 2)];
        }
      }
      $_mode = $_mode != SORT_NUMERIC ? ($_argList[($_i + 2)] != SORT_STRING ? SORT_REGULAR : SORT_STRING) : SORT_NUMERIC;
      $_rules[] = array('name' => $_name, 'order' => $_order, 'mode' => $_mode);
    }

    foreach ($_data as $_k => $_row) {
      foreach ($_rules as $_rule) {
        if (!isset($_cols[$_rule['name']])) {
          $_cols[$_rule['name']] = array();
          $_params[] = &$_cols[$_rule['name']];
          $_params[] = $_rule['order'];
          $_params[] = $_rule['mode'];
        }
        $_cols[$_rule['name']][$_k] = $_row[$_rule['name']];
      }
    }

    $_params[] = &$_data; // finally append the data

    // as of php 5.3, every arguments must be a reference, so we create a second array wich only contains reference to the first one
    $_params2 = array();
    foreach ($_params as &$param)
      $_params2[] = &$param;

    call_user_func_array('array_multisort', $_params2);
  }

  public static function fetchURL($url) {
    $url_parsed = parse_url($url);
    $host = $url_parsed["host"];
    $port = $url_parsed["port"];
    if ($port == 0)
      $port = 80;

    $path = explode("/", $url_parsed["path"]);
    foreach ($path as $k => $v)
      $path[$k] = rawurlencode($v);
    $path = implode("/", $path);

    if ($url_parsed["query"] != "")
      $path .= "?".$url_parsed["query"];

    $out = "GET $path HTTP/1.0\r\n".
           "Host: $host\r\n".
           "\r\n";

    if ($fp = fsockopen($host, $port, $errno, $errstr, 30)) {
      fwrite($fp, $out);
      $body = false;
      $s = fgets($fp, 1024);
      $headers = explode(" ", $s);
      if ($headers[1] == "404")
        return false;

      while (!feof($fp)) {
        $s = fgets($fp, 1024);
        if ($body)
          $in .= $s;
        if ($s == "\r\n")
          $body = true;
      }

      fclose($fp);

      return $in;

    } else {
      return false;
    }
  }

  /*
   * Create the HASH from BE2BILL parameters
   *
   * @param $password The account password
   * @param $parameters A BE2BILL (and only BE2BILL) parameters array
   *
   * @return The HASH signature
   */
  public static function be2bill_signature($password, array $params) {
    ksort($params);
    $clear_string = $password;
    foreach ($params as $key => $value) {
      if (is_array($value)) {
        ksort($value);
        foreach ($value as $index => $val) {
          $clear_string .= $key . '[' . $index . ']=' . $val . $password;
        }
      } else {
        if ($key == 'HASH') {
          // Skip HASH parameter if supplied
          continue;
        } else {
          $clear_string .= $key . '=' . $value . $password;
        }
      }
    }

    return hash('sha256', $clear_string);
  }

  public static function get_price_string($pdt_price, $ref_price, $as_estimate, $prefix = '', $suffix = '', $estiStr = 'sur devis') {
    if ($as_estimate != 0) {
      $price = $estiStr;
    } elseif ($ref_price !== null && $ref_price < __THRESHOLD_PRICE_FOR_ESTIMATE__) {
      $price = $prefix.$ref_price.$suffix;
    } elseif (preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/',$pdt_price) && $pdt_price < __THRESHOLD_PRICE_FOR_ESTIMATE__) {
      $price = $prefix.$pdt_price.$suffix;
    } else {
      $price = $estiStr;
    }
    return $price;
  }

  public static function get_pdts_infos($pdtIds, $idTCs, $template) {
    $conn = Doctrine_Manager::connection();
    $db = $conn->getDbh();

    if (empty($pdtIds) && empty($idTCs)) {
      return array();
    }

    $querySelectionCommon = "
      p.id,
      p.price AS pdt_price,
      pfr.name,
      pfr.ref_name,
      pfr.descc,
      ffr.id AS cat_id,
      rc.price AS ref_price,
      (p.as_estimate + a.as_estimate) as pdt_as_estimate";

    $queryJoinCommon = "
      INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1
      INNER JOIN products_families pf ON p.id = pf.idProduct
      INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
      INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1";

    $queries = array();
    if (!empty($pdtIds)) {
      $queries[] = "
        SELECT
          IFNULL(rc.id, p.idTC) AS idTC,
          ".$querySelectionCommon."
        FROM products p".
        $queryJoinCommon."
        LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
        WHERE pfr.id IN (".implode(",",$pdtIds).")
        GROUP BY p.id";
    }
    if (!empty($idTCs)) {
      $queries[] = "
        SELECT
          rc.id AS idTC,
          ".$querySelectionCommon."
        FROM products p".
        $queryJoinCommon."
        INNER JOIN references_content rc ON p.id = rc.idProduct AND rc.vpc = 1 AND rc.deleted = 0
        WHERE rc.id IN (".implode(",",$idTCs).")
        GROUP BY p.id";
      $queries[] = "
        SELECT
          p.idTC,
          ".$querySelectionCommon."
        FROM products p".
        $queryJoinCommon."
        LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.vpc = 1 AND rc.deleted = 0
        WHERE rc.id IS NULL AND p.idTC IN (".implode(",",$idTCs).")
        GROUP BY p.id";
    }
    try {
      $sth = $db->query(implode(" UNION ", $queries));
    } catch (Exception $e) {
      //pp($e);
    }

    $pdtInfosArray = $sth->fetchAll(PDO::FETCH_ASSOC);

    if (count($pdtInfosArray) < 1) // bad result count
      return array();

    $pdtList = array();
    $pdtI = 0;
    foreach ($pdtInfosArray as $pdtInfos) {
      $pdt = array();

      $pdt['id'] = $pdtInfos['id'];
      $pdt['name'] = $pdtInfos['name'];
      $pdt['saleable'] = $pdtInfos['ref_price'] !== null && $pdtInfos['ref_price'] < __THRESHOLD_PRICE_FOR_ESTIMATE__;

      switch ($template) {
        case 'pdf-block':
          $pdt['desc'] = substr(trim(preg_replace(array('/(\r\n)+/', '/(\r)+/', '/(\n)+/'), ' ', preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($pdtInfos['descc'], FILTER_SANITIZE_STRING), ENT_QUOTES)))),0,80)."...";
          $pdt['pic'] = self::get_pdt_pic_path($pdtInfos['id'], 'card', 1);
          $pdt['price'] = self::get_price_string($pdtInfos['pdt_price'], $pdtInfos['ref_price'], $pdtInfos['pdt_as_estimate'], 'à partir de ', ' &euro; HT', 'sur devis');
          break;
        case 'simple-block':
        default:
          if ($_SERVER['HTTPS'] == 'on')
            $pdt['pic'] = self::get_secure_pdt_pic_url($pdtInfos['id'], 'thumb_small', 1, $pdtInfos['ref_name']);
          else
            $pdt['pic'] = self::get_pdt_pic_url($pdtInfos['id'], 'thumb_small', 1, $pdtInfos['ref_name']);
          $pdt['price'] = self::get_price_string($pdtInfos['pdt_price'], $pdtInfos['ref_price'], $pdtInfos['pdt_as_estimate']);
          $pdt['url'] = self::get_pdt_fo_url($pdtInfos['id'], $pdtInfos['ref_name'], $pdtInfos['cat_id']);
          break;
      }

      $pdtList[$pdtI] = $pdt;
      $pdtListIds[$pdtInfos['id']] = $pdtListIdTCs[$pdtInfos['idTC']] = $pdtI++;
    }

    // respect original order
    $pdtListOrdered = array();
    foreach ($pdtIds as $pdtId) {
      if (isset($pdtListIds[$pdtId]))
        $pdtListOrdered[] = $pdtList[$pdtListIds[$pdtId]];
    }
    foreach ($idTCs as $idTC) {
      if (isset($pdtListIdTCs[$idTC]))
        $pdtListOrdered[] = $pdtList[$pdtListIdTCs[$idTC]];
    }

    return $pdtListOrdered;

  }

}
