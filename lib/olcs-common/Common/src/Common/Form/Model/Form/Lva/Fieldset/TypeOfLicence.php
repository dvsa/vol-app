<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("Laminas\Form\Fieldset")
 */
class TypeOfLicence
{
    /**
     * @Form\Name("operator-location")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "short-label": "short-label-tol-operator-location",
     *      "fieldset-attributes": {"id": "operator-location"},
     *      "fieldset-data-group": "operator-location",
     *      "label": "application_type-of-licence_operator-location.data.niFlag",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "value_options": {
     *          {"value": "N", "label": "Great Britain"},
     *          {"value": "Y", "label": "Northern Ireland"}
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $operatorLocation;

    /**
     * @Form\Name("operator-type")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "short-label": "short-label-tol-operator-type",
     *      "error-message": "operator-type-error",
     *      "fieldset-attributes": {"id": "operator-type"},
     *      "fieldset-data-group": "operator-type",
     *      "label": "application_type-of-licence_operator-type.data.goodsOrPsv",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "value_options": {
     *          {"value": "lcat_gv", "label": "Goods vehicles"},
     *          "lcat_psv": "Public service vehicles"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $operatorType;

    /**
     * @Form\Name("licence-type")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\LicenceType")
     * @Form\Options({
     *     "label": "short-label-tol-licence-type",
     * })
     */
    public $licenceType;
}
