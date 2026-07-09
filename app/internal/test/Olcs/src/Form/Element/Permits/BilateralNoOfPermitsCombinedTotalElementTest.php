<?php

declare(strict_types=1);

/**
 * BilateralNoOfPermitsCombinedTotalElement Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Form\Element\Permits;

use Olcs\Form\Element\Permits\BilateralNoOfPermitsCombinedTotalElement;
use Olcs\Form\Element\Permits\BilateralNoOfPermitsCombinedTotalValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Validator\Callback;

/**
 * BilateralNoOfPermitsCombinedTotalElement Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
final class BilateralNoOfPermitsCombinedTotalElementTest extends TestCase
{
    public function testGetInputSpecification(): void
    {
        $elementName = 'elementName';

        $bilateralNoOfPermitsCombinedTotalElement = new BilateralNoOfPermitsCombinedTotalElement($elementName);

        $inputSpecification = $bilateralNoOfPermitsCombinedTotalElement->getInputSpecification();

        // Verify the callback points at the expected validator method. Comparing the closure for
        // equality directly (via assertEquals on the whole array) is unreliable, so assert its
        // identity via reflection and then compare the remaining structure with a placeholder.
        $callback = $inputSpecification['validators'][0]['options']['callback'];
        $this->assertIsCallable($callback);
        $callbackReflection = new \ReflectionFunction($callback);
        $this->assertSame('validateNonZeroValuePresent', $callbackReflection->getName());
        $this->assertSame(
            BilateralNoOfPermitsCombinedTotalValidator::class,
            $callbackReflection->getClosureCalledClass()?->getName()
        );

        $inputSpecification['validators'][0]['options']['callback'] = 'callback';

        $expectedInputSpecification = [
            'name' => $elementName,
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => Callback::class,
                    'options' => [
                        'callback' => 'callback',
                        'messages' => [
                            Callback::INVALID_VALUE => 'Enter a number of permits in at least one field'
                        ]
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedInputSpecification, $inputSpecification);
    }
}
