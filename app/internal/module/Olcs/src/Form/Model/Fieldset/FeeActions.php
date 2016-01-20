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
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"pay"})
     * @Form\Options({"label": "Pay"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $pay = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"recommend"})
     * @Form\Options({"label": "Recommend waive"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $recommend = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"approve"})
     * @Form\Options({"label": "Approve waive"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $approve = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"reject"})
     * @Form\Options({"label": "Reject waive"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $reject = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary","id":"refund"})
     * @Form\Options({"label": "Refund fee"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $refund = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"cancel"})
     * @Form\Options({"label": "Back"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $cancel = null;
}
