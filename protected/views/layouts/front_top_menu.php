
<div class="top-menu-wrapper <?php echo "top-".$action;?>">

<div class="container border" >
  <div class="col-md-3 col-xs-3 border col-a">
    <?php if ( $theme_hide_logo<>2):?>
    <!--<a href="<?php echo websiteUrl()?>">-->
     <a href="#">
     <img src="<?php echo FunctionsV3::getDesktopLogo();?>" class="logo logo-desktop">
     <img src="<?php echo FunctionsV3::getMobileLogo();?>" class="logo logo-mobile">
    </a>
    <?php endif;?>
  </div>
  

  
 
  
  <!--menu-nav-mobile-->
  
  <?php if(isset(Yii::app()->controller->action->id)):?>
  <?php if ( Yii::app()->controller->action->id =="menu"):
            $service_id = $_SESSION['kr_restaurant_service'];
            if($service_id == 8 || $service_id == 1 || $service_id == 4){
                $description = '<span class="red">Pickup</span> and <span class="red">Delivery</span>';
            }elseif ($service_id == 2 || $service_id == 5){
                $description = '<span class="red">Delivery</span>';
            }elseif($service_id == 3 || $service_id == 6){
                $description = '<span class="red">Pickup</span>';
            }else{
                $description = '<span class="red">Dinein</span>';
            }
          ?>
        <h4 class="service"><?php echo $description; ?></h4>
  <div class="col-xs-1 cart-mobile-handle border relative" style="display: none;">
      <div class="badge cart_count"></div>
     <a href="<?php echo Yii::app()->createUrl('store/cart')?>">       
       <i class="ion-ios-cart"></i>
     </a>
  </div> <!--cart-mobile-handle-->
  <?php endif;?>
  <?php endif;?>
  
 
  
  
 <!-- <div class="col-md-9 border col-b">
    <?php $this->widget('zii.widgets.CMenu', FunctionsV3::getMenu() );?> 
    <div class="clear"></div>
  </div>-->
  
</div> <!--container-->

</div> <!--END top-menu-->

<!--<div class="menu-top-menu">
    <?php $this->widget('zii.widgets.CMenu', FunctionsV3::getMenu('mobile-menu') );?> 
    <div class="clear"></div>
</div> <!--menu-top-menu-->