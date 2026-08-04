<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Name("licence-type")
 * @Form\Options({
 *     "radio-element":"licence-type"
 * })
 */
class LicenceType
{
    /**
     * @Form\Name("licence-type")
     * @Form\Attributes({"id": ""})
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     * @Form\Options({
     *      "short-label": "short-label-tol-licence-type",
     *      "error-message": "type-of-licence-error",
     *      "fieldset-attributes": {"id": "licence-type"},
     *      "fieldset-data-group": "licence-type",
     *      "label": "application_type-of-licence_licence-type.data.licenceType",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "value_options": {
     *          \Common\RefData::LICENCE_TYPE_RESTRICTED: {
     *             "value":\Common\Refdata::LICENCE_TYPE_RESTRICTED,
     *             "label":"Restricted",
     *          },
     *          \Common\RefData::LICENCE_TYPE_STANDARD_NATIONAL: "Standard National",
     *          \Common\RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL: {
     *             "value":\Common\Refdata::LICENCE_TYPE_STANDARD_INTERNATIONAL,
     *             "label":"Standard International",
     *          },
     *          "Special Restricted":{
     *              "value":\Common\RefData::LICENCE_TYPE_SPECIAL_RESTRICTED,
     *              "label":"Special Restricted",
     *              "attributes":{"id":"ltyp_sr_radio"},
     *              "item_wrapper_attributes":{"id":"ltyp_sr_radio_group"}
     *          }
     *      },
     *
     * })
     */
    public $radio;

    /**
     * @Form\Attributes({
     *     "value": "application_type-of-licence_licence-type.data.restrictedGuidance",
     *     "data-container-class": "typeOfLicence-guidance-restricted js-visible",
     * })
     * @Form\Options({"tokens":{"application_type-of-licence_licence-type.data.restrictedGuidance"}})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $ltyp_rContent;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Form\Lva\Fieldset\StandardInternationalVehicleType")
     */
    public $ltyp_siContent;
}
