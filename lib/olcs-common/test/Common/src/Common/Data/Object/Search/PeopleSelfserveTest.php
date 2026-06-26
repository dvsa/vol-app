<?php

declare(strict_types=1);

namespace CommonTest\Common\Data\Object\Search;

use Common\Data\Object\Search\PeopleSelfserve;
use DateTime;
use DateTimeInterface;

class PeopleSelfserveTest extends SearchAbstractTest
{
    protected $class = PeopleSelfserve::class;

    private const NAME_COLUMN_INDEX = 3;
    private const BASE_TEST_DATA = [
        'personFullname' => 'Bob Smith',
    ];

    /**
     * @dataProvider provideNameFormattingCases
     */
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

    public static function provideNameFormattingCases(): array
    {
        return [
            'should not append suffix when removal date is empty string' => [
                'additionalData' => ['dateRemoved' => ''],
                'expectRemovedSuffix' => false,
            ],
            'should not append suffix when removal date is null' => [
                'additionalData' => ['dateRemoved' => null],
                'expectRemovedSuffix' => false,
            ],
            'should not append suffix when removal date is not provided' => [
                'additionalData' => [],
                'expectRemovedSuffix' => false,
            ],
            'should append suffix when removal date is set' => [
                'additionalData' => [
                    'dateRemoved' => (new DateTime())->format(DateTimeInterface::ATOM),
                ],
                'expectRemovedSuffix' => true,
            ]
        ];
    }
}
