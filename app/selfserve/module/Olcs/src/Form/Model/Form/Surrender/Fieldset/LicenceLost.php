<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-lost")
 * @Form\Options({"prefer_form_input_filter":true})
 */
class LicenceLost
{
    /**
     * @Form\Options({
     *     "label":"licence.surrender.operator_licence.lost.note",
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice = "LicenceLost";

    /**
     * @Form\Required(true)
     * @Form\Type("\Zend\Form\Element\Textarea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Filter({"name":"Zend\Filter\StringToLower"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":1}})
     * @Form\Attributes({
     *     "class" : "govuk-textarea",
     *     "rows" : "5"
     * })
     * @Form\Options({
     *     "hint": "licence.surrender.operator_licence.text_area.hint"
     * })
     */
    public $details = null;
}
