$(function() {
  $(document).on("click", ".table__header [name=action]", function(e) {
    e.preventDefault();

    var template = [
      '<div class="overlay  js-hidden"></div>',
      '<div class="modal__wrapper js-hidden">',
        '<div class="modal">',
          '<div class="modal__header">',
            '<h2 class="modal__title"></h2>',
            '<a href="" class="modal__close">Close</a>',
          '</div>',
          '<div class="modal__content"></div>',
        '</div>',
      '</div>'
    ].join('\n');

    var form = $(this).parents("form");
    form.prepend("<input type=hidden name=action value='" + $(this).val() + "' />");

    OLCS.formAjax(form, function(data) {
      $("body").prepend(template);
      $(".modal__content").html(data);
      $(".overlay").show();
      $(".modal__wrapper").show();
    });
  });
});
