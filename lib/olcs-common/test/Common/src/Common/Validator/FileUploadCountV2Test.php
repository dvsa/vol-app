<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Validator\FileUploadCountV2;

final class FileUploadCountV2Test extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestIsValid')]
    public function testIsValid($expected, $min, $context): void
    {
        $sut = new FileUploadCountV2(['min' => $min]);

        $valid = $sut->isValid(null, ['list' => $context]);

        $this->assertSame($expected, $valid);
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | bool | int)>>
     *
     * @psalm-return list{list{true, 0, array<never, never>}, list{false, 1, array<never, never>}, list{false, 2, array<never, never>}, list{true, 0, list{'file1'}}, list{true, 1, list{'file1'}}, list{false, 2, list{'file1'}}, list{true, 0, list{'file1', 'file2'}}, list{true, 1, list{'file1', 'file2'}}, list{true, 2, list{'file1', 'file2'}}, list{true, 2, list{'file1', 'file2', 'file3'}}}
     */
    public static function dataProviderTestIsValid(): \Iterator
    {
        // isValid, min, context
        yield [true, 0, []];
        yield [false, 1, []];
        yield [false, 2, []];
        yield [true, 0, ['file1']];
        yield [true, 1, ['file1']];
        yield [false, 2, ['file1']];
        yield [true, 0, ['file1', 'file2']];
        yield [true, 1, ['file1', 'file2']];
        yield [true, 2, ['file1', 'file2']];
        yield [true, 2, ['file1', 'file2', 'file3']];
    }
}
