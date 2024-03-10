<?php
class CronController extends CController
{
	public function actionIndex()
	{
		echo "<h2>cron index</h2>";
	}
	
	
	public function actionProcessPrint()
	{
		$db = new DbExt();
		$status='';
		$job_order_id='';
		
		$stmt="SELECT * FROM
		{{printer_print}}
		WHERE
		status ='pending'		
		ORDER BY id ASC
		LIMIT 0,5
		";		
		
		//$credentials = PrinterClass::getCredentials();
		
		if($res=$db->rst($stmt)){
		  foreach ($res as $val) {		  
		  			  	
		  	if ($val['merchant_id']>=1){
				$mtid = $val['merchant_id'];
				$credentials = PrinterClass::getMerchantCredentials($mtid);						
			} else $credentials = PrinterClass::getCredentials();				
			
			
		  	 if($credentials){
		  	 	 $resp=PrinterClass::thermalPrint(
		  	 	   $credentials['user'],
		  	 	   $credentials['stime'],
		  	 	   $credentials['sig'],
		  	 	   $val['printer_sn'],
		  	 	   $val['content']
		  	 	 );
		  	 	 if ($resp){
					$job_order_id = $resp;
					$status='ok';
				} else $status = PrinterClass::$message;
		  	 } else $status="check printer settings";
		  	 
		  	 /*INSERT RECORDS*/		  	 
		  	 $params=array(
		  	   'status'=>$status,
		  	   'print_order_id'=>$job_order_id,
		  	   'date_modified'=>FunctionsV3::dateNow(),
		  	   'ip_address'=>$_SERVER['REMOTE_ADDR']
		  	 );
		  	 dump($params);
		  	 $db->updateData("{{printer_print}}",$params,'id',$val['id']);
		  }
		}
	}
	
	public function actionQueryStatus()
	{
		$db = new DbExt();
		$stmt="SELECT * FROM
		{{printer_print}}
		WHERE
		query_status ='pending'		
		AND
		status != 'pending'		
		ORDER BY id ASC
		LIMIT 0,5
		";				
		
		if ($res=$db->rst($stmt)){
			foreach ($res as $val) {
							
				if ($val['merchant_id']>=1){
					$mtid = $val['merchant_id'];
					$credentials = PrinterClass::getMerchantCredentials($mtid);						
				} else $credentials = PrinterClass::getCredentials();				
								
				$status='process';
				
				if($credentials){
					if(!empty($val['print_order_id'])){
						$resp = PrinterClass::queryPrintJobs(
						$credentials['user'],
						$credentials['stime'],
						$credentials['sig'],					
						$val['print_order_id']
						);								
						if($resp){						
							$status=$resp;
						} else $status = PrinterClass::$message;
					}
				} else $status='check printer settings';
									
				$params=array(
				  'query_status'=>$status,
				  //'status'=>$status,
				  'date_modified'=>FunctionsV3::dateNow(),
				  'ip_address'=>$_SERVER['REMOTE_ADDR']
				);
				dump($params);
				$db->updateData("{{printer_print}}",$params,'id',$val['id']);
			}
		} 		
	}
	
} /*end class*/