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
     *                      "messages" : {
     *                          "stringLengthTooShort": "licence.surrender.operator_licence_stolen.text_area.stringLengthTooShort",
     *                          "stringLengthTooLong": "licence.surrender.operator_licence_stolen.text_area.stringLengthTooLong",
     *                          "stringLengthInvalid": "licence.surrender.operator_licence_stolen.text_area.stringLengthTooShort",
     *                      }
     *                  }
     *              },
     *              {
     *                "name": "NotEmpty",
     *                "options":{
     *                "messages": {
     *                          "isEmpty": "licence.surrender.operator_licence_stolen.text_area.empty"
     *                         }
     *                    }
     *              }
     *          }
     *      }
     * })
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Type("\Zend\Form\Element\Textarea")
     * @Form\Attributes({
     *     "class" : "govuk-textarea",
     *     "rows" : "5"
     * })
     * @Form\Options({
     *     "hint": "licence.surrender.operator_licence_stolen.text_area.hint"
     * })
     */
    public $details = null;
}
