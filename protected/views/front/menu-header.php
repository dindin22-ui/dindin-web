
<?php //echo "<pre>"; print_r($_SESSION); exit(); 
$url=isset($_SERVER['REQUEST_URI'])?explode("/",$_SERVER['REQUEST_URI']):false;		
		if(!is_array($url) && count($url)<=0){		
			 $this->render('404-page',array(
			   'header'=>true,
			  'msg'=>"Sorry but we cannot find what you are looking for"
			));			
			return ;
		}			
		$page_slug=$url[count($url)-1];
		$page_slug=str_replace('menu-','',$page_slug);			
		if(isset($_GET)){				
			$c=strpos($page_slug,'?');
			if(is_numeric($c)){
				$page_slug=substr($page_slug,0,$c);
			}
		}			
		$page_slug=trim($page_slug);
		//dump($page_slug);
		if (isset($data['merchant'])){
			
		} else $data['merchant']=$page_slug;		
		$res=FunctionsV3::getMerchantBySlug($data['merchant']);
		 $distance=FunctionsV3::getDistanceBetweenPlot(
			                $_SESSION['client_location']['lat'],
			                $_SESSION['client_location']['long'],
			                $res['latitude'],$res['lontitude'],$distance_type
			             );
		 //exit('jijijijijibhvgvg');
?>

         
         
<style>
ul#tabs li{
		margin-bottom: 0px!important;
		padding:5px auto!important;
	}
	ul#tabs li.active {
		color:#f75d34!important;
	}
	ul#tabs li.active i,
	ul#tabs li i{
		position: relative;
		top:3px;
		left:3px;
	}
	
@media only screen and (min-width: 320px) and (max-width: 1024px){

.mobile-banner-wrap .layer {      
min-height: 265px !important;
}

.shareWal{
	min-width: 53px!important;
    top: 24%;
    left: auto!important;
    right: 25px;
    background-color: transparent!important;
}
.shareWal .btn{
	border-radius: 0px!important;
}
.mobile-social-share ul {
    float: right;
    list-style: none outside none;
    margin: 0;
    min-width: 61px;
    padding: 0;
}

.share {
    min-width: 17px;
}

.mobile-social-share li {
    display: block;
    font-size: 18px;
    list-style: none outside none;
    margin-bottom: 3px;
    margin-left: 4px;
    margin-top: 3px;
}

.btn-share {
    background-color: #BEBEBE;
    border-color: #CCCCCC;
    color: #333333;
}

.btn-twitter {
    background-color: #3399CC !important;
    width: 51px;
    color:#FFFFFF!important;
}

.btn-facebook {
    background-color: #3D5B96 !important;
    width: 51px;
    color:#FFFFFF!important;
}

.btn-facebook {
    background-color: #3D5B96 !important;
    width: 51px;
    color:#FFFFFF!important;
}

.btn-google {
    background-color: #DD3F34 !important;
    width: 51px;
    color:#FFFFFF!important;
}

.btn-linkedin {
    background-color: #1884BB !important;
    width: 51px;
    color:#FFFFFF!important;
}

.btn-pinterest {
    background-color: #CC1E2D !important;
    width: 51px;
    color:#FFFFFF!important;
}

.btn-mail {
    background-color: #FFC90E !important;
    width: 51px;
    color:#FFFFFF!important;
}

.caret {
    border-left: 4px solid rgba(0, 0, 0, 0);
    border-right: 4px solid rgba(0, 0, 0, 0);
    border-top: 4px solid;
    display: inline-block;
    height: 0;
    margin-left: 2px;
    vertical-align: middle;
    width: 0;
}

#socialShare {
    max-width:59px;
    margin-bottom:18px;
}

#socialShare > a{
    padding: 6px 10px 6px 10px;
}

@media (max-width : 320px) {
    #socialHolder{
        padding-left:5px;
        padding-right:5px;
    }
    
    .mobile-social-share h3 {
        margin-left: 0;
        margin-right: 0;
    }
    
    #socialShare{
        margin-left:5px;
        margin-right:5px;
    }
    
    .mobile-social-share h3 {
        font-size: 15px;
    }
}





	/*end share code */
	.mob_navbar_custom ul {
		list-style-type: none;
		padding: 0px;
		text-align: center;
	}

	.mob_navbar_custom ul li{
		width: 33%;
	    float: left;
	    font-size: 12px;
	    line-height: 45px;
	    border-bottom: 1.5px solid transparent;
	    /*margin-bottom: 4px;*/
	}

	.mob_navbar_custom >ul>.active{
		border-bottom: 1px solid red;
	}

	.mob_navbar_custom ul li:hover{
		border-bottom: 1px solid red;
	}

	.mobile_version{
		display: block !important;
	}

	.desktop_version{
		display: none;
	}

	.mobile_banner_custom{
		display: block !important;	
	}
	
	.mob_navbar_custom{
		display: block !important;
	}

	.share_button {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		margin-right: 15px;
		color: skyblue;
		display: block;
		float: right;
		text-align: center;
		background: #fff;
		padding-top: 10px;
		font-size: 20px;
		position: absolute;
		right: 15px;
		top: 5px;
		z-index: 9;
	}

    .banner_heading {
        position: relative;
        top: 25px;
        padding-left: 15px;
    }

    .banner_heading h1 {
        color: #fff;
        margin: 0px 0px 5px 0px !important;
    }

    .banner_heading p {
        color: #fff;
        margin: 0px 0px 5px 0px !important;
        font-size: 14px;
    }

	.para_open p{
		line-height: 15px !important;
	}

	.banner_logo img{
		height: 100% !important;
		max-height: 100% !important;
		float: right !important;
	}

	.banner_logo{
		padding-right: 15px;
		margin-right: 15px;
		position: absolute; 
		right: -20px; 
		bottom: 21px; 
	}

	
	ul#tabs li span {
        font-size: 14px;
        font-weight: bold;
	}
}



@media only screen and (min-width: 749px) and (max-width: 1024px){
	.mobile_banner_custom{
		min-height: 265px;
        margin-top: 55px;
	}

	.mobile_banner_custom img{
		min-height: 265px;
		max-height: 100%;
	}

	.logo-medium {
   		max-width: 105px !important;
    	min-width: 105px !important;
	}
	.xs_menuDistance{
		padding-left: 0px;
	}
	.xs_menuDistance h4,
	.xs_menuDistance p{
		text-align: left;
		margin-top: 0px;
	}
	.xs_menuDistance span{
		padding: 1em 2.6em 1em!important;
		 position: relative;
    	top: 16px;

	}
	.xs_menuDistance p{
		color:#5cb85c;
	}
}

@media only screen and (min-width: 320px) and (max-width: 748px){
	.mobile_banner_custom{
		min-height: 265px;
        margin-top: 55px;
	}

	.mobile_banner_custom img{
		height: 265px;
		max-height: 100%;
	}
	ul#tabs li{
		margin-bottom: 0px!important;
		padding:5px 0px!important;
	}
}	

@media only screen and (min-width: 970px) and (max-width:1024px){	
	.mob_navbar_custom{
		margin-top: 73px;
		height: 48px;
		padding-bottom: 5px;
	}
}

@media only screen and (min-width: 481px) and (max-width:969px){	
	.mob_navbar_custom{
		margin-top: 56px;
		height: 48px;
		padding-bottom: 5px;
	}
}	

@media only screen and (min-width: 320px) and (max-width: 480px){	
	.mob_navbar_custom{
		margin-top: 46px;
		height: 45px;
		padding-bottom: 5px;
	}

	.mob_navbar_custom ul {
		list-style-type: none;
		padding: 0px;
		text-align: center;
	}

	.mob_navbar_custom ul li{
		width: 33%;
	    float: left;
	    font-size: 12px;
	    line-height: 45px;
	    border-bottom: 1.5px solid transparent;
	    margin-bottom: 0px!important;
	}

	.mob_navbar_custom >ul>.active{
		border-bottom: 1px solid red;
	}

	.mob_navbar_custom ul li:hover{
		border-bottom: 1px solid red;
	}
	ul#tabs li{
		margin-bottom: 0px!important;
	}
}	
@media only screen and ( min-width: 320px) and (max-width: 749px){
	.xs_menuDistance{
		padding-left: 15px;
	}
	.xs_menuDistance h4,
	.xs_menuDistance p{
		text-align: left;
	}
	.xs_menuDistance p{
		color:#5cb85c;
	}
	.xs_menuDistance span{
		    padding: .5em 1.6em .6em!important;
    position: relative;
    top: 28px;
    right: 10px;

	}
	.progress-dot {
		display: none!important;
	}
	
	.order-progress-bar {
		display: none!important;
	}
	
	.xs_p0{
		padding: 0px;
	}
	.xs_m0{
		margin: 0px;
	}
}
@media only screen and (min-width: 1025px) and (max-width: 3000px){	
	.mobile_banner_custom{
		display: none !important;	
	}
	
	.mob_navbar_custom{
		display: none !important;
	}

	.mobile_version{
		display: none !important;
	}

	.desktop_version{
		display: block;
	}
	.desktop_menu_header{
		display: none;
	}

}
@media only screen and (min-width: 320px) and (max-width: 359px){
		ul#tabs li span {
		    font-size: 12px!important;
		    font-weight: bold;
		}
	}
	

.weblink { color: #fff; } /* CSS link color */
.label-success {
    background-color: #d9f9e6;
    color: #589278;
    padding: 5px 10px;
    font-size: 12px;
}
a:focus, a:hover {
    color: #ee7545;
    text-decoration: none;
}
.selectdiv {
    padding: 20px;
    background: #FFF;
    border: 1px solid #eee;
    display: block;
    width: 70%;
    display: none;
    border-radius: 5px;
    position: absolute;
}
.selectdiv ul li{
    list-style: none;
    padding-bottom: 10px;
    padding-top: 10px;
    color: #000;
}

.box {
  width: 40%;
  margin: 0 auto;
  background: rgba(255,255,255,0.2);
  padding: 35px;
  border: 2px solid #fff;
  border-radius: 20px/50px;
  background-clip: padding-box;
  text-align: center;
}

.button {
  font-size: 1em;
  padding: 10px;
  color: #fff;
  border: 2px solid #06D85F;
  border-radius: 20px/50px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease-out;
}
.button:hover {
  background: #06D85F;
}
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
}
.popup .content p{
	color:#444;
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
<link rel="stylesheet" type="text/css" href="<?php echo  Yii::app()->request->baseUrl; ?>/assets/slickSlider/slick.css">


<div class="clearfix"></div>

<div class="mobile-banner-wrap relative mobile_banner_custom" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),url('<?php echo empty($background)?assetsURL()."/images/b-2-mobile.jpg":uploadURL()."/$background"; ?>');background-size: cover;
    background-repeat: no-repeat;">
 <div class="layer">
 	<div class="banner_heading">
 		
 			<h1><?php echo clearString($restaurant_name)?></h1>
	
	<?php if(!empty($social_facebook_page) || !empty($social_twitter_page) || !empty($social_google_page)):?>
	<ul class="merchant-social-list">
	 <?php if(!empty($social_google_page)):?>
	 <li>
	   <a href="<?php echo FunctionsV3::prettyUrl($social_google_page)?>" target="_blank">
	    <i class="ion-social-googleplus"></i>
	   </a>
	 </li>
	 <?php endif;?>
	 
	 <?php if(!empty($social_twitter_page)):?>
	 <li>
	   <a href="<?php echo FunctionsV3::prettyUrl($social_twitter_page)?>" target="_blank">
	   <i class="ion-social-twitter"></i>
	   </a>
	 </li>
	 <?php endif;?>
	 
	 <?php if(!empty($social_facebook_page)):?>
	 <li>
	   <a href="<?php echo FunctionsV3::prettyUrl($social_facebook_page)?>" target="_blank">
	   <i class="ion-social-facebook"></i>
	   </a>
	 </li>
	 <?php endif;?>
	 
	</ul>
	<?php endif;?>
	
	<p><i class="fa fa-map-marker"></i> <?php echo $merchant_address?></p>
	<?php 
$DbExt=new DbExt;
		$stmt="SELECT * FROM
		{{merchant}}
		WHERE
		merchant_id='$merchant_id'
		LIMIT 0,1
		";
		if ($res=$DbExt->rst($stmt)){ ?>
			<p class="small"><i class="fa fa-mobile-phone" style="font-size:15px;margin-right: 6PX;"></i>

			<a class="weblink" href="tel://<?php echo $res[0]['restaurant_phone']; ?>">
	  <?php echo $res[0]['restaurant_phone']; ?>
	</a></p>
			<!-- // return $res[0]; -->
		<?php }
		
		// return false;	
?>
	<!-- <p class="small"><?php //echo FunctionsV3::displayCuisine($cuisine);?>--> 
<!--	<p><?php echo FunctionsV3::getFreeDeliveryTag($merchant_id)?></p>-->

	
	<p class="small">
	<?php // echo t("Back to website").": "?>
        <i style="font-size:15px;margin-right: 6PX;" class="fa fa-globe"></i>
        <a class="weblink" href="<?php echo FunctionsV3::fixedLink($merchant_website)?>">
             <?php echo trim($merchant_website);?>
        </a>
	</p> 
        <?php
            $opening_hours = FunctionsV3::getOpeningHours($merchant_id);
            $day = strtolower(date("l"));
            if(count($opening_hours) > 0){
        ?>
                <p class="small" id="opening_hours_down">
                    <?php // echo t("Back to website").": "?>
                    <i style="font-size:15px;margin-right: 6PX;" class="fa fa-clock-o"></i>
                    <?php echo $opening_hours[$day]['hours'] ?>
                    <i style="font-size:15px;margin-left: 6PX;" class="fa fa-angle-down"></i>
                </p>
                    <div class="selectdiv">
                    <ul>
                        <?php foreach ($opening_hours as $key=>$value){ ?>
                            <li>  <?php echo ucfirst($key).": ". $value['hours']; ?> </li>
                        <?php } ?>
                    </ul>
            </div>
                <div style="clear: both;height: 20px"></div>

        <?php } ?>

 


        <p><?php echo FunctionsV3::merchantOpenTag($merchant_id)?>  </p>

        <?php
		$messages = FunctionsV3::PopUpMessage($merchant_id);
		if(is_array($messages) && count($messages) > 0){
		?>
		<div id="messages_popup" class="overlay">
			<div class="popup">
				<a onclick="closePopup()" class="close" href="#"><img style="width:30px;height:30px" src="<?php echo baseUrl(); ?>/assets/images/close.png" /></a>
        		<div style="clear: both;height: 35px"></div>
				<div class="content">
					<?php foreach($messages as $message){
					?>
					<!-- <h3><?php echo $message['title']; ?></h3> -->
					<?php echo $message['message']; ?>
					<?php
					}?>
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
		<?php } ?>
        <div style="clear: both;height: 20px"></div>
 	</div>
<div class="banner_logo">
	<!--<img class="logo-medium bottom15" src="<?php //echo $merchant_logo;?>">-->
	<!--<img class="logo-medium" src="<?php echo $merchant_logo;?>">-->
</div>
 </div>
 		<!-- <button href="#"  class="btn btn-info dropdown-toggle share"> -->
    				<!--	<a href="#" class="share_button" data-toggle="dropdown"><i class="fa fa-share-alt" aria-hidden="true"></i>
    					</a>-->
 
 	<ul class="dropdown-menu shareWal">
 		<?php $pageurl = urlencode("http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]); ?>
        				<li>
    					    <a data-original-title="Twitter" rel="tooltip"  href="https://twitter.com/share?url=<?php echo $pageurl;?>" class="btn btn-twitter" data-placement="left">
								<i class="fa fa-twitter"></i>
							</a>
    					</li>
    					<li>
    						
    						<a data-original-title="Facebook" rel="tooltip"  href="http://www.facebook.com/sharer/sharer.php?u=<?php echo $pageurl;?>" class="btn btn-facebook" data-placement="left">
								<i class="fa fa-facebook"></i>
							</a>
    					</li>					
    					<li>
    						<a data-original-title="Google+" rel="tooltip"  href="https://plus.google.com/share?url=<?php echo $pageurl;?>" class="btn btn-google" data-placement="left">
								<i class="fa fa-google-plus"></i>
							</a>
    					</li>
    				    
                    </ul>
</div>
 



	
	<?php if ( getOption($merchant_id,'merchant_show_time')=="yes"):?>
	<p class="small">
	<?php //echo
		// echo $merchant_id; exit; 
	// Yii::app()->functions->translateDate(date('F d l')." ".timeFormat(date('c'),true));?>
	
	
	</p>
	<?php endif;?>
	
	<?php //if (!empty($merchant_website)):?>
	<p class="small">
	<?php //echo t("Website").": "?>
	<a target="_blank" href="<?php //echo FunctionsV3::fixedLink($merchant_website)?>">
	  <?php //echo $merchant_website;?>
	</a>
	</p>
	<?php //endif;?> 
			
</div> <!--search-wraps-->

</div> <!--parallax-container-->
</div>








<div class="desktop_version">
	<div class="mob_navbar_custom">
	<ul>
		<li class="active"> <span>Menu</span> <i class="ion-fork"></i></li>
		<li> <span>Opening Hours</span> <i class="ion-clock"></i> </li>
		<li> <span>Information</span> <i class="ion-ios-information-outline"></i> </li>
	</ul>
</div>
<div class="clearfix"></div>

<div class="mobile-banner-wrap relative mobile_banner_custom">
 <div class="layer"></div>
 <img class="mobile-banner" src="<?php echo empty($background)?assetsURL()."/images/b-2-mobile.jpg":uploadURL()."/$background"; ?>">

</div>

<div id="parallax-wrap" class="parallax-search parallax-menu" 
data-parallax="scroll" data-position="top" data-bleed="10" 
data-image-src="<?php echo empty($background)?assetsURL()."/images/b-2.jpg":uploadURL()."/$background"; ?>">

<div class="search-wraps center menu-header">

     <!-- <img class="logo-medium bottom15" src="<?php echo $merchant_logo;?>"> 
      
	  <div class="mytable">
		 <!-- <div class="mycol">
	       <div class="rating-stars" data-score="<?php echo $ratings['ratings']?>"></div>
	     </div> -->
	     <!--<div class="mycol">
	        <p class="small">
	        <a href="javascript:;"class="goto-reviews-tab">
	        <?php echo $ratings['votes']." ".t("Reviews")?>
	        </a>
	        </p>
	     </div>	-->
	     <!--<div class="mycol">
	        <?php echo FunctionsV3::merchantOpenTag($merchant_id)?>             
	     </div>-->
	     <div class="mycol">
	        <p class="small"><?php echo t("Minimum Order").": ".FunctionsV3::prettyPrice($minimum_order)?></p>
	     </div>
	   </div> 
	<p></p>
		<div class="mycol">
	        <?php echo FunctionsV3::merchantOpenTag($merchant_id)?>             
	     </div>
	<!--mytable-->

	<h1><?php echo clearString($restaurant_name)?></h1>
	
	<?php if(!empty($social_facebook_page) || !empty($social_twitter_page) || !empty($social_google_page)):?>
	<ul class="merchant-social-list">
	 <?php if(!empty($social_google_page)):?>
	 <li>
	   <a href="<?php echo FunctionsV3::prettyUrl($social_google_page)?>" target="_blank">
	    <i class="ion-social-googleplus"></i>
	   </a>
	 </li>
	 <?php endif;?>
	 
	 <?php if(!empty($social_twitter_page)):?>
	 <li>
	   <a href="<?php echo FunctionsV3::prettyUrl($social_twitter_page)?>" target="_blank">
	   <i class="ion-social-twitter"></i>
	   </a>
	 </li>
	 <?php endif;?>
	 
	 <?php if(!empty($social_facebook_page)):?>
	 <li>
	   <a href="<?php echo FunctionsV3::prettyUrl($social_facebook_page)?>" target="_blank">
	   <i class="ion-social-facebook"></i>
	   </a>
	 </li>
	 <?php endif;?>

	</ul>
	<?php endif;?>
	
	<p><i class="fa fa-map-marker"></i> <?php echo $merchant_address?></p>
	<p class="small"><?php echo FunctionsV3::displayCuisine($cuisine);?></p>
	<p><?php echo FunctionsV3::getFreeDeliveryTag($merchant_id)?></p>
	
	<?php if ( getOption($merchant_id,'merchant_show_time')=="yes"):?>
	<p class="small">
	<?php echo t("Merchant Current Date/Time").": ".
	Yii::app()->functions->translateDate(date('F d l')." ".timeFormat(date('c'),true));?>
	</p>
	<?php endif;?>
	
	<?php if (!empty($merchant_website)):?>
	<p class="small">
	<?php echo t("Website").": "?>
	<a target="_blank" href="<?php echo FunctionsV3::fixedLink($merchant_website)?>">
	  <?php echo $merchant_website;?>
	</a>
	</p>
	<?php endif;?>
			
</div> <!--search-wraps-->

</div> <!--parallax-container-->
</div>
<script>

</script>