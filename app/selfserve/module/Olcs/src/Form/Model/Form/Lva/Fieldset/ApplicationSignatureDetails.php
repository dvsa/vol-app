<?php

namespace Olcs\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Class ApplicationSignatureDetails
 */
class ApplicationSignatureDetails
{
    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Attributes({"data-container-class": "verify"})
     */
    public $signature = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "id": "submitAndPay"
     * })
     * @Form\Options({"label": "submitandpay.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submitAndPay = null;
}
