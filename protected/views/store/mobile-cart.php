<?php 

$this->renderPartial('/front/mobile_header',array(
    'slug'=> isset($data['restaurant_slug'])?$data['restaurant_slug']:'',
    'title'=>t("Cart")
));

$min_fees=FunctionsV3::getMinOrderByTableRates($merchant_id,
   $distance,
   $distance_type_raw,
   $data['minimum_order']
);

echo CHtml::hiddenField('merchant_id',$merchant_id);
echo CHtml::hiddenField('currentController','store');

$now=date('Y-m-d');
$now_time='';
$checkout=FunctionsV3::isMerchantcanCheckout($merchant_id); 


echo CHtml::hiddenField('is_merchant_open',isset($checkout['code'])?$checkout['code']:'' );

/*hidden TEXT*/
echo CHtml::hiddenField('restaurant_slug',$data['restaurant_slug']);
echo CHtml::hiddenField('merchant_id',$merchant_id);
echo CHtml::hiddenField('is_client_login',Yii::app()->functions->isClientLogin());

echo CHtml::hiddenField('website_disbaled_auto_cart',
Yii::app()->functions->getOptionAdmin('website_disbaled_auto_cart'));

$hide_foodprice=Yii::app()->functions->getOptionAdmin('website_hide_foodprice');
echo CHtml::hiddenField('hide_foodprice',$hide_foodprice);

echo CHtml::hiddenField('accept_booking_sameday',getOption($merchant_id
,'accept_booking_sameday'));

echo CHtml::hiddenField('customer_ask_address',getOptionA('customer_ask_address'));

echo CHtml::hiddenField('merchant_required_delivery_time',
  Yii::app()->functions->getOption("merchant_required_delivery_time",$merchant_id));   

/** add minimum order for pickup status*/
$merchant_minimum_order_pickup=Yii::app()->functions->getOption('merchant_minimum_order_pickup',$merchant_id);
if (!empty($merchant_minimum_order_pickup)){
	  echo CHtml::hiddenField('merchant_minimum_order_pickup',$merchant_minimum_order_pickup);
	  
	  echo CHtml::hiddenField('merchant_minimum_order_pickup_pretty',
         displayPrice(baseCurrency(),prettyFormat($merchant_minimum_order_pickup)));
}
 
$merchant_maximum_order_pickup=Yii::app()->functions->getOption('merchant_maximum_order_pickup',$merchant_id);
if (!empty($merchant_maximum_order_pickup)){
	  echo CHtml::hiddenField('merchant_maximum_order_pickup',$merchant_maximum_order_pickup);
	  
	  echo CHtml::hiddenField('merchant_maximum_order_pickup_pretty',
         displayPrice(baseCurrency(),prettyFormat($merchant_maximum_order_pickup)));
}  

/*add minimum and max for delivery*/
//$minimum_order=Yii::app()->functions->getOption('merchant_minimum_order',$merchant_id);
$minimum_order=$min_fees;
if (!empty($minimum_order)){
	echo CHtml::hiddenField('minimum_order',unPrettyPrice($minimum_order));
	echo CHtml::hiddenField('minimum_order_pretty',
	 displayPrice(baseCurrency(),prettyFormat($minimum_order))
	);
}
$merchant_maximum_order=Yii::app()->functions->getOption("merchant_maximum_order",$merchant_id);
 if (is_numeric($merchant_maximum_order)){
 	echo CHtml::hiddenField('merchant_maximum_order',unPrettyPrice($merchant_maximum_order));
    echo CHtml::hiddenField('merchant_maximum_order_pretty',baseCurrency().prettyFormat($merchant_maximum_order));
 }

$is_ok_delivered=1;
if (is_numeric($merchant_delivery_distance)){
	if ( $distance>$merchant_delivery_distance){
		$is_ok_delivered=2;
		/*check if distance type is feet and meters*/
		//if($distance_type=="ft" || $distance_type=="mm" || $distance_type=="mt"){
		if($distance_type=="ft" || $distance_type=="mm" || $distance_type=="mt" || $distance_type=="meter"){
			$is_ok_delivered=1;
		}
	}
} 

echo CHtml::hiddenField('is_ok_delivered',$is_ok_delivered);
echo CHtml::hiddenField('merchant_delivery_miles',$merchant_delivery_distance);
echo CHtml::hiddenField('unit_distance',$distance_type);
echo CHtml::hiddenField('from_address', FunctionsV3::getSessionAddress() );

echo CHtml::hiddenField('merchant_close_store',getOption($merchant_id,'merchant_close_store'));

echo CHtml::hiddenField('merchant_close_msg',
isset($checkout['msg'])?$checkout['msg']:t("Sorry merchant is closed."));

echo CHtml::hiddenField('disabled_website_ordering',getOptionA('disabled_website_ordering'));
echo CHtml::hiddenField('web_session_id',session_id());

echo CHtml::hiddenField('merchant_map_latitude',$data['latitude']);
echo CHtml::hiddenField('merchant_map_longtitude',$data['lontitude']);
echo CHtml::hiddenField('restaurant_name',$data['restaurant_name']);


echo CHtml::hiddenField('current_page','menu');

/*add meta tag for image*/
Yii::app()->clientScript->registerMetaTag(
Yii::app()->getBaseUrl(true).FunctionsV3::getMerchantLogo($merchant_id)
,'og:image');

$remove_delivery_info=false;
if($data['service']==3 || $data['service']==6 || $data['service']==7 ){	
	$remove_delivery_info=true;
}

$s=$_SESSION;
$continue=false;

$merchant_address='';
if ($merchant_info=Yii::app()->functions->getMerchant($s['kr_merchant_id'])){
    $merchant_address=$merchant_info['street']." ".$merchant_info['city']." ".$merchant_info['state'];
    $merchant_address.=" "	. $merchant_info['post_code'];
}

$client_info='';

if ($is_guest_checkout){
    $continue=true;
} else {
    $client_info = Yii::app()->functions->getClientInfo(Yii::app()->functions->getClientId());
    if (isset($s['kr_search_address'])){
        $temp=explode(",",$s['kr_search_address']);
        if (is_array($temp) && count($temp)>=2){
            $street=isset($temp[0])?$temp[0]:'';
            $city=isset($temp[1])?$temp[1]:'';
            $state=isset($temp[2])?$temp[2]:'';
        }
        if ( isset($client_info['street'])){
            if ( empty($client_info['street']) ){
                $client_info['street']=$street;
            }
        }
        if ( isset($client_info['city'])){
            if ( empty($client_info['city']) ){
                $client_info['city']=$city;
            }
        }
        if ( isset($client_info['state'])){
            if ( empty($client_info['state']) ){
                $client_info['state']=$state;
            }
        }
    }

    if (isset($s['kr_merchant_id']) && Yii::app()->functions->isClientLogin() && is_array($merchant_info) ){
        $continue=true;
    }
}
?>
<div class="container">

<div style="padding:10px;padding-bottom:30px;">
  <p class="bold center"><?php echo t("Your Order")?></p>

        <!--DELIVERY OPTIONS-->
        <div class="inner line-top relative delivery-option center" style="padding-top:15px;">
           <!--<i class="order-icon delivery-option-icon"></i>-->

           <?php if ($remove_delivery_info==false):?>
             <p class="bold"><?php echo t("Delivery Options")?></p>
           <?php else :?>
             <p class="bold"><?php echo t("Options")?></p>
           <?php endif;?>

           <?php  // echo CHtml::dropDownList('delivery_type',$now, (array)Yii::app()->functions->DeliveryOptions($merchant_id),array('class'=>'grey-fields')); ?>
            <?php
            $delievery_type = (array)Yii::app()->functions->DeliveryOptions($merchant_id);
            $d_type = isset($_SESSION['kr_delivery_options']['delivery_type']) ? $_SESSION['kr_delivery_options']['delivery_type']:'';
            if(count($delievery_type) > 1) {

                $width = ceil(100/count($delievery_type));
?>
                <div class="delivery_type">
                    <ul>
            <?php
                $i = 0;
                foreach ($delievery_type as $key=>$value) {
                    if($key == 'pickup' && $d_type == ''){
                        $d_type = $key;
                    }
                    ?> <li style="width: <?php echo $width; ?>%"><a   class="<?php if($key == $d_type){ echo 'active'; }else{ echo ''; }?>" href="javascript:void(0)" data-value="<?php echo $key; ?>" ><?php echo $value; ?></a> </li>
                    <?php
                    $i++;
                }
                ?>
                    </ul>
                    <div style="clear: both"></div>
                </div>
                <input type="hidden" name="delivery_type" id="delivery_type" value="<?php echo $d_type; ?>" />
            <?php
            }else{
            foreach ($delievery_type as $key=>$value) {
                $d_type = $key;
                ?>
                <input type="hidden" name="delivery_type" id="delivery_type" value="<?php echo $key;?>" />
           <?php }
            }
            ?>

            <div style="clear: both"></div>

            <!-- DELIVERY-->
            <div class="sections section-payment-option address address-cart" style="transform: none;<?php if($d_type != 'delivery') echo 'display: none;'; ?>">
                <div class="container" style="transform: none;">
                    <input type="hidden" value="<?php echo $merchant_info['merchant_id']; ?>" name="merchant_id" id="merchant_id">
                    <div class="col-md-12">
                        <?php FunctionsV3::sectionHeader('Delivery information')?>
                        <p>
                            <?php echo clearString(ucwords($merchant_info['restaurant_name']))?>
                            <?php echo Yii::t("default","Restaurant")?>
                            <?php // echo "<span class='bold'>".Yii::t("default",ucwords($s['kr_delivery_options']['delivery_type'])) . "</span> ";
//                            if ($s['kr_delivery_options']['delivery_asap']==1){
//                                $s['kr_delivery_options']['delivery_date']." ".Yii::t("default","ASAP");
//                            } else {
//                                echo '<span class="bold">'.Yii::app()->functions->translateDate(date("M d Y",strtotime($s['kr_delivery_options']['delivery_date']))).
//                                    " ".t("at"). " ". FunctionsV3::prettyTime($s['kr_delivery_options']['delivery_time'])."</span> ".t("to");
//                            }
                            ?>
                        </p>

                        <div class="top10">

                            <?php FunctionsV3::sectionHeader('Address')?>

                            <?php if (isset($is_guest_checkout)):?>
                                <div class="row top10">
                                    <div class="col-md-10">
                                        <?php echo CHtml::textField('first_name','',array(
                                            'class'=>'grey-fields full-width',
                                            'placeholder'=>Yii::t("default","First Name"),
                                            'data-validation'=>"required"
                                        ))?>
                                    </div>
                                </div>

                                <div class="row top10">
                                    <div class="col-md-10">
                                        <?php echo CHtml::textField('last_name','',array(
                                            'class'=>'grey-fields full-width',
                                            'placeholder'=>Yii::t("default","Last Name"),
                                            'data-validation'=>"required"
                                        ))?>
                                    </div>
                                </div>
                            <?php endif;?>
                            <!--$is_guest_checkout-->

                            <?php if (!$search_by_location):?>
                                <?php if ( $website_enabled_map_address==2 && $enabled_map_selection_delivery!=1 ):?>
                                    <div class="top10">
                                        <?php Widgets::AddressByMap()?>
                                    </div>
                                <?php endif;?>

                                <?php $address_list=Yii::app()->functions->addressBook(Yii::app()->functions->getClientId());?>
                                <?php if(is_array($address_list) && count($address_list)>=1):?>
                                    <div class="address_book_wrap">
                                        <div class="row top10">
                                            <div class="col-md-10">
                                                <?php
                                                echo CHtml::dropDownList('address_book_id',$address_book['id'],
                                                    (array)$address_list,array(
                                                        'class'=>"grey-fields full-width"
                                                    ));
                                                ?>
                                                <a href="javascript:;" class="edit_address_book block top10">
                                                    <i class="ion-compose"></i> <?php echo t("Edit")?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--address_book_wrap-->
                                <?php endif;?>
                            <?php else :?>

                                <?php
                                if($has_addressbook){
                                    $location_addressbook = FunctionsV3::getAddressBookList($client_id);
                                    $default_location_address = FunctionsV3::getDefaultAddressByLocation($client_id);
                                    ?>
                                    <div class="row top10 address_book_wrap">
                                        <div class="col-md-10">
                                            <?php
                                            echo CHtml::dropDownList('address_book_id_location',
                                                isset($default_location_address['id'])?$default_location_address['id']:''
                                                ,
                                                (array)$location_addressbook,array(
                                                    'class'=>'grey-fields full-width',
                                                    'data-validation'=>"required"
                                                ));
                                            ?>

                                            <a href="javascript:;" class="edit_address_book block top10">
                                                <i class="ion-compose"></i> <?php echo t("Edit")?>
                                            </a>

                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>

                            <?php endif;?>


                            <div class="address-block">

                                <div class="row top10">
                                    <div class="col-md-10">
                                        <?php echo CHtml::textField('street', isset($client_info['street'])?$client_info['street']:'' ,array(
                                            'class'=>'grey-fields full-width',
                                            'placeholder'=>Yii::t("default","Street"),
                                            'data-validation'=>"required"
                                        ))?>
                                    </div>
                                </div>

                                <?php if (!$search_by_location):?>

                                    <div class="row top10">
                                        <div class="col-md-10">
                                            <?php echo CHtml::textField('city',
                                                isset($client_info['city'])?$client_info['city']:''
                                                ,array(
                                                    'class'=>'grey-fields full-width',
                                                    'placeholder'=>Yii::t("default","City"),
                                                    'data-validation'=>"required"
                                                ))?>
                                        </div>
                                    </div>

                                    <div class="row top10">
                                        <div class="col-md-10">
                                            <?php echo CHtml::textField('state',
                                                isset($client_info['state'])?$client_info['state']:''
                                                ,array(
                                                    'class'=>'grey-fields full-width',
                                                    'placeholder'=>Yii::t("default","State"),
                                                    'data-validation'=>"required"
                                                ))?>
                                        </div>
                                    </div>

                                    <div class="row top10">
                                        <div class="col-md-10">
                                            <?php echo CHtml::textField('zipcode',
                                                isset($client_info['zipcode'])?$client_info['zipcode']:''
                                                ,array(
                                                    'class'=>'grey-fields full-width',
                                                    'placeholder'=>Yii::t("default","Zip code")
                                                ))?>
                                        </div>
                                    </div>

                                <?php else :?>
                                    <!--ADDRESS BY LOCATION -->
                                    <?php


                                    $country_id=getOptionA('location_default_country'); $state_ids='';
                                    $location_search_data=FunctionsV3::getSearchByLocationData();

                                    echo CHtml::hiddenField('is_search_by_location',1);
                                    echo CHtml::hiddenField('state');
                                    echo CHtml::hiddenField('city');
                                    echo CHtml::hiddenField('area_name');

                                    $states = FunctionsV3::ListLocationState($country_id);
                                    $citys = array(); $areas= array();

                                    if(is_array($location_search_data) && count($location_search_data)>=1){
                                        $citys=FunctionsV3::ListCityList( isset($location_search_data['state_id'])?$location_search_data['state_id']:'' );
                                        $areas=FunctionsV3::AreaList( isset($location_search_data['city_id'])?$location_search_data['city_id']:'' );
                                    } else {
                                        $citys=array(
                                            ''=>t("Select City")
                                        );
                                        $areas=array(
                                            ''=>t("Select Distric/Area/neighborhood")
                                        );
                                    }
                                    ?>
                                    <div class="row top10">
                                        <div class="col-md-10">
                                            <?php
                                            echo CHtml::dropDownList('state_id',
                                                isset($location_search_data['state_id'])?$location_search_data['state_id']:'',
                                                (array)$states
                                                ,array(
                                                    'class'=>'grey-fields full-width',
                                                    'data-validation'=>"required"
                                                ));
                                            ?>
                                        </div>
                                    </div>

                                    <div class="row top10">
                                        <div class="col-md-10">
                                            <?php
                                            echo CHtml::dropDownList('city_id',
                                                isset($location_search_data['city_id'])?$location_search_data['city_id']:'',
                                                (array)$citys
                                                ,array(
                                                    'class'=>'grey-fields full-width',
                                                    'data-validation'=>"required"
                                                ));
                                            ?>
                                        </div>
                                    </div>

                                    <div class="row top10">
                                        <div class="col-md-10">
                                            <?php
                                            echo CHtml::dropDownList('area_id',
                                                isset($location_search_data['area_id'])?$location_search_data['area_id']:'',
                                                (array)$areas
                                                ,array(
                                                    'class'=>'grey-fields full-width',
                                                    'data-validation'=>"required"
                                                ));
                                            ?>
                                        </div>
                                    </div>

                                    <div class="row top10">
                                        <div class="col-md-10">
                                            <?php echo CHtml::textField('zipcode',
                                                isset($client_info['zipcode'])?$client_info['zipcode']:''
                                                ,array(
                                                    'class'=>'grey-fields full-width',
                                                    'placeholder'=>Yii::t("default","Zip code")
                                                ))?>
                                        </div>
                                    </div>


                                <?php endif; // end search by location?>


                                <div class="row top10">
                                    <div class="col-md-10">
                                        <?php echo CHtml::textField('location_name',
                                            isset($client_info['location_name'])?$client_info['location_name']:''
                                            ,array(
                                                'class'=>'grey-fields full-width',
                                                'placeholder'=>Yii::t("default","Apartment suite, unit number, or company name")
                                            ))?>
                                    </div>
                                </div>

                            </div>
                            <!--address-block-->

                            <div class="row top10">
                                <div class="col-md-10">
                                    <?php echo CHtml::textField('contact_phone',
                                        isset($client_info['contact_phone'])?$client_info['contact_phone']:''
                                        ,array(
                                            'class'=>'grey-fields mobile_inputs full-width',
                                            'placeholder'=>Yii::t("default","Mobile Number"),
                                            'data-validation'=>"required",
                                            'maxlength'=>15
                                        ))?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-10">
                                    <?php echo CHtml::textField('delivery_instruction','',array(
                                        'class'=>'grey-fields full-width',
                                        'placeholder'=>Yii::t("default","Delivery instructions")
                                    ))?>
                                </div>
                            </div>

                            <div class="row top10 saved_address_block" style="display: none">
                                <div class="col-md-10">
                                    <?php
                                    echo CHtml::checkBox('saved_address',false,array('class'=>"icheck",'value'=>2));
                                    echo " ".t("Save to my address book");
                                    ?>
                                </div>
                            </div>

                            <?php if (isset($is_guest_checkout)):?>
                                <div class="row top10">
                                    <div class="col-md-10">
                                        <?php echo CHtml::textField('email_address','',array(
                                            'class'=>'grey-fields full-width',
                                            'placeholder'=>Yii::t("default","Email address"),
                                        ))?>
                                    </div>
                                </div>

                            <?php endif;?>


                            <?php if (isset($is_guest_checkout)):?>
                                <?php // FunctionsV3::sectionHeader('Optional')?>
                                <div class="row top10" style="display: none; ">
                                    <div class="col-md-10">
                                        <?php echo CHtml::passwordField('password','',array(
                                            'class'=>'grey-fields full-width',
                                            'placeholder'=>Yii::t("default","Password"),
                                        ))?>
                                    </div>
                                </div>

                            <?php endif;?>

                        </div> <!--top10-->
                    </div>
                </div>
            </div>
            <!-- ENDIF DELIVERY-->
            <?php
           if($website_use_date_picker==2){
           	  echo CHtml::dropDownList('delivery_date','',
            	(array)FunctionsV3::getDateList($merchant_id)
            	,array(
            	  'class'=>'grey-fields date_list'
            	));
           } else {
	           echo CHtml::hiddenField('delivery_date',$now);
	           echo CHtml::textField('delivery_date1',
	            FormatDateTime($now,false),array('class'=>"j_date grey-fields",'data-id'=>'delivery_date'));
           }
            ?>

            <div style="clear: both"></div>
           <div class="delivery_asap_wrap delivery_time" style="display: none;">
             <?php
             $options_al = array();
             $options_al['ASAP'] = 'ASAP';
             echo CHtml::dropDownList('delivery_time',$now_time,
             (array)$options_al
             ,array(
              'class'=>"grey-fields"
             ))
             ?>
	          <?php // if ( $checkout['is_pre_order']==2):?>
	          <!-- <span class="delivery-asap" style="display:none;">
	           <?php // echo CHtml::checkBox('delivery_asap',false,array('class'=>"icheck"))?>
	            <span class="text-muted"><?php // echo Yii::t("default","Delivery ASAP?")?></span>
	         </span>       	         	        	      -->
	         <?php // endif;?>

            </div><!-- delivery_asap_wrap-->

            <div class="delivery_asap_wrap pickup_time" style="display: block;">
             <?php
             echo CHtml::dropDownList('pickup_time',$now_time,
             (array)FunctionsV3::timeList($merchant_id)
             ,array(
              'class'=>"grey-fields"
             ))
             ?>
	          <?php // if ( $checkout['is_pre_order']==2):?>
	          <!-- <span class="delivery-asap" style="display:none;">
	           <?php // echo CHtml::checkBox('delivery_asap',false,array('class'=>"icheck"))?>
	            <span class="text-muted"><?php // echo Yii::t("default","Delivery ASAP?")?></span>
	         </span>       	         	        	      -->
	         <?php // endif;?>

           </div><!-- delivery_asap_wrap-->

            <div class="item-order-wrap"></div>

           <?php if ( $checkout['code']==1):?>
              <a style="display: block" href="javascript:;" class="orange-button medium checkout">Continue<?php // echo $checkout['button']?></a>
           <?php else :?>
              <?php if ( $checkout['holiday']==1):?>
                 <?php echo CHtml::hiddenField('is_holiday',$checkout['msg'],array('class'=>'is_holiday'));?>
                 <p class="text-danger"><?php echo $checkout['msg']?></p>
              <?php else :?>
                 <p class="text-danger"><?php echo $checkout['msg']?></p>
                 <p class="small">
                 <?php echo Yii::app()->functions->translateDate(date('F d l')."@".timeFormat(date('c'),true));?></p>
              <?php endif;?>
           <?php endif;?>

        </div> <!--inner-->
        <!--END DELIVERY OPTIONS-->
</div> <!--padding-->

</div> <!--mobile-cart-->

<style>
.overlay {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.7);
  transition: opacity 500ms;
  visibility: visible;
  opacity: 1;
  z-index:99;
}
.overlay:target {
  visibility: visible;
  opacity: 1;
}

.popup {
  margin: 70px auto;
  padding: 20px;
  background: #fff;
  border-radius: 5px;
  width: 30%;
  position: relative;
  transition: all 5s ease-in-out;
}

.popup h2 {
  margin-top: 0;
  color: #333;
  font-size: 20px;
}
.popup .close {
  position: absolute;
  top: 20px;
  right: 30px;
  transition: all 200ms;
  font-size: 30px;
  font-weight: bold;
  text-decoration: none;
  color: #333;
}
.popup .close:hover {
  color: #000;
}
.popup .content {
  max-height: 400px;
  overflow: auto;
  text-align:center;
}
.popup .content p{
	color:#444;
}
.close-message{
    display: block;width: 100px;margin: auto;border-radius: 4px;padding: 10px 30px !important;
}
@media screen and (max-width: 700px){
  .box{
    width: 70%;
  }
  .popup{
    width: 60%;
	top:45px;
  }
}
</style>
<div id="messages_popup" class="overlay" style="display:none;">
			<div class="popup">
				<!--<a onclick="closePopup()" class="close" href="#">&times;</a>-->
        		<div style="clear: both;height: 20px"></div>
				<div class="content">
					<p>You are outside of our delivery radius, please select pick up instead</p>
					 
				    <a onclick="closePopup()" style="display: block" href="javascript:;" class="orange-button medium close-message">Close</a>
				</div>
			</div>
		</div>
		<script>
			function closePopup() {
				var x = document.getElementById("messages_popup");
				if (x.style.display === "none") {
					x.style.display = "block";
				} else {
					x.style.display = "none";
				}
			} 
		</script>
