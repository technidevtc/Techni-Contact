<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$json = json_decode($input, true);

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    throw new Exception('Type de requête non supporté', 405);

  $user = new BOUser();

  if (!$user->login())
    throw new Exception('Votre session a expirée', 440);

  //if (!$args)
    //throw new Exception('Données JSON invalides', 422);

  $object = $json['object'];

  switch ($object) {
    case 'attribute':
      require CONTROLLER.'manager/AttributeController.php';
      $controller = new AttributeController();
      break;
    case 'attribute-unit':
      require CONTROLLER.'manager/AttributeUnitController.php';
      $controller = new AttributeUnitController();
      break;
    case 'product-attribute':
      require CONTROLLER.'manager/ProductAttributeController.php';
      $controller = new ProductAttributeController();
      break;
    case 'family':
      require CONTROLLER.'manager/FamilyController.php';
      $controller = new FamilyController();
      break;
    case 'facet':
      require CONTROLLER.'manager/FacetController.php';
      $controller = new FacetController();
      break;
    case 'facet-line':
      require CONTROLLER.'manager/FacetLineController.php';
      $controller = new FacetLineController();
      break;
  }

  $args = $json['args'];

  // flog(str_repeat('-', 80));
  // flog($json);

  $s = microtime(true);
  switch ($json['method']) {
    case 'list':
      checkRights($user, 'm-prod--sm-categories', 'r');
      $response = $controller->getList($args);
      break;
    case 'get':
      checkRights($user, 'm-prod--sm-categories', 'r');
      $response = $controller->get($args['id']);
      break;
    case 'create':
      checkRights($user, 'm-prod--sm-categories', 'e');
      $response = $controller->create($args);
      break;
    case 'update':
      checkRights($user, 'm-prod--sm-categories', 'e');
      $response = $controller->update($args);
      break;
    case 'delete':
      checkRights($user, 'm-prod--sm-categories', 'd');
      $response = $controller->delete($args['id']);
      break;
    case 'custom':
      checkRights($user, 'm-prod--sm-categories', 'e');
      $response = $controller->custom($args);
      break;
    default:
      if (method_exists($controller, $json['method']))
        $response = $controller->{$json['method']}($args);
      else
        throw new Exception('Méthode non supportée', 422);
  }
  $e = microtime(true);

  // flog('time = '.round(($e-$s)*1000,3).'ms');
  echo json_encode($response);

} catch (Exception $e) {

  http_response_code($e->getCode());
  echo $e->getMessage();

}

function checkRights($user, $where, $type, $message = 'Vous n’avez pas les droits adéquats pour réaliser cette opération') {
  if (!$user->get_permissions()->has($where, $type))
    throw new Exception($message, 401);
}
