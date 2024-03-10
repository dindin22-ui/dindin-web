<?php
/**
 * CronController Controller
 *
 */
//if (!isset($_SESSION)) { session_start(); }

class CronController extends CController
{
	
	public function init()
	{		
		 $name=Yii::app()->functions->getOptionAdmin('website_title');
		 if (!empty($name)){		 	
		 	 Yii::app()->name = $name;
		 }	
		 		 
		 // set website timezone
		 $website_timezone=Yii::app()->functions->getOptionAdmin("website_timezone");		 		 
		 if (!empty($website_timezone)){		 	
		 	Yii::app()->timeZone=$website_timezone;
		 }		 				 
	}
	
	public function actionIndex()
	{
		echo "CONTROLLER INDEX";
	}

	public function actionDoordashWebhook(){
        $doordash_api_mode = Yii::app()->functions->getOptionAdmin("drive_api_mode");
        $doordash_api_key = $doordash_api_mode=='sandbox'?Yii::app()->functions->getOptionAdmin("drive_api_key_sandbox"):Yii::app()->functions->getOptionAdmin("drive_api_key_live");
        header('Authorization: Bearer '.$doordash_api_key);
        header('Content-Type: application/json');
        if($json = json_decode(file_get_contents("php://input"), true)) {
            print_r($json);
            $data = $json;
        } else {
            print_r($_POST);
            $data = $_POST;
        }
        $data = json_encode($data);
        FunctionsV3::m_log('Doordash Webhook Start: '.date('Y-m-d H:i'));
        FunctionsV3::m_log($data);

    }
	public function actionProcessBroacast()
	{
	   define('LOCK_SUFFIX', '.lock');
		
		if(($pid = cronHelper::lock()) !== FALSE) {			
			if(isset($_GET['debug'])){echo 'cron running';}
			
			$this->ProcessBroacast();
			sleep(1); // Cron job code for demonstration
	
			cronHelper::unlock();
	    } else {	    	
	    	if(isset($_GET['debug'])){echo "CRON LOCK";}
	    }
	}
	
	public function actionUberEatsOrder(){
// 	 define('YII_ENABLE_ER// ROR_HANDLER', true);
 define('YII_ENABLE_EXCEPTION_HANDLER', true);
 ini_set("display_errors",true);
//  	
		$db_ext=new DbExt;
		
		   
		$stmt="SELECT * FROM
		{{merchant}}
		WHERE
		uber_eats_store_id != ''
		";
//		FunctionsV3::m_log('Uber Eats Cron Start: '.date('Y-m-d H:i'));
		if ( $merchants=$db_ext->rst($stmt)){
			$store_order_read_token = FunctionsV3::getUberEatsToken();
			// FunctionsV3::m_log('Uber Eats Cron Complete Orders Read Token: '.$store_order_read_token);
			
			foreach($merchants as $merchant){
				$store_id = $merchant['uber_eats_store_id'];
//				FunctionsV3::m_log('Uber Eats Cron Start Store Id: '.$store_id);
				
				$orders = FunctionsV3::getUberEatsOrders($store_id,$store_order_read_token);
//				FunctionsV3::m_log('Uber Eats Cron Complete Orders Response: '.$orders);
				
				$orders = json_decode($orders);
				if(isset($orders->orders)){
					$orders = $orders->orders;
					foreach($orders as $order){
						$order_id = $order->id;
//						FunctionsV3::m_log('Uber Eats Cron Start Order Id: '.$store_id);
						
						$placed_at = $order->placed_at;
						$merchant_id = $merchant['merchant_id'];
						$params = array();
		
		$order_details_json = FunctionsV3::getUberEatsOrderDetails($order_id,$store_order_read_token);
//		FunctionsV3::m_log('Uber Eats Cron Complete Order Details Response: '.$order_details_json);
		
		$order_details = json_decode($order_details_json,true);
        $merchant_info = array();
        $delivery_details = array();
//         $order_details = array();
        $customer_details = array();
        $order_item = array();
        $order_id = '';
        $db_order_id = '';
        $subtotal = '';
        $client_info = array();
        $tax = '';
        $total = '';
        $tip = '';
        $confirm_link = ''; 
// 		echo '<pre>';
// 		print_r($order_details);
		$order_id = $order_details['id'];
		$stmt="SELECT * FROM {{order}} WHERE delivery_service_order_number = '".$order_id."' LIMIT 0,1";
		$res=$db_ext->rst($stmt);
		if(count($res) > 0){
			$existing_order  = $res[0];
		}
		$client_info_name = $order_details['eater']['first_name']." ".$order_details['eater']['last_name'];
		$client_info_phone = $order_details['eater']['phone'];
		$client_info = array('name'=>$client_info_name,'phone'=>$client_info_phone);
		$params['order_id_token'] = FunctionsV3::generateOrderToken();
        $params['merchant_id'] = $merchant['merchant_id'];
        $params['delivery_service_client_details'] = json_encode($client_info);
        $cart_items = $order_details['cart']['items'];
        $j = 0;
        foreach($cart_items as $cart_item){
        	$price = $cart_item['price']['total_price']['formatted_amount'];
        	$price = str_replace('$','',$price);
        	$order_item[$j]['item_name'] = $cart_item['title'];
        	$order_item[$j]['quantity'] = $cart_item['quantity'];
        	$order_item[$j]['price'] = $price;
        	$instructions = $cart_item['selected_modifier_groups'];
        	if(count($instructions) > 0){
        		$instruction_text = 'Instructions: ';
        		foreach($instructions as $instruction){
        			$title = $instruction['title'];
        			$instruction_selected_items = $instruction['selected_items'];
        			$instruction_text .= $title.'   <br/>';
        			foreach($instruction_selected_items as $instruction_selected_item){
        				$title = $instruction_selected_item['title'];
        				$quantity = $instruction_selected_item['quantity'];
        				$price = $instruction_selected_item['price']['total_price']['formatted_amount'];
        				
        				$instruction_text .= $title." ".$quantity." (".$price.") <br/>";
        			}
        		}
        		$order_item[$j]['instructions'] = $instruction_text;
        	}
        	$j++;
        }
        
        $trans_type = $order_details['type'];
        $d_date = $order_details['placed_at'];
        
        if($trans_type == 'DELIVERY_BY_UBER' || $trans_type == 'DELIVERY_BY_RESTAURANT'){
        	$trans_type = 'delivery';
        }else if($trans_type == 'PICK_UP'){
        	if(isset($order_details['placed_at'])){
        		$d_date = $order_details['placed_at'];
        	}
        	$trans_type = 'pickup';
        }else{
        	$trans_type = 'dinein';
        }
        $d_date = explode('T',$d_date);
        $delivery_date = $d_date[0];
        $time = explode(':',$d_date[1]);
        $delivery_time = $time[0].':'.$time[1];
        if(isset($order_details['payment']['charges']['total'])){
        	$total = $order_details['payment']['charges']['total']['amount'];
        	$total =$total/100;
        }
        
        if(isset($order_details['payment']['charges']['sub_total'])){
        	$subtotal = $order_details['payment']['charges']['sub_total']['amount'];
        	$subtotal = $subtotal/100;
        }
        $tax = 0.0;
        if(isset($order_details['payment']['charges']['tax'])){
        	$tax = $order_details['payment']['charges']['tax']['amount'];
        	$tax = $tax/100;
        }
        
        // if(isset($order_details['payment']['charges']['total'])){
//         	$total = $order_details['payment']['charges']['total'];
//         	$total = $total/100;
//         }
        $ins = ucwords(str_replace("_"," ",$order_details['type']));
        $params['uber_eats_complete_order_details'] = $order_details_json;
        $params['delivery_service_order_details'] = json_encode($order_item);
        $params['delivery_service_order_number'] = $order_id;
        $params['delivery_service_type'] = 'ubereats';
        $params['delivery_service_special_instructions'] = $ins;
        $params['delivery_instruction'] = $ins;
        $params['json_details'] = json_encode($order_item);
        $params['trans_type'] = $trans_type;
        $params['sub_total'] = round($subtotal,2);
        $params['tax'] = round( $tax,2);
         $params['confirm_link'] = '';                          
         if($tax != '0.00'){
         	$params['taxable_total'] = round($total,2);
         }
//         $params['total_w_tax'] = round($total,2);
         $params['total_w_tax'] = round($subtotal,2);
         $params['cart_tip_value'] = $tip;
         $params['status'] = 'Uber Eats';
         $params['delivery_date'] = $delivery_date;
          $params['delivery_time'] = $delivery_time;
          $params['request_from'] = 'ubereats';
          $order_id = null;
          if(isset($existing_order['order_id'])){
          		$db_ext->updateData("{{order}}",$params,'order_id',$existing_order['order_id']);
          		$db_order_id = $order_id = $existing_order['order_id'];
				$params['date_modified'] = date('Y-m-d G:i:s');
	 	  }else{
	 	  		$params['merchantapp_viewed'] = 0;
				$params['date_modified'] = date('Y-m-d G:i:s');
				$params['date_created'] = date('Y-m-d G:i:s');
				$params['admin_viewed'] = 0;
				$db_ext->insertData("{{order}}",$params);
				$db_order_id = $order_id = Yii::app()->db->getLastInsertID();
           }
//		  FunctionsV3::m_log('Uber Eats Cron Order Id Created on Dindin: '.$order_id);
//         print_r($params);
		   FunctionsV3::callAddons($order_id);
		   
		   $token_eats = FunctionsV3::getUberEatsToken('eats.order');
//		   FunctionsV3::m_log('Uber Eats Order Token: '.$token_eats);
		  
		   $response = FunctionsV3::acceptUberEatsOrder($order_details['id'],$token_eats);
//		   FunctionsV3::m_log('Uber Eats Auto Accept Order Response'.$response);
					}
				}
			}
		}
//		FunctionsV3::m_log('Uber Eats Order Ended: '.date('Y-m-d H:i'));
	}
	
	public function actionFoodOrder(){
	   require ('/home/orderdindin/public_html/vendor/autoload.php');
           require (Yii::app()->basePath.'/components/simple_html_dom.php');
            $client = new Google_Client();
            $fromEmail = 'foodorders@omnitech.pro';
            $client->useApplicationDefaultCredentials();
            $client->setSubject($fromEmail);
            $client->setApplicationName("Dindin");
            $client->setDeveloperKey("AIzaSyDilU59Xvhkhqvlo7WUYYD92j_20PYiLe8");
            $client->setAuthConfig(Yii::app()->basePath.'/components/food-order-298409-830a24669dd4.json');
            $client->setScopes(["https://mail.google.com/",
                "https://www.googleapis.com/auth/gmail.compose",
                "https://www.googleapis.com/auth/gmail.modify",
                "https://www.googleapis.com/auth/gmail.send",
                "https://www.googleapis.com/auth/gmail.readonly"]);
            $client->setAccessType('offline'); //not sure this is necessary for this type of call
            $service = new Google_Service_Gmail($client);
            $opt_param = array();
            $messages = array();
            $success = true;
            $opt_param['q'] = "is:unread";  //this will get us only unread messages
			$opt_param['maxResults'] = 20;  //this limits the amount of unread messages returned
			$opt_param['labelIds'] =  'INBOX';
			$user_id = 0;
            try {
                $messagesResponse = $service->users_messages->listUsersMessages($fromEmail, $opt_param);
                if ($messagesResponse->getMessages()) {
                    $messages = array_merge($messages, $messagesResponse->getMessages());
                    if ($success) {
                        foreach ($messages as $message) {
                            $q['format'] = 'raw';

							$msg = $service->users_messages->get('me', $message->getId(),$q);
							
                            $data = $msg['raw'];                     
							$data_raw = $msg['raw'];
							$msgAtt = $service->users_messages->get('me', $message['id']);
            				$message_parts = $msgAtt->getPayload()->getParts();
							// $attachmentObj = $service->users_messages_attachments->get($, $messageId, $attachmentId);
							 // $data = $attachmentObj->getData(); 
							$status = 'grubhub';
							echo '<pre>';
                            $merchant_info = array();
                            $delivery_details = array();
                            $order_details = array();
                            $customer_details = array();
							$order_item = array();
							$order_id = '';
							$db_order_id = '';
							$subtotal = '';
							$client_info = array();
							$tax = '';
							$total = '';
							$tip = '';
							$confirm_link = ''; 
							$DbExt=new DbExt;
							foreach($message_parts as $message_part){
								$attachmentId = '';
								if(isset($message_part['body']->attachmentId)){
									$attachmentId = $message_part['body']->attachmentId; 
								}else if(isset($message_part['parts'][1]['body']->attachmentId)){
									$attachmentId =  $message_part['parts'][1]['body']->attachmentId;
								}
								if($attachmentId !== ''){
									// $attachmentId =  $message_part['parts'][1]['body']->attachmentId;
									$attachmentObj = $service->users_messages_attachments->get('me', $message->getId(), $attachmentId);
									 $dataa = $attachmentObj->getData();
									 $dataa = str_replace(array('-', '_'), array('+', '/'),$dataa); 
									 $bin = base64_decode( $dataa);
									// $bin = strtr($bin, array('-' => '+', '_' => '/'));
									unlink('file.pdf');
									$myfile = fopen("file.pdf", "w+");;
									fwrite($myfile, $bin);
									fclose($myfile);
									$parser = new \Smalot\PdfParser\Parser();
									$pdf    = $parser->parseFile('file.pdf');
	// echo 'test';
	// $text = (new Spatie\PdfToText\Pdf())
    // ->setPdf('file.pdf')
    // ->text();
	// echo $text;exit;


									$text = $pdf->getText();
									$order_details = explode(PHP_EOL,$text);
// 									print_r($order_details);exit;
									$trans_type = 'Delivery';
									if(count($order_details) > 0){
										$status = 'doordash';
										for($i = 0; $i < count($order_details); $i++){
											if($i == 0){
												if ( strpos(trim($order_details[0]), 'Order Number') !== false)  {
													$merchant_name = trim($order_details[6]);
												}else{
													$merchant_info = explode('Placed on',$order_details[$i]);
												
													$merchant_name = trim($merchant_info[0]);
												}
												$merchant =yii::app()->functions->getMerchantInfoByName($merchant_name);
												print_r($merchant);	

											}
											if ( strpos(trim($order_details[$i]), 'Order Number') !== false)  {
												$order_number = explode(':',$order_details[$i]);
												$order_id = trim($order_number[1]);
												$stmt="SELECT * FROM
														{{order}}
														WHERE
														delivery_service_order_number = '".$order_id."'
														LIMIT 0,1
														";
												$res=$DbExt->rst($stmt);
												if(count($res) > 0){
													$existing_order  = $res[0];
												}	
											}
											if ( strpos(trim($order_details[$i]), 'Tip') !== false)  {
												$tip_price = explode('$',$order_details[$i]);
												// print_r($tip_price);
												$tip = $tip_price[1];	
											} 
											if ( strpos(trim($order_details[$i]), '+ Tax') !== false)  {
												$tax_price = explode('$',$order_details[$i]);
												// print_r($tax_price);
												$tax = $tax_price[1];	
											} 
											if ( strpos(trim($order_details[$i]), 'Customer') !== false && strpos(trim($order_details[$i]), 'Order Size') !== false)  {
												$i = $i+1;
												$client_info['name'] = $order_details[$i]; 
												$i = $i+1;
												$client_info['phone'] = $order_details[$i];
												$i = $i+1;
												$del_time = explode('at',$order_details[$i]);
												$del_time = $del_time[1];
												$del_time = explode(' ',trim($del_time));
												$am_pm = $del_time[1];
												$del_time = explode(':',$del_time[0]);
												$hour = $del_time[0];
												$minute = $del_time[1];
												if($am_pm == 'PM'){
													$hour = $hour+12;
												}
												$minute = (int)$minute;
												if($minute < 10){
													$minute = '0'.$minute;
												}


												$i = $i+1;
// print_r($del_time);
												$delivery_date = date('Y-m-d',strtotime($order_details[$i]));
												$delivery_time = $hour.':'.$minute;
												$delivery_time_info = $delivery_date.' '.$delivery_time;
												// $delivery_time_info = date('Y-m-d G:i',strtotime($delivery_time_info));	
												$delivery_time_info = explode(' ',$delivery_time_info);
												$delivery_date = $delivery_time_info[0];
												$delivery_time = $delivery_time_info[1];
											}
											if ( strpos(trim($order_details[$i]), 'Qty.') !== false)  {
												$i = $i+1;
												$j = 0;
												for( ;$i <  count($order_details);$i++){
													$page2 = explode('Placed on',$order_details[$i]);
													if ( strpos(trim($order_details[$i]), 'Placed on') !== false || strpos(trim($order_details[$i]), 'For any urgent order adjustment') !== false)  {
														continue;
													}
													
													$price = 0;
													if ( strpos(trim($order_details[$i]), '~ End of Order ~') !== false)  {
														$i = $i+1;
														$subtotal_price = explode('$',$order_details[$i]);
														$subtotal = $subtotal_price[1];
														// $i = $i+1;
														
														// $i = $i+1;
													}
													
													if ( strpos(trim($order_details[$i]), 'Total') !== false)  {
														$total_price = explode('$',$order_details[$i]);
														// print_r($total_price);
														$total = $total_price[1];
														break;	
													}
													$instruction = '';
													$new_line = true;
													
													$order_info = explode('x',$order_details[$i]);
													if(count($order_info) == 2 || count($order_info) == 3){
														if(count($order_info) == 3){
															$order_info[1] = $order_info[1].'x'.$order_info[2];
														}
														$qty = $order_info[0];
														$order_info = explode('$',$order_info[1]);
														$item_details = trim($order_info[0]);
														$item_details = explode('(in 	)',$item_details);
														$item_name = trim($item_details[0]);
														// print_r($order_info);
														if ( strpos(trim($order_details[$i]), '(+ $') !== false)  {
														}else{
															if(isset($order_info[2])){
																$price = $order_info[2];
															}
														}	
														$order_item[$j]['item_name'] = $item_name .'( in '.trim($item_details[1]).')';
														$order_item[$j]['quantity'] = $qty;
														if($price == 0){
															for($k = $i; $k < count($order_details)-1; $k++){
																$break_loop = false;
																if ( strpos(trim($order_details[$k]), '• ') !== false || strpos(trim($order_details[$k]), '\"') !== false)  {
																	$instruction = $instruction.'<br/>'.$order_details[$k];
																	if(strpos(trim($order_details[$k]), '• Special instructions') !== false){
																		$instruction = $instruction.'<br/>'.$order_details[$k+1];	
																	}
																	// continue;
																}
																if ( strpos(trim($order_details[$k]), '(+ $') !== false)  {
																}else{
																	if ( strpos(trim($order_details[$k]), '$') !== false)  {
																		$order_infoo = explode('$',$order_details[$k]);
																		// print_r($order_infoo);
																		$price = trim($order_infoo[count($order_infoo)-1]);
																		$break_loop = true;
																	}
																}

																if($break_loop){
																	$i = $k;
																	$order_item[$j]['price'] = trim($price);
																	$instruction = str_replace('• ','',$instruction);

																	$instruction = str_replace('"','',$instruction);

																	$order_item[$j]['instructions'] = $instruction;
																	break;
																}
															}
														}else{
															$order_item[$j]['price'] = trim($price);
														}
													}	
													$j = $j+1;
												}
											}
										}
									}
									// $text = explode('');
								}
									// $attachmentIds[] = $message_part[body]['attachmentId']; 
							}
							// print_r($attachmentIds);
							// echo '</pre>'; 
							// exit;
							// echo $status;
						if($data != ''){	
							$data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data)); 
							//from php.net/manual/es/function.base64-decode.php#118244
							$data = htmlspecialchars_decode(imap_qprint($data));
							$str = urldecode($data);
							$str = utf8_decode($data);
// echo $str;
							// $data = strstr($data,'<html>');
							// $data = htmlspecialchars_decode(strstr($data,'</html>',true));
							$myfile = fopen("email.html", "w+");
							if ($status == 'doordash') {
                                fwrite($myfile, $str);
							}else{
                                fwrite($myfile, $data);
                            }
							fclose($myfile);
							$html   =   file_get_html('email.html');
                            if (!empty($html)) {
								// echo $html;
                                foreach ($html->find("a") as $key=>$val) {
                                    if (strpos(trim($html), 'Click here') !== false) {
                                        $text = strip_tags($val->innertext);
                                        $confirm_link = $val->href;
                                        if ($status == 'doordash') {
											$link = explode('',$confirm_link);
                                            if (count($link) == 2) {
                                                // print_r($link);
                                                $confirm_link = trim($link[0]).'=16'.trim($link[1]);
                                            }
										// 	$link = explode('?ts ',$confirm_link);
										// 	if(count($link) > 0)
										// 		$confirm_link = trim($link[0]).'?ts=16'.trim($link[1]);
										// 	// print_r($link);
												
                                        //     // echo $confirm_link = str_replace('%', '=', $confirm_link);
                                        }
                                    }
                                    // print_r($val);
                                    // echo $val->href;
                                    // echo '\n';
                                    // echo strip_tags($val->innertext);
                                    // echo '\n';
                                }
                            }
							unlink('email.html');
						}

						if($status == 'grubhub'){
							// print_r($data_raw);
							$data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data_raw)); 
                            //from php.net/manual/es/function.base64-decode.php#118244
                            $data = htmlspecialchars_decode(imap_qprint($data));
                            // $data = strstr($data,'<html>');
                            // $data = strstr($data,'</html>');
                        //    echo $data;
//							$data = strstr($data,'<html>');
//							$data = htmlspecialchars_decode(strstr($data,'</html>',true));
							$DOM = new DOMDocument;
							$DOM->loadHTML($data);
							// print_r($data);
							// print_r($aTag);exit;
                            $items = $DOM->getElementsByTagName('table');
							// print_r($items);
//                            $order_summary = $DOM->getElementsByClass('orderSummary__body');
                            $return = array();
                            $i = 0;
							// print_r($items);
                            foreach ($items as $node) {
                                $tr = $node->childNodes;
                                $str = array();
                                foreach ($tr as $trelement) {
                                    if (count(explode('  ',trim($trelement->nodeValue))) > 1) {
                                        if ($i == 4) {
                                            // print_r();
                                            $merchant_info = explode('  ', trim($trelement->nodeValue));
                                            // print_r($merchant_info);
                                        }
                                        if ($i == 7) {
                                            $delivery_details = explode('  ', trim($trelement->nodeValue));
                                            // echo $i.' <br/>';
                                            // print_r($delivery_details);
                                        }
                                        if ($i == 9) {
                                            $order_details = explode('  ', trim($trelement->nodeValue));
                                        }
                                        if ($i == 10 || $i == 12) {
                                            $customer_details = explode('  ', trim($trelement->nodeValue));
                                        }
                                        $str[] = $trelement->nodeValue;
                                    }
                                }
                                if(trim($str[0]) == "SCHEDULED ORDER:  CONFIRM NOW"){

                                }else {
                                    $return[] = $str;//tdrows($node->childNodes);
                                    $i++;
                                }
//                                $return[] = $str;//tdrows($node->childNodes);
//                                $i++;
                            }
							// exit;
//                            $delivery = explode('  ',trim($return[7][0]));
                            
                                echo '<pre>';
							   if(count($return) == 0){
								    
									$data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data_raw)); 
									//from php.net/manual/es/function.base64-decode.php#118244
									$data = htmlspecialchars_decode(imap_qprint($data));	
									$DOM = new DOMDocument;
									$DOM->loadHTML($data);
									// print_r($data);
									// print_r($aTag);exit;
									$items = $DOM->getElementsByTagName('table');
									// print_r($items);
		//                            $order_summary = $DOM->getElementsByClass('orderSummary__body');
									$return = array();
									$i = 0;
									// print_r($items);
									foreach ($items as $node) {
										$tr = $node->childNodes;
										$str = array();
										foreach ($tr as $trelement) {
											if (count(explode('  ',trim($trelement->nodeValue))) > 1) {
												if ($i == 4) {
													// print_r();
													$merchant_info = explode('  ', trim($trelement->nodeValue));
													// print_r($merchant_info);
												}
												if ($i == 7) {
													$delivery_details = explode('  ', trim($trelement->nodeValue));
													// echo $i.' <br/>';
													// print_r($delivery_details);
												}
												if ($i == 9) {
													$order_details = explode('  ', trim($trelement->nodeValue));
												}
												if ($i == 10 || $i == 12) {
													$customer_details = explode('  ', trim($trelement->nodeValue));
												}
												$str[] = $trelement->nodeValue;
											}
										}
										$return[] = $str;//tdrows($node->childNodes);
										$i++;
									}
							   }
                            //    print_r($return);
							//    exit;
                                    foreach ($delivery_details as $key => $value) {
                                        if (empty(trim($value))) {
                                            unset($delivery_details[$key]);
                                        }
                                    }
                                    foreach($merchant_info as $key => $value){          
                                        if (empty(trim($value))) {
                                            unset($merchant_info[$key]);
                                        }
									}
                                    foreach ($order_details as $key => $value) {
                                        if (empty(trim($value))) {
                                            unset($order_details[$key]);
                                        }
                                    }
                                    foreach ($customer_details as $key => $value) {
                                        if (empty(trim($value))) {
                                            unset($customer_details[$key]);
                                        }
                                    }
                                //    print_r($merchant_info); 
									$merchant_info = array_values($merchant_info);
                                    $delivery_details = array_values($delivery_details);
                                    $order_details = array_values($order_details);
                                    $customer_details = array_values($customer_details);
                                //    print_r($delivery_details);
                            if(count($merchant_info) > 0){  
//                                if(trim($delivery_details[0]) == 'DELIVERY'){
                                    
                                    echo $merchant_name = trim($merchant_info[0]);
									echo '<br/>';
                                    $merchant =yii::app()->functions->getMerchantInfoByName($merchant_name);
                                    $order_id = explode('#',$merchant_info[4]);
                                    $order_id = $order_id[1];
                                    $stmt="SELECT * FROM
                                            {{order}}
                                            WHERE
                                            delivery_service_order_number = '".$order_id."'
                                            LIMIT 0,1
                                            ";
                                    $res=$DbExt->rst($stmt);
                                    if(count($res) > 0){
                                        $existing_order  = $res[0];
                                    }
//                                    print_r($delivery_details);
                                    if(trim($delivery_details[0]) == 'ORDER ADJUSTMENT' ||  trim($delivery_details[0]) == 'SCHEDULED ORDER:'){
                                        $delivery_details = $order_details;
                                        $order_details = array();
                                        $order_details = explode('  ',trim($return[11][1]));
                                        
                                        foreach($order_details as $key => $value)          
                                            if(empty($value)) 
                                                unset($order_details[$key]);
                                        
                                        $order_details = array_values($order_details);
                                    }
                                    $j = 0;
//                                    print_r($order_details);
                                    for($i = 0; $i < count($order_details)-1; $i++){
                                        $quantity = '';
                                        $item_name = '';
                                        $instructions = array();
                                        if( is_numeric(trim($order_details[$i])) ){
                                           $quantity = $order_details[$i];
                                           $before_price = false;
                                            $price = '';
                                            $break_loop = false;
                                           for($k = $i+1; $k < count($order_details)-1;$k++){
                                                if( is_numeric(trim($order_details[$k])) ){
                                                    $break_loop = true;
                                                    break;
                                                }else if($before_price == false){
                                                    $price_val = explode('$',$order_details[$k]);
                                                    if(count($price_val) == 2){
                                                        $price = $price_val[1];
                                                        $before_price= true;
                                                    }else
                                                        $item_name = $item_name.' '.$order_details[$k];
                                                }else if($before_price == true){
                                                    if(trim($order_details[$k]) == 'Subtotal' || trim($order_details[$k]) == 'Order Adjustments' || is_numeric(trim($order_details[$k])) ){
                                                         $break_loop = true;
                                                         break;
                                                    }else{
                                                        if(trim($order_details[$k]) == 'Include napkins and utensils?' || trim($order_details[$k]) == 'YES' || trim($order_details[$k]) == 'NO'){
                                                            
                                                        }else{
                                                            $instructions[] = $order_details[$k];
                                                        }
                                                    }
                                                }
                                               
                                           }
                                           if(count($instructions) > 0){
                                            $instruction_html = '';
                                            foreach ($instructions as $instruction){
                                                $instruction_html .= $instruction.' <br/>'; 
                                            }
                                           }else{
                                               $instruction_html = '';
                                           }
                                           
                                           $item_name_arr = explode('Instructions:',$item_name);
                                           
                                           if(count($item_name_arr) == 2){
//                                           print_r($item_name_arr);
                                                $order_item[$j]['item_name'] = $item_name_arr[0];
                                                 if(count($instructions) > 0){
                                                    $order_item[$j]['instructions'] = 'Instructions: '.$item_name_arr[1].'<br/>' .$instruction_html;
                                                 }else{
                                                    $order_item[$j]['instructions'] = 'Instructions: '.$item_name_arr[1];
                                                 }
                                           }else{
                                               $order_item[$j]['item_name'] = $item_name;
                                                if(count($instructions) > 0){
                                                    $order_item[$j]['instructions'] =  $instruction_html;
                                                }
                                           }
                                           if(trim($order_details[$i]) )
                                           $order_item[$j]['quantity'] = $quantity;
                                           $order_item[$j]['price'] = $price;
                                           $j = $j+1;
                                        }
                                        if(trim($order_details[$i]) == 'Subtotal'){
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $subtotal = $price_val[1];
                                            }
                                        }
                                        if(trim($order_details[$i]) == 'Tax'){
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $tax = $price_val[1];
                                            }
                                        }
                                        if(trim($order_details[$i]) == 'Tip'){
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $tip = $price_val[1];
                                            }
										}
										
                                        if ( strpos(trim($order_details[$i]), 'TOTAL') !== false)  {
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $total = $price_val[1];
                                            }
                                        }
                                    }
//                                    print_r($order_item);
                                    $instruction = '';
                                    $after_phone = false;
                                    $trans_type = '';
                                    $c = 0;
//                                    print_r($customer_details);
                                    foreach($customer_details as $customer ){
                                        if($after_phone){
                                            $instruction = $instruction.' '.$customer;
                                        }else{
                                            if(trim(strtolower($delivery_details[0])) == 'delivery' || trim(strtolower($delivery_details[0])) == 'grubhub delivery') {


                                                if ( strpos(trim($customer), 'Deliver to') !== false)  {
                                                    $customer_detail = explode(':',$customer);
                                                    if($customer_detail[1] !== null){
                                                        $trans_type = 'delivery';
                                                        $client_info['name'] = $customer_detail[1];
                                                    }
                                                }
                                            }
                                            
                                            if(trim(strtolower($delivery_details[0])) == 'pickup'){
                                                if ( strpos(trim($customer), 'Pickup') !== false)  {
                                                    $customer_detail = explode(':',$customer);
                                                    if($customer_detail[1] !== null){
                                                        $trans_type = 'pickup';
                                                        $client_info['name'] = $customer_detail[1];
                                                        $client_info['name'] = $customer_details[$c+1];
                                                    }
                                                }
                                            }
                                            
                                            
                                        
                                            preg_match_all('!\d+!', $customer, $matches);
                                            if(count($matches[0]) > 1 || strpos(trim($customer), '+') !== false){
                                                $client_info['phone'] = $customer; 
                                                $after_phone = true;
                                            }
                                            $client_info[] = $customer;
                                        }
                                        $c++;
                                        
                                    }
                                    $delivery_date = '';
                                    $delivery_time = '';
                                    if(isset($delivery_details[2])){
                                        $delivery_date_time = strtotime($delivery_details[2]);
                                        if(date('Y',$delivery_date_time) == '1969'){
                                            $date_time = explode(',',$delivery_details[2]);
                                            $dateee = $date_time[0].', '.date('Y');
                                            $delivery_date = date('Y-m-d',strtotime($dateee));
                                                if(strpos(trim(strtolower($date_time[1])), 'am') !== false){
                                                    $delivery_time = str_replace('am','',$date_time[1]);
                                                }else if(strpos(trim(strtolower($date_time[1])), 'pm') !== false){
                                                    $delivery_time = str_replace('pm','',$date_time[1]);
                                                }
                                            }else {
                                            $delivery_date = date('Y-m-d', $delivery_date_time);
                                            $delivery_time = date('H:i', $delivery_date_time);
                                        }
                                    }
//                                    if(isset($delivery_details[2])){
//                                        $delivery_date_time = strtotime($delivery_details[2]);
//                                        $delivery_date = date('Y-m-d',$delivery_date_time);
//                                        $delivery_time = date('H:i',$delivery_date_time);
//									}
								
							}
						}
						if(!empty($subtotal)){
                                    $params = array();
                                    if($merchant['merchant_id'] !== null){
										// $mt_timezone=Yii::app()->functions->getOption("merchant_timezone",$merchant['merchant_id']);
										// if (!empty($mt_timezone)){       	 	
										// 	Yii::app()->timeZone=$mt_timezone;
										// }
										// date_default_timezone_set($mt_timezone);
										$params['order_id_token'] = FunctionsV3::generateOrderToken();
                                        $params['merchant_id'] = $merchant['merchant_id'];
                                        $params['delivery_service_client_details'] = json_encode($client_info);
                                        $params['delivery_service_order_details'] = json_encode($order_item);
                                        $params['delivery_service_order_number'] = $order_id;
                                        $params['delivery_service_type'] = $status;
                                        $params['delivery_service_special_instructions'] = $instruction;
                                        $params['delivery_instruction'] = $instruction;
                                        $params['json_details'] = json_encode($order_item);
                                        $params['trans_type'] = $trans_type;
                                        $params['sub_total'] = $subtotal;
                                        $params['tax'] = $tax;
                                        if ($confirm_link !== '') {
											$params['confirm_link'] = html_entity_decode($confirm_link);
                                        }else{
                                            $params['confirm_link'] = '';
                                        }                             
										if($tax != '0.00'){
                                            $params['taxable_total'] = $total;
                                        }	
										
                                        $params['total_w_tax'] = $total;
                                        $params['cart_tip_value'] = $tip;
                                        $params['status'] = ucfirst($status);
                                        $params['delivery_date'] = $delivery_date;
                                        $params['delivery_time'] = $delivery_time;
                                        $params['request_from'] = $status;
                                        $order_id = null;
                                        if(isset($existing_order['order_id'])){
                                            $DbExt->updateData("{{order}}",$params,'order_id',$existing_order['order_id']);
                                            $db_order_id = $order_id = $existing_order['order_id'];
											$params['date_modified'] = date('Y-m-d G:i:s');

                                        }else{
											$params['merchantapp_viewed'] = 0;
											$params['date_modified'] = date('Y-m-d G:i:s');
											$params['date_created'] = date('Y-m-d G:i:s');
											$params['admin_viewed'] = 0;
                                            $DbExt->insertData("{{order}}",$params);
                                           	$db_order_id = $order_id = Yii::app()->db->getLastInsertID();
                                        }
//                                         print_r($params);
                                        FunctionsV3::callAddons($order_id);
									}
							}
//                                    echo '<b>Instructions: </b>'.$instruction.'<br/>';
//                                    echo '<b>Subtotal</b>'.$subtotal.'<br/>';
//                                    echo '<b>Tax</b>'.$tax.'<br/>';
//                                    echo '<b>Total</b>'.$total.'<br/>';
//                                    echo '<h3>Order</h3>';
//                                    print_r($order_details);
//                                    echo '<h3>Customer</h3>';
                                    // print_r($customer_details);
                                    echo '</pre>';
                                
//                            }
								$service->users_messages->trash('me', $message->getId()); 
                            if($db_order_id != ''){
								$service->users_messages->trash('me', $message->getId()); 
							}
                        }
                    }
                }
            } catch (Exception $e) {
                echo "<h2>you don't have email access</h2>";
                echo "<br />";
                echo $e;
                $success = false;
            }
	}
	public function actionFoodOrderTest(){
	   require ('/home/orderdindin/public_html/vendor/autoload.php');
           require (Yii::app()->basePath.'/components/simple_html_dom.php');
            $client = new Google_Client();
            $fromEmail = 'foodorders@omnitech.pro';
            $client->useApplicationDefaultCredentials();
            $client->setSubject($fromEmail);
            $client->setApplicationName("Dindin");
            $client->setDeveloperKey("AIzaSyDilU59Xvhkhqvlo7WUYYD92j_20PYiLe8");
            $client->setAuthConfig(Yii::app()->basePath.'/components/food-order-298409-830a24669dd4.json');
            $client->setScopes(["https://mail.google.com/",
                "https://www.googleapis.com/auth/gmail.compose",
                "https://www.googleapis.com/auth/gmail.modify",
                "https://www.googleapis.com/auth/gmail.send",
                "https://www.googleapis.com/auth/gmail.readonly"]);
            $client->setAccessType('offline'); //not sure this is necessary for this type of call
            $service = new Google_Service_Gmail($client);
            $opt_param = array();
            $messages = array();
//             $success = true;
//             $opt_param['q'] = "is:unread";  //this will get us only unread messages
// 			$opt_param['maxResults'] = 2;  //this limits the amount of unread messages returned
// 			$opt_param['labelIds'] =  'IMPORTANT';
			$user_id = 0;
			 
            try {
                $messagesResponse = $service->users_messages->listUsersMessages($fromEmail, $opt_param);
                echo "<pre>"; print_r($messagesResponse); exit('hu8huh');
                if ($messagesResponse->getMessages()) {
                    $messages = array_merge($messages, $messagesResponse->getMessages());
                    if ($success) {
                        foreach ($messages as $message) {
                            $q['format'] = 'raw';

							$msg = $service->users_messages->get('me', $message->getId(),$q);
							
                            $data = $msg['raw'];
							$data_raw = $msg['raw'];
							$msgAtt = $service->users_messages->get('me', $message['id']);
            				$message_parts = $msgAtt->getPayload()->getParts();
							// $attachmentObj = $service->users_messages_attachments->get($, $messageId, $attachmentId);
							 // $data = $attachmentObj->getData(); 
							$status = 'grubhub';
							echo '<pre>';
                            $merchant_info = array();
                            $delivery_details = array();
                            $order_details = array();
                            $customer_details = array();
							$order_item = array();
							$order_id = '';
							$db_order_id = '';
							$subtotal = '';
							$client_info = array();
							$tax = '';
							$total = '';
							$tip = '';
							$confirm_link = ''; 
							$DbExt=new DbExt;
							foreach($message_parts as $message_part){
								$attachmentId = '';
								if(isset($message_part['body']->attachmentId)){
									$attachmentId = $message_part['body']->attachmentId; 
								}else if(isset($message_part['parts'][1]['body']->attachmentId)){
									$attachmentId =  $message_part['parts'][1]['body']->attachmentId;
								}
								if($attachmentId !== ''){
									// $attachmentId =  $message_part['parts'][1]['body']->attachmentId;
									$attachmentObj = $service->users_messages_attachments->get('me', $message->getId(), $attachmentId);
									 $dataa = $attachmentObj->getData();
									 $dataa = str_replace(array('-', '_'), array('+', '/'),$dataa); 
									 $bin = base64_decode( $dataa);
									// $bin = strtr($bin, array('-' => '+', '_' => '/'));
									unlink('file.pdf');
									$myfile = fopen("file.pdf", "w+");;
									fwrite($myfile, $bin);
									fclose($myfile);
									$parser = new \Smalot\PdfParser\Parser();
									$pdf    = $parser->parseFile('file.pdf');
	// echo 'test';
	// $text = (new Spatie\PdfToText\Pdf())
    // ->setPdf('file.pdf')
    // ->text();
	// echo $text;exit;


									$text = $pdf->getText();
									$order_details = explode(PHP_EOL,$text);
									$trans_type = 'Delivery';
									if(count($order_details) > 0){
										$status = 'doordash';
										for($i = 0; $i < count($order_details); $i++){
											if($i == 0){
												if ( strpos(trim($order_details[0]), 'Order Number') !== false)  {
													$merchant_name = trim($order_details[6]);
												}else{
													$merchant_info = explode('Placed on',$order_details[$i]);
												
													$merchant_name = trim($merchant_info[0]);
												}
												$merchant =yii::app()->functions->getMerchantInfoByName($merchant_name);
												// print_r($merchant);	

											}
											if ( strpos(trim($order_details[$i]), 'Order Number') !== false)  {
												$order_number = explode(':',$order_details[$i]);
												$order_id = trim($order_number[1]);
												$stmt="SELECT * FROM
														{{order}}
														WHERE
														delivery_service_order_number = '".$order_id."'
														LIMIT 0,1
														";
												$res=$DbExt->rst($stmt);
												if(count($res) > 0){
													$existing_order  = $res[0];
												}	
											}
											if ( strpos(trim($order_details[$i]), 'Tip') !== false)  {
												$tip_price = explode('$',$order_details[$i]);
												// print_r($tip_price);
												$tip = $tip_price[1];	
											} 
											if ( strpos(trim($order_details[$i]), '+ Tax') !== false)  {
												$tax_price = explode('$',$order_details[$i]);
												// print_r($tax_price);
												$tax = $tax_price[1];	
											} 
											if ( strpos(trim($order_details[$i]), 'Customer') !== false && strpos(trim($order_details[$i]), 'Order Size') !== false)  {
												$i = $i+1;
												$client_info['name'] = $order_details[$i]; 
												$i = $i+1;
												$client_info['phone'] = $order_details[$i];
												$i = $i+1;
												$del_time = explode('at',$order_details[$i]);
print_r($del_time);
												$del_time = $del_time[1];
												$del_time = explode(' ',trim($del_time));
												$am_pm = $del_time[1];
												$del_time = explode(':',$del_time[0]);
												$hour = $del_time[0];
												$minute = $del_time[1];
												if($am_pm == 'PM'){
													$hour = $hour+12;
												}
												$minute = (int)$minute;
												if($minute < 10){
													$minute = '0'.$minute;
												}


												$i = $i+1;
												$delivery_date = date('Y-m-d',strtotime($order_details[$i]));
												$delivery_time = $hour.':'.$minute;
												$delivery_time_info = $delivery_date.' '.$delivery_time;
												// $delivery_time_info = date('Y-m-d G:i',strtotime($delivery_time_info));	
												$delivery_time_info = explode(' ',$delivery_time_info);
												$delivery_date = $delivery_time_info[0];
												$delivery_time = $delivery_time_info[1];
											}
											if ( strpos(trim($order_details[$i]), 'Qty.') !== false)  {
												$i = $i+1;
												$j = 0;
												for( ;$i <  count($order_details);$i++){
													$page2 = explode('Placed on',$order_details[$i]);
													if ( strpos(trim($order_details[$i]), 'Placed on') !== false || strpos(trim($order_details[$i]), 'For any urgent order adjustment') !== false)  {
														continue;
													}
													
													$price = 0;
													if ( strpos(trim($order_details[$i]), '~ End of Order ~') !== false)  {
														$i = $i+1;
														$subtotal_price = explode('$',$order_details[$i]);
														$subtotal = $subtotal_price[1];
														// $i = $i+1;
														
														// $i = $i+1;
													}
													
													if ( strpos(trim($order_details[$i]), 'Total') !== false)  {
														$total_price = explode('$',$order_details[$i]);
														// print_r($total_price);
														$total = $total_price[1];
														break;	
													}
													$instruction = '';
													$new_line = true;
													
													$order_info = explode('x',$order_details[$i]);
													if(count($order_info) == 2 || count($order_info) == 3){
														if(count($order_info) == 3){
															$order_info[1] = $order_info[1].'x'.$order_info[2];
														}
														$qty = $order_info[0];
														$order_info = explode('$',$order_info[1]);
														$item_details = trim($order_info[0]);
														$item_details = explode('(in 	)',$item_details);
														$item_name = trim($item_details[0]);
														// print_r($order_info);
														if ( strpos(trim($order_details[$i]), '(+ $') !== false)  {
														}else{
															if(isset($order_info[2])){
																$price = $order_info[2];
															}
														}	
														$order_item[$j]['item_name'] = $item_name .'( in '.trim($item_details[1]).')';
														$order_item[$j]['quantity'] = $qty;
														if($price == 0){
															for($k = $i; $k < count($order_details)-1; $k++){
																$break_loop = false;
																if ( strpos(trim($order_details[$k]), '• ') !== false || strpos(trim($order_details[$k]), '\"') !== false)  {
																	$instruction = $instruction.'<br/>'.$order_details[$k];
																	if(strpos(trim($order_details[$k]), '• Special instructions') !== false){
																		$instruction = $instruction.'<br/>'.$order_details[$k+1];	
																	}
																	// continue;
																}
																if ( strpos(trim($order_details[$k]), '(+ $') !== false)  {
																}else{
																	if ( strpos(trim($order_details[$k]), '$') !== false)  {
																		$order_infoo = explode('$',$order_details[$k]);
																		// print_r($order_infoo);
																		$price = trim($order_infoo[count($order_infoo)-1]);
																		$break_loop = true;
																	}
																}

																if($break_loop){
																	$i = $k;
																	$order_item[$j]['price'] = trim($price);
																	$instruction = str_replace('• ','',$instruction);

																	$instruction = str_replace('"','',$instruction);

																	$order_item[$j]['instructions'] = $instruction;
																	break;
																}
															}
														}else{
															$order_item[$j]['price'] = trim($price);
														}
													}	
													$j = $j+1;
												}
											}
										}
									}
									// $text = explode('');
								}
									// $attachmentIds[] = $message_part[body]['attachmentId']; 
							}
							// print_r($attachmentIds);
							// echo '</pre>'; 
							// exit;
							// echo $status;
						if($data != ''){	
							$data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data)); 
							//from php.net/manual/es/function.base64-decode.php#118244
							$data = htmlspecialchars_decode(imap_qprint($data));
							$str = urldecode($data);
							$str = utf8_decode($data);
// echo $str;
							// $data = strstr($data,'<html>');
							// $data = htmlspecialchars_decode(strstr($data,'</html>',true));
							$myfile = fopen("email.html", "w+");
							if ($status == 'doordash') {
                                fwrite($myfile, $str);
							}else{
                                fwrite($myfile, $data);
                            }
							fclose($myfile);
							$html   =   file_get_html('email.html');
                            if (!empty($html)) {
                                foreach ($html->find("a") as $key=>$val) {
                                    if (strpos(trim($html), 'Click here') !== false) {
                                        $text = strip_tags($val->innertext);
                                        $confirm_link = $val->href;
                                        if ($status == 'doordash') {
											$link = explode('',$confirm_link);
                                            if (count($link) == 2) {
                                                // print_r($link);
                                                $confirm_link = trim($link[0]).'=16'.trim($link[1]);
                                            }
										// 	$link = explode('?ts ',$confirm_link);
										// 	if(count($link) > 0)
										// 		$confirm_link = trim($link[0]).'?ts=16'.trim($link[1]);
										// 	// print_r($link);
												
                                        //     // echo $confirm_link = str_replace('%', '=', $confirm_link);
                                        }
                                    }
                                    // print_r($val);
                                    // echo $val->href;
                                    // echo '\n';
                                    // echo strip_tags($val->innertext);
                                    // echo '\n';
                                }
                            }
							unlink('email.html');
						}

						if($status == 'grubhub'){
							$data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data_raw)); 
                            //from php.net/manual/es/function.base64-decode.php#118244
                            $data = htmlspecialchars_decode(imap_qprint($data));
                            // $data = strstr($data,'<html>');
                            // $data = strstr($data,'</html>');
//                            echo $data;
//							$data = strstr($data,'<html>');
//							$data = htmlspecialchars_decode(strstr($data,'</html>',true));
							$DOM = new DOMDocument;
							$DOM->loadHTML($data);
							// print_r($data);
							// print_r($aTag);exit;
                            $items = $DOM->getElementsByTagName('table');
//							 print_r($items);
//                            $order_summary = $DOM->getElementsByClass('orderSummary__body');
                            $return = array();
                            $i = 0;
							// print_r($items);
                            foreach ($items as $node) {
                                $tr = $node->childNodes;
                                $str = array();
                                foreach ($tr as $trelement) {
                                    if (count(explode('  ',trim($trelement->nodeValue))) > 1) {
                                        if ($i == 4) {
                                            // print_r();
                                            $merchant_info = explode('  ', trim($trelement->nodeValue));
                                            // print_r($merchant_info);
                                        }
                                        if ($i == 7) {
                                            $delivery_details = explode('  ', trim($trelement->nodeValue));
                                            // echo $i.' <br/>';
                                            // print_r($delivery_details);
                                        }
                                        if ($i == 9) {
                                            $order_details = explode('  ', trim($trelement->nodeValue));
                                        }
                                        if ($i == 10 || $i == 12) {
                                            $customer_details = explode('  ', trim($trelement->nodeValue));
                                        }
                                        $str[] = $trelement->nodeValue;
                                    }
                                }
                                if(trim($str[0]) == "SCHEDULED ORDER:  CONFIRM NOW"){

                                }else {
                                    $return[] = $str;//tdrows($node->childNodes);
                                    $i++;
                                }
                            }
//                            print_r($return);
//							 exit;
//                            $delivery = explode('  ',trim($return[7][0]));
                            
                                echo '<pre>';
							   if(count($return) == 0){
								    
									$data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data_raw)); 
									//from php.net/manual/es/function.base64-decode.php#118244
									$data = htmlspecialchars_decode(imap_qprint($data));	
									$DOM = new DOMDocument;
									$DOM->loadHTML($data);
									// print_r($data);
									// print_r($aTag);exit;
									$items = $DOM->getElementsByTagName('table');
									// print_r($items);
		//                            $order_summary = $DOM->getElementsByClass('orderSummary__body');
									$return = array();
									$i = 0;
									// print_r($items);
									foreach ($items as $node) {
										$tr = $node->childNodes;
										$str = array();
										foreach ($tr as $trelement) {
											if (count(explode('  ',trim($trelement->nodeValue))) > 1) {
												if ($i == 4) {
													// print_r();
													$merchant_info = explode('  ', trim($trelement->nodeValue));
													// print_r($merchant_info);
												}
												if ($i == 7) {
													$delivery_details = explode('  ', trim($trelement->nodeValue));
													// echo $i.' <br/>';
													// print_r($delivery_details);
												}
												if ($i == 9) {
													$order_details = explode('  ', trim($trelement->nodeValue));
												}
												if ($i == 10 || $i == 12) {
													$customer_details = explode('  ', trim($trelement->nodeValue));
												}
												$str[] = $trelement->nodeValue;
											}
										}
										$return[] = $str;//tdrows($node->childNodes);
										$i++;
									}
							   }
                            //    print_r($return);
							//    exit;
                                    foreach ($delivery_details as $key => $value) {
                                        if (empty(trim($value))) {
                                            unset($delivery_details[$key]);
                                        }
                                    }
                                    foreach($merchant_info as $key => $value){          
                                        if (empty(trim($value))) {
                                            unset($merchant_info[$key]);
                                        }
									}
                                    foreach ($order_details as $key => $value) {
                                        if (empty(trim($value))) {
                                            unset($order_details[$key]);
                                        }
                                    }
                                    foreach ($customer_details as $key => $value) {
                                        if (empty(trim($value))) {
                                            unset($customer_details[$key]);
                                        }
                                    }
                                //    print_r($merchant_info); 
									$merchant_info = array_values($merchant_info);
                                    $delivery_details = array_values($delivery_details);
                                    $order_details = array_values($order_details);
                                    $customer_details = array_values($customer_details);
                                //    print_r($delivery_details);
                            if(count($merchant_info) > 0){  
//                                if(trim($delivery_details[0]) == 'DELIVERY'){
                                    
                                    echo $merchant_name = trim($merchant_info[0]);
									echo '<br/>';
                                    $merchant =yii::app()->functions->getMerchantInfoByName($merchant_name);
                                    $order_id = explode('#',$merchant_info[4]);
                                    $order_id = $order_id[1];
                                    $stmt="SELECT * FROM
                                            {{order}}
                                            WHERE
                                            delivery_service_order_number = '".$order_id."'
                                            LIMIT 0,1
                                            ";
                                    $res=$DbExt->rst($stmt);
                                    if(count($res) > 0){
                                        $existing_order  = $res[0];
                                    }
//                                    print_r($delivery_details);
                                    if(trim($delivery_details[0]) == 'ORDER ADJUSTMENT' ||  trim($delivery_details[0]) == 'SCHEDULED ORDER:'){
                                        $delivery_details = $order_details;
                                        $order_details = array();
                                        $order_details = explode('  ',trim($return[11][1]));
                                        
                                        foreach($order_details as $key => $value)          
                                            if(empty($value)) 
                                                unset($order_details[$key]);
                                        
                                        $order_details = array_values($order_details);
                                    }
                                    $j = 0;
//                                    print_r($order_details);
                                    for($i = 0; $i < count($order_details)-1; $i++){
                                        $quantity = '';
                                        $item_name = '';
                                        $instructions = array();
                                        if( is_numeric(trim($order_details[$i])) ){
                                           $quantity = $order_details[$i];
                                           $before_price = false;
                                            $price = '';
                                            $break_loop = false;
                                           for($k = $i+1; $k < count($order_details)-1;$k++){
                                                if( is_numeric(trim($order_details[$k])) ){
                                                    $break_loop = true;
                                                    break;
                                                }else if($before_price == false){
                                                    $price_val = explode('$',$order_details[$k]);
                                                    if(count($price_val) == 2){
                                                        $price = $price_val[1];
                                                        $before_price= true;
                                                    }else
                                                        $item_name = $item_name.' '.$order_details[$k];
                                                }else if($before_price == true){
                                                    if(trim($order_details[$k]) == 'Subtotal' || trim($order_details[$k]) == 'Order Adjustments' || is_numeric(trim($order_details[$k])) ){
                                                         $break_loop = true;
                                                         break;
                                                    }else{
                                                        if(trim($order_details[$k]) == 'Include napkins and utensils?' || trim($order_details[$k]) == 'YES' || trim($order_details[$k]) == 'NO'){
                                                            
                                                        }else{
                                                            $instructions[] = $order_details[$k];
                                                        }
                                                    }
                                                }
                                               
                                           }
                                           if(count($instructions) > 0){
                                            $instruction_html = '';
                                            foreach ($instructions as $instruction){
                                                $instruction_html .= $instruction.' <br/>'; 
                                            }
                                           }else{
                                               $instruction_html = '';
                                           }
                                           
                                           $item_name_arr = explode('Instructions:',$item_name);
                                           
                                           if(count($item_name_arr) == 2){
//                                           print_r($item_name_arr);
                                                $order_item[$j]['item_name'] = $item_name_arr[0];
                                                 if(count($instructions) > 0){
                                                    $order_item[$j]['instructions'] = 'Instructions: '.$item_name_arr[1].'<br/>' .$instruction_html;
                                                 }else{
                                                    $order_item[$j]['instructions'] = 'Instructions: '.$item_name_arr[1];
                                                 }
                                           }else{
                                               $order_item[$j]['item_name'] = $item_name;
                                                if(count($instructions) > 0){
                                                    $order_item[$j]['instructions'] =  $instruction_html;
                                                }
                                           }
                                           if(trim($order_details[$i]) )
                                           $order_item[$j]['quantity'] = $quantity;
                                           $order_item[$j]['price'] = $price;
                                           $j = $j+1;
                                        }
                                        if(trim($order_details[$i]) == 'Subtotal'){
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $subtotal = $price_val[1];
                                            }
                                        }
                                        if(trim($order_details[$i]) == 'Tax'){
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $tax = $price_val[1];
                                            }
                                        }
                                        if(trim($order_details[$i]) == 'Tip'){
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $tip = $price_val[1];
                                            }
										}
										
                                        if ( strpos(trim($order_details[$i]), 'TOTAL') !== false)  {
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $total = $price_val[1];
                                            }
                                        }
                                    }
//                                    print_r($order_item);
                                    $instruction = '';
                                    $after_phone = false;
                                    $trans_type = '';
                                    $c = 0;

                                    foreach($customer_details as $customer ){
                                        if($after_phone){
                                            $instruction = $instruction.' '.$customer;
                                        }else{

//                                            define('YII_ENABLE_EXCEPTION_HANDLER', true);
//                                            ini_set("display_errors",true);
                                            if(trim(strtolower($delivery_details[0])) == 'delivery' || trim(strtolower($delivery_details[0])) == 'grubhub delivery') {

//                                                echo trim(strtolower($delivery_details[0])).' ';
//                                            print_r($delivery_details);
                                                if ( strpos(trim($customer), 'Deliver to') !== false)  {
                                                    $customer_detail = explode(':',$customer);
                                                    if($customer_detail[1] !== null){
                                                        $trans_type = 'delivery';
                                                        $client_info['name'] = $customer_detail[1];
                                                    }
                                                }
                                            }
                                            
                                            if(trim(strtolower($delivery_details[0])) == 'pickup'){
                                                if ( strpos(trim($customer), 'Pickup') !== false)  {
                                                    $customer_detail = explode(':',$customer);
                                                    if($customer_detail[1] !== null){
                                                        $trans_type = 'pickup';
                                                        $client_info['name'] = $customer_detail[1];
                                                        $client_info['name'] = $customer_details[$c+1];
                                                    }
                                                }
                                            }
                                            
                                            
                                        
                                            preg_match_all('!\d+!', $customer, $matches);
                                            if(count($matches[0]) > 1 || strpos(trim($customer), '+') !== false){
                                                $client_info['phone'] = $customer; 
                                                $after_phone = true;
                                            }
                                            $client_info[] = $customer;
                                        }
                                        $c++;
                                        
                                    }
                                    $delivery_date = '';
                                    $delivery_time = '';
                                    if(isset($delivery_details[2])){
                                        $delivery_date_time = strtotime($delivery_details[2]);
                                        if(date('Y',$delivery_date_time) == '1969'){
                                            $date_time = explode(',',$delivery_details[2]);
                                            $dateee = $date_time[0].', '.date('Y');
                                            $delivery_date = date('Y-m-d',strtotime($dateee));
                                            if(strpos(trim(strtolower($date_time[1])), 'am') !== false){
                                                $delivery_time = str_replace('am','',$date_time[1]);
                                            }else if(strpos(trim(strtolower($date_time[1])), 'pm') !== false){
                                                $delivery_time = str_replace('pm','',$date_time[1]);
                                            }
                                        }else {
                                            $delivery_date = date('Y-m-d', $delivery_date_time);
                                            $delivery_time = date('H:i', $delivery_date_time);
                                        }
									}
								
							}
						}
						if(!empty($subtotal)){
                                    $params = array();
                                    if($merchant['merchant_id'] !== null){
										// $mt_timezone=Yii::app()->functions->getOption("merchant_timezone",$merchant['merchant_id']);
										// if (!empty($mt_timezone)){       	 	
										// 	Yii::app()->timeZone=$mt_timezone;
										// }
										// date_default_timezone_set($mt_timezone);
										$params['order_id_token'] = FunctionsV3::generateOrderToken();
                                        $params['merchant_id'] = 9;// $merchant['merchant_id'];
                                        $params['delivery_service_client_details'] = json_encode($client_info);
                                        $params['delivery_service_order_details'] = json_encode($order_item);
                                        $params['delivery_service_order_number'] = $order_id;
                                        $params['delivery_service_type'] = $status;
                                        $params['delivery_service_special_instructions'] = $instruction;
                                        $params['delivery_instruction'] = $instruction;
                                        $params['json_details'] = json_encode($order_item);
                                        $params['trans_type'] = $trans_type;
                                        $params['sub_total'] = $subtotal;
                                        $params['tax'] = $tax;
                                        if ($confirm_link !== '') {
											$params['confirm_link'] = html_entity_decode($confirm_link);
                                        }else{
                                            $params['confirm_link'] = '';
                                        }                             
										if($tax != '0.00'){
                                            $params['taxable_total'] = $total;
                                        }	
										
                                        $params['total_w_tax'] = $total;
                                        $params['cart_tip_value'] = $tip;
                                        $params['status'] = ucfirst($status);
                                        $params['delivery_date'] = $delivery_date;
                                        $params['delivery_time'] = $delivery_time;
                                        $params['request_from'] = $status;
                                        $order_id = null;
                                        if(isset($existing_order['order_id'])){
                                            $DbExt->updateData("{{order}}",$params,'order_id',$existing_order['order_id']);
                                            $db_order_id = $order_id = $existing_order['order_id'];
											$params['date_modified'] = date('Y-m-d G:i:s');

                                        }else{
											$params['merchantapp_viewed'] = 0;
											$params['date_modified'] = date('Y-m-d G:i:s');
											$params['date_created'] = date('Y-m-d G:i:s');
											$params['admin_viewed'] = 0;
                                            $DbExt->insertData("{{order}}",$params);
                                           	$db_order_id = $order_id = Yii::app()->db->getLastInsertID();
                                        }
                                        print_r($params);
                                        FunctionsV3::callAddons($order_id);
									}
							}
//                                    echo '<b>Instructions: </b>'.$instruction.'<br/>';
//                                    echo '<b>Subtotal</b>'.$subtotal.'<br/>';
//                                    echo '<b>Tax</b>'.$tax.'<br/>';
//                                    echo '<b>Total</b>'.$total.'<br/>';
//                                    echo '<h3>Order</h3>';
//                                    print_r($order_details);
//                                    echo '<h3>Customer</h3>';
                                    // print_r($customer_details);
                                    echo '</pre>';
                                
//                            }
								// $service->users_messages->trash('me', $message->getId()); 
                            if($db_order_id != ''){
								// $service->users_messages->trash('me', $message->getId()); 
							}
                        }
                    }
                }
            } catch (Exception $e) {
                echo "<h2>you don't have email access</h2>";
                echo "<br />";
                echo $e;
                exit('thu'); 
                $success = false;
            }
	}
	public function ProcessBroacast()
	{
		$cron=new CronFunctions;
		$db_ext=new DbExt;
		$stmt="SELECT * FROM
		{{sms_broadcast}}
		WHERE
		status IN ('pending')
		LIMIT 0,1
		";
		if ( $res=$db_ext->rst($stmt)){
			foreach ($res as $val) {
				if(isset($_GET['debug'])){dump($val);}
				if ( $val['send_to']==1 ){
					$cron->getAllCustomer($val);
				} elseif ( $val['send_to']==2){
					if(isset($_GET['debug'])){echo "Merchant customer";}
					$cron->getAllCustomerByMerchant($val);
				} else {
					if(isset($_GET['debug'])){echo "custom mobile";}
					$cron->customMobile($val);
				}
				$db_ext->updateData("{{sms_broadcast}}",
				  array('status'=>"process",'date_modified'=>FunctionsV3::dateNow()),
				  'broadcast_id',$val['broadcast_id']);
			}
		} else {
			if(isset($_GET['debug'])){
			   echo "<p>No records to process</p>";
			}
		}
	}	

	public function actionProcessSMS()
	{
	   define('LOCK_SUFFIX', '.locksms');
		
		if(($pid = cronHelper::lock()) !== FALSE) {			
			if(isset($_GET['debug'])){
			   echo 'cron running sms';
			}
			
			$this->ProcessSMS();
			sleep(1); // Cron job code for demonstration
	
			cronHelper::unlock();
	    } else {	    	
	    	if(isset($_GET['debug'])){
	    	   echo "CRON LOCK";
	    	}
	    }
	}
			
	public function actionProcessPayout()
	{		
		$db_ext=new DbExt;
		
		$paypal_client_id=yii::app()->functions->getOptionAdmin('wd_paypal_client_id');
		$paypal_client_secret=yii::app()->functions->getOptionAdmin('wd_paypal_client_secret');
		
		$paypal_config=Yii::app()->functions->getPaypalConnectionWithdrawal();
		//dump($paypal_config);
		$Paypal=new Paypal($paypal_config);
		$Paypal->debug=true;
		
		$website_title=yii::app()->functions->getOptionAdmin('website_title');
		
		$cron=new CronFunctions;		
		if ( $res=$cron->getPayoutToProcess()){
			if (is_array($res) && count($res)>=1){
				foreach ($res as $val) {
					$withdrawal_id=$val['withdrawal_id'];
					$api_raw_response='';
					$status_msg='';
					//dump($val);
					switch ($val['payment_method']){
						case "paypal":
							//dump("Process paypal");
							//if (!empty($paypal_client_id) && !empty($paypal_client_secret)){
							if (is_array($paypal_config) && count($paypal_config)>=1){
								if ( $val['account']!=""){
									
									$Paypal->params['RECEIVERTYPE']="EmailAddress";
									$Paypal->params['CURRENCYCODE']="USD";
									$Paypal->params['EMAILSUBJECT']="=You have a payment from ".$website_title;
									
									$Paypal->params['L_EMAIL0']=$val['account'];
									$Paypal->params['L_AMT0']=normalPrettyPrice($val['amount']);
									$Paypal->params['L_UNIQUEID0']=str_pad($val['withdrawal_id'],10,"0");																														
									if ( $pay_resp=$Paypal->payout()){
									    dump($pay_resp);
									    if ( $pay_resp['ACK']=="Success"){
									    	$status_msg='paid';		
									    	$api_raw_response=json_encode($pay_resp);
									    } else {
									    	$api_raw_response=json_encode($pay_resp);
									    	$status_msg=$pay_resp['L_LONGMESSAGE0'];
									    }
									} else $status_msg=$Paypal->getError();
								} else $status_msg=t("Paypal account is empty");
							} else $status_msg=t("Payout settings for paypal not yet set");
							break;
							
						case "bank":
							$status_msg='paid';
							break;	
					}
					
					echo "<h3>Update status</h3>";
					/*dump($api_raw_response);
					dump($status_msg);*/
					$params_update=array(
					  'date_process'=>FunctionsV3::dateNow(),
					  'api_raw_response'=>$api_raw_response,
					  'status'=>$status_msg
					);
					//dump($params_update);
					if ( $db_ext->updateData("{{withdrawal}}",$params_update,'withdrawal_id',$withdrawal_id)){
						//echo "<h2>Update ok</h2>";
					} //else echo "<h2>Update Failed</h2>";
					
					if ( $status_msg=="paid"){
						// send email
						$subject=yii::app()->functions->getOptionAdmin('wd_template_process_subject');
						if (empty($subject)){
	                        $subject=t("Your Request for Withdrawal has been Processed");
                        }
                        if ( $merchant_info=Yii::app()->functions->getMerchant($val['merchant_id'])){ 
                        	$merchant_email=$merchant_info['contact_email'];
                        	$tpl=yii::app()->functions->getOptionAdmin('wd_template_process');
                        	$tpl=smarty("merchant-name",$merchant_info['restaurant_name'],$tpl);
			                $tpl=smarty("payout-amount",standardPrettyFormat($val['amount']),$tpl);
			                $tpl=smarty("payment-method",$val['payment_method'],$tpl);
			                $tpl=smarty("acoount",$val['account'],$tpl);
                        	//dump($tpl);
                        	if(!empty($tpl)){
                        		sendEmail($merchant_email,'',$subject,$tpl);
                        	}
                        }	
					}
				}
			}
		} //else dump("No record to process");
	}
	
	public function actionFax()
	{
		$msg='';
		$send_fax_link='https://www.faxage.com/httpsfax.php';
		
		$db_ext=new DbExt;
		$stmt="SELECT * FROM
		{{fax_broadcast}}
		WHERE
		status='pending'
		AND faxno!=''		
		LIMIT 0,5
		";
		
		$fax_company=yii::app()->functions->getOptionAdmin("fax_company");
		$fax_username=yii::app()->functions->getOptionAdmin("fax_username");
		$fax_password=yii::app()->functions->getOptionAdmin("fax_password");
		
		/*dump("company: ".$fax_company);
		dump("username: ".$fax_username);
		dump("password: ".$fax_password);*/
		$notify_url=websiteUrl()."/cron/faxpostback/";
		
		if ( $res=$db_ext->rst($stmt)){			
			foreach ($res as $val) {
				//dump($val);				
				$jobid='';
				$record_id=$val['id'];
				$credit=Yii::app()->functions->getMerchantFaxCredit($val['merchant_id']);	    	
				//dump($credit);
				if ($credit>=1){
					$params="username=".$fax_username;
					$params.="&company=".$fax_company;
					$params.="&password=".$fax_password;
					$params.="&recipname=".$val['recipname'];
					$params.="&faxno=".$val['faxno'];
					$params.="&operation=sendfax";
					$params.="&faxurl=".$val['faxurl'];
					$params.="&url_notify=$notify_url";					
					//dump($params);
					if ( $response=Yii::app()->functions->Curl($send_fax_link,$params)){
						$msg=$response;
						if (preg_match("/JOBID/i", $response)) {
							$jobid=str_replace("JOBID:",'',$response);
							$jobid=trim($jobid);
						} else $jobid='';
					} else $msg="Invalid response";
				} else $msg=t("Zero credits");
				
				$params_update=array(
				 'status'=>"process",
				 'api_raw_response'=>$msg,
				 'date_process'=>FunctionsV3::dateNow(),
				 'jobid'=>$jobid
				);
				$db_ext->updateData("{{fax_broadcast}}",$params_update,'id',$record_id);
			} /*end foreach*/
		} //else $msg="NO records to process";
		
		//dump("Result: ".$msg);
	}
	
	public function actionFaxPostBack()
	{
		$data=$_REQUEST;
		dump($data);
		if ( $res=Yii::app()->functions->getFaxJobId($data['jobid'])){
			dump($res);
			$record_id=$res['id'];
			$params=array(
			 'status'=>$data['shortstatus'],
			 'api_raw_response'=>$data['longstatus'],
			 'date_postback'=>FunctionsV3::dateNow()
			);
			dump($params);
			$db_ext=new DbExt;
			$db_ext->updateData("{{fax_broadcast}}",$params,'jobid',$data['jobid']);
		}
	}
	
	public function actionSetMerchantExpired()
	{		
		Yii::app()->functions->updateMerchantExpired();
	}
	
	public function ProcessSMS()
	{
		$DbExt=new DbExt;
		$stmt="
		SELECT * FROM
		{{sms_broadcast_details}}
		WHERE
		status IN ('pending')
		ORDER BY id ASC
		LIMIT 0,10
		";		
		if ( $res=$DbExt->rst($stmt)){
			foreach ($res as $val) {
				if(isset($_GET['debug'])){
					dump($val);
				}
				Yii::app()->functions->sendSMS(trim($val['contact_phone']),$val['sms_message'],$val['id']);
			}
		} else {
			if(isset($_GET['debug'])){
				echo "no records";
			}
		}
		
	}
	
	public function actionProcessEmail()
	{
		$DbExt=new DbExt;
		$stmt="
		SELECT * FROM
		{{email_logs}}
		WHERE
		status IN ('pending')
		ORDER BY id ASC
		LIMIT 0,10
		";		
		if ( $res=$DbExt->rst($stmt)){
			foreach ($res as $val) {
				if(isset($_GET['debug'])){
					dump($val);					
				}
				
				$DbExt->updateData("{{email_logs}}",array(
				 'status'=>"processing"
				),'id',$val['id']);				
				
				sendEmail(
				  $val['email_address'],
				  $val['sender'],
				  $val['subject'],
				  $val['content'],
				  $val['id']
				);
			}
		} else {
			if(isset($_GET['debug'])){
				echo "no records";
			}
		}
	}
	
	public function actionMerchantExpired()
	{
		
		Yii::app()->functions->updateMerchantSponsored();
		Yii::app()->functions->updateMerchantExpired();
	}
	
	public function actionMerchantNearExpiration()
	{
		$lang=Yii::app()->language;
		$email_enabled=getOptionA("merchant_near_expiration_email");
		$sms_enabled=getOptionA("merchant_near_expiration_sms");
		$sender=getOptionA("global_admin_sender_email");
		
		if($email_enabled!=1 && $sms_enabled!=1){
			if(isset($_GET['debug'])){ echo "disabled"; }
			return ;
		}
		
		$days=getOptionA('merchant_near_expiration_day');
		if(empty($days)){
			$days=5;
		}
		$date=date("Y-m-d", strtotime("+$days day"));		
		$DbExt=new DbExt;
		$stmt="
		SELECT * FROM
		{{merchant}}
		WHERE
		membership_expired<".FunctionsV3::q($date)."
		AND status in ('active')
		AND is_commission ='1'
		";		
		if(isset($_GET['debug'])){
		   dump($stmt);
		}
		if ($res=$DbExt->rst($stmt)){
			if(isset($_GET['debug'])){
			  dump($res);
			}
			
			$tpl_orig=getOptionA("merchant_near_expiration_tpl_content_$lang");
			$subject_orig=getOptionA("merchant_near_expiration_tpl_subject_$lang");
		    $tpl_sms_orig=getOptionA("merchant_near_expiration_sms_content_$lang");
		    		    
			foreach ($res as $val) {
				
				$merchant_email=$val['contact_email'];
				
				$pattern=array(		    	   
		    	   'restaurant_name'=>'restaurant_name',	
		    	   'expiration_date'=>'membership_expired',
		    	   'sitename'=>getOptionA('website_title'),
		    	   'siteurl'=>websiteUrl(),	 		    	   
		    	);
		    	$tpl=FunctionsV3::replaceTemplateTags($tpl_orig,$pattern,$val); 
		    	$subject=FunctionsV3::replaceTemplateTags($subject_orig,$pattern,$val); 
		    	$tpl_sms=FunctionsV3::replaceTemplateTags($tpl_sms_orig,$pattern,$val); 		    	
				
		    	/*dump($subject);
		    	dump($tpl);*/
		    	//dump($tpl_sms);
		    	
				$params=array(
	    		  'email_address'=>$merchant_email,
	    		  'sender'=>$sender,
	    		  'subject'=>$subject,
	    		  'content'=>$tpl,
	    		  'date_created'=>FunctionsV3::dateNow(),
	    		  'ip_address'=>$_SERVER['REMOTE_ADDR'],
	    		  'module_type'=>'core'
	    		);	    		
	    		$DbExt->insertData("{{email_logs}}",$params); 
	    		
	    		$params=array(
	    		  'contact_phone'=>$val['contact_phone'],
	    		  'sms_message'=>$tpl_sms,
	    		  'date_created'=>FunctionsV3::dateNow(),
	    		  'ip_address'=>$_SERVER['REMOTE_ADDR']    		 
	    		);	    		
	    		$DbExt->insertData("{{sms_broadcast_details}}",$params); 				
			}
		} else {
			if(isset($_GET['debug'])){ echo "no records"; }
		}
		unset($DbExt);
		FunctionsV3::runCronEmail();
		FunctionsV3::runCronSMS();
	}
	
	public function actionIdleOrder()
	{
		$DbExt=new DbExt;
		$datenow=date("Y-m-d");
		$stmt="
		SELECT 
		a.order_id,
		a.merchant_id,
		a.date_created,
		b.restaurant_name
		
		FROM {{order}} a
		LEFT JOIN {{merchant}} b
		ON 
		a.merchant_id = b.merchant_id
		
		WHERE
		a.date_created LIKE '$datenow%'
		AND
		a.status = 'pending'
		AND
		a.critical = '1'
		ORDER BY a.order_id ASC
		limit 0,10
		";
		if(isset($_GET['debug'])){
		   dump($stmt);
		}		
		$idle_minutes=getOptionA('order_idle_admin_minutes');
		if(!is_numeric($idle_minutes)){			
			$idle_minutes=5;
		}
		
		if ($res=$DbExt->rst($stmt)){
			foreach ($res as $val) {	
			   if(isset($_GET['debug'])){
			  	  dump($val);
			   }
			   $critical=false;	
			   $time_1=date('Y-m-d g:i:s a');			
		       $time_2=date("Y-m-d g:i:s a",strtotime($val['date_created']));						
			   $time_diff=FunctionsV3::dateDifference($time_2,$time_1);	
			   		   
			   if (is_array($time_diff) && count($time_diff)>=1){			   	   
			   	   if ($time_diff['minutes']>$idle_minutes){
			   	   	  $critical=true;
			   	   }
			   	   if ($time_diff['hours']>=1){
			   	   	  $critical=true;
			   	   }
			   	   if ($time_diff['days']>=1){
			   	   	  $critical=true;
			   	   }
			   }
			   
			   if($critical){
			   	  $val['time_diff']=$time_diff;			   	  
			   	  $DbExt->updateData("{{order}}",array('critical'=>2),'order_id',$val['order_id']);
			   	  self::notiIdleAdmin($val);			   	  
			   }
			   
			}			
		} 
	}
	
	public static function notiIdleAdmin($data='')
	{
		$lang=Yii::app()->language; 
		$sender=getOptionA('global_admin_sender_email');
		
		$enabled=getOptionA('order_idle_to_admin_email');
		$email=getOptionA('order_idle_admin_email');
		if($enabled==true && !empty($email)){
			$tpl=getOptionA("order_idle_to_admin_tpl_content_$lang");
			$subject = getOptionA("order_idle_to_admin_tpl_subject_$lang");
									
			$data['idle_time']=$data['time_diff']['hours'].":".$data['time_diff']['minutes'].":".$data['time_diff']['seconds'];
			
			$pattern=array(		    	   	   	   
			   'order_id'=>'order_id',
	    	   'restaurant_name'=>'restaurant_name',	    	   
	    	   'sitename'=>getOptionA('website_title'),
	    	   'siteurl'=>websiteUrl(),	 		    	   
	    	   'idle_time'=>'idle_time'
	    	  );
	    	$tpl=FunctionsV3::replaceTemplateTags($tpl,$pattern,$data);
	    	$subject=FunctionsV3::replaceTemplateTags($subject,$pattern,$data);
									
			$DbExt=new DbExt();
			  $params=array(
			   'email_address'=>$email,
			   'sender'=>$sender,
			   'subject'=>$subject,
			   'content'=>$tpl,
			   'date_created'=>FunctionsV3::dateNow(),
			   'ip_address'=>$_SERVER['REMOTE_ADDR'],
			   'module_type'=>'core'
			  );	    			  
			  $DbExt->insertData("{{email_logs}}",$params);    	  
			  FunctionsV3::runCronEmail();
			
		}
	}
	
}/* END CLASS*/