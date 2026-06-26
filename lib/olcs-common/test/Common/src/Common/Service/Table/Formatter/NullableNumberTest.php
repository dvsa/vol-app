<?php

namespace CommonTest\Common\Service\Table\Formatter;

use Common\Service\Table\Formatter\NullableNumber;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class NullableNumberTest
 *
 * Formatter test.
 *
 * @package CommonTest\Service\Table\Formatter
 */
class NullableNumberTest extends MockeryTestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group NullableNumberFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data): void
    {
        $this->assertEquals($data['expected'], (new NullableNumber())->format($data, ['name' => 'permitsRequired']));
    }

    /**
     * @return (int|null)[][][]
     *
     * @psalm-return list{list{array{permitsRequired: null, expected: 0}}, list{array{permitsRequired: 3, expected: 3}}}
     */
    public function provider(): array
    {
        return [
            [
                [
                    'permitsRequired' => null,
                    'expected' => 0
                ],
            ],
            [
                [
                    'permitsRequired' => 3,
                    'expected' => 3
                ],
            ],
        ];
    }
}
