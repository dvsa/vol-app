/* 
 * Ajax function for OLCS
 * Author: Mike Cooper
 */

(function ($) {
    olcs.ajax = {
        response : null,
        request : function (thisObj, data, successCallback)    {
            var $this = $(thisObj).addClass('loading'),
                request = $.ajax({
                    url: $(thisObj).attr('data-url') || "/",
                    type: $(thisObj).attr('data-method') || "POST",
                    data: data,
                    dataType: $(thisObj).attr('data-type') || "json"
                });

            //MWC - Can have passed in callback function
            request.done(function(response) {
                $this.removeClass('loading');
                olcs.ajax.response = response;
                if (successCallback != undefined) {
                    if (successCallback.length) {
                        eval(successCallback+'()');
                    }
                }
            });
            request.fail(function( jqXHR, textStatus ) {
                //MWC Temporary for debugging until error handling is sorted
                alert( "Request failed: " + textStatus );
                $this.removeClass('loading');
            });
        }
    };
}(jQuery));
