<?php

namespace Common\Form\Elements\Custom;

use Common\Form\Elements\Validators\EcmtNoOfPermitsCombinedTotalValidator;
use Laminas\Form\Element\Hidden;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Callback;

class EcmtNoOfPermitsCombinedTotalElement extends Hidden implements InputProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @return ((((mixed|string)[]|\Closure)[]|string|true)[][]|null|string|true)[]
     *
     * @psalm-return array{name: null|string, continue_if_empty: true, validators: list{array{name: Callback::class, options: array{callback: \Closure(mixed, array, int):bool, callbackOptions: list{mixed}, messages: array{callbackValue: 'qanda.ecmt.number-of-permits.error.total-max-exceeded'}}, break_chain_on_failure: true}, array{name: Callback::class, options: array{callback: \Closure(mixed, array):bool, messages: array{callbackValue: 'qanda.ecmt.number-of-permits.error.total-min-exceeded'}}}}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => Callback::class,
                    'options' => [
                        'callback' => static fn($value, array $context, int $maxValue): bool => \Common\Form\Elements\Validators\EcmtNoOfPermitsCombinedTotalValidator::validateMax($value, $context, $maxValue),
                        'callbackOptions' => [$this->options['maxPermitted']],
                        'messages' => [
                            Callback::INVALID_VALUE => 'qanda.ecmt.number-of-permits.error.total-max-exceeded'
                        ]
                    ],
                    'break_chain_on_failure' => true
                ],
                [
                    'name' => Callback::class,
                    'options' => [
                        'callback' => static fn($value, array $context): bool => \Common\Form\Elements\Validators\EcmtNoOfPermitsCombinedTotalValidator::validateMin($value, $context),
                        'messages' => [
                            Callback::INVALID_VALUE => 'qanda.ecmt.number-of-permits.error.total-min-exceeded'
                        ]
                    ]
                ],
            ],
        ];
    }
}
