<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Validator\ValidateIf;
use Mockery as m;

/**
 * Class ValidateIfTest
 * @package CommonTest\Validator
 */
final class ValidateIfTest extends MockeryTestCase
{
    /**
     *
     */
    public function testSetOptions(): void
    {
        $sut = new ValidateIf();
        $sut->setOptions(
            [
                'context_field' => 'test',
                'context_truth' => false,
                'context_values' => [null],
                'allow_empty' => true
            ]
        );

        $this->assertEquals('test', $sut->getContextField());
        $this->assertEquals([null], $sut->getContextValues());
        $this->assertEquals(false, $sut->getContextTruth());
        $this->assertEquals(true, $sut->allowEmpty());
    }

    /**
     *
     */
    public function testGetValidatorChain(): void
    {
        $mockValidator = m::mock(\Laminas\Validator\NotEmpty::class);

        $mockValidatorPluginManager = m::mock(\Laminas\Validator\ValidatorPluginManager::class);
        $mockValidatorPluginManager->shouldReceive('get')->with('NotEmpty', [])->andReturn($mockValidator);

        $sut = new ValidateIf();
        $sut->setValidatorPluginManager($mockValidatorPluginManager);
        $sut->setValidators([['name' => 'NotEmpty']]);

        $validatorChain = $sut->getValidatorChain();
        $this->assertInstanceOf(\Laminas\Validator\ValidatorChain::class, $validatorChain);
        $this->assertSame($validatorChain, $sut->getValidatorChain());

        $this->assertCount(1, $validatorChain->getValidators());
        $this->assertSame($mockValidatorPluginManager, $validatorChain->getPluginManager());
    }

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

        $sut = new ValidateIf();
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
        yield [false, ['context_field' => 'field', 'context_values' => ['Y']], 'isValid', ['field' => 'Y'], false];
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

    public function testIsValidInjecttPost(): void
    {
        $mockValidatorChain = m::mock(\Laminas\Validator\ValidatorChain::class);
        $mockValidatorChain->shouldReceive('isValid')->with('XXX', ['bar' => 'VALUE'])->andReturn(true);

        $_POST = ['foo' => ['bar' => 'VALUE']];

        $sut = new ValidateIf();
        $sut->setOptions(
            [
                'inject_post_data' => 'foo->bar',
                'context_field' => 'bar',
                'context_values' => 'VALUE'
            ]
        );
        $sut->setValidatorChain($mockValidatorChain);

        $this->assertEquals(true, $sut->isValid('XXX'));
    }
}
