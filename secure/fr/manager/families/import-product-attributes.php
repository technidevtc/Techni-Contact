<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL ^ E_NOTICE);

$user = new BOUser();
if (!$user->login() || !$user->get_permissions()->has("m-prod--sm-categories","e"))
	exit();

if (!is_uploaded_file($_FILES['import_file']['tmp_name']))
  exit();

require LIB_VENDOR_PATH.'spreadsheet-reader/php-excel-reader/excel_reader2.php';
require LIB_VENDOR_PATH.'spreadsheet-reader/SpreadsheetReader.php';
// require LIB_VENDOR_PATH.'excel-reader/excel_reader2.php';

$spreadsheet = new SpreadsheetReader($_FILES['import_file']['tmp_name'], $_FILES['import_file']['name']);
// if an error occured, SpreadsheetReader should have thrown an error

require CONTROLLER.'manager/AttributeController.php';
require CONTROLLER.'manager/AttributeUnitController.php';
require CONTROLLER.'manager/ProductAttributeController.php';

$attrCtrl = new AttributeController();
$attrUnitCtrl = new AttributeUnitController();
$pdtAttrCtrl = new ProductAttributeController();

//$db = Doctrine_Manager::connection()->getDbh();

$sheets = $spreadsheet->Sheets();

$spreadsheet->ChangeSheet($index);

$rowCount = $spreadsheet->count();

$attrCache = ['id' => [], 'name' => []];
$pdtIdCache = [];
$attrUnitCache = ['id' => [], 'name' => []];

foreach ($spreadsheet as $rowNum => $row) {
	if ($rowNum == 0) {
		foreach ($row as $colIndex => $colValue)
			$colAttr[$colValue] = $colIndex;
		continue;
	}

  $attribute = trim($row[$colAttr['attribut']]);
  $product_id = trim($row[$colAttr['produit']]);
  $value = trim($row[$colAttr['valeur']]);
  $unit = trim($row[$colAttr['unite']]);

  // echo "processing row ".$rowNum." with values : $attribute | $product_id | $value | $unit\n";

  // no value = nothing to do
  if (empty($value))
    continue;

  // attribute column is an id
  if (preg_match('`\d+`', $attribute)) {
    if (isset($attrCache['id'][$attribute]))
      $attr = $attrCache['id'][$attribute];
    else
      $attr = $attrCtrl->get($attribute);

    // attribute not found, we ignore the line after informing the cache
    if (!$attr) {
      $attrCache['id'][$attribute] = false;
      continue;
    }

  // attribute column is a name
  } else {
    if (isset($attrCache['name'][$attribute]))
      $attr = $attrCache['name'][$attribute];
    else
      $attr = $attrCtrl->getByName($attribute);

    // attribute not found, but it's a name, wo we implicitly create it
    if (!$attr)
      $attr = $attrCtrl->create(['name' => $attribute], true);
  }

  // caching the attribute to avoid any unneeded sql query
  $attrCache['id'][$attr['id']] = $attrCache['name'][$attr['name']] = $attr;

  // getting the product from cache or DB
  if (isset($pdtIdCache[$product_id])) {
    $pdt = $pdtIdCache[$product_id];
  } else {
    $pdt = Doctrine_Query::create()
      ->select('p.id')
      ->from('Products p')
      ->where('p.id = ?', $product_id)
      ->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  // product not found, we ignore the line
  if (!$pdt) {
    $pdtIdCache[$pdt['id']] = false;
    continue;
  }

  $attrUnit = false;
  if (!empty($unit)) {
      // unit column is an id
    if (preg_match('`\d+`', $unit)) {
      if (isset($attrUnitCache['id'][$unit]))
        $attrUnit = $attrUnitCache['id'][$unit];
      else
        $attrUnit = $attrUnitCtrl->get($unit);

    // unit column is a name
    } else {
      if (isset($attrUnitCache['name'][$unit]))
        $attrUnit = $attrUnitCache['name'][$unit];
      else
        $attrUnit = $attrUnitCtrl->getBy(['attribute_id' => $attr['id'], 'name' => trim($unit)]);

      // unit not found, but it's a name, so we implicitly create it
      if (!$attrUnit)
        $attrUnit = $attrUnitCtrl->create([
          'attribute_id' => $attr['id'],
          'name' => $unit,
          'name_single' => $unit,
          'abbreviation' => $unit,
        ], true);
    }

    // caching the unit if there is one
    if ($attrUnit)
      $attrUnitCache['id'][$attrUnit['id']] = $attrCache['name'][$attrUnit['name']] = $attrUnit;
  }

  // search if this exact same relation is already present
  $pdtAttr = $pdtAttrCtrl->getBy([
    'product_id' => $pdt['id'],
    'attribute_id' => $attr['id'],
    'value' => $value,
  ]);

  // create it if not
  if (!$pdtAttr) {
    $pdtAttr = $pdtAttrCtrl->create([
      'product_id' => $pdt['id'],
      'attribute_id' => $attr['id'],
      'attribute_unit_id' => $attrUnit ? $attrUnit['id'] : '',
      'value' => $value,
    ], true);
  }

}

echo 'ok';
