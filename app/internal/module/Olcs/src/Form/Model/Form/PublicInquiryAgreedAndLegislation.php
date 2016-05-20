<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("traffic-commissioner-agreement-legislation")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({
 *     "prefer_form_input_filter": true
 * })
 */
class PublicInquiryAgreedAndLegislation
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\PublicInquiryAgreedAndLegislationMain")
     */
    public $fields;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
