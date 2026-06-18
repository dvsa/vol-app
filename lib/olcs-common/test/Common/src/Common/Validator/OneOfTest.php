<?php

namespace CommonTest\Validator;

use Common\Validator\OneOf;

/**
 * Class OneOfTest test
 */
class OneOfTest extends \PHPUnit\Framework\TestCase
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
     * @dataProvider provideIsValid
     * @param bool  $expected expected result
     * @param array $options  options
     * @param array $context  context
     */
    public function testIsValid($expected, $options, $context): void
    {
        $sut = new OneOf();
        $sut->setOptions($options);
        $this->assertEquals($expected, $sut->isValid('', $context));
    }

    /**
     * Provider isValid
     *
     * @return array
     */
    public function provideIsValid()
    {
        return [
            [true, ['fields' => ['test1', 'test2']], ['test1' => 'notempty']],
            [true, ['fields' => ['test1', 'test2']], ['test2' => 'notempty']],
            [true, ['fields' => ['test1', 'test2']], ['test1' => 'notempty', 'test2' => 'notempty']],
            [false, ['fields' => ['test1', 'test2']], ['test1' => '', 'test2' => '']],
            [false, ['fields' => ['test1', 'test2'], 'allowZero' => true], []],
            [true, ['fields' => ['test1', 'test2'], 'allowZero' => true], ['test1' => '0']],
            [false, ['fields' => ['test1', 'test2'], 'allowZero' => false], ['test1' => '0']],
        ];
    }
}
