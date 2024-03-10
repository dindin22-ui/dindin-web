<?php
class UpdateController extends CController
{
	
	public function beforeAction($action)
	{		
		if(!Yii::app()->functions->isAdminLogin()){			
			Yii::app()->end();		
		}
		return true;
	}
	
	public function actionIndex()
	{
		$prefix=Yii::app()->db->tablePrefix;		
		$table_prefix=$prefix;		
		$DbExt=new DbExt;
		
		$stmt="		
		CREATE TABLE IF NOT EXISTS ".$table_prefix."printer_list (
		  `printer_id` int(14) NOT NULL,
		  `merchant_id` int(14) NOT NULL DEFAULT '0',
		  `printer_sn` varchar(50) NOT NULL DEFAULT '',
		  `printer_key` varchar(50) NOT NULL DEFAULT '',
		  `printer_name` varchar(255) NOT NULL DEFAULT '',
		  `is_default` int(1) NOT NULL DEFAULT '0',
		  `api_response` text,
		  `printer_status` varchar(255) NOT NULL DEFAULT '',
		  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `ip_address` varchar(50) NOT NULL DEFAULT ''
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		
		 ALTER TABLE ".$table_prefix."printer_list
         ADD PRIMARY KEY (`printer_id`);
        
         ALTER TABLE ".$table_prefix."printer_list
         MODIFY `printer_id` int(14) NOT NULL AUTO_INCREMENT;
		";		
		echo "Creating Table printer_list..<br/>";	
		$DbExt->qry($stmt);
		echo "(Done)<br/>"; 
		
		$stmt="	
		CREATE TABLE IF NOT EXISTS ".$table_prefix."printer_print (
		  `id` int(14) NOT NULL,
		  `printer_type` varchar(50) DEFAULT 'open_printer',
		  `printer_sn` int(14) NOT NULL DEFAULT '0',
		  `content` text,
		  `status` varchar(255) NOT NULL DEFAULT 'pending',
		  `query_status` varchar(50) NOT NULL DEFAULT 'pending',
		  `print_order_id` varchar(255) NOT NULL DEFAULT '',
		  `order_id` int(14) NOT NULL DEFAULT '0',
		  `merchant_id` varchar(14) NOT NULL DEFAULT '0',
		  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `ip_address` varchar(50) NOT NULL DEFAULT ''
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		
		ALTER TABLE ".$table_prefix."printer_print
		ADD PRIMARY KEY (`id`);

		ALTER TABLE ".$table_prefix."printer_print
        MODIFY `id` int(14) NOT NULL AUTO_INCREMENT;
		";
		echo "Creating Table printer_print..<br/>";	
		$DbExt->qry($stmt);
		echo "(Done)<br/>"; 		

		
		/*ADD INDEX*/	
		
		$this->addIndex('printer_list','merchant_id');
		$this->addIndex('printer_list','printer_sn');
		$this->addIndex('printer_list','is_default');
		$this->addIndex('printer_list','printer_status');
		
		$this->addIndex('printer_print','printer_type');
		$this->addIndex('printer_print','printer_sn');
		$this->addIndex('printer_print','status');
		$this->addIndex('printer_print','query_status');
		$this->addIndex('printer_print','print_order_id');
		$this->addIndex('printer_print','order_id');
		$this->addIndex('printer_print','merchant_id');
		
		echo "(FINISH)<br/>";  
	}
	
	public function addIndex($table='',$index_name='')
	{
		$DbExt=new DbExt;
		$prefix=Yii::app()->db->tablePrefix;		
		
		$table=$prefix.$table;
		
		$stmt="
		SHOW INDEX FROM $table
		";		
		$found=false;
		if ( $res=$DbExt->rst($stmt)){
			foreach ($res as $val) {				
				if ( $val['Key_name']==$index_name){
					$found=true;
					break;
				}
			}
		} 
		
		if ($found==false){
			echo "create index<br>";
			$stmt_index="ALTER TABLE $table ADD INDEX ( $index_name ) ";
			dump($stmt_index);
			$DbExt->qry($stmt_index);
			echo "Creating Index $index_name on $table <br/>";		
            echo "(Done)<br/>";		
		} else echo 'index exist<br>';
	}
	
	public function alterTable($table='',$new_field='')
	{
		$DbExt=new DbExt;
		$prefix=Yii::app()->db->tablePrefix;		
		$existing_field='';
		if ( $res = Yii::app()->functions->checkTableStructure($table)){
			foreach ($res as $val) {								
				$existing_field[$val['Field']]=$val['Field'];
			}			
			foreach ($new_field as $key_new=>$val_new) {				
				if (!in_array($key_new,$existing_field)){
					echo "Creating field $key_new <br/>";
					$stmt_alter="ALTER TABLE ".$prefix."$table ADD $key_new ".$new_field[$key_new];
					dump($stmt_alter);
				    if ($DbExt->qry($stmt_alter)){
					   echo "(Done)<br/>";
				   } else echo "(Failed)<br/>";
				} else echo "Field $key_new already exist<br/>";
			}
		}
	}		
	
} /*end class*/