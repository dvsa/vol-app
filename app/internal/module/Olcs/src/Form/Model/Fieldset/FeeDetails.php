<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("fee-details")
 */
class FeeDetails
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Options({
     *     "checked_value": "Y",
     *     "unchecked_value":"N",
     *     "label": "Waive fee remainder?"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $waiveRemainder;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Waive note"})
     * @Form\Type("\Common\Form\Elements\InputFilters\FeeWaiveNote")
     */
    public $waiveReason = null;
}
