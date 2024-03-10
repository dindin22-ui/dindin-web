
<div class="text-right">
<a class="btn btn-default" href="<?php echo Yii::app()->createUrl('printer/index/add_printer')?>"><?php echo PrinterClass::t("Add printer")?></a>
</div>

<div class="pad10"></div>

<form id="frm_table" class="frm_table">
<?php echo CHtml::hiddenField('action','printer_list')?>
</form>
<table id="table_list" class="table table-hover dataTable no-footer">
  <thead>
    <tr>
     <th width="5%"><?php echo PrinterClass::t("ID")?></th>
     <th width="15%"><?php echo PrinterClass::t("Printer number")?></th>
     <th width="15%"><?php echo PrinterClass::t("Name")?></th>
     <th width="10%"><?php echo PrinterClass::t("Default")?></th>
     <th width="15%"><?php echo PrinterClass::t("Status")?></th>
     <th width="10%"><?php echo PrinterClass::t("Action")?></th>
    </tr>
  </thead>
</table>