var OLCS = OLCS || {};

/**
 * Multi-filter
 *
 * used to filter a destination multiselect based on the current
 * value(s) of a source multiselect. The exact keys/values used
 * to do the filtering are tied one to one with the use-case which
 * spawned the component so probably aren't re-usable (yet)
 */

OLCS.multiFilter = (function(document, $, undefined) {

  "use strict";

  return function init(options) {

    var cachedOptions = {};

    $(options.to).find("option").each(function(_, v) {
      var option = $(v);
      var group  = option.parent().prop("label");

      if (!cachedOptions[group]) {
        cachedOptions[group] = [];
      }

      // build up an object keyed by the containing optgroup
      cachedOptions[group].push({
        text: option.text(),
        value: option.val()
      });
    });

    function setup() {

      var available = [];
      $(options.from).find(":selected").each(function(_, v) {
        available.push($(v).text());
      });

      // take a record of our destination's current values
      var current = $(options.to).val() || [];

      // iterate over the available opt groups and render
      // the appropriate set of options
      var groupStr = $.map(available, function(v) {
        return renderOptGroup(v, current);
      });

      // completely replace the destination's groups and options
      // and make sure chosen knows about it
      $(options.to).html(groupStr).trigger("chosen:updated");
    }

    // Helper to render a single <optgroup> and all child <option> elems
    function renderOptGroup(label, current) {
      var opts = cachedOptions[label];
      var optStr = "";

      for (var i = 0, j = opts.length; i < j; i++) {
        var option = opts[i];
        var attrs;

        // make sure we preserve any selected values which are still valid
        // @NOTE: we can't use current.indexOf() - IE8 doesn't support it
        if ($.inArray(option.value, current) !== -1) {
          attrs = " selected=''";
        } else {
          attrs = "";
        }
        optStr += "<option value='" + option.value + "'" + attrs + ">" + option.text + "</option>";
      }

      return "<optgroup label='" + label + "'>" + optStr + "</optgroup>";
    }

    setup();

    $(document).on("change", options.from, setup);
  };

}(document, window.jQuery));
