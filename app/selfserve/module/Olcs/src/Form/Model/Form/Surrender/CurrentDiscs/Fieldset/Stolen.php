<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Laminas\Form\Annotation as Form;

class Stolen
{
    #[Form\Type('OlcsCheckbox')]
    #[Form\Attributes(['class' => 'govuk-checkboxes__input'])]
    #[Form\Options(['label' => 'licence.surrender.operator_licence.stolen.label', 'label_attributes' => ['class' => 'govuk-label govuk-checkboxes__label'], 'checked_value' => 'Y', 'unchecked_value' => 'N'])]
    public $stolen = null;

    #[Form\ComposedObject('Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset\StolenInfo')]
    #[Form\Attributes(['class' => 'govuk-checkboxes__conditional', 'id' => 'stolenInfo'])]
    public $info = null;
}
