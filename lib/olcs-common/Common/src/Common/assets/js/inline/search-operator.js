$(document).on('ready', function() {

    function toggleLabels() {
        var current = $('input[name="searchBy"]:checked').val();
        var label = $('label[for="search"]');

        label.text(label.attr('data-search-'+current));
    }

    toggleLabels();

    $(document).on('change', 'input[name="searchBy"]', function() {
        toggleLabels();
    });

});