<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Zend\Form\Annotation as Form;

class InPossessionInfo
{
    /**
     * @Form\Type("Number")
     * @Form\Options({
     *     "label":"Number of discs you will destroy",
     * })
     * @Form\Attributes({
     *      "class":"govuk-input govuk-!-width-one-third"
     * })
     */
    public $numberDestroy = null;
}
