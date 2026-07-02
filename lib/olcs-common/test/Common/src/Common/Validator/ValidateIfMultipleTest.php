<?php

namespace CommonTest\Validator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Validator\ValidateIfMultiple;
use Mockery as m;

/**
 * Class ValidateIfMultipleTest
 * @package CommonTest\Validator
 */
class ValidateIfMultipleTest extends MockeryTestCase
{
    /**
     * @dataProvider provideIsValid
     * @param $expected
     * @param $options
     * @param $context
     * @param $chainValid
     * @param array $errorMessages
     */
    public function testIsValid($expected, $options, $value, $context, $chainValid, $errorMessages = []): void
    {
        $errorMessages = empty($errorMessages) ? ['error' => 'message'] : $errorMessages;

        $mockValidatorChain = m::mock(\Laminas\Validator\ValidatorChain::class);
        $mockValidatorChain->shouldReceive('isValid')->with($value, $context)->andReturn($chainValid);
        $mockValidatorChain->shouldReceive('getMessages')->andReturn($errorMessages);

        $sut = new ValidateIfMultiple();
        $sut->setValidatorChain($mockValidatorChain);
        $sut->setOptions($options);
        $this->assertEquals($expected, $sut->isValid($value, $context));

        if (!$expected) {
            $this->assertEquals($errorMessages, $sut->getMessages());
        }
    }

    /**
     * @return array
     */
    public function provideIsValid()
    {
        return [
            //context matches, field is valid
            [true, ['context_field' => 'field', 'context_values' => ['Y']], 'isValid', ['field' => 'Y'], true],
            //context matches, field is invalid
            [true, ['context_field' => 'field', 'context_values' => ['Y']], 'isValid', ['field' => 'Y'], false],
            //context doesn't match, field is invalid
            [true, ['context_field' => 'field', 'context_values' => ['Y']], 'isValid', ['field' => 'N'], false],
            //inverse context match, field valid
            [
                true,
                ['context_field' => 'field', 'context_values' => ['Y'], 'context_truth' => 0],
                'isValid',
                ['field' => 'N'],
                true
            ],
            //missing context
            [false, [], 'isValid', [], false, ['no_context' => 'Context field was not found in the input']],
            //context matches value is empty
            [
                true,
                ['allow_empty' => true, 'context_field' => 'field', 'context_values' => ['Y']],
                null,
                ['field' => 'Y'],
                true
            ],
        ];
    }
}
