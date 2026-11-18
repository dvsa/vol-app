<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Util;

use Dvsa\Olcs\Api\Domain\Util\QueryHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QueryHelperTest extends TestCase
{
    public function testToSortDirectionReturnsExistingEnum(): void
    {
        $direction = \SortDirection::Descending;

        $this->assertSame($direction, QueryHelper::toSortDirection($direction));
    }

    #[DataProvider('sortDirectionProvider')]
    public function testToSortDirectionConvertsStringValues(string $input, \SortDirection $expected): void
    {
        $this->assertSame($expected, QueryHelper::toSortDirection($input));
    }

    public static function sortDirectionProvider(): iterable
    {
        yield ['desc', \SortDirection::Descending];
        yield ['DESC', \SortDirection::Descending];
        yield ['asc', \SortDirection::Ascending];
        yield ['Asc', \SortDirection::Ascending];
    }

    public function testToSortDirectionRejectsUnsupportedValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Order must be a string or instance of SortDirection');

        QueryHelper::toSortDirection(123);
    }
}
