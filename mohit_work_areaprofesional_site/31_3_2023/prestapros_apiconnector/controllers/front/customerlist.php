<?php
/**
 * <ModuleClassName> => Cheque
 * <FileName> => validation.php
 * Format expected: <ModuleClassName><FileName>ModuleFrontController
 */
class prestapros_apiconnectorCustomerlistModuleFrontController extends ModuleFrontController
{
	
	public function initContent()
	{
		$db = \Db::getInstance();
		
		$request = "SELECT * FROM " . _DB_PREFIX_ . "configuration where name='PS_SHOP_DOMAIN'";
		
		$result = $db->executeS($request);
		$shopdomain=$result[0]['value'];
		$start=$_GET['start'];
		$limit=$_GET['limit'];
		$pidset=$_GET['pid'];
		$pidset=str_replace("'","",$pidset);
		$keyget=Configuration::get('hweprestakey');
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
		
		

					$getproductdetails=$this->getproductinformation($pidset,$shopdomain,$_GET['lngid']);
	
            echo json_encode($getproductdetails);
		
			die();
				
				
		
		
	}
	
	public function getproductinformation($pid,$shopdomain,$languageid)
	{
		
		$keyget=Configuration::get('hweprestakey');
		if($languageid>0)
		{
				   $link="https://".$keyget."@".$shopdomain."/api/customers/".$pid."?ws_key=".$keyget."&output_format=JSON&language=".$languageid;
		}
		else
		{
				  $link="https://".$keyget."@".$shopdomain."/api/customers/".$pid."?ws_key=".$keyget."&output_format=JSON";
			
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