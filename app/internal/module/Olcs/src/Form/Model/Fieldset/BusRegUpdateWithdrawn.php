<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
#[Form\Name('BusRegUpdateWithdrawn')]
class BusRegUpdateWithdrawn extends Base
{
    #[Form\Attributes(['value' => ''])]
    #[Form\Type('Hidden')]
    public $status = null;

    #[Form\Options(['label' => 'Reason', 'category' => 'withdrawn_reason'])]
    #[Form\Type('DynamicRadio')]
    #[Form\Validator('Laminas\Validator\NotEmpty')]
    public $reason = null;
}
