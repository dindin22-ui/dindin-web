<?php
$menu =  array(  		    		    
    'activeCssClass'=>'active', 
    'encodeLabel'=>false,
    'items'=>array(
        array('visible'=>true,'label'=>'<i class="ion-gear-a"></i>&nbsp; '.PrinterClass::t("General Settings"),
        'url'=>array('/printer/merchant_panel/settings'),'linkOptions'=>array()),
        
        array('visible'=>true,'label'=>'<i class="ion-ios-paper-outline"></i>&nbsp; '.PrinterClass::t('Template'),
        'url'=>array('/printer/merchant_panel/template'),'linkOptions'=>array()),               
        
        
        array('visible'=>true,'label'=>'<i class="ion-ios-printer-outline"></i>&nbsp; '.PrinterClass::t('Printer'),
        'url'=>array('/printer/merchant_panel/printer'),'linkOptions'=>array()),               
        
        array('visible'=>true,'label'=>'<i class="ion-ios-list-outline"></i>&nbsp; '.PrinterClass::t('HCC Printer'),
        'url'=>array('/printer/merchant_panel/hccprinter'),'linkOptions'=>array()),   

        array('visible'=>true,'label'=>'<i class="ion-ios-list-outline"></i>&nbsp; '.PrinterClass::t('Print Records'),
        'url'=>array('/printer/merchant_panel/logs'),'linkOptions'=>array()),               
                
     )   
);       
?>
<div class="menu">
<?php $this->widget('zii.widgets.CMenu', $menu);?>
</div>