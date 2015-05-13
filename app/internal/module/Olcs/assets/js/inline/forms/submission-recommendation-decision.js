OLCS.ready(function() {
  "use strict";

  var source = ".js-sub_st_rec";
  var dest   = ".js-sub-legislation";
  var target = "sub_st_rec_ptr";

  var original = $(dest).clone();
  var subset   = original.clone();

  /**
   * This looks a bit rough but all we're doing is filtering out
   * all the irrelevant options of the subset by destroying their
   * immediate parent (an optgroup)
   *
   * This works because options are already 'grouped' properly, so
   * there is never a situation where one item in a group is Y and
   * another is N
   */
  subset.find("[data-in-office-revokation=N]").parent().remove();

  $(source).on("change", function() {
    var contents;
    var recommendations = $(this).val();

    if (recommendations && recommendations.indexOf(target) >= 0) {
      contents = subset.html();
    } else {
      contents = original.html();
    }

    $(dest).html(contents).trigger("chosen:updated");
  });
});
