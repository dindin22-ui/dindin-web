<?php echo CHtml::beginForm('','post',array(
 'onsubmit'=>"return false;"
)); 
?> 

<div class="row">
  <div class="col-md-5">
    
<div class="form-group">
    <label><b><?php echo mobileWrapper::t("App Default Language")?></b></label>        
    <?php 
    $lang_list[0]=mobileWrapper::t("Please select");
    $enabled_lang=FunctionsV3::getEnabledLanguage();	    
    if(is_array($enabled_lang) && count($enabled_lang)>=1){
    	foreach ($enabled_lang as $val) {
    		$lang_list[$val]=$val;
    	}
    }
    	    
    echo CHtml::dropDownList('mobileapp2_language',getOptionA('mobileapp2_language'),
    (array)$lang_list,array(
      'class'=>"form-control"
    ));
    ?>    
    <small class="form-text text-muted">
      <?php echo mobileWrapper::t("set application default language")?>
    </small>
</div>  
  
  </div> <!--col-->
  
  <div class="col-md-5">
    
<div class="form-group">
    <label><b><?php echo mobileWrapper::t("Location Accuracy")?></b></label>        
     <?php 
    echo CHtml::dropDownList('mobileapp2_location_accuracy',getOptionA('mobileapp2_location_accuracy'),
    mobileWrapper::locationAccuracyList()
    ,array(
      'class'=>"form-control"
    ));
    ?>     
</div>  
  
  </div> <!--col-->
  
</div> <!--row-->


<div class="height10"></div>
<p><b><?php echo mobileWrapper::t("Header Imaage/Video")?></b></p>
<div class="row">
     <div class="col-md-5">
    
<div class="form-group">
<div class="height10"></div>
<div class="form-group">
<button id="header_image" type="button" class="btn btn-light">
 <?php echo mobileWrapper::t("Browse")?>
</button>     
</div> 
    <div id="headerInner">
    <?php if($header_image != ''):?>
        <!--<a href="<?php //echo $header_image; ?>" target="_blank"><h5>View Header</h5></a>-->
        <a href="<?php echo $header_image; ?>" target="_blank" id = "multi_remove_picture_img" >
        <?php
            $ext = strtolower(end(explode('.', $header_image)));
    	    
    	    if ($ext =='mp4'){?>
    	        <iframe src="<?php echo $header_image ?>" style=" height: 150px; width: 150px;" id = "img_prev" title="Iframe Example"></iframe>;
    	    <?php }else{    ?>
    	        <image src="<?php echo $header_image?>" style=" height: 150px; width: 150px;" id = "img_prev"> </image>         
    	    <?php }?>
        
        </a>
       
      <div class="card preview_uploadpushpicture" style="width: 10rem;">
    	
    	<div class="card-body">
    	  <a href="javascript:;" data-id="uploadpushpicture" 
    	  data-fieldname="android_push_picture" 
    	  class="card-link multi_remove_picture"><?php echo mobileWrapper::t("Remove Header");?></a>
    	</div>
    	
    	<input type="hidden" name="mobileapp2_header_image" value="<?php echo $header_image?>">
     </div>			 
     <div class="height10"></div> 
    
    <?php endif;?>
    </div>
</div>
</div>
</div>

<div class="height10"></div>
<p><b><?php echo mobileWrapper::t("Home Page")?></b></p>
<div class="row">
  
  <div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_home_banner','',"Enabled Banner", getOptionA('mobile2_home_banner') );?>   
  </div> <!--col-->
  
  <div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_home_offer','',"Enabled Offers", getOptionA('mobile2_home_offer') );?>   
  </div> <!--col-->
  
  <div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_home_featured','',"Enabled Feature Restaurant", getOptionA('mobile2_home_featured') );?>   
  </div> <!--col-->
  
  <div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_home_cuisine','',"Enabled Browse By Cuisine", getOptionA('mobile2_home_cuisine') );?>   
  </div> <!--col-->
  
  <div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_home_all_restaurant','',"Enabled All Restaurant", getOptionA('mobile2_home_all_restaurant') );?>   
  </div> <!--col-->
  
  
  <div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_home_favorite_restaurant','',"Enabled Favorites Restaurant", getOptionA('mobile2_home_favorite_restaurant') );?>   
  </div> <!--col-->
  

</div> <!--row-->

<div class="height10"></div>

<p><b><?php echo mobileWrapper::t("Home Page Filter")?></b></p>

<div class="row">
  
  <div class="col-md-4">
  <?php echo htmlWrapper::checkbox('mobile2_show_only_current_location','',"Show Restaurant based on location only", getOptionA('mobile2_show_only_current_location') );?>   
  </div> <!--col-->
</div>  

<div class="height10"></div>
<div class="height10"></div>

<p><b><?php echo mobileWrapper::t("Menu/List Style")?></b></p>

<div class="row">
   
<div class="col-md-5">
<div class="form-group">
    <label><?php echo mobileWrapper::t("Restaurant List Type")?></label>        
     <?php 
    echo CHtml::dropDownList('mobileapp2_merchant_list_type',getOptionA('mobileapp2_merchant_list_type'),
    mobileWrapper::RestaurantListType()
    ,array(
      'class'=>"form-control"
    ));
    ?>     
</div>  
</div> <!--col-->


<div class="col-md-5">
<div class="form-group">
    <label><?php echo mobileWrapper::t("Menu Type")?></label>        
     <?php 
    echo CHtml::dropDownList('mobileapp2_merchant_menu_type',getOptionA('mobileapp2_merchant_menu_type'),
    mobileWrapper::MenuType()
    ,array(
      'class'=>"form-control"
    ));
    ?>     
</div>  
</div> <!--col-->
   
</div>

<div class="height10"></div>

<p style="margin-bottom:0;"><b><?php echo mobileWrapper::t("Search Results Data")?></b></p>
<small class="form-text text-muted">
 <?php echo mobileWrapper::t("choose less data for faster results")?>
</small>
<div class="height20"></div>

<div class="row">
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[1]','',"Open/Close", $search_options,'open_tag' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[2]','',"Reviews", $search_options ,'review' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[3]','',"Cuisine", $search_options, 'cuisine' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[4]','',"Address", $search_options , 'address' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[5]','',"Minimum Order", $search_options , 'minimum_order' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[6]','',"Distance", $search_options , 'distace' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[12]','',"Delivery Est", $search_options , 'delivery_estimation' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[7]','',"Delivery Distance", $search_options , 'delivery_distance' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[8]','',"Delivery Fee", $search_options , 'delivery_fee' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[9]','',"Offers", $search_options , 'offers' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[10]','',"Services", $search_options , 'services' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[11]','',"Payment Options", $search_options , 'payment_option' );?>   
  </div> <!--col-->
  
  <div class="col-md-1">
  <?php echo htmlWrapper::checkbox('mobile2_search_data[13]','',"Vouchers", $search_options , 'voucher' );?>   
  </div> <!--col-->
  
</div> <!--row-->



<div class="height20"></div>

<p><b><?php echo mobileWrapper::t("Menu")?></b></p>

<div class="row">

<div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_disabled_default_image','',"Disabled default menu image", getOptionA('mobile2_disabled_default_image') );?>   
</div> <!--col-->

<div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_enabled_menu_carousel','',"Enabled Carousel", getOptionA('mobile2_enabled_menu_carousel') );?>   
</div> <!--col-->

<div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_enabled_dish','',"Enabled Dishes", getOptionA('mobile2_enabled_dish') );?>   
</div> <!--col-->

<div class="col-md-2">
  <?php echo htmlWrapper::checkbox('mobile2_disabled_image_menu1','',"Disabled image Menu 1", getOptionA('mobile2_disabled_image_menu1') );?>   
</div> <!--col-->

</div> <!--row-->

<div class="height20"></div>

<p><b><?php echo mobileWrapper::t("Distance Results")?></b></p>

<div class="row">

	<div class="col-md-3">
	   <div class="radio">
		  <label>
		  <?php 
		  echo CHtml::radioButton('mobileapp2_distance_results',
		  getOptionA('mobileapp2_distance_results')==1?true:false
		  ,array(
		    'id'=>'mobileapp2_distance_results',		    
		    'value'=>1
		  ));
		  ?>
		  <?php echo mobileWrapper::t("Using Straight line")?>
		  </label>
		</div>
		<p class="text-muted"><?php echo mt("This options does not use any api like google and mapbox for faster results")?></p>
	</div> <!--col-->
	
	<div class="col-md-3">
	   <div class="radio">
		  <label>
		  <?php 
		  echo CHtml::radioButton('mobileapp2_distance_results',
		  getOptionA('mobileapp2_distance_results')==2?true:false
		  ,array(
		    'id'=>'mobileapp2_distance_results',		    
		    'value'=>2
		  ));
		  ?>
		  <?php echo mobileWrapper::t("Using Map Provider")?>
		  </label>
		</div>
		<p class="text-muted"><?php echo mt("This options will use api for google and mapbox")?></p>
	</div> <!--col-->

</div> <!--row-->


<p><b><?php echo mobileWrapper::t("Customer Order History")?></b></p>


<div class="row">
	<div class="col-md-3">
     <p><?php echo mt("Processing")?></p>
	 <?php 
	 unset($order_status_list[0]);	 
	 echo CHtml::dropDownList('mobileapp2_order_processing',(array)json_decode(getOptionA('mobileapp2_order_processing')),
    (array)$order_status_list,array(
      'class'=>"form-control chosen",
      "multiple"=>"multiple"
    ));
	 ?>	
	</div> <!--col-->
	
	<div class="col-md-3">
	  <p><?php echo mt("Completed")?></p>
	 <?php 
	 echo CHtml::dropDownList('mobileapp2_order_completed',(array)json_decode(getOptionA('mobileapp2_order_completed')),
    (array)$order_status_list,array(
      'class'=>"form-control chosen",
      "multiple"=>"multiple"
    ));
	 ?>	
	</div> <!--col-->
	
	<div class="col-md-3">
	  <p><?php echo mt("Cancelled")?></p>
	 <?php 
	 echo CHtml::dropDownList('mobileapp2_order_cancelled',(array)json_decode(getOptionA('mobileapp2_order_cancelled')),
    (array)$order_status_list,array(
      'class'=>"form-control chosen",
      "multiple"=>"multiple"
    ));
	 ?>	
	</div> <!--col-->
	
</div>	

<div class="height20"></div>

<p><b><?php echo mobileWrapper::t("Registration Settings")?></b></p>
<div class="row">

    <div class="col-md-2">
    <?php     
    echo CHtml::dropDownList('mobileapp2_prefix',getOptionA('mobileapp2_prefix'),
    (array)mobileWrapper::mobileCodeList(),array(
      'class'=>"form-control"
    ));
    ?>
    <small class="form-text text-muted">
      <?php echo mobileWrapper::t("Default Phone Prefix")?>
    </small>
    </div>
    
	<div class="col-md-2">
	<?php echo htmlWrapper::checkbox('mobileapp2_turnoff_prefix','',"Turn off mobile prefix", getOptionA('mobileapp2_turnoff_prefix') );?>   	
	</div>
	
	<div class="col-md-2">
	<?php echo htmlWrapper::checkbox('mobileapp2_reg_email','',"Customer Register via Email", getOptionA('mobileapp2_reg_email') );?>   	
	</div>
	
	<div class="col-md-2">
	<?php echo htmlWrapper::checkbox('mobileapp2_reg_phone','',"Customer Register via Phone", getOptionA('mobileapp2_reg_phone') );?>   	
	</div>
		
	
</div> <!--row-->

<div class="height20"></div>

<p><b><?php echo mobileWrapper::t("Tracking Settings")?></b></p>

<div class="row">

<div class="col-md-2">
<?php     
echo CHtml::dropDownList('mobileapp2_tracking_theme',getOptionA('mobileapp2_tracking_theme'),
(array)mobileWrapper::trackingTheme(),array(
  'class'=>"form-control"
));
?>
<small class="form-text text-muted">
  <?php echo mobileWrapper::t("Tracking Theme")?>
</small>
</div>

<div class="col-md-2">
<?php echo CHtml::textField('mobileapp2_tracking_interval', getOptionA('mobileapp2_tracking_interval'),
array('class'=>"numeric_only form-control","placeholder"=>mt("Track Interval") ));?>
<small class="form-text text-muted">
  <?php echo mobileWrapper::t("In Millisecond default is 7000, Minimum is 5000")?>
</small>
</div>

    
</div> <!--row-->

<div class="height20"></div>

<p><b><?php echo mobileWrapper::t("Cart Settings")?></b></p>

<div class="row">

<div class="col-md-2">
<?php     
echo CHtml::dropDownList('mobileapp2_cart_theme',getOptionA('mobileapp2_cart_theme'),
(array)mobileWrapper::cartTheme(),array(
  'class'=>"form-control"
));
?>
</div>

<div class="col-md-2">
	<?php echo htmlWrapper::checkbox('mobileapp2_cart_auto_address','',"Auto Set Address", getOptionA('mobileapp2_cart_auto_address') );?>   	
</div>

</div><!-- row-->

<div class="height20"></div>
<div class="height10"></div>
  
  <?php
echo CHtml::ajaxSubmitButton(
	mobileWrapper::t('SAVE SETTINGS'),
	array('ajax/savesettings_app'),
	array(
		'type'=>'POST',
		'dataType'=>'json',
		'beforeSend'=>'js:function(){
		   loader(1);                 
		}
		',
		'complete'=>'js:function(){		                 
		   loader(2);
		 }',
		'success'=>'js:function(data){	
		   if(data.code==1){
		     notify(data.msg);
		   } else {
		     notify(data.msg,"danger");
		   }
		}
		'
	),array(
	  'class'=>'btn '.APP_BTN,
	  'id'=>'save_application'
	)
);
?>

<?php echo CHtml::endForm(); ?>