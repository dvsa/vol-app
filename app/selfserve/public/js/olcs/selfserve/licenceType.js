olcs.selfserve.section.licenceType = (function(selfserve) {
    var saveAppended = false;

    function addSaveButton() {
        if (!saveAppended) {
            olcs.common.appendButton('Save', 'summaryBtn', 'licence-type', saveButton);
            saveAppended = true;
        }
    }
    function removeSaveButton() {
        if(saveAppended) {
            saveAppended = false;
            olcs.common.removeElement('summaryBtn');
        }
    }

    function saveButton() {
        selfserve.showStep('summary');
        selfserve.hideSteps(['operator-location-form', 'operator-type-form',
            'licence-type-psv-form', 'licence-type-goods-form']);

        removeSaveButton();
    }

    function operatorLocation() {
        removeSaveButton();
        if ($(this).val() === 'uk') {
            selfserve.showStep('operator-type-form');
            selfserve.hideStep('licence-type-goods-form');
        } else {
            selfserve.showStep('licence-type-goods-form');
            selfserve.hideStep('licence-type-psv-form');
            selfserve.hideStep('operator-type-form');
        }
    }

    function operatorType() {
        removeSaveButton();
        if ($(this).val() == 'goods') {
            selfserve.showStep('licence-type-psv-form');
            selfserve.hideStep('licence-type-goods-form');
        } else {
            selfserve.showStep('licence-type-goods-form');
            selfserve.hideStep('licence-type-psv-form');
        }

    }

    function licenceTypeGoods() {
        addSaveButton();
        selfserve.sendForms(['operator-location-form', 'operator-type-form',
                              'licence-type-psv-form']);
    }

    function licenceTypePsv() {
        addSaveButton();
        selfserve.sendForms(['operator-location-form', 'licence-type-goods-form']);
    }

    function summary() {
    }

    return {
        run: function() {

            var hiddenSteps = ['summary', 'operator-type-form',
                'licence-type-psv-form', 'licence-type-goods-form'];

            var bindEvents = {
                'application-licence-trafficArea': operatorLocation,
                'application-licence-goodsOrPsv': operatorType,
                'application-licence-licenceType-goods': licenceTypeGoods,
                'application-licence-licenceType-psv': licenceTypePsv
            };

            selfserve.hideSteps(hiddenSteps);
            selfserve.hideButtons();
            selfserve.bindAll(bindEvents);
        }
    };
})(olcs.selfserve);