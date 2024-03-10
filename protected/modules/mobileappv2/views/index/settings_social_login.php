<?php echo CHtml::beginForm('','post',array(
 'onsubmit'=>"return false;"
)); 
?> 

<div class="custom-control custom-checkbox">  
  <?php 
  echo CHtml::checkBox('mobile2_enabled_fblogin',
  getOptionA('mobile2_enabled_fblogin')==1?true:false
  ,array(
    'id'=>'mobile2_enabled_fblogin',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="mobile2_enabled_fblogin">
    <?php echo mobileWrapper::t("Enabled Facebook Login")?>
  </label>
</div>

<div class="height10"></div>



<div class="custom-control custom-checkbox">  
  <?php 
  echo CHtml::checkBox('mobile2_enabled_googlogin',
  getOptionA('mobile2_enabled_googlogin')==1?true:false
  ,array(
    'id'=>'mobile2_enabled_googlogin',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="mobile2_enabled_googlogin">
    <?php echo mobileWrapper::t("Enabled Google Login")?>
  </label>
</div>

<div class="height10"></div>


<hr/>
<h2 style= "color: #444; font-weight: 400; font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;"><?php echo Yii::t('default',"Facebook")?></h2>
<div class="height10"></div>


   <!--here added Facebook app id-->

<div class="form-group">
    <label><?php echo mobileWrapper::t("Facebook App ID")?></label>
        
    <?php 
    echo CHtml::textField('mobileapp2_fb_id_both',getOptionA('mobileapp2_fb_id_both'),array(
     'class'=>"form-control",
     'style'=>"width:50% !important;",
     'required'=>true,     
    ));
    ?>        
  </div> 
  <!--end-->
  <div class="height10"></div>
  
  <!--here added app secret for facebook-->
  
  <div class="form-group">
    <label><?php echo mobileWrapper::t("Facebook App Secret")?></label>
        
    <?php 
    echo CHtml::textField('mobileapp2_fb_app_secret_both',getOptionA('mobileapp2_fb_app_secret_both'),array(
     'class'=>"form-control",
     'style'=>"width:50% !important;",
     'required'=>true,     
    ));
    ?>        
  </div> 
  
  <!--end-->
  <div class="height10"></div>

<h2 style= "color: #444; font-weight: 400; font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;"><?php echo Yii::t('default',"Google")?></h2>

    <div class="height10"></div>
  
  
  <!--here adding google app id for ios-->
    <div class="form-group">
    <label><?php echo mobileWrapper::t("Google Client ID iOS")?></label>
        
    <?php 
    echo CHtml::textField('mobileapp2_Google_client_id_ios',getOptionA('mobileapp2_Google_client_id_ios'),array(
     'class'=>"form-control",
     'style'=>"width:50% !important;",
     'required'=>true,     
    ));
    ?>        
  </div>
  
  
  <!--end-->
  
   <!--here adding google app Secret for ios-->
   
    <div class="form-group">
    <label><?php echo mobileWrapper::t("Google Client Secret iOS")?></label>
        
    <?php 
    echo CHtml::textField('mobileapp2_Google_client_secret_ios',getOptionA('mobileapp2_Google_client_secret_ios'),array(
     'class'=>"form-control",
     'style'=>"width:50% !important;",
     'required'=>true,     
    ));
    ?>        
  </div>
   
   
   <!--end-->
  
    <div class="height10"></div>
  
  <!--<hr/>-->
  
  
<!--here adding google app id for Android-->

  <div class="form-group">
    <label><?php echo mobileWrapper::t("Google Client ID Android")?></label>
        
    <?php 
    echo CHtml::textField('mobileapp2_Google_client_id_android',getOptionA('mobileapp2_Google_client_id_android'),array(
     'class'=>"form-control",
     'style'=>"width:50% !important;",
     'required'=>true,     
    ));
    ?>        
  </div>

  <!--end-->
  <div class="height10"></div>
  
  <!--here adding google app secret for Android-->

  <div class="form-group">
    <label><?php echo mobileWrapper::t("Google Client Secret Android")?></label>
        
    <?php 
    echo CHtml::textField('mobileapp2_Google_client_secret_android',getOptionA('mobileapp2_Google_client_secret_android'),array(
     'class'=>"form-control",
     'style'=>"width:50% !important;",
     'required'=>true,     
    ));
    ?>        
  </div>
  
   <div class="height10"></div>

  <!--end-->




<?php
echo CHtml::ajaxSubmitButton(
	mobileWrapper::t('Save Settings'),
	array('ajax/savesettings_social'),
	array(
		'type'=>'POST',
		'dataType'=>'json',
		'beforeSend'=>'js:function(){
		   loader(1);                 
		}',
		'complete'=>'js:function(){		                 
		   loader(2);
		}',
		'success'=>'js:function(data){	
		   if(data.code==1){
		     notify(data.msg);
		   } else {
		     notify(data.msg,"danger");
		   }
		}',
		'error'=>'js:function(data){
		   notify(error_ajax_message,"danger");
		}',
	),array(
	  'class'=>'btn '.APP_BTN,
	  'id'=>'save_social'
	)
);
?>

<?php echo CHtml::endForm(); ?>