<?php
$min_fees=FunctionsV3::getMinOrderByTableRates($merchant_id,
   $distance,
   $distance_type_orig,
   $val['minimum_order']
);

$show_delivery_info=false;
if($val['service']==1 || $val['service']==2  || $val['service']==4  || $val['service']==5 ){
	$show_delivery_info=true;
}

?>
<div id="search-listgrid" class="infinite-item <?php echo $delivery_fee!=true?'free-wrap':'non-free'; ?>">
    <div class="inner list-view">
    
    <?php if ( $val['is_sponsored']==2):?>
    <div class="ribbon"><span><?php echo t("Sponsored")?></span></div>
    <?php endif;?>
    
    <?php if ($offer=FunctionsV3::getOffersByMerchant($merchant_id)):?>
    <div class="ribbon-offer"><span><?php echo $offer;?></span></div>
    <?php endif;?>
    
    <div class="row">
	    <div class="col-md-2 border ">
	     <!--<a href="<?php echo Yii::app()->createUrl('store/menu/merchant/'.$val['restaurant_slug'])?>">-->
	     <a href="<?php echo Yii::app()->createUrl("/menu-". trim($val['restaurant_slug']))?>">
	      <img class="logo-small"src="<?php echo FunctionsV3::getMerchantLogo($merchant_id);?>">
	     </a>	     
	      
	    </div> <!--col-->
	    
	    <div class="col-md-7 border">
	     
	       <div class="mytable">
	         <!--<div class="mycol">
	            <div class="rating-stars" data-score="<?php echo $ratings['ratings']?>"></div>   
	         </div>-->
	         <!--<div class="mycol">
	            <p><?php echo $ratings['votes']." ".t("Reviews")?></p>
	         </div>-->
	         
	         <div class="mycol">
	            <?php echo FunctionsV3::merchantOpenTag($merchant_id)?>                
	         </div>
	         <br>
	        <!-- <div class="mycol">
	            <a href="javascript:;" data-id="<?php echo $val['merchant_id']?>"  title="<?php echo t("add to your favorite restaurant")?>" class="add_favorites <?php echo "fav_".$val['merchant_id']?>"><i class="ion-android-favorite-outline"></i></a>
	         </div>-->
	         
	       </div> <!--mytable-->
	       
	       <h2><?php echo clearString($val['restaurant_name'])?></h2>
	       <p class="merchant-address concat-text"><?php echo $val['merchant_address']?></p>   
	       	       	       
	       <p class="cuisine">
           <?php echo FunctionsV3::displayCuisine($val['cuisine']);?>
           </p>                
                                                       
           <p>
	        <?php 
	        if(!$search_by_location){
		        if ($distance){
		        	echo t("Distance to restaurant").": ".number_format($distance,1)." $distance_type";
		        } else echo  t("Distance to restaurant").": ".t("not available");
	        }
	        ?>
	        </p>
	        
	        <?php //if($val['service']!=3):?>
	        <?php if($show_delivery_info):?>
	        <!--<p><?php echo t("Delivery Est")?>: <?php echo FunctionsV3::getDeliveryEstimation($merchant_id)?></p>-->
	        <?php endif;?>
	        
	        <p>
	        <?php 	        
	        //if($val['service']!=3){
	        if($show_delivery_info){
		        if (!empty($merchant_delivery_distance)){		        	
		        	//echo t("Delivery Distance").": ".$merchant_delivery_distance." $distance_type_orig";
		        	echo t("Delivery Distance").": ".$merchant_delivery_distance." ".t($distance_type_orig);
		        } else echo  t("Delivery Distance").": ".t("not available");
	        }
	        ?>
	        </p>
	                                
	        <p>
	        <?php 
	        //if($val['service']!=3){
	        if($show_delivery_info){
		        if ($delivery_fee){
		             echo t("Delivery Fee").": ".FunctionsV3::prettyPrice($delivery_fee);
		        } else echo  t("Delivery Fee").": ".t("Free Delivery");
	        }
	        ?>
	        </p>
	        
	        <?php if(method_exists('FunctionsV3','getOffersByMerchantNew')):?>
	        <?php if ($offer=FunctionsV3::getOffersByMerchantNew($merchant_id)):?>
	          <?php foreach ($offer as $offer_value):?>
	            <p><?php echo $offer_value?></p>
	          <?php endforeach;?>
	        <?php endif;?>
	        <?php endif;?>
	        
	      <!--  <p class="top15"><?php echo FunctionsV3::getFreeDeliveryTag($merchant_id)?></p> -->
	        
	    
	    </div> <!--col-->
	    
	    <div class="col-md-3 relative border">
	    
	      <!--<a href="<?php echo Yii::app()->createUrl('store/menu/merchant/'.$val['restaurant_slug'])?>" -->
	      <a href="<?php echo Yii::app()->createUrl("/menu-". trim($val['restaurant_slug']))?>" 
         class="orange-button rounded3 medium">
          <?php echo t("Order Now")?>
         </a>   
	    
	    </div>
    </div> <!--row-->
    
    </div> <!--inner-->
</div>  <!--infinite-item-->   