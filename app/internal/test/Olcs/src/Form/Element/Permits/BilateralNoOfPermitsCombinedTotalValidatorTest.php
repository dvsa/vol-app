<?php

/**
 * BilateralNoOfPermitsCombinedTotalValidator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Form\Element\Permits;

use Olcs\Form\Element\Permits\BilateralNoOfPermitsCombinedTotalValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * BilateralNoOfPermitsCombinedTotalValidator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class BilateralNoOfPermitsCombinedTotalValidatorTest extends TestCase
{
    /**
     * @dataProvider dpValidateNonZeroValuePresent
     */
    public function testValidateNonZeroValuePresent($context, $expected)
    {
        $this->assertEquals(
            $expected,
            BilateralNoOfPermitsCombinedTotalValidator::validateNonZeroValuePresent('', $context)
        );
    }

    public function dpValidateNonZeroValuePresent()
    {
        return [
            'incorrectly named field contains valid value' => [
                [
                    'something-else' => '4',
                    'standard-journey_single' => '',
                    'standard-journey_multiple' => '',
                    'cabotage-journey_single' => '',
                    'cabotage-journey_multiple' => '',
                ],
                false
            ],
            'zero' => [
                [
                    'something-else' => '',
                    'standard-journey_single' => '0',
                    'standard-journey_multiple' => ''
                ],
                false
            ],
            'negative' => [
                [
                    'something-else' => '',
                    'standard-journey_single' => '0',
                    'standard-journey_multiple' => ''
                ],
                false
            ],
            'non numeric' => [
                [
                    'something-else' => '',
                    'standard-journey_single' => 'Cheese',
                    'standard-journey_multiple' => ''
                ],
                false
            ],
            'one valid value' => [
                [
                    'something-else' => '',
                    'standard-journey_single' => '5',
                    'standard-journey_multiple' => ''
                ],
                true
            ],
            'two valid values' => [
                [
                    'something-else' => '',
                    'standard-journey_single' => '5',
                    'standard-journey_multiple' => '3'
                ],
                true
            ]
        ];
    }
}
