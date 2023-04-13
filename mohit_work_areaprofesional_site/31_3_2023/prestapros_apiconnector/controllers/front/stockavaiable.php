<?php
/**
 * <ModuleClassName> => Cheque
 * <FileName> => validation.php
 * Format expected: <ModuleClassName><FileName>ModuleFrontController
 */
class prestapros_apiconnectorStockavaiableModuleFrontController extends ModuleFrontController
{
	
	public function initContent()
	{
		$db = \Db::getInstance();
		
		$request = "SELECT * FROM " . _DB_PREFIX_ . "configuration where name='PS_SHOP_DOMAIN'";
		
		$result = $db->executeS($request);
		$shopdomain=$result[0]['value'];
		$pid=$_GET['pid'];
		
		if(isset($_REQUEST['getbarcodedata']))
		{
		
				$barcode=$_REQUEST['getbarcodedata'];
				
				
				
				//	$barcode=$listvalue['barcode'];
					$attributeid="";
					
						   $request = "SELECT * FROM " . _DB_PREFIX_ . "product where 
						  ean13='$barcode' ";		
						$result = $db->executeS($request);	
						if(count($result)>0)
						{					
						
								echo $id_product=$result[0]['id_product'];
								die();
								
								
								
								
								 
						}
						else					
							{
									 $requestatt= "SELECT * FROM " . _DB_PREFIX_ . "product_attribute where (ean13='$barcode')";		
									$resultatt = $db->executeS($requestatt);
									
									echo $id_product_attribute=$resultatt[0]['id_product_attribute'];
										
																		 
								
							}
						
						
							
							
							
				
				//echo $totalpriceset;
			exit;
		}
		
		
		 $request = "SELECT * FROM " . _DB_PREFIX_ . "product where 
						  reference='$pid' ";		
						$result = $db->executeS($request);	
						if(count($result)>0)
						{					
						
								 $pid=$result[0]['id_product'];
								
								
								
								
								
								 
						}
						else
						{
							echo json_encode(array("statusset"=>0));
							die();	
							
						}
		
		$keyget=Configuration::get('hweprestakey');
		
  $link="https://".$keyget."@".$shopdomain."/api/products/".$pid."?ws_key=".$keyget."&output_format=JSON";
		
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
					
				   $response=json_decode($response,true);
				}
				
				
		
				
					
					
					  $idset=$pid;
					$product = new Product($idset, false, Context::getContext()->language->id);
					
					$stockidset=$response[product]['associations']['stock_availables'][0][id];
					  $requeststock = "SELECT * FROM " . _DB_PREFIX_ . "stock_available where id_stock_available='$stockidset'";
							
					$resultstock = $db->executeS($requeststock);
					 $quantity=$resultstock[0]['quantity'];
					 $basequantity=$product->quantity;
					
					$combinations=$product->getAttributeCombinations(Context::getContext()->language->id);
					$flag=0;
						if(count($combinations)>0)
						{	
							foreach($combinations as $combvalue)
								{
									//echo $combvalue['attribute_name'];
									
									if($combvalue['attribute_name']=="N/A")
									{
										
										$flag=1;
										unset($productstockinfo[cominbation]);
										break;	
									}
								
									
									//print_r($combvalue);
									$idproductatt=$combvalue['id_product_attribute'];
									$requestatt= "SELECT * FROM " . _DB_PREFIX_ . "product_attribute where id_product_attribute='$idproductatt'";		
									$resultatt = $db->executeS($requestatt);
									
									$id_product=$resultatt[0]['id_product'];
									$reference=$resultatt[0]['reference'];
									$barcode=$resultatt[0]['ean13'];
										
										 $requestpr = "SELECT * FROM " . _DB_PREFIX_ . "product where id_product='$id_product'";		
											$resultpr = $db->executeS($requestpr);	
									
									 $price=$resultatt[0]['price']+$resultpr[0]['price'];
									
										$productstockinfo[$idset][$combvalue['id_product_attribute']]=array($basequantity+$combvalue['quantity'], $price,$reference,$barcode);
										
									
				
				
								}
							$productstockinfo[cominbation]=count($combinations);
						}						
						
						else
						{
							
							
							 $request = "SELECT * FROM " . _DB_PREFIX_ . "product where id_product='$pid'";		
								$result = $db->executeS($request);	
								 $price=$result[0]['price'];
						
								$productstockinfo[$idset]=array($quantity,$price);
								$productstockinfo[cominbation]=0;
							
						}
						
					if($flag==1)
						{
							
							 $request = "SELECT * FROM " . _DB_PREFIX_ . "product where id_product='$pid'";		
								$result = $db->executeS($request);	
								 $price=$result[0]['price'];
						
								$productstockinfo[$idset]=array($quantity,$price);
								$productstockinfo[cominbation]=0;
							
						}
						
					
					
				
			echo json_encode($productstockinfo);
				
				//print_r($productstockinfo);
				die();
				
				
		
		
	}
	
	
}

?>