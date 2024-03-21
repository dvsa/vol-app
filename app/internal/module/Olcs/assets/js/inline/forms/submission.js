OLCS.ready(function () {
    "use strict";

    var selector = "#submission";

    $(selector).find("button:first").hide();

    $(document).on("change", selector + " select", function (e) {
        e.preventDefault();

      // would really rather not do this here, but the backend relies on this value to determine
      // which button has been pressed, so we have to inject this value into the form before
      // firing the ajax request
        $(selector).prepend(
            "<input type=hidden class=form__action name='fields[submissionSections][submissionTypeSubmit]' value='' />"
        );

        OLCS.submitForm({
            form: $(selector),
            success: OLCS.filterResponse(selector, selector),
            complete: function () {
                $(selector).find("button:first").hide();
                $(".form__action").remove();
            },
            disable: false
        });

      // similarly, we need to disable all the checkboxes which currently have a value (i.e.
      // aren't disabled). We don't need to re-enable them since the whole form is redrawn
      // so the relevant checbkxoes will always be correctly enabled / disabled
        $("[type=checkbox][value!='']").attr("disabled", true);
    });
});
