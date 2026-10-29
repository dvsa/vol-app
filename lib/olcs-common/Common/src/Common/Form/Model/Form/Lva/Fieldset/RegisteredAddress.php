<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Name('registered-address')]
#[Form\Type('\Laminas\Form\Fieldset')]
#[Form\Options(['label' => 'Registered address'])]
#[Form\Attributes(['class' => 'address'])]
class RegisteredAddress
{
    #[Form\Attributes(['value' => ''])]
    #[Form\Type('Hidden')]
    public $id;

    #[Form\Attributes(['value' => ''])]
    #[Form\Type('Hidden')]
    public $version;

    #[Form\Attributes(['class' => 'long', 'id' => 'addressLine1', 'data-container-class' => 'compound'])]
    #[Form\Options(['label' => 'lva.registered-address.address-line-1', 'error-message' => 'registeredAddress_addressLine1-error'])]
    #[Form\Type('Text')]
    #[Form\Filter('Laminas\Filter\StringTrim')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['min' => 0, 'max' => 200])]
    public $addressLine1;

    #[Form\Attributes(['class' => 'long', 'id' => '', 'data-container-class' => 'compound'])]
    #[Form\Options(['label' => 'lva.registered-address.address-line-2'])]
    #[Form\Required(false)]
    #[Form\Type('Text')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['min' => 0, 'max' => 200])]
    public $addressLine2;

    #[Form\Attributes(['class' => 'long', 'id' => '', 'data-container-class' => 'compound'])]
    #[Form\Options(['label' => 'lva.registered-address.address-line-3'])]
    #[Form\Required(false)]
    #[Form\Type('Text')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['min' => 0, 'max' => 200])]
    public $addressLine3;

    #[Form\Attributes(['class' => 'long', 'id' => '', 'data-container-class' => 'compound'])]
    #[Form\Options(['label' => 'lva.registered-address.address-line-4'])]
    #[Form\Required(false)]
    #[Form\Type('Text')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['min' => 0, 'max' => 200])]
    public $addressLine4;

    #[Form\Attributes(['class' => 'long', 'id' => ''])]
    #[Form\Options(['label' => 'lva.registered-address.town-city'])]
    #[Form\Required(false)]
    #[Form\Type('Text')]
    #[Form\Validator('Laminas\Validator\StringLength', options: ['min' => 0, 'max' => 200])]
    public $town;

    #[Form\Attributes(['id' => ''])]
    #[Form\Options(['label' => 'lva.registered-address.postcode'])]
    #[Form\Type('Text')]
    #[Form\Required(false)]
    #[Form\Filter('Dvsa\Olcs\Transfer\Filter\Postcode')]
    #[Form\Validator('Dvsa\Olcs\Transfer\Validators\Postcode')]
    public $postcode;
}
