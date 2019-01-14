<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-lost")
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
     * @Form\AllowEmpty(true)
     * @Form\ContinueIfEmpty(true)
     * @Form\Validator({
     *     "name": "ValidateIf",
     *      "options":{
     *          "context_field": "licenceDocument",
     *          "context_values": {"lost"},
     *          "inject_post_data" : "operatorLicenceDocument->licenceDocument",
     *          "validators": {
     *              {
     *                  "name": "StringLength",
     *                  "options": {
     *                      "min" : 0,
     *                      "max" : 500,
     *                      "break_chain_on_failure": true,
     *                      "messages" : {
     *                          "stringLengthTooShort": "licence.surrender.operator_licence_lost.text_area.stringLengthTooShort",
     *                          "stringLengthTooLong": "licence.surrender.operator_licence_lost.text_area.stringLengthTooLong",
     *                          "stringLengthInvalid": "licence.surrender.operator_licence_lost.text_area.stringLengthToShort",
     *                      }
     *                  }
     *              },
     *              {
     *               "name": "NotEmpty",
     *                 "options":{
     *                "messages": {
     *                          "isEmpty": "licence.surrender.operator_licence_lost.text_area.empty"
     *                    }
     *                }
     *            }
     *        }
     *     }
     * })
     * @Form\Type("\Zend\Form\Element\Textarea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Attributes({
     *     "class" : "govuk-textarea",
     *     "rows" : "5"
     * })
     * @Form\Options({
     *     "hint": "licence.surrender.operator_licence_lost.text_area.hint"
     * })
     */
    public $details = null;
}
