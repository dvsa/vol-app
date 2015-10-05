<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"actions-container"})
 */
class SearchFormActions
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({"label": "search.form.filter.update_button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $updateResults = null;
}
