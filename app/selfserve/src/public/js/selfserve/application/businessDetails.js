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
        var x = $('<button class="btn btn-add" id="TNCL' + gTNCID + '">Remove</button>');
        var input = $('<input type="text" id="TNC' + gTNCID + '" />');
        var currentID = gTNCID;
        
        x.click(function (e) { 
            $('#TNCL' + currentID).remove();  
            $('#TNC' + currentID).remove();  
            e.preventDefault();
        });
        
        $('#trading-names-collection').before(input);
        $('#TNC' + gTNCID).after(x);
        
        $('#TNC' + gTNCID + ' .instruction-text').after(x);
    });
}

$(document).ready(function () {
    defaultView();
    typeSelect();
    bindAddAnother();
}); 