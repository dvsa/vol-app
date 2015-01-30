<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class SiFields extends CaseBase
{
    /**
     * @Form\Attributes({"id":"notificationNumber","placeholder":"","class":"small"})
     * @Form\Options({"label": "Notification No."})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":36}})
     */
    public $notificationNumber;

    /**
     * @Form\Attributes({"id":"siCategory","placeholder":""})
     * @Form\Options({
     *     "label": "Category",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\SiCategory"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $siCategory = null;

    /**
     * @Form\Attributes({"id":"siCategoryType","placeholder":"","class":"short"})
     * @Form\Options({
     *     "label": "Category",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\SiCategoryType"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $siCategoryType = null;

    /**
     * @Form\Attributes({"id":"infringementDate","placeholder":"","class":""})
     * @Form\Options({
     *     "label": "Date of infringement",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     */
    public $infringementDate = null;

    /**
     * @Form\Attributes({"id":"checkDate","placeholder":"","class":""})
     * @Form\Options({
     *     "label": "Date of check",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     */
    public $checkDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Member state",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country",
     *     "category": "isMemberState",
     *     "use_groups": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $memberStateCode = null;
}
