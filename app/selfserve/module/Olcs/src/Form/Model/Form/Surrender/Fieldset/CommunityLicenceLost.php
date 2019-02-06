<?php

namespace Olcs\Form\Model\Form\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("community-licence-lost")
 */
class CommunityLicenceLost
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
     *          "context_field": "communityLicenceDocument",
     *          "context_values": {"lost"},
     *          "inject_post_data" : "communityLicenceDocument->communityLicenceDocument",
     *          "validators": {
     *              {
     *                  "name": "StringLength",
     *                  "options": {
     *                      "min" : 0,
     *                      "max" : 500,
     *                       "break_chain_on_failure": true,
     *                       "messages" : {
     *                          "stringLengthTooShort": "licence.surrender.community_licence_lost.text_area.stringLengthTooShort",
     *                          "stringLengthTooLong": "licence.surrender.community_licence_lost.text_area.stringLengthTooLong",
     *                          "stringLengthInvalid": "licence.surrender.community_licence_lost.text_area.stringLengthTooShort",
     *                      }
     *                  }
     *              },
     *              {
     *                 "name": "NotEmpty",
     *                 "options":{
     *                    "messages": {
     *                          "isEmpty": "licence.surrender.community_licence_lost.text_area.empty"
     *                      }
     *                 }
     *              }
     *          },
     *
     *      }
     * })
     * @Form\Type("\Zend\Form\Element\Textarea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Attributes({
     *     "class" : "govuk-textarea",
     *     "rows" : "5"
     * })
     * @Form\Options({
     *     "hint": "licence.surrender.operator_licence_lost.text_area.hint",
     *
     * })
     */
    public $details = null;
}
