<div class="text-right">
<a class="btn btn-default" href="<?php echo Yii::app()->createUrl('printer/index/printer')?>">
<?php echo PrinterClass::t("Back")?>
</a>
</div>

<?php echo CHtml::beginForm('/','post',array(
 'class'=>"form-horizontal"
)); 

if (isset($data['printer_id'])){
	echo CHtml::hiddenField('id',$data['printer_id']);
}
if (isset($_GET['msg'])){
	echo CHtml::hiddenField('msg_alert',$_GET['msg']);
}
?>  


<div class="row">
  <div class="col-md-6">
  
  <div class="form-group">
	<label class="control-label col-sm-2" ><?php echo PrinterClass::t("SN")?></label>
	<div class="col-sm-10">
	<?php 
	echo CHtml::textField('printer_sn',
	isset($data['printer_sn'])?$data['printer_sn']:''
	,array(
	'class'=>'form-control',	
	));
	?>
	</div>
  </div>
  
  <div class="form-group">
	<label class="control-label col-sm-2" ><?php echo PrinterClass::t("KEY")?></label>
	<div class="col-sm-10">
	<?php 
	echo CHtml::textField('printer_key',
	isset($data['printer_key'])?$data['printer_key']:''
	,array(
	'class'=>'form-control',	
	));
	?>
	</div>
  </div>
  
  <div class="form-group">
	<label class="control-label col-sm-2" ><?php echo PrinterClass::t("Name")?></label>
	<div class="col-sm-10">
	<?php 
	echo CHtml::textField('printer_name',
	isset($data['printer_name'])?$data['printer_name']:''
	,array(
	'class'=>'form-control',	
	));
	?>
	</div>
  </div>
  
  <div class="form-group">
	<label class="control-label col-sm-2" ><?php echo PrinterClass::t("Set Default Printer")?></label>
	<div class="col-sm-10">
	<?php 
	$data['is_default']=isset($data['is_default'])?$data['is_default']:'';
	echo CHtml::checkBox('is_default',
	$data['is_default']==1?true:false
	,array(
	  'value'=>1
	));
	?>
	</div>
  </div>
  
  </div> <!--col-->  
</div> <!--row-->


 
<div style="padding-left:90px;">
  <?php
echo CHtml::ajaxSubmitButton(
	PrinterClass::t('Save'),
	array('ajax/add_printer'),
	array(
		'type'=>'POST',
		'dataType'=>'json',
		'beforeSend'=>'js:function(){
		                 busy(true); 	
		                 $("#save-settings").val("'.PrinterClass::t('Processing').'");
		                 $("#save-settings").css({ "pointer-events" : "none" });	                 	                 
		              }
		',
		'complete'=>'js:function(){
		                 busy(false); 		                 
		                 $("#save-settings").val("'.PrinterClass::t("Save Settings").'");
		                 $("#save-settings").css({ "pointer-events" : "auto" });	                 	                 
		              }',
		'success'=>'js:function(data){	
		               if(data.code==1){		               
		                  noty_msg_success(data.msg);
		                  if (!empty(data.details)){
		                      window.location.href =  data.details;
		                  }
		               } else {
		                  noty_msg(data.msg);
		               }
		            }
		'
	),array(
	  'class'=>'btn btn-primary',
	  'id'=>'save-settings'
	)
);
?>
</div>

<?php echo CHtml::endForm(); ?>