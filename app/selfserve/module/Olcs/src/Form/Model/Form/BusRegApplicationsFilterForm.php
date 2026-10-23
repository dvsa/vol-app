<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

#[Form\Name('bus-reg-filter-form')]
#[Form\Options(['prefer_form_input_filter' => true])]
#[Form\Attributes(['method' => 'post'])]
#[Form\Type('Common\Form\Form')]
class BusRegApplicationsFilterForm
{
    #[Form\Name('fields')]
    #[Form\ComposedObject('Olcs\Form\Model\Fieldset\BusRegApplicationsFilterFields')]
    public $fields = null;

    #[Form\Name('form-actions')]
    #[Form\Attributes(['class' => 'govuk-button-group'])]
    #[Form\ComposedObject('Olcs\Form\Model\Fieldset\SearchFormActions')]
    public $formActions = null;
}
