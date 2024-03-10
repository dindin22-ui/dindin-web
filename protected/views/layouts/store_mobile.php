<link href='http://fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300ita‌​lic,400italic,500,500italic,700,700italic,900italic,900)'>
<style>
    .container_desktop #bg_img {
        top: 0;
        background-image: url(<?php echo Yii::app()->request->baseUrl ?>/assets/images/shape.png);
        background-repeat: no-repeat;
        background-size: 800px 100%;
        background-position: right 0px top 0px;
        padding-top: 30px;
        padding-bottom: 50px;
        height: 100vh;
    }
    .fw-bold{
        font-weight: bold;
    }
    .container_desktop .heading_top p
    {
        font-family: 'Roboto', sans-serif;
        font-weight:900;
        font-size:43px;
        color:#2a395a;
    }
    .container_desktop .heading_top p span
    {
        font-family: 'Roboto', sans-serif;
        font-weight:900;
        font-size:43px;
        color:#f77e47;
    }
    .container_desktop .heading_main .h2
    {
        font-family: 'Roboto', sans-serif;
        font-weight:900;
        font-size:35px;
        color:#424242;
    }
    .container_desktop .label_main p
    {
        font-family: 'Roboto', sans-serif;
        font-weight:normal;
        font-size:18px;
        color:#424242;
    }
    .container_desktop .btn_bottom
    {
        background-color:#fb7f16;
        border:solid 1px #fb7f16;
        font-size:22px;
    }
    .phone_img
    {
        background-image:url(<?php echo Yii::app()->request->baseUrl ?>assets/images/mobile_img.png);
        background-repeat:no-repeat;
        background-size:cover;
        min-height: 600px;
        width: 350px;
        margin:auto;
        -webkit-box-shadow: 0px 0px 28px 0px rgba(184,184,184,1);
        -moz-box-shadow: 0px 0px 28px 0px rgba(184,184,184,1);
        box-shadow: 0px 0px 28px 0px rgba(184,184,184,1);
        border-radius:30px !important;
    }
    .container_desktop .phone_img_iframe {
        height: 700px !important;
        width: 450px;
        border-radius: 15px;
        padding: 0;
        border: 7px solid #dadde3;
        margin-left: 7px;
        margin-top: 1px;
    }
    * {
        padding: 0%;
        margin: 0%;
        box-sizing: border-box;
    }

    @media(min-width: 2101px) and (max-width: 2560px) {
        #bg_img {

            background-size: 1600px 1000px;
            padding-top:40px;

        }
        .heading_main .h2 {
            font-size: 100px;
        }
        .heading_top p {

            font-size: 80px;
        }
        .label_main p {

            font-size: 36px;
        }
        .phone_img {

            min-height: 70vh;
            width: 600px;
        }
        .btn_bottom {
            background-color: #fb7f16;
            border: solid 1px #fb7f16;
            font-size: 32px;
        }
    }
    @media(min-width: 1700px) and (max-width: 2100px) {
        #bg_img {
            background-size: 1200px 800px;

        }

        .heading_main .h2 {
            font-size: 78px;
        }
        .heading_top p {

            font-size: 60px;
        }
        .label_main p {

            font-size: 30px;
        }
        .btn_bottom {
            background-color: #fb7f16;
            border: solid 1px #fb7f16;
            font-size: 32px;
        }
    }
    @media(min-width: 768px) and (max-width: 991px) {
        #bg_img {
            background-size: 500px 600px;
            padding-top: 0px;
        }
        .container_desktop .phone_img_iframe {
            height: 600px !important;
            width: 375px;
            border-radius: 15px;
            padding: 0;
            border: 7px solid #dadde3;
            margin-left: 7px;
            margin-top: 1px;
        }
        .container_desktop .btn_bottom {
            background-color: #fb7f16;
            border: solid 1px #fb7f16;
            font-size: 16px;
        }
        .container_desktop .heading_main .h2 {
            font-family: 'Roboto', sans-serif;
            font-weight: 900;
            font-size: 25px;
            color: #424242;
        }
        .heading_main .h2 {
            font-size: 39px;
        }
        .btn_bottom
        {
            font-size:14px ;
        }
    }
    .opening_hours_desktop ul{
        padding: 0;
        margin: 0;
    }
    .opening_hours_desktop ul li{
        list-style: none;
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>
<div id="bg_img" class="row">
    <?php
    $slug = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if($slug != ''){
        $merchant_name = $_SESSION['kr_restaurant_name']." - Online Ordering";
        $descrtiption = 'At <span class="fw-bold">'.$_SESSION['kr_restaurant_name'].'</span> we use dindin as our online ordering platform which will provide you with an easy way to get the food you love.';
    }else{
        $merchant_name = 'Welcome to DinDin';
        $descrtiption = 'Search for hundred of restaurants near you and order you favorite meal for pickup and delivery.';
    }
    ?>
    <div class="container pt-5" style="margin-top:100px">
        <div class="row">
            <div class="col-md-7 col-sm-6">
                <div class="heading_top">
                    <img src="<?php echo Yii::app()->request->baseUrl ?>/assets/images/dindin-logo.png" style="width: 220px;margin-left: -20px;"/>
                </div>
                <div class="pt-1 heading_main">
                    <p class="h2"><?php echo $merchant_name; ?></p>
                </div>
                <div class="pt-3 label_main">
                    <p><?php echo $descrtiption; ?></p>
                </div>
                <div class="d-grid pt-2">
                    <button class="btn btn-warning text-white btn_bottom">Place Your Order in the interactive Phone <img style="width: 20px;" src="<?php echo Yii::app()->request->baseUrl; ?>assets/images/hand.png" /></button>
                </div>
                <?php if($slug != '' && isset($_SESSION['kr_restaurant_opening_hours'])){ ?>
                <div class="d-grid pt-2 opening_hours_desktop">
                    <h3>Opening Hours</h3>
                    <?php $opening_hours = ($_SESSION['kr_restaurant_opening_hours']); ?>
                    <ul>
                        <?php foreach ($opening_hours as $key=>$value){ ?>
                            <li>  <b><?php echo ucfirst($key).": </b>". $value['hours']; ?> </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
            </div>
            <div class="col-md-5 col-sm-6">
                <iframe src="<?php echo  $_SERVER['REQUEST_URI'] ?>" style="" class="phone_img_iframe" title="<?php echo $merchant_name; ?>"></iframe>
            </div>
        </div>
    </div>
</div>