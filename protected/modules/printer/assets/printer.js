var ajax_request;
var data_table;

jQuery.fn.exists = function(){return this.length>0;}

function dump(data)
{
	console.debug(data);
}

function busy(e)
{
    if (e) {
        $('body').css('cursor', 'wait');	
    } else $('body').css('cursor', 'auto');        
    
    if (e) {
    	$("body").before("<div class=\"preloader\"></div>");
    } else $(".preloader").remove();
    
}

function empty(data)
{
	if (typeof data === "undefined" || data==null || data=="" ) { 
		return true;
	}
	return false;
}

function noty_msg(msg)
{
	var n = noty({
		 text: msg,
		 type        : "warning" ,		 
		 theme       : 'relax',
		 layout      : 'topCenter',		 
		 timeout:3500,
		 animation: {
	        open: 'animated fadeInDown', // Animate.css class names
	        close: 'animated fadeOut', // Animate.css class names	        
	    }
	});
}

function noty_msg_success(msg)
{
	var n = noty({
		 text: msg,
		 type        : "success" ,		 
		 theme       : 'relax',
		 layout      : 'topCenter',		 
		 timeout:3500,
		 animation: {
	        open: 'animated fadeInDown', // Animate.css class names
	        close: 'animated fadeOut', // Animate.css class names	        
	    }
	});	  
}

jQuery(document).ready(function() {
	
   if ( $("#table_list").exists() ) {
       initTable();
   }
   
   $( document ).on( "click", ".delete_printer", function() {
   	   callAjax("delete_printer",'id='+ $(this).data("id"),'');
   });
   
   if ( $("#msg_alert").exists() ){
   	  noty_msg_success( $("#msg_alert").val() );
   }
      
   $( document ).on( "click", ".get_printer_status", function() {
   	   callAjax("get_printer_status",'id='+ $(this).data("id"),'');
   });
   
   $( document ).on( "click", ".test_print", function() {
   	   callAjax("test_print",'id='+ $(this).data("id"),'');
   });
   
   $( document ).on( "click", ".load_template", function() {
   	   callAjax("load_template",'id='+ $(this).data("id") + "&target=" + $(this).data("target") ,'');
   });
   
   $( document ).on( "click", ".mt_print", function() {
       callAjax("mt_print",'');
   });
      
   $( document ).on( "click", ".mt_printer_check_status", function() {
       callAjax("mt_printer_check_status",'');
   });
   
   $( document ).on( "click", ".mt_delete_printer", function() {
   	   var a = confirm(js_translation.are_you_sure+"?");
   	   if(a){
          callAjax("mt_delete_printer",'');
   	   }
   });   
   
}); /*end docu*/

function initTable()
{		
	var params=$("#frm_table").serialize();	
	
	data_table = $('#table_list').dataTable({		
		   "iDisplayLength": 20,
	       "bProcessing": true, 	       
	       "bServerSide": true,
	       //"sAjaxSource": ajaxurl+"/"+ $("#action").val()+"/?currentController=admin",	       
	       "sAjaxSource": ajaxurl+"/"+ $("#action").val()+"/?"+params,	       
	       "aaSorting": [[ 0, "DESC" ]],	       
           "sPaginationType": "full_numbers",   
           //"bFilter":false,            
           "bLengthChange": false,
	       "oLanguage":{	       	 
	       	 //"sProcessing": "<p>Processing.. <i class=\"fa fa-spinner fa-spin\"></i></p>"
	       	   "sEmptyTable":    js_translation.tablet_1,
			    "sInfo":           js_translation.tablet_2,
			    "sInfoEmpty":      js_translation.tablet_3,
			    "sInfoFiltered":   js_translation.tablet_4,
			    "sInfoPostFix":    "",
			    "sInfoThousands":  ",",
			    "sLengthMenu":     js_translation.tablet_5,
			    "sLoadingRecords": js_translation.tablet_6,
			    "sProcessing":     js_translation.tablet_7,
			    "sSearch":         js_translation.tablet_8,
			    "sZeroRecords":    js_translation.tablet_9,
			    "oPaginate": {
			        "sFirst":    js_translation.tablet_10,
			        "sLast":     js_translation.tablet_11,
			        "sNext":     js_translation.tablet_12,
			        "sPrevious": js_translation.tablet_13
			    },
			    "oAria": {
			        "sSortAscending":  js_translation.tablet_14,
			        "sSortDescending": js_translation.tablet_15
			    }
	       },	       
	       "fnInitComplete": function(oSettings, json) {	       	  		      
		   }		
	});	   	
}

function tableReload()
{	
	data_table.fnReloadAjax(); 
}

function callAjax(action,params,button)
{
	dump(action);
	dump(params);
	dump(button);
	
	if ( !empty(button) ){
		button.css({ 'pointer-events' : 'none' });
	}
	
	params+= addValidationRequest();
	
	ajax_request = $.ajax({
		url: ajaxurl+"/"+action, 
		data: params,
		type: 'post',           		
		dataType: 'json',
		timeout: 7000,		
	 beforeSend: function() {
	 	dump("before=>");
	 	dump( ajax_request );
	 	if(ajax_request != null) {
	 	   ajax_request.abort();	 	   
	 	   busy(false);	 	   
	 	   if ( !empty(button) ){
	 	      button.css({ 'pointer-events' : 'auto' });
	 	   }
	 	} else {
	 	   busy(true);	 	  
	 	}
	 },
	 complete: function(data) {					
		ajax_request= (function () { return; })();
		dump( 'Completed');
		dump(ajax_request);
		busy(false);	
		if ( !empty(button) ){
		   button.css({ 'pointer-events' : 'auto' });
		}
	 },
	 success: function (data) {
	 	dump(data);
	 	if (data.code==1){
	 		switch (action)
	 		{
	 			
	 			case "delete_printer":
	 			case "get_printer_status":
	 			noty_msg_success(data.msg);
	 			tableReload();
	 			break;
	 			
	 			case "load_template":
	 			  $("#"+ data.msg ).val( data.details );
	 			break;
	 			
	 			default:
	 			noty_msg_success(data.msg);
	 			break;
	 		}
	 	} else {
	 		//failed 
	 		noty_msg(data.msg);
	 	}
	 },
	 error: function (request,error) {	    
	 	busy(false);
	  }
    });    
}


function addValidationRequest()
{
	var params='';		
	params+="&YII_CSRF_TOKEN="+YII_CSRF_TOKEN;
	return params;
}			