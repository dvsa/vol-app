function defaultView() {
    $('#business-company-details').toggle();
    $('#business-training').toggle();
    $('#business-types').toggle();
}
        
function typeSelect() {
    $('#entityType').on('change', function () {
        if ($(this).val() === 'Registered company') {
            defaultView();
        }
    });
}

gTNCID = 0;
function bindAddAnother() {
    $('#tradingAddAnother').click(function () {
        gTNCID++;
        var x = $('<a href="#">X</a>');
        
        x.click(function (e) { $('#TNC' + gTNCID).remove(); gTNCID--; e.preventDefault();});
        $('#tradingNameContainers').append($('#tradingNameContainer').clone().attr('id', 'TNC' + gTNCID));
        
        $('#TNC' + gTNCID + ' .instruction-text').after(x);
    });
}

$(document).ready(function () {
    defaultView();
    typeSelect();
    bindAddAnother();
}); 