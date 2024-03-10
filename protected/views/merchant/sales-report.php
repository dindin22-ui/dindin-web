<?php 
$res = FunctionsV3::SumOfTotal(); 
$enable_report = FunctionsV3::enableReports();
?>

<form id="frm_table_list" method="POST" class="report uk-form uk-form-horizontal" >

<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","Total")?></label>
  $<?php echo CHtml::textField('total',$res["total"]
  ,array(
  'class'=>'uk-form-width-medium ',
  'data-id'=>'total',
  'readonly'=>1,
  )) ?>
</div>
<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","Tips")?></label>
  $<?php echo CHtml::textField('tips',$res["tips"]
  ,array(
  'class'=>'uk-form-width-medium ',
  'data-id'=>'tips',
  'readonly'=>1,
  )) ?>
</div>
<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","Total - Fee W/Comm")?></label>
  $<?php echo CHtml::textField('totalMinusFeeComm', $res["total"]-$res["commission"] 
  ,array(
  'class'=>'uk-form-width-medium ',
  'data-id'=>'total-minusFeeComm',
  'readonly'=>1,
  ))?>
</div>

<?php 
$order_stats=Yii::app()->functions->orderStatusList(false);    
?>

<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","Start Date")?></label>
  <?php echo CHtml::hiddenField('start_date')?>
  <?php echo CHtml::textField('start_date1',''  
  ,array(
  'class'=>'uk-form-width-large j_date',
  'data-id'=>'start_date',
  ))?>
</div>

<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","End Date")?></label>
  <?php echo CHtml::hiddenField('end_date')?>
  <?php echo CHtml::textField('end_date1',''  
  ,array(
  'class'=>'uk-form-width-large j_date',
  'data-id'=>'end_date',
  ))?>
</div>


<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","Order Status")?></label>
  <?php echo CHtml::dropDownList('stats_id[]',array(4),(array)$order_stats,array(
  'class'=>"chosen uk-form-width-large",
  'multiple'=>true
  ))?>
</div>


<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","Email monthly payed report?")?></label>
  <?php 
      if(!isset($enable_report)){
          $enable_report=''; }
      echo CHtml::checkBox('enable_report', $enable_report==1?true:false ,
        array(  'class'=>"enable_report",'onclick'=>"enableReport(this)", 'value'=>1 ))
  ?>
</div>

<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","Email Address")?></label>
  <?php 
      if(!isset($data['email_address'])){
          $data['email_address']='';
        }
  echo CHtml::textField('email_address', FunctionsV3::reportEmails(),
    array(
  'class'=>'uk-form-width-large email_address',
  'data-id'=>'email_address',
  ))?>
  <input type="button" class="uk-button uk-form-width-extra-small uk-button-success" value="<?php echo t("Save")?>" onclick="emailAddress('email_address');">  
</div>


<div class="uk-form-row">
  <label class="uk-form-label">&nbsp;</label>
  <input type="button" class="uk-button uk-form-width-medium uk-button-success" value="<?php echo t("Search")?>" onclick="sales_summary_reload();">  
  <a href="javascript:;" rel="sales-report" class="export_btn uk-button"><?php echo Yii::t("default","Export")?></a>
  <a href="javascript:;" rel="sales-report" class="download_btn uk-button"><?php echo Yii::t("default","Download Report")?></a>
</div>  

<div style="height:20px;"></div>

<input type="hidden" name="action" id="action" value="salesReport">
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
            <th width="3%"><?php echo Yii::t('default',"Tip")?></th>
            <th width="3%"><?php echo Yii::t('default',"Convenience Fee")?></th>
            <th width="3%"><?php echo Yii::t('default',"Total W/Tax")?></th>
            <th width="3%"><?php echo Yii::t('default',"Status")?></th>
            <th width="3%"><?php echo Yii::t('default',"Total - Fee W/Comm")?></th>
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












<script>

</script>




