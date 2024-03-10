
<?php echo CHtml::beginForm('/','post',array(
 'class'=>"form-horizontal"
)); 
?>  
<div class="row">
  <div class="col-md-6">
  
  <div class="form-group">
	<label class="control-label col-sm-2" ><?php echo PrinterClass::t("Device ID")?></label>
	<div class="col-sm-10">
	<?php 
	echo CHtml::textField('mt_hccprinter_device_id',
	getOption($mtid,'mt_hccprinter_device_id')
	,array(
	'class'=>'form-control',	
	));
	?>
	</div>
  </div>
  
  <div class="form-group">
	<label class="control-label col-sm-2" ><?php echo PrinterClass::t("Secret Key")?></label>
	<div class="col-sm-10">
	<?php 
	echo CHtml::textField('mt_hccprinter_secret_key',
	getOption($mtid,'mt_hccprinter_secret_key')
	,array(
	'class'=>'form-control',	
	));
	?>
	</div>
  </div>
  
  </div> <!--col-->  
</div> <!--row-->


 
<div style="padding-left:90px;">
  <?php
echo CHtml::ajaxSubmitButton(
	PrinterClass::t('Save Settings'),
	array('ajax/mt_add_hccprinter'),
	array(
		'type'=>'POST',
		'dataType'=>'json',
		'beforeSend'=>'js:function(){
		                 busy(true); 	
		                 $("#save-hccprinter-settings").val("'.PrinterClass::t('Processing').'");
		                 $("#save-hccprinter-settings").css({ "pointer-events" : "none" });	                 	                 
		              }
		',
		'complete'=>'js:function(){
		                 busy(false); 		                 
		                 $("#save-hccprinter-settings").val("'.PrinterClass::t("Save Settings").'");
		                 $("#save-hccprinter-settings").css({ "pointer-events" : "auto" });	                 	                 
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
	  'id'=>'save-hccprinter-settings'
	)
);
?>
<?php if(!empty($mt_printer_sn)):?>
<a style="margin-left:10px;" href="javascript:;" class="btn btn-default mt_delete_printer"><?php echo t("Delete Printer")?></a>
 <a style="margin-left:10px;" href="javascript:;" class="btn btn-default mt_print"><?php echo t("Test Print")?></a>
 <a style="margin-left:10px;" href="javascript:;" class="btn btn-default mt_printer_check_status"><?php echo t("Check Status")?></a>
<?php endif;?>
</div>

<?php echo CHtml::endForm(); ?>