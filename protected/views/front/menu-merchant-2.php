<script type="text/javascript">
<!--
if (screen.width >= 699) {
// document.location = "redirect.html";
}
//-->
</script>

<style>
@media only screen and (max-width: 750px){

.menu-2 a.dsktop{
        display: block !important;
}
}
@media only screen and (min-width: 320px) and (max-width: 1024px){
  
}
@media only screen and (min-width: 320px) and (max-width: 768px){
    .mobile_menuMerchant img.merchant_log{
            position: absolute;
            top: 21px;
            width: 38%;
            right: 6px;
            border-radius: 0px 4px 4px 0px;
            display: block;
            max-width: 38%;
            min-height: 38%;
            object-fit: cover;
    }
    .mobile_menuMerchant{
        display: block;
    }
    .desktop_menuMerchant{
        display: none;
    }
    .draggable{
        padding: 0px 0px !important;
    }
    .xs_merchantCat{
        display: block;
        margin-bottom: 10px;
        color:#fff;
    }
    .mobile_menuMerchant .panel-body {
        padding: 2px 10px;
    }
    .xs_merchantDesc{
        line-height: 17px !important;
        color:#fff!important;
        margin-bottom: 0px;
    }
    .mobile_menuMerchant .section-label a.section-label-a span{
        background: transparent;
        color:#fff;
        font-size: 18px;
        text-transform: uppercase;
        padding-right: 32px;
    }
     .mobile_menuMerchant .section-label a.section-label-a{
        padding-bottom: 0px;
     }
     .mobile_menuMerchant .section-label a.section-label-a:before{
           content: "❮";
            position: absolute;
            right: 10px;
            font-size: 24px;
            color: #84448a;
            /* top: 5px; */
            height: 25px;
            padding: 2.5px 3.5px;
            width: 25px;
            text-align: center;
            line-height: 20px;
            background: white;
            border-radius: 50%;
            }
    .mobile_menuMerchant .section-label a.collapsed:before{
        content: "❯";
        position: absolute;
        right: 10px;
        font-size: 24px;
        color: #ff6339;
        /* top: 5px; */
        height: 25px;
        padding: 2.5px 3.5px;
        width: 25px;
        text-align: center;
        line-height: 20px;
        background: white;
        border-radius: 50%;
    }
	.panel-default {
    border-color: white;
	}
    .mobile_menuMerchant .section-label a.section-label-a:before:hover{
        cursor: pointer;
    }
    .mobile_menuMerchant .panel-heading{
        position: relative;
        background: #ff6339;
        color:#fff;
        border-radius: 10px;
    }
    .mobile_menuMerchant .section-label a.section-label-a b{
        border-bottom: none!important;
    }
    .mobile_menuMerchant .panel-default{
        margin-bottom: 5px;
    }
    .mobile_menuMerchant .panel-group {
        background: #fff!important;
    }
    .xs_itemHead{
        color: #964c4c;
        font-family: "Montserrat",sans-serif;
        font-weight: bolder;
        font-size: 16px;
        margin-bottom: 5px!important;
    }
    .mobile_menuMerchant .food-description{
        min-height: auto!important;
        color: #000;
    }
    .mobile_menuMerchant .box-grey{
        border:none!important;
        margin-bottom: 0px;
        margin-top: 10px;
    }
    .xs_p0{
        padding: 0px;
    }
    .xs_m0{
        margin: 0px;
    }
    .mobile_menuMerchant .food-thumbnail{
     width: 165px!important;
     float: right;
    }
    .mobile_menuMerchant h5{
        margin: 0px;
    }
    .xs_menuBtn {
    display: block !important;
    text-align: left !important;
    font-weight: bold !important;
    color: black !important;
    font-family: "Montserrat",sans-serif;
    }
    .xs_p_slider .slide_title{
        margin-top: 10px!important;
        display: block;  
        position: relative;
        top:10px;
    }
    .xs_p_slider .orange-button{
    background: #ea3893;
    color: #fff;
    width: 100%;
    border:none;
    }
    .xs_p_slider .Slideitem .box-grey{
        padding: 10px!important;
    }
    .mobile_menuMerchant img {
        position: absolute;
        top: 1px;
        width: 39%;
        right: 6px;
        border-radius: 0px 4px 4px 0px;
        display: block;
        max-width: 100%;
        max-height: 100%;
        min-height: 78%;
        object-fit: cover;
    }
    .Slideitem{
        /*width: 300px!important;*/
    }
        .slick-list{padding:0 25% 0 0!important;width: auto!important;min-width: auto!important;}
        .chepBox{
                position: relative;
    top: 0px;
    width: 138%;
    z-index: 9999999!Important;
        }
}
@media only screen and (min-width: 750px) and (max-width: 3000px){
    .mobile_menuMerchant{
        display: none;
    }
    .desktop_menuMerchant{
        display: block;
    }
    .xs_padding_0{
        padding:0px;
    }
    .xs_margin_0{
        margin:0px;
    }

}

</style>
<div class="mobile_menuMerchant">
    
    <div class="multiple-items xs_p_slider">
        <?php
        if(is_array($menu) && count($menu)>=1):
            $menu_array = array();
            $merchant_details = FunctionsV3::getMerchantById($merchant_id);
            $top_items = FunctionsV3::getTopItems($merchant_id);
            foreach ($menu as $val):
                $hour = date('G:i');
            
                $currentTime = strtotime($hour);
                $enabled_category_sked = getOption($merchant_id,'enabled_category_sked');
                if($enabled_category_sked != 1){
                    $val['start_time'] = '0:00';
                    $val['end_time'] = '24:00';
                }
                $startTime = strtotime($val['start_time'].':00');
                $endTime = strtotime($val['end_time'].':00');
                if (
                    (
                        $startTime < $endTime &&
                        $currentTime >= $startTime &&
                        $currentTime <= $endTime
                    ) ||
                    (
                        $startTime > $endTime && (
                            $currentTime >= $startTime ||
                            $currentTime <= $endTime
                        )
                    )
                ) {
                    if(is_array($val['item']) && count($val['item']) > 0) {
                        $menu_array[] = $val;
                    }
                }
            endforeach;
                if(count($menu_array) > 0){
                    ?>
                    <div class="m1 menu-scroll" id="menu-scroll-fixed">
                        <div id="menu-center">
                            <ul id="menu-center-ul">
                                
                                <?php if (count($top_items) > 0){ ?>
                                        <li><a class="active" href="#popular_item">Popular Items</a></li>
                                <?php
                                }?>
                                <?php $i = 0;
                                foreach ($menu_array as $menu_val){
                                    $css_id = strtolower(str_replace(" ","_",$menu_val['category_name']))."_".$menu_val['category_id'];
                                ?>
                                    <li><a href="#<?php echo $css_id; ?>"><?php echo $menu_val['category_name']; ?></a></li>
                                <?php
                                    if($i == 3){
                                        ?>
                                    <?php
                                        ?>
                                    <?php
                                    }
                                    $i++;
                                }
                                ?>

                            </ul>
                        </div>
                    </div>
                    <?php if (count($top_items) > 0){ ?>
                    <div id="popular_item" style="overflow: hidden" class="menu_details">
                        <h4 class="panel-title">Popular Items </h4>
                        <?php if(is_array($top_items)){
                                foreach($top_items as $top_item){
                                    $val_item = Yii::app()->functions->getItemDetailsByItemId($top_item['item_id'],true,$merchant_id);
                                    if(is_array($val_item)){
                                        $category_ids = json_decode($val_item['category'],true);
                                        $res_category=Yii::app()->functions->getCategory($category_ids[0]);
                                        $atts='';
                                        if ( $val_item['single_item']==2){
                                            $atts.='data-price="'.$val_item['single_details']['price'].'"';
                                            $atts.=" ";
                                            $atts.='data-size="'.$val_item['single_details']['size'].'"';
                                        }
                                        ?>

                                        <div class="col-md-6" style="padding-left:5px;padding-right:5px;">
                                            <div class="box-grey" style="padding:0; margin: 0">
                                                <div class=" xs_p0">

                                                    <?php $path=FunctionsV3::uploadPath()."/".$val_item['photo']; ?>
                                                    <div class="food_item_left" <?php if (!file_exists($path) || empty($val_item['photo'])){ echo 'style="width:70%";clear:both'; }?>>
                                                        <a href="javascript:;"
                                                        class="mbile rounded3 menu-item <?php echo $val_item['not_available']==2?"item_not_available":''?>"
                                                        rel="<?php echo $val_item['item_id']?>"
                                                        data-single="<?php echo $val_item['single_item']?>"
                                                            <?php echo $atts;?>
                                                        data-category_id="<?php echo $res_category['category_id']?>"
                                                        data-category_name="<?php echo qTranslate($res_category['category_name'],'category_name',$res_category); ?>"
                                                        >

                                                            <p class="bold top10 xs_itemHead"><?php echo qTranslate($val_item['item_name'],'item_name',$val_item)?></p>
                                                            <p class="small food-description read-more" style="width:80%;">
                                                                <?php echo qTranslate(strip_tags($val_item['item_description']),'item_description',$val_item)?>
                                                            </p>
                                                            <?php
                                                            if (strlen($val_item['item_description'])<59){
                                                                //echo '<div class="dummy-link"></div>';
                                                            }
                                                            ?>

                                                            <?php if ( $disabled_addcart==""):?>
                                                                <div class="center top10 food-price-wrap">
                                                                    <p class="xs_menuBtn">
                                                                        <?php echo FunctionsV3::getItemFirstPrice($val_item['prices'],$val_item['discount']) ?>
                                                                    </p>
                                                                </div>
                                                            <?php endif;?>
                                                        </a>
                                                    </div>
                                                    <?php if (!file_exists($path) || empty($val_item['photo'])) {
                                                        $path=FunctionsV3::uploadPath()."/".$merchant_details['logo'];
                                                        if(!file_exists($path) || empty($merchant_details['logo'])){
                                                        }else{
                                                            ?>
                                                            <div class="food_item_right">
                                                                <a href="javascript:;"
                                                                class="mbile rounded3 menu-item <?php echo $val_item['not_available']==2?"item_not_available":''?>"
                                                                rel="<?php echo $val_item['item_id']?>"
                                                                data-single="<?php echo $val_item['single_item']?>"
                                                                    <?php echo $atts;?>
                                                                data-category_id="<?php echo $menu_val['category_id']?>"
                                                                data-category_name="<?php echo qTranslate($menu_val['category_name'],'category_name',$menu_val); ?>"
                                                                >
                                                                    <img class="merchant_log" sizes="(max-width: 768px) 25vw, (min-width: 768px) 200px" src="<?php echo Yii::app()->request->baseUrl."/upload/".$merchant_details['logo']; ?>" width="100%">
                                                                </a>
                                                            </div>
                                                        <?php
                                                        }
                                                    }else{ ?>
                                                    <div class="food_item_right">
                                                        <a href="javascript:;"
                                                           class="mbile rounded3 menu-item <?php echo $val_item['not_available']==2?"item_not_available":''?>"
                                                           rel="<?php echo $val_item['item_id']?>"
                                                           data-single="<?php echo $val_item['single_item']?>"
                                                            <?php echo $atts;?>
                                                           data-category_id="<?php echo $menu_val['category_id']?>"
                                                           data-category_name="<?php echo qTranslate($menu_val['category_name'],'category_name',$menu_val); ?>"
                                                        >
                                                            <img sizes="(max-width: 768px) 25vw, (min-width: 768px) 200px" src="<?php echo FunctionsV3::getFoodDefaultImage($val_item['photo'],false); ?>" width="100%">
                                                        </a>
                                                    </div>
                                                    <?php } ?>
                                                    <div style="clear: both"></div>
                                                </div>
                                                <hr>
                                                <div class="clearfix"></div>
                                            </div> <!--box-grey-->
                                        </div>
                                    <?php
                                    }
                                }   
                        } ?>
                    </div>
                    <?php } ?>
                    <?php $i = 0;
                    foreach  ($menu_array as $menu_val){
                        $css_id = strtolower(str_replace(" ","_",$menu_val['category_name']))."_".$menu_val['category_id'];
                        ?>
                        <div id="<?php echo $css_id; ?>" class="menu_details" <?php if($i == 0){ ?>style="overflow: hidden"; <?php } ?>>
                            <h4 class="panel-title">
                                <?php echo qTranslate($menu_val['category_name'],'category_name',$menu_val)?>
                            </h4>
                            <?php if (!empty($menu_val['category_description'])):?>
                                <p class="xs_merchantDes" style="color: #000 !important;"><?php echo strip_tags($menu_val['category_description']); ?></p>
                            <?php endif; ?>
                            <?php $x=0?>
                            <?php if (is_array($menu_val['item']) && count($menu_val['item'])>=1):?>
                                <?php foreach ($menu_val['item'] as $val_item):?>

                                    <?php
                                    $atts='';
                                    if ( $val_item['single_item']==2){
                                        $atts.='data-price="'.$val_item['single_details']['price'].'"';
                                        $atts.=" ";
                                        $atts.='data-size="'.$val_item['single_details']['size'].'"';
                                    }
                                    ?>

                                    <div class="col-md-6" style="padding-left:5px;padding-right:5px;">
                                        <div class="box-grey" style="padding:0; margin: 0">

                                            <div class=" xs_p0">

                                                <?php $path=FunctionsV3::uploadPath()."/".$val_item['photo']; ?>
                                                <div class="food_item_left" <?php if (!file_exists($path) || empty($val_item['photo'])){ echo 'style="width:59%";clear:both'; }?>>
                                                    <a href="javascript:;"
                                                       class="mbile rounded3 menu-item <?php echo $val_item['not_available']==2?"item_not_available":''?>"
                                                       rel="<?php echo $val_item['item_id']?>"
                                                       data-single="<?php echo $val_item['single_item']?>"
                                                        <?php echo $atts;?>
                                                       data-category_id="<?php echo $menu_val['category_id']?>"
                                                       data-category_name="<?php echo qTranslate($menu_val['category_name'],'category_name',$menu_val); ?>"
                                                    >

                                                        <p class="bold top10 xs_itemHead"><?php echo qTranslate($val_item['item_name'],'item_name',$val_item)?></p>
                                                        <p class="small food-description read-more">
                                                            <?php echo qTranslate(strip_tags($val_item['item_description']),'item_description',$val_item)?>
                                                        </p>
                                                        <?php
                                                        if (strlen($val_item['item_description'])<59){
                                                            //echo '<div class="dummy-link"></div>';
                                                        }
                                                        ?>

                                                        <?php if ( $disabled_addcart==""):?>
                                                            <div class="center top10 food-price-wrap">
                                                                <p class="xs_menuBtn">
                                                                    <?php echo FunctionsV3::getItemFirstPrice($val_item['prices'],$val_item['discount']) ?>
                                                                </p>
                                                            </div>
                                                        <?php endif;?>
                                                    </a>
                                                </div>
                                                <?php if (!file_exists($path) || empty($val_item['photo'])) {
                                                    $path=FunctionsV3::uploadPath()."/".$merchant_details['logo'];
                                                    if(!file_exists($path) || empty($merchant_details['logo'])){
                                                    }else{
                                                        ?>
                                                        <div class="food_item_right">
                                                            <a href="javascript:;"
                                                            class="mbile rounded3 menu-item <?php echo $val_item['not_available']==2?"item_not_available":''?>"
                                                            rel="<?php echo $val_item['item_id']?>"
                                                            data-single="<?php echo $val_item['single_item']?>"
                                                                <?php echo $atts;?>
                                                            data-category_id="<?php echo $menu_val['category_id']?>"
                                                            data-category_name="<?php echo qTranslate($menu_val['category_name'],'category_name',$menu_val); ?>"
                                                            >
                                                                <img class="merchant_log" sizes="(max-width: 768px) 25vw, (min-width: 768px) 200px" src="<?php echo Yii::app()->request->baseUrl."/upload/".$merchant_details['logo']; ?>" width="100%">
                                                            </a>
                                                        </div>
                                                    <?php
                                                    }
                                                }else{ ?>
                                                <div class="food_item_right">
                                                    <a href="javascript:;"
                                                       class="mbile rounded3 menu-item <?php echo $val_item['not_available']==2?"item_not_available":''?>"
                                                       rel="<?php echo $val_item['item_id']?>"
                                                       data-single="<?php echo $val_item['single_item']?>"
                                                        <?php echo $atts;?>
                                                       data-category_id="<?php echo $menu_val['category_id']?>"
                                                       data-category_name="<?php echo qTranslate($menu_val['category_name'],'category_name',$menu_val); ?>"
                                                    >
                                                        <img sizes="(max-width: 768px) 25vw, (min-width: 768px) 200px" src="<?php echo FunctionsV3::getFoodDefaultImage($val_item['photo'],false); ?>" width="100%">
                                                    </a>
                                                </div>
                                                <?php } ?>
                                                <div style="clear: both"></div>
                                        </div>

                                        <hr>
                                        <div class="clearfix"></div>
                                    </div> <!--box-grey-->
                                </div>
                                <?php endforeach;?>
                            <?php else :?>
                                <div class="col-md-6 border">
                                    <p class="small text-danger"><?php echo t("no item found on this category")?></p>
                                </div>
                            <?php endif;?>
                            <div style="clear:both;"></div>
                        </div>
                        <?php $i++;
                    }
                }
            ?>


        <?php else :?>
        <p class="text-danger"><?php echo t("This restaurant has not published their menu yet.")?></p>
        <?php endif;?>
    <div style="clear:both;"></div>
    </div>
    <div style="clear:both;"></div>
</div>

<?php if(isset(Yii::app()->controller->action->id)):
    if ( Yii::app()->controller->action->id =="menu"): ?>
        <div class="cart-mobile-handle-wrapper">
            <div class="cart-mobile-handle" style="width: 100%;">
<!--                <div class="badge cart_count"></div>-->
                <?php if (isset($_SESSION['kr_client'])){
                    ?>
                    <a class="checkout_btn" href="<?php echo Yii::app()->createUrl('store/cart')?>">
                        <i class="fa fa-shopping-bag"></i><span class="badge cart_count"></span> <span style="margin-left: 25px;margin-right: 20px;">Checkout</span>
                    </a>
                    <?php
                }else{ ?>

                    <a class="checkout_btn" href="<?php echo Yii::app()->createUrl('store/checkout')?>">
                        <i class="fa fa-shopping-bag"></i><span class="badge cart_count"></span> <span style="margin-left: 25px;margin-right: 20px;">Checkout</span>
                    </a>
                <?php } ?>
            </div> <!--cart-mobile-handle !-->
        </div>
    <?php endif;?>
<?php endif;?>


