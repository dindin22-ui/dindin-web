
<div class="uk-width-1">
<a href="<?php echo Yii::app()->request->baseUrl; ?>/admin/AdminPopupMessages/Do/Add" class="uk-button"><i class="fa fa-plus"></i> <?php echo Yii::t("default","Add New")?></a>
<a href="<?php echo Yii::app()->request->baseUrl; ?>/admin/AdminPopupMessages" class="uk-button"><i class="fa fa-list"></i> <?php echo Yii::t("default","List")?></a>
<!--<a href="<?php echo Yii::app()->request->baseUrl; ?>/admin/AdminPopupMessages/Do/Sort" class="uk-button"><i class="fa fa-sort-alpha-asc"></i> <?php echo Yii::t("default","Sort")?></a>-->
</div>

<form id="frm_table_list" method="POST" >
<input type="hidden" name="action" id="action" value="AdminPopupMessages">
<input type="hidden" name="tbl" id="tbl" value="popup_messages">
<input type="hidden" name="clear_tbl"  id="clear_tbl" value="clear_tbl">
<input type="hidden" name="whereid"  id="whereid" value="id">
<input type="hidden" name="slug" id="slug" value="AdminPopupMessages/Do/Add">
<table id="table_list" class="uk-table uk-table-hover uk-table-striped uk-table-condensed">
  <caption>Popup Messages</caption>
   <thead>
        <tr>
            <th><input type="checkbox" id="chk_all" class="chk_all"></th>
            <th><?php echo Yii::t('default',"Title")?></th>
            <th><?php echo Yii::t('default',"Status")?></th>  
            <th><?php echo Yii::t('default',"Date")?></th>
        </tr>
    </thead>
    <tbody> 
    </tbody>
</table>
<div class="clear"></div>
</form>