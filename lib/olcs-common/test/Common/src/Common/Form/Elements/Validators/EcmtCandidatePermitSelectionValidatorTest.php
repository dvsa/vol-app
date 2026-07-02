<?php

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\EcmtCandidatePermitSelectionValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EcmtCandidatePermitSelectionValidatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtCandidatePermitSelectionValidatorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpValidate
     */
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
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{'0', '0', '0', false}, list{'1', '0', '1', true}, list{'1', '1', '1', true}}
     */
    public function dpValidate(): array
    {
        return [
            ['0', '0', '0', false],
            ['1', '0', '1', true],
            ['1', '1', '1', true],
        ];
    }
}
