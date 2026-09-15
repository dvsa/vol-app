<?php

declare(strict_types=1);

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
final class BilateralNoOfPermitsCombinedTotalValidatorTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpValidateNonZeroValuePresent')]
    public function testValidateNonZeroValuePresent(mixed $context, mixed $expected): void
    {
        $this->assertEquals(
            $expected,
            BilateralNoOfPermitsCombinedTotalValidator::validateNonZeroValuePresent('', $context)
        );
    }

    public static function dpValidateNonZeroValuePresent(): \Iterator
    {
        yield 'incorrectly named field contains valid value' => [
            [
                'something-else' => '4',
                'standard-journey_single' => '',
                'standard-journey_multiple' => '',
                'cabotage-journey_single' => '',
                'cabotage-journey_multiple' => '',
            ],
            false
        ];
        yield 'zero' => [
            [
                'something-else' => '',
                'standard-journey_single' => '0',
                'standard-journey_multiple' => ''
            ],
            false
        ];
        yield 'negative' => [
            [
                'something-else' => '',
                'standard-journey_single' => '0',
                'standard-journey_multiple' => ''
            ],
            false
        ];
        yield 'non numeric' => [
            [
                'something-else' => '',
                'standard-journey_single' => 'Cheese',
                'standard-journey_multiple' => ''
            ],
            false
        ];
        yield 'one valid value' => [
            [
                'something-else' => '',
                'standard-journey_single' => '5',
                'standard-journey_multiple' => ''
            ],
            true
        ];
        yield 'two valid values' => [
            [
                'something-else' => '',
                'standard-journey_single' => '5',
                'standard-journey_multiple' => '3'
            ],
            true
        ];
    }
}
