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
class IrhpApplication
{
    /**
     * @Form\Name("topFields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrhpApplication\Top")
     */
    public $topFields = null;

    /**
     * @Form\Name("qa")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\QA")
     * @Form\Flags({"priority": -1})
     */
    public $qa = null;

    /**
     * @Form\Name("bottomFields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrhpApplication\Bottom")
     * @Form\Flags({"priority": -2})
     */
    public $bottomFields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\IrhpActions")
     * @Form\Flags({"priority": -3})
     */
    public $formActions = null;
}
