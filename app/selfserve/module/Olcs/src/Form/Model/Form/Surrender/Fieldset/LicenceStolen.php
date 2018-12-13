<?php

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
     * @Form\AllowEmpty(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Validator({
     *     "name": "ValidateIf",
     *      "options":{
     *          "context_field": "licenceDocument",
     *          "context_values": {"stolen"},
     *          "inject_post_data" : "operatorLicenceDocument->licenceDocument",
     *          "validators": {
     *              {
     *                  "name": "StringLength",
     *                  "options": {
     *                      "min" : 0,
     *                      "max" : 500,
     *                  }
     *              },
     *              {"name": "NotEmpty"}
     *          }
     *      }
     * })
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Filter({"name":"Zend\Filter\StringToLower"})
     * @Form\Type("\Zend\Form\Element\Textarea")
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