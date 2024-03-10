jQuery.fn.exists = function () {
  return this.length > 0;
};
jQuery.fn.found = function () {
  return this.length > 0;
};

var data_table;

function dump(data) {
  console.debug(data);
}
function open_fancy_box(params) {
  params += addValidationRequest();
  //   alert(params);
  var URL = ajaxurl + "/" + params;
  $.fancybox({
    maxWidth: 800,
    closeBtn: false,
    autoSize: true,
    padding: 20,
    margin: 20,
    modal: false,
    type: "ajax",
    href: URL,
    openEffect: "elastic",
    closeEffect: "elastic",
  });
}

function close_fb() {
  $.fancybox.close();
}
function addValidationRequest() {
  var params = "";
  params += "&yii_session_token=" + yii_session_token;
  params += "&YII_CSRF_TOKEN=" + YII_CSRF_TOKEN;
  return params;
}
function busy(e) {
  if (e) {
    $("body").css("cursor", "wait");
  } else $("body").css("cursor", "auto");

  if (e) {
    $("body").before('<div class="preloader"></div>');
  } else $(".preloader").remove();
}

jQuery(document).on("click", ".update-link", function () {
  var fields = $("#frm-comment-pop").serialize();
  fields += addValidationRequest();
  $.ajax({
    type: "POST",
    url: ajaxurl + "/updateCustomLink/?currentController=merchantapp",
    data: fields,
    dataType: "json",
    beforeSend: function () {
      dump("before=>");
    },
    success: function () {
      // if (data.code==1){
      //   initTable();
      //   alert("yrdy");
      console.log("test");
      //   if ($(".uk-notify").is(":visible")) {
      //   } else {
      //     if ($("#alert_off").val() == "") {
      //       $("#jquery_jplayer_1").jPlayer("play");
      //     } else {
      //     }
      //     $.UIkit.notify({
      //       message: "Name/Link Upfated Successfully",
      //     });
      close_fb();
      location.reload();
      //   }
      // }
    },
    error: function () {},
  });
});

jQuery(document).on("click", ".remove-link", function () {
  var txt;
  var r = confirm("Are you sure you want to remove this item?");
  if (r == true) {
    var id = $(this).data("id");
    $.ajax({
      type: "GET",
      url: ajaxurl + "/removeCustomLink/?currentController=merchantapp",
      data: "id=" + id + addValidationRequest(),
      dataType: "json",
      beforeSend: function () {
        dump("before=>");
      },
      success: function () {
        // close_fb();
        location.reload();
      },
      error: function () {},
    });
  }
});

jQuery(document).on("click", ".edit-link", function () {
  var id = $(this).data("id");
  //   alert("test " + id);
  var params = "editOrder/?currentController=merchantapp&id=" + id;
  open_fancy_box(params);
});
jQuery(document).ready(function () {
  if ($("#translation-save-wrap").exists()) {
    $("#translation-save-wrap").sticky({ topSpacing: 0 });
  }

  $(".export-language").click(function (e) {
    dump(ajaxurl);
    openExportWindow(100, 100, ajaxurl + "/exportlang");
  });

  if ($("#import-language").exists()) {
    var uploader = new ss.SimpleUpload({
      button: "import-language", // HTML element used as upload button
      url: ajaxurl + "/importLang", // URL of server-side upload handler
      name: "uploadfile", // Parameter name of the uploaded file
      responseType: "json",
      allowedExtensions: ["json"],
      maxSize: 11024, // kilobytes
      onExtError: function (filename, extension) {
        nAlert("Invalid File extennsion", "warning");
      },
      onSizeError: function (filename, fileSize) {
        nAlert("Invalid File size", "warning");
      },
      onSubmit: function (filename, extension) {
        busy(true);
      },
      onComplete: function (filename, response) {
        dump(response);
        busy(false);
        if (response.code == 1) {
          nAlert(response.msg, "success");
          window.location.refresh();
        } else {
          nAlert(response.msg, "warning");
        }
      },
    });
  }

  if ($("#table_list").exists()) {
    initTable();
  }
}); /*end docu*/

function nAlert(msg, alert_type) {
  var n = noty({
    text: msg,
    type: alert_type,
    theme: "relax",
    layout: "topCenter",
    timeout: 2000,
    animation: {
      open: "animated fadeInDown", // Animate.css class names
      close: "animated fadeOut", // Animate.css class names
    },
  });
}

function openExportWindow(h, w, url) {
  leftOffset = screen.width / 2 - w / 2;
  topOffset = screen.height / 2 - h / 2;
  window.open(
    url,
    this.target,
    "left=" +
      leftOffset +
      ",top=" +
      topOffset +
      ",width=" +
      w +
      ",height=" +
      h +
      ",resizable,scrollbars=yes"
  );
}

function initTable() {
  var params = $("#frm_table").serialize();

  data_table = $("#table_list").dataTable({
    iDisplayLength: 20,
    bProcessing: true,
    bServerSide: true,
    sAjaxSource:
      ajaxurl +
      "/" +
      $("#action").val() +
      "/?currentController=admin&" +
      params,
    aaSorting: [[0, "DESC"]],
    sPaginationType: "full_numbers",
    //"bFilter":false,
    bLengthChange: false,
    oLanguage: {
      sProcessing: '<p>Processing.. <i class="fa fa-spinner fa-spin"></i></p>',
    },
    oLanguage: {
      sEmptyTable: js_translation.tablet_1,
      sInfo: js_translation.tablet_2,
      sInfoEmpty: js_translation.tablet_3,
      sInfoFiltered: js_translation.tablet_4,
      sInfoPostFix: "",
      sInfoThousands: ",",
      sLengthMenu: js_translation.tablet_5,
      sLoadingRecords: js_translation.tablet_6,
      sProcessing: js_translation.tablet_7,
      sSearch: js_translation.tablet_8,
      sZeroRecords: js_translation.tablet_9,
      oPaginate: {
        sFirst: js_translation.tablet_10,
        sLast: js_translation.tablet_11,
        sNext: js_translation.tablet_12,
        sPrevious: js_translation.tablet_13,
      },
      oAria: {
        sSortAscending: js_translation.tablet_14,
        sSortDescending: js_translation.tablet_15,
      },
    },
    fnInitComplete: function (oSettings, json) {},
  });
}

jQuery(document).ready(function () {
  if ($("#upload-certificate-dev").exists()) {
    var uploader = new ss.SimpleUpload({
      button: "upload-certificate-dev", // HTML element used as upload button
      url: ajaxurl + "/uploadCertificate", // URL of server-side upload handler
      name: "uploadfile", // Parameter name of the uploaded file
      responseType: "json",
      allowedExtensions: ["pem"],
      maxSize: 11024, // kilobytes
      onExtError: function (filename, extension) {
        nAlert("Invalid File extennsion", "warning");
      },
      onSizeError: function (filename, fileSize) {
        nAlert("Invalid File size", "warning");
      },
      onSubmit: function (filename, extension) {
        busy(true);
      },
      onComplete: function (filename, response) {
        dump(response);
        busy(false);
        if (response.code == 1) {
          nAlert(response.msg, "success");
          $("#mt_ios_push_dev_cer").val(filename);
        } else {
          nAlert(response.msg, "warning");
        }
      },
    });
  }

  if ($("#upload-certificate-prod").exists()) {
    var uploader = new ss.SimpleUpload({
      button: "upload-certificate-prod", // HTML element used as upload button
      url: ajaxurl + "/uploadCertificate", // URL of server-side upload handler
      name: "uploadfile", // Parameter name of the uploaded file
      responseType: "json",
      allowedExtensions: ["pem"],
      maxSize: 11024, // kilobytes
      onExtError: function (filename, extension) {
        nAlert("Invalid File extennsion", "warning");
      },
      onSizeError: function (filename, fileSize) {
        nAlert("Invalid File size", "warning");
      },
      onSubmit: function (filename, extension) {
        busy(true);
      },
      onComplete: function (filename, response) {
        dump(response);
        busy(false);
        if (response.code == 1) {
          nAlert(response.msg, "success");
          $("#mt_ios_push_prod_cer").val(filename);
        } else {
          nAlert(response.msg, "warning");
        }
      },
    });
  }

  if ($(".editor").found()) {
    $(".editor").summernote({
      height: 250,
    });
  }

  if ($(".chosen").exists()) {
    $(".chosen").chosen({
      allow_single_deselect: true,
      width: "100%",
    });
  }

  $(".numeric_only").keyup(function () {
    this.value = this.value.replace(/[^0-9\.]/g, "");
  });
}); /*end docu*/
