<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class PermitFormActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large","id":"saveIrhpPermitApplication"})
     * @Form\Options({"label": "Save"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $save = null;


    /**
     * @Form\Attributes({"type":"submit","class":"action--secondary","id":"backToPermitList"})
     * @Form\Options({"label": "Back"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $back = null;
}
