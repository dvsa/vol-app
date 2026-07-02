<?php

namespace Common\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("tm-convictions-and-penalties")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "action_lcfirst": true})
 */
class TmConvictionsAndPenalties
{
    /**
     * @Form\Name("tm-convictions-and-penalties-details")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\ConvictionsAndPenaltiesDetails")
     */
    public $tmConvictionsAndPenaltiesDetails;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     */
    public $formActions;
}
