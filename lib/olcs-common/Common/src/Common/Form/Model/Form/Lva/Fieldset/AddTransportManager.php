<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Add Transport Manager fieldset
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddTransportManager
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "transport-manager-choose"
     * })
     * @Form\Type("Select")
     */
    public $registeredUser;

    /**
     * @Form\Attributes({"id":"addUser","type":"submit","class":"govuk-button"})
     * @Form\Options({"label": "transport-manager-add"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $addUser;
}
