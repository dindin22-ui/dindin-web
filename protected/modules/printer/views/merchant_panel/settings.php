<?php echo CHtml::beginForm(); ?>   


<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
<li role="presentation" class="active">
<a href="#tab1" aria-controls="home" role="tab" data-toggle="tab">
 <?php echo PrinterClass::t("Feieyun open printer")?>
</a>
</li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
  <div role="tabpanel" class="tab-pane active" id="tab1">

   
   
   
<div class="row">
  <div class="col-md-6">
    
  <div class="form-group">
	<label ><?php echo PrinterClass::t("User")?></label>
	<?php 
	echo CHtml::textField('mt_printer_user',getOption($mtid,'mt_printer_user'),array(
	'class'=>'form-control',
	));
	?>
  </div>
  
  </div> <!--col-->  
</div> <!--row-->


<div class="row">
  <div class="col-md-6">
  
  <div class="form-group">
	<label ><?php echo PrinterClass::t("UKEY")?></label>
	<?php 
	echo CHtml::textField('mt_printer_ukey',getOption($mtid,'mt_printer_ukey'),array(
	'class'=>'form-control',
	));
	?>
  </div>
  
  </div> <!--col-->  
</div> <!--row-->



<div class="row">
  <div class="col-md-6">
  
  <div class="form-group">
	<label ><?php echo PrinterClass::t("Auto print")?></label>	
	<?php 
	$printer_auto_print=getOption($mtid,'mt_printer_auto_print');
	echo CHtml::checkBox('mt_printer_auto_print',
	$printer_auto_print==1?true:false
	,array(
	  'value'=>1
	));
	?>
	<p><?php echo PrinterClass::t("Print receipt automatically when there is new order")?>.</p>
  </div>
  
  </div> <!--col-->  
</div> <!--row-->


 <div class="form-group pad10">  
  <?php
echo CHtml::ajaxSubmitButton(
	PrinterClass::t('Save Settings'),
	array('ajax/mt_savesettings'),
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
  
  </div>
</div> <!--  end tab-->