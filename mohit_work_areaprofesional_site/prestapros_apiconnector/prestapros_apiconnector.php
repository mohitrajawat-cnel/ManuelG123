<?php
if (!defined('_PS_VERSION_')) {

    exit;

}

class prestapros_apiconnector extends Module

{
	

    public function __construct()

    {

        $this->name = 'prestapros_apiconnector';

        $this->tab = 'front_office_features';

        $this->version = '1.0.0';

        $this->author = 'hirewordpressexperts.com';

        $this->need_instance = 0;

        $this->ps_versions_compliancy = [

            'min' => '1.7',

            'max' => _PS_VERSION_

        ];

        $this->bootstrap = true;



        parent::__construct();



        $this->displayName = $this->l('Prestashop API Connector');

        $this->description = $this->l('It will show all details of products in json format.');



        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    }

	

	public function install()

    {

        if (!parent::install() || !$this->registerHook('displayHome') || !$this->registerHook('displayHeader') || !$this->registerHook('actionPaymentConfirmation') || !$this->registerHook('hookActionPaymentConfirmation') || !$this->registerHook('displayOrderConfirmation') || !$this->registerHook('displayCheckoutSummaryTop') || !$this->registerHook('actionObjectProductInCartDeleteAfter') || !$this->registerHook('actionObjectProductInCartDeleteBefore') || !$this->registerHook('cart'))
        {
            return false;
        }

        return true;

    }

	public function hookActionObjectProductInCartDeleteBefore($params) {

			
	}

	public function hookActionObjectProductInCartDeleteAfter($params) {


		
		$db = \Db::getInstance();

		$cart_id = $params['cart']->id;
		$customer_id = $params['cart']->id_customer;

		$get_last_point = "SELECT * FROM voucher_delete_after_cart_remove WHERE cart_id='$cart_id'";
		$result_last_point = $db->executeS($get_last_point);
		if(count($result_last_point) > 0)
		{
			$array_product =array();
			foreach($result_last_point as $result_last_point_hwe)
			{
					$product_id = $result_last_point_hwe['product_id'];

					$row_id = $result_last_point_hwe['id'];

					$cart_amount = $result_last_point_hwe['cart_amount'];
			

					$check_product = "SELECT * FROM "._DB_PREFIX_."cart_product WHERE id_cart='$cart_id' && id_product='$product_id'";
					$check_product_result = $db->executeS($check_product);
					if(count($check_product_result) <= 0)
					{
	
						$cart_rule_id = $result_last_point_hwe["cart_rule_id"];
						$type = $result_last_point_hwe['type'];
	
						$cart_rule_id_delete="DELETE from `"._DB_PREFIX_."cart_cart_rule` WHERE id_cart_rule='$cart_rule_id' && id_cart='$cart_id'";
						$db->execute($cart_rule_id_delete);
		

						$loyality_points=0;
						$get_last_point = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && type='$type' order by id_loyaltyeditpoints desc";
						$result_last_point = $db->executeS($get_last_point);
						if(count($result_last_point) > 0)
						{
							foreach($result_last_point as $result_last_point)
							{
								
								$loyality_points = $result_last_point['points'];
								$id_loyality_points = $result_last_point['id_loyaltyeditpoints'];
								
							}

							$total_points = $cart_amount + $loyality_points;

							$update_points = "UPDATE " . _DB_PREFIX_ . "loyaltyeditpoints SET points='$total_points' WHERE id_customer='$customer_id' && id_loyaltyeditpoints='$id_loyality_points' && type='$type'";
							$db->execute($update_points);
						}

					
						
						$cart_rule_data_delete="DELETE from `"._DB_PREFIX_."cart_rule` WHERE id_cart_rule='$cart_rule_id' && id_customer='$customer_id'";
						$db->execute($cart_rule_data_delete);

						$delete_voucher_record="DELETE from `voucher_delete_after_cart_remove` WHERE cart_rule_id='$cart_rule_id' && product_id='$product_id' && customer_id='$customer_id' && id='$row_id'";
						$db->execute($delete_voucher_record);

						$voucher_order_hwe= "DELETE from `" . _DB_PREFIX_ . "cart_rule_lang` where `id_cart_rule`='".(int) $cart_rule_id."'";
						$db->execute($voucher_order_hwe);

						// $delet_order_cart_rule= "DELETE from `" . _DB_PREFIX_ . "order_cart_rule` where `id_cart_rule`='".(int) $cart_rule_id."'";
						// $db->execute($delet_order_cart_rule);
	
					}
	
				
			}


		}

	
	}

	public function hookCart($params) {

		if($_SERVER['REQUEST_URI'] != "/areaprofesional/pedidos/es/pedido" || $_SERVER['REQUEST_URI'] != "/areaprofesional/pedidos/gb/pedido")
		{

			if(!isset($_REQUEST['delete']))
			{
				
				$db = \Db::getInstance();

				$cart = $params['cart'];
				$cart_id = $cart->id;

				$customer_id=$cart->id_customer;

				$select_custom_cart= "SELECT * from " . _DB_PREFIX_ . "cart where id_cart='$cart_id' && id_customer='$customer_id'";
				$check_cart = $db->executeS($select_custom_cart);
				//print_r($get_products);
				if(count($check_cart) > 0)
				{
						$select_products= "SELECT * from " . _DB_PREFIX_ . "cart_product where id_cart='$cart_id'";
						$get_products = $db->executeS($select_products);
				
						$products_id = array();
						$per = 0;
						
						foreach ( $get_products as $product )
						{

							
								$product_id = $product['id_product'];
								$product_quantity = $product['quantity'];

								$select_products_details= "SELECT * from " . _DB_PREFIX_ . "product where id_product='$product_id'";
								$get_products_details = $db->executeS($select_products_details);

								foreach($get_products_details as $get_products_details_hwe)
								{
									$cat_id = $get_products_details_hwe['id_category_default'];
									$price= $get_products_details_hwe['price'];
								}

								

								
										$hweprestaformacian_15_per = Configuration::get('hweprestaformacian');
										$hweprestaformacian_15_per_hwe = explode(',' , $hweprestaformacian_15_per);
										$date = date("Y-m-d H:i:s");
										$per=0;
										$loyality_points=0;

										if(count($hweprestaformacian_15_per_hwe) > 0)
										{
											if(in_array($cat_id,$hweprestaformacian_15_per_hwe))
											{

												$get_last_point4 = "SELECT * FROM voucher_delete_after_cart_remove WHERE cart_id='$cart_id' && product_id='$product_id' && customer_id='$customer_id' order by id desc limit 1";
												$result_last_point4 = $db->executeS($get_last_point4);
												if(count($result_last_point4) > 0)
												{
													foreach($result_last_point4 as $result_last_point4_hwe3)
													{
														$last_quantity_product = $result_last_point4_hwe3['product_quantity'];
														$cart_rule_id_voucher_hwe3 = $result_last_point4_hwe3['cart_rule_id'];
														$row_id_voucher_hwe3 = $result_last_point4_hwe3['id'];
														$product_id_voucher_hwe3 = $result_last_point4_hwe3['product_id'];
														$current_cart_amount = $result_last_point4_hwe3['cart_amount'];
													}

													if($product_quantity >= $last_quantity_product)
													{
														$type=2;
														$price_product = $price * ($product_quantity - $last_quantity_product);

														if($price_product != 0)
														{
															$get_last_point = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && type='$type'";
															$result_last_points = $db->executeS($get_last_point);
															$loyality_points=0;
															if(count($result_last_points) > 0)
															{
	
																foreach($result_last_points as $result_last_point)
																{
																	$loyality_points = $loyality_points + $result_last_point['points'];
																	$loyality_points_id = $result_last_point['id_loyaltyeditpoints'];
								
																}
																if($loyality_points > 0)
																{
	
																	if($loyality_points >= $price_product)
																	{
																		$remain_points = $loyality_points - $price_product;
	
																		$update_loyality_points41="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																		$db->execute($update_loyality_points41);
	
																		$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='$remain_points' WHERE id_customer='$customer_id' && id_loyaltyeditpoints='$loyality_points_id'";
																		$db->execute($update_loyality_points);
	
																		$loyality_points = $price_product;
																	}
																	else
																	{
																		$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																		$db->execute($update_loyality_points);
																		for($i=1; $i <=($product_quantity - $last_quantity_product); $i++)
																		{
																			$check_quantity1 = (int)(($i * $price) - $loyality_points);

																			if($check_quantity1 == 0)
																			{
																				$product_quantity = $last_quantity_product + $i;
																				break;
																			}
																		}
																	}

																	$vouvher_amount=0;
																	$vouvher_amount = $current_cart_amount + $loyality_points;
	
																	$update_voucher_cart_prodct_id= "UPDATE voucher_delete_after_cart_remove SET product_quantity='$product_quantity',cart_amount='$vouvher_amount',`type`='$type' where product_id='$product_id_voucher_hwe3' && cart_rule_id='$cart_rule_id_voucher_hwe3' && customer_id='$customer_id' && id='$row_id_voucher_hwe3' && cart_id='$cart_id'";
																	$db->execute($update_voucher_cart_prodct_id);
	
																	$cart_rule_id_update7="UPDATE `"._DB_PREFIX_."cart_rule` SET reduction_amount='$vouvher_amount' WHERE id_cart_rule='$cart_rule_id_voucher_hwe3' && id_customer='$customer_id'";
																	$db->execute($cart_rule_id_update7);
																}
	
															}
														}

													}
													else
													{
														$type=2;
														$price_product = $price * ($last_quantity_product - $product_quantity);

														if($price_product != 0)
														{
															$vouvher_amount=0;
															$vouvher_amount = $current_cart_amount - $price_product;

															if($current_cart_amount >= $price_product)
															{
																$get_last_point = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && type='$type'";
																$result_last_points = $db->executeS($get_last_point);
																$loyality_points=0;
																if(count($result_last_points) > 0)
																{
		
																	foreach($result_last_points as $result_last_point)
																	{
																		$loyality_points = $loyality_points + $result_last_point['points'];
																		$loyality_points_id = $result_last_point['id_loyaltyeditpoints'];
									
																	}
		
																	
																	$total_points = $loyality_points + $price_product;
	
																	$update_loyality_points41="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																	$db->execute($update_loyality_points41);
	
																	$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='$total_points' WHERE id_customer='$customer_id' && id_loyaltyeditpoints='$loyality_points_id'";
																	$db->execute($update_loyality_points);
	
																	$update_voucher_cart_prodct_id= "UPDATE voucher_delete_after_cart_remove SET product_quantity='$product_quantity',cart_amount='$vouvher_amount',`type`='$type' where product_id='$product_id_voucher_hwe3' && cart_rule_id='$cart_rule_id_voucher_hwe3' && customer_id='$customer_id' && id='$row_id_voucher_hwe3' && cart_id='$cart_id'";
																	$db->execute($update_voucher_cart_prodct_id);
	
																	$cart_rule_id_update7="UPDATE `"._DB_PREFIX_."cart_rule` SET reduction_amount='$vouvher_amount' WHERE id_cart_rule='$cart_rule_id_voucher_hwe3' && id_customer='$customer_id'";
																	$db->execute($cart_rule_id_update7);
																	
		
																}
															}

															
														}
													}
												}
												else
												{
													$price = $price * $product_quantity;
													$type=2;
													$get_last_point = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && type='$type'";
													$result_last_point = $db->executeS($get_last_point);

						
													$loyality_points=0;
													if(count($result_last_point) > 0)
													{


														foreach($result_last_point as $result_last_point)
														{
															$loyality_points = $loyality_points + $result_last_point['points'];
															$loyality_points_id = $result_last_point['id_loyaltyeditpoints'];
						
														}
														if($loyality_points > 0)
														{
															if($loyality_points > $price)
															{
																$remain_points = $loyality_points - $price;

																$update_loyality_points41="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																$db->execute($update_loyality_points41);

																$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='$remain_points' WHERE id_customer='$customer_id' && id_loyaltyeditpoints='$loyality_points_id'";
																$db->execute($update_loyality_points);

																$loyality_points = $price;
															}
															else
															{
																$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																$db->execute($update_loyality_points);
															}
													
															$customerPoints = $loyality_points;
															// Generate a voucher code
															$voucherCode = null;
															if (!Configuration::get('LEP_AUTO')) {
																do {
																	$voucherCode = Configuration::get('LEP_PREFIX').Tools::strtoupper(Tools::passwdGen(8));
																} while (CartRule::cartRuleExists($voucherCode));
															}

															// Voucher creation and affectation to the customer
															$cartRule = new CartRule();
															$cartRule->code = $voucherCode;
															$cartRule->id_customer = (int) $this->context->customer->id;
															$cartRule->reduction_currency = (int) $this->context->currency->id;
															$cartRule->reduction_amount = $customerPoints;
															$cartRule->quantity = 1;
															$cartRule->highlight = (bool)Configuration::get('LEP_HIGHLIGHT');
															$cartRule->quantity_per_user = 1;
															$cartRule->partial_use = (bool)Configuration::get('LEP_PARTIAL');
															$cartRule->reduction_tax = (bool)Configuration::get('LEP_TAX');

															if (Configuration::get('LEP_COMPATIBILITY')) {
																$cartRule->cart_rule_restriction = 1;
																$_POST['cart_rule_select'] = explode(',', Configuration::get('LEP_VOUCHER_CART_RULES'));
															}

															$cartRule->date_from = date('Y-m-d H:i:s');
															if (! (int) Configuration::get('LEP_VALIDITY_PERIOD')) {
																$cartRule->date_to = date('Y-m-d H:i:s', 2147483647);
															} else {
																$cartRule->date_to = date('Y-m-d H:i:s', strtotime('+'. (int) Configuration::get('LEP_VALIDITY_PERIOD').' days'));
															}

															$cartRule->minimum_amount = (float)Configuration::get('LEP_MINIMAL');
															$cartRule->minimum_amount_currency = (int) Configuration::get('LEP_MINIMAL_CURRENCY');
															$cartRule->minimum_amount_tax = (int) Configuration::get('LEP_MINIMAL_TAX');
															$cartRule->minimum_amount_shipping = (int) Configuration::get('LEP_MINIMAL_SHIPPING');
															$cartRule->active = 1;
															if (Shop::isFeatureActive() && $this->context->shop->id) {
																$cartRule->shop_restriction = 1;
															}

															$all_categories = Category::getSimpleCategories((int) $this->context->cookie->id_lang);
															$categories = Configuration::get('LEP_VOUCHER_CATEGORY');
															if ($categories != '' && $categories != 0) {
																$categories = explode(',', $categories);
															} else {
																$categories = array();
															}

															$languages = Language::getLanguages(true);
															$default_text = Configuration::get('LEP_VOUCHER_DETAILS', (int) Configuration::get('PS_LANG_DEFAULT'));

															foreach ($languages as $language) {
																$text = Configuration::get('LEP_VOUCHER_DETAILS', (int) $language['id_lang']);
																$cartRule->name[ (int) $language['id_lang']] = $text ?: $default_text;
															}

															if (count($categories) && count($categories) != count($all_categories)) {
																$cartRule->product_restriction = 1;
															}

															$result = $cartRule->add();

															// print_r($cartRule);

															// die("ghd");
															if (!$result) {
																$cartRule->delete();
																return false;
															}

															$id_cart_rule = (int) $cartRule->id;


															$cart_amount = $cartRule->reduction_amount;
															$insert_voucher_cart_prodct_id= "INSERT voucher_delete_after_cart_remove SET cart_id='$cart_id',
															product_id='$product_id',cart_rule_id='$id_cart_rule',product_quantity='$product_quantity',customer_id='$customer_id',cart_amount='$cart_amount',`type`='$type'";
															$db->execute($insert_voucher_cart_prodct_id);


															//Creating shop restriction
															if (Shop::isFeatureActive() && $this->context->shop->id) {
																$query = 'INSERT INTO '._DB_PREFIX_.'cart_rule_shop (id_cart_rule, id_shop) VALUES ('.$id_cart_rule.', '.(int)$this->context->shop->id.')';
																$result = Db::getInstance()->execute($query);
																if (!$result) {
																	$cartRule->delete();
																	return false;
																}
															}

															//Restrict cartRule with categories
															if (count($categories) && count($categories) != count($all_categories)) {
																//Creating rule group
																$query = 'INSERT INTO ' ._DB_PREFIX_. 'cart_rule_product_rule_group (id_cart_rule, quantity) VALUES (' .$id_cart_rule. ', 1)';
																$result = Db::getInstance()->execute($query);
																if (!$result) {
																	$cartRule->delete();
																	return false;
																}
																$id_group = (int) Db::getInstance()->Insert_ID();

																//Creating product rule
																$query = 'INSERT INTO ' ._DB_PREFIX_. 'cart_rule_product_rule (id_product_rule_group, type) VALUES (' .$id_group.", 'categories')";
																$result = Db::getInstance()->execute($query);
																if (!$result) {
																	$cartRule->delete();
																	return false;
																}
																$id_product_rule = (int) Db::getInstance()->Insert_ID();

																//Creating restrictions
																$values = array();
																//$values[] = "('$id_product_rule', '1')";
																foreach ($categories as $category) {
																	$category = (int) $category;
																	$values[] = "('$id_product_rule', '$category')";
																}
																$values = implode(',', $values);
																$query = 'INSERT INTO ' ._DB_PREFIX_."cart_rule_product_rule_value (id_product_rule, id_item) VALUES $values";
																$result = Db::getInstance()->execute($query);
																if (!$result) {
																	$cartRule->delete();
																	return false;
																}
															}

															if (Configuration::get('LEP_COMPATIBILITY')) {
																// And if the new cart rule has restrictions, previously unrestricted cart rules may now be restricted (a mug of coffee is strongly advised to understand this sentence)
																$ruleCombinations = Db::getInstance()->executeS('
																	SELECT cr.id_cart_rule
																	FROM ' . _DB_PREFIX_ . 'cart_rule cr
																	WHERE cr.id_cart_rule != ' . (int) $id_cart_rule . '
																	AND cr.cart_rule_restriction = 0
																	AND NOT EXISTS (
																		SELECT 1
																		FROM ' . _DB_PREFIX_ . 'cart_rule_combination
																		WHERE cr.id_cart_rule = ' . _DB_PREFIX_ . 'cart_rule_combination.id_cart_rule_2 AND ' . (int) $id_cart_rule . ' = id_cart_rule_1
																	)
																	AND NOT EXISTS (
																		SELECT 1
																		FROM ' . _DB_PREFIX_ . 'cart_rule_combination
																		WHERE cr.id_cart_rule = ' . _DB_PREFIX_ . 'cart_rule_combination.id_cart_rule_1 AND ' . (int) $id_cart_rule . ' = id_cart_rule_2
																	)
																');

																foreach ($ruleCombinations as $incompatibleRule) {
																	Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET cart_rule_restriction = 1 WHERE id_cart_rule = ' . (int) $incompatibleRule['id_cart_rule'] . ' LIMIT 1');
																	Db::getInstance()->execute('
																		INSERT IGNORE INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) (
																			SELECT id_cart_rule, ' . (int) $incompatibleRule['id_cart_rule'] . ' FROM `' . _DB_PREFIX_ . 'cart_rule`
																			WHERE active = 1
																			AND id_cart_rule != ' . (int) $id_cart_rule . '
																			AND id_cart_rule != ' . (int) $incompatibleRule['id_cart_rule'] . '
																	)');
																}
															}

															$voucher_order_hwe= "UPDATE `" . _DB_PREFIX_ . "cart_rule_lang` SET name='Descuento de Formacion' where
															`id_cart_rule`='".(int) $id_cart_rule."'";
															$db->execute($voucher_order_hwe);

															// Add voucher to cart
															if (Tools::getValue('from') === 'checkout'
																|| Tools::getValue('from') === 'order') {
																$this->context->cart->addCartRule($cartRule->id);
															}
															//echo $cartrule;
															//}

														}
													}
												}
												
											}
										}
										////////end voucher code for formacian category///////////////
										$hweprestamerchand_10_per=Configuration::get('hweprestamerchand');
										$hweprestamerchand_10_per_hwe = explode(',' , $hweprestamerchand_10_per); 
										if(count($hweprestamerchand_10_per_hwe) > 0)
										{
											if(in_array($cat_id,$hweprestamerchand_10_per_hwe))
											{
												$get_last_point4 = "SELECT * FROM voucher_delete_after_cart_remove WHERE cart_id='$cart_id' && product_id='$product_id' && customer_id='$customer_id' order by id desc limit 1";
												$result_last_point4 = $db->executeS($get_last_point4);
												if(count($result_last_point4) > 0)
												{
													foreach($result_last_point4 as $result_last_point4_hwe3)
													{
														$last_quantity_product = $result_last_point4_hwe3['product_quantity'];
														$cart_rule_id_voucher_hwe3 = $result_last_point4_hwe3['cart_rule_id'];
														$row_id_voucher_hwe3 = $result_last_point4_hwe3['id'];
														$product_id_voucher_hwe3 = $result_last_point4_hwe3['product_id'];
														$current_cart_amount = $result_last_point4_hwe3['cart_amount'];
													}

													if($product_quantity >= $last_quantity_product)
													{
														$type=1;
														$price_product = $price * ($product_quantity - $last_quantity_product);

														if($price_product != 0)
														{
															$get_last_point = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && type='$type'";
															$result_last_points = $db->executeS($get_last_point);
															$loyality_points=0;
															if(count($result_last_points) > 0)
															{
	
																foreach($result_last_points as $result_last_point)
																{
																	$loyality_points = $loyality_points + $result_last_point['points'];
																	$loyality_points_id = $result_last_point['id_loyaltyeditpoints'];
								
																}
																if($loyality_points > 0)
																{
	
																	if($loyality_points >= $price_product)
																	{
																		$remain_points = $loyality_points - $price_product;
	
																		$update_loyality_points41="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																		$db->execute($update_loyality_points41);
	
																		$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='$remain_points' WHERE id_customer='$customer_id' && id_loyaltyeditpoints='$loyality_points_id'";
																		$db->execute($update_loyality_points);
	
																		$loyality_points = $price_product;
																	}
																	else
																	{
																		$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																		$db->execute($update_loyality_points);
																		for($i=1; $i <=($product_quantity - $last_quantity_product); $i++)
																		{
																			$check_quantity = (float)(($i * $price) - (float)$loyality_points);

																			if($check_quantity == 0)
																			{
																				$product_quantity = $last_quantity_product + $i;
																				break;
																			}
																		}
																	}

																	$vouvher_amount=0;
																	$vouvher_amount = $current_cart_amount + $loyality_points;
	
																	$update_voucher_cart_prodct_id= "UPDATE voucher_delete_after_cart_remove SET product_quantity='$product_quantity',cart_amount='$vouvher_amount',`type`='$type' where product_id='$product_id_voucher_hwe3' && cart_rule_id='$cart_rule_id_voucher_hwe3' && customer_id='$customer_id' && id='$row_id_voucher_hwe3' && cart_id='$cart_id'";
																	$db->execute($update_voucher_cart_prodct_id);
	
																	$cart_rule_id_update7="UPDATE `"._DB_PREFIX_."cart_rule` SET reduction_amount='$vouvher_amount' WHERE id_cart_rule='$cart_rule_id_voucher_hwe3' && id_customer='$customer_id'";
																	$db->execute($cart_rule_id_update7);
																}
	
															}
														}

													}
													else
													{
														$type=1;
														$price_product = $price * ($last_quantity_product - $product_quantity);

														if($price_product != 0)
														{
															$vouvher_amount=0;
															$vouvher_amount = $current_cart_amount - $price_product;

															if($current_cart_amount >= $price_product)
															{
																$get_last_point = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && type='$type'";
																$result_last_points = $db->executeS($get_last_point);
																$loyality_points=0;
																if(count($result_last_points) > 0)
																{
		
																	foreach($result_last_points as $result_last_point)
																	{
																		$loyality_points = $loyality_points + $result_last_point['points'];
																		$loyality_points_id = $result_last_point['id_loyaltyeditpoints'];
									
																	}
		
																	
																	$total_points = $loyality_points + $price_product;
	
																	$update_loyality_points41="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																	$db->execute($update_loyality_points41);
	
																	$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='$total_points' WHERE id_customer='$customer_id' && id_loyaltyeditpoints='$loyality_points_id'";
																	$db->execute($update_loyality_points);
	
																	$update_voucher_cart_prodct_id= "UPDATE voucher_delete_after_cart_remove SET product_quantity='$product_quantity',cart_amount='$vouvher_amount',`type`='$type' where product_id='$product_id_voucher_hwe3' && cart_rule_id='$cart_rule_id_voucher_hwe3' && customer_id='$customer_id' && id='$row_id_voucher_hwe3' && cart_id='$cart_id'";
																	$db->execute($update_voucher_cart_prodct_id);
	
																	$cart_rule_id_update7="UPDATE `"._DB_PREFIX_."cart_rule` SET reduction_amount='$vouvher_amount' WHERE id_cart_rule='$cart_rule_id_voucher_hwe3' && id_customer='$customer_id'";
																	$db->execute($cart_rule_id_update7);
																	
		
																}
															}

															
														}
													}
												}
												else
												{
													$price = $price * $product_quantity;
													$type=1;
													$get_last_point = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && type='$type'";
													$result_last_point = $db->executeS($get_last_point);

						
													$loyality_points=0;
													if(count($result_last_point) > 0)
													{


														foreach($result_last_point as $result_last_point)
														{
															$loyality_points = $loyality_points + $result_last_point['points'];
															$loyality_points_id = $result_last_point['id_loyaltyeditpoints'];
						
														}
														if($loyality_points > 0)
														{
															if($loyality_points > $price)
															{
																$remain_points = $loyality_points - $price;

																$update_loyality_points41="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																$db->execute($update_loyality_points41);

																$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='$remain_points' WHERE id_customer='$customer_id' && id_loyaltyeditpoints='$loyality_points_id'";
																$db->execute($update_loyality_points);

																$loyality_points = $price;
															}
															else
															{
																$update_loyality_points="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='0' WHERE id_customer='$customer_id' && type='$type'";
																$db->execute($update_loyality_points);
															}
													
															$customerPoints = $loyality_points;
															// Generate a voucher code
															$voucherCode = null;
															if (!Configuration::get('LEP_AUTO')) {
																do {
																	$voucherCode = Configuration::get('LEP_PREFIX').Tools::strtoupper(Tools::passwdGen(8));
																} while (CartRule::cartRuleExists($voucherCode));
															}

															// Voucher creation and affectation to the customer
															$cartRule = new CartRule();
															$cartRule->code = $voucherCode;
															$cartRule->id_customer = (int) $this->context->customer->id;
															$cartRule->reduction_currency = (int) $this->context->currency->id;
															$cartRule->reduction_amount = $customerPoints;
															$cartRule->quantity = 1;
															$cartRule->highlight = (bool)Configuration::get('LEP_HIGHLIGHT');
															$cartRule->quantity_per_user = 1;
															$cartRule->partial_use = (bool)Configuration::get('LEP_PARTIAL');
															$cartRule->reduction_tax = (bool)Configuration::get('LEP_TAX');

															if (Configuration::get('LEP_COMPATIBILITY')) {
																$cartRule->cart_rule_restriction = 1;
																$_POST['cart_rule_select'] = explode(',', Configuration::get('LEP_VOUCHER_CART_RULES'));
															}

															$cartRule->date_from = date('Y-m-d H:i:s');
															if (! (int) Configuration::get('LEP_VALIDITY_PERIOD')) {
																$cartRule->date_to = date('Y-m-d H:i:s', 2147483647);
															} else {
																$cartRule->date_to = date('Y-m-d H:i:s', strtotime('+'. (int) Configuration::get('LEP_VALIDITY_PERIOD').' days'));
															}

															$cartRule->minimum_amount = (float)Configuration::get('LEP_MINIMAL');
															$cartRule->minimum_amount_currency = (int) Configuration::get('LEP_MINIMAL_CURRENCY');
															$cartRule->minimum_amount_tax = (int) Configuration::get('LEP_MINIMAL_TAX');
															$cartRule->minimum_amount_shipping = (int) Configuration::get('LEP_MINIMAL_SHIPPING');
															$cartRule->active = 1;
															if (Shop::isFeatureActive() && $this->context->shop->id) {
																$cartRule->shop_restriction = 1;
															}

															$all_categories = Category::getSimpleCategories((int) $this->context->cookie->id_lang);
															$categories = Configuration::get('LEP_VOUCHER_CATEGORY');
															if ($categories != '' && $categories != 0) {
																$categories = explode(',', $categories);
															} else {
																$categories = array();
															}

															$languages = Language::getLanguages(true);
															$default_text = Configuration::get('LEP_VOUCHER_DETAILS', (int) Configuration::get('PS_LANG_DEFAULT'));

															foreach ($languages as $language) {
																$text = Configuration::get('LEP_VOUCHER_DETAILS', (int) $language['id_lang']);
																$cartRule->name[ (int) $language['id_lang']] = $text ?: $default_text;
															}

															if (count($categories) && count($categories) != count($all_categories)) {
																$cartRule->product_restriction = 1;
															}

															$result = $cartRule->add();
															if (!$result) {
																$cartRule->delete();
																return false;
															}

															$id_cart_rule = (int) $cartRule->id;


															$cart_amount = $cartRule->reduction_amount;
															$insert_voucher_cart_prodct_id= "INSERT voucher_delete_after_cart_remove SET cart_id='$cart_id',
															product_id='$product_id',cart_rule_id='$id_cart_rule',product_quantity='$product_quantity',customer_id='$customer_id',cart_amount='$cart_amount',`type`='$type'";
															$db->execute($insert_voucher_cart_prodct_id);


															//Creating shop restriction
															if (Shop::isFeatureActive() && $this->context->shop->id) {
																$query = 'INSERT INTO '._DB_PREFIX_.'cart_rule_shop (id_cart_rule, id_shop) VALUES ('.$id_cart_rule.', '.(int)$this->context->shop->id.')';
																$result = Db::getInstance()->execute($query);
																if (!$result) {
																	$cartRule->delete();
																	return false;
																}
															}

															//Restrict cartRule with categories
															if (count($categories) && count($categories) != count($all_categories)) {
																//Creating rule group
																$query = 'INSERT INTO ' ._DB_PREFIX_. 'cart_rule_product_rule_group (id_cart_rule, quantity) VALUES (' .$id_cart_rule. ', 1)';
																$result = Db::getInstance()->execute($query);
																if (!$result) {
																	$cartRule->delete();
																	return false;
																}
																$id_group = (int) Db::getInstance()->Insert_ID();

																//Creating product rule
																$query = 'INSERT INTO ' ._DB_PREFIX_. 'cart_rule_product_rule (id_product_rule_group, type) VALUES (' .$id_group.", 'categories')";
																$result = Db::getInstance()->execute($query);
																if (!$result) {
																	$cartRule->delete();
																	return false;
																}
																$id_product_rule = (int) Db::getInstance()->Insert_ID();

																//Creating restrictions
																$values = array();
																//$values[] = "('$id_product_rule', '1')";
																foreach ($categories as $category) {
																	$category = (int) $category;
																	$values[] = "('$id_product_rule', '$category')";
																}
																$values = implode(',', $values);
																$query = 'INSERT INTO ' ._DB_PREFIX_."cart_rule_product_rule_value (id_product_rule, id_item) VALUES $values";
																$result = Db::getInstance()->execute($query);
																if (!$result) {
																	$cartRule->delete();
																	return false;
																}
															}

															if (Configuration::get('LEP_COMPATIBILITY')) {
																// And if the new cart rule has restrictions, previously unrestricted cart rules may now be restricted (a mug of coffee is strongly advised to understand this sentence)
																$ruleCombinations = Db::getInstance()->executeS('
																	SELECT cr.id_cart_rule
																	FROM ' . _DB_PREFIX_ . 'cart_rule cr
																	WHERE cr.id_cart_rule != ' . (int) $id_cart_rule . '
																	AND cr.cart_rule_restriction = 0
																	AND NOT EXISTS (
																		SELECT 1
																		FROM ' . _DB_PREFIX_ . 'cart_rule_combination
																		WHERE cr.id_cart_rule = ' . _DB_PREFIX_ . 'cart_rule_combination.id_cart_rule_2 AND ' . (int) $id_cart_rule . ' = id_cart_rule_1
																	)
																	AND NOT EXISTS (
																		SELECT 1
																		FROM ' . _DB_PREFIX_ . 'cart_rule_combination
																		WHERE cr.id_cart_rule = ' . _DB_PREFIX_ . 'cart_rule_combination.id_cart_rule_1 AND ' . (int) $id_cart_rule . ' = id_cart_rule_2
																	)
																');

																foreach ($ruleCombinations as $incompatibleRule) {
																	Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET cart_rule_restriction = 1 WHERE id_cart_rule = ' . (int) $incompatibleRule['id_cart_rule'] . ' LIMIT 1');
																	Db::getInstance()->execute('
																		INSERT IGNORE INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) (
																			SELECT id_cart_rule, ' . (int) $incompatibleRule['id_cart_rule'] . ' FROM `' . _DB_PREFIX_ . 'cart_rule`
																			WHERE active = 1
																			AND id_cart_rule != ' . (int) $id_cart_rule . '
																			AND id_cart_rule != ' . (int) $incompatibleRule['id_cart_rule'] . '
																	)');
																}
															}

															$voucher_order_hwe= "UPDATE `" . _DB_PREFIX_ . "cart_rule_lang` SET name='Descuento de Merchandising' where
															`id_cart_rule`='".(int) $id_cart_rule."'";
															$db->execute($voucher_order_hwe);

															// Add voucher to cart
															if (Tools::getValue('from') === 'checkout'
																|| Tools::getValue('from') === 'order') {
																$this->context->cart->addCartRule($cartRule->id);
															}
															//echo $cartrule;
															//}

														}
													}
												}
												
											}
										}

							
						}


						// die();
				

				}
							
				
			}
	
			
		}
	
	}


	/* Function to show free shipping alert on checkout page*/

	public function hookDisplayCheckoutSummaryTop()
	{
	  ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
	  <script>
jQuery(document).ready(function(){
	var keys = [];
	var amountarray = [];

	var count = 0;
	jQuery(".promo-name.card-block li .voucher-info span").each(function(index ){

	   var checkfgsd = jQuery(this).attr("class");

	   if(checkfgsd == 'label')
	   {
			var label = jQuery(this).text();
			keys.push(label);
	   }
	   else if(checkfgsd == 'value')
	   {
			var value = jQuery(this).text();
			amountarray.push(value);
	   }

	   count = parseInt(count) + 1;
	
	});
    var amount_value =0;
	var all_amount =0;
	jQuery.each( keys, function (key, valuesdfgsd){
		
		if(valuesdfgsd == 'Descuento de Merchandising')
		{
			amount_value = amountarray[key].match(/\d+/);

            all_amount = parseInt(amount_value) + parseInt(all_amount);
			
		}

	});
	// jQuery(".cart-summary-line").remove(".testhwe");
	// jQuery(".cart-summary-line").append('<div class="voucher-info testhwe"><span class="label">Descuento de Merchandising</span><span class="value">-'+all_amount+',00&nbsp;€</span></div>')

});
		</script>

	  <?php
	}

	public function hookDisplayOrderConfirmation($params)
	{
				$order = $params['order'];
				$id_order = $order->id;
				$id_carrier = $order->id_carrier;
				$date_add = $order->date_add;
				$date_upd = $order->date_upd;

							
				$products = $order->getProducts();
				$product_ids = array();

				$per=0;
				foreach($products as $product)
				{
						$product_ids[] = (int)$product['id_product'];
						$productidhw= $product['id_product'];   
						global $cookie;
						$customer_id=(int)$this->context->customer->id;
						$db = \Db::getInstance();
						$mercy = "SELECT * FROM " . _DB_PREFIX_ . "product where id_product='$productidhw'";
						$result = $db->executeS($mercy);
						$categoryid_hw = $result[0]['id_category_default'];
						// echo $categoryid_hw.',';
						$price= $product['total_price'];
						$trainingpoints = Configuration::get('hweprestakey');
						$trainingarreypoints = explode(',' , $trainingpoints);
						$date = date("Y-m-d H:i:s");
						if(count($trainingarreypoints) > 0)
						{
							if(in_array($categoryid_hw,$trainingarreypoints))
							{
								$per =0;
								$per  = $price*10/100;
								$type = 1;
								if($per > 0)
								{
									$loyaltypoints = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && id_order='$id_order' && type='$type'";
									$result_loyaltypoints = $db->executeS($loyaltypoints);

									if(count($result_loyaltypoints) > 0)
									{
										foreach($result_loyaltypoints as $result_loyaltypoints_hwe)
										{
											$per = (float)$per + (float)$result_loyaltypoints_hwe['points'];
										}
										$updatetrainingpoints="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='$per' WHERE id_customer='$customer_id' && id_order='$id_order' && type='$type'";
										$db->execute($updatetrainingpoints);
									}
									else
									{
										$insert_points_data= "INSERT "._DB_PREFIX_."loyaltyeditpoints SET points='$per',
																												id_shop='1',
																												state='$id_carrier',
																												id_customer='$customer_id',
																												id_order='$id_order',
																												id_cart_rule='0',
																												expiry_days='0',
																												date_validation='$date',
																												date_add='$date_add',
																												date_upd='$date_upd',type='$type'";
										$db->execute($insert_points_data);
									}
								}
							}
						}
						$merchandisingpoints=Configuration::get('hweprestaurl');
						$merchandisingarreypoints = explode(',' , $merchandisingpoints); 
						if(count($merchandisingarreypoints) >0)
						{
							if(in_array($categoryid_hw,$merchandisingarreypoints))
							{
								$per1=0;
								$per1  = $price*15/100;
								$type = 2;
								if($per1 > 0)
								{
									$loyaltypoints1 = "SELECT * FROM " . _DB_PREFIX_ . "loyaltyeditpoints WHERE id_customer='$customer_id' && id_order='$id_order' && type='$type'";
									$result_loyaltypoints1 = $db->executeS($loyaltypoints1);
									if(count($result_loyaltypoints1) > 0)
									{
										foreach($result_loyaltypoints1 as $result_loyaltypoints_hwe1)
										{
											$per1 = (float)$per1 + (float)$result_loyaltypoints_hwe1['points'];
										}
										$updatetrainingpoints1="UPDATE "._DB_PREFIX_."loyaltyeditpoints SET points='$per1' WHERE id_customer='$customer_id' && id_order='$id_order' && type='$type'";
										$db->execute($updatetrainingpoints1);
									}
									else
									{
										$insert_points_data1= "INSERT "._DB_PREFIX_."loyaltyeditpoints SET points='$per1',
																												id_shop='1',
																												state='$id_carrier',
																												id_customer='$customer_id',
																												id_order='$id_order',
																												id_cart_rule='0',
																												expiry_days='0',
																												date_validation='$date',
																												date_add='$date_add',
																												date_upd='$date_upd',type='$type'";
										$db->execute($insert_points_data1);
									}
								}
							}
						}


						
						
				}




				/////////////////enf foreach loop//////////
				
				

				
				
		// die('sasdsasa');
	}



	public function hookActionPaymentConfirmation($params){


	}	
	
    public function actionPaymentConfirmation($params) 
	{

	}


   

    public function uninstall()
    {

        return parent::uninstall();

    }
   

	public function hookDisplayHeader($params)
	{
		
								
	}

        

	public function getContent()

	{

			$output = null;
			

				 if (Tools::isSubmit('submit'.$this->name))

					 {

							$myModuleName = Tools::getValue("hweprestakey");

							$myModuleName_url = Tools::getValue("hweprestaurl");

							$myModuleName_formacian_coupon = Tools::getValue("hweprestaformacian");
							$myModuleName_merchand_coupon = Tools::getValue("hweprestamerchand");



							if (!$myModuleName ||empty($myModuleName) ) 

							{

								$output .= $this->displayError($this->l('Invalid Configuration value'));

							} else {

								Configuration::updateValue('hweprestakey', $myModuleName);

								$output .= $this->displayConfirmation($this->l('Settings updated'));

							}

							
							if (!$myModuleName_url ||empty($myModuleName_url) ) 

							{

								$output .= $this->displayError($this->l('Invalid Configuration value'));

							} else {

								Configuration::updateValue('hweprestaurl', $myModuleName_url);

								$output .= $this->displayConfirmation($this->l('Settings updated'));

							}

							if (!$myModuleName_formacian_coupon ||empty($myModuleName_formacian_coupon) ) 
							{

								$output .= $this->displayError($this->l('Invalid Configuration value'));

							} else {

								Configuration::updateValue('hweprestaformacian', $myModuleName_formacian_coupon);

								$output .= $this->displayConfirmation($this->l('Settings updated'));

							}

							if (!$myModuleName_merchand_coupon ||empty($myModuleName_merchand_coupon) ) 
							{

								$output .= $this->displayError($this->l('Invalid Configuration value'));

							} else {

								Configuration::updateValue('hweprestamerchand', $myModuleName_merchand_coupon);

								$output .= $this->displayConfirmation($this->l('Settings updated'));

							}


					}

					



			return $output.$this->displayForm();

		

	}

		public function displayForm()

			{

				// Get default language

				$defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');



				// Init Fields form array

				$fieldsForm[0]['form'] = [

					'legend' => [

						'title' => $this->l('Settings'),

					],

					'input' => [

						[

							'type' => 'text',

							'label' => $this->l('Merchandising Points'),
							
							'name' => 'hweprestakey',

							'size' => 20,

							'required' => true

						]

					],

					'submit' => [

						'title' => $this->l('Save'),

						'class' => 'btn btn-default pull-right'

					]

				];

				$fieldsForm[1]['form'] = [

					'legend' => [

						'title' => $this->l('Settings'),

					],

					
					'input' => [

						[

							'type' => 'text',

							'label' => $this->l('Formación Points'),

							'name' => 'hweprestaurl',

							'size' => 20,

							'required' => true

						]

					],

					'submit' => [

						'title' => $this->l('Save'),

						'class' => 'btn btn-default pull-right'

					]

				];

				$fieldsForm[2]['form'] = [

					'legend' => [

						'title' => $this->l('Settings'),

					],

					
					'input' => [

						[

							'type' => 'text',

							'label' => $this->l('Formacian Coupon Earned'),

							'name' => 'hweprestaformacian',

							'size' => 20,

							'required' => true

						]

					],

					'submit' => [

						'title' => $this->l('Save'),

						'class' => 'btn btn-default pull-right'

					]

				];

				$fieldsForm[3]['form'] = [

					'legend' => [

						'title' => $this->l('Settings'),

					],

					
					'input' => [

						[

							'type' => 'text',

							'label' => $this->l('Merchandising Coupon Earned'),

							'name' => 'hweprestamerchand',

							'size' => 20,

							'required' => true

						]

					],

					'submit' => [

						'title' => $this->l('Save'),

						'class' => 'btn btn-default pull-right'

					]

				];



				$helper = new HelperForm();



				// Module, token and currentIndex

				$helper->module = $this;

				$helper->name_controller = $this->name;

				$helper->token = Tools::getAdminTokenLite('AdminModules');

				$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;



				// Language

				$helper->default_form_language = $defaultLang;

				$helper->allow_employee_form_lang = $defaultLang;



				// Title and toolbar

				$helper->title = $this->displayName;

				$helper->show_toolbar = true;        // false -> remove toolbar

				$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.

				$helper->submit_action = 'submit'.$this->name;

				$helper->toolbar_btn = [

					'save' => [

						'desc' => $this->l('Save'),

						'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.

						'&token='.Tools::getAdminTokenLite('AdminModules'),

					],

					'back' => [

						'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),

						'desc' => $this->l('Back to list')

					]

				];



				// Load current value

				$helper->fields_value['hweprestakey'] = Tools::getValue('hweprestakey', Configuration::get('hweprestakey'));
				$helper->fields_value['hweprestaurl'] = Tools::getValue('hweprestaurl', Configuration::get('hweprestaurl'));
				$helper->fields_value['hweprestaformacian'] = Tools::getValue('hweprestaformacian', Configuration::get('hweprestaformacian'));
				$helper->fields_value['hweprestamerchand'] = Tools::getValue('hweprestamerchand', Configuration::get('hweprestamerchand'));



				return $helper->generateForm($fieldsForm);

			}

	  

}

