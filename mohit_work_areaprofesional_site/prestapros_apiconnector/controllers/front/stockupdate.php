<?php
/**
 * <ModuleClassName> => Cheque
 * <FileName> => validation.php
 * Format expected: <ModuleClassName><FileName>ModuleFrontController
 */
class prestapros_apiconnectorStockupdateModuleFrontController extends ModuleFrontController
{
	
	public function initContent()
	{
		$db = \Db::getInstance();


		//mohit
		
		$date=date("Y-m-d h:i:s");

			$request = "SELECT * FROM " . _DB_PREFIX_ . "configuration where name='PS_SHOP_DOMAIN'";
			
			$result = $db->executeS($request);
			$shopdomain=$result[0]['value'];

			$keyget=Configuration::get('hweprestakey');

			$clientkey=Configuration::get('clientkey');
			$applicationkey=Configuration::get('applicationkey');
			$apiurl=Configuration::get('api_url');
			
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

		$select="SELECT  * from  " . _DB_PREFIX_ . "syncproduct";
		$query=$db->executeS($select);
		$pidset='';
			$goprid='';
			$productlistinformation_hwe =array();
		foreach($query as $result1)
		{
			$update4="UPDATE " . _DB_PREFIX_ . "prestaproduct set date='$date' where id='".$result1['ID']."'";
			$db->executeS($update4);
	
		
			$pidset='';
			$goprid='';
			$pidset=$result1['prestapid'];
			$goprid=$result1['goprid'];
			
			if(isset($pidset) && $pidset !='')
			{
					
					$link="https://".$keyget."@".$shopdomain."/api/products?ws_key=".$keyget."&filter[id]=[$pidset]&output_format=JSON";
				
			}
			else
			{
			
				$link="https://".$keyget."@".$shopdomain."/api/products?ws_key=".$keyget."&limit=".$start.",".$limit."&sort=[id_DESC]&output_format=JSON";
			}
		
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
					
				   $response=json_decode($response,true);
				}
				
				
				unset($productlistinformation);
				
			
				foreach($response['products'] as $value)
				{
					
					
					  $idset=$value['id'];
					unset($getproductdetails);
					$getproductdetails=$this->getproductinformation($idset,$shopdomain,$_GET['lngid']);
					
					
					//echo "<br>";
					
					
					$id_manufacturer = $getproductdetails['product']['id_manufacturer'];
					
					$stockidset=$getproductdetails['product']['associations']['stock_availables'][0]['id'];
					 $requeststock = "SELECT * FROM " . _DB_PREFIX_ . "stock_available where id_stock_available='$stockidset'";
		
					$resultstock = $db->executeS($requeststock);
				
					
					$productlistinformation[$idset][quantity]=$resultstock[0]['quantity'];
					$productlistinformation[$idset][type]=$getproductdetails['product']['type'];
					$productlistinformation[$idset][reference]=$getproductdetails['product']['reference'];
					$productlistinformation[$idset][width]=$getproductdetails['product']['width'];
					$productlistinformation[$idset][height]=$getproductdetails['product']['height'];
					$productlistinformation[$idset][depth]=$getproductdetails['product']['depth'];
					$productlistinformation[$idset][weight]=$getproductdetails['product']['weight'];
					$productlistinformation[$idset][ean13]=$getproductdetails['product']['ean13'];
					$productlistinformation[$idset][isbn]=$getproductdetails['product']['isbn'];
					
						$productlistinformation[$idset][upc]=$getproductdetails['product']['upc'];
					$productlistinformation[$idset][mpn]=$getproductdetails['product']['mpn'];
					$productlistinformation[$idset][price]=$getproductdetails['product']['price'];
					
					$productlistinformation[$idset][description]=$getproductdetails['product']['description'];
					$productlistinformation[$idset][description_short]=$getproductdetails['product']['description_short'];
					
				
					$languageiduserd=Context::getContext()->language->id;				
					if(isset($_GET['lngid']))
					{
						$productlistinformation[$idset]['manufacturer_name']=$getproductdetails['product']['manufacturer_name'];
						$productlistinformation[$idset][name]=$getproductdetails['product']['name'];
						$languageiduserd=$_GET['lngid'];
					}
					else
					{
							
									$getlanguageset=Language::getLanguages(true, $this->context->shop->id);
							foreach($getlanguageset as $language)
							{
								$langhwe[$language['id_lang']]=$language['language_code'];
								$manufacturer_nameset=new Manufacturer($id_manufacturer, $language['id_lang']);
										
								$productlistinformation[$idset]['manufacturer_name'][$language['language_code']]=$manufacturer_nameset->name;
								$productlistinformation[$idset][language][]=$language['language_code'];
							}
							
						if(is_array($getproductdetails['product']['name']))
						{
						
							foreach($getproductdetails['product']['name'] as $valuename)
							{
										$productlistinformation[$idset][name][$langhwe[$valuename['id']]]=$valuename['value'];
								
							}
						}
						else
						{
						
							$productlistinformation[$idset][name]=$getproductdetails['product']['name'];	
							
						}
						
					}
		
			
					
					$product = new Product($idset);	
				$basequantity=$product->quantity;					
					$combinations=$product->getAttributeCombinations($languageiduserd);	
					
				
				unset($combset);
				$hwecheck=array();
			
				if(count($combinations)>0)
				{
					
					
					foreach($combinations as $combvalue)
					{
						if(!in_array($combvalue['reference'],$hwecheck))
						{
							$productlistinformation[$idset][quantity]=$basequantity+$combvalue['quantity'];
							array_push($hwecheck,$combvalue['reference']);
							
						}
						  $imgs = Image::getImages($languageiduserd, $combvalue['id_product'], $combvalue['id_product_attribute']);
						  if(count($imgs) > 0)
							{
								$image_url = $this->context->link->getImageLink($product->link_rewrite[$languageiduserd], $idset . '-' . $imgs[0]['id_image'], 'large_default');
								$combset[$combvalue['id_product_attribute']]['image']=$image_url;
						
							}
						  
						
						if(count($combset[$combvalue['id_product_attribute']])>0)
						{
							
							$combset[$combvalue['id_product_attribute']]['groupnameset'][]=$combvalue['group_name'];
							$combset[$combvalue['id_product_attribute']]['attribute_nameset'][$combvalue['group_name']]=$combvalue['attribute_name'];
								if($combvalue['attribute_name']=="N/A")
								{
									unset($combset);
									break;	
								}
							
						}
						else
						{
							$combset[$combvalue['id_product_attribute']]=$combvalue;
							$combset[$combvalue['id_product_attribute']]['groupnameset'][]=$combvalue['group_name'];
							$combset[$combvalue['id_product_attribute']]['attribute_nameset'][$combvalue['group_name']]=$combvalue['attribute_name'];
							
							if($combvalue['attribute_name']=="N/A")
								{
									unset($combset);
									break;	
								}
							
							
						}
								//$getproductdetails['product']['price']
								
								
								$combset[$combvalue['id_product_attribute']][price]=$combvalue[price]+$getproductdetails['product']['price'];
						
					}
					
					
					
				}
				
				$productlistinformation[$idset][combinations]=$combset;
					
					
					$imagesset=$getproductdetails['product']['associations']['images'];
					foreach($imagesset as $imageval)
					{
						
						
						
						$link = new Link;	 
						 $imagePath = $link->getImageLink($product->link_rewrite[$languageiduserd],$imageval[id], 'large_default');
						
						$productlistinformation[$idset][image][]=$imagePath;
					
						
					}
					
					
					
					 $id_category_defaults=$getproductdetails['product']['associations']['categories'];
					 $categorymainset=$getproductdetails['product']['id_category_default'];
				
			if(isset($_GET['lngid']))
			{
					foreach($id_category_defaults as $valcat)
					{						
						$categoryObj = new Category($valcat[id],$_GET['lngid']);	
						
						 $request = "SELECT * FROM " . _DB_PREFIX_ . "category where id_category='$valcat[id]'";		
								$result = $db->executeS($request);	
								$parentid=$result[0]['id_parent'];
								
									
												$productlistinformation[$idset][category][$valcat[id]]=$categoryObj->name."###".$parentid;
										
									
					}					
					 
					 $categoryObj1 = new Category($categorymainset,$_GET['lngid']);
					
												$productlistinformation[$idset][maintype]=$categoryObj1->name;
										
													 
				
			}
			else
			{
				
					foreach($id_category_defaults as $valcat)
					{						
						$categoryObj = new Category($valcat[id]);	
						
						 $request = "SELECT * FROM " . _DB_PREFIX_ . "category where id_category='$valcat[id]'";		
								$result = $db->executeS($request);	
								$parentid=$result[0]['id_parent'];
								
									foreach($categoryObj->name as $key=>$valuenamecat)
									{
												$productlistinformation[$idset][category][$langhwe[$key]][$valcat[id]]=$valuenamecat."###".$parentid;
										
									}
					}					
					 
					 $categoryObj1 = new Category($categorymainset);
					foreach($categoryObj1->name as $key=>$valuenamecat)
									{
												$productlistinformation[$idset][maintype][$langhwe[$key]]=$valuenamecat;
										
									}		
				
			}
					
					
					
					$product_features=$getproductdetails['product']['associations']['product_features'];
					
					
					foreach ($product_features as $feature)
					{ 
					
						$featureid=$feature[id];

							 $request = "SELECT * FROM " . _DB_PREFIX_ . "feature_lang where id_feature='$featureid' and id_lang='$languageiduserd'";		
								$result = $db->executeS($request);	

							$featurename=$result[0][name];					
							$featureValueObj = new FeatureValue($feature['id_feature_value'],$languageiduserd);
						
							$featurevalue= $featureValueObj->value;
							
							$productlistinformation[$idset][features][]=$featurename."-".$featurevalue;
							
							
						//die();
					}
					$productlistinformation[$idset]['goprid']=$goprid;
					
				
			}

			$productlistinformation_hwe[] =$productlistinformation;
		}

		    // power office functions
		
			if(count($productlistinformation_hwe) > 0)
			{
				//echo json_encode($productlistinformation);

				function createaccesstoken($apiurl,$clientkey,$applicationkey)
				{
				// echo $apiurl;
				// echo $clientkey."<br>";
				// echo $applicationkey;
				
			
			
						$curl = curl_init();
			
						curl_setopt_array($curl, array(
						CURLOPT_URL => $apiurl."OAuth/Token",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
					
					CURLOPT_POSTFIELDS => "grant_type=client_credentials",
						CURLOPT_HTTPHEADER => array(
							"accept: application/json",
							"authorization: Basic ".base64_encode("$applicationkey:$clientkey"),
						
							"content-type: application/x-www-form-urlencoded",
							
						),
						));
			
						$response = curl_exec($curl);
			
				// print_r($response);
				// die;
			
						$err = curl_error($curl);
			
						curl_close($curl);

						return json_decode($response,true);
					
						
				}

				function getproduct($apiurl,$accesstoken,$productid)
				{
					
					
				

				$curl = curl_init();

				curl_setopt_array($curl, array(
					CURLOPT_URL => $apiurl."Product/".$productid,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "GET",
					CURLOPT_HTTPHEADER => array(
					"authorization: Bearer ".$accesstoken,
					"cache-control: no-cache",
					"content-type: application/json",
					
					),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				return json_decode($response,true);
				

				
				}

				function stockupdate($apiurl,$accesstoken,$postrequest)
				{
					
					
					
						$curl = curl_init();
						curl_setopt_array($curl, array(
						CURLOPT_URL => $apiurl.'Product',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_POSTFIELDS =>json_encode($postrequest),
						CURLOPT_HTTPHEADER => array(
							"authorization: Bearer ".$accesstoken,
							"cache-control: no-cache",
							"Content-Type: application/json"
						
						),
						));
						
						$response = curl_exec($curl);
						$err = curl_error($curl);
						
						curl_close($curl);
						return json_decode($response,true);
				}
	
				$getresponse2 =$productlistinformation_hwe;

			
				
				$create_ass_token =  createaccesstoken($apiurl,$clientkey,$applicationkey);
			
				$accesstoken = $create_ass_token['access_token'];

				if(!empty($getresponse2))
				{ 
					foreach($getresponse2 as $getresponse)
					{

						foreach($getresponse as $key=>$value)
						{
							$quantity=	$value['quantity'];
							$goprid =$value['goprid'];
							
						
						}  
				      
						$response_product = getproduct($apiurl,$accesstoken,$goprid);	
							
						
						$response_product['data']["productsOnHand"]=$quantity;
						$response_product['data']["availableStock"]=$quantity;
						
					
						$response_stockupdate = stockupdate($apiurl,$accesstoken,$response_product['data']);
						echo "<br>";
						echo "Stock update for product id : ".$goprid;
						echo "<br>";
						echo "Stock update successfully.";
						echo "<br>";
						echo "<pre>";
							print_r($response_stockupdate);
						echo "<pre>";
						echo "<hr>";
					}

				}
				else
				{
					echo "Stock not update for product id : ".$goprid."<hr>";
				}
			
				
			}
			else
			{
				echo json_encode(array("statusset"=>0));
				echo "<br>";
				echo "Not Product Found";
				$update3="update " . _DB_PREFIX_ . "prestaproduct set count='0',date='$date'";
				$db->executeS($update3);
			}
					
		
				
				
		
		
	}
	
	public function getproductinformation($pid,$shopdomain,$languageid)
	{
		
		$keyget=Configuration::get('hweprestakey');
		if($languageid>0)
		{
				   $link="https://".$keyget."@".$shopdomain."/api/products/".$pid."?ws_key=".$keyget."&output_format=JSON&language=".$languageid;
		}
		else
		{
				 $link="https://".$keyget."@".$shopdomain."/api/products/".$pid."?ws_key=".$keyget."&output_format=JSON";
			
		}
		
			 
		
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