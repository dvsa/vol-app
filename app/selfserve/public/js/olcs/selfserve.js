olcs.selfserve = (function() {
    return {
        sendForms: function (forms) {
          var dataString = '';
          for(k in forms) {
              dataString += $('#' + forms[k] + ' form').serialize();
          }
           $.ajax({
                  type: 'post',
                  url: '',
                  data: dataString,
                  success: function () {}});
        },
        hideSteps: function(steps) {
            for (step in steps) {
                olcs.selfserve.hideStep(steps[step]);
            }
        },
        hideStep: function(step) {
            $('#' + step).hide();
        },
        showStep: function(step) {
            $('#' + step).show();
        },
        moveFromTo: function(oldStep, newStep) {
            olcs.selfserve.hideStep(oldStep);
            olcs.selfserve.showStep(newStep);
        },
        hideButtons: function() {
            $('input[type="submit"]').hide();
        },
        bindOnClick: function(id, fn) {
            $('.' + id).click(fn);
        },
        bindAll: function(map) {
            for (var k in map) {
                olcs.selfserve.bindOnClick(k, map[k]);
            }
        },
        run: function() {
        }
    };
})();