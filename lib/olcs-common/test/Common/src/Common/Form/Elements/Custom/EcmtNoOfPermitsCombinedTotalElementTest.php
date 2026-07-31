<?php

declare(strict_types=1);

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
final class EcmtNoOfPermitsCombinedTotalElementTest extends MockeryTestCase
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
                        'messages' => [
                            Callback::INVALID_VALUE => 'qanda.ecmt.number-of-permits.error.total-min-exceeded'
                        ]
                    ]
                ],
            ],
        ];

        $ecmtNoOfPermitsCombinedTotalElement = new EcmtNoOfPermitsCombinedTotalElement($name);
        $ecmtNoOfPermitsCombinedTotalElement->setOption('maxPermitted', $maxPermitted);

        $actual = $ecmtNoOfPermitsCombinedTotalElement->getInputSpecification();

        // The callbacks are closures which cannot be reliably compared for equality;
        // assert they delegate to the validator, then compare the rest of the structure.
        $maxCallback = $actual['validators'][0]['options']['callback'];
        $this->assertIsCallable($maxCallback);
        $this->assertTrue($maxCallback('notused', ['euro5' => '10'], $maxPermitted));
        $this->assertFalse($maxCallback('notused', ['euro5' => '56'], $maxPermitted));
        unset($actual['validators'][0]['options']['callback']);

        $minCallback = $actual['validators'][1]['options']['callback'];
        $this->assertIsCallable($minCallback);
        $this->assertTrue($minCallback('notused', ['euro5' => '1']));
        $this->assertFalse($minCallback('notused', ['euro5' => '0']));
        unset($actual['validators'][1]['options']['callback']);

        $this->assertEquals($expectedInputSpecification, $actual);
    }
}
