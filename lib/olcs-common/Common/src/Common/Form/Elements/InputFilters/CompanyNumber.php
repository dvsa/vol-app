<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\I18n\Validator\Alnum;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\StringLength;

/**
 * Company Number
 *
 * @author Someone <someone@valtech.co.uk>
 */
class CompanyNumber extends \Laminas\Form\Element implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     *
     * @return (((int|string[])[]|string)[][]|null|string|true)[]
     *
     * @psalm-return array{name: null|string, required: true, filters: list{array{name: \Laminas\Filter\StringTrim::class}}, validators: list{array{name: StringLength::class, options: array{min: 1, max: 8, messages: array{stringLengthTooLong: 'common.form.validation.company_number.too_long'}}}, array{name: 'Alnum', options: array{messages: array{notAlnum: 'common.form.validation.company_number.not_alnum'}}}}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'filters' => [
                ['name' => \Laminas\Filter\StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => \Laminas\Validator\StringLength::class,
                    'options' => [
                        'min' => 1,
                        'max' => 8,
                        'messages' => [
                            StringLength::TOO_LONG => 'common.form.validation.company_number.too_long',
                        ]
                    ]
                ],
                [
                    'name' => 'Alnum',
                    'options' => [
                        'messages' => [
                             Alnum::NOT_ALNUM => 'common.form.validation.company_number.not_alnum',
                        ],
                    ],
                ]
            ]
        ];
    }
}
