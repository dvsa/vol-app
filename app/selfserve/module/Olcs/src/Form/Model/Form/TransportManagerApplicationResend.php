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
     * @Form\Attributes({"id":"emailAddress","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "transport-managers-email-address",
     *     "label_attributes": {
     *         "aria-label": "Enter Transport Manager's email address"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     */
    protected $emailAddress;

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
