<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;
use Common\Service\Helper\TranslationHelperService;

class AuthorityToOperate
{
    /**
     * @Form\Attributes({
     *     "value": "interim.application.undertakings.form.textarea.placeholder",
     *     "data-container-class": "typeOfLicence-guidance-restricted js-visible",
     *     "id": "application-interim-reason",
     * })
     * @Form\Options({"tokens":{"application_type-of-licence_licence-type.data.restrictedGuidance"}})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $interimGuidanceText;

    /**
     * @Form\Required(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\AllowEmpty(true)
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *     "required": false,
     *     "id": "applicationInterimReason",
     *     "class": "long js-interim-reason",
     *     "data-container-class": "interimFee",
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "goodsApplicationInterim",
     *          "context_values": {"Y"},
     *          "inject_post_data": "interim->goodsApplicationInterim",
     *          "validators": {
     *              {
     *                  "name": "NotEmpty",
     *                  "options": {
     *                      "messages": {"isEmpty" : "Value is required and can't be empty"},
     *                  }
     *              }
     *          }
     *      }
     * )
     */
    public $goodsApplicationInterimReason;

    /**
     * @Form\Attributes({"value": "markup-interim-fee","data-container-class": "interimFee", "id" : "interimFee"})
     * @Form\Type("\Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $interimFee;
}
