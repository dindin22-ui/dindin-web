
<div class="pad10"></div>

<form id="frm_table" class="frm_table">
<?php echo CHtml::hiddenField('action','printer_logs')?>
</form>
<table id="table_list" class="table table-hover dataTable no-footer">
  <thead>
    <tr>
     <th width="5%"><?php echo PrinterClass::t("ID")?></th>
     <th width="15%"><?php echo PrinterClass::t("Printer number")?></th>
     <th width="15%"><?php echo PrinterClass::t("Content")?></th>     
     <th width="10%"><?php echo PrinterClass::t("Status")?></th>
     <th width="10%"><?php echo PrinterClass::t("Print Job ID")?></th>
     <th width="15%"><?php echo PrinterClass::t("Query Status")?></th>
     <th width="15%"><?php echo PrinterClass::t("Date Created")?></th>     
    </tr>
  </thead>
</table>