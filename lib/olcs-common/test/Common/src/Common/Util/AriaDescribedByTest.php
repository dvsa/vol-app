<?php

declare(strict_types=1);

namespace CommonTest\Common\Util;

use Common\Util\AriaDescribedBy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AriaDescribedBy::class)]
final class AriaDescribedByTest extends TestCase
{
    #[DataProvider('descriptionIdsProvider')]
    public function testMerge(array $values, string $expected): void
    {
        $this->assertSame($expected, AriaDescribedBy::merge(...$values));
    }

    public static function descriptionIdsProvider(): array
    {
        return [
            'no values' => [[], ''],
            'null and empty values' => [[null, '', null], ''],
            'whitespace only' => [[" \t\n\r\f "], ''],
            'single ID' => [['field-hint'], 'field-hint'],
            'existing descriptions and new IDs' => [
                ['existing-description field-hint', 'field-error', 'below-hint'],
                'existing-description field-hint field-error below-hint',
            ],
            'mixed whitespace' => [
                [" \tfirst\nsecond\r\nthird ", "\ffourth\t"],
                'first second third fourth',
            ],
            'duplicates within and across lists' => [
                ['hint hint error', 'error extra hint', 'extra'],
                'hint error extra',
            ],
            'empty values between IDs' => [[null, 'hint', '', 'error', null], 'hint error'],
            'zero is a valid ID' => [['0', 'hint 0'], '0 hint'],
            'IDs are case sensitive' => [['Hint hint', 'Hint'], 'Hint hint'],
        ];
    }

    public function testMergingRepeatedlyDoesNotDuplicateIds(): void
    {
        $describedBy = AriaDescribedBy::merge('existing-description', 'field-hint', 'field-error');

        $this->assertSame($describedBy, AriaDescribedBy::merge($describedBy, 'field-hint', 'field-error'));
    }
}
