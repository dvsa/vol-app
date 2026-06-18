<?php

namespace CommonTest\Common\Data\Object\Search;

use Common\Data\Object\Search\People;

/**
 * @covers \Common\Data\Object\Search\SearchAbstract
 * @covers \Common\Data\Object\Search\People
 */
class PeopleTest extends SearchAbstractTest
{
    protected $class = People::class;

    /**
     * @dataProvider dataProviderTestDisqualifiedFormatter
     */
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
     * @return (string|string[])[][]
     *
     * @psalm-return list{list{'Yes', array{foundAs: 'XX', disqualified: 'Yes'}}, list{'No', array{foundAs: 'XX', disqualified: 'No'}}}
     */
    public function dataProviderTestDisqualifiedFormatter(): array
    {
        return [
            ['Yes', ['foundAs' => 'XX', 'disqualified' => 'Yes']],
            ['No', ['foundAs' => 'XX', 'disqualified' => 'No']],
        ];
    }
}
