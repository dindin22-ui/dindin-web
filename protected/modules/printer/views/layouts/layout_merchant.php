<?php $this->renderPartial('/layouts/header');?>

<body>

<a style="padding:10px;display:table;"
href="<?php echo Yii::app()->createUrl('/merchant') ?>"><i class="ion-ios-arrow-thin-left"></i> <?php echo tp("Back")?></a>

<div class="container" id="main-wrapper">
  <div class="panel panel-default">
     <div class="panel-heading"><?php echo tp("Karenderia Printer modules for merchant")?></div> 
     
     <?php $this->renderPartial('/layouts/menu_merchant');?>
     
     <div class="pad10">
     <?php echo $content?>  
     </div>
    
   </div> <!--panel-->
</div> <!--container-->
</body>

<?php $this->renderPartial('/layouts/footer');?>