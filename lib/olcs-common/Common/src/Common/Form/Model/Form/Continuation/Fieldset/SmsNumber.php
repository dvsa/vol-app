<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Name('smsNumber')]
class SmsNumber
{
    #[Form\Attributes(['class' => 'medium', 'id' => 'smsNumber'])]
    #[Form\Type('\Common\Form\Elements\InputFilters\SmsPhone')]
    public $smsNumber;
}
