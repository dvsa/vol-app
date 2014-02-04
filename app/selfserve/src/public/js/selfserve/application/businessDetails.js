
function defaultView()
{
    $('#business-company-details').hide();
    $('#business-trading').hide();
    $('#business-types').hide();
}

function showRegisteredCompany()
{

    $('label[for="operatorName"]').text('Registered company name:');
    $('#business-company-details > h2').text('Registered company details:');
    $('div#operatorNameField div.instruction-text p').text('Instruction text goes here');

    $('#business-company-details').show();
    $('#business-trading').show();
    $('#business-types').show();
}

function showSoleTrader()
{
    $('#business-company-details').hide();

    $('label[for="operatorName"]').text('Operator name:');
    $('#business-company-details > h2').text('Sole trader details:');
    $('div#operatorNameField div.instruction-text p').text('Instruction text goes here');

    $('#business-trading').show();
    $('#business-types').show();

}

function showPartnership()
{
    $('#business-trading').show();
    $('#business-types').show();
    $('#companyNumberField').hide();

    $('label[for="operatorName"]').text('Partnership name:');
    $('div#operatorNameField div.instruction-text p').text('e.g. John Smith & Partners');
    $('#business-company-details > h2').text('Partnership details:');

    $('#business-company-details').show();
    $('#operatorNameField').show();

}

function showLLP()
{
    $('#business-trading').hide();
    $('#business-types').hide();
    $('#operatorNameField').hide();

    $('#business-company-details > h2').text('LLP details:');
    $('label[for="companyNumId"]').text('LLP number:');
    $('div#operatorNameField div.instruction-text p').text('Instruction text goes here');

    $('#business-company-details').show();
    $('#companyNumberField').show();

}

function showPublicAuthority()
{
    $('#companyNumberField').hide();

    $('label[for="operatorName"]').text('Public authority name:');
    $('#business-company-details > h2').text('Public authority details:');
    $('div#operatorNameField div.instruction-text p').text('Instruction text goes here');
    
    $('#operatorNameField').show();
    $('#business-company-details').show();
    $('#business-trading').show();
    $('#business-types').show();
    
}

function showOther()
{

    $('#companyNumberField').hide();
    
    $('label[for="operatorName"]').text('Organisation name:');
    $('div#operatorNameField div.instruction-text p').text('Instruction text goes here');
    $('#business-company-details > h2').text('Organisation details:');

    $('#business-company-details').show();
    $('#business-trading').show();
    $('#business-types').show();    
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
            case 'LLP':
                showLLP();
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