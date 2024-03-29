<?php
class DbExt
{			
	public function rst($sql='')
	{		
		//Yii::app()->db->active = false;		
		if (!empty($sql)){
			$connection=Yii::app()->db;
		    $rows=$connection->createCommand($sql)->queryAll();
		    if (is_array($rows) && count($rows)>=1){
		    	return $rows;
		    } else return false;
		} else return false;
	}
	
	public function rst_special($sql='')
	{		
		//Yii::app()->db->active = false;		
		if (!empty($sql)){
			$connection=Yii::app()->db_special;
		    $rows=$connection->createCommand($sql)->queryAll();
		    if (is_array($rows) && count($rows)>=1){
		    	return $rows;
		    } else return false;
		} else return false;
	}
	
	public function qry($sql='')
	{		
		if (!empty($sql)){			
			if (Yii::app()->db->createCommand($sql)->query()){
			    return true;
		    } else return false;
		} else return false;
	}
	
	public function qrySp($sql='')
	{		
		if (!empty($sql)){			
			if (Yii::app()->db_special->createCommand($sql)->query()){
			    return true;
		    } else return false;
		} else return false;
	}
	
	
	public function insertData($table='' ,$data=array()){
	   // print_r($data); exit('hello-dbb');
		$connection=Yii::app()->db;
		$command = Yii::app()->db->createCommand();
		if ($command->insert($table,$data)){
			return true;
		} 
		return false;
	}
	
	public function insertSpecialData($table='' ,$data=array()){
	   // print_r($data); exit('hello-dbb'); 
		$connection=Yii::app()->db_special;
		$command = Yii::app()->db_special->createCommand();
		if ($command->insert($table,$data)){
			return true;
		} 
		return false;
	}
	
	public function updateData($table='' ,$data=array() , $wherefield='', $whereval=''){						
		$connection=Yii::app()->db;
		$command = Yii::app()->db->createCommand();
		$res = $command->update($table , $data , 
               "$wherefield=:$wherefield" , array(":$wherefield"=> addslashes($whereval) ));
        if ($res){
        	return true;
        }
        return false;
	}
	
	
	public function updateSpecialData($table='' ,$data=array() , $wherefield='', $whereval=''){						
		$connection=Yii::app()->db_special;
		$command = Yii::app()->db_special->createCommand();
		$res = $command->update($table , $data , 
               "$wherefield=:$wherefield" , array(":$wherefield"=> addslashes($whereval) ));
        if ($res){
        	return true;
        }
        return false;
	}
	
    public	function getClient($table='', $client_token='')
	{
		$clients = Yii::app()->db->createCommand()
		    ->select('client_id')
		    ->from($table)
		    ->where('token=:token', array(':token'=>$client_token))
		    ->queryRow();
		   if ($clients) {
		   	 return $clients;
		   }else
		   return	false;
		   

	}
	
		
}
/*END: Cdb*/