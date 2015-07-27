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
     * @Form\Name("operator-type")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "id": "operator-type",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-type",
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
     * @Form\Type("Hidden")
     */
    public $personId = null;

    /**
     * @Form\Type("Hidden")
     */
    public $personVersion = null;
}
