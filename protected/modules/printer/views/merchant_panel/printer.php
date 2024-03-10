
<?php echo CHtml::beginForm('/','post',array(
 'class'=>"form-horizontal"
)); 
?>  
<div class="row">
  <div class="col-md-6">
  
  <div class="form-group">
	<label class="control-label col-sm-2" ><?php echo PrinterClass::t("SN")?></label>
	<div class="col-sm-10">
	<?php 
	$mt_printer_sn = getOption($mtid,'mt_printer_sn');
	echo CHtml::textField('mt_printer_sn',
	$mt_printer_sn
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
	echo CHtml::textField('mt_printer_key',
	getOption($mtid,'mt_printer_key')
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
	echo CHtml::textField('mt_printer_name',
	getOption($mtid,'mt_printer_name')
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
	array('ajax/mt_add_printer'),
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
<?php if(!empty($mt_printer_sn)):?>
<a style="margin-left:10px;" href="javascript:;" class="btn btn-default mt_delete_printer"><?php echo t("Delete Printer")?></a>
 <a style="margin-left:10px;" href="javascript:;" class="btn btn-default mt_print"><?php echo t("Test Print")?></a>
 <a style="margin-left:10px;" href="javascript:;" class="btn btn-default mt_printer_check_status"><?php echo t("Check Status")?></a>
<?php endif;?>
</div>

<?php echo CHtml::endForm(); ?>