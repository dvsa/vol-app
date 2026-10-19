<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Name('hoursOfWeekContent')]
#[Form\Type('Laminas\Form\Fieldset')]
class HoursOfWeekContentRequired
{
    #[Form\Type('Text')]
    #[Form\Filter('Common\Filter\NullToFloat')]
    #[Form\Attributes(['class' => 'short', 'pattern' => '\d(\.)*', 'data-container-class' => 'inline-text'])]
    #[Form\Options(['label' => 'tm-review-responsibility-mon'])]
    #[Form\Validator('Laminas\I18n\Validator\IsFloat', options: ['messages' => ['notFloat' => 'Only numbers are allowed']])]
    #[Form\Validator('Between', options: ['min' => 0, 'max' => 24, 'messages' => ['notBetween' => "Mon must be between '%min%' and '%max%', inclusively"]])]
    #[Form\Validator('Common\Form\Elements\Validators\SumContext', options: ['min' => 0.1, 'messages' => ['belowMin' => 'transport-manager-hours-per-week-validation-message']])]
    public $hoursMon;

    #[Form\Filter('Common\Filter\NullToFloat')]
    #[Form\Attributes(['class' => 'short', 'pattern' => '\d(\.)*', 'data-container-class' => 'inline-text'])]
    #[Form\Options(['label' => 'tm-review-responsibility-tue'])]
    #[Form\Validator('Laminas\I18n\Validator\IsFloat', options: ['messages' => ['notFloat' => 'Only numbers are allowed']])]
    #[Form\Validator('Between', options: ['min' => 0, 'max' => 24, 'messages' => ['notBetween' => "Tue must be between '%min%' and '%max%', inclusively"]])]
    public $hoursTue;

    #[Form\Filter('Common\Filter\NullToFloat')]
    #[Form\Attributes(['class' => 'short', 'pattern' => '\d(\.)*', 'data-container-class' => 'inline-text'])]
    #[Form\Options(['label' => 'tm-review-responsibility-wed'])]
    #[Form\Validator('Laminas\I18n\Validator\IsFloat', options: ['messages' => ['notFloat' => 'Only numbers are allowed']])]
    #[Form\Validator('Between', options: ['min' => 0, 'max' => 24, 'messages' => ['notBetween' => "Wed must be between '%min%' and '%max%', inclusively"]])]
    public $hoursWed;

    #[Form\Filter('Common\Filter\NullToFloat')]
    #[Form\Attributes(['class' => 'short', 'pattern' => '\d(\.)*', 'data-container-class' => 'inline-text'])]
    #[Form\Options(['label' => 'tm-review-responsibility-thu'])]
    #[Form\Validator('Laminas\I18n\Validator\IsFloat', options: ['messages' => ['notFloat' => 'Only numbers are allowed']])]
    #[Form\Validator('Between', options: ['min' => 0, 'max' => 24, 'messages' => ['notBetween' => "Thu must be between '%min%' and '%max%', inclusively"]])]
    public $hoursThu;

    #[Form\Filter('Common\Filter\NullToFloat')]
    #[Form\Attributes(['class' => 'short', 'pattern' => '\d(\.)*', 'data-container-class' => 'inline-text'])]
    #[Form\Options(['label' => 'tm-review-responsibility-fri'])]
    #[Form\Validator('Laminas\I18n\Validator\IsFloat', options: ['messages' => ['notFloat' => 'Only numbers are allowed']])]
    #[Form\Validator('Between', options: ['min' => 0, 'max' => 24, 'messages' => ['notBetween' => "Fri must be between '%min%' and '%max%', inclusively"]])]
    public $hoursFri;

    #[Form\Filter('Common\Filter\NullToFloat')]
    #[Form\Attributes(['class' => 'short', 'pattern' => '\d(\.)*', 'data-container-class' => 'inline-text'])]
    #[Form\Options(['label' => 'tm-review-responsibility-sat'])]
    #[Form\Validator('Laminas\I18n\Validator\IsFloat', options: ['messages' => ['notFloat' => 'Only numbers are allowed']])]
    #[Form\Validator('Between', options: ['min' => 0, 'max' => 24, 'messages' => ['notBetween' => "Sat must be between '%min%' and '%max%', inclusively"]])]
    public $hoursSat;

    #[Form\Filter('Common\Filter\NullToFloat')]
    #[Form\Attributes(['class' => 'short', 'pattern' => '\d(\.)*', 'data-container-class' => 'inline-text'])]
    #[Form\Options(['label' => 'tm-review-responsibility-sun'])]
    #[Form\Validator('Laminas\I18n\Validator\IsFloat', options: ['messages' => ['notFloat' => 'Only numbers are allowed']])]
    #[Form\Validator('Between', options: ['min' => 0, 'max' => 24, 'messages' => ['notBetween' => "Sun must be between '%min%' and '%max%', inclusively"]])]
    public $hoursSun;
}
