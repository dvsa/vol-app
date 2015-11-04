<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"actions-container"})
 * @Form\Name("fee-actions")
 */
class FeeActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"recommend"})
     * @Form\Options({
     *     "label": "Recommend waive",
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
     *     "label": "Approve waive",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $approve = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"reject"})
     * @Form\Options({
     *     "label": "Reject waive",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $reject = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"refund"})
     * @Form\Options({
     *     "label": "Refund fee",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-10"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $refund = null;

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
