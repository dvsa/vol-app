<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Validator\ValidateIfMultiple;
use Mockery as m;

/**
 * Class ValidateIfMultipleTest
 * @package CommonTest\Validator
 */
final class ValidateIfMultipleTest extends MockeryTestCase
{
    /**
     * @param $expected
     * @param $options
     * @param $context
     * @param $chainValid
     * @param array $errorMessages
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsValid')]
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideIsValid(): \Iterator
    {
        //context matches, field is valid
        yield [true, ['context_field' => 'field', 'context_values' => ['Y']], 'isValid', ['field' => 'Y'], true];
        //context matches, field is invalid
        yield [true, ['context_field' => 'field', 'context_values' => ['Y']], 'isValid', ['field' => 'Y'], false];
        //context doesn't match, field is invalid
        yield [true, ['context_field' => 'field', 'context_values' => ['Y']], 'isValid', ['field' => 'N'], false];
        //inverse context match, field valid
        yield [
            true,
            ['context_field' => 'field', 'context_values' => ['Y'], 'context_truth' => 0],
            'isValid',
            ['field' => 'N'],
            true
        ];
        //missing context
        yield [false, [], 'isValid', [], false, ['no_context' => 'Context field was not found in the input']];
        //context matches value is empty
        yield [
            true,
            ['allow_empty' => true, 'context_field' => 'field', 'context_values' => ['Y']],
            null,
            ['field' => 'Y'],
            true
        ];
    }
}
