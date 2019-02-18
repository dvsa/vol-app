<?php

namespace Olcs\Form\Model\Form\Licence\Surrender\Fieldset;

use Zend\Form\Annotation as Form;

class Actions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({"label": "Surrender"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $surrender = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary large js-modal-ajax"})
     * @Form\Options({
     *     "label": "Withdraw",
     *     "route": "licence/surrender-details/withdraw/GET"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionLink")
     */
    public $withdraw = null;
}
