<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-convictions-penalties")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class ConvictionsPenalties
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesData")
     */
    public $data;

    /**
     * @Form\Name("convictionsConfirmation")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesConfirmation")
     */
    public $convictionsConfirmation;

    /**
     * @Form\Name("convictionsReadMoreLink")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\ConvictionsPenaltiesReadMoreLink")
     */
    public $convictionsReadMoreLink;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
