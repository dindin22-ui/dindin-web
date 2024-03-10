
<?php
unset($_SESSION['pts_earn']);
unset($_SESSION['pts_redeem_amt']);
unset($_SESSION['kr_item']);
unset($_SESSION['kr_merchant_id']);
unset($_SESSION['voucher_code']);
unset($_SESSION['less_voucher']);
unset($_SESSION['shipping_fee']);


$this->renderPartial('/front/banner-receipt',array(
   'h1'=>t("Thank You"),
   'sub_text'=>t("We are sorry to let you know that your order was not processed due to high demand, your card was not charged. Please try again later"),
   'href'=>"<?php echo FunctionsV3::fixedLink($merchant_website)?>"
));
$data = Yii::app()->functions->getOrder($_GET['id']);
?>



<div class="sections section-grey2 section-receipt">
    <div class="container">

        <div class="inner" id="receipt-content">


            <center>	
                <h3 class="text-danger"></h3>
                <a class="weblink" href="<?php echo Yii::app()->request->baseUrl . "/menu-" . clearString($data['restaurant_slug']); ?>">Return to store
                </a>

            </center>
        </div> <!--box-grey-->

    </div> <!--inner-->


</div> <!--container-->

