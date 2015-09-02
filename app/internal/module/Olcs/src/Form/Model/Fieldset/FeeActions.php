<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("fee-actions")
 */
class FeeActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"recommend"})
     * @Form\Options({
     *     "label": "Recommend Waive",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $recommend = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"approve"})
     * @Form\Options({
     *     "label": "Approve Waive",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $approve = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"reject"})
     * @Form\Options({
     *     "label": "Reject Waive",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $reject = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"cancel"})
     * @Form\Options({
     *     "label": "Back",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
