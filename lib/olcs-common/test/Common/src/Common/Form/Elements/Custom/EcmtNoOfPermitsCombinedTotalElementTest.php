<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\EcmtNoOfPermitsCombinedTotalElement;
use Common\Form\Elements\Validators\EcmtNoOfPermitsCombinedTotalValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Validator\Callback;

/**
 * EcmtNoOfPermitsCombinedTotalElementTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtNoOfPermitsCombinedTotalElementTest extends MockeryTestCase
{
    public function testGetInputSpecification(): void
    {
        $name = 'combinedTotalChecker';
        $maxPermitted = 55;

        $expectedInputSpecification = [
            'name' => $name,
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => Callback::class,
                    'options' => [
                        'callback' => static fn($value, array $context, int $maxValue): bool => \Common\Form\Elements\Validators\EcmtNoOfPermitsCombinedTotalValidator::validateMax($value, $context, $maxValue),
                        'callbackOptions' => [$maxPermitted],
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

        $ecmtNoOfPermitsCombinedTotalElement = new EcmtNoOfPermitsCombinedTotalElement($name);
        $ecmtNoOfPermitsCombinedTotalElement->setOption('maxPermitted', $maxPermitted);

        $this->assertEquals(
            $expectedInputSpecification,
            $ecmtNoOfPermitsCombinedTotalElement->getInputSpecification()
        );
    }
}
