<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;

/**
 * @codeCoverageIgnore No methods
 */
class ReplacementDetails
{
    use IdTrait;

    /**
     * @Form\Type("Text")
     * @Form\Name("placeholder")
     * @Form\Attributes({"id":"placeholder","class":"medium"})
     * @Form\Options({
     *     "label": "Placeholder",
     *     "required": true
     * })
     */
    public $placeholder = null;

    /**
     * @Form\Type("Text")
     * @Form\Name("replacementText")
     * @Form\Attributes({"id":"replacementText","class":"medium"})
     * @Form\Options({
     *     "label": "Replacement Text",
     *     "required": true
     * })
     * @Form\Required(false)
     */
    public $replacementText = null;
}
