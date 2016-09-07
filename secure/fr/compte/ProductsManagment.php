<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . 'CustomerBasket.php');
require(ICLASS . 'CustomerUser.php');

$handle = DBHandle::get_instance();
$basket = & new CustomerBasket($handle);
$user   = & new CustomerUser($handle, $basket);

header("Content-Type: text/html; charset=iso-8859-1");

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

/*
ProductsManagement.php?action=updatecomment&idTC=13513357&comment=Commentaire%20de%20test
ProductsManagement.php?action=spf&productID=13513357&familyID=15&user_email=email@domain.ext&fmail1=email1@domain.ext&fmail2=email2@domain.ext&fmail3=email3@domain.ext...
*/
$es = $os = '';

if (isset($_GET['action']))
{
	switch ($_GET['action'])
	{
		// ProductsManagement.php?action=updatecomment&idTC=13513357&comment=Commentaire%20de%20test
		case "updatecomment" :
			$os .= "updatecomment" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['idTC']))
			{
				settype($_GET['idTC'], "integer");
				if (isset($_GET['comment']))
				{
					if ($basket->UpdateComment($_GET['idTC'], rawurldecodeEuro($_GET['comment'])))
						$os .=  "OK" . __OUTPUT_SEPARATOR__;
					else $es .= COMMON_AJAX_COMMENTS_ERROR_UPDATE . $_GET['id'] . __ERROR_SEPARATOR__;
				}
				else $es .= COMMON_AJAX_COMMENTS_ERROR_EMPTY . __ERROR_SEPARATOR__;
			}
			else $es .= COMMON_AJAX_COMMENTS_ERROR_IDTC . __ERROR_SEPARATOR__;
			break;
		
		// ProductsManagement.php?action=spf&productID=13513357&familyID=15&umail=email1@domain.ext&fmail1=email1@domain.ext&fmail2=email2@domain.ext&fmail3=email3@domain.ext...
		case "spf" :
			$os = "spf" . __OUTPUT_SEPARATOR__;
			$pdtID = isset($_GET['pdtID']) ? (int)$_GET['pdtID'] : 0;
			$famID = isset($_GET['famID']) ? (int)$_GET['famID'] : 0;
			$umail = isset($_GET['umail']) ? $_GET['umail'] : "";
			if (!empty($pdtID) && !empty($famID) && !empty($umail))
			{
				$fel = "";		// Friend Email List
				$fen = 1;		// Friend Email Num
				$fef = 0;		// Friend Email found
				while (isset($_GET['fmail'.$fen]) && $fen <= 5)
				{
					if (!empty($_GET['fmail'.$fen]))
					{
						if(preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $_GET['fmail'.$fen]))
						{
							if ($fef > 0) $fel .= ", ";
							$fel .= rawurldecode($_GET['fmail'.$fen]);
							$fef++;
						}
						else $es .= "- " . COMMON_AJAX_SPTF_ERROR_INVALID_FRIEND_MAIL_1 . " " . $fen . " " . COMMON_AJAX_SPTF_ERROR_INVALID_FRIEND_MAIL_2 . __ERROR_SEPARATOR__;
					}
					$fen++;
				}
				
				// We got no error and found at least 1 valid email
				if ($es == '' && $fef > 0)
				{
					// If we correctly get the flood protection field
					if ($result = & $handle->query("select last_mail from paniers where id = '" . $basket->getID() . "'", __FILE__, __LINE__))
					{
						list($last_mail_time) = $handle->fetch($result);
						$current_mail_time = time();
						$time_left = $last_mail_time + __MAIL_FLOOD_PROTECTION_TIME__ - $current_mail_time;
						// Flood protection is OK
						if ($time_left <= 0)
						{
							// If the flood protection's update is ok
							if ($handle->query("update paniers set last_mail = " . $current_mail_time . " where id = '" . $basket->getID() ."'", __FILE__, __LINE__) && $handle->affected(__FILE__, __LINE__) == 1)
							{
								$query = "select ".
									"p.id, pfr.name, pfr.alias, " .
									"pfr.fastdesc, pfr.descc as `desc`, pfr.descd, " .
									"p.idAdvertiser, p.idTC, p.refSupplier, " .
									"p.price, p.unite, p.idTVA, " .
									"pfr.delai_livraison, p.contrainteProduit, p.tauxRemise, " .
									"p.price2, a.parent as adv_parent, pfr.ref_name " .
								"from " .
									"products_fr pfr, products_families pf, products p, advertisers a " .
								"where " .
									"p.id = " . $pdtID . " and pf.idFamily = " . $famID . " " .
									"and p.id = pf.idProduct and p.id = pfr.id " .
									"and a.id = p.idAdvertiser and a.actif = 1";
								
								// If we correctly get the product's information
								if (($result = & $handle->query($query, __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
								{
									$pdt = & $handle->fetchAssoc($result);
									$pdt_url = URL . "produits/" . $famID . "-" . $pdtID . "-" . $pdt['ref_name'] . ".html";
									$isFromSupplier = $pdt['adv_parent'] == __ID_TECHNI_CONTACT__ ? true : false;
									
									/*
									// Loading the product's references if needed
									if($p['price'] == 'ref')
									{
										$tab_ref_cols = array();
										$tab_ref_lines = array();
										
									    if($result = & $handle->query("select content from references_cols where idProduct = '" . $handle->escape($_GET['priv_idproduct']) . "'", __FILE__, __LINE__, false))
										{
										    $data = & $handle->fetch($result);
											$tab_ref_cols = unserialize($data[0]);
											if ($tab_ref_cols[0] != 'Référence TC')	// pre lot3 advertiser's refs table
											{
												$nbcols = count($tab_ref_cols)+1;
												$tab_ref_cols2 = array();
												$tab_ref_cols2[0] = 'Référence TC';
												$tab_ref_cols2[1] = 'Libellé';
												for ($i = 1; $i < count($tab_ref_cols)-1; $i++)
												{
													$tab_ref_cols2[$i+1] = $tab_ref_cols[$i];
												}
												$tab_ref_cols2[$nbcols - 1] = 'Prix';
												$tab_ref_cols = & $tab_ref_cols2;
												$price2_present = false;
											}
											elseif ($isFromSupplier)
											{
												if ($tab_ref_cols[2] == 'Référence Fournisseur')	// common supplier's ref table
													$price2_present = true;
												else	// post lot3 advertiser's refs table
													$price2_present = false;
											}
										}
										
									    if($result = & $handle->query('select id, label, content, refSupplier, price, idTVA, unite from references_content where idProduct = \'' . $handle->escape($_GET['priv_idproduct']) . '\' order by classement', __FILE__, __LINE__, false))
										{
										    while($data = & $handle->fetchArray($result))
												$tab_ref_lines[] = $data;
										}
									}
									elseif (preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $p['price']))	// prix normal
									{
										if ($p['price2'] != '0' && preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $p['price2']))
											$price2_present = true;
										else	// old supplier annonceur (no price2)
											$price2_present = false;
									}
									*/
									require(ICLASS . '_ClassEmail.php');
									$mail = new Email($handle);
									
									$mail_data = array(
										"User-Mail" => $umail,
										"Product-Name" => $pdt['name'],
										"Product-URL" => $pdt_url
									);
									
									$mail->Build(COMMON_AJAX_SPTF_MAIL_SUBJECT_1." ".$pdt['name']." ".COMMON_AJAX_SPTF_MAIL_SUBJECT_2, "", "send-product-friend", "", $mail_data, true);
									$sent = $mail->Send($fel.",info12@techni-contact.com,f.stumm@techni-contact.com,t.henryg@techni-contact.com");
									$mail->Save();
									
									if ($fef == 1) $os .= COMMON_AJAX_SPTF_1_MAIL_SENT;
									else $os .= $fef . " " . COMMON_AJAX_SPTF_X_MAIL_SENT;
									$os .= " " . COMMON_AJAX_SPTF_X_MAIL_SENT_SUCCESSFULY . __OUTPUT_SEPARATOR__;
								}
							}
							else $es .= "- " . COMMON_AJAX_SPTF_ERROR_LOADING_PRODUCT;
						}
						else $es .= "- " . COMMON_AJAX_SPTF_ERROR_FLOOD_PROTECTION . " (" . ($time_left > 1 ? $time_left ."s " . COMMON_AJAX_SPTF_ERROR_FLOOD_XS_REMAINING : COMMON_AJAX_SPTF_ERROR_FLOOD_1S_REMAINING) . ")";
					}
					else $es .= "- " . COMMON_AJAX_SPTF_ERROR_FLOOD_CHECK;
				}
				else $os .= COMMON_AJAX_SPTF_NO_MAIL_SENT . __OUTPUT_SEPARATOR__;
			}
			else
			{
				if (empty($pdtID)) $es .= "- " . COMMON_AJAX_SPTF_ERROR_INVALID_PRODUCT_ID . __ERROR_SEPARATOR__;
				if (empty($famID)) $es .= "- " . COMMON_AJAX_SPTF_ERROR_INVALID_FAMILY_ID . __ERROR_SEPARATOR__;
				if (empty($umail)) $es .= "- " . COMMON_AJAX_SPTF_ERROR_INVALID_OWN_MAIL . __ERROR_SEPARATOR__;
			}
			break;
			
		default : break;
	}
}

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
