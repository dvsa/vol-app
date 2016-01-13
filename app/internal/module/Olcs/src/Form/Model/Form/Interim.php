<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("interim")
 * @Form\Attributes({"method":"post"})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Interim
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Name("requested")
     * @Form\Options({"label":"","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\InterimRequested")
     */
    public $requested = null;

    /**
     * @Form\Name("data")
     * @Form\Options({"label":"","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\InterimDetails")
     */
    public $data = null;

    /**
     * @Form\Name("operatingCentres")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $operatingCentres = null;

    /**
     * @Form\Name("vehicles")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $vehicles = null;

    /**
     * @Form\Name("interimStatus")
     * @Form\Options({"label":"","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\InterimStatus")
     */
    public $interimStatus = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\InterimActions")
     */
    public $formActions = null;
}
