<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class OkCancelFormActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({
     *     "label": "Ok",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $ok = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"cancel"})
     * @Form\Options({
     *     "label": "Cancel",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
