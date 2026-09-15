<?php

declare(strict_types=1);

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
final class NullableNumberTest extends MockeryTestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('NullableNumberFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data): void
    {
        $this->assertEquals($data['expected'], new NullableNumber()->format($data, ['name' => 'permitsRequired']));
    }

    /**
     * @return \Iterator<(int | string), array<array<(int | null)>>>
     *
     * @psalm-return list{list{array{permitsRequired: null, expected: 0}}, list{array{permitsRequired: 3, expected: 3}}}
     */
    public static function provider(): \Iterator
    {
        yield [
            [
                'permitsRequired' => null,
                'expected' => 0
            ],
        ];
        yield [
            [
                'permitsRequired' => 3,
                'expected' => 3
            ],
        ];
    }
}
