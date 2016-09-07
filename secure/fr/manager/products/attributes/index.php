<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
  require_once '../../../../config.php';
}else{
  require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$user = new BOUser();
if (!$user->login())
	exit();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Techni-Contact - Product Attributes Module</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="assets/app-product-attributes.css" rel="stylesheet">
  <style>
    body { overflow: hidden }
  </style>
</head>
<body>
  <div class="no-conflict">
    <app-product-attributes>Chargement...</app-product-attributes>
  </div>
  <script>
    var params = {};
    location.search.substring(1).replace(/([^&]+)=([^&]+)/gi,function(s,$1,$2){params[$1]=$2});
    var parent_product_id = params.productId;
    var parent_family_id = params.familyId;
    function onProductAttributeSelected(attribute) {
      top.addAttributeToReferences(attribute.name);
    }
    function onAttributesRefreshed() {
      top.resizeProductAttributesIframe();
    }
    function onFacetsRefreshed(facetNames) {
      top.facetNames = facetNames;
      top.colorReferenceCols();
    }
  </script>
  <script src="assets/angular2.js"></script>
  <script src="assets/app-product-attributes.js"></script>
</body>
</html>
