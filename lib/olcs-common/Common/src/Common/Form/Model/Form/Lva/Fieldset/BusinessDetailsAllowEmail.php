<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Business details allowEmail fieldset
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusinessDetailsAllowEmail
{
    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"js-enabled"})
     * @Form\Options({
     *     "label": "application_business-details_allow-email.label",
     *     "value_options": {"N": "Post", "Y": "Email"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $allowEmail;
}
