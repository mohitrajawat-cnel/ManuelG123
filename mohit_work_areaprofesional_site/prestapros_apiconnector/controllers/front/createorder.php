<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include('config.php');
include('functions.php');

global $conn;
global $prestashoplink;
$date=date("Y-m-d h:i:s");
$select="select * from prestacustomer";
 $query=mysqli_query($conn,$select);
 $limit=10;
 $start=0;
 if(mysqli_num_rows($query)>0)
 {
	while($row=mysqli_fetch_array($query))
		{
			$lastcount=$row['count'];	
			 $start=$lastcount;
		}
		
		$updatecount= $start+$limit;
	$update="update prestacustomer set count='$updatecount',date='$date'";
	mysqli_query($conn,$update);
	 
 }
 else
 {
	$updatecount= $start+$limit;
	$insert="insert into prestacustomer set count='$updatecount',date='$date'";
	mysqli_query($conn,$insert);
	 
 }
//echo $prestashoplink."/index.php?fc=module&module=prestapros_apiconnector&controller=createcustomorder&start=".$start."&limit=".$limit;
$curl = curl_init();

	curl_setopt_array($curl, array(
		  CURLOPT_URL => $prestashoplink."/index.php?fc=module&module=prestapros_apiconnector&controller=createcustomorder&start=".$start."&limit=".$limit,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
		   
		  ),
		));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

		if ($err)
		 {
		 	 echo "cURL Error #:" . $err;
			 die();
		} 
		else
		 {
		 	 $getresponse=json_decode($response,true);
		 }
	
		 if(isset($getresponse['statusset']) && $getresponse['statusset'] == 0)
		 {
			$update="update prestacustomer set count='0',date='$date'";
			mysqli_query($conn,$update);
			die();
		 }
		 
		 $create_ass_token =  createaccesstoken();
		
		$accesstoken = $create_ass_token['access_token'];
	
		$accesstoken = $create_ass_token['access_token'];

		foreach($getresponse as $key=>$value)
		{
			$select_order="select * from `order` where order_id='".$value['id']."'";
			$query_order=mysqli_query($conn,$select_order);
			if(mysqli_num_rows($query_order)>0)
			{
				
			}
			else
			{
				$id_order=$value['id'];
				$id_address_delivery =$value['id_address_delivery'];
				// $postrequest_order["id_address_invoice"]=$value["id_address_invoice"];
				// $postrequest_order["id_cart"]=$value["id_cart"];
				// $postrequest_order["id_currency"]=$value["id_currency"];
				// $postrequest_order["id_lang"]=$value["id_lang"];
				// $postrequest_order["id_carrier"]=$value["id_carrier"];
				// $postrequest_order["current_state"]=$value["current_state"];
				// $postrequest_order["module"]=$value["module"];
				// $postrequest_order["invoice_number"]=$value["invoice_number"];
				// $postrequest_order["invoice_date"]=$value["invoice_date"];

				// $postrequest_order["delivery_number"]=$value["delivery_number"];
				// $postrequest_order["delivery_date"]=$value["delivery_date"];

				// $postrequest_order["valid"]=$value["valid"];
				// $postrequest_order["date_add"]=$value["date_add"];
				// $postrequest_order["date_upd"]=$value["date_upd"];
				// $postrequest_order["shipping_number"]=$value["shipping_number"];

				// $postrequest_order["id_shop_group"]=$value["id_shop_group"];
				// $postrequest_order["id_shop"]=$value["id_shop"];
				// $postrequest_order["secure_key"]=$value["secure_key"];
				// $postrequest_order["payment"]=$value["payment"];
				// $postrequest_order["recyclable"]=$value["recyclable"];
				// $postrequest_order["gift"]=$value["gift"];
				// $postrequest_order["gift_message"]=$value["gift_message"];
				// $postrequest_order["mobile_theme"]=$value["mobile_theme"];
				// $postrequest_order["total_discounts"]=$value["total_discounts"];

				// $postrequest_order["total_discounts_tax_incl"]=$value["total_discounts_tax_incl"];
				// $postrequest_order["total_discounts_tax_excl"]=$value["total_discounts_tax_excl"];
				// $postrequest_order["total_paid"]=$value["total_paid"];
				// $postrequest_order["total_paid_tax_incl"]=$value["total_paid_tax_incl"];
				// $postrequest_order["total_paid_tax_excl"]=$value["total_paid_tax_excl"];
				// $postrequest_order["total_paid_real"]=$value["total_paid_real"];
				// $postrequest_order["total_products"]=$value["total_products"];
				// $postrequest_order["total_products_wt"]=$value["total_products_wt"];
				// $postrequest_order["total_shipping"]=$value["total_shipping"];
				// $postrequest_order["total_shipping_tax_incl"]=$value["total_shipping_tax_incl"];
				// $postrequest_order["total_shipping_tax_excl"]=$value["total_shipping_tax_excl"];
				// $postrequest_order["carrier_tax_rate"]=$value["carrier_tax_rate"];
				// $postrequest_order["total_wrapping"]=$value["total_wrapping"];
				// $postrequest_order["totalAmount"]=$value["total_wrapping_tax_incl"];
				// $postrequest_order["netAmount"]=$value["total_wrapping_tax_excl"];

				// $postrequest_order["round_mode"]=$value["round_mode"];
				// $postrequest_order["round_type"]=$value["round_type"];
				// $postrequest_order["conversion_rate"]=$value["conversion_rate"];
				// $postrequest_order["reference"]=$value["reference"];
				// $postrequest_order["status"]=1;
				//$postrequest_order["associations"]=$value["associations"];
				
				
				
				$product_data =$value["associations"]['order_rows'][0];
				
				$product_id =$product_data['id'];
				$product_description =$product_data['product_name'];
				$product_quantity =$product_data['product_quantity'];
				$product_discountPercent =$value["total_discounts"];
				$productCode =$product_data['product_reference'];
				$product_totalAmount =$product_data['unit_price_tax_incl'];
				$product_netAmount =$product_data['unit_price_tax_excl'];
				
				$postrequest_order["outgoingInvoiceLines"]=array(
					array(
					"lineType"=>0,
					"productCode" =>$product_id,
					"description" =>$product_description,
					"quantity" =>$product_quantity,
					"discountPercent" =>$product_discountPercent,
					"unitOfMeasure" =>"",
					"totalAmount" =>$product_totalAmount,
					"netAmount" =>$product_netAmount,
					"sortOrder" =>0,
					"vatReturnSpecification" =>0,
					"unitPrice" =>$product_netAmount
						)
					);
			
				
				
				//$unit_price_tax_incl =$product_data['unit_price_tax_incl'];
				//unit_price_tax_incl =$product_data['unit_price_tax_incl'];

                $id_customer =$value['id_customer'];
				$reference =$value['reference'];


				$curl = curl_init();

					curl_setopt_array($curl, array(
						CURLOPT_URL => $prestashoplink."/index.php?fc=module&module=prestapros_apiconnector&controller=customerlist&pid='$id_customer'",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "GET",
						CURLOPT_HTTPHEADER => array(
							"cache-control: no-cache",
						
						),
						));

					$customer_response = curl_exec($curl);
					$err = curl_error($curl);

					curl_close($curl);

						if ($err)
						{
							echo "cURL Error #:" . $err;
							die();
						} 
						else
						{
							$customer_getresponse=json_decode($customer_response,true);
						}
						
						//customer delivery address
						
				
//echo $prestashoplink."/index.php?fc=module&module=prestapros_apiconnector&controller=deliveryaddresslist&pid='$id_address_delivery'";
						$curl = curl_init();

						curl_setopt_array($curl, array(
						CURLOPT_URL => $prestashoplink."/index.php?fc=module&module=prestapros_apiconnector&controller=deliveryaddresslist&pid='$id_address_delivery'",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "GET",
						CURLOPT_HTTPHEADER => array(
							"cache-control: no-cache",
						
						),
						));

					$deliver_address_response = curl_exec($curl);
					$err = curl_error($curl);

					curl_close($curl);

						if ($err)
						{
							echo "cURL Error #:" . $err;
							die();
						} 
						else
						{
							$city_customer='';
							$postcode_customer='';
							$phone_mobile_customer='';
							$address1_customer='';
							$address2_customer='';
						
							$customer_deliver_address_getresponse=json_decode($deliver_address_response,true);
							
							$city_customer=$customer_deliver_address_getresponse['address']['city'];
							$postcode_customer=$customer_deliver_address_getresponse['address']['postcode'];
							$phone_mobile_customer=$customer_deliver_address_getresponse['address']['phone_mobile'];
							$address1_customer=$customer_deliver_address_getresponse['address']['address1'];
							$address2_customer=$customer_deliver_address_getresponse['address']['address2'];
						}
						
					//print_r($customer_deliver_address_getresponse);
					//die("fgdfgf");

                $select_customer="select * from go_customers where customer_id='".$id_customer."'";
				$query_customer=mysqli_query($conn,$select_customer);

				if(mysqli_num_rows($query_customer)>0)
				{
					$customer_id_get  =mysqli_fetch_assoc($query_customer);
					
					
					
					$id_customer =$customer_id_get['go_customer_id'];
					
					$custom_response_for_order =getcustomer($accesstoken,$id_customer);
					
					
					foreach($custom_response_for_order as $customer_getresponse_hwe)
					{



						$postrequest_order["currencyCode"]=$customer_getresponse_hwe['currencyCode'];
						$postrequest_order["customerCode"]=$custom_response_for_order['data']['code'];
						$postrequest_order["customerEmail"]=$customer_getresponse_hwe['email'];
						$postrequest_order["totalAmount"]=$value["total_shipping_tax_incl"];
						$postrequest_order["netAmount"]=$value["total_shipping_tax_excl"];
						$postrequest_order["status"]=0;
						$postrequest_order["customerReference"]=$custom_response_for_order['data']['code'];
						
						$get_street_addresses =$customer_getresponse_hwe["streetAddresses"];
						$get_street_address =$customer_getresponse_hwe["streetAddress"];
						
						if($get_street_address['zipCode'] != $postcode_customer)
						{
						
							foreach($get_street_addresses as $key => $get_street_address_hwe)
							{
								$data_zipcode=$get_street_address_hwe['zipCode'];							
							}
							
							if(!in_array($postcode_customer,$data_zipcode))
								{
									$count_hwe =count($data_zipcode);
									$key =$count_hwe +1;
								    $custom_response_for_order['data']['streetAddresses'][$key]=Array
									(
										"city" => $city_customer,
										"zipCode" => $postcode_customer,
										"address1" => $address1_customer,
										"address2"=> $address2_customer,
										"countryCode" => "NO",
										"lastChanged" => $getcustomer_create_date,
										"isPrimary" => true
									);
								   
									
								}
						
						
								
						}
					
						
					}
					//echo json_encode($custom_response_for_order_hwe);
					$updated = updatecustomer($accesstoken,$custom_response_for_order['data']);
					$get_address=$updated['data']['streetAddresses'];
					foreach($get_address as $key => $get_address_hwe)
					{
					
						if($get_address_hwe['zipCode'] == $postcode_customer)
						{
						
							$postrequest_order["deliveryAddressId"]=$get_address_hwe['id'];	
						}						
					}
					
					
					
					$order_response = createorder($accesstoken,$postrequest_order);
					print_r($order_response);
						
				}
				else
				{
					
					
	
					foreach($customer_getresponse as $customer_getresponse_hwe)
					{
			
						$customer_create_date =strtotime($customer_getresponse_hwe['date_add']);
						$getcustomer_create_date =date("Y-m-d",strtotime($customer_create_date)).'T'.date("h:i:s",strtotime($customer_create_date)).' +00:00';
						$name =$customer_getresponse_hwe['firstname'].' '.$customer_getresponse_hwe['lastname'];

								$postrequest["invoiceDeliveryType"] = 1;
								$postrequest["isVatFree"] =false;
								$postrequest["invoiceEmailAddress"] =$customer_getresponse_hwe['email'];
								$postrequest["invoiceEmailAddressCC"] ="";
								$postrequest["useFactoring"] = false;
								$postrequest["sendReminders"] = 1;
								$postrequest["doNotAddLatePaymentFees"] = "";
								$postrequest["doNotAddLatePaymentInterest"] =""; 
								$postrequest["reminderEmailAddress"] = "";
								$postrequest["transferToDebtCollectionAgency"] =1;
								$postrequest["customerCreatedDate"] = $getcustomer_create_date;
								$postrequest["useInvoiceFee"] = 1;
								$postrequest["name"] = $name;
								$postrequest["legalName"] =$name;
								$postrequest["websiteUrl"] = $customer_getresponse_hwe['website'];
								$postrequest["since"] = date("Y-m-d",strtotime($customer_create_date));
								$postrequest["isPerson"] = false;
								$postrequest["code"] = $reference;
								$postrequest["mailAddress"] = Array
									(
										"city" => "",
										"zipCode" => "",
										"address1" => "",
										"countryCode" => "NO",
										"lastChanged" => $getcustomer_create_date,
										"isPrimary" => false
									);

								$postrequest["streetAddress"] = Array
									(
										"city" => $city_customer,
										"zipCode" => $postcode_customer,
										"address1" => $address1_customer,
										"address2"=> $address2_customer,
										"countryCode" => "NO",
										"lastChanged" => $getcustomer_create_date,
										"isPrimary" => true
									);

								$postrequest["streetAddresses"] = Array
									(
									);
								$postrequest["emailAddress"] = $customer_getresponse_hwe['email'];
								$postrequest["isArchived"] = false;
								$postrequest["lastChanged"] = $getcustomer_create_date;
								$postrequest["createdDate"] = $getcustomer_create_date;
								$postrequest["contactGroups"] = Array
									(
									);

								$postrequest["subledgerNumberSeriesId"] = 00000000-0000-0000-0000-000000000000;
								$postrequest["reportInternationalId"] = false;
								$postrequest["internationalIdType"] = 0;
								

						$customer_response = createcustomer($accesstoken,$postrequest);
					
						
						if($customer_response['data']['id'])
						{
							$gocustomerid=$customer_response['data']['id'];

							$postrequest_order["currencyCode"]=$customer_response['data']['currencyCode'];
							$postrequest_order["customerCode"]=$customer_response['data']['code'];
							$postrequest_order["customerEmail"]=$customer_response['data']['email'];
							$postrequest_order["totalAmount"]=$value["total_shipping_tax_incl"];
							$postrequest_order["netAmount"]=$value["total_shipping_tax_excl"];
							$postrequest_order["status"]=0;
							$postrequest_order["customerReference"]=$customer_response['data']['code'];
							$postrequest_order["deliveryAddressId"]=$customer_response['data']['streetAddress']['id'];
							

						}
						
					}

				}
				
							$insert="insert into go_customers set customer_id='$id_customer',go_customer_id='$gocustomerid'";
							mysqli_query($conn,$insert);

                            $order_response = createorder($accesstoken,$postrequest_order);
					
							if($order_response['data']['id'])
							{
								$goorderid=$order_response['data']['id'];
	
								echo $insert="insert into `order` set order_id='$id_order',go_order_id='$goorderid'";
								mysqli_query($conn,$insert);
							}

			}
		}
		
?>