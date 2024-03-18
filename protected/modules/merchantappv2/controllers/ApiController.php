<?php
class ApiController extends CController
{
	public $data;
	public $code = 2;
	public $msg = '';
	public $details = '';

	public function __construct()
	{
		$this->data = $_GET;

		$website_timezone = Yii::app()->functions->getOptionAdmin("website_timezone");
		if (!empty($website_timezone)) {
			Yii::app()->timeZone = $website_timezone;
		}

		FunctionsV3::handleLanguage();
		$lang = Yii::app()->language;
		if (isset($_GET['debug'])) {
			//dump($lang);
		}
	}

	public function beforeAction($action)
	{
		/*check if there is api has key*/

		/*$action=Yii::app()->controller->action->id;				
			  if(isset($this->data['api_key'])){
				  if(!empty($this->data['api_key'])){			   
					 $continue=true;
					 if($action=="getLanguageSettings" || $action=="registerMobile"){
						   $continue=false;
					 }
					 if($continue){
							$key=getOptionA('merchant_app_hash_key');
						 if(trim($key)!=trim($this->data['api_key'])){
							  $this->msg=$this->t("api hash key is not valid");
						   $this->output();
						   Yii::app()->end();
						 }
					 }			
				  }
			  }*/

		$action = Yii::app()->controller->action->id;
		$key = getOptionA('merchantappv2_api_hash_key');
		if (!empty($key)) {
			$continue = true;
			if ($action == "getLanguageSettings" || $action == "registerMobile") {
				$continue = false;
			}
			if ($continue) {
				$this->data['api_key'] = isset($this->data['api_key']) ? $this->data['api_key'] : '';
				if (trim($key) != trim($this->data['api_key'])) {
					$this->msg = $this->t("api hash key is not valid");
					$this->output();
					Yii::app()->end();
				}
			}
		}
		return true;
	}

	public function actionIndex()
	{
		//throw new CHttpException(404,'The specified url cannot be found.');
	}

	private function q($data = '')
	{
		return Yii::app()->db->quoteValue($data);
	}

	private function t($message = '')
	{
		//return Yii::t("default",$message);		
		return Yii::t("merchantapp-backend", $message);
	}

	private function output()
	{

		if (!isset($this->data['debug'])) {
			header('Access-Control-Allow-Origin: *');
			header('Content-type: application/javascript;charset=utf-8');
		}

		if (empty($this->details)) {

			$this->details = (object) [];
			// $this->details = array();
		}

		$resp = array(
			'code' => $this->code,
			'msg' => $this->msg,
			'details' => $this->details,
			'request' => json_encode($this->data, true)
		);
		if (isset($this->data['debug'])) {
			dump($resp);
		}


		if (!isset($_GET['callback'])) {
			$_GET['callback'] = '';
		}

		if (isset($_GET['json']) && $_GET['json'] == TRUE) {
			echo CJSON::encode($resp);
		} else {
			echo $_GET['callback'] . '(' . CJSON::encode($resp) . ')';
		}
		Yii::app()->end();
	}


	private function outputArray()
	{

		if (!isset($this->data['debug'])) {
			header('Access-Control-Allow-Origin: *');
			header('Content-type: application/javascript;charset=utf-8');
		}

		if (empty($this->details)) {

			// $this->details = (object)[];
			$this->details = array();
		}

		$resp = array(
			'code' => $this->code,
			'msg' => $this->msg,
			'details' => $this->details,
			'request' => json_encode($this->data, true)
		);
		if (isset($this->data['debug'])) {
			dump($resp);
		}


		if (!isset($_GET['callback'])) {
			$_GET['callback'] = '';
		}

		if (isset($_GET['json']) && $_GET['json'] == TRUE) {
			echo CJSON::encode($resp);
		} else {
			echo $_GET['callback'] . '' . CJSON::encode($resp) . '';
		}
		Yii::app()->end();
	}

	public function actionLogin()
	{
		$Validator = new Validator;
		$req = array(
			'username' => $this->t("username is required"),
			'password' => $this->t("password is required"),
			'merchant_device_id' => $this->t("Device id is required"),
			'device_platform' => $this->t("Device Platform is required"),
		);
		$DbExt = new DbExt;
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if ($res = merchantApp::login($this->data['username'], md5($this->data['password']))) {

				//dump($res);
				$stmtc = "SELECT name,link from {{mobile_links}} where status=1";
				$mobile_links = array();
				if ($resc = $DbExt->rst($stmtc)) {
					$mobile_links = $resc;
				}
				$params = array(
					'merchant_id' => $res['merchant_id'],
					'merchant_user_id' => isset($res['merchant_user_id']) ? $res['merchant_user_id'] : 0,
					'user_type' => $res['user_type'],
					'device_platform' => $this->data['device_platform'],
					'device_id' => $this->data['merchant_device_id'],
					'printer_status' => $this->data['printer_status'],
					'enabled_push' => 1,
					'date_created' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR'],
				);

				if ($res['status'] == "active" || $res['status'] == "expired") {


					if (
						$resp = merchantApp::getMerchantDeviceInfoByType(
							$res['user_type'],
							$res['merchant_id'],
							$params['merchant_user_id']
						)
					) {

						if ($res['user_type'] == "admin") {
							$sql_delete = "DELETE FROM
							{{mobile_device_merchant}}
							WHERE
							user_type=" . self::q($res['user_type']) . "
							AND
							merchant_id=" . self::q($res['merchant_id']) . "
							";
						} else {
							$sql_delete = "DELETE FROM
							{{mobile_device_merchant}}
							WHERE
							user_type=" . self::q($res['user_type']) . "
							AND
							merchant_id=" . self::q($res['merchant_id']) . "
							AND 
							merchant_user_id =" . self::q($params['merchant_user_id']) . "
							";
						}

						if (count($resp) >= 2) {
							$DbExt->qry($sql_delete);
							if (!$DbExt->insertData("{{mobile_device_merchant}}", $params)) {
								$this->msg = $this->t("Failed cannot insert records");
								$this->output();
							}

						} else {

							$record_id = $resp[0]['id'];
							unset($params['enabled_push']);
							unset($params['date_created']);
							$params['date_modified'] = FunctionsV3::dateNow();

							if (!$DbExt->updateData("{{mobile_device_merchant}}", $params, 'id', $record_id)) {
								$this->msg = $this->t("Failed cannot update records");
								$this->output();
							}
						}

					} else {
						if (!$DbExt->insertData("{{mobile_device_merchant}}", $params)) {
							$this->msg = $this->t("Failed cannot insert records");
							$this->output();
						}
					}

					$this->msg = $this->t("Successul");
					$this->code = 1;
					$this->details = array(
						'token' => $res['token'],
						'mobile_links' => $mobile_links,
						'info' => array(
							'username' => $res['username'],
							'restaurant_name' => isset($res['restaurant_name']) ? $res['restaurant_name'] : '',
							'contact_email' => $res['contact_email'],
							'user_type' => $res['user_type'],
							'merchant_id' => $res['merchant_id'],
							'merchant_user_id' => $params['merchant_user_id']
						)
					);
				} else
					$this->msg = $this->t("Login Failed. You account status is") . " " . $res['status'];
			} else
				$this->msg = $this->t("either username or password is invalid");
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionGetTodaysOrder()
    {    	
     
    	$Validator=new Validator;
		$req=array(
		  'token'=>$this->t("token is required"),
		  'mtid'=>$this->t("merchant id is required"),
		  'user_type'=>$this->t("user type is required"),
		);
		$Validator->required($req,$this->data);
		if ($Validator->validate()){
			if ( $res=merchantApp::validateToken($this->data['mtid'],
			    $this->data['token'],$this->data['user_type'])){
			    				    			   
			    /*SET MERCHANT TIMEZONE*/
			    merchantApp::setMerchantTimeZone($this->data['mtid']);		
			    $_in = '"pending","accepted","Accepted","Pending","paid","Paid","Grubhub","Doordash","Uber Eats"';	
			    $DbExt=new DbExt;	
				$stmt="
				SELECT a.*,
				(
				select concat(first_name,' ',last_name)
				from 
				{{client}}
				where
				client_id=a.client_id
				limit 0,1				
				) as customer_name
				
				FROM
				{{order}} a
				WHERE
				merchant_id=".$this->q($res['merchant_id'])."				
				AND
				(date_created LIKE '".date("Y-m-d")."%' OR date_modified LIKE '".date("Y-m-d")."%' OR delivery_date LIKE '".date("Y-m-d")."%')						
				AND 
				status IN ($_in)					
				AND 
				request_cancel='2'
				ORDER BY order_id DESC
				LIMIT 0,100
				";
				
                $merchant_id = $this->data['mtid'];
                $merchant_enabled_auto_confirm_prep_time=0;
                $merchant_enabled_auto_confirm_prep_time = Yii::app()->functions->getOption("merchant_enabled_auto_confirm_prep_time",$merchant_id);
                if($merchant_enabled_auto_confirm_prep_time){
                   $merchant_enabled_auto_confirm_prep_time =$merchant_enabled_auto_confirm_prep_time; 
                }
                
                $merchant_info=Yii::app()->functions->getMerchant(isset($merchant_id)?$merchant_id:'');
                $push_log_link = FunctionsV3::getPushNotificationLink($this->data['mtid']);
				if ( $res=$DbExt->rst($stmt)){	
				    
					$this->code=1; $this->msg="OK";					
					foreach ($res as $key => $val) {
					    if( array_key_exists('delivery_date', $val) &&  $val['delivery_date'] != date("Y-m-d") ) {
					        unset($val);
					        continue;
					    }
                                            if($val['delivery_service_type'] == 'grubhub' || $val['delivery_service_type'] == 'doordash'|| $val['delivery_service_type'] == 'ubereats'){
                                                $customer_details = json_decode($val['delivery_service_client_details'],true);
                                                $name = ucwords($customer_details['name']);
                                            }else{
                                                $name = !empty($val['customer_name'])?$val['customer_name']:$this->t('No name');
                                            }
                                     $status = '';
                                     
                                     //Apply Condition for delivery for DinDin only
                                     
                        if($val['delivery_service_type'] = 'dindin'){
                            
                                  if($val['doordash_drive_pickup_time'] != ''){
                            $delivery_time = $val['doordash_drive_pickup_time'];
                            $status .= ' -Delivered by Doordash';
                        }elseif ($val['doordash_drive_pickup_time'] == '' || is_null($val['doordash_drive_pickup_time'])){
                            $status .= ' -Delivery by Doordash';
                            $delivery_time = $val['delivery_time'];
                            
                        }else{
                            $delivery_time = $val['delivery_time'];
                        }
                            
                        }
                        else{
                             $delivery_time = $val['delivery_time']; 
                        }
                                    
                                    
                        // if($val['doordash_drive_pickup_time'] != ''){
                        //     $delivery_time = $val['doordash_drive_pickup_time'];
                        //     $status .= ' -Delivered by Doordash';
                        // }elseif ($val['doordash_drive_pickup_time'] == '' || is_null($val['doordash_drive_pickup_time'])){
                        //     $status .= ' -Delivery by Doordash';
                        //     $delivery_time = $val['delivery_time'];
                            
                        // }else{
                        //     $delivery_time = $val['delivery_time'];
                        // }
                        
                        
						$data[]=array(						  
						  'order_id'=>$val['order_id'],	 
						  'confirm_link'=>$val['confirm_link'], 
						  'confirm_link_clicked'=>$val['confirm_link_clicked'],  
						  'confirmed'=>$val['confirmed'], 
						  'pickup_in'=>$val['pickup_in'],
						  'viewed'=>$val['viewed'],
						  'status_raw'=>strtolower($val['status']),
                          'delivery_service_type' => $val['delivery_service_type'],
						  'status'=>merchantApp::t($val['status']).$status,
						  'trans_type_raw'=>$val['trans_type'],			  
						  'trans_type'=>merchantApp::t($val['trans_type']),						  
						  'total_w_tax'=>$val['total_w_tax'],						  
						  'total_w_tax_prety'=>merchantApp::prettyPrice($val['total_w_tax']),
						  'transaction_date'=>Yii::app()->functions->FormatDateTime($val['date_created'],true),
						  'transaction_time'=>Yii::app()->functions->timeFormat($val['date_created'],true),
						  'delivery_time'=>Yii::app()->functions->timeFormat($delivery_time,true),
						  'delivery_asap_raw'=>$val['delivery_asap'],
						  'delivery_asap'=>$val['delivery_asap']==1?merchantApp::t("ASAP"):'',
						  'merchant_enabled_auto_confirm_prep_time'=> $merchant_enabled_auto_confirm_prep_time,
						  'delivery_date'=>Yii::app()->functions->FormatDateTime($val['delivery_date'],false),
						  'customer_name'=> $name
						);
					}					
					
					$unopen_count = 0;
					$unopen_resp = merchantApp::getUnOpenOrder($this->data['mtid']);					
					if($unopen_resp){						
						$unopen_count = $unopen_resp['total_unopen'];
					}


					$this->code=1;
					$this->msg="OK";
					$this->details=array(
					  'data'=>$data,
					  'total_order'=>count($data),
                        'push_log_link'=>$push_log_link,
					  'unopen_count'=>$unopen_count
					);
				} else {
                    $this->details=array(
                        'push_log_link'=>$push_log_link,
                    );
                    $this->code = 1;
				    $this->msg=$this->t("no current orders");
                }
			} else {
				$this->code=3;
				$this->msg=$this->t("you session has expired or someone login with your account");
			}
		} else $this->msg=merchantApp::parseValidatorError($Validator->getError());	    	
		$this->output();    	    
    }
    

	public function actionGetTodaysOrder2()
	{

		//exit('ttttt');
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				/*SET MERCHANT TIMEZONE*/
				merchantApp::setMerchantTimeZone($this->data['mtid']);
				$_in = '"pending","accepted","Accepted","Pending","paid","Paid","Grubhub","Doordash","Uber Eats"';
				$DbExt = new DbExt;
				$stmt = "
				SELECT a.*,
				(
				select concat(first_name,' ',last_name)
				from 
				{{client}}
				where
				client_id=a.client_id
				limit 0,1				
				) as customer_name
				
				FROM
				{{order}} a
				WHERE
				merchant_id=" . $this->q($res['merchant_id']) . "				
				AND
				(date_created LIKE '" . date("Y-m-d") . "%' OR date_modified LIKE '" . date("Y-m-d") . "%' OR delivery_date LIKE '" . date("Y-m-d") . "%')						
				AND 
				status IN ($_in)					
				AND 
				request_cancel='2'
				ORDER BY order_id DESC
				LIMIT 0,100
				";

				$merchant_id = $this->data['mtid'];
				$merchant_enabled_auto_confirm_prep_time = 0;
				$merchant_enabled_auto_confirm_prep_time = Yii::app()->functions->getOption("merchant_enabled_auto_confirm_prep_time", $merchant_id);
				if ($merchant_enabled_auto_confirm_prep_time) {
					$merchant_enabled_auto_confirm_prep_time = $merchant_enabled_auto_confirm_prep_time;
				}

				$merchant_info = Yii::app()->functions->getMerchant(isset($merchant_id) ? $merchant_id : '');
				$push_log_link = FunctionsV3::getPushNotificationLink($this->data['mtid']);
				if ($res = $DbExt->rst($stmt)) {

					$this->code = 1;
					$this->msg = "OK";
					foreach ($res as $key => $val) {

						if (array_key_exists('delivery_date', $val) && $val['delivery_date'] != date("Y-m-d")) {
							unset($val);
							continue;
						}

						if ($val['delivery_service_type'] == 'grubhub' || $val['delivery_service_type'] == 'doordash' || $val['delivery_service_type'] == 'ubereats') {
							$customer_details = json_decode($val['delivery_service_client_details'], true);
							$name = ucwords($customer_details['name']);
						} else {
							$name = !empty($val['customer_name']) ? $val['customer_name'] : $this->t('No name');
						}
						$status = '';

						//Apply Condition for delivery for DinDin only

						if ($val['delivery_service_type'] = 'dindin') {

							if ($val['doordash_drive_pickup_time'] != '') {
								$delivery_time = $val['doordash_drive_pickup_time'];
								$status .= ' -Delivered by Doordash';
							} elseif ($val['doordash_drive_pickup_time'] == '' || is_null($val['doordash_drive_pickup_time'])) {
								$status .= ' -Delivery by Doordash';
								$delivery_time = $val['delivery_time'];

							} else {
								$delivery_time = $val['delivery_time'];
							}

						} else {
							$delivery_time = $val['delivery_time'];
						}


						// if($val['doordash_drive_pickup_time'] != ''){
						//     $delivery_time = $val['doordash_drive_pickup_time'];
						//     $status .= ' -Delivered by Doordash';
						// }elseif ($val['doordash_drive_pickup_time'] == '' || is_null($val['doordash_drive_pickup_time'])){
						//     $status .= ' -Delivery by Doordash';
						//     $delivery_time = $val['delivery_time'];

						// }else{
						//     $delivery_time = $val['delivery_time'];
						// }



						$data[] = array(
							'order_id' => $val['order_id'],
							'confirm_link' => $val['confirm_link'],
							'confirm_link_clicked' => $val['confirm_link_clicked'],
							'confirmed' => $val['confirmed'],
							'pickup_in' => $val['pickup_in'],
							'viewed' => $val['viewed'],
							'status_raw' => strtolower($val['status']),
							'delivery_service_type' => $val['delivery_service_type'],
							'status' => merchantApp::t($val['status']) . $status,
							'trans_type_raw' => $val['trans_type'],
							'trans_type' => merchantApp::t($val['trans_type']),
							'total_w_tax' => $val['total_w_tax'],
							'total_w_tax_prety' => merchantApp::prettyPrice($val['total_w_tax']),
							'transaction_date' => Yii::app()->functions->FormatDateTime($val['date_created'], true),
							'transaction_time' => Yii::app()->functions->timeFormat($val['date_created'], true),
							'delivery_time' => Yii::app()->functions->timeFormat($delivery_time, true),
							'delivery_asap_raw' => $val['delivery_asap'],
							'delivery_asap' => $val['delivery_asap'] == 1 ? merchantApp::t("ASAP") : '',
							'merchant_enabled_auto_confirm_prep_time' => $merchant_enabled_auto_confirm_prep_time,
							'delivery_date' => Yii::app()->functions->FormatDateTime($val['delivery_date'], false),
							'customer_name' => $name
						);
					}

					$unopen_count = 0;
					$unopen_resp = merchantApp::getUnOpenOrder($this->data['mtid']);
					if ($unopen_resp) {
						$unopen_count = $unopen_resp['total_unopen'];
					}


					$this->code = 1;
					$this->msg = "OK";
					$this->details = array(
						'data' => $data,
						'total_order' => count($data),
						'push_log_link' => $push_log_link,
						'unopen_count' => $unopen_count
					);
				} else {
					$this->details = array(
						'push_log_link' => $push_log_link,
					);
					$this->code = 1;
					$this->msg = $this->t("no current orders");
				}
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionGetPendingOrders()
	{
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				$_in = "'pending'";
				$pending_tabs = getOptionA('merchant_app_pending_tabs');
				if (!empty($pending_tabs)) {
					$pending_tabs = json_decode($pending_tabs, true);
					if (is_array($pending_tabs) && count($pending_tabs) >= 1) {
						$_in = '';
						foreach ($pending_tabs as $key => $val) {
							$_in .= "'$val',";
						}
						$_in = substr($_in, 0, -1);
					}
				}

				$DbExt = new DbExt;
				$stmt = "
				SELECT a.*,				
				(
				select concat(first_name,' ',last_name)
				from 
				{{client}}
				where
				client_id=a.client_id
				limit 0,1				
				) as customer_name

				FROM
				{{order}} a
				WHERE
				merchant_id=" . $this->q($res['merchant_id']) . "
				AND
				status IN ($_in)			
				AND request_cancel='2'				
				ORDER BY date_created DESC
				LIMIT 0,100
				";
				if (isset($_GET['debug'])) {
					dump($stmt);
				}
				if ($res = $DbExt->rst($stmt)) {
					$this->code = 1;
					$this->msg = "OK";
					foreach ($res as $val) {
						$data[] = array(
							'order_id' => $val['order_id'],
							'viewed' => $val['viewed'],
							'status' => t($val['status']),
							'status_raw' => strtolower($val['status']),
							'trans_type' => t($val['trans_type']),
							'trans_type_raw' => $val['trans_type'],
							'delivery_service_type' => $val['delivery_service_type'],
							'total_w_tax' => $val['total_w_tax'],
							'total_w_tax_prety' => merchantApp::prettyPrice($val['total_w_tax']),
							'transaction_date' => Yii::app()->functions->FormatDateTime($val['date_created'], true),
							'transaction_time' => Yii::app()->functions->timeFormat($val['date_created'], true),
							'delivery_time' => Yii::app()->functions->timeFormat($val['delivery_time'], true),
							'delivery_asap' => $val['delivery_asap'] == 1 ? merchantApp::t("ASAP") : '',
							'delivery_asap_raw' => $val['delivery_asap'],
							'delivery_date' => Yii::app()->functions->FormatDateTime($val['delivery_date'], false),
							'customer_name' => !empty($val['customer_name']) ? $val['customer_name'] : $this->t('No name')
						);
					}
					$this->code = 1;
					$this->msg = "OK";
					$this->details = $data;
				} else
					$this->msg = $this->t("no pending orders");
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		if (isset($this->data['json'])) {
			$this->outputArray();
		} else {
			$this->output();
		}


	}

	public function actionGetAllOrders()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				$DbExt = new DbExt;
				$stmt = "
				SELECT a.*,

				(
				select concat(first_name,' ',last_name)
				from 
				{{client}}
				where
				client_id=a.client_id
				limit 0,1				
				) as customer_name
				
				FROM
				{{order}} a
				WHERE
				merchant_id=" . $this->q($res['merchant_id']) . "	
				AND status NOT IN ('initial_order')			
				ORDER BY date_created DESC
				LIMIT 0,100
				";
				if ($res = $DbExt->rst($stmt)) {
					$this->code = 1;
					$this->msg = "OK";

					$merchant_id = $this->data['mtid'];
					$merchant_info = Yii::app()->functions->getMerchant(isset($merchant_id) ? $merchant_id : '');
					foreach ($res as $val) {
						if ($val['delivery_service_type'] == 'grubhub' || $val['delivery_service_type'] == 'doordash' || $val['delivery_service_type'] == 'ubereats') {
							$customer_details = json_decode($val['delivery_service_client_details'], true);
							$name = ucwords($customer_details['name']);
						} else {
							$name = !empty($val['customer_name']) ? $val['customer_name'] : $this->t('No name');
						}
						$new_status = 'no';
						if (date('Y-m-d') == date('Y-m-d', strtotime($val['delivery_date'])) && $val['viewed'] == 1) {
							$new_status = yes;
						}
						$status = '';
						if ($merchant_info['service'] == 8 && $val['trans_type'] == 'delivery' && $val['doordash_drive_pickup_time'] != '') {
							$status .= ' -Delivered by Doordash';
						}

						if ($this->data['json']) {
							if ($merchant_info['service'] == 8 && $val['trans_type'] == 'delivery' && ($val['doordash_drive_pickup_time'] == '' || is_null($val['doordash_drive_pickup_time']))) {
								$status .= ' -Delivery by Doordash';
							}

						}


						$data[] = array(
							'order_id' => $val['order_id'],
							'viewed' => $val['viewed'],
							'new_status' => $new_status,
							'delivery_service_type' => $val['delivery_service_type'],
							'status' => merchantApp::t($val['status']) . $status,
							'confirmed' => $val['confirmed'],

							'confirm_link' => $val['confirm_link'],
							'confirm_link_clicked' => $val['confirm_link_clicked'],
							'status_raw' => strtolower($val['status']),
							'trans_type' => merchantApp::t($val['trans_type']),
							'trans_type_raw' => $val['trans_type'] . $delivery,
							'total_w_tax' => $val['total_w_tax'],
							'total_w_tax_prety' => merchantApp::prettyPrice($val['total_w_tax']),
							'transaction_date' => Yii::app()->functions->FormatDateTime($val['date_created'], true),
							'transaction_time' => Yii::app()->functions->timeFormat($val['date_created'], true),
							'delivery_time' => Yii::app()->functions->timeFormat($val['delivery_time'], true),
							'delivery_asap' => $val['delivery_asap'] == 1 ? merchantApp::t("ASAP") : '',
							'delivery_asap_raw' => $val['delivery_asap'],
							'delivery_date' => Yii::app()->functions->FormatDateTime($val['delivery_date'], false),
							'customer_name' => $name
						);
					}
					$this->code = 1;
					$this->msg = "OK";
					$this->details = $data;
				} else
					$this->msg = $this->t("no orders found");
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		if (isset($this->data['json'])) {
			$this->outputArray();
		} else {
			$this->output();
		}
	}


	//test function created on 12-04-2023 to debug something went wrong in Detail Order API
	public function actionGetAllOrders2()
	{
		// exit('123');
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				$DbExt = new DbExt;
				$stmt = "
				SELECT a.*,

				(
				select concat(first_name,' ',last_name)
				from 
				{{client}}
				where
				client_id=a.client_id
				limit 0,1				
				) as customer_name
				
				FROM
				{{order}} a
				WHERE
				merchant_id=" . $this->q($res['merchant_id']) . "	
				AND status NOT IN ('initial_order')			
				ORDER BY date_created DESC
				LIMIT 0,100
				";
				if ($res = $DbExt->rst($stmt)) {
					$this->code = 1;
					$this->msg = "OK";

					$merchant_id = $this->data['mtid'];
					$merchant_info = Yii::app()->functions->getMerchant(isset($merchant_id) ? $merchant_id : '');
					foreach ($res as $val) {
						if ($val['delivery_service_type'] == 'grubhub' || $val['delivery_service_type'] == 'doordash' || $val['delivery_service_type'] == 'ubereats') {
							$customer_details = json_decode($val['delivery_service_client_details'], true);
							$name = ucwords($customer_details['name']);
						} else {
							$name = !empty($val['customer_name']) ? $val['customer_name'] : $this->t('No name');
						}
						$new_status = 'no';
						if (date('Y-m-d') == date('Y-m-d', strtotime($val['delivery_date'])) && $val['viewed'] == 1) {
							$new_status = yes;
						}
						$status = '';
						if ($merchant_info['service'] == 8 && $val['trans_type'] == 'delivery' && $val['doordash_drive_pickup_time'] != '') {
							$status .= ' -Delivered by Doordash';
						}

						if ($this->data['json']) {
							if ($merchant_info['service'] == 8 && $val['trans_type'] == 'delivery' && ($val['doordash_drive_pickup_time'] == '' || is_null($val['doordash_drive_pickup_time']))) {
								$status .= ' -Delivery by Doordash';
							}

						}


						$data[] = array(
							'order_id' => $val['order_id'],
							'viewed' => $val['viewed'],
							'new_status' => $new_status,
							'delivery_service_type' => $val['delivery_service_type'],
							'status' => merchantApp::t($val['status']) . $status,
							'confirmed' => $val['confirmed'],

							'confirm_link' => $val['confirm_link'],
							'confirm_link_clicked' => $val['confirm_link_clicked'],
							'status_raw' => strtolower($val['status']),
							'trans_type' => merchantApp::t($val['trans_type']),
							'trans_type_raw' => $val['trans_type'] . $delivery,
							'total_w_tax' => $val['total_w_tax'],
							'total_w_tax_prety' => merchantApp::prettyPrice($val['total_w_tax']),
							'transaction_date' => Yii::app()->functions->FormatDateTime($val['date_created'], true),
							'transaction_time' => Yii::app()->functions->timeFormat($val['date_created'], true),
							'delivery_time' => Yii::app()->functions->timeFormat($val['delivery_time'], true),
							'delivery_asap' => $val['delivery_asap'] == 1 ? merchantApp::t("ASAP") : '',
							'delivery_asap_raw' => $val['delivery_asap'],
							'delivery_date' => Yii::app()->functions->FormatDateTime($val['delivery_date'], false),
							'customer_name' => $name
						);
					}
					$this->code = 1;
					$this->msg = "OK";
					$this->details = $data;
				} else
					$this->msg = $this->t("no orders found");
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		if (isset($this->data['json'])) {
			$this->outputArray();
		} else {
			$this->output();
		}
	}

	public function actionUpdateOrderStatus()
	{

		session_start();
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if ($res = merchantApp::validateToken($this->data['mtid'], $this->data['token'], $this->data['user_type'])) {
				$merchant_id = $this->data['mtid'];
				$order_id = $this->data['order_id'];
				Yii::app()->functions->updateOption("is_confirm_email_triggered_" . $order_id, 'true', $merchant_id);
				if ($this->UpdateStatusPrepTime($this->data['order_id'], $this->data['confirmed'], $this->data['time_in_selected']))
					;
				else {
					$this->code = 3;
					$this->msg = $this->t("order details not available");
				}

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionResumeTakingOrders()
	{
		session_start();
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				$store_start_date = '';

				$store_close_date = '';
				$params = array(
					'merchant_id' => $this->data['mtid'],
					'date_modified' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR'],
					'store_start_date' => $store_start_date,
					'store_close_date' => $store_close_date
				);

				$DbExt = new DbExt;
				//if ( $resp=merchantApp::getDeviceInfo($this->data['merchant_device_id'])){					
				if (
					$resp = merchantApp::getDeviceInfoByUserType(
						$this->data['merchant_device_id'],
						$this->data['user_type'],
						$this->data['mtid']
					)
				) {
					//dump($resp);			
					if ($DbExt->updateData('{{mobile_device_merchant}}', $params, 'id', $resp['id'])) {

						$this->msg = $this->t("You resumed taking orders");
						$this->code = 1;


						//dump($this->data);

						$merchant_id = $this->data['mtid'];
						if (isset($store_start_date)) {
							Yii::app()->functions->updateOption("store_start_date", '', $merchant_id);
						}
						if (isset($this->data['end_time'])) {
							Yii::app()->functions->updateOption("end_time", '', $merchant_id);
						}
						if (isset($store_close_date)) {
							Yii::app()->functions->updateOption("store_close_date", '', $merchant_id);
						}

					} else
						$this->msg = $this->t("ERROR: Cannot update");
				} else
					$this->msg = $this->t("Device id not found please restart the app");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionSaveTakingOrders()
	{
		session_start();
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {
				$store_start_date = date('Y-m-d G:i:s');
				if ($this->data['custom_time']) {
					$time = $this->data['custom_time'];
					$timearray = explode(' ', $time);

					if ($timearray[1] == 'hours') {

						$end_time = strtotime($store_start_date) + $timearray[0] * 3600;

					}
					if ($timearray[1] == 'minutes') {
						$end_time = strtotime($store_start_date) + $timearray[0] * 60;
					}
				}


				if ($this->data['end_time'] == 'today') {
					$sec = 86400 - date('H') * 3600 - date('i') * 60 - date('s');
					$end_time = strtotime($store_start_date) + $sec;
				}
				if (isset($this->data['end_time'])) {
					$end_time = strtotime($store_start_date) + $this->data['end_time'];
				}

				$store_close_date = date('Y-m-d G:i:s', $end_time);


				$params = array(
					'merchant_id' => $this->data['mtid'],
					'date_modified' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR'],
					'store_start_date' => $store_start_date,
					'store_close_date' => $store_close_date
				);

				$DbExt = new DbExt;
				//if ( $resp=merchantApp::getDeviceInfo($this->data['merchant_device_id'])){					
				if (
					$resp = merchantApp::getDeviceInfoByUserType(
						$this->data['merchant_device_id'],
						$this->data['user_type'],
						$this->data['mtid']
					)
				) {
					//dump($resp);			
					if ($DbExt->updateData('{{mobile_device_merchant}}', $params, 'id', $resp['id'])) {
						if ($merchant_info = Yii::app()->functions->getMerchant($this->data['mtid'])) {
							$subject = $merchant_info['restaurant_name'] . ' pause order';
							$time = '';

							if ($this->data['end_time'] == 'today') {
								$time = 'today';
							} else if ($this->data['end_time'] == '86400') {
								$time = 'rest of the day';
							} else if ($this->data['end_time'] == '1800') {
								$time = '30 minutes';
							} else if ($this->data['end_time'] == '3600') {
								$time = '1 hour';
							} else if ($this->data['end_time'] == '7200') {
								$time = '2 hours';
							} else if ($this->data['custom_time']) {
								$time = $this->data['custom_time'];
								$this->data['end_time'] = $this->data['custom_time'];
							}

							$body = $merchant_info['restaurant_name'] . ' has requested to pause the order for ' . $time;
							FunctionsV3::notifyStopTakingOrder($subject, $body);
						}
						$this->msg = $this->t("Setting saved");
						$this->code = 1;


						//dump($this->data);

						$merchant_id = $this->data['mtid'];
						if (isset($store_start_date)) {
							Yii::app()->functions->updateOption("store_start_date", strtotime($store_start_date), $merchant_id);
						}
						if (isset($store_start_date)) {
							Yii::app()->functions->updateOption("store_start_date", strtotime($store_start_date), $merchant_id);
						}
						if (isset($time)) {
							Yii::app()->functions->updateOption("end_time_val", $time, $merchant_id);
						}

						if (isset($this->data['end_time'])) {
							Yii::app()->functions->updateOption("end_time_val", $this->data['end_time'], $merchant_id);
						}

						if (isset($store_close_date)) {
							Yii::app()->functions->updateOption("store_close_date", strtotime($store_close_date), $merchant_id);
						}

					} else
						$this->msg = $this->t("ERROR: Cannot update");
				} else
					$this->msg = $this->t("Device id not found please restart the app");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}



	public function actionOrderdDetails()
	{
		session_start();
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if ($data = Yii::app()->functions->getOrder2($this->data['order_id'])) {

					if ($this->data['json']) {
						if (!is_array(json_decode($data['json_details']))) {
							$data['json_details'] = (array) $data['json_details'];

						}
					}
					$promo_name = '';
					if ($data['voucher_type'] != '') {
						$promo_name = 'Promo by Dindin';
					}

					$json_details = !empty($data['json_details']) ? json_decode($data['json_details'], true) : false;



					Yii::app()->functions->displayOrderHTML(
						array(
							'order_id' => $data['order_id'],
							'merchant_id' => $data['merchant_id'],
							'delivery_type' => $data['trans_type'],
							'delivery_charge' => $data['delivery_charge'],
							'packaging' => $data['packaging'],
							'cart_tip_value' => $data['cart_tip_value'],
							'cart_tip_percentage' => $data['cart_tip_percentage'] / 100,
							'card_fee' => $data['card_fee'],
							'total_w_tax' => $data['total_w_tax'],
							'tax' => $data['tax'],
							'delivery_service_type' => $data['delivery_service_type'],
							'points_discount' => isset($data['points_discount']) ? $data['points_discount'] : '' /*POINTS PROGRAM*/ ,
							'voucher_amount' => $data['voucher_amount'],
							'voucher_type' => $data['voucher_type'],
							'promo_name' => $promo_name
						),
						$json_details,
						true,
						$data['order_id'],
						'api'
					);


					if (Yii::app()->functions->code == 1) {
						$data_raw = Yii::app()->functions->details['raw'];

						$data_raw['html'] = Yii::app()->functions->details['html'];
						$data_raw['confirm_link'] = $data['confirm_link'];
						$data_raw['confirm_link_clicked'] = $data['confirm_link_clicked'];

						$sub_total = $data_raw['total']['subtotal'];


						// Total Price Print

						$data_raw['total_print']['subtotal'] = normalPrettyPrice($data_raw['total']['subtotal']);

						//   if( !isset($data['voucher_amount']) && ( $data['voucher_amount'] < 0) )
						//   {
						//         $data_raw['total_print']['subtotal'] = $data_raw['total_print']['subtotal'] + $data['voucher_amount'];
						//   }

						$data_raw['total_print']['subtotal1'] = $data['sub_total'];
						$data_raw['total_print']['subtotal2'] = prettyFormat($data['sub_total']);
						if ($data['delivery_service_type'] == 'grubhub' || $data['delivery_service_type'] == 'doordash' || $data['delivery_service_type'] == 'ubereats') {
							$data_raw['total_print']['taxable_total'] = prettyFormat($data['tax']);
						} else {
							$data_raw['total_print']['taxable_total'] = prettyFormat($data['taxable_total']);
						}

						$data_raw['total_print']['delivery_charges'] = $data_raw['total']['delivery_charges'];//prettyFormat($data_raw['total']['delivery_charges']);

						$data_raw['total_print']['total_print'] = prettyFormat($data['total_w_tax']);

						$data_raw['total_print']['tax_amt'] = $data_raw['total']['tax_amt'] . "%";
						$data_raw['total_print']['merchant_packaging_charge'] = prettyFormat($data_raw['total']['merchant_packaging_charge']);
						$data_raw['total_print']['packaging'] = prettyFormat($data['packaging']);

						if ($data['order_change'] > 0) {
							$data_raw['total_print']['order_change'] = prettyFormat($data['order_change']);
						}
						$data_raw['total_print']['promo_name'] = '';
						$data_raw['total']['voucher_amount1'] = '';
						$data_raw['total']['promo_name'] = '';
						if ($data['voucher_amount'] > 0) {
							if ($data['voucher_type'] == "percentage") {
								$data_raw['total_print']['voucher_percentage'] = number_format($data['voucher_amount'], 0) . "%";
								$data['voucher_amount'] = $data['total_w_tax'] * ($data['voucher_amount'] / 100);
								$data_raw['total']['voucher_amount'] = $data['voucher_amount'];
								$data_raw['total']['voucher_amount1'] = merchantApp::prettyPrice($data['voucher_amount']);
								$data_raw['total']['promo_name'] = $promo_name;
								$data_raw['total']['voucher_type'] = $data['voucher_type'];
							}
							if ($data['voucher_type'] == "fixed amount") {
								$data_raw['total_print']['voucher_percentage'] = number_format($data['voucher_amount'], 0) . "%";
								$data['voucher_amount'] = $data['voucher_amount'];
								$data_raw['total']['voucher_amount'] = $data['voucher_amount'];
								$data_raw['total']['voucher_amount1'] = merchantApp::prettyPrice($data['voucher_amount']);
								$data_raw['total']['promo_name'] = $promo_name;
								$data_raw['total']['voucher_type'] = $data['voucher_type'];
							}


							$data_raw['total_print']['voucher_amount'] = $data['voucher_amount'];
							$data_raw['total_print']['voucher_amount1'] = prettyFormat($data['voucher_amount']);

							$data_raw['total_print']['voucher_type'] = $data['voucher_type'];
							$data_raw['total_print']['promo_name'] = $promo_name;

						}

						if ($data['discounted_amount'] > 0) {
							$data_raw['total_print']['discounted_amount'] = $data['discounted_amount'];
							$data_raw['total_print']['discounted_amount1'] = prettyFormat($data['discounted_amount']);
							$data_raw['total_print']['discount_percentage'] = number_format($data['discount_percentage'], 0) . "%";
							$data_raw['total_print']['subtotal'] = prettyFormat($data['sub_total'] + $data['voucher_amount']);
						}

						/*less points_discount*/
						if (isset($data['points_discount'])) {
							if ($data['points_discount'] > 0) {
								$data_raw['total_print']['points_discount'] = $data['points_discount'];
								$data_raw['total_print']['points_discount1'] = prettyFormat($data['points_discount']);
								$data_raw['total_print']['subtotal'] = prettyFormat($data['sub_total']);
							}
						}

						/*tips*/
						if ($data['cart_tip_value'] > 0) {
							$data_raw['total_print']['cart_tip_value'] = $data['cart_tip_value'];
							$data_raw['total_print']['cart_tip_value'] = prettyFormat($data['cart_tip_value']);
							$data_raw['total_print']['cart_tip_percentage'] = number_format($data['cart_tip_percentage'], 0) . "%";
						}

						// Total Price Print
						$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data_raw['total']['subtotal']);


						//$data_raw['total']['subtotal'] = 000;

						$data_raw['total']['subtotal1'] = $data['sub_total'];
						$data_raw['total']['subtotal2'] = merchantApp::prettyPrice($data['sub_total']);
						$data_raw['total']['taxable_total'] = merchantApp::prettyPrice($data['taxable_total']);
						$data_raw['total']['delivery_charges'] = merchantApp::prettyPrice($data_raw['total']['delivery_charges']);

						$data_raw['total']['total'] = merchantApp::prettyPrice($data['total_w_tax']);

						$data_raw['total']['tax_amt'] = $data_raw['total']['tax_amt'] . "%";
						$data_raw['total']['merchant_packaging_charge'] = merchantApp::prettyPrice($data_raw['total']['merchant_packaging_charge']);

						if ($data['order_change'] > 0) {
							$data_raw['total']['order_change'] = merchantApp::prettyPrice($data['order_change']);
						}

						//dump($data);
						//$data_raw['total']['promo_name']='';
						//if ($data['voucher_amount']>0){
						//	  if ( $data['voucher_type']=="percentage"){
						//	      print_r($data); exit('dd');
						//	  	  $data_raw['total']['voucher_percentage']=number_format($data['voucher_amount'],0)."%";
						//	  	 echo  $data['voucher_amount']=$data['total_w_tax'] * ($data['voucher_amount']/100);
						//	  }						  	  
						//    $data_raw['total']['voucher_amount']=$data['voucher_amount'];
						//    $data_raw['total']['voucher_amount1']=merchantApp::prettyPrice($data['voucher_amount']);

						//    $data_raw['total']['voucher_type']=$data['voucher_type'];
						//    $data_raw['total']['promo_name']=$promo_name;
						//}
						//print_r($data_raw); echo "kk";print_r($data); exit('dd');
						if ($data['discounted_amount'] > 0) {
							$data_raw['total']['discounted_amount'] = $data['discounted_amount'];
							$data_raw['total']['discounted_amount1'] = merchantApp::prettyPrice($data['discounted_amount']);
							$data_raw['total']['discount_percentage'] = number_format($data['discount_percentage'], 0) . "%";
							//	 $data_raw['total']['subtotal']=merchantApp::prettyPrice($data['sub_total']+$data['voucher_amount']);						  	
							$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data['sub_total']);
						}

						//if( $data['voucher_amount'] > 0 )
						//                   {
						//                         $data_raw['total']['subtotal']=merchantApp::prettyPrice($data['sub_total']+$data['voucher_amount']);
						//                   }
						/*less points_discount*/
						if (isset($data['points_discount'])) {
							if ($data['points_discount'] > 0) {
								$data_raw['total']['points_discount'] = $data['points_discount'];
								$data_raw['total']['points_discount1'] = merchantApp::prettyPrice($data['points_discount']);
								$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data['sub_total']);
							}
						}

						/*tips*/
						if ($data['cart_tip_value'] > 0) {
							$data_raw['total']['cart_tip_value'] = $data['cart_tip_value'];
							$data_raw['total']['cart_tip_value'] = merchantApp::prettyPrice($data['cart_tip_value']);
							$data_raw['total']['cart_tip_percentage'] = number_format($data['cart_tip_percentage'], 0) . "%";
						}

						$pos = Yii::app()->functions->getOptionAdmin('admin_currency_position');
						$data_raw['currency_position'] = $pos;

						$delivery_date = $data['delivery_date'];

						$data_raw['transaction_date'] = Yii::app()->functions->FormatDateTime($data['date_created']);
						$data_raw['print_transaction_date'] = Yii::app()->functions->FormatDateTime($data['date_created'], false);
						$data_raw['print_transaction_time'] = Yii::app()->functions->timeFormat($data['date_created'], true);



						$data_raw['delivery_date'] = Yii::app()->functions->FormatDateTime($delivery_date, false);



						$data_raw['doordash_drive_pickup_date'] = Yii::app()->functions->FormatDateTime($data['doordash_drive_pickup_date'], false);

						//$data_raw['delivery_time'] = $data['delivery_time'];


						$data_raw['delivery_time'] = Yii::app()->functions->timeFormat($data['delivery_time'], true);

						//$data_raw['delivery_time'] = FunctionsV3::prettyTime( date("h:i:s", strtotime("+". $time_in_select ." min",  strtotime( date("Y-m-d h:i:s") )  ))  ,true);

						//exit('fff');

						$data_raw['doordash_drive_pickup_time'] = Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);

						$merchant_info = Yii::app()->functions->getMerchant(isset($data['merchant_id']) ? $data['merchant_id'] : '');

						$present_date = date("M j, Y");

						if ($merchant_info['service'] == 8 && $data['trans_type'] == 'delivery') {



							if (!empty($data_raw['delivery_date'])) {

								//if(strtotime($data_raw['delivery_date']) == strtotime($present_date)  ){
								if (strtotime($data_raw['delivery_date']) == strtotime($present_date) && $data['pickup_in'] != '' && $data['pickup_in'] != NULL) {
									//Time not displaying in order details when auto confirm is off.
									$data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);
									//exit('if');
									//change 11-27-2023
									//$data_raw['delivery_time'] =  Today  .'-'.Yii::app()->functions->timeFormat($data['delivery_time'],true);
								} elseif (strtotime($data_raw['delivery_date']) == strtotime($present_date) && strtotime($data_raw['delivery_time']) < strtotime($data_raw['doordash_drive_pickup_time'])) {

									//when auto confirm is ON this will be used for delivery time in orders detail API. 
									$data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);
									//exit('elseif');

								} else {
									// $data_raw['delivery_time'] =   $data_raw['delivery_date']  .'-'. Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'],true);
									$data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['delivery_time'], true);
									//exit('else');
								}
								//exit;  
							} else {

								$data_raw['delivery_time'] = $data_raw['delivery_date'];
							}


						} else {



							if (!empty($data_raw['delivery_date'])) {
								$time_in_select = Yii::app()->functions->getOption("merchant_auto_prep_time", $data['merchant_id']);

								$delivery_time = date("h:i:s", strtotime("+" . $time_in_select . " min", strtotime(date("Y-m-d h:i:s"))));

								if (strtotime($data_raw['delivery_date']) == strtotime($present_date)) {
									//change 11-24-2023 time is again static 4:00 on auto confirm disable $time_in_select is empty
									// $data_raw['delivery_time'] =   Today  .'-'. Yii::app()->functions->timeFormat($delivery_time,true);
									$data_raw['delivery_time'] = Today . '-' . $data_raw['delivery_time'];


								} else {
									$data_raw['delivery_time'] = $data_raw['delivery_date'] . '-' . Yii::app()->functions->timeFormat($data['delivery_time'], true);

								}


							} else {
								$data_raw['delivery_time'] = $data_raw['delivery_date'];
							}

						}


						$data_raw['delivery_asap_raw'] = $data['delivery_asap'];
						$data_raw['delivery_asap'] = $data['delivery_asap'] == 1 ? t("Yes") : "";
						$is_confirm_email_triggered = Yii::app()->functions->getOption("is_confirm_email_triggered_" . $data['order_id'], $data['merchant_id']);
						$data_raw['is_confirm_email_triggered'] = ($is_confirm_email_triggered == 'true') ? $is_confirm_email_triggered : "false";
						$merchant_time_interval = Yii::app()->functions->getOption("merchant_time_interval", $data['merchant_id']);
						$asap_status = Yii::app()->functions->getOption('order_asap', $data['merchant_id']);
						$data_raw['merchant_enabled_auto_confirm_asap'] = ($asap_status == '2') ? 'true' : 'false';
						$data_raw['merchant_enabled_auto_confirm_prep_time'] = ($merchant_time_interval == '' || $merchant_time_interval == 0) ? '15' : $merchant_time_interval;//Yii::app()->functions->getOption("merchant_enabled_auto_confirm_prep_time",$data['merchant_id']);

						$data_raw['status_raw'] = strtolower($data['status']);
						$data_raw['status'] = $this->t($data['status']);
						$data_raw['pickup_in'] = $data['pickup_in'];

						$data_raw['trans_type_raw'] = $data['trans_type'];
						$data_raw['trans_type'] = t($data['trans_type']);

						$data_raw['payment_type_raw'] = strtoupper($data['payment_type']);
						$data_raw['payment_type'] = strtoupper(t($data['payment_type']));
						$data_raw['viewed'] = $data['viewed'];
						$data_raw['auto_printed'] = $data['auto_printed'];
						$data_raw['order_id'] = $data['order_id'];
						$data_raw['payment_provider_name'] = $data['payment_provider_name'];

						$data_raw['delivery_instruction'] = $data['delivery_instruction'];

						$data_raw['dinein_number_of_guest'] = $data['dinein_number_of_guest'];
						$data_raw['dinein_special_instruction'] = $data['dinein_special_instruction'];
						$data_raw['dinein_table_number'] = $data['dinein_table_number'];
						$data_raw['merchant_name'] = $data['merchant_name'];
						$data_raw['delivery_service_type'] = $data['delivery_service_type'];
						if ($data['delivery_service_type'] == 'grubhub' || $data['delivery_service_type'] == 'doordash' || $data['delivery_service_type'] == 'ubereats') {
							$customer_details = json_decode($data['delivery_service_client_details'], true);
							$name = ucwords($customer_details['name']);
							$phone = $customer_details['phone'];
							$first_name = ucwords($customer_details['name']);
						} else {
							$name = $data['full_name'];
							$phone = $data['contact_phone'];
							$first_name = $data['first_name'];
						}
						$data_raw['client_info'] = array(
							'full_name' => $name,
							'first_name' => $first_name,
							'email_address' => $data['email_address'],
							'address' => $data['client_full_address'],
							'location_name' => $data['location_name1'],
							'contact_phone' => $phone
						);
						if ($data['trans_type'] == "delivery") {
							if (!empty($data['contact_phone1'])) {
								$data_raw['client_info']['contact_phone'] = $data['contact_phone1'];
							}
						}

						if ($data['trans_type'] == "delivery") {
							if ($delivery_info = merchantApp::getDeliveryAddressByOrderID($this->data['order_id'])) {
								if (isset($delivery_info['google_lat'])) {
									if (!empty($delivery_info['google_lat'])) {
										$data_raw['client_info']['delivery_lat'] = $delivery_info['google_lat'];
										$data_raw['client_info']['delivery_lng'] = $delivery_info['google_lng'];
										//$data_raw['client_info']['address']=$delivery_info['formatted_address'];
									} else {
										$res_lat = Yii::app()->functions->geodecodeAddress($data['client_full_address']);
										if ($res_lat) {
											$data_raw['client_info']['delivery_lat'] = $res_lat['lat'];
											$data_raw['client_info']['delivery_lng'] = $res_lat['long'];
										} else {
											$data_raw['client_info']['delivery_lat'] = 0;
											$data_raw['client_info']['delivery_lng'] = 0;
										}
									}
								}
							}
						}

						if (FunctionsV3::hasModuleAddon("driver")) {
							if ($data_raw['trans_type_raw'] == "delivery") {
								if ($task_info = merchantApp::getTaskInfoByOrderID($data['order_id'])) {
									//dump($task_info);

									$data_raw['driver_app'] = 1;
									$data_raw['driver_id'] = $task_info['driver_id'];
									$data_raw['task_id'] = $task_info['task_id'];
									$data_raw['task_status'] = $task_info['status'];

									$data_raw['icon_location'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/racing-flag.png";
									$data_raw['icon_driver'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/car.png";
									$data_raw['icon_dropoff'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/restaurant-pin-32.png";

									$data_raw['driver_profilepic'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/user.png";

									$driver_infos = '';
									$driver_info = Driver::driverInfo($task_info['driver_id']);
									if ($driver_info) {

										if ($profile_pic = merchantApp::getDriverProfilePic($driver_info['profile_photo'])) {
											$data_raw['driver_profilepic'] = $profile_pic;
										}

										unset($driver_info['username']);
										unset($driver_info['password']);
										unset($driver_info['forgot_pass_code']);
										unset($driver_info['token']);
										unset($driver_info['date_created']);
										unset($driver_info['date_modified']);
										$driver_infos = $driver_info;

										if (method_exists("FunctionsV3", "latToAdress")) {
											$driver_address = FunctionsV3::latToAdress(
												$driver_info['location_lat'],
												$driver_info['location_lng']
											);
										} else {
											$driver_address = merchantApp::latToAdress(
												$driver_info['location_lat'],
												$driver_info['location_lng']
											);
										}
										if ($driver_address) {
											$driver_infos['formatted_address'] = $driver_address['formatted_address'];
										} else
											$driver_infos['formatted_address'] = '';
									}

									switch ($task_info['status']) {
										case "successful":
											break;

										default:
											$data_raw['task_info'] = $task_info;
											$data_raw['driver_info'] = $driver_infos;

											/*$task_distance_resp = merchantApp::getTaskDistance(
																		  isset($driver_infos['location_lat'])?$driver_infos['location_lat']:'',
																		  isset($driver_infos['location_lng'])?$driver_infos['location_lng']:'',
																		  isset($task_info['task_lat'])?$task_info['task_lat']:'',
																		  isset($task_info['task_lng'])?$task_info['task_lng']:'',
																		  isset($task_info['transport_type_id'])?$task_info['transport_type_id']:''
																		);*/
											$task_distance_resp = '';

											if ($task_distance_resp) {
												$data_raw['time_left'] = $task_distance_resp;
											} else
												$data_raw['time_left'] = merchantApp::t("N/A");


											break;
									}
								}
							}
						} else
							$data_raw['driver_app'] = 2;

						if ($data_raw['payment_type'] == "OCR" || $data_raw['payment_type'] == "ocr") {
							$_cc_info = Yii::app()->functions->getCreditCardInfo($data['cc_id']);
							$data_raw['credit_card_number'] = Yii::app()->functions->maskCardnumber(
								$_cc_info['credit_card_number']
							);

							$data_raw['cvv'] = $_cc_info['cvv'];
							$data_raw['expiry_date'] = $_cc_info['expiration_month'] . "/" . $_cc_info['expiration_yr'];

						} else
							$data_raw['credit_card_number'] = '';

						//format according to android app 
						if ($this->data['json']) {
							foreach ($data_raw['item'] as $keyy => $item) {
								$data_raw['item'][$keyy]['non_taxable'] = intval($data_raw['item'][$keyy]['non_taxable']);
								unset($data_raw['item'][$keyy]['new_sub_item']);
								$data_raw['item'][$keyy]['category_name_trans'] = "";

							}
							$data_raw['total']['delivery_charges'] = strval($data_raw['total']['delivery_charges']);
							if (!isset($data_raw['total']['cart_tip_value'])) {

								$data_raw['total']['cart_tip_value'] = "";
							}

						}

						//format according to android app  
						$this->code = 1;
						$this->msg = "OK";
						if (
							$resp = merchantApp::getDeviceInfoByUserType(
								$this->data['device_id'],
								$this->data['user_type'],
								$this->data['mtid']
							)
						) {
							$resp['food_option_not_available'] = getOption($resp['merchant_id'], 'food_option_not_available');
							$resp['merchant_close_store'] = getOption($resp['merchant_id'], 'merchant_close_store');
							$resp['merchant_show_time'] = getOption($resp['merchant_id'], 'merchant_show_time');
							$resp['merchant_disabled_ordering'] = getOption($resp['merchant_id'], 'merchant_disabled_ordering');
							$resp['merchant_enabled_voucher'] = getOption($resp['merchant_id'], 'merchant_enabled_voucher');
							$resp['merchant_required_delivery_time'] = getOption($resp['merchant_id'], 'merchant_required_delivery_time');
							$resp['merchant_enabled_tip'] = getOption($resp['merchant_id'], 'merchant_enabled_tip');

							$resp['merchant_table_booking'] = getOption($resp['merchant_id'], 'merchant_table_booking');
							$resp['accept_booking_sameday'] = getOption($resp['merchant_id'], 'accept_booking_sameday');
							$resp['printer_status'] = $resp['printer_status'];
							$resp['printer_ip'] = $resp['printer_ip'];
							$resp['printer_status'] = getOption($resp['merchant_id'], 'printer_status');
							$resp['printer_device_id'] = $resp['printer_device_id'];
							$resp['printer_timeout'] = $resp['printer_timeout'];

							$data_raw['config'] = $resp;
						}



						$this->details = $data_raw;
						// update the order id to viewed	
						if (!isset($this->data['merchant_auto_print']) && $data_raw['viewed'] != 2 && $data_raw['delivery_asap'] != 'Yes') {
							$receipt = $this->actionOrderEmail($this->data['order_id'], Yii::app()->functions->details['raw']);
							if ($receipt) {
								$to = $data['email_address'];
								//   $to='zeeshananweraziz@gmail.com';
								//   $sender = 'support@dindin.site';
								//   $subject = $recipt['subject'];
								//   $recipt= $recipt['tpl'];

								//  		FunctionsV3::notifyCustomer($data,Yii::app()->functions->additional_details,$receipt, $to);
								FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processemail"));
							}
						}

						//   if($data_raw['viewed'] !=2 ){
						//	  $confirm_link_clicked = $this->data['confirm_link_clicked']; 
						// 		  $params=array(
						// 		    'viewed'=>2
						// 		  );
						// 		  $DbExt=new DbExt;
						// 		  //$data_raw['viewed'] = 2;
						// 		  $DbExt->updateData("{{order}}",$params,'order_id',$this->data['order_id']);
						//   }	
						if ((!isset($this->data['merchant_auto_print']) && $data_raw['viewed'] != 2)) {

							$confirm_link_clicked = $this->data['confirm_link_clicked'];
							$params = array(
								'viewed' => 2,
								'confirm_link_clicked' => 1,
								'merchantapp_viewed' => 1,
							);

							Yii::app()->functions->updateOption("is_print_done_" . $this->data['order_id'], 'true', $this->data['mtid']);
							$DbExt = new DbExt;
							$DbExt->updateData("{{order}}", $params, 'order_id', $this->data['order_id']);
							//new start 12-29-2023 (Email was not sending from ASAP web order)
							if ($data['request_from'] === 'web' && $data['delivery_asap'] == 1) {
								$receipt = $this->actionOrderEmail($this->data['order_id'], Yii::app()->functions->details['raw']);
								if ($receipt) {
									$to = $data['email_address'];
									FunctionsV3::notifyCustomer($data, Yii::app()->functions->additional_details, $receipt, $to);
									FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processemail"));
								}
							}

							//new end

						}

					} else
						$this->msg = $this->t("order details not available");
				} else
					$this->msg = $this->t("order details not available");
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}


	public function actionOrderdDetailsCopy()
	{

		session_start();
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if ($data = Yii::app()->functions->getOrder2($this->data['order_id'])) {
					echo '<pre>';
					print_r($data);
					exit;
					if ($this->data['json']) {
						if (!is_array(json_decode($data['json_details']))) {
							$data['json_details'] = (array) $data['json_details'];

						}
					}
					$promo_name = '';
					if ($data['voucher_type'] != '') {
						$promo_name = 'Promo by Dindin';
					}

					$json_details = !empty($data['json_details']) ? json_decode($data['json_details'], true) : false;



					Yii::app()->functions->displayOrderHTML(
						array(
							'order_id' => $data['order_id'],
							'merchant_id' => $data['merchant_id'],
							'delivery_type' => $data['trans_type'],
							'delivery_charge' => $data['delivery_charge'],
							'packaging' => $data['packaging'],
							'cart_tip_value' => $data['cart_tip_value'],
							'cart_tip_percentage' => $data['cart_tip_percentage'] / 100,
							'card_fee' => $data['card_fee'],
							'total_w_tax' => $data['total_w_tax'],
							'tax' => $data['tax'],
							'delivery_service_type' => $data['delivery_service_type'],
							'points_discount' => isset($data['points_discount']) ? $data['points_discount'] : '' /*POINTS PROGRAM*/ ,
							'voucher_amount' => $data['voucher_amount'],
							'voucher_type' => $data['voucher_type'],
							'promo_name' => $promo_name
						),
						$json_details,
						true,
						$data['order_id'],
						'api'
					);


					if (Yii::app()->functions->code == 1) {
						$data_raw = Yii::app()->functions->details['raw'];

						$data_raw['html'] = Yii::app()->functions->details['html'];
						$data_raw['confirm_link'] = $data['confirm_link'];
						$data_raw['confirm_link_clicked'] = $data['confirm_link_clicked'];

						$sub_total = $data_raw['total']['subtotal'];


						// Total Price Print

						$data_raw['total_print']['subtotal'] = normalPrettyPrice($data_raw['total']['subtotal']);

						//   if( !isset($data['voucher_amount']) && ( $data['voucher_amount'] < 0) )
						//   {
						//         $data_raw['total_print']['subtotal'] = $data_raw['total_print']['subtotal'] + $data['voucher_amount'];
						//   }

						$data_raw['total_print']['subtotal1'] = $data['sub_total'];
						$data_raw['total_print']['subtotal2'] = prettyFormat($data['sub_total']);
						if ($data['delivery_service_type'] == 'grubhub' || $data['delivery_service_type'] == 'doordash' || $data['delivery_service_type'] == 'ubereats') {
							$data_raw['total_print']['taxable_total'] = prettyFormat($data['tax']);
						} else {
							$data_raw['total_print']['taxable_total'] = prettyFormat($data['taxable_total']);
						}

						$data_raw['total_print']['delivery_charges'] = $data_raw['total']['delivery_charges'];//prettyFormat($data_raw['total']['delivery_charges']);

						$data_raw['total_print']['total_print'] = prettyFormat($data['total_w_tax']);

						$data_raw['total_print']['tax_amt'] = $data_raw['total']['tax_amt'] . "%";
						$data_raw['total_print']['merchant_packaging_charge'] = prettyFormat($data_raw['total']['merchant_packaging_charge']);
						$data_raw['total_print']['packaging'] = prettyFormat($data['packaging']);

						if ($data['order_change'] > 0) {
							$data_raw['total_print']['order_change'] = prettyFormat($data['order_change']);
						}
						$data_raw['total_print']['promo_name'] = '';
						$data_raw['total']['voucher_amount1'] = '';
						$data_raw['total']['promo_name'] = '';
						if ($data['voucher_amount'] > 0) {
							if ($data['voucher_type'] == "percentage") {
								$data_raw['total_print']['voucher_percentage'] = number_format($data['voucher_amount'], 0) . "%";
								$data['voucher_amount'] = $data['total_w_tax'] * ($data['voucher_amount'] / 100);
								$data_raw['total']['voucher_amount'] = $data['voucher_amount'];
								$data_raw['total']['voucher_amount1'] = merchantApp::prettyPrice($data['voucher_amount']);
								$data_raw['total']['promo_name'] = $promo_name;
								$data_raw['total']['voucher_type'] = $data['voucher_type'];
							}
							if ($data['voucher_type'] == "fixed amount") {
								$data_raw['total_print']['voucher_percentage'] = number_format($data['voucher_amount'], 0) . "%";
								$data['voucher_amount'] = $data['voucher_amount'];
								$data_raw['total']['voucher_amount'] = $data['voucher_amount'];
								$data_raw['total']['voucher_amount1'] = merchantApp::prettyPrice($data['voucher_amount']);
								$data_raw['total']['promo_name'] = $promo_name;
								$data_raw['total']['voucher_type'] = $data['voucher_type'];
							}


							$data_raw['total_print']['voucher_amount'] = $data['voucher_amount'];
							$data_raw['total_print']['voucher_amount1'] = prettyFormat($data['voucher_amount']);

							$data_raw['total_print']['voucher_type'] = $data['voucher_type'];
							$data_raw['total_print']['promo_name'] = $promo_name;

						}

						if ($data['discounted_amount'] > 0) {
							$data_raw['total_print']['discounted_amount'] = $data['discounted_amount'];
							$data_raw['total_print']['discounted_amount1'] = prettyFormat($data['discounted_amount']);
							$data_raw['total_print']['discount_percentage'] = number_format($data['discount_percentage'], 0) . "%";
							$data_raw['total_print']['subtotal'] = prettyFormat($data['sub_total'] + $data['voucher_amount']);
						}

						/*less points_discount*/
						if (isset($data['points_discount'])) {
							if ($data['points_discount'] > 0) {
								$data_raw['total_print']['points_discount'] = $data['points_discount'];
								$data_raw['total_print']['points_discount1'] = prettyFormat($data['points_discount']);
								$data_raw['total_print']['subtotal'] = prettyFormat($data['sub_total']);
							}
						}

						/*tips*/
						if ($data['cart_tip_value'] > 0) {
							$data_raw['total_print']['cart_tip_value'] = $data['cart_tip_value'];
							$data_raw['total_print']['cart_tip_value'] = prettyFormat($data['cart_tip_value']);
							$data_raw['total_print']['cart_tip_percentage'] = number_format($data['cart_tip_percentage'], 0) . "%";
						}

						// Total Price Print
						$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data_raw['total']['subtotal']);


						//$data_raw['total']['subtotal'] = 000;

						$data_raw['total']['subtotal1'] = $data['sub_total'];
						$data_raw['total']['subtotal2'] = merchantApp::prettyPrice($data['sub_total']);
						$data_raw['total']['taxable_total'] = merchantApp::prettyPrice($data['taxable_total']);
						$data_raw['total']['delivery_charges'] = merchantApp::prettyPrice($data_raw['total']['delivery_charges']);

						$data_raw['total']['total'] = merchantApp::prettyPrice($data['total_w_tax']);

						$data_raw['total']['tax_amt'] = $data_raw['total']['tax_amt'] . "%";
						$data_raw['total']['merchant_packaging_charge'] = merchantApp::prettyPrice($data_raw['total']['merchant_packaging_charge']);

						if ($data['order_change'] > 0) {
							$data_raw['total']['order_change'] = merchantApp::prettyPrice($data['order_change']);
						}

						//dump($data);
						//$data_raw['total']['promo_name']='';
						//if ($data['voucher_amount']>0){
						//	  if ( $data['voucher_type']=="percentage"){
						//	      print_r($data); exit('dd');
						//	  	  $data_raw['total']['voucher_percentage']=number_format($data['voucher_amount'],0)."%";
						//	  	 echo  $data['voucher_amount']=$data['total_w_tax'] * ($data['voucher_amount']/100);
						//	  }						  	  
						//    $data_raw['total']['voucher_amount']=$data['voucher_amount'];
						//    $data_raw['total']['voucher_amount1']=merchantApp::prettyPrice($data['voucher_amount']);

						//    $data_raw['total']['voucher_type']=$data['voucher_type'];
						//    $data_raw['total']['promo_name']=$promo_name;
						//}
						//print_r($data_raw); echo "kk";print_r($data); exit('dd');
						if ($data['discounted_amount'] > 0) {
							$data_raw['total']['discounted_amount'] = $data['discounted_amount'];
							$data_raw['total']['discounted_amount1'] = merchantApp::prettyPrice($data['discounted_amount']);
							$data_raw['total']['discount_percentage'] = number_format($data['discount_percentage'], 0) . "%";
							//	 $data_raw['total']['subtotal']=merchantApp::prettyPrice($data['sub_total']+$data['voucher_amount']);						  	
							$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data['sub_total']);
						}

						//if( $data['voucher_amount'] > 0 )
						//                   {
						//                         $data_raw['total']['subtotal']=merchantApp::prettyPrice($data['sub_total']+$data['voucher_amount']);
						//                   }
						/*less points_discount*/
						if (isset($data['points_discount'])) {
							if ($data['points_discount'] > 0) {
								$data_raw['total']['points_discount'] = $data['points_discount'];
								$data_raw['total']['points_discount1'] = merchantApp::prettyPrice($data['points_discount']);
								$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data['sub_total']);
							}
						}

						/*tips*/
						if ($data['cart_tip_value'] > 0) {
							$data_raw['total']['cart_tip_value'] = $data['cart_tip_value'];
							$data_raw['total']['cart_tip_value'] = merchantApp::prettyPrice($data['cart_tip_value']);
							$data_raw['total']['cart_tip_percentage'] = number_format($data['cart_tip_percentage'], 0) . "%";
						}

						$pos = Yii::app()->functions->getOptionAdmin('admin_currency_position');
						$data_raw['currency_position'] = $pos;

						$delivery_date = $data['delivery_date'];

						$data_raw['transaction_date'] = Yii::app()->functions->FormatDateTime($data['date_created']);
						$data_raw['print_transaction_date'] = Yii::app()->functions->FormatDateTime($data['date_created'], false);
						$data_raw['print_transaction_time'] = Yii::app()->functions->timeFormat($data['date_created'], true);



						$data_raw['delivery_date'] = Yii::app()->functions->FormatDateTime($delivery_date, false);



						$data_raw['doordash_drive_pickup_date'] = Yii::app()->functions->FormatDateTime($data['doordash_drive_pickup_date'], false);

						//$data_raw['delivery_time'] = $data['delivery_time'];


						$data_raw['delivery_time'] = Yii::app()->functions->timeFormat($data['delivery_time'], true);

						//$data_raw['delivery_time'] = FunctionsV3::prettyTime( date("h:i:s", strtotime("+". $time_in_select ." min",  strtotime( date("Y-m-d h:i:s") )  ))  ,true);

						//exit('fff');

						$data_raw['doordash_drive_pickup_time'] = Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);

						$merchant_info = Yii::app()->functions->getMerchant(isset($data['merchant_id']) ? $data['merchant_id'] : '');

						$present_date = date("M j, Y");

						if ($merchant_info['service'] == 8 && $data['trans_type'] == 'delivery') {



							if (!empty($data_raw['delivery_date'])) {

								//if(strtotime($data_raw['delivery_date']) == strtotime($present_date)  ){
								if (strtotime($data_raw['delivery_date']) == strtotime($present_date) && $data['pickup_in'] != '' && $data['pickup_in'] != NULL) {
									//Time not displaying in order details when auto confirm is off.
									$data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);
									//exit('if');
									//change 11-27-2023
									//$data_raw['delivery_time'] =  Today  .'-'.Yii::app()->functions->timeFormat($data['delivery_time'],true);
								} elseif (strtotime($data_raw['delivery_date']) == strtotime($present_date) && strtotime($data_raw['delivery_time']) < strtotime($data_raw['doordash_drive_pickup_time'])) {

									//when auto confirm is ON this will be used for delivery time in orders detail API. 
									$data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);
									//exit('elseif');

								} else {
									// $data_raw['delivery_time'] =   $data_raw['delivery_date']  .'-'. Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'],true);
									$data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['delivery_time'], true);
									//exit('else');
								}
								//exit;  
							} else {

								$data_raw['delivery_time'] = $data_raw['delivery_date'];
							}


						} else {



							if (!empty($data_raw['delivery_date'])) {
								$time_in_select = Yii::app()->functions->getOption("merchant_auto_prep_time", $data['merchant_id']);

								$delivery_time = date("h:i:s", strtotime("+" . $time_in_select . " min", strtotime(date("Y-m-d h:i:s"))));

								if (strtotime($data_raw['delivery_date']) == strtotime($present_date)) {
									//change 11-24-2023 time is again static 4:00 on auto confirm disable $time_in_select is empty
									// $data_raw['delivery_time'] =   Today  .'-'. Yii::app()->functions->timeFormat($delivery_time,true);
									$data_raw['delivery_time'] = Today . '-' . $data_raw['delivery_time'];


								} else {
									$data_raw['delivery_time'] = $data_raw['delivery_date'] . '-' . Yii::app()->functions->timeFormat($data['delivery_time'], true);

								}


							} else {
								$data_raw['delivery_time'] = $data_raw['delivery_date'];
							}

						}


						$data_raw['delivery_asap_raw'] = $data['delivery_asap'];
						$data_raw['delivery_asap'] = $data['delivery_asap'] == 1 ? t("Yes") : "";

						$data_raw['merchant_enabled_auto_confirm_prep_time'] = Yii::app()->functions->getOption("merchant_enabled_auto_confirm_prep_time", $data['merchant_id']);

						$data_raw['status_raw'] = strtolower($data['status']);
						$data_raw['status'] = $this->t($data['status']);
						$data_raw['pickup_in'] = $data['pickup_in'];

						$data_raw['trans_type_raw'] = $data['trans_type'];
						$data_raw['trans_type'] = t($data['trans_type']);

						$data_raw['payment_type_raw'] = strtoupper($data['payment_type']);
						$data_raw['payment_type'] = strtoupper(t($data['payment_type']));
						$data_raw['viewed'] = $data['viewed'];
						$data_raw['auto_printed'] = $data['auto_printed'];
						$data_raw['order_id'] = $data['order_id'];
						$data_raw['payment_provider_name'] = $data['payment_provider_name'];

						$data_raw['delivery_instruction'] = $data['delivery_instruction'];

						$data_raw['dinein_number_of_guest'] = $data['dinein_number_of_guest'];
						$data_raw['dinein_special_instruction'] = $data['dinein_special_instruction'];
						$data_raw['dinein_table_number'] = $data['dinein_table_number'];
						$data_raw['merchant_name'] = $data['merchant_name'];
						$data_raw['delivery_service_type'] = $data['delivery_service_type'];
						if ($data['delivery_service_type'] == 'grubhub' || $data['delivery_service_type'] == 'doordash' || $data['delivery_service_type'] == 'ubereats') {
							$customer_details = json_decode($data['delivery_service_client_details'], true);
							$name = ucwords($customer_details['name']);
							$phone = $customer_details['phone'];
							$first_name = ucwords($customer_details['name']);
						} else {
							$name = $data['full_name'];
							$phone = $data['contact_phone'];
							$first_name = $data['first_name'];
						}
						$data_raw['client_info'] = array(
							'full_name' => $name,
							'first_name' => $first_name,
							'email_address' => $data['email_address'],
							'address' => $data['client_full_address'],
							'location_name' => $data['location_name1'],
							'contact_phone' => $phone
						);
						if ($data['trans_type'] == "delivery") {
							if (!empty($data['contact_phone1'])) {
								$data_raw['client_info']['contact_phone'] = $data['contact_phone1'];
							}
						}

						if ($data['trans_type'] == "delivery") {
							if ($delivery_info = merchantApp::getDeliveryAddressByOrderID($this->data['order_id'])) {
								if (isset($delivery_info['google_lat'])) {
									if (!empty($delivery_info['google_lat'])) {
										$data_raw['client_info']['delivery_lat'] = $delivery_info['google_lat'];
										$data_raw['client_info']['delivery_lng'] = $delivery_info['google_lng'];
										//$data_raw['client_info']['address']=$delivery_info['formatted_address'];
									} else {
										$res_lat = Yii::app()->functions->geodecodeAddress($data['client_full_address']);
										if ($res_lat) {
											$data_raw['client_info']['delivery_lat'] = $res_lat['lat'];
											$data_raw['client_info']['delivery_lng'] = $res_lat['long'];
										} else {
											$data_raw['client_info']['delivery_lat'] = 0;
											$data_raw['client_info']['delivery_lng'] = 0;
										}
									}
								}
							}
						}

						if (FunctionsV3::hasModuleAddon("driver")) {
							if ($data_raw['trans_type_raw'] == "delivery") {
								if ($task_info = merchantApp::getTaskInfoByOrderID($data['order_id'])) {
									//dump($task_info);

									$data_raw['driver_app'] = 1;
									$data_raw['driver_id'] = $task_info['driver_id'];
									$data_raw['task_id'] = $task_info['task_id'];
									$data_raw['task_status'] = $task_info['status'];

									$data_raw['icon_location'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/racing-flag.png";
									$data_raw['icon_driver'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/car.png";
									$data_raw['icon_dropoff'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/restaurant-pin-32.png";

									$data_raw['driver_profilepic'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/user.png";

									$driver_infos = '';
									$driver_info = Driver::driverInfo($task_info['driver_id']);
									if ($driver_info) {

										if ($profile_pic = merchantApp::getDriverProfilePic($driver_info['profile_photo'])) {
											$data_raw['driver_profilepic'] = $profile_pic;
										}

										unset($driver_info['username']);
										unset($driver_info['password']);
										unset($driver_info['forgot_pass_code']);
										unset($driver_info['token']);
										unset($driver_info['date_created']);
										unset($driver_info['date_modified']);
										$driver_infos = $driver_info;

										if (method_exists("FunctionsV3", "latToAdress")) {
											$driver_address = FunctionsV3::latToAdress(
												$driver_info['location_lat'],
												$driver_info['location_lng']
											);
										} else {
											$driver_address = merchantApp::latToAdress(
												$driver_info['location_lat'],
												$driver_info['location_lng']
											);
										}
										if ($driver_address) {
											$driver_infos['formatted_address'] = $driver_address['formatted_address'];
										} else
											$driver_infos['formatted_address'] = '';
									}

									switch ($task_info['status']) {
										case "successful":
											break;

										default:
											$data_raw['task_info'] = $task_info;
											$data_raw['driver_info'] = $driver_infos;

											/*$task_distance_resp = merchantApp::getTaskDistance(
																		  isset($driver_infos['location_lat'])?$driver_infos['location_lat']:'',
																		  isset($driver_infos['location_lng'])?$driver_infos['location_lng']:'',
																		  isset($task_info['task_lat'])?$task_info['task_lat']:'',
																		  isset($task_info['task_lng'])?$task_info['task_lng']:'',
																		  isset($task_info['transport_type_id'])?$task_info['transport_type_id']:''
																		);*/
											$task_distance_resp = '';

											if ($task_distance_resp) {
												$data_raw['time_left'] = $task_distance_resp;
											} else
												$data_raw['time_left'] = merchantApp::t("N/A");


											break;
									}
								}
							}
						} else
							$data_raw['driver_app'] = 2;

						if ($data_raw['payment_type'] == "OCR" || $data_raw['payment_type'] == "ocr") {
							$_cc_info = Yii::app()->functions->getCreditCardInfo($data['cc_id']);
							$data_raw['credit_card_number'] = Yii::app()->functions->maskCardnumber(
								$_cc_info['credit_card_number']
							);

							$data_raw['cvv'] = $_cc_info['cvv'];
							$data_raw['expiry_date'] = $_cc_info['expiration_month'] . "/" . $_cc_info['expiration_yr'];

						} else
							$data_raw['credit_card_number'] = '';

						//format according to android app 
						if ($this->data['json']) {
							foreach ($data_raw['item'] as $keyy => $item) {
								$data_raw['item'][$keyy]['non_taxable'] = intval($data_raw['item'][$keyy]['non_taxable']);
								unset($data_raw['item'][$keyy]['new_sub_item']);
								$data_raw['item'][$keyy]['category_name_trans'] = "";

							}
							$data_raw['total']['delivery_charges'] = strval($data_raw['total']['delivery_charges']);
							if (!isset($data_raw['total']['cart_tip_value'])) {

								$data_raw['total']['cart_tip_value'] = "";
							}

						}

						//format according to android app  
						$this->code = 1;
						$this->msg = "OK";
						if (
							$resp = merchantApp::getDeviceInfoByUserType(
								$this->data['device_id'],
								$this->data['user_type'],
								$this->data['mtid']
							)
						) {
							$resp['food_option_not_available'] = getOption($resp['merchant_id'], 'food_option_not_available');
							$resp['merchant_close_store'] = getOption($resp['merchant_id'], 'merchant_close_store');
							$resp['merchant_show_time'] = getOption($resp['merchant_id'], 'merchant_show_time');
							$resp['merchant_disabled_ordering'] = getOption($resp['merchant_id'], 'merchant_disabled_ordering');
							$resp['merchant_enabled_voucher'] = getOption($resp['merchant_id'], 'merchant_enabled_voucher');
							$resp['merchant_required_delivery_time'] = getOption($resp['merchant_id'], 'merchant_required_delivery_time');
							$resp['merchant_enabled_tip'] = getOption($resp['merchant_id'], 'merchant_enabled_tip');

							$resp['merchant_table_booking'] = getOption($resp['merchant_id'], 'merchant_table_booking');
							$resp['accept_booking_sameday'] = getOption($resp['merchant_id'], 'accept_booking_sameday');
							$resp['printer_status'] = $resp['printer_status'];
							$resp['printer_ip'] = $resp['printer_ip'];
							$resp['printer_status'] = getOption($resp['merchant_id'], 'printer_status');
							$resp['printer_device_id'] = $resp['printer_device_id'];
							$resp['printer_timeout'] = $resp['printer_timeout'];

							$data_raw['config'] = $resp;
						}
						$this->details = $data_raw;

						// update the order id to viewed	
						if (!isset($this->data['merchant_auto_print']) && $data_raw['viewed'] != 2 && $data_raw['delivery_asap'] != 'Yes') {
							$receipt = $this->actionOrderEmail($this->data['order_id'], Yii::app()->functions->details['raw']);
							if ($receipt) {
								$to = $data['email_address'];
								//   $to='zeeshananweraziz@gmail.com';
								//   $sender = 'support@dindin.site';
								//   $subject = $recipt['subject'];
								//   $recipt= $recipt['tpl'];

								FunctionsV3::notifyCustomer($data, Yii::app()->functions->additional_details, $receipt, $to);
								FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processemail"));
							}
						}
						//if(!isset($this->data['merchant_auto_print']) && $data_raw['viewed'] !=2 ){
						if ($data_raw['viewed'] != 2) {

							$confirm_link_clicked = $this->data['confirm_link_clicked'];
							$params = array(
								'viewed' => 2,
								'confirm_link_clicked' => 1,
								'merchantapp_viewed' => 1,
							);
							$DbExt = new DbExt;
							$DbExt->updateData("{{order}}", $params, 'order_id', $this->data['order_id']);

							//new start 12-29-2023 (Email was not sending from ASAP web order)
							if ($data['request_from'] === 'web' && $data['delivery_asap'] == 1) {
								$receipt = $this->actionOrderEmail($this->data['order_id'], Yii::app()->functions->details['raw']);
								if ($receipt) {
									$to = $data['email_address'];
									FunctionsV3::notifyCustomer($data, Yii::app()->functions->additional_details, $receipt, $to);
									FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processemail"));
								}
							}

							//new end

						}

					} else
						$this->msg = $this->t("order details not available");
				} else
					$this->msg = $this->t("order details not available");
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}
	public function actionOrderEmail($order_id, $raw)
	{
		$data = Yii::app()->functions->getOrder2($order_id);
		$delivery_time = $data['delivery_time'];
		if ($data['doordash_drive_tracking_link'] != '') {
			$print[] = array('label' => Yii::t("default", "Tracking Link"), 'value' => $data['doordash_drive_tracking_link']);
		}
		$print[] = array('label' => Yii::t("default", "Customer Name"), 'value' => $data['full_name']);
		$print[] = array('label' => Yii::t("default", "Merchant Name"), 'value' => $data['merchant_name']);
		$print[] = array(
			'label' => Yii::t("default", "ABN"),
			'value' => $data['abn']
		);
		$print[] = array(
			'label' => Yii::t("default", "Telephone"),
			'value' => $data['merchant_contact_phone']
		);
		$print[] = array(
			'label' => Yii::t("default", "Address"),
			'value' => $full_merchant_address
		);
		$print[] = array(
			'label' => Yii::t("default", "Tax number"),
			'value' => $merchant_tax_number
		);
		$print[] = array(
			'label' => Yii::t("default", "TRN Type"),
			'value' => t($data['trans_type'])
		);
		$print[] = array(
			'label' => Yii::t("default", "Payment Type"),
			'value' => FunctionsV3::prettyPaymentType('payment_order', $data['payment_type'], $order_id, $data['trans_type'])
		);

		if ($data['payment_provider_name']):
			$print[] = array(
				'label' => Yii::t("default", "Card#"),
				'value' => strtoupper($data['payment_provider_name'])
			);
		endif;
		$print[] = array(
			'label' => Yii::t("default", "Reference #"),
			'value' => Yii::app()->functions->formatOrderNumber($data['order_id'])
		);
		if (!empty($data['payment_reference'])):
			$print[] = array(
				'label' => Yii::t("default", "Payment Ref"),
				'value' => $data['payment_reference']
			);
		endif;

		if ($data['payment_type'] == "pyp"):
			$paypal_info = Yii::app()->functions->getPaypalOrderPayment($data['order_id']);
			$print[] = array(
				'label' => Yii::t("default", "Paypal Transaction ID"),
				'value' => isset($paypal_info['TRANSACTIONID']) ? $paypal_info['TRANSACTIONID'] : ''
			);
		endif;
		if ($data['payment_type'] == "ccr" || $data['payment_type'] == "ocr"):
			$print[] = array(
				'label' => Yii::t("default", "Card #"),
				'value' => $card
			);
		endif;

		$trn_date = FunctionsV3::prettyDate($data['date_created']) . " " . FunctionsV3::prettyTime($data['date_created']);

		$print[] = array(
			'label' => Yii::t("default", "TRN Date"),
			'value' => $trn_date
		);
		if ($data['trans_type'] == "delivery"):
			if (isset($data['delivery_date'])):
				$deliver_date = FunctionsV3::prettyDate($data['delivery_date']);
				$print[] = array(
					'label' => Yii::t("default", "Delivery Date"),
					'value' => $deliver_date
				);
			endif;
			if ($data['delivery_asap'] != 1):
				if (isset($data['delivery_time'])):
					if (!empty($data['delivery_time'])):
						$print[] = array(
							'label' => Yii::t("default", "Delivery Time"),
							'value' => $delivery_timee
						);
					endif;
				endif;
			endif;


			if ($data['delivery_asap'] == 1):
				if (isset($data['delivery_asap'])):
					if (!empty($data['delivery_asap'])):
						$print[] = array(
							'label' => Yii::t("default", "Deliver ASAP"),
							'value' => $delivery_timee
						);
					endif;
				endif;
			endif;
			if (!empty($data['client_full_address'])) {
				$delivery_address = $data['client_full_address'];
			}
			$delivery_address = $data['full_address'];
			$delivery_address = $data['client_street'] . " " . $data['client_city'] . " " . $data['client_state'] . " " . $data['client_zipcode'];
			$print[] = array(
				'label' => Yii::t("default", "Deliver to"),
				'value' => $delivery_address
			);
			$print[] = array(
				'label' => Yii::t("default", "Delivery Instruction"),
				'value' => $data['delivery_instruction']
			);
			$print[] = array(
				'label' => Yii::t("default", "Location Name"),
				'value' => $data['location_name']
			);

			if (!empty($data['contact_phone1'])) {
				$data['contact_phone'] = $data['contact_phone1'];
			}
			$print[] = array(
				'label' => Yii::t("default", "Contact Number"),
				'value' => $data['contact_phone']
			);
			if ($data['order_change'] >= 0.1):
				$print[] = array(
					'label' => Yii::t("default", "Change"),
					'value' => normalPrettyPrice($data['order_change'])
				);
			endif;
		else:
			$label_date = t("Pickup Date");
			$label_time = t("Pickup Time");
			if ($transaction_type == "dinein") {
				$label_date = t("Dine in Date");
				$label_time = t("Dine in Time");
			}

			if (isset($data['contact_phone1'])) {
				if (!empty($data['contact_phone1'])) {
					$data['contact_phone'] = $data['contact_phone1'];
				}
			}
			$print[] = array(
				'label' => Yii::t("default", "Contact Number"),
				'value' => $data['contact_phone']
			);
			if (isset($data['delivery_date'])):
				$print[] = array(
					'label' => $label_date,
					'value' => FunctionsV3::prettyDate($data['delivery_date'])
				);
			endif;
			$show_time = true;
			if (!empty($delivery_time)):
				$print[] = array(
					'label' => $label_time,
					'value' => FunctionsV3::prettyTime($delivery_time, true)
				);
			endif;
			if ($transaction_type == "dinein"):
				if ($data['order_change'] >= 0.1):
					$print[] = array(
						'label' => Yii::t("default", "Change"),
						'value' => $data['order_change']
					);
				endif;

				$print[] = array(
					'label' => t("Number of guest"),
					'value' => $data['dinein_number_of_guest']
				);
				$print[] = array(
					'label' => t("Table number"),
					'value' => $data['dinein_table_number'] > 0 ? $data['dinein_table_number'] : ''
				);
				$print[] = array(
					'label' => t("Special instructions"),
					'value' => $data['dinein_special_instruction']
				);
			endif;
		endif;
		//   $lang=Yii::app()->language;  
		// $subject = getOptionA("receipt_template_tpl_subject_$lang");
		// 	        $tpl = getOptionA("receipt_template_tpl_content_$lang");
		// 	        $receipt_html =EmailTPL::salesReceipt($print,$raw);
		// 	       $pattern=array(
		// 	   'customer_name'=>'full_name',
		// 	   'order_id'=>'order_id',
		// 	   'restaurant_name'=>'merchant_name',
		// 	   'total_amount'=>'total_w_tax',
		// 	   'sitename'=>getOptionA('website_title'),
		// 	   'siteurl'=>websiteUrl(),	    	   
		// 	   'receipt'=>$receipt_html
		// 	);
		// 	        if(!empty($subject)){
		// 			    $subject=FunctionsV3::replaceTemplateTags($subject,$pattern,$data);
		// 	        	}
		// 	        if(!empty($tpl)){  
		// 		    $tpl=FunctionsV3::replaceTemplateTags($tpl,$pattern,$data);
		// 		}

		// 		$receipt=array('subject'=>$subject,'tpl'=>$tpl);
		// print_r($receipt); exit('fg');
// 		$receipt =  "<p>Please be advised that your bank charge will display as DinDin Restaurant Order in your bank account.<br>
//                     Thank you for shopping at <b>".$data['merchant_name']."</b><br>
//                     Your order number is <b>".$data['order_id']."</b><br>
//                     We have included your order details below:</p>";
// 		print_r($print);
// 		echo "kkkkkkk";
// 		print_r($raw);
		$receipt = EmailTPL::salesReceipt($print, $raw);
		// 		$receipt .="<p>Your friends at <b>".$data['merchant_name']."</b> <br>
//                     Issues with your order?<br>
//                     Send us an email to support@dindin.site and we'll be able to assist you.</p>";
		return $receipt;

	}

	public function actionUpdateAutoPrint()
	{
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'order_id' => $this->t("order id is required"),
			'merchant_id' => $this->t("Merchant id is required"),
			//'auto_printed'=>$this->t("auto_printed is required"),
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			$params = array(
				'auto_printed' => $this->data['auto_printed']
			);

			$DbExt = new DbExt;
			if ($DbExt->updateData('{{order}}', $params, 'order_id', $this->data['order_id'])) {
				$this->code = 1;
				$this->msg = merchantApp::t("Order ID") . ":$order_id " . merchantApp::t("has been updated");
				$this->details = array(
					'order_id' => $this->data['order_id']
				);
			} else {

			}
		}
		$this->output();

	}

	public function actionAcceptOrdes()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				$merchant_id = $res['merchant_id'];
				$order_id = $this->data['order_id'];

				if (Yii::app()->functions->isMerchantCommission($merchant_id)) {
					if (FunctionsK::validateChangeOrder($order_id)) {
						$this->msg = merchantApp::t("Sorry but you cannot change the order status of this order it has reference already on the withdrawals that you made");
						$this->output();
					}
				}

				/*check if merchant can change the status*/
				$can_edit = Yii::app()->functions->getOptionAdmin('merchant_days_can_edit_status');
				if (is_numeric($can_edit) && !empty($can_edit)) {

					$date_now = date('Y-m-d');
					$base_option = getOptionA('merchant_days_can_edit_status_basedon');

					$resp = Yii::app()->functions->getOrderInfo($order_id);

					if ($base_option == 2) {
						$date_created = date(
							"Y-m-d",
							strtotime($resp['delivery_date'] . " " . $resp['delivery_time'])
						);
					} else
						$date_created = date("Y-m-d", strtotime($resp['date_created']));


					$date_interval = Yii::app()->functions->dateDifference($date_created, $date_now);
					if (is_array($date_interval) && count($date_interval) >= 1) {
						if ($date_interval['days'] > $can_edit) {
							$this->msg = merchantApp::t("Sorry but you cannot change the order status anymore. Order is lock by the website admin");
							$this->details = json_encode($date_interval);
							$this->output();
						}
					}
				}


				$order_status = 'Accepted';
				$accept_order_status = getOptionA('merchant_app_accept_order_status');
				if (!empty($accept_order_status)) {
					$order_status = $accept_order_status;
				}

				if ($resp = Yii::app()->functions->verifyOrderIdByOwner($order_id, $merchant_id)) {
					$params = array(
						'status' => $order_status,
						'date_modified' => FunctionsV3::dateNow(),
						'viewed' => 2
					);

					$DbExt = new DbExt;
					if ($DbExt->updateData('{{order}}', $params, 'order_id', $order_id)) {
						$this->code = 1;
						$this->msg = merchantApp::t("Order ID") . ":$order_id " . merchantApp::t("has been accepted");
						$this->details = array(
							'order_id' => $order_id
						);

						/*Now we insert the order history*/
						$params_history = array(
							'order_id' => $order_id,
							'status' => $order_status,
							'remarks' => isset($this->data['remarks']) ? $this->data['remarks'] : '',
							'date_created' => FunctionsV3::dateNow(),
							'ip_address' => $_SERVER['REMOTE_ADDR']
						);
						$DbExt->insertData("{{order_history}}", $params_history);


						/*UPDATE REVIEWS BASED ON STATUS*/
						if (method_exists('FunctionsV3', 'updateReviews')) {
							FunctionsV3::updateReviews($order_id, $order_status);
						}

						/*SEND NOTIFICATIONS TO CUSTOMER*/
						FunctionsV3::notifyCustomerOrderStatusChange(
							$order_id,
							$order_status,
							isset($this->data['remarks']) ? $this->data['remarks'] : ''
						);

						/*UPDATE POINTS BASED ON ORDER STATUS*/
						if (FunctionsV3::hasModuleAddon("pointsprogram")) {
							if (method_exists('PointsProgram', 'updateOrderBasedOnStatus')) {
								PointsProgram::updateOrderBasedOnStatus($order_status, $order_id);
							}
							if (method_exists('PointsProgram', 'udapteReviews')) {
								PointsProgram::udapteReviews($order_id, $order_status);
							}
						}

						/*Driver app*/
						if (FunctionsV3::hasModuleAddon("driver")) {
							Yii::app()->setImport(
								array(
									'application.modules.driver.components.*',
								)
							);
							Driver::addToTask($order_id);
						}

					} else
						$this->msg = merchantApp::t("ERROR: cannot update order.");
				} else
					$this->msg = $this->t("This Order does not belong to you");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionDeclineOrders()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				$merchant_id = $res['merchant_id'];
				$order_id = $this->data['order_id'];

				if (Yii::app()->functions->isMerchantCommission($merchant_id)) {
					if (FunctionsK::validateChangeOrder($order_id)) {
						$this->msg = merchantApp::t("Sorry but you cannot change the order status of this order it has reference already on the withdrawals that you made");
						$this->output();
					}
				}

				/*check if merchant can change the status*/
				$can_edit = Yii::app()->functions->getOptionAdmin('merchant_days_can_edit_status');
				if (is_numeric($can_edit) && !empty($can_edit)) {

					$date_now = date('Y-m-d');
					$base_option = getOptionA('merchant_days_can_edit_status_basedon');

					$resp = Yii::app()->functions->getOrderInfo($order_id);

					if ($base_option == 2) {
						$date_created = date(
							"Y-m-d",
							strtotime($resp['delivery_date'] . " " . $resp['delivery_time'])
						);
					} else
						$date_created = date("Y-m-d", strtotime($resp['date_created']));

					$date_interval = Yii::app()->functions->dateDifference($date_created, $date_now);
					if (is_array($date_interval) && count($date_interval) >= 1) {
						if ($date_interval['days'] > $can_edit) {
							$this->msg = merchantApp::t("Sorry but you cannot change the order status anymore. Order is lock by the website admin");
							$this->details = json_encode($date_interval);
							$this->output();
						}
					}
				}

				$order_status = 'Rejected';
				$app_decline_order_status = getOptionA('merchant_app_decline_order_status');
				if (!empty($app_decline_order_status)) {
					$order_status = $app_decline_order_status;
				}

				if ($resp = Yii::app()->functions->verifyOrderIdByOwner($order_id, $merchant_id)) {
					$params = array(
						'status' => $order_status,
						'date_modified' => FunctionsV3::dateNow(),
						'viewed' => 2
					);

					$DbExt = new DbExt;
					if ($DbExt->updateData('{{order}}', $params, 'order_id', $order_id)) {
						$this->code = 1;
						//$this->msg=t("order has been declined");
						$this->msg = merchantApp::t("Order ID") . ":$order_id " . merchantApp::t("has been rejected");
						$this->details = array(
							'order_id' => $order_id
						);

						/*Now we insert the order history*/
						$params_history = array(
							'order_id' => $order_id,
							'status' => $order_status,
							'remarks' => isset($this->data['remarks']) ? $this->data['remarks'] : '',
							'date_created' => FunctionsV3::dateNow(),
							'ip_address' => $_SERVER['REMOTE_ADDR']
						);
						$DbExt->insertData("{{order_history}}", $params_history);

						/*UPDATE REVIEWS BASED ON STATUS*/
						if (method_exists('FunctionsV3', 'updateReviews')) {
							FunctionsV3::updateReviews($order_id, $order_status);
						}

						/*SEND NOTIFICATIONS TO CUSTOMER*/
						FunctionsV3::notifyCustomerOrderStatusChange(
							$order_id,
							$order_status,
							isset($this->data['remarks']) ? $this->data['remarks'] : ''
						);

						/*UPDATE POINTS BASED ON ORDER STATUS*/
						if (FunctionsV3::hasModuleAddon("pointsprogram")) {
							if (method_exists('PointsProgram', 'updateOrderBasedOnStatus')) {
								PointsProgram::updateOrderBasedOnStatus($order_status, $order_id);
							}
							if (method_exists('PointsProgram', 'udapteReviews')) {
								PointsProgram::udapteReviews($order_id, $order_status);
							}
						}

					} else
						$this->msg = merchantApp::t("ERROR: cannot update order.");

				} else
					$this->msg = $this->t("This Order does not belong to you");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}
	public function actionConfirmOrderToNew()
	{
		$params = array(
			'date_modified' => FunctionsV3::dateNow()
		);
		$order_id = $_GET['order_id'];
		$DbExt = new DbExt;
		if ($DbExt->updateData('{{order}}', $params, 'order_id', $order_id)) {
			$this->code = 1;
			$this->msg = merchantApp::t("Order moved to main tab.");

			$this->output();

		} else {
			$this->msg = merchantApp::t("ERROR: cannot move to new orders.");

			$this->output();
		}
	}
	public function actionChangeOrderStatus()
	{
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required"),
			'status' => $this->t("order status is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				$merchant_id = $res['merchant_id'];
				$order_id = $this->data['order_id'];

				if (Yii::app()->functions->isMerchantCommission($merchant_id)) {
					if (FunctionsK::validateChangeOrder($order_id)) {
						$this->msg = merchantApp::t("Sorry but you cannot change the order status of this order it has reference already on the withdrawals that you made");
						$this->output();
					}
				}

				/*check if merchant can change the status*/
				$can_edit = Yii::app()->functions->getOptionAdmin('merchant_days_can_edit_status');
				if (is_numeric($can_edit) && !empty($can_edit)) {

					$date_now = date('Y-m-d');
					$base_option = getOptionA('merchant_days_can_edit_status_basedon');

					$resp = Yii::app()->functions->getOrderInfo($order_id);

					if ($base_option == 2) {
						$date_created = date(
							"Y-m-d",
							strtotime($resp['delivery_date'] . " " . $resp['delivery_time'])
						);
					} else
						$date_created = date("Y-m-d", strtotime($resp['date_created']));

					$date_interval = Yii::app()->functions->dateDifference($date_created, $date_now);
					if (is_array($date_interval) && count($date_interval) >= 1) {
						if ($date_interval['days'] > $can_edit) {
							$this->msg = merchantApp::t("Sorry but you cannot change the order status anymore. Order is lock by the website admin");
							$this->details = json_encode($date_interval);
							$this->output();
						}
					}
				}

				$order_status = $this->data['status'];

				if ($resp = Yii::app()->functions->verifyOrderIdByOwner($order_id, $merchant_id)) {
					$params = array(
						'status' => $order_status,
						'date_modified' => FunctionsV3::dateNow(),
						'viewed' => 2
					);

					$DbExt = new DbExt;
					if ($DbExt->updateData('{{order}}', $params, 'order_id', $order_id)) {
						$this->code = 1;
						$this->msg = merchantApp::t("order status successfully changed");

						/*Now we insert the order history*/
						$params_history = array(
							'order_id' => $order_id,
							'status' => $order_status,
							'remarks' => isset($this->data['remarks']) ? $this->data['remarks'] : '',
							'date_created' => FunctionsV3::dateNow(),
							'ip_address' => $_SERVER['REMOTE_ADDR']
						);
						$DbExt->insertData("{{order_history}}", $params_history);

						/*UPDATE REVIEWS BASED ON STATUS*/
						if (method_exists('FunctionsV3', 'updateReviews')) {
							FunctionsV3::updateReviews($order_id, $order_status);
						}

						/*SEND NOTIFICATIONS TO CUSTOMER*/
						FunctionsV3::notifyCustomerOrderStatusChange(
							$order_id,
							$order_status,
							isset($this->data['remarks']) ? $this->data['remarks'] : ''
						);

						/*UPDATE POINTS BASED ON ORDER STATUS*/
						if (FunctionsV3::hasModuleAddon("pointsprogram")) {
							if (method_exists('PointsProgram', 'updateOrderBasedOnStatus')) {
								PointsProgram::updateOrderBasedOnStatus($order_status, $order_id);
							}
							if (method_exists('PointsProgram', 'udapteReviews')) {
								PointsProgram::udapteReviews($order_id, $order_status);
							}
						}

						/*Driver app*/
						if (FunctionsV3::hasModuleAddon("driver")) {
							Yii::app()->setImport(
								array(
									'application.modules.driver.components.*',
								)
							);
							$_POST['status'] = $order_status;
							Driver::addToTask($order_id);
						}

					} else
						$this->msg = merchantApp::t("ERROR: cannot update order.");

				} else
					$this->msg = $this->t("This Order does not belong to you");
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionForgotPassword()
	{

		if (isset($this->data['email_address'])) {
			if (empty($this->data['email_address'])) {
				$this->msg = merchantApp::t("email address is required");
				$this->output();
			}

			if ($res = merchantApp::getUserByEmail($this->data['email_address'])) {

				$tbl = "merchant";
				if ($res['user_type'] == "user") {
					$tbl = "merchant_user";
				}
				$params = array('lost_password_code' => yii::app()->functions->generateCode());

				$DbExt = new DbExt;
				if ($DbExt->updateData("{{{$tbl}}}", $params, 'merchant_id', $res['merchant_id'])) {
					$this->code = 1;
					$this->msg = merchantApp::t("We have sent verification code in your email.");

					$tpl = EmailTPL::merchantForgotPass($res[0], $params['lost_password_code']);
					$sender = Yii::app()->functions->getOptionAdmin('website_contact_email');
					$to = $res['contact_email'];
					if (!sendEmail($to, $sender, merchantApp::t("Merchant Forgot Password"), $tpl)) {
						$email_stats = "failed";
					} else
						$email_stats = "ok mail";

					$this->details = array(
						'email_stats' => $email_stats,
						'user_type' => $res['user_type'],
						'email_address' => $this->data['email_address']
					);

				} else
					$this->msg = merchantApp::t("ERROR: Cannot update");

			} else
				$this->msg = merchantApp::t("sorry but the email address you supplied does not exist in our records");

		} else
			$this->msg = merchantApp::t("email address is required");
		$this->output();
	}

	public function actionChangePasswordWithCode()
	{


		$Validator = new Validator;
		$req = array(
			'code' => $this->t("code is required"),
			'newpass' => $this->t("new passwords is required"),
			'user_type' => t("user type is missing"),
			'email_address' => $this->t("email address is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {

			if (
				$res = merchantApp::getMerchantByCode(
					$this->data['code'],
					$this->data['email_address'],
					$this->data['user_type']
				)
			) {

				$params = array(
					'password' => md5($this->data['newpass']),
					'date_modified' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR']
				);

				$DbExt = new DbExt;
				if ($this->data['user_type'] == "admin") {
					// update merchant table
					if ($DbExt->updateData("{{merchant}}", $params, 'merchant_id', $res['merchant_id'])) {
						$this->msg = merchantApp::t("You have successfully change your password");
						$this->code = 1;
					} else
						$this->msg = merchantApp::t("ERROR: cannot update records.");
				} else {
					// update merchant user table merchant_user_id
					if ($DbExt->updateData("{{merchant_user}}", $params, 'merchant_user_id', $res['merchant_user_id'])) {
						$this->msg = merchantApp::t("You have successfully change your password");
						$this->code = 1;
					} else
						$this->msg = merchantApp::t("ERROR: cannot update records.");
				}
			} else
				$this->msg = t("verification code is invalid");

		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionRegisterMobile()
	{
		$DbExt = new DbExt;
		$params['device_id'] = isset($this->data['registrationId']) ? $this->data['registrationId'] : '';
		$params['device_platform'] = isset($this->data['device_platform']) ? $this->data['device_platform'] : '';
		$params['ip_address'] = $_SERVER['REMOTE_ADDR'];

		$user_type = 'admin';
		if (!empty($this->data['token'])) {
			if ($info = merchantApp::getUserByToken($this->data['token'])) {
				$user_type = $info['user_type'];
				$params['merchant_id'] = $info['merchant_id'];
				$params['user_type'] = $user_type;
				if ($user_type == "user") {
					$params['merchant_user_id'] = $info['merchant_user_id'];
				} else
					$params['merchant_user_id'] = 0;
			}
		}
		if ($res = merchantApp::getDeviceInfo($this->data['registrationId'])) {
			$params['date_modified'] = FunctionsV3::dateNow();
			$DbExt->updateData('{{mobile_device_merchant}}', $params, 'id', $res['id']);
			$this->code = 1;
			$this->msg = "Updated";
		} else {
			$params['date_created'] = FunctionsV3::dateNow();
			$DbExt->insertData('{{mobile_device_merchant}}', $params);
			$this->code = 1;
			$this->msg = "OK";
		}
		$this->output();
	}

	public function actionStatusList()
	{
		if (
			$res = merchantApp::validateToken(
				$this->data['mtid'],
				$this->data['token'],
				$this->data['user_type']
			)
		) {

			if (!$order_info = Yii::app()->functions->getOrder($this->data['order_id'])) {
				$this->msg = merchantApp::t("order records not found");
				$this->output();
			}

			if ($res = merchantApp::orderStatusList($this->data['mtid'])) {
				$this->details = array(
					'status' => $order_info['status'],
					'status_list' => $res
				);
				$this->code = 1;
				$this->msg = "OK";
			} else
				$this->msg = merchantApp::t("Status list not available");
		} else {
			$this->code = 3;
			$this->msg = $this->t("you session has expired or someone login with your account");
		}
		$this->output();
	}

	public function actionGetLanguageSelection()
	{
		if ($list = FunctionsV3::getEnabledLanguage()) {
			if (is_array($list) && count($list) >= 1) {
				$this->code = 1;
				$this->msg = "OK";
				$this->details = $list;
			} else
				$this->msg = merchantApp::t("no language available");
		} else
			$this->msg = merchantApp::t("no language available");

		$this->output();
	}

	public function actionSaveSettings()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'merchant_device_id' => t("mobile device id is empty please restart the app")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {


				$params = array(
					'merchant_id' => $this->data['mtid'],
					'enabled_push' => isset($this->data['enabled_push']) ? 1 : 2,
					'date_modified' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR'],
					'printer_status' => $this->data['printer_status'],
					'printer_ip' => $this->data['printer_ip'],
					'printer_device_id' => $this->data['printer_device_id'],
					'number_prints' => $this->data['number_prints'],
					'printer_timeout' => $this->data['printer_timeout'],
				);
				$DbExt = new DbExt;
				//if ( $resp=merchantApp::getDeviceInfo($this->data['merchant_device_id'])){					
				if (
					$resp = merchantApp::getDeviceInfoByUserType(
						$this->data['merchant_device_id'],
						$this->data['user_type'],
						$this->data['mtid']
					)
				) {
					//dump($resp);			
					if ($DbExt->updateData('{{mobile_device_merchant}}', $params, 'id', $resp['id'])) {
						$this->msg = $this->t("Setting saved");
						$this->code = 1;

						$details = array(
							'enabled_push' => $params['enabled_push']
						);

						$this->details = $details;

						//dump($this->data);

						$merchant_id = $this->data['mtid'];

						Yii::app()->functions->updateOption(
							"display_default_settings",
							isset($this->data['display_default_settings']) ? $this->data['display_default_settings'] : 1
							,
							$merchant_id
						);

						if (isset($this->data['food_option_not_available'])) {
							Yii::app()->functions->updateOption("food_option_not_available", 1, $merchant_id);
						}
						if (isset($this->data['food_option_not_available_disabled'])) {
							Yii::app()->functions->updateOption("food_option_not_available", 2, $merchant_id);
						}
						if (!isset($this->data['food_option_not_available']) && !isset($this->data['food_option_not_available_disabled'])) {
							Yii::app()->functions->updateOption("food_option_not_available", "", $merchant_id);
						}

						Yii::app()->functions->updateOption(
							"merchant_close_store",
							isset($this->data['merchant_close_store']) ? "yes" : ""
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_show_time",
							isset($this->data['merchant_show_time']) ? "yes" : ""
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_disabled_ordering",
							isset($this->data['merchant_disabled_ordering']) ? "yes" : ""
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_enabled_voucher",
							isset($this->data['merchant_enabled_voucher']) ? "yes" : ""
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_required_delivery_time",
							isset($this->data['merchant_required_delivery_time']) ? "yes" : ""
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_enabled_tip",
							isset($this->data['merchant_enabled_tip']) ? "2" : ""
							,
							$merchant_id
						);


						Yii::app()->functions->updateOption(
							'merchant_auto_print',
							isset($this->data['merchant_auto_print']) ? $this->data['merchant_auto_print'] : ''
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_table_booking",
							isset($this->data['merchant_table_booking']) ? "yes" : ""
							,
							$merchant_id
						);


						Yii::app()->functions->updateOption(
							"accept_booking_sameday",
							isset($this->data['accept_booking_sameday']) ? "2" : ""
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"printer_status",
							$this->data['printer_status']
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"printer_ip",
							$this->data['printer_ip']
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"bluetooth_printer",
							$this->data['bluetooth_printer']
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"printer_device_id",
							$this->data['printer_device_id']
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"printer_timeout",
							$this->data['printer_timeout']
							,
							$merchant_id
						);
						Yii::app()->functions->updateOption(
							"number_prints",
							$this->data['number_prints']
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_enabled_auto_confirm_prep_time",
							$this->data['merchant_enabled_auto_confirm_prep_time']
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_auto_prep_time",
							$this->data['merchant_auto_prep_time']
							,
							$merchant_id
						);

						Yii::app()->functions->updateOption(
							"merchant_enabled_not_display_price_on_tickets",
							$this->data['merchant_enabled_not_display_price_on_tickets']
							,
							$merchant_id
						);

					} else
						$this->msg = $this->t("ERROR: Cannot update");
				} else
					$this->msg = $this->t("Device id not found please restart the app");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionGetTrackTime()
	{
		if (isset($this->data['device_id'])) {
			//if ( $resp=merchantApp::getDeviceInfo($this->data['device_id'])){					
			if (
				$resp = merchantApp::getDeviceInfoByUserType(
					$this->data['device_id'],
					$this->data['user_type'],
					$this->data['mtid']
				)
			) {
				$merchant_id = $this->data['mtid'];
				$end_time = getOption($merchant_id, 'end_time_val');
				if ($end_time > 7200)
					$end_time = 'today';
				$store_start_date = getOption($merchant_id, 'store_start_date');
				$store_close_date = getOption($merchant_id, 'store_close_date');
				$time = time();
				$resp['in_time'] = 'no';
				$resp['end_time'] = $end_time;
				if ($time < $store_close_date && $time > $store_start_date) {
					// $this->msg=t("Sorry merchant is closed.");
					$resp['in_time'] = 'yes';
					$this->code = 1;
				}
				if (isset($this->data['json'])) {
					if ($resp['store_close_date'] == null) {
						$resp['store_close_date'] = "0000-00-00 00:00:00";
					}
					if ($resp['store_start_date'] == null) {
						$resp['store_start_date'] = "0000-00-00 00:00:00";
					}
				}
				$resp['current_time'] = date('Y-m-d G:i:s');
				$this->details = $resp;
			} else {
				$this->code = 3;
				$this->msg = $this->t("Device id not found please relogin again");
			}
		} else
			$this->msg = $this->t("Device id not found please restart the app");
		$this->output();
	}
	public function actionGetSettings()
	{
		if (isset($this->data['device_id'])) {
			//if ( $resp=merchantApp::getDeviceInfo($this->data['device_id'])){					
			if (
				$resp = merchantApp::getDeviceInfoByUserType(
					$this->data['device_id'],
					$this->data['user_type'],
					$this->data['mtid']
				)
			) {
				$this->code = 1;
				$this->msg = "OK";
				$resp['food_option_not_available'] = getOption($resp['merchant_id'], 'food_option_not_available');
				$resp['merchant_close_store'] = getOption($resp['merchant_id'], 'merchant_close_store');
				$resp['merchant_show_time'] = getOption($resp['merchant_id'], 'merchant_show_time');
				$resp['merchant_disabled_ordering'] = getOption($resp['merchant_id'], 'merchant_disabled_ordering');
				$resp['merchant_enabled_voucher'] = getOption($resp['merchant_id'], 'merchant_enabled_voucher');
				$resp['merchant_required_delivery_time'] = getOption($resp['merchant_id'], 'merchant_required_delivery_time');
				$resp['merchant_enabled_tip'] = getOption($resp['merchant_id'], 'merchant_enabled_tip');

				$resp['display_default_settings'] = getOption($resp['merchant_id'], 'display_default_settings');

				if ($resp['display_default_settings'] == '') {
					$resp['display_default_settings'] = 1;
				}

				$resp['merchant_auto_print'] = getOption($resp['merchant_id'], 'merchant_auto_print');
				$resp['bluetooth_printer'] = getOption($resp['merchant_id'], 'bluetooth_printer');
				$resp['merchant_table_booking'] = getOption($resp['merchant_id'], 'merchant_table_booking');
				$resp['accept_booking_sameday'] = getOption($resp['merchant_id'], 'accept_booking_sameday');
				$resp['printer_status'] = $resp['printer_status'];
				$resp['printer_ip'] = $resp['printer_ip'];

				if ($resp['printer_ip'] == 'null') {
					$resp['printer_ip'] = '';
				}

				$resp['merchant_enabled_auto_confirm_prep_time'] = getOption($resp['merchant_id'], 'merchant_enabled_auto_confirm_prep_time');

				if ($resp['merchant_enabled_auto_confirm_prep_time'] == '') {
					$resp['merchant_enabled_auto_confirm_prep_time'] = 0;
				}

				$resp['merchant_enabled_not_display_price_on_tickets'] = getOption($resp['merchant_id'], 'merchant_enabled_not_display_price_on_tickets');

				if ($resp['merchant_enabled_not_display_price_on_tickets'] == '') {
					$resp['merchant_enabled_not_display_price_on_tickets'] = 0;
				}


				$resp['merchant_auto_prep_time'] = getOption($resp['merchant_id'], 'merchant_auto_prep_time');

				if ($resp['merchant_auto_prep_time'] == '') {
					$resp['merchant_auto_prep_time'] = '';
				}

				$resp['printer_status'] = getOption($resp['merchant_id'], 'printer_status');
				$resp['printer_device_id'] = $resp['printer_device_id'];
				$resp['printer_timeout'] = $resp['printer_timeout'];
				$resp['number_prints'] = $resp['number_prints'];

				$this->details = $resp;
			} else {
				$this->code = 3;
				$this->msg = $this->t("Device id not found please relogin again");
			}
		} else
			$this->msg = $this->t("Device id not found please restart the app");
		$this->output();
	}

	public function actiongeoDecodeAddress()
	{

		if (isset($this->data['address'])) {
			if ($res = Yii::app()->functions->geodecodeAddress($this->data['address'])) {
				$this->code = 1;
				$this->msg = "OK";
				$res['address'] = $this->data['address'];
				$this->details = $res;
			} else
				$this->msg = merchantApp::t("Error: cannot view location");
		} else
			$this->msg = $this->t("address is required");
		$this->output();
	}

	public function actionOrderHistory()
	{
		if (!isset($this->data['order_id'])) {
			$this->msg = $this->t("order is missing");
			$this->output();
		}

		if (
			$res = merchantApp::validateToken(
				$this->data['mtid'],
				$this->data['token'],
				$this->data['user_type']
			)
		) {

			if ($res = merchantApp::getOrderHistory($this->data['order_id'])) {
				$data = [];

				foreach ($res as $val) {

					$remarks = $val['remarks'];
					if (!empty($val['remarks2']) && !empty($val['remarks_args'])) {
						$remarks_args = json_decode($val['remarks_args'], true);
						if (is_array($remarks_args) && count($remarks_args) >= 1) {
							$remarks = Yii::t("driver", $val['remarks2'], $remarks_args);
						}
					}

					$data[] = array(
						'id' => $val['id'],
						'status' => merchantApp::t($val['status']),
						'status_raw' => strtolower($val['status']),
						'remarks' => $remarks,
						'date_created' => Yii::app()->functions->FormatDateTime($val['date_created'], true),
						'ip_address' => $val['ip_address']
					);
				}
				$this->code = 1;
				$this->msg = "OK";
				$this->details = array(
					'order_id' => $this->data['order_id'],
					'data' => $data
				);
			} else {
				$this->msg = $this->t("No history found");
				//$this->details=$this->data['order_id'];
				$this->details = array('order_id' => $this->data['order_id']);
			}
		} else {
			$this->code = 3;
			$this->msg = $this->t("you session has expired or someone login with your account");
			// $this->details=$this->data['order_id'];
			$this->details = array('order_id' => $this->data['order_id']);
		}
		$this->output();
	}

	public function actionsaveProfile()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'password' => $this->t("password is required"),
			'cpassword' => $this->t("confirm password is required")
		);

		if (isset($this->data['password']) && isset($this->data['cpassword'])) {
			if ($this->data['password'] != $this->data['cpassword']) {
				$Validator->msg[] = $this->t("Confirm password does not match");
			}
		}

		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				$params = array(
					'password' => md5($this->data['password']),
					'date_modified' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR']
				);

				$DbExt = new DbExt;
				switch ($res['user_type']) {
					case "user":
						if ($DbExt->updateData('{{merchant_user}}', $params, 'merchant_user_id', $res['merchant_user_id'])) {
							$this->code = 1;
							$this->msg = $this->t("Profile saved");
						} else
							$this->msg = $this->t("ERROR: Cannot update profile");
						break;

					default:
						if ($DbExt->updateData('{{merchant}}', $params, 'merchant_id', $res['merchant_id'])) {
							$this->code = 1;
							$this->msg = $this->t("Profile saved");
						} else
							$this->msg = $this->t("ERROR: Cannot update profile");
						break;
				}
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionGetProfile()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);

		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {
				$this->code = 1;
				$this->msg = "OK";
				$this->details = $res;
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionGetLanguageSettings()
	{
		$is_login = false;
		if (isset($this->data['user_type'])) {
			if ($res = merchantApp::validateToken($this->data['mtid'], $this->data['token'], $this->data['user_type'])) {
				$res['merchant_user_id'] = isset($res['merchant_user_id']) ? $res['merchant_user_id'] : '';
				if (merchantApp::getMerchantDeviceInfoByType($res['user_type'], $res['merchant_id'], $res['merchant_user_id'])) {
					$is_login = true;
				}
			}
		}

		$lang = merchantApp::getAppLanguage();

		$default_lang = Yii::app()->language;

		$merchant_app_force_lang = getOptionA('merchant_app_force_lang');
		if (is_numeric($merchant_app_force_lang)) {
			$merchant_app_force_lang = '';
		}

		if ($default_lang == "null" || is_null($default_lang)) {
			$default_lang = 'en';
		}

		$app_decline_order_status = getOptionA('merchant_app_decline_order_status');
		if (empty($app_decline_order_status)) {
			$app_decline_order_status = 'decline';
		}
		$this->details = array(
			'default_lang' => $default_lang,
			'app_force_lang' => $merchant_app_force_lang,
			'is_login' => $is_login,
			'app_enabled_alert' => getOptionA('merchant_app_enabled_alert'),
			'app_alert_interval' => getOptionA('merchant_app_alert_interval'),
			'app_cancel_order_alert' => getOptionA('merchant_app_cancel_order_alert'),
			'app_cancel_order_alert_interval' => getOptionA('merchant_app_cancel_order_alert_interval'),
			'app_decline_order_status' => $app_decline_order_status,
			'app_keep_awake' => getOptionA('merchant_app_keep_awake'),
			'map_provider' => getOptionA('map_provider'),
			'mapbox_token' => getOptionA('mapbox_access_token'),
			'translation' => $lang
		);

		$this->msg = "OK";
		$this->code = 1;
		$this->output();
	}

	public function actiongetNotification()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);

		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if (
					$resp = merchantApp::getMerchantNotification(
						$res['merchant_id'],
						$res['user_type'],
						isset($res['merchant_user_id']) ? $res['merchant_user_id'] : ''
					)
				) {

					$data = '';
					foreach ($resp as $val) {
						$val['date_created'] = Yii::app()->functions->FormatDateTime($val['date_created'], true);
						$data[] = $val;
					}

					$this->code = 1;
					$this->msg = "OK";
					$this->details = $data;

				} else
					$this->msg = $this->t("no notifications");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionsearchOrder()
	{
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);

		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if (
					$resp = merchantApp::searchOrderByMerchantId(
						$this->data['order_id_customername'],
						$this->data['mtid']
					)
				) {

					$this->code = 1;
					$this->msg = "OK";
					foreach ($resp as $val) {
						//dump($val);
						$data[] = array(
							'order_id' => $val['order_id'],
							'customer_name' => isset($val['customer_name']) ? $val['customer_name'] : '',
							'viewed' => $val['viewed'],
							'status' => merchantApp::t($val['status']),
							'status_raw' => strtolower($val['status']),
							'trans_type' => merchantApp::t($val['trans_type']),
							'trans_type_raw' => $val['trans_type'],
							'total_w_tax' => $val['total_w_tax'],
							'total_w_tax_prety' => merchantApp::prettyPrice($val['total_w_tax']),
							'transaction_date' => Yii::app()->functions->FormatDateTime($val['date_created'], true),
							'transaction_time' => Yii::app()->functions->timeFormat($val['date_created'], true),
							'delivery_time' => Yii::app()->functions->timeFormat($val['delivery_time'], true),
							'delivery_asap' => $val['delivery_asap'] == 1 ? t("ASAP") : '',
							'delivery_date' => Yii::app()->functions->FormatDateTime($val['delivery_date'] . " " . $val['delivery_time'], true)
						);
					}
					$this->code = 1;
					$this->msg = $this->t("Search Results") . " (" . count($data) . ") " . $this->t("Found records");
					$this->details = $data;

				} else
					$this->msg = $this->t("no results");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionPendingBooking()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);

		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if ($res = merchantApp::getPendingTables($this->data['mtid'])) {
					$this->code = 1;
					$this->msg = "OK";
					$data = array();
					foreach ($res as $val) {
						$val['status_raw'] = strtolower($val['status']);
						$val['status'] = $this->t($val['status']);
						$val['date_of_booking'] = Yii::app()->functions->FormatDateTime($val['date_booking'] .
							" " . $val['booking_time'], true);
						$data[] = $val;
					}
					$this->details = $data;
				} else
					$this->msg = $this->t("no pending booking");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionAllBooking()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);

		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if ($res = merchantApp::getAllBooking($this->data['mtid'])) {
					$this->code = 1;
					$this->msg = "OK";
					$data = array();
					foreach ($res as $val) {
						$val['status_raw'] = strtolower($val['status']);
						$val['status'] = $this->t($val['status']);
						$val['date_of_booking'] = Yii::app()->functions->FormatDateTime($val['date_booking'] .
							" " . $val['booking_time'], true);
						$data[] = $val;
					}
					$this->details = $data;
				} else
					$this->msg = $this->t("no current booking");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionGetBookingDetails()
	{

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);

		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if ($res = merchantApp::getBookingDetails($this->data['mtid'], $this->data['booking_id'])) {
					$res['status_raw'] = strtolower($res['status']);
					$res['date_of_booking'] = Yii::app()->functions->FormatDateTime($res['date_booking'] .
						" " . $res['booking_time'], true);

					$res['transaction_date'] = Yii::app()->functions->FormatDateTime($res['date_created'], true);
					$res['date_booking'] = Yii::app()->functions->FormatDateTime($res['date_booking'], false);


					$res['status'] = merchantApp::t($res['status']);

					$this->code = 1;
					$this->msg = "OK";
					$this->details = array(
						'booking_id' => $this->data['booking_id'],
						'data' => $res
					);

					$params = array(
						'viewed' => 2
					);
					$DbExt = new DbExt;
					$DbExt->updateData('{{bookingtable}}', $params, 'booking_id', $this->data['booking_id']);

				} else
					$this->msg = $this->t("booking details not available");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionBookingChangeStats()
	{
		/*$this->code=1;
			  $this->msg="ok";
			  $this->output(); 		
			  Yii::app()->end();*/

		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);

		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if ($res = merchantApp::getBookingDetails($this->data['mtid'], $this->data['booking_id'])) {

					$params = array(
						'status' => $this->data['status'],
						'date_modified' => FunctionsV3::dateNow(),
						'ip_address' => $_SERVER['REMOTE_ADDR']
					);

					/*dump($this->data);			
								dump($res);
								die();*/

					$DbExt = new DbExt;
					if ($DbExt->updateData('{{bookingtable}}', $params, 'booking_id', $this->data['booking_id'])) {
						$this->code = 1;
						$this->msg = $this->t("Booking id #") . $this->data['booking_id'] .
							" " . $this->t($this->data['status']);

						switch ($this->data['status']) {
							case "approved":
								$subject = getOptionA('tpl_booking_approved_title');
								$content = getOptionA('tpl_booking_approved_content');

								break;

							default:
								$subject = getOptionA('tpl_booking_denied_title');
								$content = getOptionA('tpl_booking_denied_content');
								break;
						}


						$res['remarks'] = $this->data['remarks'];
						$res['status'] = $this->data['status'];

						/*NOTIFY CUSTOMER*/
						FunctionsV3::updateBookingNotify($res);

						/*POINTS PROGRAM*/
						if (FunctionsV3::hasModuleAddon("pointsprogram")) {
							PointsProgram::updateBookTable($this->data['booking_id'], $this->data['status']);
						}

					} else
						$this->msg = merchantApp::t("ERROR: Cannot update");

				} else
					$this->msg = $this->t("booking details not available");

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionloadTeamList()
	{
		if ($res = merchantApp::getTeamByMerchantID($this->data['mtid'])) {
			$this->msg = "OK";
			$this->code = 1;
			$this->details = $res;
		} else
			$this->msg = $this->t("You dont have current team");
		$this->output();
	}

	public function actionDriverList()
	{
		if (FunctionsV3::hasModuleAddon("driver")) {
			Yii::app()->setImport(
				array(
					'application.modules.driver.components.*',
				)
			);
			if ($res = Driver::getDriverByTeam($this->data['team_id'])) {
				$this->code = 1;
				$this->msg = "OK";
				$this->details = $res;
			} else
				$this->msg = $this->t("Team selected has no driver");
		} else
			$this->msg = $this->t("Missing addon driver app");
		$this->output();
	}

	public function actionAssignTask()
	{
		$Validator = new Validator;
		$req = array(
			'driver_id' => $this->t("Please select a driver"),
			'team_id' => $this->t("Please select a team")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {

			$DbExt = new DbExt;
			$assigned_task = 'assigned';
			$params = array(
				'team_id' => $this->data['team_id'],
				'driver_id' => $this->data['driver_id'],
				'status' => $assigned_task,
				'date_modified' => FunctionsV3::dateNow(),
				'ip_address' => $_SERVER['REMOTE_ADDR']
			);
			if ($DbExt->updateData("{{driver_task}}", $params, 'task_id', $this->data['task_id'])) {

				$this->code = 1;
				$this->msg = merchantApp::t("Successfully Assigned");
				$this->details = '';


				$DbExt->updateData("{{order}}", array(
					'status' => $assigned_task
				), 'order_id', $this->data['order_id']);

				/*add to history*/
				if ($res = Driver::getTaskId($this->data['task_id'])) {
					$status_pretty = Driver::prettyStatus($res['status'], $assigned_task);

					$remarks_args = array(
						'{from}' => $res['status'],
						'{to}' => $assigned_task
					);
					$params_history = array(
						'order_id' => $res['order_id'],
						'remarks' => $status_pretty,
						'status' => $assigned_task,
						'date_created' => FunctionsV3::dateNow(),
						'ip_address' => $_SERVER['REMOTE_ADDR'],
						'task_id' => $this->data['task_id'],
						'remarks2' => "Status updated from {from} to {to}",
						'remarks_args' => json_encode($remarks_args)
					);
					$DbExt->insertData('{{order_history}}', $params_history);
				}

				/*send notification to driver*/
				Driver::sendDriverNotification('ASSIGN_TASK', $res = Driver::getTaskId($this->data['task_id']));
				if ($res['order_id'] > 0) {
					if (FunctionsV3::hasModuleAddon("mobileapp")) {

						/** Mobile save logs for push notification */
						/*Yii::app()->setImport(array(			
											'application.modules.mobileapp.components.*',
										  ));
										  AddonMobileApp::savedOrderPushNotification(array(
											'order_id'=>$res['order_id'],
											'status'=>$res['status'],
										  ));*/
					}
				}

			} else
				$this->msg = Merchant::t("failed cannot update record");

		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionPendingBookingTab()
	{
		$this->actionPendingBooking();
	}

	public function actionprint()
	{
		$order_id = isset($this->data['order_id']) ? $this->data['order_id'] : '';
		$_GET['backend'] = true;
		$print = array();
		if ($data = Yii::app()->functions->getOrder2($order_id)) {
			$merchant_id = $data['merchant_id'];
			$json_details = !empty($data['json_details']) ? json_decode($data['json_details'], true) : false;
			if ($json_details != false) {
				Yii::app()->functions->displayOrderHTML(
					array(
						'order_id' => $order_id,
						'merchant_id' => $data['merchant_id'],
						'delivery_type' => $data['trans_type'],
						'delivery_charge' => $data['delivery_charge'],
						'packaging' => $data['packaging'],
						'cart_tip_value' => $data['cart_tip_value'],
						'cart_tip_percentage' => $data['cart_tip_percentage'] / 100,
						'card_fee' => $data['card_fee'],
						'tax' => $data['tax'],
						'points_discount' => isset($data['points_discount']) ? $data['points_discount'] : '' /*POINTS PROGRAM*/ ,
						'voucher_amount' => $data['voucher_amount'],
						'voucher_type' => $data['voucher_type']
					), $json_details, true, $order_id);
			}

			$print[] = array('label' => t("Customer Name"), 'value' => $data['full_name']);
			$print[] = array('label' => t("Merchant Name"), 'value' => $data['merchant_name']);
			if (isset($data['abn']) && !empty($data['abn'])) {
				$print[] = array(
					'label' => Yii::t("default", "ABN"),
					'value' => $data['abn']
				);
			}
			$print[] = array('label' => Yii::t("default", "Telephone"), 'value' => $data['merchant_contact_phone']);

			$merchant_info = Yii::app()->functions->getMerchant(isset($merchant_id) ? $merchant_id : '');
			$full_merchant_address = $merchant_info['street'] . " " . $merchant_info['city'] . " " . $merchant_info['state'] .
				" " . $merchant_info['post_code'];

			$print[] = array('label' => Yii::t("default", "Address"), 'value' => $full_merchant_address);

			$print[] = array('label' => Yii::t("default", "TRN Type"), 'value' => t($data['trans_type']));

			$print[] = array(
				'label' => Yii::t("default", "Payment Type"),
				'value' => FunctionsV3::prettyPaymentType('payment_order', $data['payment_type'], $order_id, $data['trans_type'])
			);

			if ($data['payment_provider_name']) {
				$print[] = array('label' => Yii::t("default", "Card#"), 'value' => strtoupper($data['payment_provider_name']));
			}

			if ($data['payment_type'] == "pyp") {
				$paypal_info = Yii::app()->functions->getPaypalOrderPayment($order_id);
				$print[] = array(
					'label' => Yii::t("default", "Paypal Transaction ID"),
					'value' => isset($paypal_info['TRANSACTIONID']) ? $paypal_info['TRANSACTIONID'] : ''
				);
			}

			$print[] = array(
				'label' => Yii::t("default", "Reference #"),
				'value' => Yii::app()->functions->formatOrderNumber($data['order_id'])
			);

			if (!empty($data['payment_reference'])) {
				$print[] = array(
					'label' => Yii::t("default", "Payment Ref"),
					'value' => isset($data['payment_reference']) ? $data['payment_reference'] : Yii::app()->functions->formatOrderNumber($data['order_id'])
				);
			}

			if ($data['payment_type'] == "ccr" || $data['payment_type'] == "ocr") {
				$print[] = array(
					'label' => Yii::t("default", "Card #"),
					'value' => Yii::app()->functions->maskCardnumber($data['credit_card_number'])
				);
			}

			$trn_date = date('M d,Y G:i:s', strtotime($data['date_created']));
			$print[] = array(
				'label' => Yii::t("default", "TRN Date"),
				'value' => $trn_date
			);

			/*dump($data);
					 dump($print);
					 die();*/

			switch ($data['trans_type']) {
				case "delivery":
					$print[] = array(
						'label' => Yii::t("default", "Delivery Date"),
						'value' => Yii::app()->functions->translateDate($data['delivery_date'])
					);

					if (!empty($data['delivery_time'])) {
						$print[] = array(
							'label' => Yii::t("default", "Delivery Time"),
							'value' => Yii::app()->functions->timeFormat($data['delivery_time'], true)
						);
					}

					if (!empty($data['delivery_asap'])) {
						$delivery_asap = $data['delivery_asap'] == 1 ? t("Yes") : '';
						$print[] = array(
							'label' => Yii::t("default", "Deliver ASAP"),
							'value' => $delivery_asap
						);
					}

					if (!empty($data['client_full_address'])) {
						$delivery_address = $data['client_full_address'];
					} else
						$delivery_address = $data['full_address'];

					$delivery_address = $data['client_street'] . " " . $data['client_city'] . " " . $data['client_state'] . " " . $data['client_zipcode'];
					$print[] = array(
						'label' => Yii::t("default", "Deliver to"),
						'value' => $delivery_address
					);

					$print[] = array(
						'label' => Yii::t("default", "Delivery Instruction"),
						'value' => $data['delivery_instruction']
					);

					$print[] = array(
						'label' => Yii::t("default", "Location Name"),
						'value' => $data['location_name']
					);

					$print[] = array(
						'label' => Yii::t("default", "Contact Number"),
						'value' => $data['contact_phone']
					);

					if ($data['order_change'] >= 0.1) {
						$print[] = array(
							'label' => Yii::t("default", "Change"),
							'value' => normalPrettyPrice($data['order_change'])
						);
					}

					break;

				case "pickup":
				case "dinein":

					$label_date = t("Pickup Date");
					$label_time = t("Pickup Time");
					if ($data['trans_type'] == "dinein") {
						$label_date = t("Dine in Date");
						$label_time = t("Dine in Time");
					}

					if (isset($data['contact_phone1'])) {
						if (!empty($data['contact_phone1'])) {
							$data['contact_phone'] = $data['contact_phone1'];
						}
					}

					$print[] = array(
						'label' => Yii::t("default", "Contact Number"),
						'value' => $data['contact_phone']
					);

					$print[] = array(
						'label' => $label_date,
						'value' => Yii::app()->functions->translateDate($data['delivery_date'])
					);

					if (!empty($data['delivery_time'])) {
						$print[] = array(
							'label' => $label_time,
							'value' => Yii::app()->functions->timeFormat($data['delivery_time'], true)
						);
					}

					if ($data['order_change'] >= 0.1) {
						$print[] = array(
							'label' => Yii::t("default", "Change"),
							'value' => normalPrettyPrice($data['order_change'])
						);
					}

					if ($data['trans_type'] == "dinein") {
						$print[] = array(
							'label' => t("Number of guest"),
							'value' => $data['dinein_number_of_guest']
						);
						$print[] = array(
							'label' => t("Special instructions"),
							'value' => $data['dinein_special_instruction']
						);
					}

					break;

				default:
					break;
			}

			/*PRINTER ADDON*/
			if (FunctionsV3::hasModuleAddon("printer")) {
				Yii::app()->setImport(array('application.modules.printer.components.*'));

				$html = getOption($merchant_id, 'mt_printer_receipt_tpl');
				if ($print_receipt = ReceiptClass::formatReceipt($html, $print, Yii::app()->functions->details['raw'], $data)) {
					PrinterClass::printReceiptMerchant($merchant_id, $data['order_id'], $print_receipt, true);
				}
				FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("printer/cron/processprint"));

				$this->msg = 1;
				$this->msg = merchantApp::t("Print request has been sent");
				$this->details = '';

			} else
				$this->msg = merchantApp::t("Printer addon not available");

		} else
			$this->msg = merchantApp::t("Order not found");
		$this->output();
	}


	public function actionisPrintDone()
	{

		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}
		if ($res = merchantApp::validateToken($this->data['mtid'], $this->data['token'], $this->data['user_type'])) {

			if ($resp = merchantApp::getUnOpenOrder($mtid)) {
				$is_print_done = Yii::app()->functions->getOption("is_print_done_" . $this->data['order_id']);
				$is_print_done = ($is_print_done == 'true') ? $is_print_done : 'false';
				$this->code = 1;
				$this->msg = "OK";
				$data = array();
				$data['is_print_done'] = $is_print_done;

				Yii::app()->functions->updateOption("is_print_done_" . $this->data['order_id'], 'true', $mtid);
				$this->details = $data;

			} else
				$this->msg = "no results";

		} else {
			$this->code = 3;
			$this->msg = $this->t("you session has expired or someone login with your account");
		}

		$this->output();
	}

	public function actiongetCountUnOpenOrder()
	{
		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}
		if ($res = merchantApp::validateToken($this->data['mtid'], $this->data['token'], $this->data['user_type'])) {

			if ($resp = merchantApp::getUnOpenOrder($mtid)) {
				$this->code = 1;
				$this->msg = Yii::t("merchantapp-backend", "[total] New Order has been placed.", array(
					'[total]' => $resp['total_unopen']
				)
				);

				$sub_message = Yii::t("merchantapp-backend", "Order id #[order_id]", array(
					'[order_id]' => $resp['order_id']
				)
				);

				$this->details = array(
					'total_unopen' => $resp['total_unopen'],
					'total_order' => $resp['total_order'],
					'sub_message' => $sub_message
				);
			} else
				$this->msg = "no results";

		} else {
			$this->code = 3;
			$this->msg = $this->t("you session has expired or someone login with your account");
		}

		$this->output();
	}

	public function actiontrackDriver()
	{
		if (
			$res = merchantApp::validateToken(
				$this->data['mtid'],
				$this->data['token'],
				$this->data['user_type']
			)
		) {
			if (FunctionsV3::hasModuleAddon("driver")) {
				$driver_id = isset($this->data['driver_id']) ? $this->data['driver_id'] : '';
				if ($driver_id > 0) {
					if ($data = Driver::driverInfo($driver_id)) {
						$this->code = 1;
						$this->msg = "OK";
						$this->details = array(
							'driver_id' => $data['driver_id'],
							'device_platform' => $data['device_platform'],
							'location_lat' => $data['location_lat'],
							'location_lng' => $data['location_lng'],
						);
					} else
						$this->msg = $this->t("No record found");
				} else
					$this->msg = $this->t("Invalid driver id");
			} else
				$this->msg = $this->t("No driver app addon found");
		} else {
			$this->code = 3;
			$this->msg = $this->t("you session has expired or someone login with your account");
		}
		$this->output();
	}

	public function actionreRegisterDevice()
	{

		$new_device_id = isset($this->data['new_device_id']) ? $this->data['new_device_id'] : '';
		if (empty($new_device_id)) {
			$this->msg = $this->t("New device id is empty");
			$this->output();
		}

		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';
		$token = isset($this->data['token']) ? $this->data['token'] : '';
		$user_type = isset($this->data['user_type']) ? $this->data['user_type'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}

		$db = new DbExt();
		if ($res = merchantApp::validateToken($mtid, $token, $user_type)) {

			$merchant_user_id = isset($res['merchant_user_id']) ? $res['merchant_user_id'] : '';

			if ($resp = merchantApp::getMerchantDeviceInfoByType($res['user_type'], $res['merchant_id'], $merchant_user_id)) {
				$resp = $resp[0];
				/*UPDDATE DEVICE ID*/
				$id = $resp['id'];
				$params = array(
					'device_id' => trim($new_device_id),
					'date_modified' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR']
				);
				if ($db->updateData("{{mobile_device_merchant}}", $params, 'id', $id)) {
					$this->code = 1;
					$this->msg = "UPDATE OK";
					$this->details = $new_device_id;
				} else
					$this->msg = Merchant::t("failed cannot update record");
			} else {
				/*INSERT TO DEVICE TABLE*/
				$params = array(
					'merchant_id' => $res['merchant_id'],
					'merchant_user_id' => isset($res['merchant_user_id']) ? $res['merchant_user_id'] : 0,
					'user_type' => $res['user_type'],
					'device_platform' => $this->data['device_platform'],
					'device_id' => trim($new_device_id),
					'enabled_push' => 1,
					'date_created' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR'],
				);
				if ($db->insertData("{{mobile_device_merchant}}", $params)) {
					$this->code = 1;
					$this->msg = "ADD OK";
					$this->details = $new_device_id;
				} else
					$this->msg = $this->t("Failed cannot insert records");
			}

		} else
			$this->t("you session has expired or someone login with your account");
		$this->output();
	}

	public function actionloadCancelOrder()
	{
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {


				$DbExt = new DbExt;
				$stmt = "
				SELECT a.*,
				(
				select concat(first_name,' ',last_name)
				from 
				{{client}}
				where
				client_id=a.client_id
				limit 0,1				
				) as customer_name
				
				FROM
				{{order}} a
				WHERE
				merchant_id=" . $this->q($res['merchant_id']) . "

				AND status in ('decline','rejected','Rejected')
				
				ORDER BY date_created DESC
				LIMIT 0,100
				";
				//dump($stmt);
				if ($res = $DbExt->rst($stmt)) {
					$this->code = 1;
					$this->msg = "OK";
					foreach ($res as $val) {
						$data[] = array(
							'cancel_order' => 1,
							'order_id' => $val['order_id'],
							'viewed' => $val['viewed'],
							'status_raw' => strtolower($val['status']),
							'status' => merchantApp::t($val['status']),
							'delivery_service_type' => $val['delivery_service_type'],
							'trans_type_raw' => $val['trans_type'],
							'trans_type' => merchantApp::t($val['trans_type']),
							'total_w_tax' => $val['total_w_tax'],
							'total_w_tax_prety' => merchantApp::prettyPrice($val['total_w_tax']),
							'transaction_date' => Yii::app()->functions->FormatDateTime($val['date_created'], true),
							'transaction_time' => Yii::app()->functions->timeFormat($val['date_created'], true),
							'delivery_time' => Yii::app()->functions->timeFormat($val['delivery_time'], true),
							'delivery_asap' => $val['delivery_asap'] == 1 ? merchantApp::t("ASAP") : '',
							'delivery_date' => Yii::app()->functions->FormatDateTime($val['delivery_date'], false),
							'customer_name' => !empty($val['customer_name']) ? $val['customer_name'] : $this->t('No name')
						);
					}

					$this->code = 1;
					$this->msg = "OK";
					$this->details = array(
						'data' => $data,
						'total_order' => count($data),
					);
				} else
					$this->msg = $this->t("no cancel orders");
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionapprovedOrder()
	{
		$order_id = isset($this->data['order_id']) ? $this->data['order_id'] : '';
		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';
		$token = isset($this->data['token']) ? $this->data['token'] : '';
		$user_type = isset($this->data['user_type']) ? $this->data['user_type'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}
		if ($order_id <= 0) {
			$this->msg = $this->t("Invalid order id");
			$this->output();
		}

		$db = new DbExt();
		if ($data = merchantApp::validateToken($mtid, $token, $user_type)) {
			if ($res = Yii::app()->functions->getOrder($order_id)) {

				$default_cancel_status = 'cancelled';
				$website_review_approved_status = getOptionA('website_review_approved_status');
				if (!empty($website_review_approved_status)) {
					$default_cancel_status = $website_review_approved_status;
				}

				$params = array(
					'request_cancel' => 2,
					'status' => $default_cancel_status,
					'request_cancel_status' => 'approved',
					'date_modified' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR']
				);


				$db = new DbExt();
				if ($db->updateData("{{order}}", $params, 'order_id', $order_id)) {

					$this->code = 1;
					$this->msg = "OK";
					$this->details = '';

					/*UPDATE REVIEWS BASED ON STATUS*/
					if (method_exists('FunctionsV3', 'updateReviews')) {
						FunctionsV3::updateReviews($order_id, $default_cancel_status);
					}

					FunctionsV3::notifyCustomerCancelOrder($res, t($params['request_cancel_status']));

					/*UPDATE POINTS BASED ON ORDER STATUS*/
					if (FunctionsV3::hasModuleAddon("pointsprogram")) {
						if (method_exists('PointsProgram', 'updateOrderBasedOnStatus')) {
							PointsProgram::updateOrderBasedOnStatus($default_cancel_status, $order_id);
						}
						if (method_exists('PointsProgram', 'udapteReviews')) {
							PointsProgram::udapteReviews($order_id, $default_cancel_status);
						}
					}

				} else
					$this->msg = t("ERROR: cannot update order.");

			} else
				$this->msg = $this->t("order records not found");
		} else {
			$this->code = 3;
			$this->msg = $this->t("you session has expired or someone login with your account");
		}
		$this->output();
	}

	public function actiondeclineOrder()
	{
		$order_id = isset($this->data['order_id']) ? $this->data['order_id'] : '';
		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';
		$token = isset($this->data['token']) ? $this->data['token'] : '';
		$user_type = isset($this->data['user_type']) ? $this->data['user_type'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}
		if ($order_id <= 0) {
			$this->msg = $this->t("Invalid order id");
			$this->output();
		}

		$db = new DbExt();
		if ($data = merchantApp::validateToken($mtid, $token, $user_type)) {
			if ($res = Yii::app()->functions->getOrder($order_id)) {

				$params = array(
					'request_cancel' => 2,
					'request_cancel_status' => 'decline',
					'date_modified' => FunctionsV3::dateNow(),
					'ip_address' => $_SERVER['REMOTE_ADDR']
				);

				$db = new DbExt();
				if ($db->updateData("{{order}}", $params, 'order_id', $order_id)) {

					$this->code = 1;
					$this->msg = "OK";
					$this->details = '';

					FunctionsV3::notifyCustomerCancelOrder($res, t($params['request_cancel_status']));

				} else
					$this->msg = t("ERROR: cannot update order.");

			} else
				$this->msg = $this->t("order records not found");
		} else {
			$this->code = 3;
			$this->msg = $this->t("you session has expired or someone login with your account");
		}
		$this->output();
	}

	public function actiongetCancelOrder()
	{
		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';
		$token = isset($this->data['token']) ? $this->data['token'] : '';
		$user_type = isset($this->data['user_type']) ? $this->data['user_type'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}

		if ($data = merchantApp::validateToken($mtid, $token, $user_type)) {

			if (method_exists("FunctionsV3", "getNewCancelOrder")) {
				$new_order_count = FunctionsV3::getNewCancelOrder($mtid);
			} else {
				$new_order_count = merchantApp::getNewCancelOrder($mtid);
			}
			if ($new_order_count) {
				$this->code = 1;
				$this->msg = Yii::t("merchantapp-backend", "You have [count] new cancel order request", array(
					'[count]' => $new_order_count
				)
				);
				$details = array(
					'count' => $new_order_count
				);
				$this->details = $details;
			} else
				$this->msg = t("no results");
		} else
			$this->msg = $this->t("you session has expired or someone login with your account");

		$this->output();
	}

	public function actioncancelOrder()
	{
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {
				if ($data = Yii::app()->functions->getOrder($this->data['order_id'])) {
					if ($merchant_info = Yii::app()->functions->getMerchant($this->data['mtid'])) {
						$subject = "Order cancelation";
						$body = $merchant_info['restaurant_name'] . " requested to cancel the order #" . $data['order_id'] . ' for ' . $data['delivery_service_type'];

						$body .= '<h4>Restaurant Details</h4>';
						$body .= '<p><b>Restauran Name: </b>' . $merchant_info['restaurant_name'] . '</p>';
						$body .= '<p><b>Restaurant Phone: </b>' . $merchant_info['restaurant_phone'] . '</p>';
						$body .= '<p><b>Customer Name: </b>' . $data["full_name"] . '</p>';
						$body .= '<p><b>Customer Phone: </b>' . $data["contact_phone"] . '</p>';

						if ($data["email_address"]) {
							// $body .= '<p><b>Customer Email: </b>' . $merchant_info['contact_email'] . '</p>';
							$body .= '<p><b>Customer Email: </b>' . $data['email_address'] . '</p>';
						}


						FunctionsV3::notifyOmniTech($subject, $body);
						$params = array(
							'status' => 'acknowledged',

						);
						$DbExt = new DbExt;
						$DbExt->updateData("{{order}}", $params, 'order_id', $this->data['order_id']);
						$status['status'] = 'acknowledge';
						$this->details = $status;
						$this->msg = $this->t("Your request has been sent");
						$this->code = 1;
					} else
						$this->msg = $this->t("Restaurant Not Found");
				} else
					$this->msg = $this->t("It's not a valid order");
			} else
				$this->msg = $this->t("Merchant id is incorrect");
		} else
			$this->msg = $this->t("you session has expired or someone login with your account");

		$this->output();
	}

	public function actionnotifyOmni()
	{
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required"),
			'amount' => $this->t("Amount is required"),
			'adjustment_reason' => $this->t("Adjust Reason is required")
		);
		//        define('YII_ENABLE_EXCEPTION_HANDLER', true);
//        ini_set("display_errors",true);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {
				if ($data = Yii::app()->functions->getOrder($this->data['order_id'])) {
					// print_r($data); 
					$from = $this->data['from'];
					$body = '';
					$subject = '';
					if ($merchant_info = Yii::app()->functions->getMerchant($this->data['mtid'])) {
						if ($from == 'chargeExtra') {
							$subject = $merchant_info['restaurant_name'] . " requested extra amount for the order # " . $data['order_id'];
							$body = "The order #" . $data['order_id'] . ' for ' . $data['delivery_service_type'] . ' has been requested for addditional amount, ' . $this->data['amount'];
						} else {
							$subject = $merchant_info['restaurant_name'] . " want to refund amount for the order # " . $data['order_id'];
							$body = "The order #" . $data['order_id'] . ' for ' . $data['delivery_service_type'] . ' want to refund amount, ' . $this->data['amount'];
						}
						$body .= '<h4>Restaurant Details</h4>';
						$body .= '<p><b>Restauran Name: </b>' . $merchant_info['restaurant_name'] . '</p>';
						//    					    print_r($data);
//    					    echo $data['client_id'];exit;
						if ($data['client_id'] == 0 || empty($data['client_id'])) {
							$client_info = json_decode($data['delivery_service_client_details']);
							$contact_name = $client_info->name;
							$contact_phone = $client_info->phone;
							$contact_email = '';
						} else {
							$client_id = $data['client_id'];
							$client_info = Yii::app()->functions->getClientInfo($client_id);
							$contact_name = $client_info['first_name'] . " " . $client_info['last_name'];
							$contact_phone = $client_info['contact_phone'];
							$contact_email = $client_info['email_address'];
						}
						$body .= '<p><b>Restaurant Phone: </b>' . $merchant_info['restaurant_phone'] . '</p>';
						$body .= '<p><b>Customer Name: </b>' . $contact_name . '</p>';
						$body .= '<p><b>Customer Phone: </b>' . $contact_phone . '</p>';
						if ($contact_email) {
							// $body .= '<p><b>Customer Email: </b>' . $merchant_info['contact_email'] . '</p>';
							$body .= '<p><b>Customer Email: </b>' . $contact_email . '</p>';
						}
						$body .= '<h4>Customer Notes & Reason</h4>';
						if ($this->data['adjustment_reason']) {
							$body .= '<br/> <b> Reason: </b>' . $this->data['adjustment_reason'];
						}
						if ($this->data['notes']) {
							$body .= '<br/> <b> Additional Notes: </b>' . $this->data['notes'];
						}
						FunctionsV3::notifyOmniTech($subject, $body);
						$this->msg = $this->t("Your request has been sent.");
						$this->code = 1;
					} else
						$this->msg = $this->t("Restaurant Not Found");
				} else
					$this->msg = $this->t("It's not a valid order");
			} else
				$this->msg = $this->t("Merchant id is incorrect");
		} else
			$this->msg = $this->t("you session has expired or someone login with your account");

		$this->output();
	}

	public function actionSalesReport()
	{

		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';
		$token = isset($this->data['token']) ? $this->data['token'] : '';
		$user_type = isset($this->data['user_type']) ? $this->data['user_type'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}

		if ($data = merchantApp::validateToken($mtid, $token, $user_type)) {
			$db = new DbExt();

			$start_date = date('Y-m-d H:i:s', strtotime($this->data['start_date']));
			$end_date = date('Y-m-d H:i:s', strtotime($this->data['end_date']));
			$data = array();

			$data['dindin'] = FunctionsV3::SumOfTotalSalesReport($mtid, $start_date, $end_date, 'dindin');
			$data['grubhub'] = FunctionsV3::SumOfTotalSalesReport($mtid, $start_date, $end_date, 'grubhub');
			$data['doordash'] = FunctionsV3::SumOfTotalSalesReport($mtid, $start_date, $end_date, 'doordash');
			$data['ubereats'] = FunctionsV3::SumOfTotalSalesReport($mtid, $start_date, $end_date, 'ubereats');
			$this->details = $data;
			$this->code = 1;
			$this->msg = "OK";
		} else
			$this->msg = $this->t("you session has expired or someone login with your account");

		$this->output();
	}
	public function actionsetAppStatus()
	{
		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';
		$token = isset($this->data['token']) ? $this->data['token'] : '';
		$user_type = isset($this->data['user_type']) ? $this->data['user_type'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}

		if ($data = merchantApp::validateToken($mtid, $token, $user_type)) {
			$db = new DbExt();
			if ($data['user_type'] == "admin") {
				$stmt = "UPDATE {{mobile_device_merchant}}
				SET app_status=" . FunctionsV3::q($this->data['app_status']) . "
				WHERE
				merchant_id=" . FunctionsV3::q($data['merchant_id']) . "				
				AND
				user_type='admin'				
				";
			} else {
				$stmt = "UPDATE {{mobile_device_merchant}}
				SET app_status=" . FunctionsV3::q($this->data['app_status']) . "
				WHERE
				merchant_id=" . FunctionsV3::q($data['merchant_id']) . "
				AND
				merchant_user_id=" . FunctionsV3::q($data['merchant_user_id']) . "	
				AND
				user_type='user'		
				";
			}
			$db->qry($stmt);
			$this->code = 1;
			$this->msg = "OK";
		} else
			$this->msg = $this->t("you session has expired or someone login with your account");

		$this->output();
	}

	public function actionclearNotification()
	{

		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';
		$token = isset($this->data['token']) ? $this->data['token'] : '';
		$user_type = isset($this->data['user_type']) ? $this->data['user_type'] : '';

		if ($mtid <= 0) {
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}
		if ($data = merchantApp::validateToken($mtid, $token, $user_type)) {
			$user_id = isset($data['merchant_user_id']) ? $data['merchant_user_id'] : '';
			if (merchantApp::clearNotification($data['user_type'], $data['merchant_id'], $user_id)) {
				$this->code = 1;
				$this->msg = "OK";
			} else
				$this->msg = $this->t("Failed cannot update records");
		} else
			$this->msg = $this->t("you session has expired or someone login with your account");

		$this->output();
	}

	public function actionthermalPrinter()
	{

		$mtid = isset($this->data['mtid']) ? $this->data['mtid'] : '';
		$html = isset($this->data['mtid']) ? $this->data['html'] : '';
		$printer_ip = isset($this->data['mtid']) ? $this->data['printer_ip'] : '';
		$print_timeout = isset($this->data['mtid']) ? $this->data['print_timeout'] : '';
		$token = isset($this->data['token']) ? $this->data['token'] : '';
		$user_type = isset($this->data['user_type']) ? $this->data['user_type'] : '';

		if ($mtid <= 0) {
			$this->code = 2;
			$this->msg = $this->t("Invalid merchant id");
			$this->output();
		}
		if ($printer_ip == '') {
			$this->code = 2;
			$this->msg = $this->t("Invalid printer ip");
			$this->output();
		}
		if ($printer_ip == '') {
			$this->code = 2;
			$this->msg = $this->t("Invalid printer ip");
			$this->output();
		}
		if ($data = merchantApp::validateToken($mtid, $token, $user_type)) {

			try {
				$connector = new NetworkPrintConnector($printer_ip, $print_timeout, 60000);
				// $connector = new NetworkPrintConnector($printer_ip);
				//   print_r($connector); exit('port'); 
				$printer = new Printer($connector);
				$printer->text($html);
				$this->msg = $this->t("Your request has been sent");
				$printer->close();
			} catch (Throwable $e) {
				$this->code = 2;
				$msg_error = 'Connection can not be established. ' . $e->getMessage();
				$this->msg = $this->t($msg_error);
			}
			// finally {
			//     $printer -> close();
			// }

		} else
			$this->msg = $this->t("you session has expired or someone login with your account");

		$this->output();
	}
	public function actiongetMobileLinks()
	{
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
		);
		$DbExt = new DbExt;
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			$stmtc = "SELECT name,link from {{mobile_links}} where status=1";
			$mobile_links = array();
			if ($resc = $DbExt->rst($stmtc)) {
				$mobile_links = $resc;
			}
			$this->msg = $this->t("Successul");
			$this->code = 1;
			$this->details = array(
				'mobile_links' => $mobile_links,
			);

		}
		$this->output();

	}

	public function actiontestApi()
	{
		$this->msg = $this->t("Successul");
		$this->code = 1;
		$this->details = array('ms' => 'dome');

		$this->output();

	}

	private function UpdateStatusPrepTime($order_id, $confirmed, $time_in_select)
	{

		$time_in_selected = $time_in_select;
		if ($data = Yii::app()->functions->getOrder2($order_id)) {
			if (is_array($data) && count($data) >= 1) {
				$merchant_id = $data['merchant_id'];
				$json_details = !empty($data['json_details']) ? json_decode($data['json_details'], true) : false;
				if ($json_details != false) {
					Yii::app()->functions->displayOrderHTML(
						array(
							'merchant_id' => $data['merchant_id'],
							'delivery_type' => $data['trans_type'],
							'delivery_charge' => $data['delivery_charge'],
							'packaging' => $data['packaging'],
							'cart_tip_value' => $data['cart_tip_value'],
							'cart_tip_percentage' => $data['cart_tip_percentage'] / 100,
							'card_fee' => $data['card_fee'],
							'tax' => $data['tax'],
							'points_discount' => isset($data['points_discount']) ? $data['points_discount'] : '' /*POINTS PROGRAM*/ ,
							'voucher_amount' => $data['voucher_amount'],
							'voucher_type' => $data['voucher_type'],
							'tax_set' => $data['tax'],
						), $json_details, true);
					if (Yii::app()->functions->code == 1) {
						$ok = true;
					}
					/*ITEM TAXABLE*/
					$mtid = $merchant_id;
					$apply_tax = $data['apply_food_tax'];
					$tax_set = $data['tax'];
					if ($apply_tax == 1 && $tax_set > 0) {
						Yii::app()->functions->details['html'] = Yii::app()->controller->renderPartial('/front/cart-with-tax', array(
							'data' => Yii::app()->functions->details['raw'],
							'tax' => $tax_set,
							'receipt' => true,
							'merchant_id' => $mtid
						), true);
					}
				}
				//  $data['confirmed'] =0; // override to validate 

				if ($data['confirmed'] == 0) {
					$time_in_selected = $time_in_selected;
					$order_id = $order_id;
					$confirmed = $confirmed;

					if ($data['delivery_asap'] == 1) {
						//   exit('hello-asap');
						// $delivery_time = date('G:i',strtotime('+'.$time_in_selected.' minutes',strtotime(date('G:i'))));
						$timezone = Yii::app()->functions->getOption("merchant_timezone", $data['merchant_id']);
						date_default_timezone_set($timezone);
						$delivery_time = date("h:i A", strtotime("+" . $time_in_select . " minutes", strtotime(date("Y-m-d h:i A"))));
						$delivery_timee = date('h:i A', strtotime('+' . $time_in_selected . ' minutes', strtotime(date('G:i'))));
					} else {
						$delivery_time = $data['delivery_time'];
						$delivery_timee = date('h:i A', strtotime($data['delivery_time']));
					}

					$merchant_info = Yii::app()->functions->getMerchant(isset($merchant_id) ? $merchant_id : '');
					if ($merchant_info['service'] == 8 && $data['trans_type'] == 'delivery') {

						$the_date = strtotime($data['delivery_date'] . " " . $delivery_time);
						date_default_timezone_set('UTC');
						$doordash_date = date('Y-m-d', $the_date);
						$doordash_time = date('G:i:s', $the_date);
						$delivery_date = $doordash_date . "T" . $doordash_time . 'Z';
						$doordash_result = FunctionsV3::createDoordashDelivery($delivery_date, $data, $merchant_info, $time_in_selected, $confirmed);
						if ($doordash_result['code'] == 2) {
							$this->code = 3;
							$this->msg = $doordash_result['msg'];
							$this->output();
						}
						$timezone = Yii::app()->functions->getOption("merchant_timezone", $merchant_id);
						if (!empty($timezone)) {
							date_default_timezone_set($timezone);
						}
						$data = Yii::app()->functions->getOrder2($order_id);
						$delivery_time = $data['delivery_time'];
						$date['doordash_drive_pickup_time'] = date('h:i A', strtotime($data['doordash_drive_pickup_time']));
						$delivery_timee = date('h:i A', strtotime($data['delivery_time']));
					} else {
						$params = array(
							'confirmed' => $confirmed,
							'pickup_in' => $time_in_selected,
							'delivery_time' => $delivery_time,
						);

						// $params['delivery_time'] = FunctionsV3::prettyTime( date("h:i:s", strtotime("+". $time_in_selected ." min",  strtotime( date("Y-m-d h:i:s") )  ))  ,true);

						$DbExt = new DbExt;
						$DbExt->updateData("{{order}}", $params, 'order_id', $order_id);
					}
					$this->code = 1;
					$this->msg = "OK";

					if ($data['doordash_drive_tracking_link'] != '') {
						$print[] = array('label' => Yii::t("default", "Tracking Link"), 'value' => $data['doordash_drive_tracking_link']);
					}
					$print[] = array('label' => Yii::t("default", "Customer Name"), 'value' => $data['full_name']);
					$print[] = array('label' => Yii::t("default", "Merchant Name"), 'value' => $data['merchant_name']);
					$print[] = array(
						'label' => Yii::t("default", "ABN"),
						'value' => $data['abn']
					);
					$print[] = array(
						'label' => Yii::t("default", "Telephone"),
						'value' => $data['merchant_contact_phone']
					);
					$print[] = array(
						'label' => Yii::t("default", "Address"),
						'value' => $full_merchant_address
					);
					$print[] = array(
						'label' => Yii::t("default", "Tax number"),
						'value' => $merchant_tax_number
					);
					$print[] = array(
						'label' => Yii::t("default", "TRN Type"),
						'value' => t($data['trans_type'])
					);
					$print[] = array(
						'label' => Yii::t("default", "Payment Type"),
						'value' => FunctionsV3::prettyPaymentType('payment_order', $data['payment_type'], $order_id, $data['trans_type'])
					);

					if ($data['payment_provider_name']):
						$print[] = array(
							'label' => Yii::t("default", "Card#"),
							'value' => strtoupper($data['payment_provider_name'])
						);
					endif;
					$print[] = array(
						'label' => Yii::t("default", "Reference #"),
						'value' => Yii::app()->functions->formatOrderNumber($data['order_id'])
					);
					if (!empty($data['payment_reference'])):
						$print[] = array(
							'label' => Yii::t("default", "Payment Ref"),
							'value' => $data['payment_reference']
						);
					endif;

					if ($data['payment_type'] == "pyp"):
						$paypal_info = Yii::app()->functions->getPaypalOrderPayment($data['order_id']);
						$print[] = array(
							'label' => Yii::t("default", "Paypal Transaction ID"),
							'value' => isset($paypal_info['TRANSACTIONID']) ? $paypal_info['TRANSACTIONID'] : ''
						);
					endif;
					if ($data['payment_type'] == "ccr" || $data['payment_type'] == "ocr"):
						$print[] = array(
							'label' => Yii::t("default", "Card #"),
							'value' => $card
						);
					endif;

					$trn_date = FunctionsV3::prettyDate($data['date_created']) . " " . FunctionsV3::prettyTime($data['date_created']);

					$print[] = array(
						'label' => Yii::t("default", "TRN Date"),
						'value' => $trn_date
					);
					if ($data['trans_type'] == "delivery"):
						if (isset($data['delivery_date'])):
							$deliver_date = FunctionsV3::prettyDate($data['delivery_date']);
							$print[] = array(
								'label' => Yii::t("default", "Delivery Date"),
								'value' => $deliver_date
							);
						endif;

						if ($data['delivery_asap'] != 1):
							if (isset($data['delivery_time'])):
								if (!empty($data['delivery_time'])):
									$print[] = array(
										'label' => Yii::t("default", "Delivery Time"),
										'value' => $delivery_timee
									);
								endif;
							endif;
						endif;


						if ($data['delivery_asap'] == 1):
							if (isset($data['delivery_asap'])):
								if (!empty($data['delivery_asap'])):
									$print[] = array(
										'label' => Yii::t("default", "Deliver ASAP"),
										'value' => $delivery_timee
									);
								endif;
							endif;
						endif;
						if (!empty($data['client_full_address'])) {
							$delivery_address = $data['client_full_address'];
						}
						$delivery_address = $data['full_address'];
						$delivery_address = $data['client_street'] . " " . $data['client_city'] . " " . $data['client_state'] . " " . $data['client_zipcode'];
						$print[] = array(
							'label' => Yii::t("default", "Deliver to"),
							'value' => $delivery_address
						);
						$print[] = array(
							'label' => Yii::t("default", "Delivery Instruction"),
							'value' => $data['delivery_instruction']
						);
						$print[] = array(
							'label' => Yii::t("default", "Location Name"),
							'value' => $data['location_name']
						);

						if (!empty($data['contact_phone1'])) {
							$data['contact_phone'] = $data['contact_phone1'];
						}
						$print[] = array(
							'label' => Yii::t("default", "Contact Number"),
							'value' => $data['contact_phone']
						);
						if ($data['order_change'] >= 0.1):
							$print[] = array(
								'label' => Yii::t("default", "Change"),
								'value' => normalPrettyPrice($data['order_change'])
							);
						endif;
					else:
						$label_date = t("Pickup Date");
						$label_time = t("Pickup Time");
						if ($transaction_type == "dinein") {
							$label_date = t("Dine in Date");
							$label_time = t("Dine in Time");
						}

						if (isset($data['contact_phone1'])) {
							if (!empty($data['contact_phone1'])) {
								$data['contact_phone'] = $data['contact_phone1'];
							}
						}
						$print[] = array(
							'label' => Yii::t("default", "Contact Number"),
							'value' => $data['contact_phone']
						);
						if (isset($data['delivery_date'])):
							$print[] = array(
								'label' => $label_date,
								'value' => FunctionsV3::prettyDate($data['delivery_date'])
							);
						endif;
						$show_time = true;

						if (isset($delivery_time) && $show_time):
							if (!empty($delivery_time)):
								$print[] = array(
									'label' => $label_time,
									'value' => FunctionsV3::prettyTime($delivery_time, true)
								);
							endif;
						endif;
						if ($transaction_type == "dinein"):
							if ($data['order_change'] >= 0.1):
								$print[] = array(
									'label' => Yii::t("default", "Change"),
									'value' => $data['order_change']
								);
							endif;

							$print[] = array(
								'label' => t("Number of guest"),
								'value' => $data['dinein_number_of_guest']
							);
							$print[] = array(
								'label' => t("Table number"),
								'value' => $data['dinein_table_number'] > 0 ? $data['dinein_table_number'] : ''
							);
							$print[] = array(
								'label' => t("Special instructions"),
								'value' => $data['dinein_special_instruction']
							);
						endif;
					endif;

					$item_details = Yii::app()->functions->details['html'];
					if ($data['delivery_service_type'] == 'dindin') {
						$data_raw = Yii::app()->functions->details['raw'];
						if ($apply_tax == 1 && $tax_set > 0) {
							$receipt = EmailTPL::salesReceiptTax($print, Yii::app()->functions->details['raw']);
						} else
							$receipt = EmailTPL::salesReceipt($print, Yii::app()->functions->details['raw']);
						$to = isset($data['email_address']) ? $data['email_address'] : '';
						// 		$to="zeeshananweraziz@gmail.com";

						/*SEND EMAIL TO CUSTOMER*/

						FunctionsV3::notifyCustomer($data, Yii::app()->functions->additional_details, $receipt, $to);

						// 		print_r($receipt); print_r($data); exit('stop'); 

						FunctionsV3::notifyMerchant($data, Yii::app()->functions->additional_details, $receipt);
						FunctionsV3::notifyAdmin($data, Yii::app()->functions->additional_details, $receipt);

						FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processemail"));
						FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processsms"));
					}
				}
			}
			return true;
		} else {
			return false;
		}

	}

	//12-06-2023 test functions

	public function actionOrderdDetails2()
	{
		// exit('123');    
		session_start();
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if (
				$res = merchantApp::validateToken(
					$this->data['mtid'],
					$this->data['token'],
					$this->data['user_type']
				)
			) {

				if ($data = Yii::app()->functions->getOrder2($this->data['order_id'])) {

					if ($this->data['json']) {
						if (!is_array(json_decode($data['json_details']))) {
							$data['json_details'] = (array) $data['json_details'];

						}
					}
					$promo_name = '';
					if ($data['voucher_type'] != '') {
						$promo_name = 'Promo by Dindin';
					}

					$json_details = !empty($data['json_details']) ? json_decode($data['json_details'], true) : false;
					Yii::app()->functions->displayOrderHTML(
						array(
							'order_id' => $data['order_id'],
							'merchant_id' => $data['merchant_id'],
							'delivery_type' => $data['trans_type'],
							'delivery_charge' => $data['delivery_charge'],
							'packaging' => $data['packaging'],
							'cart_tip_value' => $data['cart_tip_value'],
							'cart_tip_percentage' => $data['cart_tip_percentage'] / 100,
							'card_fee' => $data['card_fee'],
							'total_w_tax' => $data['total_w_tax'],
							'tax' => $data['tax'],
							'delivery_service_type' => $data['delivery_service_type'],
							'points_discount' => isset($data['points_discount']) ? $data['points_discount'] : '' /*POINTS PROGRAM*/ ,
							'voucher_amount' => $data['voucher_amount'],
							'voucher_type' => $data['voucher_type'],
							'promo_name' => $promo_name
						),
						$json_details,
						true,
						$data['order_id'],
						'api'
					);

					if (Yii::app()->functions->code == 1) {
						$data_raw = Yii::app()->functions->details['raw'];

						$data_raw['html'] = Yii::app()->functions->details['html'];
						$data_raw['confirm_link'] = $data['confirm_link'];
						$data_raw['confirm_link_clicked'] = $data['confirm_link_clicked'];

						$sub_total = $data_raw['total']['subtotal'];


						// Total Price Print

						$data_raw['total_print']['subtotal'] = normalPrettyPrice($data_raw['total']['subtotal']);

						if (!isset($data['voucher_amount']) && ($data['voucher_amount'] < 0)) {
							$data_raw['total_print']['subtotal'] = $data_raw['total_print']['subtotal'] + $data['voucher_amount'];
						}

						$data_raw['total_print']['subtotal1'] = $data['sub_total'];
						$data_raw['total_print']['subtotal2'] = prettyFormat($data['sub_total']);
						if ($data['delivery_service_type'] == 'grubhub' || $data['delivery_service_type'] == 'doordash' || $data['delivery_service_type'] == 'ubereats') {
							$data_raw['total_print']['taxable_total'] = prettyFormat($data['tax']);
						} else {
							$data_raw['total_print']['taxable_total'] = prettyFormat($data['taxable_total']);
						}

						$data_raw['total_print']['delivery_charges'] = $data_raw['total']['delivery_charges'];//prettyFormat($data_raw['total']['delivery_charges']);

						$data_raw['total_print']['total_print'] = prettyFormat($data['total_w_tax']);

						$data_raw['total_print']['tax_amt'] = $data_raw['total']['tax_amt'] . "%";
						$data_raw['total_print']['merchant_packaging_charge'] = prettyFormat($data_raw['total']['merchant_packaging_charge']);
						$data_raw['total_print']['packaging'] = prettyFormat($data['packaging']);

						if ($data['order_change'] > 0) {
							$data_raw['total_print']['order_change'] = prettyFormat($data['order_change']);
						}
						$data_raw['total_print']['promo_name'] = '';
						//dump($data);
						if ($data['voucher_amount'] > 0) {
							if ($data['voucher_type'] == "percentage") {
								$data_raw['total_print']['voucher_percentage'] = number_format($data['voucher_amount'], 0) . "%";
								$data['voucher_amount'] = $sub_total * ($data['voucher_amount'] / 100);
							}
							$data_raw['total_print']['voucher_amount'] = $data['voucher_amount'];
							$data_raw['total_print']['voucher_amount1'] = prettyFormat($data['voucher_amount']);

							$data_raw['total_print']['voucher_type'] = $data['voucher_type'];
							$data_raw['total_print']['promo_name'] = $promo_name;

						}

						if ($data['discounted_amount'] > 0) {
							$data_raw['total_print']['discounted_amount'] = $data['discounted_amount'];
							$data_raw['total_print']['discounted_amount1'] = prettyFormat($data['discounted_amount']);
							$data_raw['total_print']['discount_percentage'] = number_format($data['discount_percentage'], 0) . "%";
							$data_raw['total_print']['subtotal'] = prettyFormat($data['sub_total'] + $data['voucher_amount']);
						}

						/*less points_discount*/
						if (isset($data['points_discount'])) {
							if ($data['points_discount'] > 0) {
								$data_raw['total_print']['points_discount'] = $data['points_discount'];
								$data_raw['total_print']['points_discount1'] = prettyFormat($data['points_discount']);
								$data_raw['total_print']['subtotal'] = prettyFormat($data['sub_total']);
							}
						}

						/*tips*/
						if ($data['cart_tip_value'] > 0) {
							$data_raw['total_print']['cart_tip_value'] = $data['cart_tip_value'];
							$data_raw['total_print']['cart_tip_value'] = prettyFormat($data['cart_tip_value']);
							$data_raw['total_print']['cart_tip_percentage'] = number_format($data['cart_tip_percentage'], 0) . "%";
						}

						// Total Price Print

						$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data_raw['total']['subtotal']);


						//$data_raw['total']['subtotal'] = 000;

						$data_raw['total']['subtotal1'] = $data['sub_total'];
						$data_raw['total']['subtotal2'] = merchantApp::prettyPrice($data['sub_total']);
						$data_raw['total']['taxable_total'] = merchantApp::prettyPrice($data['taxable_total']);
						$data_raw['total']['delivery_charges'] = merchantApp::prettyPrice($data_raw['total']['delivery_charges']);

						$data_raw['total']['total'] = merchantApp::prettyPrice($data['total_w_tax']);

						$data_raw['total']['tax_amt'] = $data_raw['total']['tax_amt'] . "%";
						$data_raw['total']['merchant_packaging_charge'] = merchantApp::prettyPrice($data_raw['total']['merchant_packaging_charge']);

						if ($data['order_change'] > 0) {
							$data_raw['total']['order_change'] = merchantApp::prettyPrice($data['order_change']);
						}

						//dump($data);
						$data_raw['total']['promo_name'] = '';
						if ($data['voucher_amount'] > 0) {
							if ($data['voucher_type'] == "percentage") {
								$data_raw['total']['voucher_percentage'] = number_format($data['voucher_amount'], 0) . "%";
								$data['voucher_amount'] = $sub_total * ($data['voucher_amount'] / 100);
							}
							$data_raw['total']['voucher_amount'] = $data['voucher_amount'];
							$data_raw['total']['voucher_amount1'] = merchantApp::prettyPrice($data['voucher_amount']);

							$data_raw['total']['voucher_type'] = $data['voucher_type'];
							$data_raw['total']['promo_name'] = $promo_name;
						}

						if ($data['discounted_amount'] > 0) {
							$data_raw['total']['discounted_amount'] = $data['discounted_amount'];
							$data_raw['total']['discounted_amount1'] = merchantApp::prettyPrice($data['discounted_amount']);
							$data_raw['total']['discount_percentage'] = number_format($data['discount_percentage'], 0) . "%";
							$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data['sub_total'] + $data['voucher_amount']);
						}

						if ($data['voucher_amount'] > 0) {
							$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data['sub_total'] + $data['voucher_amount']);
						}
						/*less points_discount*/
						if (isset($data['points_discount'])) {
							if ($data['points_discount'] > 0) {
								$data_raw['total']['points_discount'] = $data['points_discount'];
								$data_raw['total']['points_discount1'] = merchantApp::prettyPrice($data['points_discount']);
								$data_raw['total']['subtotal'] = merchantApp::prettyPrice($data['sub_total']);
							}
						}

						/*tips*/
						if ($data['cart_tip_value'] > 0) {
							$data_raw['total']['cart_tip_value'] = $data['cart_tip_value'];
							$data_raw['total']['cart_tip_value'] = merchantApp::prettyPrice($data['cart_tip_value']);
							$data_raw['total']['cart_tip_percentage'] = number_format($data['cart_tip_percentage'], 0) . "%";
						}

						$pos = Yii::app()->functions->getOptionAdmin('admin_currency_position');
						$data_raw['currency_position'] = $pos;

						$delivery_date = $data['delivery_date'];

						$data_raw['transaction_date'] = Yii::app()->functions->FormatDateTime($data['date_created']);
						$data_raw['print_transaction_date'] = Yii::app()->functions->FormatDateTime($data['date_created'], false);
						$data_raw['print_transaction_time'] = Yii::app()->functions->timeFormat($data['date_created'], true);



						$data_raw['delivery_date'] = Yii::app()->functions->FormatDateTime($delivery_date, false);



						$data_raw['doordash_drive_pickup_date'] = Yii::app()->functions->FormatDateTime($data['doordash_drive_pickup_date'], false);

						//$data_raw['delivery_time'] = $data['delivery_time'];


						$data_raw['delivery_time'] = Yii::app()->functions->timeFormat($data['delivery_time'], true);

						//$data_raw['delivery_time'] = FunctionsV3::prettyTime( date("h:i:s", strtotime("+". $time_in_select ." min",  strtotime( date("Y-m-d h:i:s") )  ))  ,true);

						//exit('fff');

						$data_raw['doordash_drive_pickup_time'] = Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);

						$merchant_info = Yii::app()->functions->getMerchant(isset($data['merchant_id']) ? $data['merchant_id'] : '');

						$present_date = date("M j, Y");

						if ($merchant_info['service'] == 8 && $data['trans_type'] == 'delivery') {

							if (!empty($data_raw['delivery_date'])) {

								//if(strtotime($data_raw['delivery_date']) == strtotime($present_date)  ){
								if (strtotime($data_raw['delivery_date']) == strtotime($present_date) && $data['pickup_in'] != '' && $data['pickup_in'] != NULL) {
									//Time not displaying in order details when auto confirm is off.
									$data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);
									//exit('if');
									//change 11-27-2023
									//$data_raw['delivery_time'] =  Today  .'-'.Yii::app()->functions->timeFormat($data['delivery_time'],true);
								} elseif (strtotime($data_raw['delivery_date']) == strtotime($present_date) && strtotime($data_raw['delivery_time']) < strtotime($data_raw['doordash_drive_pickup_time'])) {

									//when auto confirm is ON this will be used for delivery time in orders detail API. 
									echo $data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'], true);
									exit('elseif');

								} else {
									// $data_raw['delivery_time'] =   $data_raw['delivery_date']  .'-'. Yii::app()->functions->timeFormat($data['doordash_drive_pickup_time'],true);
									$data_raw['delivery_time'] = Today . '-' . Yii::app()->functions->timeFormat($data['delivery_time'], true);
									//exit('else');
								}
								//exit;  
							} else {

								$data_raw['delivery_time'] = $data_raw['delivery_date'];
							}


						} else {



							if (!empty($data_raw['delivery_date'])) {
								$time_in_select = Yii::app()->functions->getOption("merchant_auto_prep_time", $data['merchant_id']);

								$delivery_time = date("h:i:s", strtotime("+" . $time_in_select . " min", strtotime(date("Y-m-d h:i:s"))));

								if (strtotime($data_raw['delivery_date']) == strtotime($present_date)) {
									//change 11-24-2023 time is again static 4:00 on auto confirm disable $time_in_select is empty
									// $data_raw['delivery_time'] =   Today  .'-'. Yii::app()->functions->timeFormat($delivery_time,true);
									$data_raw['delivery_time'] = Today . '-' . $data_raw['delivery_time'];


								} else {
									$data_raw['delivery_time'] = $data_raw['delivery_date'] . '-' . Yii::app()->functions->timeFormat($data['delivery_time'], true);

								}


							} else {
								$data_raw['delivery_time'] = $data_raw['delivery_date'];
							}

						}


						$data_raw['delivery_asap_raw'] = $data['delivery_asap'];
						$data_raw['delivery_asap'] = $data['delivery_asap'] == 1 ? t("Yes") : "";

						$data_raw['merchant_enabled_auto_confirm_prep_time'] = Yii::app()->functions->getOption("merchant_enabled_auto_confirm_prep_time", $data['merchant_id']);

						$data_raw['status_raw'] = strtolower($data['status']);
						$data_raw['status'] = $this->t($data['status']);
						$data_raw['pickup_in'] = $data['pickup_in'];

						$data_raw['trans_type_raw'] = $data['trans_type'];
						$data_raw['trans_type'] = t($data['trans_type']);

						$data_raw['payment_type_raw'] = strtoupper($data['payment_type']);
						$data_raw['payment_type'] = strtoupper(t($data['payment_type']));
						$data_raw['viewed'] = $data['viewed'];
						$data_raw['auto_printed'] = $data['auto_printed'];
						$data_raw['order_id'] = $data['order_id'];
						$data_raw['payment_provider_name'] = $data['payment_provider_name'];

						$data_raw['delivery_instruction'] = $data['delivery_instruction'];

						$data_raw['dinein_number_of_guest'] = $data['dinein_number_of_guest'];
						$data_raw['dinein_special_instruction'] = $data['dinein_special_instruction'];
						$data_raw['dinein_table_number'] = $data['dinein_table_number'];
						$data_raw['merchant_name'] = $data['merchant_name'];
						$data_raw['delivery_service_type'] = $data['delivery_service_type'];
						if ($data['delivery_service_type'] == 'grubhub' || $data['delivery_service_type'] == 'doordash' || $data['delivery_service_type'] == 'ubereats') {
							$customer_details = json_decode($data['delivery_service_client_details'], true);
							$name = ucwords($customer_details['name']);
							$phone = $customer_details['phone'];
							$first_name = ucwords($customer_details['name']);
						} else {
							$name = $data['full_name'];
							$phone = $data['contact_phone'];
							$first_name = $data['first_name'];
						}
						$data_raw['client_info'] = array(
							'full_name' => $name,
							'first_name' => $first_name,
							'email_address' => $data['email_address'],
							'address' => $data['client_full_address'],
							'location_name' => $data['location_name1'],
							'contact_phone' => $phone
						);
						if ($data['trans_type'] == "delivery") {
							if (!empty($data['contact_phone1'])) {
								$data_raw['client_info']['contact_phone'] = $data['contact_phone1'];
							}
						}

						if ($data['trans_type'] == "delivery") {
							if ($delivery_info = merchantApp::getDeliveryAddressByOrderID($this->data['order_id'])) {
								if (isset($delivery_info['google_lat'])) {
									if (!empty($delivery_info['google_lat'])) {
										$data_raw['client_info']['delivery_lat'] = $delivery_info['google_lat'];
										$data_raw['client_info']['delivery_lng'] = $delivery_info['google_lng'];
										//$data_raw['client_info']['address']=$delivery_info['formatted_address'];
									} else {
										$res_lat = Yii::app()->functions->geodecodeAddress($data['client_full_address']);
										if ($res_lat) {
											$data_raw['client_info']['delivery_lat'] = $res_lat['lat'];
											$data_raw['client_info']['delivery_lng'] = $res_lat['long'];
										} else {
											$data_raw['client_info']['delivery_lat'] = 0;
											$data_raw['client_info']['delivery_lng'] = 0;
										}
									}
								}
							}
						}

						if (FunctionsV3::hasModuleAddon("driver")) {
							if ($data_raw['trans_type_raw'] == "delivery") {
								if ($task_info = merchantApp::getTaskInfoByOrderID($data['order_id'])) {
									//dump($task_info);

									$data_raw['driver_app'] = 1;
									$data_raw['driver_id'] = $task_info['driver_id'];
									$data_raw['task_id'] = $task_info['task_id'];
									$data_raw['task_status'] = $task_info['status'];

									$data_raw['icon_location'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/racing-flag.png";
									$data_raw['icon_driver'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/car.png";
									$data_raw['icon_dropoff'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/restaurant-pin-32.png";

									$data_raw['driver_profilepic'] = websiteUrl() . "/protected/modules/merchantapp/assets/images/user.png";

									$driver_infos = '';
									$driver_info = Driver::driverInfo($task_info['driver_id']);
									if ($driver_info) {

										if ($profile_pic = merchantApp::getDriverProfilePic($driver_info['profile_photo'])) {
											$data_raw['driver_profilepic'] = $profile_pic;
										}

										unset($driver_info['username']);
										unset($driver_info['password']);
										unset($driver_info['forgot_pass_code']);
										unset($driver_info['token']);
										unset($driver_info['date_created']);
										unset($driver_info['date_modified']);
										$driver_infos = $driver_info;

										if (method_exists("FunctionsV3", "latToAdress")) {
											$driver_address = FunctionsV3::latToAdress(
												$driver_info['location_lat'],
												$driver_info['location_lng']
											);
										} else {
											$driver_address = merchantApp::latToAdress(
												$driver_info['location_lat'],
												$driver_info['location_lng']
											);
										}
										if ($driver_address) {
											$driver_infos['formatted_address'] = $driver_address['formatted_address'];
										} else
											$driver_infos['formatted_address'] = '';
									}

									switch ($task_info['status']) {
										case "successful":
											break;

										default:
											$data_raw['task_info'] = $task_info;
											$data_raw['driver_info'] = $driver_infos;

											/*$task_distance_resp = merchantApp::getTaskDistance(
																		  isset($driver_infos['location_lat'])?$driver_infos['location_lat']:'',
																		  isset($driver_infos['location_lng'])?$driver_infos['location_lng']:'',
																		  isset($task_info['task_lat'])?$task_info['task_lat']:'',
																		  isset($task_info['task_lng'])?$task_info['task_lng']:'',
																		  isset($task_info['transport_type_id'])?$task_info['transport_type_id']:''
																		);*/
											$task_distance_resp = '';

											if ($task_distance_resp) {
												$data_raw['time_left'] = $task_distance_resp;
											} else
												$data_raw['time_left'] = merchantApp::t("N/A");


											break;
									}
								}
							}
						} else
							$data_raw['driver_app'] = 2;

						if ($data_raw['payment_type'] == "OCR" || $data_raw['payment_type'] == "ocr") {
							$_cc_info = Yii::app()->functions->getCreditCardInfo($data['cc_id']);
							$data_raw['credit_card_number'] = Yii::app()->functions->maskCardnumber(
								$_cc_info['credit_card_number']
							);

							$data_raw['cvv'] = $_cc_info['cvv'];
							$data_raw['expiry_date'] = $_cc_info['expiration_month'] . "/" . $_cc_info['expiration_yr'];

						} else
							$data_raw['credit_card_number'] = '';

						//format according to android app 
						if ($this->data['json']) {
							foreach ($data_raw['item'] as $keyy => $item) {
								$data_raw['item'][$keyy]['non_taxable'] = intval($data_raw['item'][$keyy]['non_taxable']);
								unset($data_raw['item'][$keyy]['new_sub_item']);
								$data_raw['item'][$keyy]['category_name_trans'] = "";

							}
							$data_raw['total']['delivery_charges'] = strval($data_raw['total']['delivery_charges']);
							if (!isset($data_raw['total']['cart_tip_value'])) {

								$data_raw['total']['cart_tip_value'] = "";
							}

						}

						//format according to android app  
						$this->code = 1;
						$this->msg = "OK";
						if (
							$resp = merchantApp::getDeviceInfoByUserType(
								$this->data['device_id'],
								$this->data['user_type'],
								$this->data['mtid']
							)
						) {
							$resp['food_option_not_available'] = getOption($resp['merchant_id'], 'food_option_not_available');
							$resp['merchant_close_store'] = getOption($resp['merchant_id'], 'merchant_close_store');
							$resp['merchant_show_time'] = getOption($resp['merchant_id'], 'merchant_show_time');
							$resp['merchant_disabled_ordering'] = getOption($resp['merchant_id'], 'merchant_disabled_ordering');
							$resp['merchant_enabled_voucher'] = getOption($resp['merchant_id'], 'merchant_enabled_voucher');
							$resp['merchant_required_delivery_time'] = getOption($resp['merchant_id'], 'merchant_required_delivery_time');
							$resp['merchant_enabled_tip'] = getOption($resp['merchant_id'], 'merchant_enabled_tip');

							$resp['merchant_table_booking'] = getOption($resp['merchant_id'], 'merchant_table_booking');
							$resp['accept_booking_sameday'] = getOption($resp['merchant_id'], 'accept_booking_sameday');
							$resp['printer_status'] = $resp['printer_status'];
							$resp['printer_ip'] = $resp['printer_ip'];
							$resp['printer_status'] = getOption($resp['merchant_id'], 'printer_status');
							$resp['printer_device_id'] = $resp['printer_device_id'];
							$resp['printer_timeout'] = $resp['printer_timeout'];

							$data_raw['config'] = $resp;
						}
						$this->details = $data_raw;

						// update the order id to viewed	
						if (!isset($this->data['merchant_auto_print']) && $data_raw['viewed'] != 2 && $data_raw['delivery_asap'] != 'Yes') {
							$receipt = $this->actionOrderEmail($this->data['order_id'], Yii::app()->functions->details['raw']);
							if ($receipt) {
								$to = $data['email_address'];
								//   $to='zeeshananweraziz@gmail.com';
								//   $sender = 'support@dindin.site';
								//   $subject = $recipt['subject'];
								//   $recipt= $recipt['tpl'];

								FunctionsV3::notifyCustomer($data, Yii::app()->functions->additional_details, $receipt, $to);
								FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processemail"));

							}
						}
						if (!isset($this->data['merchant_auto_print']) && $data_raw['viewed'] != 2) {

							$confirm_link_clicked = $this->data['confirm_link_clicked'];
							$params = array(
								'viewed' => 2,
								'confirm_link_clicked' => 1,
								'merchantapp_viewed' => 1,
							);
							$DbExt = new DbExt;
							$DbExt->updateData("{{order}}", $params, 'order_id', $this->data['order_id']);

						}

					} else
						$this->msg = $this->t("order details not available");
				} else
					$this->msg = $this->t("order details not available");
			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	public function actionUpdateOrderStatus2()
	{

		session_start();
		$Validator = new Validator;
		$req = array(
			'token' => $this->t("token is required"),
			'mtid' => $this->t("merchant id is required"),
			'user_type' => $this->t("user type is required"),
			'order_id' => $this->t("order id is required")
		);
		$Validator->required($req, $this->data);
		if ($Validator->validate()) {
			if ($res = merchantApp::validateToken($this->data['mtid'], $this->data['token'], $this->data['user_type'])) {

				if ($this->UpdateStatusPrepTime2($this->data['order_id'], $this->data['confirmed'], $this->data['time_in_selected']))
					;
				else {
					$this->code = 3;
					$this->msg = $this->t("order details not available");
				}

			} else {
				$this->code = 3;
				$this->msg = $this->t("you session has expired or someone login with your account");
			}
		} else
			$this->msg = merchantApp::parseValidatorError($Validator->getError());
		$this->output();
	}

	private function UpdateStatusPrepTime2($order_id, $confirmed, $time_in_select)
	{

		$time_in_selected = $time_in_select;
		if ($data = Yii::app()->functions->getOrder2($order_id)) {
			// print_r($data);
			// exit;
			if (is_array($data) && count($data) >= 1) {
				$merchant_id = $data['merchant_id'];
				$json_details = !empty($data['json_details']) ? json_decode($data['json_details'], true) : false;
				if ($json_details != false) {
					Yii::app()->functions->displayOrderHTML(
						array(
							'merchant_id' => $data['merchant_id'],
							'delivery_type' => $data['trans_type'],
							'delivery_charge' => $data['delivery_charge'],
							'packaging' => $data['packaging'],
							'cart_tip_value' => $data['cart_tip_value'],
							'cart_tip_percentage' => $data['cart_tip_percentage'] / 100,
							'card_fee' => $data['card_fee'],
							'tax' => $data['tax'],
							'points_discount' => isset($data['points_discount']) ? $data['points_discount'] : '' /*POINTS PROGRAM*/ ,
							'voucher_amount' => $data['voucher_amount'],
							'voucher_type' => $data['voucher_type'],
							'tax_set' => $data['tax'],
						), $json_details, true);
					if (Yii::app()->functions->code == 1) {
						$ok = true;
					}
					/*ITEM TAXABLE*/
					$mtid = $merchant_id;
					$apply_tax = $data['apply_food_tax'];
					$tax_set = $data['tax'];
					if ($apply_tax == 1 && $tax_set > 0) {
						Yii::app()->functions->details['html'] = Yii::app()->controller->renderPartial('/front/cart-with-tax', array(
							'data' => Yii::app()->functions->details['raw'],
							'tax' => $tax_set,
							'receipt' => true,
							'merchant_id' => $mtid
						), true);
					}
				}
				//  $data['confirmed'] =0; // override to validate 

				if ($data['confirmed'] == 0) {
					$time_in_selected = $time_in_selected;
					$order_id = $order_id;
					$confirmed = $confirmed;

					if ($data['delivery_asap'] == 1) {

						$timezone = Yii::app()->functions->getOption("merchant_timezone", $data['merchant_id']);
						date_default_timezone_set($timezone);
						$delivery_time = date("h:i A", strtotime("+" . $time_in_select . " minutes", strtotime(date("Y-m-d h:i A"))));
						//echo $delivery_time = date('G:i',strtotime('+'.$time_in_selected.' minutes',strtotime(date('G:i'))));
						$delivery_timee = date('h:i A', strtotime('+' . $time_in_selected . ' minutes', strtotime(date('G:i'))));

					} else {
						$delivery_time = $data['delivery_time'];
						$delivery_timee = date('h:i A', strtotime($data['delivery_time']));
					}

					$merchant_info = Yii::app()->functions->getMerchant(isset($merchant_id) ? $merchant_id : '');
					if ($merchant_info['service'] == 8 && $data['trans_type'] == 'delivery') {


						$the_date = strtotime($data['delivery_date'] . " " . $delivery_time);
						date_default_timezone_set('UTC');
						$doordash_date = date('Y-m-d', $the_date);
						$doordash_time = date('G:i:s', $the_date);
						$delivery_date = $doordash_date . "T" . $doordash_time . 'Z';
						$doordash_result = FunctionsV3::createDoordashDelivery2($delivery_date, $data, $merchant_info, $time_in_selected, $confirmed);
						if ($doordash_result['code'] == 2) {
							$this->code = 3;
							$this->msg = $doordash_result['msg'];
							$this->output();
						}
						$timezone = Yii::app()->functions->getOption("merchant_timezone", $merchant_id);
						if (!empty($timezone)) {
							date_default_timezone_set($timezone);
						}
						$data = Yii::app()->functions->getOrder2($order_id);
						$delivery_time = $data['delivery_time'];
						$date['doordash_drive_pickup_time'] = date('h:i A', strtotime($data['doordash_drive_pickup_time']));
						$delivery_timee = date('h:i A', strtotime($data['delivery_time']));
						exit('delievery-exit');
					} else {
						exit($data['trans_type']);
						$params = array(
							'confirmed' => $confirmed,
							'pickup_in' => $time_in_selected,
							'delivery_time' => $delivery_time,
						);

						// $params['delivery_time'] = FunctionsV3::prettyTime( date("h:i:s", strtotime("+". $time_in_selected ." min",  strtotime( date("Y-m-d h:i:s") )  ))  ,true);

						//$DbExt = new DbExt;
						// $DbExt->updateData("{{order}}", $params, 'order_id', $order_id);
					}
					exit('zzzzzzzzzzz');
					$this->code = 1;
					$this->msg = "OK";

					if ($data['doordash_drive_tracking_link'] != '') {
						$print[] = array('label' => Yii::t("default", "Tracking Link"), 'value' => $data['doordash_drive_tracking_link']);
					}
					$print[] = array('label' => Yii::t("default", "Customer Name"), 'value' => $data['full_name']);
					$print[] = array('label' => Yii::t("default", "Merchant Name"), 'value' => $data['merchant_name']);
					$print[] = array(
						'label' => Yii::t("default", "ABN"),
						'value' => $data['abn']
					);
					$print[] = array(
						'label' => Yii::t("default", "Telephone"),
						'value' => $data['merchant_contact_phone']
					);
					$print[] = array(
						'label' => Yii::t("default", "Address"),
						'value' => $full_merchant_address
					);
					$print[] = array(
						'label' => Yii::t("default", "Tax number"),
						'value' => $merchant_tax_number
					);
					$print[] = array(
						'label' => Yii::t("default", "TRN Type"),
						'value' => t($data['trans_type'])
					);
					$print[] = array(
						'label' => Yii::t("default", "Payment Type"),
						'value' => FunctionsV3::prettyPaymentType('payment_order', $data['payment_type'], $order_id, $data['trans_type'])
					);

					if ($data['payment_provider_name']):
						$print[] = array(
							'label' => Yii::t("default", "Card#"),
							'value' => strtoupper($data['payment_provider_name'])
						);
					endif;
					$print[] = array(
						'label' => Yii::t("default", "Reference #"),
						'value' => Yii::app()->functions->formatOrderNumber($data['order_id'])
					);
					if (!empty($data['payment_reference'])):
						$print[] = array(
							'label' => Yii::t("default", "Payment Ref"),
							'value' => $data['payment_reference']
						);
					endif;

					if ($data['payment_type'] == "pyp"):
						$paypal_info = Yii::app()->functions->getPaypalOrderPayment($data['order_id']);
						$print[] = array(
							'label' => Yii::t("default", "Paypal Transaction ID"),
							'value' => isset($paypal_info['TRANSACTIONID']) ? $paypal_info['TRANSACTIONID'] : ''
						);
					endif;
					if ($data['payment_type'] == "ccr" || $data['payment_type'] == "ocr"):
						$print[] = array(
							'label' => Yii::t("default", "Card #"),
							'value' => $card
						);
					endif;

					$trn_date = FunctionsV3::prettyDate($data['date_created']) . " " . FunctionsV3::prettyTime($data['date_created']);

					$print[] = array(
						'label' => Yii::t("default", "TRN Date"),
						'value' => $trn_date
					);
					if ($data['trans_type'] == "delivery"):
						if (isset($data['delivery_date'])):
							$deliver_date = FunctionsV3::prettyDate($data['delivery_date']);
							$print[] = array(
								'label' => Yii::t("default", "Delivery Date"),
								'value' => $deliver_date
							);
						endif;

						if ($data['delivery_asap'] != 1):
							if (isset($data['delivery_time'])):
								if (!empty($data['delivery_time'])):
									$print[] = array(
										'label' => Yii::t("default", "Delivery Time"),
										'value' => $delivery_timee
									);
								endif;
							endif;
						endif;


						if ($data['delivery_asap'] == 1):
							if (isset($data['delivery_asap'])):
								if (!empty($data['delivery_asap'])):
									$print[] = array(
										'label' => Yii::t("default", "Deliver ASAP"),
										'value' => $delivery_timee
									);
								endif;
							endif;
						endif;
						if (!empty($data['client_full_address'])) {
							$delivery_address = $data['client_full_address'];
						}
						$delivery_address = $data['full_address'];
						$delivery_address = $data['client_street'] . " " . $data['client_city'] . " " . $data['client_state'] . " " . $data['client_zipcode'];
						$print[] = array(
							'label' => Yii::t("default", "Deliver to"),
							'value' => $delivery_address
						);
						$print[] = array(
							'label' => Yii::t("default", "Delivery Instruction"),
							'value' => $data['delivery_instruction']
						);
						$print[] = array(
							'label' => Yii::t("default", "Location Name"),
							'value' => $data['location_name']
						);

						if (!empty($data['contact_phone1'])) {
							$data['contact_phone'] = $data['contact_phone1'];
						}
						$print[] = array(
							'label' => Yii::t("default", "Contact Number"),
							'value' => $data['contact_phone']
						);
						if ($data['order_change'] >= 0.1):
							$print[] = array(
								'label' => Yii::t("default", "Change"),
								'value' => normalPrettyPrice($data['order_change'])
							);
						endif;
					else:
						$label_date = t("Pickup Date");
						$label_time = t("Pickup Time");
						if ($transaction_type == "dinein") {
							$label_date = t("Dine in Date");
							$label_time = t("Dine in Time");
						}

						if (isset($data['contact_phone1'])) {
							if (!empty($data['contact_phone1'])) {
								$data['contact_phone'] = $data['contact_phone1'];
							}
						}
						$print[] = array(
							'label' => Yii::t("default", "Contact Number"),
							'value' => $data['contact_phone']
						);
						if (isset($data['delivery_date'])):
							$print[] = array(
								'label' => $label_date,
								'value' => FunctionsV3::prettyDate($data['delivery_date'])
							);
						endif;
						$show_time = true;

						if (isset($delivery_time) && $show_time):
							if (!empty($delivery_time)):
								$print[] = array(
									'label' => $label_time . 'ooooo',
									'value' => FunctionsV3::prettyTime($delivery_time, true)
								);
							endif;
						endif;
						if ($transaction_type == "dinein"):
							if ($data['order_change'] >= 0.1):
								$print[] = array(
									'label' => Yii::t("default", "Change"),
									'value' => $data['order_change']
								);
							endif;

							$print[] = array(
								'label' => t("Number of guest"),
								'value' => $data['dinein_number_of_guest']
							);
							$print[] = array(
								'label' => t("Table number"),
								'value' => $data['dinein_table_number'] > 0 ? $data['dinein_table_number'] : ''
							);
							$print[] = array(
								'label' => t("Special instructions"),
								'value' => $data['dinein_special_instruction']
							);
						endif;
					endif;

					$item_details = Yii::app()->functions->details['html'];
					if ($data['delivery_service_type'] == 'dindin') {
						$data_raw = Yii::app()->functions->details['raw'];
						if ($apply_tax == 1 && $tax_set > 0) {
							$receipt = EmailTPL::salesReceiptTax($print, Yii::app()->functions->details['raw']);
						} else
							$receipt = EmailTPL::salesReceipt($print, Yii::app()->functions->details['raw']);
						$to = isset($data['email_address']) ? $data['email_address'] : '';
						// 		$to="zeeshananweraziz@gmail.com";

						/*SEND EMAIL TO CUSTOMER*/

						FunctionsV3::notifyCustomer($data, Yii::app()->functions->additional_details, $receipt, $to);

						// 		print_r($receipt); print_r($data); exit('stop'); 

						FunctionsV3::notifyMerchant($data, Yii::app()->functions->additional_details, $receipt);
						FunctionsV3::notifyAdmin($data, Yii::app()->functions->additional_details, $receipt);

						FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processemail"));
						FunctionsV3::fastRequest(FunctionsV3::getHostURL() . Yii::app()->createUrl("cron/processsms"));
					}
				}
			}
			return true;
		} else {
			return false;
		}

	}

} /*end class*/