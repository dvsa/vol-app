<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-transport-managers")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class TransportManagers
{
    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\TableRequiredTransportManager")
     * @Form\Flags({"priority": -10})
     */
    public $table;

    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Flags({"priority": -20})
     */
    public $guidance;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\Flags({"priority": -30})
     */
    public $formActions;
}
