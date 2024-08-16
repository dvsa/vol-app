<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Laminas\Form\Annotation as Form;

class InPossession
{
    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"govuk-checkboxes__item"})
     * @Form\Options({
     *     "label":"licence.surrender.current_discs.in_possession.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     * })
     */
    public $inPossession = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset\InPossessionInfo")
     * @Form\Attributes({
     *     "class":"govuk-checkboxes__conditional",
     *     "id":"possessionInfo"
     * })
     */
    public $info = null;
}
