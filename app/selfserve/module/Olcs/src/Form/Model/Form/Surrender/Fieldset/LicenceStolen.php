<?php
/**
 * Created by PhpStorm.
 * User: parthvyas
 * Date: 06/12/2018
 * Time: 14:54
 */

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-stolen")
 */
class LicenceStolen
{
    /**
     * @Form\Options({
     *      "label": "licence.surrender.licence.stolen.note",
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $notice = "LicenceStolen";

    /**
     * @Form\Type("\Zend\Form\Element\Textarea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Filter({"name":"Zend\Filter\StringToLower"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":500}})
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