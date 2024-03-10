<?php
/* ********************************************************
 *   Karenderia Printer modules
 *
 *   Initial Release : 15 March 2018 Version 1.0
 *   Last Update     : 16 April 2018 Version 2.0 
***********************************************************/
class PrinterModule extends CWebModule
{
	public $require_login;
	
	public function init()
	{
		
		$session = Yii::app()->session;
		
		// this method is called when the module is being created
		// you may place code here to customize the module or the application
		
		// import the module-level models and components
		$this->setImport(array(
			//'exportmanager.models.*',
			'printer.components.*',
		));
		
		$ajaxurl=Yii::app()->baseUrl.'/printer/ajax';
		
		Yii::app()->clientScript->scriptMap=array(
          'jquery.js'=>false,
          'jquery.min.js'=>false
        );

		$cs = Yii::app()->getClientScript();  
		$cs->registerScript(
		  'ajaxurl',
		 "var ajaxurl='$ajaxurl'",
		  CClientScript::POS_HEAD
		);
		
		$csrfTokenName = Yii::app()->request->csrfTokenName;
        $csrfToken = Yii::app()->request->csrfToken;        
        
		$cs->registerScript(
		  "$csrfTokenName",
		 "var $csrfTokenName='$csrfToken';",
		  CClientScript::POS_HEAD
		);

		
		/*JS FILE*/
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/printer/assets/jquery-1.10.2.min.js',
		CClientScript::POS_END
		);
				
		Yii::app()->clientScript->registerScriptFile(
        '//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js',
		CClientScript::POS_END
		);
						
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/printer/assets/chosen/chosen.jquery.min.js',
		CClientScript::POS_END
		);		
		
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/printer/assets/SimpleAjaxUploader.min.js',
		CClientScript::POS_END
		);		
		
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/printer/assets/noty-2.3.7/js/noty/packaged/jquery.noty.packaged.min.js',
		CClientScript::POS_END
		);		
		
		/*Yii::app()->clientScript->registerScriptFile(
        '//cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js',
		CClientScript::POS_END
		);*/		
		
		$baseUrl = Yii::app()->baseUrl."/protected/modules/printer"; 
		$cs->registerScriptFile($baseUrl."/assets/DataTables/jquery.dataTables.min.js"
		,CClientScript::POS_END); 
		$cs->registerScriptFile($baseUrl."/assets/DataTables/fnReloadAjax.js"
		,CClientScript::POS_END); 
				
		Yii::app()->clientScript->registerScriptFile(
        Yii::app()->baseUrl . '/protected/modules/printer/assets/printer.js?ver=1.0',
		CClientScript::POS_END
		);		
						
				
		/*CSS FILE*/
		$baseUrl = Yii::app()->baseUrl."/protected/modules/printer"; 
		$cs = Yii::app()->getClientScript();		
		$cs->registerCssFile("//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css");
		$cs->registerCssFile("//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css");	
		$cs->registerCssFile($baseUrl."/assets/chosen/chosen.min.css");		
		$cs->registerCssFile("//cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css");
		$cs->registerCssFile($baseUrl."/assets/animate.css?ver=1.0");
		$cs->registerCssFile($baseUrl."/assets/printer.css?ver=1.0");
	}

	public function beforeControllerAction($controller, $action)
	{				
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here									
			return true;
		}
		else
			return false;
	}
	
} /*end class*/