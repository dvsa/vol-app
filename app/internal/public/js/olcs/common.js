olcs.common = (function () {
    return {
        appendButton: function (btnText, btnId, afterId, clickFn) {
            $('#' + afterId).after(
                    $('<button />')
                    .attr('id', btnId)
                    .addClass('btn btn-primary btn-dynamic')
                    .text(btnText)
                    .click(clickFn)
            );
        },
        removeElement: function (id) {
            $('#' + id).remove();
        },
        run: function () {
            
        }
    };
})();