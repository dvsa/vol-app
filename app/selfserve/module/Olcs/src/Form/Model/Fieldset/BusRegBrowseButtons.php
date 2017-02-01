<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class BusRegBrowseButtons
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({"label": "selfserve.search.busreg.browse.form.submit.label"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    protected $submit;

    /**
     * @Form\Attributes({"type":"submit","class":"action--tertiary large"})
     * @Form\Options({"label": "selfserve.search.busreg.browse.form.export.label"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    protected $export;
}
