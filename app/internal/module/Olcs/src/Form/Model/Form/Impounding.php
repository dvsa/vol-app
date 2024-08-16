<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Impounding")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Impounding
{
    /**
     * @Form\Name("base")
     * @Form\Attributes({"class":"base"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CaseBase")
     */
    public $caseBase = null;

    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ImpoundingFields")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\PublishActions")
     */
    public $formActions = null;
}
