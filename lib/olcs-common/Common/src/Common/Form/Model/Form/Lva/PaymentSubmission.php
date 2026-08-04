<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-payment-submission")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class PaymentSubmission
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $amount;

    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $description;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label": "submit-pay.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submitPay;
}
