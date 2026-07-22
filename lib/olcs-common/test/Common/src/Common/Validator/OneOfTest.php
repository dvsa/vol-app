<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Common\Validator\OneOf;

/**
 * Class OneOfTest test
 */
final class OneOfTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test setOptions
     */
    public function testSetOptions(): void
    {
        $sut = new OneOf();
        $sut->setOptions(
            [
                'fields' => ['test', 'test2'],
                'message' => 'Please provide at least one field',
                'allowZero' => true
            ]
        );

        $this->assertEquals(['test', 'test2'], $sut->getFields());
        $this->assertEquals(['provide_one' => 'Please provide at least one field'], $sut->getMessageTemplates());
        $this->assertEquals(true, $sut->getAllowZero());
    }

    /**
     * Test is valid
     *
     * @param bool  $expected expected result
     * @param array $options  options
     * @param array $context  context
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsValid')]
    public function testIsValid($expected, $options, $context): void
    {
        $sut = new OneOf();
        $sut->setOptions($options);
        $this->assertEquals($expected, $sut->isValid('', $context));
    }

    /**
     * Provider isValid
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideIsValid(): \Iterator
    {
        yield [true, ['fields' => ['test1', 'test2']], ['test1' => 'notempty']];
        yield [true, ['fields' => ['test1', 'test2']], ['test2' => 'notempty']];
        yield [true, ['fields' => ['test1', 'test2']], ['test1' => 'notempty', 'test2' => 'notempty']];
        yield [false, ['fields' => ['test1', 'test2']], ['test1' => '', 'test2' => '']];
        yield [false, ['fields' => ['test1', 'test2'], 'allowZero' => true], []];
        yield [true, ['fields' => ['test1', 'test2'], 'allowZero' => true], ['test1' => '0']];
        yield [false, ['fields' => ['test1', 'test2'], 'allowZero' => false], ['test1' => '0']];
    }
}
