<?php

/**
 * Task checkbox formatter tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\TaskCheckbox;
use Common\Service\Table\TableBuilder;
use Mockery as m;

/**
 * Task checkbox formatter tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class TaskCheckboxTest extends \PHPUnit\Framework\TestCase
{
    protected $tableBuilder;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->tableBuilder = m::mock(TableBuilder::class);
        $this->sut = new TaskCheckbox($this->tableBuilder);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('notClosedProvider')]
    public function testFormatNotClosed($data): void
    {
        $column = [];

        $this->tableBuilder->shouldReceive('replaceContent')
            ->with('{{[elements/checkbox]}}', $data)
            ->andReturn('checkbox markup');

        $this->assertEquals('checkbox markup', $this->sut->format($data, $column));
    }

    /**
     * @return \Iterator<(int | string), array<array<(int | string | null)>>>
     *
     * @psalm-return array{N: list{array{id: 69, isClosed: 'N'}}, 'not set': list{array{id: 69}}, null: list{array{id: 69, isClosed: null}}}
     */
    public static function notClosedProvider(): \Iterator
    {
        yield 'N' => [
            [
                'id' => 69,
                'isClosed' => 'N',
            ],
        ];
        yield 'not set' => [
            [
                'id' => 69,
            ],
        ];
        yield 'null' => [
            [
                'id' => 69,
                'isClosed' => null,
            ],
        ];
    }

    public function testFormatClosed(): void
    {
        $data = [
            'id' => 69,
            'isClosed' => 'Y',
        ];

        $column = [];

        $this->assertEquals('', new TaskCheckbox($this->tableBuilder)->format($data, $column));
    }
}
