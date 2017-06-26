OLCS.ready(function() {
  "use strict";

  var labelMap = {
    1: "Licence No",
    2: "Case ID",
    3: "Bus registration No",
    5: "Transport manager",
    7: "Licence No",
    8: "IRFO ID",
    9: "Licence No"
  };

  function renderLabel(elem) {
    var val = elem.val();
    var label = labelMap[val];
    $("label[for=entity_identifier]").text(label);
  }

  $(document).on("change", "#category", function() {
    renderLabel($(this));
  });

  OLCS.cascadeInput({
    source: "#category",
    dest: "#subCategory",
    process: process("/list/scanning-sub-categories")
  });

  OLCS.cascadeInput({
    source: "#subCategory",
    dest: "#description",
    process: process("/list/sub-category-descriptions")
  });

  function process(url) {
    /**
     * We use the outer closure to bind the URL to fetch from;
     * all other behaviour is the same
     */
    return function(value, callback) {
      $.get(url + "/" + value, function(result) {
        if (result[0] && result[0].value === "") {
          // always shift off the first empty value
          delete result[0];
        }
        callback(result);
      });
    };
  }

  OLCS.cascadeForm({
    rulesets: {
      "details": {
        "*": true,
        "description": function() {
          return $("#description option").length > 0;
        },
        "otherDescription": function() {
          return $("#description option").length === 0;
        }
      }
    }
  });

  renderLabel($("#category"));
});
