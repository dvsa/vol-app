<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("irhpBilateral")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrhpBilateral
{
    /**
     * @Form\Name("topFields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrhpBilateral\Top")
     */
    public $topFields = null;

    /**
     * @Form\Name("bottomFields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrhpBilateral\Bottom")
     * @Form\Flags({"priority": -1})
     */
    public $bottomFields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\PermitFormActions")
     * @Form\Flags({"priority": -2})
     */
    public $formActions = null;
}
