var olcs = {}; // Initialize the olcs object

olcs.config = {
    tinyMceConfig : {
        plugins: "table paste",
        tools: "inserttable",
        menubar: "edit view format table",
        skin_url: '/css/olcs-tinymce'
    },
    magnificConfig : {
        type: 'ajax',
        closeOnContentClick: false,
        closeOnBgClick: false,
        enableEscapeKey: false,
        callbacks: {
            ajaxContentAdded: function () {
                olcs.applyJSToContext($(this.content));
            }
        }
    }
};

olcs.jsToContext = [];
olcs.applyJSToContext = function ($context) {
    if (typeof $context === 'string') {
        $context = $($context);
    }
    $.each(olcs.jsToContext, function (i, func) {
        func($context);
    });
};
olcs.jsToContext.push(function ($context) {
    $('.tableContent:has(.readmore)', $context).each(function () {
        var $container = $(this),
            $table = $container.find('table'),
            $rows = $table.find('tbody tr'),
            count = $rows.length,
            $hiddenRows = $rows.slice(3).hide(),
            $pager = $container.find('.readmore'),
            $currentCount = $pager.find('.current-count'),
            $totalCount = $pager.find('.total-count');

        if ($hiddenRows.length) {
            $currentCount.text('3');

            $('<a />', {
                text : 'Show all',
                href : '#'
            }).click(function (e) {
                e.preventDefault();

                $hiddenRows.toggle();

                var currentCount = $rows.filter(':visible').length;

                $pager.toggleClass('expanded', currentCount > 3);
                $currentCount.text(currentCount);
            }).appendTo($pager);
        }
    });
});

olcs.jsToContext.push(function ($context) {
    $('select.multiselect', $context).multiselect().hide();
});

/*
 * This function differs from enableOnFormChange as it looks for all required fields
 * If they are all satisfied, it enables the form. 
 * enableOnFormChange merely looks for *any* values prefilled to enable a form.
 * Used on forms with multiple fields where none are strictly required but it needs
 * something prefilled to enable it.
 */
olcs.validateOnFormChange = function (e) {
    var $formSection = $(this).closest('.form-section'),
        isEmpty = true,
        hasEmptiness = false;

    if (!$formSection.length) {
        $formSection = $(this).closest('form');
    }

    $formSection.find('.required :input').each(function () {
        var $this = $(this),
            val = $this.val(),
            nam = $this.attr('name');

        if ( !($this.is(':radio'))) {
            if (!val ||
                     (!$.isArray(val) && $.trim(val).length < ($this.is('textarea') ? 5 : 1))
                     || ($.isArray(val) && !val.length)) {
                isEmpty = true;
            } else {
                isEmpty = false;
            }
        } else {
            if ( $(':radio[name="'+nam+'"]:checked').length ) {
                isEmpty=false;
            } else {
                isEmpty=true;
            }
        }
        if ( isEmpty ) {
            hasEmptiness=true;
        }
    });

    $formSection.find('.btn-next, .btn-save, .btn-submit').prop('disabled', hasEmptiness).toggleClass('disabled', hasEmptiness);
    
};

$(document).ready(function(){
    var olcsTinyMceConfig = olcs.config.tinyMceConfig;

    $(document).bind("click", function(event,additionalData){
        if (!!additionalData) {
            return;
        }
        var target = $(event.target);
        $('.tooltip').each(function() {
            if ((this !== target.next().get(0))) {
                $(this).siblings('img').trigger('click',"remove");
            }
        });
   });
    

    $('#vcaseNewForm, #vcaseDetailsForm, #recommendDecisionForm, #vcaseActionForm, #applicationNewForm')
        .find('.required :input')
        .change(olcs.validateOnFormChange)
        .keyup(olcs.validateOnFormChange)
        .change();

    olcs.applyJSToContext($(document));

    // Non event page stuff
    $("#advancedSearch").hide();
    //$(".fullAddr").hide();
    $('.ttips').tooltip('hide');

    $("a.show-collapsed").click(function (e) {
        e.preventDefault();
        $("#advancedSearch").slideToggle('fast');
        $(this).toggleClass('expanded');
    });
    
    $( ".olcs-list-table" ).delegate( ".correAddress, .opCentAddress", "click", function() {
        $(this).find('img').toggleClass('arrRt')
                                            .toggleClass('arrDn');
        $(this).find('.fullAddr').toggleClass('hide');
    });
    
    tinymce.init($.extend({
        selector: "textarea#caseDetailsNote"
    }, olcsTinyMceConfig));
    
    $("#caseDetailsDiv .btn-save").click(function (e) {
       var data = { caseId: $('#caseId').val(),
                                caseDetailsNote: tinymce.get('caseDetailsNote').getContent({format : 'raw'}), 
                                detailTypeId: $('#detailTypeId').val()}
        if ($('#commentId').length) {
            data.commentId = $('#commentId').val();
        }
        olcs.ajax.request(this, data,  'checkForCaseDetailId');

    });

    $('#vcaseDetailsForm').submit(function(event) {
        // Convert the caseDetailsNote textarea to raw HTML
        $('#caseDetailsNote').val(tinymce.get('caseDetailsNote').getContent({format : 'raw'}));
    });
    
//------- recommendDecisionForm --------------------   
    tinymce.init($.extend({
        selector: "textarea#subRecomDecNote"
    }, olcsTinyMceConfig));
    
    $('#recommendDecisionForm').submit(function(event) {
        // Convert the caseDetailsNote textarea to raw HTML
        $('#subRecomDecNote').val(tinymce.get('subRecomDecNote').getContent({format : 'raw'}));
    });

//------- applicationNewForm --------------------
    $('.operatorTypeBlock INPUT').click(function (event) {
       // alert($(this).val());
       $("#licenceTypesContainer input:radio").filter(':checked').prop('checked',false);
       $("#licenceTypesContainer").show();
       if ( $(this).val() == "psv" ) {
           // only show special restricted if psv
           $(":radio[value='special restricted']").parent().show();
       } else {
           $(":radio[value='special restricted']").parent().hide();
       }
    });

    $('#applicationNewForm #cancelbutton, #applicationDetailsDiv #cancelbutton').click(function (event) {
       window.location.href='/';
    });

}); // end document.ready()

//MWC - Callback for the case detail save ajax call.
function checkForCaseDetailId() {
       if (!$('#commentId').length) {
            $('#caseId').after('<input type="hidden" name="commentId" id="commentId" value="'+olcs.ajax.response.commentId+'">');
       }
    }
    
function setCaseTabView() {
    console.log('put in html');
}
