<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("selectPermitType")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class SelectPermitType
{
    /**
     * @Form\Attributes({"id":"permitType"})
     * @Form\Options({
     *     "label": "Select Permit Type",
     *     "short-label": "Permit Type",
     *     "label_attributes": {"id": "label-permit-type"},
     *     "service_name": "Common\Service\Data\IrhpPermitType",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     */
    public $permitType = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ContinueCancelFormActions")
     */
    public $formActions = null;
}
