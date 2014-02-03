
function defaultView()
{
    $('#business-company-details').hide();
    $('#business-trading').hide();
    $('#business-types').hide();
}

function showRegisteredCompany()
{
    $('#business-company-details').show();
    $('#business-trading').show();
    $('#business-types').show();
    
}

function showSoleTrader()
{
    $('#business-trading').hide();
    $('#business-types').hide();
}

function showPartnership()
{
    $('#business-trading').hide();
    $('#business-types').hide();
}

function showPublicAuthority()
{
    $('#business-trading').hide();
    $('#business-types').hide();
}

function showOther()
{
    $('#business-trading').hide();
    $('#business-types').hide();
    $('label[for="operatorName"]').text('Operator name:');
    $('#business-company-details').show();
    $('#companyNumberField').hide();
    
}

function typeSelect() {
        switch($('#entityType').val())
        {
            case 'Registered company':
                showRegisteredCompany();
                break;
            case 'Sole Trader':
                showSoleTrader();
                break;
            case 'Partnership':
                showPartnership();
                break;
            case 'Public Authority':
                showPublicAuthority();
                break;
            case 'Other':
                showOther();
                break;
            default:
                defaultView();
                break;
        }

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
    
    $('#entityType').on('change', typeSelect);

}); 