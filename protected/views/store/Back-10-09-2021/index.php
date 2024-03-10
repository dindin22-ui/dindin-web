
<script type="text/javascript">
<!--
if (screen.width >= 699) {
// document.location = "redirect.html";
}
//-->
</script>
<style>
@media only screen and (min-width: 320px) and (max-width: 1024px){
    .search-wraps h1{
      color: #ffffff;
    font-weight: bolder;
    font-family: unset;
    text-transform: capitalize;
    }
}

	@media only screen and (min-width: 320px) and (max-width: 600px){		
		.single-search{
        position: absolute;
        top: 13%;
        left: 0%;
        right: 0%;
		}
		
		.search-wraps h1{
			color: #ffffff;
		}
		.search-wraps p{
			color: #ffffff;
		}
	}
</style>
<?php 
$kr_search_adrress = FunctionsV3::getSessionAddress();

$home_search_text=Yii::app()->functions->getOptionAdmin('home_search_text');
if (empty($home_search_text)){
	$home_search_text=Yii::t("default","Find restaurants near you");
}

$home_search_subtext=Yii::app()->functions->getOptionAdmin('home_search_subtext');
if (empty($home_search_subtext)){
	$home_search_subtext=Yii::t("default","Order Delivery Food Online From Local Restaurants");
}

$home_search_mode=Yii::app()->functions->getOptionAdmin('home_search_mode');
$placholder_search=Yii::t("default","Street Address,City,State");
if ( $home_search_mode=="postcode" ){
	$placholder_search=Yii::t("default","Enter your postcode");
}
$placholder_search=Yii::t("default",$placholder_search);
?>

<?php if ( $home_search_mode=="address" || $home_search_mode=="") :?>
<div class="xs-home-banner" style="background-image: url('<?php echo assetsURL()."/images/banner.jpg"?>');">
</div>
  <img class="mobile-home-banner" src="<?php echo assetsURL()."/images/banner.jpg"?>">
<div id="parallax-wrap" class="parallax-container parallax-home" 
data-parallax="scroll" data-position="top" data-bleed="10" 
data-image-src="<?php echo assetsURL()."/images/banner.jpg"?>">
<?php 
if ( $enabled_advance_search=="yes"){
	$this->renderPartial('/front/advance_search',array(
	  'home_search_text'=>$home_search_text,
	  'kr_search_adrress'=>$kr_search_adrress,
	  'placholder_search'=>$placholder_search,
	  'home_search_subtext'=>$home_search_subtext,
	  'theme_search_merchant_name'=>getOptionA('theme_search_merchant_name'),
	  'theme_search_street_name'=>getOptionA('theme_search_street_name'),
	  'theme_search_cuisine'=>getOptionA('theme_search_cuisine'),
	  'theme_search_foodname'=>getOptionA('theme_search_foodname'),
	  'theme_search_merchant_address'=>getOptionA('theme_search_merchant_address'),
	));
} else $this->renderPartial('/front/single_search',array(
      'home_search_text'=>$home_search_text,
	  'kr_search_adrress'=>$kr_search_adrress,
	  'placholder_search'=>$placholder_search,
	  'home_search_subtext'=>$home_search_subtext
));
?>

</div> <!--parallax-container-->
<?php else :?>

<!--SEARCH USING LOCATION-->
<img class="mobile-home-banner" src="<?php echo assetsURL()."/images/b6.jpg"?>">

<div id="parallax-wrap" class="parallax-container parallax-home" 
data-parallax="scroll" data-position="top" data-bleed="10" 
data-image-src="<?php echo assetsURL()."/images/b6.jpg"?>">

  <?php 
  $location_type=getOptionA("admin_zipcode_searchtype");  
  $this->renderPartial('/front/location-search-'.$location_type,array(
   'location_search_type'=>$location_type
  ));
  ?>

</div> <!--parallax-container-->

<?php endif;?>

<?php if ($theme_hide_how_works<>2):?>
<!--HOW IT WORKS SECTIONS-->

    
<script type="text/javascript">
      var framefenster = document.getElementsByTagName("iFrame");
      var auto_resize_timer = window.setInterval("autoresize_frames()", 400);
      function autoresize_frames() {
        for (var i = 0; i < framefenster.length; ++i) {
            if(framefenster[i].contentWindow.document.body){
              var framefenster_size = framefenster[i].contentWindow.document.body.offsetHeight;
              if(document.all && !window.opera) {
                framefenster_size = framefenster[i].contentWindow.document.body.scrollHeight;
              }
              framefenster[i].style.height = framefenster_size + 'px';
            }
        }
      }
    </script>
    
    <iframe id="testimonials" name="testimonials" src="<?php echo Yii::app()->request->baseUrl; ?>/protected/views/store/bottom.html"; allowtransparency="true" onload="this.style.height=(this.contentDocument.body.scrollHeight+45) +'px !important';" scrolling="no" style="width:100%;min-height:1000px;border:none;overflow-y:hidden;overflow-x:hidden;"></iframe>


<?php endif;?>


<!--FEATURED RESTAURANT SECIONS-->
<?php if ($disabled_featured_merchant==""):?>
<?php if ( getOptionA('disabled_featured_merchant')!="yes"):?>
<?php //if ($res=FunctionsV3::getFeatureMerchant()):?>
<div class="sections section-feature-resto">
<div class="container">


  <h2><?php echo t("Featured Restaurants - More coming soon!")?></h2>
  
  <div class="row">
  <div class="list-feature-merchant-loading"></div>
  <?php /* foreach ($res as $val): //dump($val);?>
  <?php $address= $val['street']." ".$val['city'];
        $address.=" ".$val['state']." ".$val['post_code'];
        
        $ratings=Yii::app()->functions->getRatings($val['merchant_id']);
  ?>   
  
    <!--<a href="<?php echo Yii::app()->createUrl('/store/menu/merchant/'. trim($val['restaurant_slug']) )?>">-->
    <a href="<?php echo Yii::app()->createUrl("/menu-". trim($val['restaurant_slug']))?>">
    <div class="col-md-5 border-light ">
    
        <div class="col-md-3 col-sm-3">
           <img class="logo-small" src="<?php echo FunctionsV3::getMerchantLogo($val['merchant_id'],$val['logo']);?>">
        </div> <!--col-->
        
        <div class="col-md-9 col-sm-9">
        
          <div class="row">
              <!-- <div class="col-sm-5">
		          <div class="rating-stars" data-score="<?php echo $ratings['ratings']?>"></div>   
	          </div> -->
	          <div class="col-sm-2 merchantopentag">
	          <?php echo FunctionsV3::merchantOpenTag($val['merchant_id'])?>   
	          </div>
          </div>
          
          <h4 class="concat-text"><?php echo clearString($val['restaurant_name'])?></h4>
          
          <p class="concat-text">
          <?php //echo wordwrap(FunctionsV3::displayCuisine($val['cuisine']),50,"<br />\n");?>
          <?php echo FunctionsV3::displayCuisine($val['cuisine']);?>
          </p>
          <p class="concat-text"><?php echo $address?></p>                             
          <?php echo FunctionsV3::displayServicesList($val['service'])?>      
        </div> <!--col-->
        
    </div> <!--col-6-->
    </a>
    <div class="col-md-1"></div>
      
  <?php endforeach; */?>
  </div> <!--end row-->

  
</div> <!--container-->
</div>
<?php //endif;?>
<?php endif;?>
<?php endif;?>
<!--END FEATURED RESTAURANT SECIONS-->


<?php if ($theme_hide_cuisine<>2):?>
<!--CUISINE SECTIONS-->
<?php if ( $list=FunctionsV3::getCuisine() ): ?>
<div class="sections section-cuisine">
<div class="container  nopad">

<div class="col-md-3 nopad">
<img src="<?php echo assetsURL()."/images/cuisine.png"?>" class="img-cuisine">
</div>

<div class="col-md-9  nopad">

  <h2><?php echo t("Browse by cuisine")?></h2>
  <p class="sub-text center"><?php echo t("choose from your favorite cuisine")?></p>
  
  <div class="row">
    <?php $x=1;?>
    <?php foreach ($list as $val): ?>
    <div class="col-md-4 col-sm-4 indent-5percent nopad">
      <a href="<?php echo Yii::app()->createUrl('/store/cuisine',array("category"=>$val['cuisine_id']))?>" 
     class="<?php echo ($x%2)?"even":'odd'?>">
      <?php 
      $cuisine_json['cuisine_name_trans']=!empty($val['cuisine_name_trans'])?json_decode($val['cuisine_name_trans'],true):'';	 
      echo qTranslate($val['cuisine_name'],'cuisine_name',$cuisine_json);
      if($val['total']>0){
      	echo "<span>(".$val['total'].")</span>";
      }
      ?>
      </a>
    </div>   
    <?php $x++;?>
    <?php endforeach;?>
  </div> 

</div>
  
</div> <!--container-->
</div> <!--section-cuisine-->
<?php endif;?>
<?php endif;?>


<?php if ($theme_show_app==2):?>
<!--MOBILE APP SECTION-->
<div id="mobile-app-sections" class="container">
<div class="container-medium">
  <div class="row">
     <div class="col-xs-5 into-row border app-image-wrap">
       <img class="app-phone" src="<?php echo assetsURL()."/images/getapp-2.jpg"?>">
     </div> <!--col-->
     <div class="col-xs-7 into-row border">
       <h2><?php echo getOptionA('website_title')." ".t("in your mobile")?>! </h2>
       <h3 class="green-text"><?php echo t("Get our app, it's the fastest way to order food on the go")?>.</h3>
       
       <div class="row border" id="getapp-wrap">
       <?php if(!empty($theme_app_ios) && $theme_app_ios!="http://"):?>
         <div class="col-xs-4 border">                      
           <a href="<?php echo $theme_app_ios?>" target="_blank">
           <img class="get-app" src="<?php echo assetsURL()."/images/get-app-store.png"?>">
           </a>           
         </div>
         <?php endif;?>
         
         <?php if(!empty($theme_app_android) && $theme_app_android!="http://"):?>
         <div class="col-xs-4 border">
           <a href="<?php echo $theme_app_android?>" target="_blank">
             <img class="get-app" src="<?php echo assetsURL()."/images/get-google-play.png"?>">
           </a>
         </div>
         <?php endif;?>
         
       </div> <!--row-->
       
     </div> <!--col-->
  </div> <!--row-->
  </div> <!--container-medium-->
  
  <div class="mytable border" id="getapp-wrap2">
     <?php if(!empty($theme_app_ios) && $theme_app_ios!="http://"):?>
     <div class="mycol border">
           <a href="<?php echo $theme_app_ios?>" target="_blank">
           <img class="get-app" src="<?php echo assetsURL()."/images/get-app-store.png"?>">
           </a>
     </div> <!--col-->
     <?php endif;?>
     <?php if(!empty($theme_app_android) && $theme_app_android!="http://"):?>
     <div class="mycol border">
          <a href="<?php echo $theme_app_android?>" target="_blank">
             <img class="get-app" src="<?php echo assetsURL()."/images/get-google-play.png"?>">
           </a>
     </div> <!--col-->
     <?php endif;?>
  </div> <!--mytable-->
  
  
</div> <!--container-->
<!--END MOBILE APP SECTION-->


<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/59c1a625c28eca75e4621107/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->


<?php endif;?>


 