<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post", "class":"form"})
 * @Form\Type("\Common\Form\Form")
 */
class CurrentDiscs
{
    /**
     * @Form\Options({
     *     "hint":"Select all options that are relevant to your discs."
     * })
     * @Form\Attributes({"value": "markup-licence-surrender-current-disc-form-header"})
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $header = null;

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
