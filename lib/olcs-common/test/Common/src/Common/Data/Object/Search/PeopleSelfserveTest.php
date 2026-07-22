<?php

declare(strict_types=1);

namespace CommonTest\Common\Data\Object\Search;

use Common\Data\Object\Search\PeopleSelfserve;
use DateTime;
use DateTimeInterface;

final class PeopleSelfserveTest extends SearchAbstractTest
{
    protected $class = PeopleSelfserve::class;

    private const int NAME_COLUMN_INDEX = 3;
    private const array BASE_TEST_DATA = [
        'personFullname' => 'Bob Smith',
    ];

    #[\PHPUnit\Framework\Attributes\DataProvider('provideNameFormattingCases')]
    public function testNameFormatterAppendsSuffixBasedOnRemovalStatus(
        array $additionalData,
        bool $expectRemovedSuffix
    ): void {
        $row = array_merge(self::BASE_TEST_DATA, $additionalData);
        $columns = $this->sut->getColumns();

        $formattedName = $columns[self::NAME_COLUMN_INDEX]['formatter']($row, []);
        $expectedName = $expectRemovedSuffix
            ? 'Bob Smith (Removed)'
            : 'Bob Smith';

        $this->assertSame($expectedName, $formattedName);
    }

    public static function provideNameFormattingCases(): \Iterator
    {
        yield 'should not append suffix when removal date is empty string' => [
            'additionalData' => ['dateRemoved' => ''],
            'expectRemovedSuffix' => false,
        ];
        yield 'should not append suffix when removal date is null' => [
            'additionalData' => ['dateRemoved' => null],
            'expectRemovedSuffix' => false,
        ];
        yield 'should not append suffix when removal date is not provided' => [
            'additionalData' => [],
            'expectRemovedSuffix' => false,
        ];
        yield 'should append suffix when removal date is set' => [
            'additionalData' => [
                'dateRemoved' => new DateTime()->format(DateTimeInterface::ATOM),
            ],
            'expectRemovedSuffix' => true,
        ];
    }
}
