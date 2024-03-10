<?php echo CHtml::beginForm(); ?>   

<h3><?php echo tp("Receipt Template")?></h3>

<div class="row">
  <div class="col-md-6">
     <a href="javascript:;" data-toggle="modal" data-target="#modal_tags" ><?php echo PrinterClass::t("Available Tags")?></a>&nbsp;
     <a href="javascript:;" data-toggle="modal" data-target="#modal_printer_tags" ><?php echo PrinterClass::t("Printer Tags")?></a>
     
     <span>| </span>
     <a href="javascript:;" class="load_template" data-id="1" data-target="printer_receipt_tpl" ><?php echo PrinterClass::t("Load Sample Template 1")?></a>
     <span>| </span>
     <a href="javascript:;"  class="load_template" data-id="2" data-target="printer_receipt_tpl" ><?php echo PrinterClass::t("Load Sample Template 2")?></a>
     
  </div>  
</div>
<div class="pad10"></div>

<div id="modal_tags" class="modal fade" role="dialog">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
             <h4 class="modal-title"><?php echo t("Available Tags")?></h4>
         </div>
         
         <div class="modal-body">
            <p>
            [sitename]<br/>
            [site_address]<br/>
            [siteurl]<br/>
            [contact_number]<br/>
            [transaction_type]<br/>[transaction_date]<br/>
[order_details]<br/>[line]</p>
         </div>
         
      </div>
   </div>
</div> <!--modal-->


<div id="modal_printer_tags" class="modal fade" role="dialog">
   <div class="modal-dialog ">
      <div class="modal-content">
         <div class="modal-header">
             <h4 class="modal-title"><?php echo t("Printer Tags")?></h4>
         </div>
         
         <div class="modal-body">
          <p>&lt;BR&gt; : Newline character </p>
          <p>&lt;CUT&gt; : Cutter command (active cut, only for cutter printer use)</p>
          <p>&lt;LOGO&gt; : Print LOGO command (precondition is that the LOGO picture is built in the machine in advance) </p>
          <p>&lt;CB&gt; &lt;/ CB&gt; : Center Zoom In </p>
          <p>&lt;B&gt; &lt;/B&gt; : double the size</p>
          <p>&lt;C&gt; &lt;/C&gt; : Center</p>
          <p>&lt;L&gt; &lt;/L&gt; : Double the font</p>
          <p>&lt;W&gt; &lt;/W&gt; : Double the font size</p>
          <p>&lt;QR&gt; &lt;/QR&gt; : QR code</p>
          <p>&lt;RIGHT&gt; &lt;/RIGHT&gt; : Right-justified</p>
         </div> <!--modal-body-->
         
      </div>
   </div>
</div> <!--modal-->

<div class="row">
  <div class="col-md-12">
    
  <div class="form-group">	
	<?php 
	echo CHtml::textArea('printer_receipt_tpl',getOptionA('printer_receipt_tpl'),array(
	 'class'=>"form-control",
	 'style'=>"height:300px;"
	));
	?>
  </div>
  
  </div> <!--col-->  
</div> <!--row-->


 <div class="form-group pad10">  
  <?php
echo CHtml::ajaxSubmitButton(
	PrinterClass::t('Save Settings'),
	array('ajax/savetemplate'),
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