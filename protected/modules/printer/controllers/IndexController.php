<?php
//if (!isset($_SESSION)) { session_start(); }

class IndexController extends CController
{	
	public $layout='layout';
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
		if (Yii::app()->controller->module->require_login){
			if(!Yii::app()->functions->isAdminLogin()){
			   $this->redirect(Yii::app()->createUrl('/admin/noaccess'));
			   Yii::app()->end();		
			}
		}
		
		   /*CHECK DATABASE*/
	    $new=0;
	    if( !FunctionsV3::checkIfTableExist('printer_list')){
			$new++;
		}	
		
		if ($new>0){
			$this->needs_db_update=true;
		} else $this->needs_db_update=false;
		
		return true;
	}
	
	public function actionIndex(){
		$this->pageTitle = PrinterClass::t('dashboard');
		$this->render('settings');
	}		
	
	public function actionPrinter(){
		$this->pageTitle = PrinterClass::t('printer');
		$this->render('printer-list');
	}	

	public function actionadd_printer()
	{
		$data=array();
		if (isset($_GET['id'])){
			$data = PrinterClass::getPrinterByID($_GET['id']);
		}
		$this->pageTitle = PrinterClass::t('printer');
		$this->render('printer-add',array(
		  'data'=>$data
		));
	}
	
	public function actionlogs()
	{
		$this->pageTitle = PrinterClass::t('Print Record');
		$this->render('print-logs');
	}
	
	public function actiontemplate()
	{
		$this->pageTitle = PrinterClass::t('Templates');
		$this->render('print-template');
	}
	
	public function actioncronjobs()
	{
		$this->pageTitle = PrinterClass::t('Cron jobs');
		$this->render('cron-jobs');
	}
	
} /*end class*/