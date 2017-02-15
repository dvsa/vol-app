<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("qualification-details")
 */
class QualificationDetails
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
     * @Form\Attributes({
     *      "id":"qualificationType",
     *      "placeholder":"",
     *      "class":"small",
     * })
     * @Form\Options({
     *     "label": "transport-manager.competences.form.qualification-type",
     *     "disable_inarray_validator": false,
     *     "category": "tm_qual_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $qualificationType = null;

    /**
     * @Form\Attributes({"class":"long","id":"serialNo"})
     * @Form\Options({"label":"transport-manager.competences.form.serial"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $serialNo = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"issuedDate","required":false})
     * @Form\Options({
     *     "label": "transport-manager.competences.form.date-of-issue",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     * @Form\Validator({"name":"\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $issuedDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "transport-manager.competences.form.country",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $countryCode = null;
}
