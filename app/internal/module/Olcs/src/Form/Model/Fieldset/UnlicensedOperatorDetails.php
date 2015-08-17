<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("operator-details")
 */
class UnlicensedOperatorDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *      "label":"internal-operator-profile-name",
     *      "short-label":"internal-operator-profile-name"
     * })
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Name("name")
     * @Form\Type("Text")
     */
    public $name = null;

    /**
     * @Form\Name("operatorType")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "id": "operatorType",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operatorType",
     *      "label": "Operator type",
     *      "short-label": "Operator type",
     *      "value_options":{
     *          "lcat_gv":"Goods",
     *          "lcat_psv":"PSV"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $operatorType = null;

    /**
     * @Form\Attributes({"id":"trafficArea","placeholder":""})
     * @Form\Options({
     *     "label": "Traffic area",
     *     "short-label": "Traffic area",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TrafficArea",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $trafficArea = null;

    /**
     * @Form\Type("Hidden")
     */
    public $contactDetailsId = null;

    /**
     * @Form\Type("Hidden")
     */
    public $contactDetailsVersion = null;
}
