<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("irhpApplication")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\BaseQaForm")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class IrhpCandidatePermit
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrhpCandidatePermit")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrhpActions")
     * @Form\Flags({"priority": -3})
     */
    public $formActions = null;
}
