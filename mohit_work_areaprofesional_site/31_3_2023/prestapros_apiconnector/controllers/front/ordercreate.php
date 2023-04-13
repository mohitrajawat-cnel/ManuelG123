<?php
/**
 * <ModuleClassName> => Cheque
 * <FileName> => validation.php
 * Format expected: <ModuleClassName><FileName>ModuleFrontController
 */
class prestapros_apiconnectorOrdercreateModuleFrontController extends ModuleFrontController
{
	
	public function initContent()
	{
		
		/*	ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);	*/
			
			
		$db = \Db::getInstance();
		if(!$_POST['data'] && !$_POST['getpricevalue'])
		{
			
			die("fake");
		}
		
		if(isset($_POST['getpricevalue']))
		{
			$data=json_decode($_POST['getpricevalue'],true);
				$productlist=$data['productlist'];
				$totalpriceset=0;
				foreach($productlist as $listvalue)
				{
					unset($where);
					$sku=$listvalue['sku'];
					$quantity=$listvalue['quantity'];
					$barcode=$listvalue['barcode'];
					$attributeid="";
					if(!empty($sku))
					{
						$where[]=" reference='$sku'";
						
					}
					if(!empty($barcode))
					{
						$where[]=" ean13='$barcode'";
						
					}
					$implodewhere=implode("or",$where);
					
					
						  $request = "SELECT * FROM " . _DB_PREFIX_ . "product where  ($implodewhere) ";	
						  
						  	
						$result = $db->executeS($request);	
						if(count($result)>0)
						{					
						
								$id_product=$result[0]['id_product'];
								
								
								 $price=$result[0]['price'];
								 $totalpriceset=$totalpriceset+($price*$quantity);
								 $getprductlist[$sku]=$price;
								
								 
						}
						else					
							{
									 $requestatt= "SELECT * FROM " . _DB_PREFIX_ . "product_attribute where ($implodewhere)";		
									$resultatt = $db->executeS($requestatt);
									
									$id_product=$resultatt[0]['id_product'];
										
										 $requestpr = "SELECT * FROM " . _DB_PREFIX_ . "product where id_product='$id_product'";		
											$resultpr = $db->executeS($requestpr);	
									
									 $price=$resultatt[0]['price']+$resultpr[0]['price'];
									 $totalpriceset=$totalpriceset+($price*$quantity);	
									$getprductlist[$sku]=$price;									 
								
							}
						
						
							
							
							
				}
				echo json_encode(array("total"=>$totalpriceset,"pricelist"=>$getprductlist));
				//echo $totalpriceset;
			exit;
		}
		$data=json_decode($_POST['data'],true);
		$productlist=$data['productlist'];
		
		$billing_addressget=$data['billing_address'];
		
		$shipping_addressget=$data['shipping_address'];
		$carrieridget=$data['carrierid'];
		$shipping_cost=$data['shipping_cost'];
		$paymentmethod=$data['paymentmethod'];
		
		
		$customerget=$data['customer'];
		$keyget=Configuration::get('hweprestakey');
		$discountget=$data['discount'];
		$languageid=$data['languageid'];
		if(!$languageid)
		{
			$languageid=intval(Configuration::get('PS_LANG_DEFAULT')); 
			
		}
		
			
					define('DEBUG', true);

				define('_PS_DEBUG_SQL_', true);

				define('PS_SHOP_PATH', Context::getContext()->shop->getBaseURL(true));

				define('PS_WS_AUTH_KEY', $keyget);
				require_once ('PSWebServiceLibrary.php');
		
					
			$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
			
			$getgroup=$this->getgroups();
			
			
			
			$xml = $webService->get(array('url' => PS_SHOP_PATH.'/api/customers/?schema=synopsis&ws_key='.$keyget));
			 $customer = array();
			   $product = array();
			    $customer['email'] = $customerget['email'];
				$customer['firstname'] =  $customerget['first_name'];
				
            $customer['lastname'] = $customerget['last_name'];
			if(!$customer['lastname'])
			{
					$customer['lastname']="Reseller";
				
			}
            $customer['address1'] = $shipping_addressget['address1'];
            $customer['city'] = $shipping_addressget['city'];
            $customer['phone'] =  $customerget['phone'];
			$customer['postcode']=$shipping_addressget['zip'];
			$customer['password']=$customerget['password'];;
			$countryisocode=$shipping_addressget['country_code'];
			
			
			/* $requestcarrier = "SELECT * FROM " . _DB_PREFIX_ . "carrier where active=1 and deleted=0 order by id_carrier ASC";		
			$resultcarrier = $db->executeS($requestcarrier);
			$carrieridhwe=$resultcarrier[0][id_carrier];*/
			$carrieridhwe=$carrieridget;
			
			
								
		
		
		$request = "SELECT * FROM " . _DB_PREFIX_ . "country where iso_code='$countryisocode'";		
		$result = $db->executeS($request);		
	
		$countryid=$result[0]['id_country'];
		//$countryid=8;
		//$customer['postcode']=93210;
		
			
			$id['country'] =$countryid;
            $id['lang'] = $languageid ;
			
		
            $id['currency'] =  Configuration::get('PS_CURRENCY_DEFAULT');

        // print_r($productlist);
		
		$fullproductdetails=array();
		foreach($productlist as $listvalue)
		{
			unset($where);
			$sku=$listvalue['sku'];
			$quantity=$listvalue['quantity'];
			$barcode=$listvalue['barcode'];
			$attributeid="";
			if(!empty($sku))
					{
						$where[]=" reference='$sku'";
						
					}
					if(!empty($barcode))
					{
						$where[]=" ean13='$barcode'";
						
					}
					  $implodewhere=implode("or",$where);
			
				 echo $request = "SELECT * FROM " . _DB_PREFIX_ . "product where ($implodewhere)";		
				$result = $db->executeS($request);	
				if(count($result)>0)
				{					
				
						$id_product=$result[0]['id_product'];
						
						
						 $price=$result[0]['price'];
						 $ean13=$result[0]['ean13'];
						 
				}
				else					
					{
							echo $requestatt= "SELECT * FROM " . _DB_PREFIX_ . "product_attribute where ($implodewhere)";		
							$resultatt = $db->executeS($requestatt);
							$id_product=$resultatt[0]['id_product'];
							
							 echo  $requestpr = "SELECT * FROM " . _DB_PREFIX_ . "product where id_product='$id_product'";		
											$resultpr = $db->executeS($requestpr);	
							
							 $price=$resultatt[0]['price']+$resultpr[0]['price'];
							 $ean13=$resultatt[0]['ean13'];	
							$attributeid=$resultatt[0]['id_product_attribute'];								 
						
					}
				//echo $id_product;
				//die();
				$productget = new Product($id_product);
				
			
					$product['quantity'] = $quantity;

					$product['id'] = $id_product;

					$product['price'] =$price;

				 $product['name'] =$productget->name[1];
				
					$product['total'] = $product['price'] * $product['quantity'];
					 $product['attributeid']=$attributeid;
					
					array_push($fullproductdetails,array("ean13"=>$ean13,"sku"=>$sku,"quant"=>$quantity,"id"=>$id_product,"attributeid"=>$attributeid,"price"=>$price,"name"=>$productget->name[1]));
					
					
					
		}
	
		
		 $requestemail = "SELECT * FROM " . _DB_PREFIX_ . "customer where email='$customer[email]'";		
				$resultemail = $db->executeS($requestemail);	
		
			
		if(count($resultemail)==0)
		{
				
            $xml->customer->firstname = $customer['firstname'];

            $xml->customer->lastname = $customer['lastname'];

            $xml->customer->email = $customer['email'];

            $xml->customer->newsletter = '1';

            $xml->customer->optin = '1';

            $xml->customer->active = '1';
			$xml->customer->passwd  = $customer['password'];
			
			
			
			$ihwe=0;
		foreach($getgroup[groups] as $valuegro)
		{
			 $groupidhwe=$valuegro[id];
			$xml->customer->associations->groups->group[$ihwe]->id = $groupidhwe;
			
			$ihwe++;
			
			
			
		}
 

 

            $opt = array('resource' => 'customers');

            $opt['postXml'] = $xml->asXML();

            $xml = $webService->add($opt);

 

            // ID of created customer

 

            $id['customer'] = $xml->customer->id;

		}
		else
		{
				 $id['customer'] = $resultemail[0][id_customer];
			
		}
		
            // CREATE Address

 

            $xml = $webService->get(array('url' => PS_SHOP_PATH.'/api/addresses?schema=synopsis&ws_key='.$keyget));



            $xml->address->id_customer = $id['customer'];

            $xml->address->firstname = $customer['firstname'];

            $xml->address->lastname = $customer['lastname'];

            $xml->address->address1 = $customer['address1'];

            $xml->address->city = $customer['city'];

            $xml->address->phone_mobile = $customer['phone'];

            $xml->address->id_country = $id['country'];
			 $xml->address->postcode = $customer['postcode'];

            $xml->address->alias = '-';

 

            $opt = array('resource' => 'addresses');

            $opt['postXml'] = $xml->asXML();

            $xml = $webService->add($opt);

 

            // ID of created address

 

            $id['address'] = $xml->address->id;

 

            // CREATE Cart

 

            $xml = $webService->get(array('url' => PS_SHOP_PATH.'/api/carts?schema=blank&ws_key='.$keyget));

 

            $xml->cart->id_customer = $id['customer'];

            $xml->cart->id_address_delivery = $id['address'];

            $xml->cart->id_address_invoice = $id['address'];

            $xml->cart->id_currency = $id['currency'];

            $xml->cart->id_lang = $id['lang'];

            $xml->cart->id_carrier =$carrieridhwe;

	$i=0;
		foreach($fullproductdetails as $prlistvalueget)
			{ 
				$xml->cart->associations->cart_rows->cart_row[$i]->id_product = $prlistvalueget['id'];

				$xml->cart->associations->cart_rows->cart_row[$i]->quantity = $prlistvalueget['quant'];			

					if(!empty($prlistvalueget['attributeid']))

						$xml->cart->associations->cart_rows->cart_row[$i]->id_product_attribute = $prlistvalueget['attributeid'];
						
				$i++;
			}
 

            $opt = array('resource' => 'carts');

            $opt['postXml'] = $xml->asXML();

            $xml = $webService->add($opt);

 

            // ID of created cart

 

            $id['cart'] = $xml->cart->id;
			
			//create cart rule
			if($discountget>0)
			{
				$customerid=$id['customer'];
				$cartid= $id['cart'];
				$datefrom=date("Y-m-d h:i:s");
				$dateto=date("Y-m-d h:i:s",(time()+2*3600));
				$dateadd=date("Y-m-d h:i:s",(time()+1*3600));
				
				 $nameset=$customer['firstname']." ".$customer['lastname'];
				 
				echo	$insert="insert into " . _DB_PREFIX_ . "cart_rule set id_customer='$customerid',quantity=1,quantity_per_user=1,reduction_percent='$discountget'
					,reduction_currency=1,partial_use=1,date_from='$datefrom',date_to='$dateto',date_add='$dateadd',date_upd='$dateadd',active=1,description='Discount ".$discountget."% for ".$nameset."'";
					$resultinsert = $db->executeS($insert);	
					$cartruleid = (int)Db::getInstance()->Insert_ID();
					
				echo 	$insert="insert into " . _DB_PREFIX_ . "cart_cart_rule set id_cart='$cartid',id_cart_rule='$cartruleid'";
				 $db->executeS($insert);
				 
				 
							$getlanguageset=Language::getLanguages(true, $this->context->shop->id);
					foreach($getlanguageset as $language)
					{
						
						$languageid=$language['id_lang'];
						$insertlang="insert into " . _DB_PREFIX_ . "cart_rule_lang set id_cart_rule='$cartruleid',id_lang='$languageid',name='".$nameset.$discountget."'";
						$db->executeS($insertlang);	
						
						
					}
					
					
				
			}


		

            // CREATE Order

			$valueget=$this->createorder( $id['cart'],$id['address'],$id['customer'],$carrieridhwe,$fullproductdetails,$discountget,$languageid,$shipping_cost,$paymentmethod);
			
				$cartid=$id['cart'];
				 $update="update " . _DB_PREFIX_ . "orders set current_state=2 where id_cart='$cartid'";
				$result = $db->executeS($update);	
				
			die();

            
		
	}
	public function createorder($cartid,$addressid,$customerid,$carrierid,$productlist,$discountget,$languageid,$shipping_cost,$paymentmethod)
	{
		$keyget=Configuration::get('hweprestakey');
		if($paymentmethod=="bankwire")
		{
			
				$paymenttype="ps_wirepayment";
				$paymeenttitle="Bank";
		}
		
		else if($paymentmethod=="stripe")
		{
			
				$paymenttype="stripe_official";
				$paymeenttitle="Stripe";
		}
		
		
		echo $cartid;
		echo "<Br>";
		echo $addressid;
		echo "<Br>";
		echo $customerid;
		echo "<Br>";
		echo $carrierid;
	 $sitelink=Context::getContext()->shop->getBaseURL(true);
		$totalprice=0;

		foreach($productlist as $valueget)
		{
					$totalprice = $totalprice + ($valueget[price]*$valueget[quant]);
			
		}
		$caculatediscount=$discountget*$totalprice/100;
		$finalprice =$totalprice-$caculatediscount;
		$finalprice=$finalprice+$shipping_cost;
		
	//
echo $postfield='<?xml version="1.0" encoding="UTF-8"?>
<prestashop xmlns:xlink="http://www.w3.org/1999/xlink">
<order>

	<id_address_delivery xlink:href="'.$sitelink.'api/addresses/'.$addressid.'"><![CDATA['.$addressid.']]></id_address_delivery>
	<id_address_invoice xlink:href="'.$sitelink.'api/addresses/'.$addressid.'"><![CDATA['.$addressid.']]></id_address_invoice>
	<id_cart xlink:href="'.$sitelink.'api/carts/'.$cartid.'"><![CDATA['.$cartid.']]></id_cart>
	<id_currency xlink:href="'.$sitelink.'api/currencies/1"><![CDATA[1]]></id_currency>
	<id_lang xlink:href="'.$sitelink.'api/languages/'.$languageid.'"><![CDATA['.$languageid.']]></id_lang>
	<id_customer xlink:href="'.$sitelink.'api/customers/'.$customerid.'"><![CDATA['.$customerid.']]></id_customer>
	<id_carrier><![CDATA['.$carrierid.']]></id_carrier>
	<current_state xlink:href="'.$sitelink.'api/order_states/2"><![CDATA[2]]></current_state>	
	<module><![CDATA['.$paymenttype.']]></module>
	<invoice_number><![CDATA[0]]></invoice_number>
	<invoice_date><![CDATA[0000-00-00 00:00:00]]></invoice_date>
	<delivery_number><![CDATA[0]]></delivery_number>
	<delivery_date><![CDATA[0000-00-00 00:00:00]]></delivery_date>
	<valid><![CDATA[0]]></valid>	
	<shipping_number notFilterable="true"></shipping_number>
	<id_shop_group><![CDATA[1]]></id_shop_group>
	<id_shop><![CDATA[1]]></id_shop>
	
	<payment><![CDATA[Payment by '.$paymeenttitle.']]></payment>
	<recyclable><![CDATA[0]]></recyclable>
	<gift><![CDATA[0]]></gift>
	<gift_message></gift_message>
	<mobile_theme><![CDATA[0]]></mobile_theme>
	<total_discounts><![CDATA['.$caculatediscount.']]></total_discounts>
	<total_discounts_tax_incl><![CDATA['.$caculatediscount.']]></total_discounts_tax_incl>
	<total_discounts_tax_excl><![CDATA['.$caculatediscount.']]></total_discounts_tax_excl>
	<total_paid><![CDATA['.$finalprice.']]></total_paid>
	<total_paid_tax_incl><![CDATA['.$finalprice.']]></total_paid_tax_incl>
	<total_paid_tax_excl><![CDATA['.$finalprice.']]></total_paid_tax_excl>
	<total_paid_real><![CDATA['.$finalprice.']]></total_paid_real>
	<total_products><![CDATA['.$totalprice.']]></total_products>
	<total_products_wt><![CDATA['.$totalprice.']]></total_products_wt>
	<total_shipping><![CDATA['.$shipping_cost.']]></total_shipping>
	<total_shipping_tax_incl><![CDATA['.$shipping_cost.']]></total_shipping_tax_incl>
	<total_shipping_tax_excl><![CDATA['.$shipping_cost.']]></total_shipping_tax_excl>
	<carrier_tax_rate><![CDATA[0.000]]></carrier_tax_rate>
	<total_wrapping><![CDATA[0.000000]]></total_wrapping>
	<total_wrapping_tax_incl><![CDATA[0.000000]]></total_wrapping_tax_incl>
	<total_wrapping_tax_excl><![CDATA[0.000000]]></total_wrapping_tax_excl>
	<round_mode><![CDATA[2]]></round_mode>
	<round_type><![CDATA[2]]></round_type>
	<conversion_rate><![CDATA[1.000000]]></conversion_rate>
	<reference><![CDATA[VNCSFUEEF]]></reference>
<associations>
<order_rows nodeType="order_row" virtualEntity="true">';


foreach($productlist as $valuepr)
{
	
	$postfield .='<order_row>
	<id><![CDATA[2]]></id>
	<product_id xlink:href="'.$sitelink.'api/products/'.$valuepr['id'].'"><![CDATA['.$valuepr[id].']]></product_id>
	<product_attribute_id><![CDATA['.$valuepr[attributeid].']]></product_attribute_id>
	<product_quantity><![CDATA['.$valuepr[quant].']]></product_quantity>
	<product_name><![CDATA['.$valuepr[name].']]></product_name>
	<product_reference><![CDATA['.$valuepr[sku].']]></product_reference>
	<product_ean13><![CDATA['.$valuepr[ean13].']]></product_ean13>
	<product_isbn></product_isbn>
	<product_upc></product_upc>
	<product_price><![CDATA['.$valuepr[price].']]></product_price>
	<id_customization xlink:href="'.$sitelink.'api/customizations/0"><![CDATA[0]]></id_customization>
	<unit_price_tax_incl><![CDATA['.$valuepr[price].']]></unit_price_tax_incl>
	<unit_price_tax_excl><![CDATA['.$valuepr[price].']]></unit_price_tax_excl>
	</order_row>';
}

 echo $postfield .='</order_rows>
</associations>
</order>
</prestashop>';

		$curl = curl_init();

					curl_setopt_array($curl, array(
				  CURLOPT_URL => $sitelink."api/orders",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				    CURLOPT_SSL_VERIFYHOST=>0,
					CURLOPT_SSL_VERIFYPEER=>0,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => $postfield,
				  CURLOPT_HTTPHEADER => array(
					"authorization: Basic ".base64_encode($keyget.":"),
					"cache-control: no-cache",
					"content-type: text/xml",
					"postman-token: 1c16036c-c281-349e-3218-bba93393516d"
								  ),
								));

								$response = curl_exec($curl);
								$err = curl_error($curl);

								curl_close($curl);

								if ($err) {
								  echo "cURL Error #:" . $err;
								} else {
									
									echo "underset";
									echo $response;
									  echo "<hr>";
								   $response=simplexml_load_string($response);
								   echo "<hr>";
								     echo "<hr>";
								   print_r($response);
								   return $response;
								}
		
		
	}
	function getgroups()
	{
		$db = \Db::getInstance();
		$request = "SELECT * FROM " . _DB_PREFIX_ . "configuration where name='PS_SHOP_DOMAIN'";
		
		$result = $db->executeS($request);
		$shopdomain=$result[0]['value'];
		
		
		$keyget=Configuration::get('hweprestakey');
		
			$link="https://".$keyget."@".$shopdomain."/api/groups/?ws_key=".$keyget."&output_format=JSON";
			
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => $link,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				    CURLOPT_SSL_VERIFYHOST=>0,
					CURLOPT_SSL_VERIFYPEER=>0,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_HTTPHEADER => array(
					"cache-control: no-cache",
					"postman-token: e06b1241-33f8-2108-5d62-34ec2c0f26ca"
				  ),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);
				if ($err) {
				  
				} else {
					
					
				  return $response=json_decode($response,true);
				}
	}
	
	
	
}

?>