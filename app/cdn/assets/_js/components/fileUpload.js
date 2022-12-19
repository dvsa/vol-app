var OLCS = OLCS || {};

/**
 * File upload
 */

OLCS.fileUpload = (function(document, $, undefined) {

  "use strict";

  return function init(options) {

    var F = OLCS.formHelper;
    var asyncUploads          = true;
    var containerSelector     = ".file-uploader";
    var removeSelector        = ".file__remove";
    var mainBodySelector      = ".js-body";
    var submitSelector        = ".js-upload";
    var inputSelector         = ".attach-action__input";
    var attachButtonSelector  = ".attach-action__label";
    var numUploaded           = 0;
    var totalUploads          = 0;
    var MULTI_UPLOAD_DELAY    = 1000;
    var uploadInProgress      = false;

    if (window.FormData === undefined) {
      OLCS.logger.warn("XHR form uploads not supported in this browser", "fileUpload");
      asyncUploads = false;
    }

    function disableElements() {
      uploadInProgress = true;
      $(removeSelector).addClass("govuk-button--disabled");
      var pageActions = $(".actions-container").last().children();
      $(attachButtonSelector).addClass("govuk-button--disabled");
      $(pageActions, inputSelector).attr({
        "disabled"    : true,
        "aria-hidden" : true
      });

    }

    function enableElements() {
      $(removeSelector).removeClass("govuk-button--disabled");
      uploadInProgress = false;
      $(attachButtonSelector).removeClass("govuk-button--disabled");
      $(".actions-container").last().children().removeAttr("disabled", "aria-hidden");
    }

    function handleResponse(response, index) {
      var originalUploader = ".file-uploader:eq("+index+")";
      var updatedUploader  = $(response).find(originalUploader);
      F.render(originalUploader, updatedUploader[0].innerHTML);
    }

    var deleteResponse = OLCS.normaliseResponse(function(response) {
      if (OLCS.modal.isVisible()) {
        OLCS.modal.updateBody(response.body);
      } else {
        F.render(mainBodySelector, response.body);
      }
    });

    function addFileList(container){
      if($(container).find(".js-upload-list").length > 0){
        return;
      }
      var newHtml = ["<div class=\"help__text\">",
        "<h3 class=\"file__heading\"></h3>",
        "<ul class=\"js-upload-list\"></ul>" ,
        "</div>"].join("\n");

      $(container).append(newHtml);
    }

    function upload(form, container, index, file) {
      var fd             = new FormData();
      var xhr            = new XMLHttpRequest();
      var kbSize         = Math.round(file.size / 1024);
      var name           = $(container).data("group");
      var containerIndex = $(container).index(containerSelector);
      var sectionIdVal   = form.find("[name='sectionId']").val();
      var security       = document.getElementById("security");
      var url            = form.attr("action") ? form.attr("action") : window.location.pathname;
      uploadInProgress = true;

      OLCS.logger.debug("Uploading file " + file.name + " (" + file.type + ")", "fileUpload");


      disableElements();
      addFileList(container);
      $(container).find(".js-upload-list").append([
        "<li class=file data-upload-index=" + index + ">",
          "<span class=file__preloader></span>",
          "<a href=#>",
            file.name,
          "</a>",
          "<span>",
            kbSize + "KB",
          "</span>",
          "<span class='uploading'>Uploading &hellip;</span>",
        "</li>"
      ].join("\n"));

      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
          numUploaded ++;
          OLCS.logger.debug(
            "File " + numUploaded + "/" + totalUploads + " complete",
            "fileUpload"
          );

          $("[data-upload-index=" + index + "]")
            .find(".file__preloader")
            .remove()
            .find(".uploading")
            .replaceWith("<a href=# class=file__remove>Remove</a>");

          if (numUploaded === totalUploads) {
            OLCS.logger.debug( "All files uploaded", "fileUpload");
            handleResponse(xhr.responseText, containerIndex);
            enableElements();
          }

        }
      };

      if (sectionIdVal) {
        fd.append("sectionId",sectionIdVal);
      }

      fd.append(name + "[file]", file);
      fd.append(name + "[upload]", "Upload");
      fd.append(security.name, security.value);

      xhr.open(
        form.attr("method"),
        url,
        // third param is async yes/no
        true
      );

      // we don't yet listen out for this header server
      // side, but let's provide it anyway in case the backend
      // ever becomes interested in the way we uploaded the file
      xhr.setRequestHeader("X-Inline-Upload", true);
      xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

      // off we go...
      setTimeout(function() {
        xhr.send(fd);
      }, index * MULTI_UPLOAD_DELAY);
    }

    function setup() {
      $(submitSelector).hide();
      $(inputSelector).attr("multiple", options.multiple);
    }

    $(document).on("click", removeSelector, function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        if(!uploadInProgress){
        var button = $(this);
        var form   = $(this).parents("form");

        F.pressButton(form, button);

        $(this).eq(0).replaceWith("<span class='uploading'>Removing &hellip;</span>");

        OLCS.submitForm({
          form: form,
          success: deleteResponse
        });
      }
    });

    if (asyncUploads) {
      $(document).on("change", inputSelector, function(e) {
        e.preventDefault();
        e.stopPropagation();

        var form       = $(this).parents("form");
        var container  = $(this).parents(containerSelector);
        var files      = e.target.files;
        numUploaded    = 0;
        totalUploads   = files.length;

        OLCS.logger.debug("Uploading " + files.length + " file(s)", "fileUpload");

        $.each(files, function(index, file) {
          upload(form, container, index, file);
        });
      });

      OLCS.eventEmitter.on("render", setup);
    }
  };

}(document, window.jQuery));
