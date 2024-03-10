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
		
		FunctionsV3::handleLanguage();
	    $lang=Yii::app()->language;	    	   
	    if(isset($_GET['debug'])){
	       dump($lang);
	    }
	}
	
	public function beforeAction($action)
	{
		if(!Yii::app()->functions->isAdminLogin() ){
			$this->msg = t("session has expired");
			$this->jsonResponse();
            Yii::app()->end();
		}		
		return true;
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
    	
	public function actionSaveSettings()
	{		
				
		Yii::app()->functions->updateOptionAdmin('merchant_android_api_key',
				isset($this->data['merchant_android_api_key'])?$this->data['merchant_android_api_key']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('tpl_order_accept_title',
				isset($this->data['tpl_order_accept_title'])?$this->data['tpl_order_accept_title']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('tpl_order_accept_content',
				isset($this->data['tpl_order_accept_content'])?$this->data['tpl_order_accept_content']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('tpl_order_denied_title',
				isset($this->data['tpl_order_denied_title'])?$this->data['tpl_order_denied_title']:''
		);
		Yii::app()->functions->updateOptionAdmin('tpl_order_denied_content',
				isset($this->data['tpl_order_denied_content'])?$this->data['tpl_order_denied_content']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('tpl_order_change_title',
				isset($this->data['tpl_order_change_title'])?$this->data['tpl_order_change_title']:''
		);
		Yii::app()->functions->updateOptionAdmin('tpl_order_change_content',
				isset($this->data['tpl_order_change_content'])?$this->data['tpl_order_change_content']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('sms_tpl_order_accept_content',
				isset($this->data['sms_tpl_order_accept_content'])?$this->data['sms_tpl_order_accept_content']:''
		);
		Yii::app()->functions->updateOptionAdmin('sms_tpl_order_denied_content',
				isset($this->data['sms_tpl_order_denied_content'])?$this->data['sms_tpl_order_denied_content']:''
		);
		Yii::app()->functions->updateOptionAdmin('sms_tpl_order_change_content',
				isset($this->data['sms_tpl_order_change_content'])?$this->data['sms_tpl_order_change_content']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('push_tpl_new_order_title',
				isset($this->data['push_tpl_new_order_title'])?$this->data['push_tpl_new_order_title']:''
		);
		Yii::app()->functions->updateOptionAdmin('push_tpl_new_order_content',
				isset($this->data['push_tpl_new_order_content'])?$this->data['push_tpl_new_order_content']:''
		);
				
		Yii::app()->functions->updateOptionAdmin('mt_ios_push_dev_cer',
				isset($this->data['mt_ios_push_dev_cer'])?$this->data['mt_ios_push_dev_cer']:''
		);
		Yii::app()->functions->updateOptionAdmin('mt_ios_push_prod_cer',
				isset($this->data['mt_ios_push_prod_cer'])?$this->data['mt_ios_push_prod_cer']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('mt_ios_push_mode',
				isset($this->data['mt_ios_push_mode'])?$this->data['mt_ios_push_mode']:''
		);
		Yii::app()->functions->updateOptionAdmin('mt_ios_passphrase',
				isset($this->data['mt_ios_passphrase'])?$this->data['mt_ios_passphrase']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('push_tpl_booking_title',
				isset($this->data['push_tpl_booking_title'])?$this->data['push_tpl_booking_title']:''
		);
		Yii::app()->functions->updateOptionAdmin('push_tpl_booking_content',
				isset($this->data['push_tpl_booking_content'])?$this->data['push_tpl_booking_content']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('tpl_booking_approved_title',
				isset($this->data['tpl_booking_approved_title'])?$this->data['tpl_booking_approved_title']:''
		);
		Yii::app()->functions->updateOptionAdmin('tpl_booking_approved_content',
				isset($this->data['tpl_booking_approved_content'])?$this->data['tpl_booking_approved_content']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('tpl_booking_denied_title',
				isset($this->data['tpl_booking_denied_title'])?$this->data['tpl_booking_denied_title']:''
		);
		Yii::app()->functions->updateOptionAdmin('tpl_booking_denied_content',
				isset($this->data['tpl_booking_denied_content'])?$this->data['tpl_booking_denied_content']:''
		);
		Yii::app()->functions->updateOptionAdmin('merchant_app_pending_tabs',
				isset($this->data['merchant_app_pending_tabs'])?json_encode($this->data['merchant_app_pending_tabs']):''
		);
		Yii::app()->functions->updateOptionAdmin('merchant_app_new_order_status',
				isset($this->data['merchant_app_new_order_status'])?$this->data['merchant_app_new_order_status']:''
		);
		Yii::app()->functions->updateOptionAdmin('merchant_app_hash_key',
				isset($this->data['merchant_app_hash_key'])?$this->data['merchant_app_hash_key']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('drv_order_status',
				isset($this->data['drv_order_status'])?$this->data['drv_order_status']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('push_booking_accepted_title',
				isset($this->data['push_booking_accepted_title'])?$this->data['push_booking_accepted_title']:''
		);
		Yii::app()->functions->updateOptionAdmin('push_booking_accepted_content',
				isset($this->data['push_booking_accepted_content'])?$this->data['push_booking_accepted_content']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('push_booking_decline_title',
				isset($this->data['push_booking_decline_title'])?$this->data['push_booking_decline_title']:''
		);
		Yii::app()->functions->updateOptionAdmin('push_booking_decline_content',
				isset($this->data['push_booking_decline_content'])?$this->data['push_booking_decline_content']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchant_app_force_lang',
		isset($this->data['merchant_app_force_lang'])?$this->data['merchant_app_force_lang']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchant_app_accept_order_status',
		isset($this->data['merchant_app_accept_order_status'])?$this->data['merchant_app_accept_order_status']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchant_app_decline_order_status',
		isset($this->data['merchant_app_decline_order_status'])?$this->data['merchant_app_decline_order_status']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchantapp_push_server_key',
		isset($this->data['merchantapp_push_server_key'])?$this->data['merchantapp_push_server_key']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchant_app_enabled_alert',
		isset($this->data['merchant_app_enabled_alert'])?$this->data['merchant_app_enabled_alert']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchant_app_alert_interval',
		isset($this->data['merchant_app_alert_interval'])?$this->data['merchant_app_alert_interval']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchant_app_cancel_order_alert',
		isset($this->data['merchant_app_cancel_order_alert'])?$this->data['merchant_app_cancel_order_alert']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchant_app_cancel_order_alert_interval',
		isset($this->data['merchant_app_cancel_order_alert_interval'])?$this->data['merchant_app_cancel_order_alert_interval']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('merchant_app_keep_awake',
		isset($this->data['merchant_app_keep_awake'])?$this->data['merchant_app_keep_awake']:''
		);
		
		Yii::app()->functions->updateOptionAdmin('website_review_approved_status',
		isset($this->data['website_review_approved_status'])?$this->data['website_review_approved_status']:''
		);

		$title = isset($this->data['tab_name'])?$this->data['tab_name']:'';	
		$link = isset($this->data['tab_link'])?$this->data['tab_link']:'';	

		$params=array(		 
			'name'=> $title,
			'link'=> $link,
			'status'=> 1
		);			
		$DbExt=new DbExt; 
		if ($DbExt->insertData("{{mobile_links}}",$params)){
			$record_id=Yii::app()->db->getLastInsertID();
			$this->code=1;
			$this->msg=merchantApp::t("Settings Saved");
		} else{		
			$this->code=2;
			$this->msg=merchantApp::t("something went wrong during processing your request");
		} 

	    $this->msg=merchantApp::t("settings saved");
		$this->jsonResponse();				
	}
	
	public function actionSaveTranslation()
	{		
		$mobile_dictionary='';
		if (is_array($this->data) && count($this->data)>=1){
			$version=str_replace(".",'',phpversion());		
			//533		
			/*if ($version<5329){	
				$mobile_dictionary=MobileUnicode::jsonUnicode1($this->data);
				$unicode=1;
			} elseif ( $version>=540) {	
			    $mobile_dictionary=json_encode($this->data,JSON_UNESCAPED_UNICODE);
			    $unicode=2;
			} else {			   
				$mobile_dictionary=json_encode($this->data);			
				$unicode=3;
			}	*/		
			$mobile_dictionary=json_encode($this->data);			
			$unicode=3;
		}				
		Yii::app()->functions->updateOptionAdmin('merchant_mobile_dictionary',$mobile_dictionary);
		$this->code=1;
		$this->msg=merchantApp::t("translation saved");
		$this->details=$unicode;
		$this->jsonResponse();
	}	
	
    public function actionExportLang()
	{
		$content=Yii::app()->functions->getOptionAdmin('merchant_mobile_dictionary');
		header('Content-disposition: attachment; filename=merchant_mobile_dictionary.json');
        header('Content-type: application/json');
        echo $content;										
		yii::app()->end();		
	}	

	public function actionimportLang()
	{
		require_once('Uploader.php');
		$path_to_upload=Yii::getPathOfAlias('webroot')."/upload";
        $valid_extensions = array('json'); 
        if(!file_exists($path_to_upload)) {	
           if (!@mkdir($path_to_upload,0777)){           	               	
           	    $this->msg=merchantApp::t("Error has occured cannot create upload directory");
                $this->jsonResponse();
           }		    
	    }
	    
        $Upload = new FileUpload('uploadfile');
        $ext = $Upload->getExtension();         
        $result = $Upload->handleUpload($path_to_upload, $valid_extensions);                
        if (!$result) {                    	
            $this->msg=$Upload->getErrorMsg();            
        } else {         	
        	$this->code=1;
        	$this->msg=merchantApp::t("upload done. kindly refresh your browser to see the changes affect"); 
			$this->details=Yii::app()->getBaseUrl(true)."/upload/".$_GET['uploadfile'];	
			
			$content = @file_get_contents($path_to_upload ."/".$_GET['uploadfile']);
			Yii::app()->functions->updateOptionAdmin('merchant_mobile_dictionary',$content);
        }
        $this->jsonResponse();
	}	

	public function actionRegisteredDeviceList()
	{
	   
				 
		$aColumns = array(
		  'a.id','a.merchant_id',
		  'a.device_platform','a.user_type',
		  'a.device_id','a.enabled_push','a.date_created','a.id','m.restaurant_name'
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
		
		$stmt="SELECT SQL_CALC_FOUND_ROWS a.*,
		(
		select username from
		{{merchant}}
		where
		merchant_id =a.merchant_id 
		limit 0,1
		) as merchant_username,
		
		(
		select username from
		{{merchant_user}}
		where
		merchant_user_id =a.merchant_user_id 
		limit 0,1
		) as user_username,
		
		(
		select restaurant_name 
		from 
		{{merchant}}
		where 
		merchant_id=a.merchant_id
		) as merchant_name
		
		FROM
		{{mobile_device_merchant}} a LEFT JOIN {{merchant}} m ON a.merchant_id=m.merchant_id
		WHERE 				
		a.status='active'
		AND
		( a.merchant_id > 0 OR a.merchant_user_id  > 0 )
		
		$sWhere
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
				$date_created=Yii::app()->functions->prettyDate($val['date_created'],true);
			    $date_created=Yii::app()->functions->translateDate($date_created);					
			    
			    $link=Yii::app()->createUrl('merchantapp/index/push',array(
			      'id'=>$val['id']
			    ));
			    $psh=merchantApp::t("Send a push");
			    $action="<a class=\"send-a-push\" data-id=\"$val[id]\" href=\"$link\" title=\"$psh\">
			    <i class=\"fa fa-commenting\" ></i>
			    </a>";
			    
			    $username=$val['merchant_username'];
			    if ( $val['user_type']=="user"){
			    	$username=$val['user_username'];
			    }
			    
				$feed_data['aaData'][]=array(
				  $val['id'],
				  !empty($val['merchant_name'])?stripslashes($val['merchant_name']):merchantApp::t("No name"),
				  $val['device_platform'],
				  $username,
				  $val['user_type'],
				  "<p class=\"concat-text\">".$val['device_id']."..."."</p>",
				  $val['enabled_push']==1?merchantApp::t("Yes"):'',				  
				  $date_created,
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
	
	public function actionUploadCertificate()
	{
		require_once('Uploader.php');
		$path_to_upload=Yii::getPathOfAlias('webroot')."/upload/mt_certificate";
        $valid_extensions = array('pem'); 
        if(!file_exists($path_to_upload)) {	
           if (!@mkdir($path_to_upload,0777)){           	               	
           	    $this->msg=merchantApp::t("Error has occured cannot create upload directory");
                $this->jsonResponse();
           }		    
	    }
	    
        $Upload = new FileUpload('uploadfile');
        $ext = $Upload->getExtension(); 
        //$Upload->newFileName = mktime().".".$ext;
        $result = $Upload->handleUpload($path_to_upload, $valid_extensions);                
        if (!$result) {                    	
            $this->msg=$Upload->getErrorMsg();            
        } else {         	
        	$this->code=1;
        	$this->msg=merchantApp::t("upload done");        	        
			$this->details=Yii::app()->getBaseUrl(true)."/upload/".$_GET['uploadfile'];			
        }
        $this->jsonResponse();
	}	
	
	public function actionpushLogs()
	{
		$aColumns = array(
		  'id',
		  'merchant_id',
		  'merchant_id',
		  'device_platform',
		  'device_id',
		  'push_title',
		  'push_message',
		  'push_type',
		  'status',
		  'date_created'
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
		
		$stmt="SELECT SQL_CALC_FOUND_ROWS a.*,
		(
		select username from
		{{merchant}}
		where
		merchant_id =a.merchant_id 
		limit 0,1
		) as merchant_username,
		
		(
		select username from
		{{merchant_user}}
		where
		merchant_user_id =a.merchant_user_id 
		limit 0,1
		) as user_username,
		
		(
		select restaurant_name 
		from
		{{merchant}}
		where
		merchant_id =a.merchant_id 
		limit 0,1
		)as merchant_name
		
		FROM
		{{mobile_merchant_pushlogs}} a
		WHERE 1		
		
		$sWhere
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
								
				$date_created=Yii::app()->functions->prettyDate($val['date_created'],true);
			    $date_created=Yii::app()->functions->translateDate($date_created);					
			    
			    
			    $username=$val['merchant_username'];
			    if ( $val['user_type']=="user"){
			    	$username=$val['user_username'];
			    }
			    
				$feed_data['aaData'][]=array(
				  $val['id'],
				  stripslashes($val['merchant_name']),
				  $username,
				  $val['device_platform'],
				  '<span class="concat-text">'.$val['device_id'].'</span>',
				  $val['push_title'],
				  $val['push_message'],
				  merchantApp::t($val['push_type']),				  
				  '<span class="tag '.$val['status'].'">'.merchantApp::t($val['status']).'</span>',
				  $date_created
				);
			}
			if (isset($_GET['debug'])){
			   dump($feed_data);
			}
			$this->otableOutput($feed_data);	
		}
		$this->otableNodata();		
	}

	public function removeMenuLink(){
		
	}
	public function actiontabLinks()
	{
		$aColumns = array(
		  'id',
		  'name',
		  'link',
		  'date_created'
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
		
		$stmt="
		select * 
		
		FROM
		{{mobile_links}} a
		WHERE 1		
		
		$sWhere
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
				$action = "<a style='margin-right: 20px;' data-id=\"" . $val['id'] . "\" class=\"edit-link\" href=\"javascript:\">" . Yii::t("default", "Edit") . "</a>";
				$action .= "   <a data-id=\"" . $val['id'] . "\" class=\"remove-link\" href=\"javascript:\">" . Yii::t("default", "Remove") . "</a>";

				$date_created=Yii::app()->functions->prettyDate($val['date_created'],true);
			    $date_created=Yii::app()->functions->translateDate($date_created);					
			    if($val['status'] == 1){
					$status = 'process';
					$status_text = 'Enabled';
				}else{
					$status = 'disabled';
					$status_text = 'Disabled';
				}
			    
			    
			    
				$feed_data['aaData'][]=array(
				  $val['id'],
				  $val['name'],
				  $val['link'],
				  $date_created,
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
	
	function actioneditOrder() {
		$id = $_GET['id'];
		$stmt="
		select * 
		
		FROM
		{{mobile_links}} a
		WHERE id=".$id."	
		";
		if (isset($_GET['debug'])){
		   dump($stmt);
		}
		
		$DbExt=new DbExt;
		$res=$DbExt->rst($stmt);
		?>
	<div class="view-receipt-pop" style="width:400pxs">
	<h3><?php echo Yii::t("default", 'Update Tab Link') ?></h3>
	
	<?php if (isset($res) && count($res) > 0 ): 
			$menu_ink = $res[0];
		?>
		<form id="frm-comment-pop" class="frm-pop uk-form uk-form-horizontal" method="POST" onsubmit="return false;">
	
		<?php echo CHtml::hiddenField('id', $_GET['id']) ?>
	
			<div class="uk-form-row">
				<label class="uk-form-label"><?php echo Yii::t("default", 'Name') ?></label>
				<?php 
				echo CHtml::textField('name',
				$menu_ink['name']
				,array(
					'class'=>"uk-form-width-large"
				))
				?>
			</div>
	
	
			<div class="uk-form-row">
				<label class="uk-form-label"><?php echo Yii::t("default", 'Link') ?></label>
				<?php 
				echo CHtml::textField('link',
				$menu_ink['link']
				,array(
					'class'=>"uk-form-width-large"
				))
				?>
			</div>
	
			<!--Driver-->
				<?php
				/* Yii::app()->setImport(array(			
				  'application.modules.driver.components.*',
				  ));
				  Driver::AdminStatusTpl(); */
				?>		    	 
			<!--Driver-->
	
	
			<div class="action-wrap uk-form-row">
			<?php echo CHtml::submitButton('Submit',
					array('value' => Yii::t("default", 'Submit'), 'class' => "update-link uk-button uk-form-width-medium uk-button-success"))
			?>
			</div>
		</form> 
			<?php else: ?>
		<p class="uk-text-danger"><?php echo Yii::t("default", "Error: Link not found") ?></p>
			<?php endif; ?>
	</div> <!--view-receipt-pop-->	    					    
	
	<?php
	die();
	}

	public function actionUpdateCustomLink(){
		$id = $_POST['id'];
		$name = $_POST['name'];
		$link = $_POST['link'];
		$params=array(
			'name'=>$name,
			'link' => $link
		);
		$DbExt=new DbExt;
		$DbExt->updateData("{{mobile_links}}",$params,'id',$id);
		echo 1;
	}

	public function actionRemoveCustomLink(){
		$id = $_GET['id'];
		$DbExt=new DbExt;
		$sql_delete = "DELETE FROM
							{{mobile_links}}
							WHERE
							id=".$id;
		$DbExt->qry($sql_delete);	
		echo 1;
	}

	public function actionSendPush()
	{		
		$validator=new Validator();
		$req=array( 
		  'device_id'=>merchantApp::t("device id is missing"),
		  'push_title'=>merchantApp::t("push title is required"),
		  'push_message'=>merchantApp::t("push message is required"),
		);
		$validator->required($req,$this->data);
		if ( $validator->validate()){
			$params=array(			 
			  'merchant_id'=>isset($this->data['merchant_id'])?$this->data['merchant_id']:'',
			  'user_type'=>isset($this->data['user_type'])?$this->data['user_type']:'',
			  'merchant_user_id'=>isset($this->data['merchant_user_id'])?$this->data['merchant_user_id']:'',
			  'device_platform'=>isset($this->data['device_platform'])?$this->data['device_platform']:'',
			  'device_id'=>isset($this->data['device_id'])?$this->data['device_id']:'',
			  'push_title'=>isset($this->data['push_title'])?$this->data['push_title']:'',
                'push_link'=>isset($this->data['push_link'])?$this->data['push_link']:'',
                'push_link_processed'=>empty($this->data['push_link'])?0:1,
			  'push_message'=>isset($this->data['push_message'])?$this->data['push_message']:'',
			  'date_created'=>FunctionsV3::dateNow(),
			  'ip_address'=>$_SERVER['REMOTE_ADDR'],
			  'push_type'=>"campaign"
			);			
			$DbExt=new DbExt; 
			if ($DbExt->insertData("{{mobile_merchant_pushlogs}}",$params)){
				$record_id=Yii::app()->db->getLastInsertID();
				$this->code=1;
				$this->msg=merchantApp::t("push has been saved. you can check the status on push notification logs section");
				
				/*PROCESS THE PUSH*/				
				FunctionsV3::fastRequest(FunctionsV3::getHostURL().Yii::app()->createUrl("merchantapp/cron/processpush"));
				
			} else $this->msg=merchantApp::t("something went wrong during processing your request");
		} else $this->msg= $validator->getErrorAsHTML();
		$this->jsonResponse();
	}
	
} /*end class*/