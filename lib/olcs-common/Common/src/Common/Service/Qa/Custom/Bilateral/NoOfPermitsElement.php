<?php

namespace Common\Service\Qa\Custom\Bilateral;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\StringLength;

class NoOfPermitsElement extends Text implements InputProviderInterface
{
    public const MAX_LENGTH = '4';

    public const ERROR_KEY = 'qanda.bilaterals.number-of-permits.error.enter-permits-required';

    protected $attributes = [
        'maxLength' => self::MAX_LENGTH
    ];

    /**
     * {@inheritdoc}
     *
     * @return (((bool|int|string|string[])[]|string)[][]|bool|null|string)[]
     *
     * @psalm-return array{name: null|string, required: false, continue_if_empty: true, filters: list{array{name: StringTrim::class}}, validators: list{array{name: StringLength::class, options: array{min: 1, max: '4', break_chain_on_failure: true, messages: array{stringLengthTooShort: 'qanda.bilaterals.number-of-permits.error.enter-permits-required', stringLengthTooLong: 'qanda.bilaterals.number-of-permits.error.enter-permits-required'}}}, array{name: Digits::class, options: array{break_chain_on_failure: true, messages: array{notDigits: 'qanda.bilaterals.number-of-permits.error.enter-permits-required'}}}, array{name: GreaterThan::class, options: array{min: 0, inclusive: false, messages: array{notGreaterThan: 'qanda.bilaterals.number-of-permits.error.enter-permits-required'}}}}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'continue_if_empty' => true,
            'filters' => [
                [
                    'name' => StringTrim::class
                ]
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 1,
                        'max' => self::MAX_LENGTH,
                        'break_chain_on_failure' => true,
                        'messages' => [
                            StringLength::TOO_SHORT => self::ERROR_KEY,
                            StringLength::TOO_LONG => self::ERROR_KEY
                        ]
                    ]
                ],
                [
                    'name' => Digits::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            Digits::NOT_DIGITS => self::ERROR_KEY
                        ]
                    ]
                ],
                [
                    'name' => GreaterThan::class,
                    'options' => [
                        'min' => 0,
                        'inclusive' => false,
                        'messages' => [
                            GreaterThan::NOT_GREATER => self::ERROR_KEY
                        ]
                    ]
                ]
            ],
        ];
    }
}
