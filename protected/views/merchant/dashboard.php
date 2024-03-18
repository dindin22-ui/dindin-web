<?php if ( Yii::app()->functions->hasMerchantAccess("DashBoard")):
$merchant_id = Yii::app()->functions->getMerchantID();
$store_start_date=getOption($merchant_id,'store_start_date');   
$store_close_date=getOption($merchant_id,'store_close_date');  
$time = time();
?>
<div style="clear: both;">
    <?php
    $resum_style="";
    $pause_style="";
    $resum_style_block = "";
    if($time < $store_close_date && $time > $store_start_date){	
        $resum_style="display:inline-block;"; 
        $pause_style = "display:none;";
        $resum_style_block = "display:block;";
    }else{
        $pause_style ="display:inline-block;"; 
        $resum_style = "display:none;";
        $resum_style_block = "display:none;";
    }  
    ?>
    <a style="<?php echo $pause_style; ?>" class="uk-button pause-order" href="javascript:">Pause Orders </a>
    <a  style="<?php echo $resum_style; ?>" data-id="<?php echo $merchant_id; ?>" class="uk-button resume-order" href="javascript:">Resume Orders </a>
    <h4 style="<?php echo $resum_style_block; ?>" data-id="<?php echo $merchant_id; ?>" class="resume-order-note">
        The merchant will reopen on <?php echo date('m-d-Y',$store_close_date); ?> at <?php echo date('H:i a',$store_close_date); ?>
</h4>
</div>x


<form id="frm_table_list" method="POST" class="report uk-form uk-form-horizontal merchant-dashboard" >
<h3><?php echo Yii::t("default","New Order List For Today")?> 
<?php 
/*$date= date('F d, Y');
$date=Yii::app()->functions->translateDate($date);
echo $date;*/
echo FormatDateTime(date('c'),false);
?>
</h3>

<input type="hidden" name="action" id="action" value="recentOrder">
<input type="hidden" name="tbl" id="tbl" value="item">
<table id="table_list" class="uk-table uk-table-hover uk-table-striped uk-table-condensed">
  <!--<caption>Merchant List</caption>-->
   <thead>
        <tr> 
            <th width="2%"><?php echo Yii::t('default',"Ref#")?></th>
            <th width="6%"><?php echo Yii::t('default',"Name")?></th>
            <th width="6%"><?php echo Yii::t('default',"Contact#")?></th>
            <th width="3%"><?php echo Yii::t('default',"Item")?></th>            
            <th width="3%"><?php echo Yii::t('default',"TransType")?></th>
            <th width="3%"><?php echo Yii::t('default',"Payment Type")?></th>
            <th width="3%"><?php echo Yii::t('default',"Total")?></th>
            <th width="3%"><?php echo Yii::t('default',"Tax")?></th>
            <th width="3%"><?php echo Yii::t('default',"Total W/Tax")?></th>
            <th width="3%"><?php echo Yii::t('default',"Status")?></th>
            <th width="3%"><?php echo Yii::t('default',"Platform")?></th>
            <th width="3%"><?php echo Yii::t('default',"Date")?></th>
            <th width="3%"></th>
        </tr>
    </thead>
    <tbody>    
    </tbody>
</table>
<div class="clear"></div>
</form>

<?php if($cancel_order_enabled==1):?>
<hr style="margin-top:20px;margin-bottom:20px;"></hr>

<div class="request_cancel_order_wrap">
<h3><?php echo t("New request cancel order")?></h3>
<form id="frm_table_list2" method="POST" class="report uk-form uk-form-horizontal merchant-dashboard" >
<input type="hidden" name="action" id="action" value="requestCancelOrderList">
<input type="hidden" name="tbl" id="tbl" value="item">
<table id="table_list2" class="uk-table uk-table-hover uk-table-striped uk-table-condensed">  
   <thead>
        <tr> 
            <th width="2%"><?php echo Yii::t('default',"Ref#")?></th>
            <th width="6%"><?php echo Yii::t('default',"Name")?></th>
            <th width="6%"><?php echo Yii::t('default',"Contact#")?></th>
            <th width="3%"><?php echo Yii::t('default',"Item")?></th>            
            <th width="3%"><?php echo Yii::t('default',"TransType")?></th>
            <th width="3%"><?php echo Yii::t('default',"Payment Type")?></th>
            <th width="3%"><?php echo Yii::t('default',"Total")?></th>
            <th width="3%"><?php echo Yii::t('default',"Tax")?></th>
            <th width="3%"><?php echo Yii::t('default',"Total W/Tax")?></th>
            <th width="3%"><?php echo Yii::t('default',"Status")?></th>
            <th width="3%"><?php echo Yii::t('default',"Platform")?></th>
            <th width="3%"><?php echo Yii::t('default',"Date")?></th>
            <th width="3%"></th>
        </tr>
    </thead>
    <tbody>    
    </tbody>
</table>
</form>
</div>
<?php endif;?>

<hr style="margin-top:20px;margin-bottom:40px;"></hr>


<div id="total_sales_chart" class="chart"></div>
<div id="total_sales_chart_by_item" class="chart"></div>

<?php else :?>
<h2><?php echo Yii::t("default","Welcome")?></h2>
<?php endif; ?>