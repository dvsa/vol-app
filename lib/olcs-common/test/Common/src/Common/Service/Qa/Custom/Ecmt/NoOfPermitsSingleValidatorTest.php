<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\NoOfPermitsSingleValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsSingleValidatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class NoOfPermitsSingleValidatorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
    public function testIsValid(
        $value,
        $permitsRemaining,
        $maxPermitted,
        $emissionsCategory,
        $expectedMessages,
        $expectedIsValid
    ): void {
        $options = [
            'permitsRemaining' => $permitsRemaining,
            'maxPermitted' => $maxPermitted,
            'emissionsCategory' => $emissionsCategory,
        ];

        $noOfPermitsSingleValidator = new NoOfPermitsSingleValidator($options);

        $this->assertEquals(
            $expectedIsValid,
            $noOfPermitsSingleValidator->isValid($value)
        );

        $this->assertEquals(
            $expectedMessages,
            $noOfPermitsSingleValidator->getMessages()
        );
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | bool | int | string)>>
     *
     * @psalm-return array{'permits remaining less than max permitted, valid': list{'6', 6, 7, 'euro5', array<never, never>, true}, 'permits remaining less than max permitted, not valid (euro5)': list{'7', 6, 7, 'euro5', array{permitsRemainingThreshold: 'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.euro5'}, false}, 'permits remaining less than max permitted, not valid (euro6)': list{'7', 6, 7, 'euro6', array{permitsRemainingThreshold: 'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.euro6'}, false}, 'max permitted less than permits remaining, valid': list{'6', 7, 6, 'euro5', array<never, never>, true}, 'max permitted less than permits remaining, not valid': list{'7', 7, 6, 'euro5', array{maxPermittedThreshold: 'qanda.ecmt.number-of-permits.error.total-max-exceeded'}, false}}
     */
    public static function dpIsValid(): \Iterator
    {
        yield 'permits remaining less than max permitted, valid' => [
            '6',
            6,
            7,
            'euro5',
            [],
            true
        ];
        yield 'permits remaining less than max permitted, not valid (euro5)' => [
            '7',
            6,
            7,
            'euro5',
            [
                NoOfPermitsSingleValidator::PERMITS_REMAINING_THRESHOLD =>
                    'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.euro5'
            ],
            false
        ];
        yield 'permits remaining less than max permitted, not valid (euro6)' => [
            '7',
            6,
            7,
            'euro6',
            [
                NoOfPermitsSingleValidator::PERMITS_REMAINING_THRESHOLD =>
                    'qanda.ecmt.number-of-permits.error.permits-remaining-exceeded.euro6'
            ],
            false
        ];
        yield 'max permitted less than permits remaining, valid' => [
            '6',
            7,
            6,
            'euro5',
            [],
            true
        ];
        yield 'max permitted less than permits remaining, not valid' => [
            '7',
            7,
            6,
            'euro5',
            [
                NoOfPermitsSingleValidator::MAX_PERMITTED_THRESHOLD =>
                    'qanda.ecmt.number-of-permits.error.total-max-exceeded'
            ],
            false
        ];
    }
}
