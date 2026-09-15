<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Name("is-longer-semi-trailer")
 * @Form\Options({
 *     "radio-element":"isLongerSemiTrailer"
 * })
 */
class IsLongerSemiTrailer
{
    /**
     * @Form\Options({
     *      "error-message": "is-longer-semi-trailer-error",
     *      "value_options": {
     *          "Y": "Yes",
     *          "N": "No",
     *      }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $isLongerSemiTrailer;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Form\Lva\Fieldset\LongerSemiTrailerWarning")
     */
    public $YContent;
}
