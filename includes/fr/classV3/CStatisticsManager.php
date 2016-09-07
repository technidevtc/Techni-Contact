<?php

class StatisticsManager
{
	
	private $handle = null;
	
	function __construct(& $handle) {
		$this->handle = $handle;
	}
	
	function __autoload($class_name) {
    require_once ICLASS . "C" . $class_name . '.php';
	}
	
	public function AddProductToCart ($idProduct = null, $idTC, $idFamily = null, $idAdvertiser = null, $quantity = 1) {
		if (empty($idProduct) || empty($idAdvertiser) || empty($idFamily)) {
			$res = $this->handle->query("
				select rc.idProduct, rc.id as idTC, p.idAdvertiser, pf.idFamily
				from references_content rc, products p, products_families pf
				where rc.idProduct = p.id and p.id = pf.idProduct and rc.id = " . $idTC . "
				group by rc.id", __FILE__, __LINE__);
			$data = $this->handle->fetchAssoc($res);
			foreach($data as $field => $value)
				$$field = $value;
		}
		
		$this->handle->query("insert into stats_cart (idProduct ,idTC, idAdvertiser, idFamily, quantity, timestamp)" .
		" values (" . $idProduct . "," . $idTC . "," . $idAdvertiser . "," . $idFamily . "," . $quantity . "," . time() . ")", __FILE__, __LINE__);
	}
	
	public function SaveCartAsEstimate (&$cartID) {
		// $cart is not an object, we assume it's a cart ID
		if (is_object($cartID))
			$cart = &$cartID;
		else
			$cart = new Cart($this->handle, $cartID);
		
		foreach ($cart->items as $item) {
			$this->handle->query("insert into stats_esti (idProduct, idTC, idAdvertiser, idFamily, quantity, idEstimate, timestamp)" .
			" values ('" . $item["idProduct"] . "', '" . $item["idTC"] . "', '" . $item["idAdvertiser"] . "', '" . $item["idFamily"] . "', '" . $item["quantity"] . "', '" . $cart->id . "', '" . time() . "')", __FILE__, __LINE__);
		}
	}
}

?>