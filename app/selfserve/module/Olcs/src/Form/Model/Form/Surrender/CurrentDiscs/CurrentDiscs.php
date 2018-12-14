<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post", "class":"form", "id":"surrender-current-discs"})
 * @Form\Type("\Common\Form\Form")
 */
class CurrentDiscs
{

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset\Header")
     */
    public $headerSection = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset\InPossession")
     */
    public $possessionSection = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset\Lost")
     */
    public $lostSection = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset\Stolen")
     */
    public $stolenSection = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({"label":"Continue"})
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $submit = null;
}
