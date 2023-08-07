<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("transport-manager-application-resend")
 * @Form\Attributes({"method":"post", "action":""})
 * @Form\Hydrator("Laminas\Stdlib\Hydrator\ArraySerializable")
 */
class TransportManagerApplicationResend
{
    /**
     * @Form\Attributes({"value":"transport-manager-application-resend"})
     * @Form\Type("Hidden")
     */
    public $formName;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "aria-label": "Confirm resend link to Transport Manger"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     * @Form\Options({
     *     "label": "resend-link"
     * })
     */
    protected $submit;
}
