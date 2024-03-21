OLCS.ready(function () {

  /**
   * NP 12/03/2015
   *
   * @FIXME: A lot of the JS in here needs addressing; much of it could
   * be wrapped up in other components and it needs to be written in a
   * far more idiomatic and clearer way. It could be trimmed down a lot.
   *
   * I've started to fix some of it but there is plenty more left to do,
   * and there are a lot of lint errors to tidy up too.
   */

  // jshint newcap:false

    "use strict";

  // get a nicer alias for our form helper
    var F = OLCS.formHelper;

  // cache some input lookups
    var niFlag       = F("operator-location", "niFlag");
    var operatorType = F("operator-type", "goodsOrPsv");
    var licenceType  = F("licence-type", "licenceType");
    var securityToken = document.getElementById("security").value;

    var discSequence = F("prefix", "discSequence");

    var startNumber        = F("discs-numbering", "startNumber");
    var endNumber          = F("discs-numbering", "endNumber");
    var originalEndNumber  = F("discs-numbering", "originalEndNumber");
    var endNumberIncreased = F("discs-numbering", "endNumberIncreased");
    var totalPages         = F("discs-numbering", "totalPages");
    var maxPages           = F("discs-numbering", "maxPages");

  // get list of prefixes for selected operator location, operator type and licence type
    $(licenceType).on("change", function () {

        clearDiscNumbers();

        var data = {
            "niFlag": niFlag.filter(":checked").val(),
            "operatorType": operatorType.filter(":checked").val(),
            "licenceType": licenceType.filter(":checked").val(),
            "security": securityToken
        };
        $.post(discPrefixesUrl, data, function (result) {
            var str = "<option value=''>Please Select</option>";
            $.each(result, function (i, r) {
                str += "<option value='" + r.value + "'>" + r.label + "</option>";
            });
            discSequence.html(str);
        });
    });

  // fetch discs numbering information if disc prefix was changed
    $(discSequence).on("change", function () {
        fetchDiscs();
    });

  // validate start number or max pages change
    $(startNumber).on("change", function () {
        fetchDiscs($(startNumber).val());
    });
    $(maxPages).on("change", function () {
        fetchDiscs($(startNumber).val());
    });

  // if user start journey again we need to clear previous discs numbers
    $(niFlag).on("change", function () {
        clearDiscNumbers();
    });

  // set up a cascade form with the appropriate rules
    OLCS.cascadeForm({
        form: "#admin_disc-printing",
        rulesets: {
            "operator-location": true,

            "operator-type": function () {
                return niFlag.filter(":checked").val() === "N";
            },

            "licence-type": {
                "*": function () {
                    return (
                  // NI
                    niFlag.filter(":checked").val() === "Y" ||
                    // ... any location checked and any operator type checked
                    niFlag.filter(":checked").length && operatorType.filter(":checked").length
                    );
                }
            },

            "prefix": function () {
                return licenceType.filter(":checked").val();
            },

            "discs-numbering": function () {
                return licenceType.filter(":checked").val() && endNumber.val() > 0;
            },

            "no-discs": function () {
              // remember; input values are strings
                return endNumber.val() === "0";
            },

            "form-actions": function () {
              // remember; input values are strings
                return endNumber.val() !== "0";
            }
        }
    });

    function fetchDiscs(startNo)
    {
        var data = {
            "niFlag": niFlag.filter(":checked").val(),
            "operatorType": operatorType.filter(":checked").val(),
            "licenceType": licenceType.filter(":checked").val(),
            "discSequence": $("#discSequence :selected").val(),
            "discPrefix": $("#discSequence :selected").text(),
            "maxPages": maxPages.val(),
            "security": securityToken
        };

        if (startNo !== null) {
            data.startNumberEntered = startNo;
        } else {
            clearDiscNumbers();
        }

        if (startNumber.parent().is("div")) {
            startNumber.parent().children("ul").remove();
            $(startNumber).unwrap();
        }

        F("form-actions", "print").attr('disabled', 'disabled');
        $.post(discNumberingUrl, data, function (result) {

            F("form-actions", "print").removeAttr('disabled');
            if (result.error) {
                $(startNumber).wrap("<div class='validation-wrapper'></div>");
                $("<ul><li>" + result.error + "</li></ul>").insertBefore(startNumber);
            }

          //set up disc numbering settings
            startNumber.val(result.startNumber);
          // trigger a change so our cascade rules kick in
            endNumber.val(result.endNumber).change();
            totalPages.val(result.totalPages);
            originalEndNumber.val(result.originalEndNumber);
            endNumberIncreased.val(result.endNumberIncreased);
        });
    }

    function clearDiscNumbers()
    {
        totalPages.val("");
        startNumber.val("");
        maxPages.val("");
      // trigger a change so our cascade rules kick in
        endNumber.val("").change();
    }
});
