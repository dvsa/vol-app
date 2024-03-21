OLCS.ready(function () {

    // jshint newcap:false

    "use strict";

    var F = OLCS.formHelper;

    function setupCascade()
    {

        var operatorType = F("type-of-licence", "operator-type");
        var licenceType = F.findInput('type-of-licence', 'licence-type');
        var vehicleType = F.findInput('type-of-licence', 'vehicle-type');
        var niFlag = OLCS.formHelper('type-of-licence', 'operator-location');
        var trafficArea = F("details", "trafficArea");

        OLCS.cascadeForm({
            form: "form",
            cascade: false,
            rulesets: {
                // operator type only shown when location has been completed and value is great britain
                "operator-type": function () {
                    return trafficArea.val() !== "N";
                },
                'type-of-licence': {
                    'selector:.js-difference-guidance': function () {
                        return niFlag.filter(':checked').val() === 'N';
                    }
                },

                // licence type is nested; the first rule defines when to show the fieldset
                // (in this case if the licence is NI or the user has chosen an operator type)
                "licence-type": {
                    "*": function () {
                        return (
                            trafficArea.val() === "N" || operatorType.filter(":checked").length
                        );
                    },

                    // this rule relates to an element within the fieldset
                    "licence-type=ltyp_sr": function () {
                        return operatorType.filter(":checked").val() === "lcat_psv";
                    },// these are the "Read more about" links
                    "#typeOfLicence-hint-goods": function () {
                        return (
                            niFlag.filter(':checked').val() === 'Y' ||
                            operatorType.filter(':checked').val() === 'lcat_gv'
                        );
                    },
                    "#typeOfLicence-hint-psv": function () {
                        return operatorType.filter(':checked').val() === 'lcat_psv';
                    },
                    'selector:#ltyp_sr_radio_group': function () {
                        return operatorType.filter(':checked').val() === 'lcat_psv';
                    },
                    'selector:div[id$=\'ltyp_si_content\']': function () {
                        var isGoods = trafficArea.val() == "N" ||
                            operatorType.filter(':checked').val() == 'lcat_gv';

                        return (
                            isGoods &&
                            licenceType.filter(':checked').val() == 'ltyp_si'
                        );
                    },
                    '#lgv-declaration': function () {
                        var isGoods = trafficArea.val() == "N" ||
                            operatorType.filter(':checked').val() == 'lcat_gv';

                        return (
                            isGoods &&
                            licenceType.filter(':checked').val() == 'ltyp_si' &&
                            vehicleType.filter(':checked').val() == 'app_veh_type_lgv'
                        );
                    }
                }
            },
        });
    }

    setupCascade();

    OLCS.eventEmitter.on("render", function () {
        setupCascade();
    });

});
