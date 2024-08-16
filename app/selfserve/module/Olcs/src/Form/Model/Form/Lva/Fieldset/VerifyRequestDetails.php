<?php

namespace Olcs\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Class VerifyRequestDetails
 */
class VerifyRequestDetails
{
    /**
     * @Form\Attributes({"value": "undertakings_redirect_to_verify"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $signature = null;
}
