<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-service-number-and-type")
 * @Form\Attributes({"method":"post"})
 * @Form\InputFilter("Common\Form\InputFilter")
 */
class BusServiceNumberAndType
{

    /**
     * @Form\Attributes({"class":"","id":"serviceNo"})
     * @Form\Options({"label":"Service number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":70}})
     * @Form\Validator({"name":"Zend\I18n\Validator\Alnum"})
     */
    public $serviceNo = null;

    /**
     * @Form\Attributes({"class":"","id":"startPoint"})
     * @Form\Options({"label":"Service number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":70}})
     * @Form\Validator({"name":"Zend\I18n\Validator\Alnum"})
     */
    public $startPoint = null;

    /**
     * @Form\Attributes({"class":"","id":"finishPoint"})
     * @Form\Options({"label":"Service number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":70}})
     * @Form\Validator({"name":"Zend\I18n\Validator\Alnum"})
     */
    public $finishPoint = null;

    /**
     * @Form\Attributes({"class":"","id":"via"})
     * @Form\Options({"label":"Service number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":70}})
     * @Form\Validator({"name":"Zend\I18n\Validator\Alnum"})
     */
    public $via = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":"otherDetails"})
     * @Form\Options({"label":"Other N&P details"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":800}})
     */
    public $otherDetails = null;

    /**
     * @Form\Attributes({"id":"dateReceived"})
     * @Form\Options({
     *     "label": "Date received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     * @Form\Validator({"name":"Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $dateReceived = null;

    /**
     * @Form\Attributes({"id":"effectiveDate"})
     * @Form\Options({
     *     "label": "Effective date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     * @Form\Validator({"name":"Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $effectiveDate = null;

    /**
     * @Form\Attributes({"id":"endDate"})
     * @Form\Options({
     *     "label": "End date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     * @Form\Validator({"name":"Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $endDate = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
