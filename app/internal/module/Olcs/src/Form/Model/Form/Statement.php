<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("statement")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Statement
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":"Statement Details"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\StatementDetails")
     */
    public $fields = null;

    /**
     * @Form\Name("requestorsAddress")
     * @Form\Options({"label":"Requestors Address"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\RequestorsAddress")
     */
    public $requestorsAddress = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
