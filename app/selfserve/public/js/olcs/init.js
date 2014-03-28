var olcs = {};
function runOlcsJavascript(olcsAppType, controller, action) {
    $(document).ready(function () {
        olcs['common'].run();
        olcs[olcsAppType].run();
        olcs[olcsAppType][controller].run();
        olcs[olcsAppType][controller][action].run();
    });
}