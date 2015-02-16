<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

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
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TmConvictionsAndPenaltiesDetails")
     */
    public $tmConvictionsAndPenaltiesDetails = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
