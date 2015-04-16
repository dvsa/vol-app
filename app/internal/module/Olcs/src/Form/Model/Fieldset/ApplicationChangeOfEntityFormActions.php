<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class ApplicationChangeOfEntityFormActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"submit"})
     * @Form\Options({
     *     "label": "save",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large","id":"cancel"})
     * @Form\Options({
     *     "label": "cancel",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"remove"})
     * @Form\Options({
     *     "label": "licence-status.remove",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $remove = null;
}
