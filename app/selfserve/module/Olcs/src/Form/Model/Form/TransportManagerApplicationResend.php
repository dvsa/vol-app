<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("transport-manager-application-resend")
 * @Form\Attributes({"method":"post", "action":""})
 * @Form\Hydrator("Zend\Stdlib\Hydrator\ArraySerializable")
 */
class TransportManagerApplicationResend
{
    /**
     * @Form\Attributes({"id":"emailAddress","placeholder":"","class":"medium", "readonly":"readonly"})
     * @Form\Options({"label":"transport-managers-email-address"})
     * @Form\Type("Text")
     */
    protected $emailAddress;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     * @Form\Options({
     *     "label": "resend-link"
     * })
     */
    protected $submit;
}
