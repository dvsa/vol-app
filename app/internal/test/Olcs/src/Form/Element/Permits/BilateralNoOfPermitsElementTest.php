<?php

/**
 * BilateralNoOfPermitsElement Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Form\Element\Permits;

use Olcs\Form\Element\Permits\BilateralNoOfPermitsElement;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Filter\StringTrim;

/**
 * BilateralNoOfPermitsElement Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
class BilateralNoOfPermitsElementTest extends TestCase
{
    public function testGetInputSpecification()
    {
        $elementName = 'elementName';

        $expectedInputSpecification =  [
            'name' => $elementName,
            'required' => false,
            'continue_if_empty' => true,
            'filters' => [
                [
                    'name' => StringTrim::class
                ]
            ],
        ];

        $bilateralNoOfPermitsElement = new BilateralNoOfPermitsElement($elementName);

        $this->assertEquals(
            $expectedInputSpecification,
            $bilateralNoOfPermitsElement->getInputSpecification()
        );
    }
}
