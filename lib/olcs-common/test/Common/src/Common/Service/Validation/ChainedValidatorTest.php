<?php

namespace CommonTest\Validation\Service;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Common\Service\Validation\Result\ValidationFailed;
use Common\Service\Validation\Result\ValidationSuccessful;
use Common\Service\Validation\ChainedValidator;
use Common\Service\Validation\CommandInterface;
use Laminas\InputFilter\InputInterface;

/**
 * Class ChainedValidatorTest
 * @package OlcsTest\Ebsr\Service
 */
class ChainedValidatorTest extends TestCase
{
    /**
     * @dataProvider provideValidate
     * @param $command
     * @param $validator
     * @param $expected
     */
    public function testValidate($command, $validator, $expected): void
    {
        $sut = new ChainedValidator();
        $sut->addValidationChain($validator);

        $result = $sut->validate($command);

        $this->assertEquals($result, $expected);
    }

    /**
     * @return (ValidationFailed|ValidationSuccessful|m\LegacyMockInterface&m\MockInterface&CommandInterface|m\LegacyMockInterface&m\MockInterface&InputInterface)[][]
     *
     * @psalm-return list{list{m\LegacyMockInterface&m\MockInterface&CommandInterface, m\LegacyMockInterface&m\MockInterface&InputInterface, ValidationFailed}, list{m\LegacyMockInterface&m\MockInterface&CommandInterface, m\LegacyMockInterface&m\MockInterface&InputInterface, ValidationFailed}, list{m\LegacyMockInterface&m\MockInterface&CommandInterface, m\LegacyMockInterface&m\MockInterface&InputInterface, ValidationSuccessful}}
     */
    public function provideValidate(): array
    {
        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getValue')->andReturn('e34fd6');
        $command->shouldReceive('getArrayCopy')->andReturn([]);

        $validator = m::mock(InputInterface::class);
        $validator->shouldReceive('setValue')->with('e34fd6');
        $validator->shouldReceive('getValue')->andReturn('e34fd6');
        $validator->shouldReceive('getName')->andReturn('validator');

        $exceptionValidator = clone $validator;
        $exceptionValidator->shouldReceive('isValid')->andThrow(new \Laminas\Filter\Exception\RuntimeException('failed'));
        $exceptionResult = new ValidationFailed($command, ['failed']);

        $failedValidator = clone $validator;
        $failedValidator->shouldReceive('isValid')->andReturn(false);
        $failedValidator->shouldReceive('getMessages')->andReturn(['invalid']);
        $failedResult = new ValidationFailed($command, ['invalid']);

        $successValidator = clone $validator;
        $successValidator->shouldReceive('isValid')->andReturn(true);
        $successResult = new ValidationSuccessful($command, 'e34fd6');

        return [
            [$command, $exceptionValidator, $exceptionResult],
            [$command, $failedValidator, $failedResult],
            [$command, $successValidator, $successResult]
        ];
    }
}
