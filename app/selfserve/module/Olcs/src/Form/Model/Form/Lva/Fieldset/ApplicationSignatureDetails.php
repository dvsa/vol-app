<?php

namespace Olcs\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

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
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"submitAndPay"})
     * @Form\Options({"label": "submitandpay.button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submitAndPay = null;
}
