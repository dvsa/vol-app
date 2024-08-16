<?php

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
class BilateralNoOfPermitsCombinedTotalElementTest extends TestCase
{
    public function testGetInputSpecification()
    {
        $elementName = 'elementName';

        $expectedInputSpecification = [
            'name' => $elementName,
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => Callback::class,
                    'options' => [
                        'callback' => [
                            BilateralNoOfPermitsCombinedTotalValidator::class,
                            'validateNonZeroValuePresent'
                        ],
                        'messages' => [
                            Callback::INVALID_VALUE => 'Enter a number of permits in at least one field'
                        ]
                    ],
                ],
            ],
        ];

        $bilateralNoOfPermitsCombinedTotalElement = new BilateralNoOfPermitsCombinedTotalElement($elementName);

        $this->assertEquals(
            $expectedInputSpecification,
            $bilateralNoOfPermitsCombinedTotalElement->getInputSpecification()
        );
    }
}
