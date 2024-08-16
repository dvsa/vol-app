<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;
use Common\Form\Model\Form\Traits\VersionTrait;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("team-details")
 */
class TeamDetails
{
    use VersionTrait;
    use IdTrait;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator({"name":"Laminas\Validator\StringLength","options":{"max":70}})
     */
    public $name = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Description"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator({"name":"Laminas\Validator\StringLength","options":{"max":255}})
     */
    public $description = null;

    /**
     * @Form\Attributes({"id":"team","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Traffic area",
     *     "service_name": "Common\Service\Data\TrafficArea",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $trafficArea = null;

    /**
     * @Form\Attributes({"id":"team","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Default printer",
     *     "service_name": "Olcs\Service\Data\Printer",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $defaultPrinter = null;

    /**
     * @Form\Name("printerExceptions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $printerExceptions = null;
}
