/* 
 * Javascript for the licence page
 */

var selfserve = {}; // Initialize the selfserve object

selfserve.setSections = function ($context) 
{
    // Section visibility
    if ( $('input:radio[name=operatorLocation]:checked').val() != "" ) 
    {
        $('#operatorTypeSection').show();
    }
    else
    {
        $('#operatorTypeSection').hide();
    }

    // Country logic
    if ( $('input:radio[name=operatorLocation]:checked').val() == "uk" ) 
    {
        // Operator type can only be goods
        $(":radio[value='psv']").parent().show();
    } 
    else
    {
        $(":radio[value='goods']").prop('checked',true);
        $('#operatorTypeSection').hide();
    }

    // Licence type visibility
    if ( $('input:radio[name=operatorType]:checked').val() ) 
    {
        $('#licenceTypeSection').show();
    }
    else
    {
        $('#licenceTypeSection').hide();
    }

    // Licence type values
    if ( $('input:radio[name=operatorType]:checked').val() == "psv" ) 
    {
        $(":radio[value='special restricted']").parent().show();
    } 
    else 
    {
        $(":radio[value='special restricted']").parent().hide();
    }
    
    // Check the save/next button
    if (( $('input:radio[name=operatorType]:checked').val() )
            && ( $('input:radio[name=operatorLocation]:checked').val() )
            && ( $('input:radio[name=licenceType]:checked').val() ))  
    {
        $('#savenextbutton').removeClass('disabled');
    }
    else 
    {
        $('#savenextbutton').addClass('disabled');

    }
};

$(document).ready(function()
{
    $('BODY').on
    (
        "click","input:radio", function(e)
        {
            selfserve.setSections();
        }
    );

});