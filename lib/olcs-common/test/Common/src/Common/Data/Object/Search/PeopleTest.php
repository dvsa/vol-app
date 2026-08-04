<?php

declare(strict_types=1);

namespace CommonTest\Common\Data\Object\Search;

use Common\Data\Object\Search\People;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Data\Object\Search\SearchAbstract::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Data\Object\Search\People::class)]
final class PeopleTest extends SearchAbstractTest
{
    protected $class = People::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestDisqualifiedFormatter')]
    public function testDisqualifiedFormatter($expected, $row): void
    {
        $columns = $this->sut->getColumns();

        $this->assertSame($expected, $columns[6]['formatter']($row));
    }

    public function testGetDateRanges(): void
    {
        $dateRanges = $this->sut->getDateRanges();

        $this->assertCount(1, $dateRanges);

        $this->assertInstanceOf(
            \Common\Data\Object\Search\Aggregations\DateRange\DateOfBirthFromAndTo::class,
            $dateRanges[0]
        );
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | string)>>
     *
     * @psalm-return list{list{'Yes', array{foundAs: 'XX', disqualified: 'Yes'}}, list{'No', array{foundAs: 'XX', disqualified: 'No'}}}
     */
    public static function dataProviderTestDisqualifiedFormatter(): \Iterator
    {
        yield ['Yes', ['foundAs' => 'XX', 'disqualified' => 'Yes']];
        yield ['No', ['foundAs' => 'XX', 'disqualified' => 'No']];
    }
}
