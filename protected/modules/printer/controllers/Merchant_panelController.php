<?php
if (!isset($_SESSION)) { session_start(); }

class Merchant_panelController extends CController
{
	public $layout='layout_merchant';
	public $needs_db_update=false;
	
	public function init()
	{
		FunctionsV3::handleLanguage();
		$lang=Yii::app()->language;				
		$cs = Yii::app()->getClientScript();
		$cs->registerScript(
		  'lang',
		  "var lang='$lang';",
		  CClientScript::POS_HEAD
		);
		
	   $table_translation=array(
	      "tablet_1"=>PrinterClass::t("No data available in table"),
    	  "tablet_2"=>PrinterClass::t("Showing _START_ to _END_ of _TOTAL_ entries"),
    	  "tablet_3"=>PrinterClass::t("Showing 0 to 0 of 0 entries"),
    	  "tablet_4"=>PrinterClass::t("(filtered from _MAX_ total entries)"),
    	  "tablet_5"=>PrinterClass::t("Show _MENU_ entries"),
    	  "tablet_6"=>PrinterClass::t("Loading..."),
    	  "tablet_7"=>PrinterClass::t("Processing..."),
    	  "tablet_8"=>PrinterClass::t("Search:"),
    	  "tablet_9"=>PrinterClass::t("No matching records found"),
    	  "tablet_10"=>PrinterClass::t("First"),
    	  "tablet_11"=>PrinterClass::t("Last"),
    	  "tablet_12"=>PrinterClass::t("Next"),
    	  "tablet_13"=>PrinterClass::t("Previous"),
    	  "tablet_14"=>PrinterClass::t(": activate to sort column ascending"),
    	  "tablet_15"=>PrinterClass::t(": activate to sort column descending"),
    	  'are_you_sure'=>PrinterClass::t("Are you sure")
	   );	
	   $js_translation=json_encode($table_translation);
		
	   $cs->registerScript(
		  'js_translation',
		  "var js_translation=$js_translation;",
		  CClientScript::POS_HEAD
		);	
	   	
	}
	
	public function beforeAction($action)
	{
		
		if(!Yii::app()->functions->isMerchantLogin()){
		   $this->redirect(Yii::app()->createUrl('/merchant/'));
		   Yii::app()->end();		
		}		
		return true;
	}
	
	public function actionIndex()
	{
	    $this->actionsettings();
	}
	
	public function actionsettings()
	{
		$this->pageTitle = PrinterClass::t('Settings');
		$this->render('settings',array(
		  'mtid'=>Yii::app()->functions->getMerchantID()
		));
	}
	
	public function actiontemplate()
	{
		$this->pageTitle = PrinterClass::t('Templates');
		$this->render('template',array(
		  'mtid'=>Yii::app()->functions->getMerchantID()
		));
	}	
	
	public function actionprinter()
	{
		$this->pageTitle = PrinterClass::t('Printer');
		$this->render('printer',array(
		  'mtid'=>Yii::app()->functions->getMerchantID()
		));
	}
	
	public function actionhccprinter()
	{
		// $this->pageTitle = PrinterClass::'hcc-printer';
		$this->render('hcc-printer',array(
		  'mtid'=>Yii::app()->functions->getMerchantID()
		));
	}
	
	public function actionlogs()
	{
		$this->pageTitle = PrinterClass::t('Print Logs');
		$this->render('logs',array(
		  'mtid'=>Yii::app()->functions->getMerchantID()
		));
	}
	
} /*end class*/