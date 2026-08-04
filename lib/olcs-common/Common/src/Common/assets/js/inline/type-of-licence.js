OLCS.ready(function () {

    'use strict';

    // change the URL in the browsers history so that they can't get back
    // to 'application/create' using the browser's back button
    if (history.pushState) {
        $(window).on('unload', function () {
            var targetLocation = $('body').data('target');
            if (targetLocation) {
                history.replaceState(null, null, targetLocation + 'type-of-licence');
            }
        });
    }

    // cache some input lookups
    var niFlag = OLCS.formHelper('type-of-licence', 'operator-location');
    var operatorType = OLCS.formHelper('type-of-licence', 'operator-type');
    var licenceType = OLCS.formHelper.findInput('type-of-licence', 'licence-type');
    var vehicleType = OLCS.formHelper.findInput('type-of-licence', 'vehicle-type');

    // set up a cascade form with the appropriate rules
    OLCS.cascadeForm({
        form: 'form',
        cascade: false,
        rulesets: {
            'type-of-licence': {
                'selector:.js-difference-guidance': function () {
                    return niFlag.filter(':checked').val() === 'N';
                }
            },
            // operator location is *always* shown
            'operator-location': true,
            // operator type only shown when location has been completed and value is great britain
            'operator-type': function () {
                return niFlag.filter(':checked').val() === 'N';
            },
            // licence type is nested; the first rule defines when to show the fieldset
            // (in this case if the licence is NI or the user has chosen an operator type)
            'licence-type': {
                '*': function () {
                    return (
                        niFlag.filter(':checked').val() === 'Y' ||
                        niFlag.filter(':checked').length && operatorType.filter(':checked').length
                    );
                },
                'licence-type=ltyp_sr': function () {
                    return operatorType.filter(':checked').val() === 'lcat_psv';
                },
                // these are the "Read more about" links
                '#typeOfLicence-hint-goods': function () {
                    return (
                        niFlag.filter(':checked').val() === 'Y' ||
                        operatorType.filter(':checked').val() === 'lcat_gv'
                    );
                },
                'selector:#typeOfLicence-hint-psv': function () {
                    return operatorType.filter(':checked').val() === 'lcat_psv';
                },
                'selector:#ltyp_sr_radio_group': function () {
                    var shouldShow = operatorType.filter(':checked').val() === 'lcat_psv';
                    if (shouldShow) {
                        $('#ltyp_sr_radio_group').removeAttr('style');
                    } else {
                        $('#ltyp_sr_radio_group').css('display', 'none');
                    }
                    return shouldShow;
                },
                'selector:div[id$=\'ltyp_si_content\']': function () {
                    var isGoods = niFlag.filter(':checked').val() === 'Y' ||
                        operatorType.filter(':checked').val() == 'lcat_gv';

                    return isGoods && licenceType.filter(':checked').val() == 'ltyp_si';
                },
                '#lgv-declaration': function () {
                    var isGoods = niFlag.filter(':checked').val() === 'Y' ||
                        operatorType.filter(':checked').val() == 'lcat_gv';

                    return isGoods &&
                        licenceType.filter(':checked').val() == 'ltyp_si' &&
                        vehicleType.filter(':checked').val() == 'app_veh_type_lgv';
                },
                '.typeOfLicence-guidance-restricted': function () {
                    return operatorType.filter(':checked').val() == 'lcat_gv' && licenceType.filter(':checked').val() == 'ltyp_r';
                },
                'selector:div[id$=\'typeOfLicence-guidance-restricted\']': function () {
                    return licenceType.filter(':checked').val() == 'ltyp_r';
                },
            }
        },
        submit: function () {
            // ensure an operator-type is set so we don't get any backend errors
            if (OLCS.formHelper('operator-type').is(':hidden')) {
                operatorType.first().prop('checked', true);
            }
            // ensure an licence-type is set so we don't get any backend errors
            if (OLCS.formHelper('licence-type').is(':hidden')) {
                OLCS.formHelper('type-of-licence', 'licence-type').first().prop('checked', true);
            }
        }
    });

});
