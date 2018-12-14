<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Zend\Form\Annotation as Form;

class Lost
{

    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"class":"govuk-checkboxes__item"})
     * @Form\Options({
     *     "label":"Lost",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     * })
     */
    public $lost = null;

    /**
     * @Form\ComposedObject("Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset\LostInfo")
     * @Form\Attributes({
     *     "class":"govuk-checkboxes__conditional",
     *     "id":"lostInfo"
     * })
     */
    public $info = null;
}
