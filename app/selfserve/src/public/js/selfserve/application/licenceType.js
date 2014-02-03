/* 
 * Javascript for the licence page
 */

var selfserve = {}; // Initialize the selfserve object

selfserve.setSections = function ($context) {
    // Section visibility
    if ( $('input:radio[name=operatorLocation]:checked').val() != "" ) {
        $('#operatorTypeSection').show();
    } else {
        $('#operatorTypeSection').hide();
    }

    // Country logic
    if ( $('input:radio[name=operatorLocation]:checked').val() == "uk" ) {
        // Operator type can only be goods
        $(":radio[value='psv']").parent().show();
    } else {
        $(":radio[value='goods']").prop('checked',true);
        $('#operatorTypeSection').hide();
    }

    // Licence type visibility
    if ( $('input:radio[name=operatorType]:checked').val() ) {
        $('#licenceTypeSection').show();
    } else {
        $('#licenceTypeSection').hide();
    }

    // Licence type values
    if ( $('input:radio[name=operatorType]:checked').val() == "psv" ) {
        $(":radio[value='special restricted']").parent().show();
    } else {
        $(":radio[value='special restricted']").parent().hide();
    }

    // licence type and operator type values to determine visibility of 
    // main business type input fields = Goods 
    if (( $('input:radio[name=operatorType]:checked').val() == "goods" )
            && ( $('input:radio[name=operatorLocation]:checked').val() )
            && ( $('input:radio[name=licenceType]:checked').val() )
            )
    {
        if (
                ( $('input:radio[name=licenceType]:checked').val() == 'special restricted' ) ||
                ( $('input:radio[name=licenceType]:checked').val() == 'standard national' ) ||
                ( $('input:radio[name=licenceType]:checked').val() == 'standard international' )
           )
        {
            $('#licenceBusinessTradeSection').show();
            $('#licenceMainBusinessSection').hide();
        }
        else
        {
            $('#licenceMainBusinessSection').show();
            $('#licenceBusinessTradeSection').hide();
        }
    } else {
        $('#licenceBusinessTradeSection').hide();
        $('#licenceMainBusinessSection').hide();

    }

     // licence type and operator type values to determine visibility of 
    // main business type input fields = PSV 
    if (( $('input:radio[name=operatorType]:checked').val() == "psv" )
            && ( $('input:radio[name=operatorLocation]:checked').val() )
            && ( $('input:radio[name=licenceType]:checked').val() )
            )
    {
        if (
                ( $('input:radio[name=licenceType]:checked').val() == 'standard national' ) ||
                ( $('input:radio[name=licenceType]:checked').val() == 'standard international' )
           )
        {
            $('#licenceBusinessTradeSection').show();
            $('#licenceMainBusinessSection').hide();
        }
        else
        {
            $('#licenceMainBusinessSection').show();
            $('#licenceBusinessTradeSection').hide();
        }
    }
    
};

$(document).ready(function(){
    $('BODY').on("click","input:radio", function(e){
        selfserve.setSections();
    });

});