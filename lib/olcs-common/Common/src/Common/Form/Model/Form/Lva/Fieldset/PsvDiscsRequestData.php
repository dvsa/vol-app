<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lva-psv-discs-request-data")
 */
class PsvDiscsRequestData
{
    /**
     * @Form\Name("additionalDiscs")
     * @Form\Type("text")
     * @Form\Options({
     *     "label": "application_vehicle-safety_discs-psv-sub-action.additionalDiscs"
     * })
     * @Form\Validator("Digits")
     * @Form\Validator("GreaterThan", options={"min":0})
     */
    public $additionalDiscs;
}
