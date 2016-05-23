<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("bus-reg-filter-form")
 * @Form\Options({"label":"Filters"})
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class BusRegApplicationsFilterForm
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\BusRegApplicationsFilterFields")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\SearchFormActions")
     */
    public $formActions = null;
}
