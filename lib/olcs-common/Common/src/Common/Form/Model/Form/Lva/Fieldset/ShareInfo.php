<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Name('share-info')]
class ShareInfo
{
    #[Form\Options(['checked_value' => 'Y', 'unchecked_value' => 'N', 'label' => 'licence.vehicles-trailers.share-info', 'label_attributes' => ['class' => 'govuk-label govuk-checkboxes__label', 'id' => 'label-shareInfo']])]
    #[Form\Type('\Common\Form\Elements\InputFilters\SingleCheckbox')]
    public $shareInfo;
}
