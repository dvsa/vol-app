OLCS.ready(function() {

  "use strict";

  // get a nicer alias for our form helper
  var F = OLCS.formHelper;

  // cache some input lookups
  var niFlag = F("operator-location", "niFlag");
  var operatorType = F("operator-type", "goodsOrPsv");
  var licenceType = F("licence-type", "licenceType");
  var discSequence = F("prefix", "discSequence");
  var startNumber = F("discs-numbering", "startNumber");
  var endNumber = F("discs-numbering", "endNumber");
  var originalEndNumber = F("discs-numbering", "originalEndNumber");
  var endNumberIncreased = F("discs-numbering", "endNumberIncreased");
  var totalPages = F("discs-numbering", "totalPages");
  var discPrefixesUrl = "/admin/disc-printing/disc-prefixes-list";
  var discNumberingUrl = "/admin/disc-printing/disc-numbering";
  var discNumbering = F("discs-numbering");
  var noDiscs = F("no-discs");
  var emptyLabel = "Please select";

  // get list of prefixes for selected operator location, operator type and licence type
  $(licenceType).change(function() {
    var data = {
      'niFlag': niFlag.filter(':checked').val(),
      'operatorType': operatorType.filter(':checked').val(),
      'licenceType': licenceType.filter(':checked').val()
    };
    $.post(discPrefixesUrl, data, function(result) {
      var str = "<option value=''>Please Select</option>";
      $.each(result, function(i, r) {
        if (r.value === "" && emptyLabel) {
          r.label = emptyLabel;
        }
        str += "<option value='" + r.value + "'>" + r.label + "</option>";
      });
      discSequence.html(str);  
    });
  });

  // fetch discs numbering information if disc prefix was changed
  $(discSequence).change(function () {
    var data = {
      'niFlag': niFlag.filter(':checked').val(),
      'operatorType': operatorType.filter(':checked').val(),
      'licenceType': licenceType.filter(':checked').val(),
      'discSequence': $('#discSequence :selected').val(),
      'discPrefix': $('#discSequence :selected').text()
    };

    startNumber.val('');
    endNumber.val('');
    totalPages.val('');
    $(noDiscs).hide();
    $('#submit').attr('disabled', 'disabled');

    // get disc numbering settings
    $.post(discNumberingUrl, data, function(result) {
      $('#submit').removeAttr('disabled');
      if ("endNumber" in result && result.endNumber == 0) {
        // no discs to print
        $(discNumbering).hide();
        $(noDiscs).show();
      } else {
        // in case disc numbering fieldset was hidden we need to show it and hide "no discs" message
        $(discNumbering).show();
        $(noDiscs).hide();

        //set up disc numbering settings
        if ("startNumber" in result) {
          startNumber.val(result.startNumber);
        }
        if ("endNumber" in result) {
          endNumber.val(result.endNumber);
        }
        if ("originalEndNumber" in result) {
          originalEndNumber.val(result.originalEndNumber);
        }
        if ("enddNumberIncreased" in result) {
          endNumberIncreased.val(result.endNumberIncreased);
        }
        if ("totalPages" in result) {
          totalPages.val(result.totalPages);
        }
      }
    });
  });
  
  // validate start number change
  $(startNumber).change(function () {
    var data = {
      'niFlag': niFlag.filter(':checked').val(),
      'operatorType': operatorType.filter(':checked').val(),
      'licenceType': licenceType.filter(':checked').val(),
      'discSequence': $('#discSequence :selected').val(),
      'discPrefix': $('#discSequence :selected').text(),
      'startNumberEntered': $(this).val()
    };

    $(this).attr('disabled','disabled');
    
    $.post(discNumberingUrl, data, function(result) {
      $(startNumber).removeAttr('disabled');
      if ("error" in result) {
        $(startNumber).wrap('<div class="validation-wrapper"></div>');
        $('<ul><li>' + result. error + '</li></ul>').insertBefore(startNumber);
      }
      //set up disc numbering settings
      if ("startNumber" in result) {
        startNumber.val(result.startNumber);
      }
      if ("endNumber" in result) {
        endNumber.val(result.endNumber);
      }
      if ("totalPages" in result) {
        totalPages.val(result.totalPages);
      }
      if ("endNumberIncreased" in result) {
        endNumberIncreased.val(result.endNumberIncreased);
      }
    });
  });
  
  // clear disc numbers if licence type was changed
  $('input[name="licence-type[licenceType]"]').change(function() {
      startNumber.val('');
      endNumber.val('');
      totalPages.val('');
  });

  // remove validation error message if present
  $('*').change(function() {
    if (startNumber.parent().is("div")) {
      startNumber.parent().children('ul').remove();
      $(startNumber).unwrap();
    }
  });

  $('#admin_disc-printing_form').submit(function() {
    // need to enable start number to pass it to backend  
    $(startNumber).removeAttr('disabled');
    return true;
  });

  // set up a cascade form with the appropriate rules
  OLCS.cascadeForm({
    form: "#admin_disc-printing_form",
    rulesets: {
      // operator location is *always* shown
      "operator-location": true,

      // operator type only shown when location has been completed
      // and value is great britain
      "operator-type": function() {
            return niFlag.filter(":checked").val() === "N";
      },

      // licence type is nested; the first rule defines when to show the fieldset
      // (in this case if the licence is NI or the user has chosen an operator type)
      "licence-type": {
        "*": function() {
          return (
            // NI
            niFlag.filter(":checked").val() === "Y" ||
            // ... any location checked and any operator type checked
            niFlag.filter(":checked").length && operatorType.filter(":checked").length
          );
        },

        // this rule relates to an element within the fieldset
        "licenceType=ltyp_sr": function() {
          return operatorType.filter(":checked").val() === "lcat_psv";
        }
      },
      
      "prefix": function() {
          return licenceType.filter(":checked").val();
      },
      
      "discs-numbering": function() {
          return licenceType.filter(":checked").val() && $('#noDiscs').attr('class') == 'hidden';
      },
      
    },
    submit: function() {
      // if we're not showing operator type yet, select a default so we don't get
      // any backend errors
      if (F("operator-type").is(":hidden")) {
        operatorType.first().prop("checked", true);
      }

      // ditto licence type; what we set here doesn't matter since as soon as the user
      // interacts with the form again we clear these fields
      if (F("licence-type").is(":hidden")) {
        F("licence-type", "licenceType").first().prop("checked", true);
      }
    }
  });
});
