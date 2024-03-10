<?php
define('IP','api.feieyun.cn');
define('PORT',80);
define('PATH','/Api/Open/');

class PrinterClass
{
	static $message;
	
	public static function t($message='')
	{
		return Yii::t("printer",$message);
	}
	
	public static function getPrinterByID($printer_id='')
	{
		$db = new DbExt();
		$stmt="SELECT * FROM
		{{printer_list}}
		WHERE
		printer_id = ".FunctionsV3::q($printer_id)."
		LIMIT 0,1
		";
		if ($res = $db->rst($stmt)){
			return $res[0];
		}
		return false;
	}
	
	public static function getPrinterBySN($sn='')
	{
		$db = new DbExt();
		$stmt="SELECT * FROM
		{{printer_list}}
		WHERE
		printer_sn = ".FunctionsV3::q($sn)."
		LIMIT 0,1
		";
		if ($res = $db->rst($stmt)){
			return $res[0];
		}
		return false;
	}
	
	public static function getCredentials()
	{
		$printer_user = Yii::app()->functions->getOptionAdmin('printer_user');
		$printer_ukey = Yii::app()->functions->getOptionAdmin('printer_ukey');
		$stime = time();
		$sig = sha1($printer_user.$printer_ukey.$stime);
		if (!empty($printer_user) && !empty($printer_ukey)){
			return array(
			  'user'=>$printer_user,
			  'ukey'=>$printer_ukey,
			  'stime'=>$stime,
			  'sig'=>$sig
			);
		}
		return false;
	}
	
	public static function getMerchantCredentials($mtid='')
	{
		$printer_user = Yii::app()->functions->getOption('mt_printer_user',$mtid);
		$printer_ukey = Yii::app()->functions->getOption('mt_printer_ukey',$mtid);
		$stime = time();
		$sig = sha1($printer_user.$printer_ukey.$stime);
		if (!empty($printer_user) && !empty($printer_ukey)){
			return array(
			  'user'=>$printer_user,
			  'ukey'=>$printer_ukey,
			  'stime'=>$stime,
			  'sig'=>$sig
			);
		}
		return false;
	}
	
	public static function updatePrinterDefault($printer_id='')
	{
		$db = new DbExt();
		$stmt="UPDATE
		{{printer_list}}
		SET is_default='0'
		WHERE printer_id NOT IN ('$printer_id')
		";
		$db->qry($stmt);
	}
	
    public static function addprinter($user='', $stime='',$sig='',$snlist){
	
    	header("Content-type: text/html; charset=utf-8");
    	
		$content = array(			
				'user'=>$user,
				'stime'=>$stime,
				'sig'=>$sig,
				'apiname'=>'Open_printerAddlist',	
			    'printerContent'=>$snlist
			);
			
		$client = new HttpClient(IP,PORT);
		if(!$client->post(PATH,$content)){			
			//self::$message = PrinterClass::t("Error");
			self::$message = PrinterClass::t("Error")." ".$client->getError();
		}
		else{
			$resp = $client->getContent();
			if(!empty($resp)){			   
			   $resp_json = json_decode($resp,true);
			   if(is_array($resp_json) && count($resp_json)>=1){
			      /*dump($resp_json);
			      dump($resp_json['data']['ok']);
			      dump($resp_json['data']['no']);*/
			      
			      if ($resp_json['ret']==0){
				      if(!empty($resp_json['data']['ok'][0])){
				      	  return $resp_json;
				      } else self::$message = $resp_json['data']['no'][0];
			      } else self::$message = $resp_json['msg'];
			      
			   } else self::$message = self::t("response is not json");
			} else self::$message = self::t("empty response from api");
		}
		return false;
	}		
	
	public static function updatePrinter($user='', $stime='',$sig='',$sn='', $printer_name='', $printer_key='')
	{
		header("Content-type: text/html; charset=utf-8");
    	
		$content = array(			
				'user'=>$user,
				'stime'=>$stime,
				'sig'=>$sig,
				'apiname'=>'Open_printerEdit',	
			    'sn'=>$sn,
			    'name'=>$printer_name,
			    'phonenum'=>$printer_key
			);
					
		$client = new HttpClient(IP,PORT);
		if(!$client->post(PATH,$content)){			
			self::$message = PrinterClass::t("Error");
		} else {
			$resp = $client->getContent();			
			if(!empty($resp)){			   
			   $resp_json = json_decode($resp,true);
			   if(is_array($resp_json) && count($resp_json)>=1){			      
			      if($resp_json['ret']==0){
			      	 return true;
			      } else self::$message = $resp_json['msg'];
			   } else self::$message = self::t("response is not json");
			} else self::$message = self::t("empty response from api");
		}
		return false;
	}
	
	public static function deletePrinter($user='', $stime='',$sig='',$snlist)
	{
		header("Content-type: text/html; charset=utf-8");
    	
		$content = array(			
				'user'=>$user,
				'stime'=>$stime,
				'sig'=>$sig,
				'apiname'=>'Open_printerDelList',	
			    'snlist'=>$snlist
			);
			
		$client = new HttpClient(IP,PORT);
		if(!$client->post(PATH,$content)){			
			self::$message = PrinterClass::t("Error");
		} else {
			$resp = $client->getContent();
			//dump($resp);
			if(!empty($resp)){			   
			   $resp_json = json_decode($resp,true);
			   if(is_array($resp_json) && count($resp_json)>=1){
			      /*dump($resp_json);
			      dump($resp_json['data']['ok']);
			      dump($resp_json['data']['no']);*/
			      
			      if ($resp_json['ret']==0){
				      if(!empty($resp_json['data']['ok'][0])){
				      	  return $resp_json;
				      } else self::$message = $resp_json['data']['no'][0];
			      } else self::$message = $resp_json['msg'];
			   } else self::$message = self::t("response is not json");
			} else self::$message = self::t("empty response from api");
		}
		return false;
	}
	
	public static function getPrinterStatus($user='', $stime='',$sig='',$sn='')
	{
		header("Content-type: text/html; charset=utf-8");
    	
		$content = array(			
				'user'=>$user,
				'stime'=>$stime,
				'sig'=>$sig,
				'apiname'=>'Open_queryPrinterStatus',	
			    'sn'=>$sn
			);
	    
		$client = new HttpClient(IP,PORT);
		if(!$client->post(PATH,$content)){			
			self::$message = PrinterClass::t("Error");
		} else {
			$resp = $client->getContent();			
			//dump($resp);
			if(!empty($resp)){			   
			   $resp_json = json_decode($resp,true);
			  // dump($resp_json);
			   if(is_array($resp_json) && count($resp_json)>=1){			      
			      if(!empty($resp_json['data']['ok'][0])){
			      	  return $resp_json['data'];
			      } else self::$message = $resp_json['msg'];
			   } else self::$message = self::t("response is not json");
			} else self::$message = self::t("empty response from api");
		}
		return false;
	}
	
	public static function thermalPrint($user='', $stime='',$sig='',$sn='',$content='',$times=1)
	{
		header("Content-type: text/html; charset=utf-8");
    	
		$content = array(			
				'user'=>$user,
				'stime'=>$stime,
				'sig'=>$sig,
				'apiname'=>'Open_printMsg',	
			    'sn'=>$sn,
			    'content'=>$content,
			    'times'=>$times
			);
			
		//dump($content);
	    
		$client = new HttpClient(IP,PORT);
		if(!$client->post(PATH,$content)){			
			self::$message = PrinterClass::t("Error");
		} else {
			$resp = $client->getContent();						
			if(!empty($resp)){			   
			   $resp_json = json_decode($resp,true);
			   //dump($resp_json);
			   if(is_array($resp_json) && count($resp_json)>=1){			      
			   	  if($resp_json['ret']==0){
			   	  	 return $resp_json['data'];
			   	  } else self::$message = $resp_json['msg'];
			   } else self::$message = self::t("response is not json");
			} else self::$message = self::t("empty response from api");
		}
		return false;
	}
	
	public static function printReceipt($oder_id='',$content='', $force_print=false)
	{
		
		$printer_auto_print=getOptionA('printer_auto_print');
		if(!$force_print){
			if($printer_auto_print!=1){			
				return ;
			}
		}
		
		$db=new DbExt; 
		$status='pending'; 
		$printer_sn='';
		
		if ($credentials = PrinterClass::getCredentials()){			
			$stmt="
			SELECT printer_sn FROM
			{{printer_list}}
			WHERE
			is_default='1'			
			AND merchant_id='0'
			";
			if($res=$db->rst($stmt)){
				$printer_sn=$res[0]['printer_sn'];				
			} else $status="no printer selected";
		} else $status="check printer settings";

		if(empty($content)){
			$status='content is empty';
		}
		
		$params=array(
		  'printer_sn'=>$printer_sn,
		  'content'=>$content,
		  'status'=>$status,
		  'order_id'=>$oder_id,
		  'date_created'=>FunctionsV3::dateNow(),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);		
		if(!is_numeric($params['printer_sn'])){
			$params['printer_sn']=0;
		}
		if(!is_numeric($params['order_id'])){
			$params['order_id']=0;
		}		
		
		$db->insertData("{{printer_print}}",$params);		
	}
	
	public static function prettyDate($date='')
	{
		if (!empty($date)){
			$date_format=Yii::app()->functions->getOptionAdmin('website_date_format');
			if (empty($date_format)){
				$date_format="M d,Y";
			}
			$date = date($date_format,strtotime($date));
			return Yii::app()->functions->translateDate($date);
		}
		return false;
	}
	
	public static function queryPrintJobs($user='', $stime='',$sig='',$print_order_id='')
	{
		header("Content-type: text/html; charset=utf-8");
    	
		$content = array(			
				'user'=>$user,
				'stime'=>$stime,
				'sig'=>$sig,
				'apiname'=>'Open_queryOrderState',	
			    'orderid'=>$print_order_id
			);
	    	   
		$client = new HttpClient(IP,PORT);
		if(!$client->post(PATH,$content)){			
			self::$message = PrinterClass::t("Error");
		} else {
			$resp = $client->getContent();						
			if(!empty($resp)){			   
			   $resp_json = json_decode($resp,true);
			   if(is_array($resp_json) && count($resp_json)>=1){			     
			     if($resp_json['ret']==0){
			     	if($resp_json['data']){
			     		return 'ok';
			     	} else self::$message = 'not printed';
			     } else self::$message = $resp_json['msg'];
			   } else self::$message = self::t("response is not json");
			} else self::$message = self::t("empty response from api");
		}
		return false;
	}
	
	public static function prettyPrice($amount='')
	{
		if(!empty($amount)){
			return displayPrice("",prettyFormat($amount));
		}
		return '';
	}			
	
	public static function printReceiptMerchant($mtid='', $oder_id='',$content='', $print=false)
	{
		
		if(!$print){
			$printer_auto_print=getOption($mtid,'mt_printer_auto_print');
			if($printer_auto_print!=1){
				return ;
			}
		}
		
		$db=new DbExt; 
		$status='pending'; 
		$printer_sn='';
		
		if ($credentials = PrinterClass::getMerchantCredentials($mtid)){
			$printer_sn = getOption($mtid,'mt_printer_sn');
			if(empty($printer_sn)){
				$status = "no printer selected";
			}
		} else $status="check printer settings";
		
		if(empty($content)){
			$status='content is empty';
		}
		
		$params=array(
		  'printer_sn'=>$printer_sn,
		  'content'=>$content,
		  'status'=>$status,
		  'order_id'=>$oder_id,
		  'merchant_id'=>$mtid,
		  'date_created'=>FunctionsV3::dateNow(),
		  'ip_address'=>$_SERVER['REMOTE_ADDR']
		);		
		$db->insertData("{{printer_print}}",$params);
		
	}
		
} /*end class*/

function tp($message='')
{
	return Yii::t("printer",$message);
}