<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\EcmtCandidatePermitSelectionValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EcmtCandidatePermitSelectionValidatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class EcmtCandidatePermitSelectionValidatorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpValidate')]
    public function testValidate($firstValue, $secondValue, $thirdValue, $expected): void
    {
        $context = [
            'candidate-123' => $firstValue,
            'otherField1' => '0',
            'candidate-456' => $secondValue,
            'otherField2' => '1',
            'candidate-789' => $thirdValue,
        ];

        $this->assertEquals(
            $expected,
            EcmtCandidatePermitSelectionValidator::validate('notused', $context)
        );
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string)>>
     *
     * @psalm-return list{list{'0', '0', '0', false}, list{'1', '0', '1', true}, list{'1', '1', '1', true}}
     */
    public static function dpValidate(): \Iterator
    {
        yield ['0', '0', '0', false];
        yield ['1', '0', '1', true];
        yield ['1', '1', '1', true];
    }
}
