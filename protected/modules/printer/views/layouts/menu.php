<?php
$menu =  array(  		    		    
    'activeCssClass'=>'active', 
    'encodeLabel'=>false,
    'items'=>array(
        array('visible'=>true,'label'=>'<i class="ion-gear-a"></i>&nbsp; '.PrinterClass::t("General Settings"),
        'url'=>array('/printer/index'),'linkOptions'=>array()),
        
        array('visible'=>true,'label'=>'<i class="ion-ios-paper-outline"></i>&nbsp; '.PrinterClass::t('Template'),
        'url'=>array('/printer/index/template'),'linkOptions'=>array()),               
        
        
        array('visible'=>true,'label'=>'<i class="ion-ios-printer-outline"></i>&nbsp; '.PrinterClass::t('Printers'),
        'url'=>array('/printer/index/printer'),'linkOptions'=>array()),               
        
        array('visible'=>true,'label'=>'<i class="ion-ios-list-outline"></i>&nbsp; '.PrinterClass::t('Print Records'),
        'url'=>array('/printer/index/logs'),'linkOptions'=>array()),               
        
        array('visible'=>true,'label'=>'<i class="ion-clock"></i>&nbsp; '.PrinterClass::t('Cron Jobs'),
        'url'=>array('/printer/index/cronjobs'),'linkOptions'=>array()),               
        
        array('visible'=>true,'label'=>'<i class="ion-ios-cloud-upload-outline"></i>&nbsp; '.PrinterClass::t('Update DB Tables'),
        'url'=>array('/printer/update'),'linkOptions'=>array(
         'target'=>"_blank"
        )),               
        
     )   
);       
?>
<div class="menu">
<?php $this->widget('zii.widgets.CMenu', $menu);?>
</div>