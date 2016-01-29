<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("generate-actions")
 */
class GenerateActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large", "id": "submit"})
     * @Form\Options({
     *     "label": "Generate"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $generate = null;
}
