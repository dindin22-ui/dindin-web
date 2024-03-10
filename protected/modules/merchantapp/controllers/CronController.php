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
	
	public function actionProcesspush()
	{
		$iOSPush=new iOSPush;
		$DbExt=new DbExt; 
		
		$push_server_key = getOptionA('merchantapp_push_server_key');		
		$channel = 'kmrs_merchant_channel';
		
		$stmt="SELECT * FROM
		{{mobile_merchant_pushlogs}}
		WHERE
		status='pending'
		ORDER BY id ASC
		LIMIT 0,10
		";
		if(isset($_GET['debug'])){
			dump($stmt);
		}
		if($res=$DbExt->rst($stmt)){		   
		   foreach ($res as $val) {		
		   	
		   	  if(isset($_GET['debug'])){
		   	     dump($val);
		   	  }
		   	  		   	  
		   	  $status=''; $json_response = array();
		   	  
		   	  $record_id=$val['id'];
		   	  $device_id = trim($val['device_id']);
		   	  $device_platform = strtolower($val['device_platform']);
		   	  
		   	  switch ($device_platform) {
		   	  	 case "android":
		   	  	 	
		   	  	 	$data = array(
					  'title'=>$val['push_title'],
					  'body'=>$val['push_message'],
					  'vibrate'	=> 1,			
		              'soundname'=> 'beep',
		              'android_channel_id'=>$channel,
		              'content-available'=>1,
		              'count'=>1,
		              'badge'=>1,
		              'push_type'=>$val['push_type'],
                        'push_link'=>$val['push_link']
					);					
					if(!empty($push_server_key)){
						 try {
						 	$json_response = MobileFCMPush::pushAndroid($data,$device_id,$push_server_key);						 	
						 	$status='process';
						 } catch (Exception $e) {
			                $status = 'Caught exception:'. $e->getMessage();
		                 }
					 } else $status = 'server key is empty';
		   	  	 	
		   	  	 	break;
		   	  	 	
		   	  	 case "ios":
		   	  	 	
		   	  	 	try {
						 $data = array( 
					      'title' =>$val['push_title'],
					      'body' => $val['push_message'],
					      'sound'=>'beep.wav',
					      'android_channel_id'=>$channel,
					      'badge'=>1,
					      'content-available'=>1,
					      'push_type'=>$val['push_type'],
                             'push_link'=>$val['push_link']
					    );					    					   
						$json_response = MobileFCMPush::pushIOS($data,$device_id,$push_server_key);
						$status='process';	
												
					} catch (Exception $e) {
						$status =  $e->getMessage();
					}		
						
		   	  	 	break;	
		   	  	 	
		   	  	 default:
		   	  	 	$status='invalid device platform';
		   	  	 	break;
		   	  }
		   	  
		   	  if(!empty($status)){
		   	  	$status=substr( strip_tags($status) ,0,255);
		   	  }
		   	  
			  $params_update=array(
			     'status'=>empty($status)?"uknown status":$status,
			     'date_process'=>FunctionsV3::dateNow(),
			     'json_response'=>json_encode($json_response)
			  );			  
			  if(isset($_GET['debug'])){
		   	     dump($params_update);
		   	  }
			  $DbExt->updateData('{{mobile_merchant_pushlogs}}',$params_update,'id',$record_id);			   			   
		   }
		}  else {
			if(isset($_GET['debug'])){echo "No records to process<br/>";}
		}
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
	
	public function actionFoodOrder(){
           require ('/home/dindinsi/public_html/devdindin/vendor/autoload.php');
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
            $opt_param['maxResults'] = 6; //this limits the amount of unread messages returned
            try {
                $messagesResponse = $service->users_messages->listUsersMessages($fromEmail, $opt_param);
                if ($messagesResponse->getMessages()) {
                    $messages = array_merge($messages, $messagesResponse->getMessages());
                    if ($success) {
                        foreach ($messages as $message) {
                            $q['format'] = 'raw';

                            $msg = $service->users_messages->get('me', $message->getId(),$q);
                            $data = $msg['raw'];
                            $data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data)); 
                            //from php.net/manual/es/function.base64-decode.php#118244
                            $data = imap_qprint($data);
                            $data = strstr($data,'<html>');
                            $data = htmlspecialchars_decode(strstr($data,'</html>',true));
//                            echo $data;
//                            
                            $DOM = new DOMDocument;
                            $DOM->loadHTML($data);
                            $items = $DOM->getElementsByTagName('table');
//                            $order_summary = $DOM->getElementsByClass('orderSummary__body');
                            $return = array();
                            $i = 0;
                            $merchant_info = array();
                            $delivery_details = array();
                            $order_details = array();
                            $customer_details = array();
                            foreach ($items as $node) {
                                $tr = $node->childNodes;
                                $str = array();
                                foreach ($tr as $trelement) {
                                    if($i == 4){
                                        $merchant_info = explode('  ',trim($trelement->nodeValue));
                                    }
                                    if($i == 7){
                                        $delivery_details = explode('  ',trim($trelement->nodeValue));
                                    }
                                    if($i == 9){
                                        $order_details = explode('  ',trim($trelement->nodeValue));
                                    }
                                    if($i == 10 || $i == 12){
                                        $customer_details = explode('  ',trim($trelement->nodeValue));
                                    }
                                    $str[] = $trelement->nodeValue;
                                }
                                $return[] = $str;//tdrows($node->childNodes);
                                $i++;
                            }
//                            $delivery = explode('  ',trim($return[7][0]));
                            
                                echo '<pre>';
                                
//                                print_r($return);
                                    foreach($delivery_details as $key => $value)          
                                        if(empty($value)) 
                                            unset($delivery_details[$key]); 
                                    foreach($merchant_info as $key => $value)          
                                        if(empty($value)) 
                                            unset($merchant_info[$key]); 
                                    foreach($order_details as $key => $value)          
                                        if(empty($value)) 
                                            unset($order_details[$key]);
                                        
                                    foreach($customer_details as $key => $value)          
                                        if(empty($value)) 
                                            unset($customer_details[$key]);
                                        
                                    $merchant_info = array_values($merchant_info);
                                    $delivery_details = array_values($delivery_details);
                                    $order_details = array_values($order_details);
                                    $customer_details = array_values($customer_details);
                            if(count($merchant_info) > 0){
//                                if(trim($delivery_details[0]) == 'DELIVERY'){
                                    $DbExt=new DbExt;
                                    
                                    $merchant_name = trim($merchant_info[0]);
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
                                    if(trim($delivery_details[0]) == 'ORDER ADJUSTMENT'){
                                        $delivery_details = $order_details;
                                        $order_details = array();
                                        $order_details = explode('  ',trim($return[11][1]));
                                        
                                        foreach($order_details as $key => $value)          
                                            if(empty($value)) 
                                                unset($order_details[$key]);
                                        
                                        $order_details = array_values($order_details);
                                    }
                                    $order_item = array();
                                    $j = 0;
                                    $subtotal = '';
                                    $tax = '';
                                    $total = '';
                                    $tip = '';
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
                                                        if(trim($order_details[$k]) == 'Include napkins and utensils?' || trim($order_details[$k]) == 'YES'){
                                                            
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
                                        if ( strpos(trim($order_details[$i], 'TOTAL') !== false) ) {
                                        }else{
                                            $price_val = explode('$',$order_details[$i+1]);
                                            if(count($price_val) == 2){
                                                $total = $price_val[1];
                                            }
                                        }
                                    }
//                                    print_r($order_item);
                                    $client = array();
                                    $instruction = '';
                                    $after_phone = false;
                                    $trans_type = '';
                                    $c = 0;
                                    foreach($customer_details as $customer ){
                                        if($after_phone){
                                            $instruction = $instruction.' '.$customer;
                                        }else{
                                            if(trim(strtolower($delivery_details[0])) == 'delivery'){
                                                
                                            
                                                if ( strpos(trim($customer, 'Deliver to') !== false) ) {

                                                }else{
                                                    $customer_detail = explode(':',$customer);
                                                    if($customer_detail[1] !== null){
                                                        $trans_type = 'delivery';
                                                        $client['name'] = $customer_detail[1];
                                                    }
                                                }
                                            }
                                            
                                            if(trim(strtolower($delivery_details[0])) == 'pickup'){
                                                if ( strpos(trim($customer, 'Pickup') !== false) ) {
                                                }else{
                                                    $customer_detail = explode(':',$customer);
                                                    if($customer_detail[1] !== null){
                                                        $trans_type = 'pickup';
                                                        $client['name'] = $customer_detail[1];
                                                        $client['name'] = $customer_details[$c+1];
                                                    }
                                                }
                                            }
                                            
                                            
                                        
                                            preg_match_all('!\d+!', $customer, $matches);
                                            if(count($matches[0]) > 1){
                                                $client['phone'] = $customer; 
                                                $after_phone = true;
                                            }
                                            $client[] = $customer;
                                        }
                                        $c++;
                                        
                                    }
                                    $delivery_date = '';
                                    $delivery_time = '';
                                    
                                    if(isset($delivery_details[2])){
                                        $delivery_date_time = strtotime($delivery_details[2]);
                                        $delivery_date = date('Y-m-d',$delivery_date_time);
                                        $delivery_time = date('H:i',$delivery_date_time);
                                    }
                                    if($merchant['merchant_id'] !== null){
                                        $params['order_id_token'] = FunctionsV3::generateOrderToken();
                                        $params['merchant_id'] = $merchant['merchant_id'];
                                        $params['delivery_service_client_details'] = json_encode($client);
                                        $params['delivery_service_order_details'] = json_encode($order_item);
                                        $params['delivery_service_order_number'] = $order_id;
                                        $params['delivery_service_type'] = 'grubhub';
                                        $params['delivery_service_special_instructions'] = $instruction;
                                        $params['delivery_instruction'] = $instruction;
                                        $params['json_details'] = json_encode($order_item);
                                        $params['trans_type'] = $trans_type;
                                        $params['sub_total'] = $subtotal;
                                        $params['tax'] = $tax;
                                        $params['total_w_tax'] = $total;
                                        $params['cart_tip_value'] = $tip;
                                        $params['status'] = 'Grubhub';
                                        $params['delivery_date'] = $delivery_date;
                                        $params['delivery_time'] = $delivery_time;
                                        $params['request_from'] = 'grubhub';
                                        $order_id = null;
                                        if(isset($existing_order['order_id'])){
                                            $DbExt->updateData("{{order}}",$params,'order_id',$existing_order['order_id']);
                                            $order_id = $existing_order['order_id'];
                                        }else{
                                            $DbExt->insertData("{{order}}",$params);
                                            $order_id = Yii::app()->db->getLastInsertID();
                                        }
                                        print_r($params);
                                        FunctionsV3::callAddons($order_id);
                                    }
//                                    echo '<b>Instructions: </b>'.$instruction.'<br/>';
//                                    echo '<b>Subtotal</b>'.$subtotal.'<br/>';
//                                    echo '<b>Tax</b>'.$tax.'<br/>';
//                                    echo '<b>Total</b>'.$total.'<br/>';
//                                    echo '<h3>Order</h3>';
//                                    print_r($order_details);
//                                    echo '<h3>Customer</h3>';
//                                    print_r($customer_details);
                                    echo '</pre>';
                                }
//                            }
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