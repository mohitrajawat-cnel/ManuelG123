<?php
/**
 * <ModuleClassName> => Cheque
 * <FileName> => validation.php
 * Format expected: <ModuleClassName><FileName>ModuleFrontController
 */
class prestapros_apiconnectorProductlistModuleFrontController extends ModuleFrontController
{
	
	public function initContent()
	{
		$db = \Db::getInstance();

		$product_count = "CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "prestaproduct (
			ID int(11) AUTO_INCREMENT,
			`count` bigint(255) DEFAULT '0',
			`date` DATETIME NULL,
			PRIMARY KEY  (ID)
			)";
			
		   $db->execute($product_count);
		


		$date=date("Y-m-d h:i:s");
		$select="select * from " . _DB_PREFIX_ . "prestaproduct";
		$query=$db->executeS($select);


		$limit=10;
		$start=0;
		if(count($query)>0)
		{
			foreach($query as $row)
				{
					$lastcount=$row['count'];	
					$start=$lastcount;
				}
				
				$updatecount= $start+$limit;
			// $update="update " . _DB_PREFIX_ . "prestaproduct set count='$updatecount',date='$date'";
			// $db->execute($request);
			
		}
		else
		{
			$updatecount= $start+$limit;
			$insert="insert into " . _DB_PREFIX_ . "prestaproduct set count='$updatecount',date='$date'";
			$db->execute($insert);
			
		}

		$request = "SELECT * FROM " . _DB_PREFIX_ . "configuration where name='PS_SHOP_DOMAIN'";
		
		 $result = $db->executeS($request);
		 $shopdomain=$result[0]['value'];
		//  $start=$_GET['start'];
		//  $limit=$_GET['limit'];
		//  $pidset=$_GET['pid'];
		//  $pidset=str_replace("'","",$pidset);
		 $keyget=Configuration::get('hweprestakey');
		 $urlget=Configuration::get('hweprestaurl');
		
		 if(!$keyget)
		 {
			
			die();
		 }
		
		if(isset($_GET['languageid']))
		{
				$getlanguageset=Language::getLanguages(true, $this->context->shop->id);
				echo json_encode($getlanguageset);
				die();
			
		}

		
		
		 if(isset($pidset) && $pidset !='')
		{
			    
				$link="https://".$keyget."@".$shopdomain."/api/products?ws_key=".$keyget."&filter[id]=[$pidset]&output_format=JSON";
			
		}
		else
		{
		
			 $link='https://'.$keyget.'@'.$urlget.'/api/products/?ws_key='.$keyget.'&limit='.$start.','.$limit.'&sort=[id_DESC]&output_format=JSON';
		}
		

				$curl = curl_init();

				curl_setopt_array($curl, array(
				CURLOPT_URL => $link,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_HTTPHEADER => array(
					"cache-control: no-cache",
					"postman-token: e06b1241-33f8-2108-5d62-34ec2c0f26ca"
				),
				));
				
				$response = curl_exec($curl);
				$err = curl_error($curl);
				
				curl_close($curl);
				//echo $response;

				if ($err) {
				  
				} else {
					
				   $response=json_decode($response,true);
				}
			
			
				unset($productlistinformation);
				$fp = fopen('tesfile.txt', 'a');
				if(count($response) > 0)
				{

					foreach($response['products'] as $value)
					{
						
						
						$idset=$value['id'];
						$getproductdetails=$this->getproductinformation($idset,$shopdomain,'1');
						$productlistinformation[]=$getproductdetails;

						$products_data = $getproductdetails['product'];

							// echo "<pre>";
							// 	print_r($getproductdetails);
							// echo "</pre>";

							// echo "<hr><br>";

						// if($products_data['type'] == 'simple')
						// {
							$stock_data = $products_data['associations']['stock_availables'];
						
							foreach($stock_data as $stock_data_hwe)
							{
								$stock_id = $stock_data_hwe['id'];
								$curl_stock = curl_init();

								curl_setopt_array($curl_stock, array(
								  CURLOPT_URL => 'https://'.$keyget.'@'.$urlget.'/api/stock_availables/'.$stock_id.'?ws_key='.$keyget.'&output_format=JSON',
								  CURLOPT_RETURNTRANSFER => true,
								  CURLOPT_ENCODING => '',
								  CURLOPT_MAXREDIRS => 10,
								  CURLOPT_TIMEOUT => 0,
								  CURLOPT_FOLLOWLOCATION => true,
								  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
								  CURLOPT_CUSTOMREQUEST => 'GET',
								  CURLOPT_HTTPHEADER => array(
									'Cookie: PrestaShop-05bbcded34909c089c1cb5c66b1e6dbf=def502005a3aa32e03020dcaa8ac4f1e2652f235cdb584e3632b2e1280e22bdcf1f225db00ba5f2386a2e4954870ad9adf676fbe8157272b3756397a925856d57938736b2641d7282e54bb58a7358cc966ef50ec40a2bd9ee80fbe817a66cf8c8b4f3353ce0c72bb83fc791c4a17df88793e1330d4adff605eab33d4373f9645ec8bdca3d893768133ef135dc611b7d398abd5fc174f62bba27b34cee197b9abb8321a'
								  ),
								));
								
								$response_stock = curl_exec($curl_stock);
								
								curl_close($curl_stock);
								$response_data = json_decode($response_stock,true);

								
								$stock_available_data = $response_data['stock_available'];
								$quantity = $stock_available_data['quantity'];
								$reference = $products_data['reference'];
								$supplier_reference = $products_data['supplier_reference'];
								

								$select_reference="select * from ". _DB_PREFIX_ ."product_supplier where `product_supplier_reference`='".$reference."'";
								$query_reference=$db->executeS($select_reference);
						
								//echo $reference."<br>";
						
								// ini_set('display_errors', 1);
								// ini_set('display_startup_errors', 1);
								// error_reporting(E_ALL);
								if(count($query_reference)>0)
								{
								
									foreach($query_reference as $result)
									{
											
											$presta_product_id=$result['id_product'];
											$result_data = $this->getproductinformation($presta_product_id,$shopdomain,'2');	
										    $presta_products_data = $result_data['product'];

											$presta_stock_data = $presta_products_data['associations']['stock_availables'];
						
											foreach($presta_stock_data as $presta_stock_data_hwe)
											{
												$presta_stock_id = $presta_stock_data_hwe['id'];
												$curl = curl_init();

												curl_setopt_array($curl, array(
												  CURLOPT_URL => 'https://9BYGKI1SC5W7AT5V6P8FNC9LBGCCENSF@'.$shopdomain.'/api/stock_availables/'.$presta_stock_id.'?ws_key=9BYGKI1SC5W7AT5V6P8FNC9LBGCCENSF&output_format=JSON',
												  CURLOPT_RETURNTRANSFER => true,
												  CURLOPT_ENCODING => '',
												  CURLOPT_MAXREDIRS => 10,
												  CURLOPT_TIMEOUT => 0,
												  CURLOPT_FOLLOWLOCATION => true,
												  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
												  CURLOPT_CUSTOMREQUEST => 'GET',
												  CURLOPT_HTTPHEADER => array(
													'Cookie: PrestaShop-2a6dd4105317d482533c7a3264b5c0fb=def502005d406444729bd70a142e70fc96578c14b899f37b1de018410119191987b93804958496a7a20722a7a33631c328717d886ef86f17527898cfdbe3a7287d0580b4a6ceae4ba8ec68c01b6382cabe74c9e6371456a10e1b49a16882dae8c922462b32b34d00e561d3b1d66d8ecbaa5525b857b967cebbbf550925fe30419dbfed20c083398c8beaf7623bd8691097ea0b41d845a1e5548dee9e17ab08ea80240b31739e49461f3cca47f06cc404dabfe15cd09bdc594311c945a8733c4c7d305bf904d3bc1361929b1fc66c53439864be891746839a45'
												  ),
												));
												
												$response = curl_exec($curl);
												
												curl_close($curl);
												

												$stock_data_presta_hwe = json_decode($response,true);


												
												$location_xml='';
												$id_xml = $stock_data_presta_hwe['stock_available']['id'];
												$id_product_xml = $stock_data_presta_hwe['stock_available']['id_product'];
												$id_product_attribute_xml = $stock_data_presta_hwe['stock_available']['id_product_attribute'];
												$id_shop_xml = $stock_data_presta_hwe['stock_available']['id_shop'];
												$id_shop_group_xml = $stock_data_presta_hwe['stock_available']['id_shop_group'];
												$depends_on_stock_xml = $stock_data_presta_hwe['stock_available']['depends_on_stock'];
												$out_of_stock_xml = $stock_data_presta_hwe['stock_available']['out_of_stock'];
												$location_xml = $stock_data_presta_hwe['stock_available']['location'];

												$xml_data = '<?xml version="1.0" encoding="UTF-8"?>
												<prestashop xmlns:xlink="http://www.w3.org/1999/xlink">
													<stock_available>
														<id>
																<![CDATA['.$id_xml.']]>
														</id>
														<id_product xlink:href="https://'.$shopdomain.'/api/products/'.$id_product_xml.'"><![CDATA['.$id_product_xml.']]></id_product>
														<id_product_attribute><![CDATA['.$id_product_attribute_xml.']]></id_product_attribute>
														<id_shop xlink:href="https://'.$shopdomain.'/api/shops/'.$id_shop_xml.'"><![CDATA['.$id_shop_xml.']]></id_shop>
														<id_shop_group><![CDATA['.$id_shop_group_xml.']]></id_shop_group>
														<quantity><![CDATA['.$quantity.']]></quantity>
														<depends_on_stock><![CDATA['.$depends_on_stock_xml.']]></depends_on_stock>
														<out_of_stock><![CDATA['.$out_of_stock_xml.']]></out_of_stock>
														<location><![CDATA['.$location_xml.']]></location>
													</stock_available>
												</prestashop>';

												$curl = curl_init();

												curl_setopt_array($curl, array(
												CURLOPT_URL => 'https://9BYGKI1SC5W7AT5V6P8FNC9LBGCCENSF@'.$shopdomain.'/api/stock_availables/'.$presta_stock_id.'?ws_key=9BYGKI1SC5W7AT5V6P8FNC9LBGCCENSF',
												CURLOPT_RETURNTRANSFER => true,
												CURLOPT_ENCODING => '',
												CURLOPT_MAXREDIRS => 10,
												CURLOPT_TIMEOUT => 0,
												CURLOPT_FOLLOWLOCATION => true,
												CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
												CURLOPT_CUSTOMREQUEST => 'PUT',
												CURLOPT_POSTFIELDS =>$xml_data,
												CURLOPT_HTTPHEADER => array(
													'ws_key: 9BYGKI1SC5W7AT5V6P8FNC9LBGCCENSF',
													'Content-Type: application/xml',
													'Cookie: PrestaShop-2a6dd4105317d482533c7a3264b5c0fb=def502005d406444729bd70a142e70fc96578c14b899f37b1de018410119191987b93804958496a7a20722a7a33631c328717d886ef86f17527898cfdbe3a7287d0580b4a6ceae4ba8ec68c01b6382cabe74c9e6371456a10e1b49a16882dae8c922462b32b34d00e561d3b1d66d8ecbaa5525b857b967cebbbf550925fe30419dbfed20c083398c8beaf7623bd8691097ea0b41d845a1e5548dee9e17ab08ea80240b31739e49461f3cca47f06cc404dabfe15cd09bdc594311c945a8733c4c7d305bf904d3bc1361929b1fc66c53439864be891746839a45'
												),
												));

												$response = curl_exec($curl);

												curl_close($curl);
												if($response)
												{
													//opens file in append mode  
													fwrite($fp, "Product Id :".$products_data."  Product Type : ".$products_data['type']."<br>");  
													fclose($fp);  
													
													
													echo "Stock updated successfully.<br>";
													echo "quantity : ".$quantity."<br>";
													echo "reference : ".$reference."<br>";

													echo "<br><hr>";
												}

												
											}

									}
								}
								else
								{
									echo "Product Not Found.<br>";
									echo "Product Reference : ".$reference;
									echo "<br><hr>";
								}

						

							}

							
							
						// }
						// else
						// { 
						// 	echo "Product Type ".$products_data['type']."<br>";
						// 	echo $products_data['id'];
						// 	echo "<br><hr>";

						// }

					


					
					}

					$updatecount= $start+$limit;
					$update="update " . _DB_PREFIX_ . "prestaproduct set count='$updatecount',date='$date'";
					$db->execute($update);

				}
				else
				{
					
					$update="update " . _DB_PREFIX_ . "prestaproduct set count='0',date='$date'";
					$db->execute($request);
				}
				// echo "<pre>";
				// 	print_r($productlistinformation);
				// echo "</pre>";
			
				die();
			
		
	}
	
	public function getproductinformation($pid,$shopdomain,$type)
	{
		
		$keyget=Configuration::get('hweprestakey');
		$urlget=Configuration::get('hweprestaurl');
		// if($languageid>0)
		// {
		// 		   $link="https://".$keyget."@".$shopdomain."/api/orders/".$pid."?ws_key=".$keyget."&output_format=JSON&language=".$languageid;
		// }
		// else
		// {
			if($type == '1')
			{
				 $link="https://".$keyget."@".$urlget."/api/products/".$pid."?ws_key=".$keyget."&output_format=JSON";
			}
			else
			{
				 $link="https://9BYGKI1SC5W7AT5V6P8FNC9LBGCCENSF@".$shopdomain."/api/products/".$pid."?ws_key=9BYGKI1SC5W7AT5V6P8FNC9LBGCCENSF&output_format=JSON";
			}
				 
			
		//}
		
	
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => $link,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				   CURLOPT_SSL_VERIFYHOST=>0,
					  CURLOPT_SSL_VERIFYPEER=>0,
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
				
				return;
				
	}
	
}

?>