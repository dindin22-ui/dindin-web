<?php

class AjaxController extends CController
{
	public $code=2;
	public $msg;
	public $details;
	public $data;
	
	public function __construct()
	{
		$this->data=$_POST;	
	}
	
	public function beforeAction($action)
	{		
		return true;
	}
	
	public function init()
	{
		FunctionsV3::handleLanguage();
		$lang=Yii::app()->language;				
	}
	
	private function jsonResponse()
	{
		$resp=array('code'=>$this->code,'msg'=>$this->msg,'details'=>$this->details);
		echo CJSON::encode($resp);
		Yii::app()->end();
	}
	
	private function otableNodata()
	{
		if (isset($_GET['sEcho'])){
			$feed_data['sEcho']=$_GET['sEcho'];
		} else $feed_data['sEcho']=1;	   
		     
        $feed_data['iTotalRecords']=0;
        $feed_data['iTotalDisplayRecords']=0;
        $feed_data['aaData']=array();		
        echo json_encode($feed_data);
    	die();
	}

	private function otableOutput($feed_data='')
	{
	  echo json_encode($feed_data);
	  die();
    }    
    
    public function t($message='')
    {
    	return PrinterClass::t($message);
    }
	
	public function actionSaveSettings()
	{		
			
		if(!Yii::app()->functions->isAdminLogin()){
			$this->msg = t("session has expired");
			$this->jsonResponse();
			Yii::app()->end();		
		}
			
		Yii::app()->functions->updateOptionAdmin('printer_user',
		  isset($this->data['printer_user'])?trim($this->data['printer_user']):''
		);
		Yii::app()->functions->updateOptionAdmin('printer_ukey',
		  isset($this->data['printer_ukey'])?trim($this->data['printer_ukey']):''
		);
		
		Yii::app()->functions->updateOptionAdmin('printer_auto_print',
		  isset($this->data['printer_auto_print'])?trim($this->data['printer_auto_print']):''
		);
		
		$this->code=1;
		$this->msg=PrinterClass::t("settings saved");
		
		$this->jsonResponse();
	}
	
	public function actionadd_printer()
	{		
		if(!Yii::app()->functions->isAdminLogin()){
			$this->msg = t("session has expired");
			$this->jsonResponse();
			Yii::app()->end();		
		}
		
		$required = array(
		  'printer_sn'=>PrinterClass::t("SN is required"),
		  'printer_key'=>PrinterClass::t("KEY is required"),
		  'printer_name'=>PrinterClass::t("Name is required"),
		);
		
		$validator = new Validator();	
		
		if (!isset($this->data['id'])){
			if(!empty($this->data['printer_sn'])){
				if ( PrinterClass::getPrinterBySN($this->data['printer_sn'])){
				    $validator->msg[]= $this->t("Printer sn already exist");
				}
			}
		}
		
		$validator->required($required,$this->data);
		if ($validator->validate()){
			$params = array(
			  'printer_sn'=>$this->data['printer_sn'],
			  'printer_key'=>$this->data['printer_key'],
			  'printer_name'=>$this->data['printer_name'],
			  'is_default'=>isset($this->data['is_default'])?$this->data['is_default']:0,
			  'date_created'=>FunctionsV3::dateNow(),
			  'ip_address'=>$_SERVER['REMOTE_ADDR']
			);			
			$db = new DbExt();
			if (isset($this->data['id'])){				
				if ($credentials = PrinterClass::getCredentials()){
					
					$resp = PrinterClass::updatePrinter($credentials['user'],$credentials['stime'],
					$credentials['sig'],
					$this->data['printer_sn'],
					$this->data['printer_name'],
					$this->data['printer_key']
					);					
					if($resp){						
						unset($params['date_created']);
					    $params['date_modified']=FunctionsV3::dateNow();
					    $db->updateData("{{printer_list}}",$params,'printer_id',$this->data['id']);
						
						$this->code = 1;
					    $this->msg = PrinterClass::t("Successfully updated");
					    $this->details='';
					    
					    PrinterClass::updatePrinterDefault($this->data['id']);
					    
					} else $this->msg = PrinterClass::$message;
				} else $this->msg = $this->t("check printer settings");
			} else {
				
				if ($credentials = PrinterClass::getCredentials()){					
					$snlist = $this->data['printer_sn']."#".$this->data['printer_key']."#".$this->data['printer_name'];
					
					$resp = PrinterClass::addprinter($credentials['user'],$credentials['stime'],
					$credentials['sig'],$snlist);
					
					if($resp){						
						$params['api_response']=json_encode($resp);
						if ( $db->insertData("{{printer_list}}",$params)){
							$printer_id = Yii::app()->db->getLastInsertID();
							$this->code = 1;
							$this->msg = PrinterClass::t("Successful");
							$this->details = Yii::app()->createUrl('printer/index/add_printer',array(
							 'id'=>$printer_id,
							 'msg'=>$this->msg
							));
							
							PrinterClass::updatePrinterDefault($printer_id);
							
						} else $this->msg = PrinterClass::t("Failed inserting records");						
					} else $this->msg = PrinterClass::$message;
										
				} else $this->msg = $this->t("check printer settings");
			}
		} else $this->msg = $validator->getErrorAsHTML();
		$this->jsonResponse();
	}
	
	public function actionprinter_list()
	{		 
		$aColumns = array(
		  'printer_id',
		  'printer_sn',
		  'printer_name',
		  'is_default',
		  'printer_status',
		  'printer_id'
		);
		
		$t=AjaxDataTables::AjaxData($aColumns);		
		if (isset($_GET['debug'])){
		    dump($t);
		}
		
		if (is_array($t) && count($t)>=1){
			$sWhere=$t['sWhere'];
			$sOrder=$t['sOrder'];
			$sLimit=$t['sLimit'];
		}	
				
		$and='';
				
		$stmt="SELECT SQL_CALC_FOUND_ROWS a.*
		FROM
		{{printer_list}} a
		WHERE merchant_id='0'		
		$sWhere
		$and
		$sOrder
		$sLimit
		";
		if (isset($_GET['debug'])){
		   dump($stmt);
		}
		
		$DbExt=new DbExt; 
		if ( $res=$DbExt->rst($stmt)){
			
			$iTotalRecords=0;						
			$stmtc="SELECT FOUND_ROWS() as total_records";
			if ( $resc=$DbExt->rst($stmtc)){									
				$iTotalRecords=$resc[0]['total_records'];
			}
			
			$feed_data['sEcho']=intval($_GET['sEcho']);
			$feed_data['iTotalRecords']=$iTotalRecords;
			$feed_data['iTotalDisplayRecords']=$iTotalRecords;										
			
			foreach ($res as $val) {
				
				$link=Yii::app()->createUrl('printer/index/add_printer',array(
				 'id'=>$val['printer_id']
				));
				$action="<a href=\"$link\">
			    Edit
			    </a>";
				
				$action.="|";
				
				$action.="<a class=\"delete_printer\" href=\"javascript:;\" data-id=\"$val[printer_id]\">
			    Delete
			    </a>";
				
				$action.="|";
				
				$action.="<a class=\"test_print\" href=\"javascript:;\" data-id=\"$val[printer_id]\">
			    Print
			    </a>";
				
				$is_default='';
				if($val['is_default']==1){
					$is_default='<i style="color:green;" class="ion-checkmark"></i>';
				}
				
				$printer_status='';
				if(!empty($val['printer_status'])){
				   $printer_status.='<p>'.$val['printer_status'].'</p>';
				}
				$printer_status.='<a class="get_printer_status" data-id="'.$val['printer_id'].'" href="javascript:;">'.PrinterClass::t("Refresh").'</a>';
				
				
				$feed_data['aaData'][]=array(
				  $val['printer_id'],
				  $val['printer_sn'],
				  $val['printer_name'],
				  $is_default,
				  $printer_status,
				  $action
				);
			}
			if (isset($_GET['debug'])){
			   dump($feed_data);
			}
			$this->otableOutput($feed_data);	
		}
		$this->otableNodata();
	}
	
	public function actiondelete_printer()
	{	
		$id = isset($this->data['id'])?$this->data['id']:'';
		if ($res = PrinterClass::getPrinterByID($id)){			
			if($credentials = PrinterClass::getCredentials()){				
				$snlist = $res['printer_sn'];				
				$resp = PrinterClass::deletePrinter($credentials['user'],
				$credentials['stime'],$credentials['sig'],$snlist);				
				if($resp){
					$this->code = 1;
					$this->msg = PrinterClass::t("Printer deleted");
					
					$stmt="DELETE FROM
					{{printer_list}}
					WHERE
					printer_id =".FunctionsV3::q($id)."
					";
					$db=new DbExt; 
					$db->qry($stmt);
					
				} else $this->msg = PrinterClass::$message;				
			} else $this->t("check printer settings");
			
		} else $this->msg = PrinterClass::t("Printer not found");
		$this->jsonResponse();
	}
	
	public function actionget_printer_status()
	{		
		$id = isset($this->data['id'])?$this->data['id']:'';
		if ( $res = PrinterClass::getPrinterByID($id)){		
			if ($credentials = PrinterClass::getCredentials()){									
				$resp = PrinterClass::getPrinterStatus(
				$credentials['user'],
				$credentials['stime'],
				$credentials['sig'],
				$res['printer_sn']
				);
				
				if ($resp){
					$status = $resp;
				} else $status = PrinterClass::$message;
				
				$this->code = 1;
				$this->msg = PrinterClass::t("Successful");
				
				$params = array(
				  'printer_status'=>$status,
				  'date_modified'=>FunctionsV3::dateNow(),
				  'ip_address'=>$_SERVER['REMOTE_ADDR']
				);
				$db=new DbExt; 
				$db->updateData("{{printer_list}}",$params,'printer_id',$id);
				
		    } else $this->msg = $this->t("check printer settings");			
		} else $this->msg = PrinterClass::t("Printer not found");
		$this->jsonResponse();
	}
	
	public function actiontest_print()
	{		
		$id = isset($this->data['id'])?$this->data['id']:'';
		if ( $res = PrinterClass::getPrinterByID($id)){		
			if ($credentials = PrinterClass::getCredentials()){	
				
				$content='<CB>this is a test</CB><BR>';
				$content.="<C>END OF TEST</C>";				
				
				$resp = PrinterClass::thermalPrint(
				  $credentials['user'],
				  $credentials['stime'],
				  $credentials['sig'],
				  $res['printer_sn'],
				  $content
				);
				
				$status = ''; $order_id='';
				if($resp){
					$order_id = $resp;
					$status='ok';
					$this->msg = PrinterClass::t("Print sucessful");
					$this->code = 1;
				} else {
					$status = PrinterClass::$message;
					$this->msg = PrinterClass::$message;
				}
				
				$params = array(
				  'printer_sn'=>$res['printer_sn'],
				  'content'=>$content,
				  'status'=>$status,
				  'print_order_id'=>$order_id,
				  'date_created'=>FunctionsV3::dateNow(),
				  'ip_address'=>$_SERVER['REMOTE_ADDR']
				);
				
				$db=new DbExt;
				$db->insertData("{{printer_print}}",$params);
				
		    } else $this->msg = $this->t("check printer settings");			
		} else $this->msg = PrinterClass::t("Printer not found");
		$this->jsonResponse();
	}
	
	public function actionprinter_logs()
	{
		if(!Yii::app()->functions->isAdminLogin()){
			$this->msg = t("session has expired");
			$this->jsonResponse();
			Yii::app()->end();		
		}
		
		$aColumns = array(
		  'id',
		  'printer_sn',
		  'content',		  
		  'status',
		  'print_order_id',
		  'query_status',
		  'date_created',		  
		);
		
		$t=AjaxDataTables::AjaxData($aColumns);		
		if (isset($_GET['debug'])){
		    dump($t);
		}
		
		if (is_array($t) && count($t)>=1){
			$sWhere=$t['sWhere'];
			$sOrder=$t['sOrder'];
			$sLimit=$t['sLimit'];
		}	
				
		$and='';
				
		$stmt="SELECT SQL_CALC_FOUND_ROWS a.*
		FROM
		{{printer_print}} a
		WHERE merchant_id='0'		
		$sWhere
		$and
		$sOrder
		$sLimit
		";
		if (isset($_GET['debug'])){
		   dump($stmt);
		}
		
		$DbExt=new DbExt; 
		if ( $res=$DbExt->rst($stmt)){
			
			$iTotalRecords=0;						
			$stmtc="SELECT FOUND_ROWS() as total_records";
			if ( $resc=$DbExt->rst($stmtc)){									
				$iTotalRecords=$resc[0]['total_records'];
			}
			
			$feed_data['sEcho']=intval($_GET['sEcho']);
			$feed_data['iTotalRecords']=$iTotalRecords;
			$feed_data['iTotalDisplayRecords']=$iTotalRecords;										
			
			foreach ($res as $val) {
								
				$link="<a href=\"javascript:;\" data-toggle=\"modal\" data-target=\"#myModal$val[id]\" >			    
				".PrinterClass::t("View details")."
			    </a>";
				
				$modal='<div id="myModal'.$val['id'].'" class="modal fade" role="dialog">';
				  $modal.='<div class="modal-dialog modal-sm">';
					  $modal.='<div class="modal-content">';
					  
					  $modal.='<div class="modal-header">';
					     $modal.='<h4 class="modal-title">'.PrinterClass::t("Print Details").'</h4>';
					  $modal.='</div>';
					  
					    $modal.='<div class="modal-body">';
					      $modal.=$val['content'];
					    $modal.='</div>';
					  $modal.='</div>';
				  $modal.='</div>';
				$modal.='</div>';
				
				$link.=$modal;
								
				$feed_data['aaData'][]=array(
				  $val['id'],
				  $val['printer_sn'],
				  $link,
				  PrinterClass::t($val['status']),
				  $val['print_order_id'],		
				  PrinterClass::t($val['query_status']),
				  Yii::app()->functions->translateDate(Yii::app()->functions->FormatDateTime($val['date_created'],true))
				);
			}
			if (isset($_GET['debug'])){
			   dump($feed_data);
			}
			$this->otableOutput($feed_data);	
		}
		$this->otableNodata();
	}
	
	public function actionsavetemplate()
	{
		
		if(!Yii::app()->functions->isAdminLogin()){
			$this->msg = t("session has expired");
			$this->jsonResponse();
			Yii::app()->end();		
		}
		
		Yii::app()->functions->updateOptionAdmin('printer_receipt_tpl',
		  isset($this->data['printer_receipt_tpl'])?trim($this->data['printer_receipt_tpl']):''
		);		
		$this->code=1;
		$this->msg=PrinterClass::t("settings saved");
		
		$this->jsonResponse();
	}
	
	public function actionload_template()
	{
		$id = isset($this->data['id'])?$this->data['id']:'';
		if($id==1){
			$tpl = ReceiptClass::template_2();
		} else $tpl = ReceiptClass::template_1();
		
		$this->code = 1;
		$this->msg = $this->data['target'];
		$this->details = $tpl;
		
		$this->jsonResponse();
	}
	
	public function actionmt_savesettings()
	{
		$mtid=Yii::app()->functions->getMerchantID();		
		if(is_numeric($mtid)){
			
			Yii::app()->functions->updateOption("mt_printer_auto_print",
	    	isset($this->data['mt_printer_auto_print'])?$this->data['mt_printer_auto_print']:'',$mtid);  
	    	
	    	Yii::app()->functions->updateOption("mt_printer_user",
	    	isset($this->data['mt_printer_user'])?$this->data['mt_printer_user']:'',$mtid);  
	    	
	    	Yii::app()->functions->updateOption("mt_printer_ukey",
	    	isset($this->data['mt_printer_ukey'])?$this->data['mt_printer_ukey']:'',$mtid);  
	    	
			$this->code=1;
			$this->msg=PrinterClass::t("Settings saved.");
		
		} else $this->msg = PrinterClass::t("Session expired");
		$this->jsonResponse();
	}
	
	public function actionmt_savetemplate()
	{		
		$mtid=Yii::app()->functions->getMerchantID();		
		if(is_numeric($mtid)){
			Yii::app()->functions->updateOption("mt_printer_receipt_tpl",
	    	isset($this->data['mt_printer_receipt_tpl'])?$this->data['mt_printer_receipt_tpl']:'',$mtid);  
	    	
			$this->code=1;
			$this->msg=PrinterClass::t("Settings saved.");
		} else $this->msg = PrinterClass::t("Session expired");
		$this->jsonResponse();
	}
	
	public function actionmt_add_hccprinter(){
		$mtid=Yii::app()->functions->getMerchantID();		
		if(is_numeric($mtid)){

// 			$validator=new Validator();
// 		   	$required = array(
// 			  'mt_hccprinter_device_id'=>PrinterClass::t("Device ID is required"),
// 			  'mt_hccprinter_secret_key'=>PrinterClass::t("Secret Key is required"),
// 		   	);
// 		   	$validator->required($required,$this->data);
// 			if($validator->validate()){
				
				$insertion = $this->data;
				unset($insertion['YII_CSRF_TOKEN']);
				 $resp = array(); 	
					foreach ($insertion as $key => $value) {
						$resps  = Yii::app()->functions->updateOption( $key, $value, $mtid); 
						array_push($resp, $resps);
					}
				if(!empty($resp)){						
			     	$this->code = 1;
					$this->msg = PrinterClass::t("Successful");
				}
				else $this->msg = 'not saved';
// 			}
// 			else $this->msg = $validator->getErrorAsHTML();
		}	
		$this->jsonResponse();
	}

	public function actionmt_add_printer()
	{
		$mtid=Yii::app()->functions->getMerchantID();		
		if(is_numeric($mtid)){
			
		   $validator=new Validator();
		   $required = array(
			  'mt_printer_sn'=>PrinterClass::t("SN is required"),
			  'mt_printer_key'=>PrinterClass::t("KEY is required"),
			  'mt_printer_name'=>PrinterClass::t("Name is required"),
		   );
		   $validator->required($required,$this->data);
		   if ($validator->validate()){
		      
		   	   $mt_printer_sn=getOption($mtid,'mt_printer_sn');		   	   
		   	   
		   	   if ($credentials = PrinterClass::getMerchantCredentials($mtid)){		   	       
			   	   if(empty($mt_printer_sn)){			   	   	  
			   	   	 /*ADD*/
			   	   	 
			   	   	 $snlist = $this->data['mt_printer_sn']."#".$this->data['mt_printer_key']."#".$this->data['mt_printer_name'];			   	   	 
			   	   	 $resp = PrinterClass::addprinter($credentials['user'],$credentials['stime'],
				     $credentials['sig'],$snlist);  
				     				     
				     if($resp){						
				     	$this->code = 1;
						$this->msg = PrinterClass::t("Successful");		

						Yii::app()->functions->updateOption("mt_printer_sn",
	    	            isset($this->data['mt_printer_sn'])?$this->data['mt_printer_sn']:'',$mtid);  
	    	            
	    	            Yii::app()->functions->updateOption("mt_printer_key",
	    	            isset($this->data['mt_printer_key'])?$this->data['mt_printer_key']:'',$mtid);  
	    	            
	    	            Yii::app()->functions->updateOption("mt_printer_name",
	    	            isset($this->data['mt_printer_name'])?$this->data['mt_printer_name']:'',$mtid);  
											
					 } else $this->msg = PrinterClass::$message;
					 			   	   	
			   	   } else {
			   	   	
			   	   	  /*UPDATE*/
			   	   	  $resp = PrinterClass::updatePrinter(
			   	   	    $credentials['user'],
			   	   	    $credentials['stime'],
						$credentials['sig'],
						$this->data['mt_printer_sn'],
						$this->data['mt_printer_name'],
						$this->data['mt_printer_key']
					   );					  
					   
					   if($resp){																		
						  $this->code = 1;
						  $this->msg = PrinterClass::t("Successfully updated");
						  $this->details='';				
						  
						  Yii::app()->functions->updateOption("mt_printer_sn",
	    	              isset($this->data['mt_printer_sn'])?$this->data['mt_printer_sn']:'',$mtid);  
	    	            
	    	              Yii::app()->functions->updateOption("mt_printer_key",
	    	              isset($this->data['mt_printer_key'])?$this->data['mt_printer_key']:'',$mtid);  
	    	            
	    	              Yii::app()->functions->updateOption("mt_printer_name",
	    	              isset($this->data['mt_printer_name'])?$this->data['mt_printer_name']:'',$mtid);  
						  			
					   } else $this->msg = PrinterClass::$message;
			   	   	
			   	   }
		   	   
		   	   } else $this->msg = $this->t("check printer settings");
		   	
		   } else $this->msg = $validator->getErrorAsHTML();
		   
		} else $this->msg = PrinterClass::t("Session expired");
		$this->jsonResponse();
	}
	
	public function actionmt_print()
	{
		$mtid=Yii::app()->functions->getMerchantID();		
		if(is_numeric($mtid)){
			if ($credentials = PrinterClass::getMerchantCredentials($mtid)){
				
				$content='<CB>this is a test</CB><BR>';
				$content.="<C>END OF TEST</C>";			
				
				$mt_printer_sn = getOption($mtid,'mt_printer_sn');	
				if(empty($mt_printer_sn)){
					$this->msg = PrinterClass::t("Printer number is empty");
					$this->jsonResponse();
				}
				
				$resp = PrinterClass::thermalPrint(
				  $credentials['user'],
				  $credentials['stime'],
				  $credentials['sig'],
				  $mt_printer_sn,
				  $content
				);
				
				$status = ''; $order_id='';
				if($resp){
					$order_id = $resp;
					$status='ok';
					$this->msg = PrinterClass::t("Print sucessful");
					$this->code = 1;
				} else {
					$status = PrinterClass::$message;
					$this->msg = PrinterClass::$message;
				}
				
				$params = array(
				  'printer_sn'=>$mt_printer_sn,
				  'content'=>$content,
				  'status'=>$status,
				  'print_order_id'=>$order_id,
				  'merchant_id'=>$mtid,
				  'date_created'=>FunctionsV3::dateNow(),
				  'ip_address'=>$_SERVER['REMOTE_ADDR']
				);
				
				$db=new DbExt;
				$db->insertData("{{printer_print}}",$params);
			
			} else $this->msg = $this->t("check printer settings");
		} else $this->msg = PrinterClass::t("Session expired");
		$this->jsonResponse();
	}
	
	public function actionmt_printer_check_status()
	{
		$mtid=Yii::app()->functions->getMerchantID();		
		if(is_numeric($mtid)){
			if ($credentials = PrinterClass::getMerchantCredentials($mtid)){
				
				$mt_printer_sn = getOption($mtid,'mt_printer_sn');	
				if(empty($mt_printer_sn)){
					$this->msg = PrinterClass::t("Printer number is empty");
					$this->jsonResponse();
				}
				
				$resp = PrinterClass::getPrinterStatus(
					$credentials['user'],
					$credentials['stime'],
					$credentials['sig'],
					$mt_printer_sn
				);
				
				if ($resp){
					$status = $resp;
				} else $status = PrinterClass::$message;
				
				$this->code = 1;
				$this->msg = PrinterClass::t( $status );
				
			} else $this->msg = $this->t("check printer settings");
		} else $this->msg = PrinterClass::t("Session expired");
		$this->jsonResponse();
	}
	
	public function actionmt_delete_printer()
	{
		$mtid=Yii::app()->functions->getMerchantID();		
		if(is_numeric($mtid)){
			if ($credentials = PrinterClass::getMerchantCredentials($mtid)){
				
				$mt_printer_sn = getOption($mtid,'mt_printer_sn');	
				if(empty($mt_printer_sn)){
					$this->msg = PrinterClass::t("Printer number is empty");
					$this->jsonResponse();
				}
				
				$snlist = $mt_printer_sn;
				
				$resp = PrinterClass::deletePrinter($credentials['user'],
				$credentials['stime'],$credentials['sig'],$snlist);	
				
				if($resp){
					$this->code = 1;
					$this->msg = PrinterClass::t("Printer deleted");
					
					Yii::app()->functions->updateOption('mt_printer_sn','',$mtid);
					Yii::app()->functions->updateOption('mt_printer_key','',$mtid);
					Yii::app()->functions->updateOption('mt_printer_name','',$mtid);
					
				} else $this->msg = PrinterClass::$message;				
				
			} else $this->msg = $this->t("check printer settings");
		} else $this->msg = PrinterClass::t("Session expired");
		$this->jsonResponse();
	}
	
	public function actionmt_printer_logs()
	{
		
		if ( !Yii::app()->functions->isMerchantLogin()){
			Yii::app()->end();
		}		
		
		$mtid=Yii::app()->functions->getMerchantID();		
	    if(!is_numeric($mtid)){
	    	$this->otableNodata();
	    }
		
		$aColumns = array(
		  'id',
		  'printer_sn',
		  'content',		  
		  'status',
		  'print_order_id',
		  'query_status',
		  'date_created',		  
		);
		
		$t=AjaxDataTables::AjaxData($aColumns);		
		if (isset($_GET['debug'])){
		    dump($t);
		}
		
		if (is_array($t) && count($t)>=1){
			$sWhere=$t['sWhere'];
			$sOrder=$t['sOrder'];
			$sLimit=$t['sLimit'];
		}	
				
		$and='';
				
		$stmt="SELECT SQL_CALC_FOUND_ROWS a.*
		FROM
		{{printer_print}} a
		WHERE merchant_id=".FunctionsV3::q($mtid)."
		$sWhere
		$and
		$sOrder
		$sLimit
		";
		if (isset($_GET['debug'])){
		   dump($stmt);
		}
		
		$DbExt=new DbExt; 
		if ( $res=$DbExt->rst($stmt)){
			
			$iTotalRecords=0;						
			$stmtc="SELECT FOUND_ROWS() as total_records";
			if ( $resc=$DbExt->rst($stmtc)){									
				$iTotalRecords=$resc[0]['total_records'];
			}
			
			$feed_data['sEcho']=intval($_GET['sEcho']);
			$feed_data['iTotalRecords']=$iTotalRecords;
			$feed_data['iTotalDisplayRecords']=$iTotalRecords;										
			
			foreach ($res as $val) {
								
				$link="<a href=\"javascript:;\" data-toggle=\"modal\" data-target=\"#myModal$val[id]\" >			    
				".PrinterClass::t("View details")."
			    </a>";
				
				$modal='<div id="myModal'.$val['id'].'" class="modal fade" role="dialog">';
				  $modal.='<div class="modal-dialog modal-sm">';
					  $modal.='<div class="modal-content">';
					  
					  $modal.='<div class="modal-header">';
					     $modal.='<h4 class="modal-title">'.PrinterClass::t("Print Details").'</h4>';
					  $modal.='</div>';
					  
					    $modal.='<div class="modal-body">';
					      $modal.=$val['content'];
					    $modal.='</div>';
					  $modal.='</div>';
				  $modal.='</div>';
				$modal.='</div>';
				
				$link.=$modal;
								
				$feed_data['aaData'][]=array(
				  $val['id'],
				  $val['printer_sn'],
				  $link,
				  PrinterClass::t($val['status']),
				  $val['print_order_id'],		
				  PrinterClass::t($val['query_status']),
				  Yii::app()->functions->translateDate(Yii::app()->functions->FormatDateTime($val['date_created'],true))
				);
			}
			if (isset($_GET['debug'])){
			   dump($feed_data);
			}
			$this->otableOutput($feed_data);	
		}
		$this->otableNodata();
	}
	
} /*end class*/