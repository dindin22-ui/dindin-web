<div class="uk-width-1">
<a href="<?php echo Yii::app()->request->baseUrl; ?>/merchant/PopupMessages/Do/Add" class="uk-button"><i class="fa fa-plus"></i> <?php echo Yii::t("default","Add New")?></a>
<a href="<?php echo Yii::app()->request->baseUrl; ?>/merchant/PopupMessages" class="uk-button"><i class="fa fa-list"></i> <?php echo Yii::t("default","List")?></a>
<!--<a href="<?php echo Yii::app()->request->baseUrl; ?>/merchant/PopupMessages/Do/Sort" class="uk-button"><i class="fa fa-sort-alpha-asc"></i> <?php echo Yii::t("default","Sort")?></a>-->
</div>

<div class="spacer"></div>

<div id="error-message-wrapper"></div>

<form class="uk-form uk-form-horizontal forms popup_message" id="forms">
<?php echo CHtml::hiddenField('action','addPopupMessage')?>
<?php echo CHtml::hiddenField('id',isset($_GET['id'])?$_GET['id']:"");?>
<?php if (!isset($_GET['id'])):?>
<?php echo CHtml::hiddenField("redirect",Yii::app()->request->baseUrl."/merchant/PopupMessages/Do/Add")?>
<?php endif;?>

<?php 
if (isset($_GET['id'])){
	if (!$data=Yii::app()->functions->getPopUpMessage($_GET['id'])){
		echo "<div class=\"uk-alert uk-alert-danger\">".
		Yii::t("default","Sorry but we cannot find what your are looking for.")."</div>";
		return ;
	}	
}
?>                                 

<div class="uk-form-row">

<div class="uk-form-row">
<label class="uk-form-label"><?php echo Yii::t("default","Title")?></label>
  <?php echo CHtml::textField('title',
  isset($data['title'])?stripslashes($data['title']):""
  ,array(
  'class'=>'uk-form-width-large',
  'data-validation'=>"required"
  ))?>  
</div>


<div style="height:20px;"></div>


<div class="uk-form-row">
  <label class="uk-form-label"><?php echo t("Popup Message")?></label>
  <div style="clear:both;height:20px;"></div>
  <!-- Create the editor container -->
  <textarea id="texteditor" name="message">
    <?php echo $data['message']; ?>
  </textarea>
</div>
<div class="uk-form-row">
  <label class="uk-form-label"><?php echo t("Open Every Time")?></label>
  <?php 
  echo CHtml::checkbox('open_every_time',
  $data['open_every_time']==1?true:false
  ,array(
    'value'=>1,
    'class'=>"icheck"
  ))
  ?> 
</div>

<div class="uk-form-row">
  <label class="uk-form-label"><?php echo t("Schedule")?></label>
  <?php 
  echo CHtml::checkbox('scheduled_message',
  $data['scheduled_message']==1?true:false
  ,array(
    'value'=>1,
    'class'=>"icheck"
  ))
  ?> 
</div>
<?php
$scheduled_time = (isset($data['schedule_time']) && $data['schedule_time'] != '0000-00-00 00:00:00') ?date('Y-m-d',strtotime($data['schedule_time'])):'';
?>
<div class="uk-form-row">
  <label class="uk-form-label"><?php echo Yii::t("default","Time post")?>:</label>
  <?php echo CHtml::textField('schedule_time',$scheduled_time,array(
  'class'=>"j_date_normal small_date"
  ));?>
</div>

<div class="uk-form-row">
  <label class="uk-form-label"><?php echo t("Status")?></label>
  <?php echo CHtml::dropDownList('status',
  isset($data['status'])?$data['status']:"",
  (array)statusMessageList(),          
  array(
  'class'=>'uk-form-width-large',
  'data-validation'=>"required"
  ))?>
</div>

<div class="uk-form-row">
<label class="uk-form-label"></label>
<input type="button" onClick="changeMessage()" value="<?php echo Yii::t("default","Save")?>" class="uk-button uk-form-width-medium uk-button-success">
</div>

</form>
 
<script>
tinymce.init({
        selector: '#texteditor', 
	    theme: "modern",
    	height: 250,
    	relative_urls: false,
    	remove_script_host: false, 
    	convert_urls: true,
    	plugins: [
    		"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
    		"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
    		"save table contextmenu directionality emoticons template paste textcolor"
    	],
    	toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | preview media fullpage | forecolor backcolor",
        branding: false,
      menubar: 'insert format table tools help',
        statusbar: true,
});
// Applying the specified format
tinymce.activeEditor.formatter.apply('custom_format');

  function changeMessage(){
    var message = tinymce.get("texteditor").getContent();
    $('#texteditor').val(message);
    $('.popup_message').submit();
  }
</script>