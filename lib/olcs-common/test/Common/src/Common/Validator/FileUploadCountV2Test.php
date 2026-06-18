<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Validator\FileUploadCountV2;

class FileUploadCountV2Test extends MockeryTestCase
{
    /**
     * @dataProvider dataProviderTestIsValid
     */
    public function testIsValid($expected, $min, $context): void
    {
        $sut = new FileUploadCountV2(['min' => $min]);

        $valid = $sut->isValid(null, ['list' => $context]);

        $this->assertSame($expected, $valid);
    }

    /**
     * @return (bool|int|string[])[][]
     *
     * @psalm-return list{list{true, 0, array<never, never>}, list{false, 1, array<never, never>}, list{false, 2, array<never, never>}, list{true, 0, list{'file1'}}, list{true, 1, list{'file1'}}, list{false, 2, list{'file1'}}, list{true, 0, list{'file1', 'file2'}}, list{true, 1, list{'file1', 'file2'}}, list{true, 2, list{'file1', 'file2'}}, list{true, 2, list{'file1', 'file2', 'file3'}}}
     */
    public function dataProviderTestIsValid(): array
    {
        return [
            // isValid, min, context
            [true, 0, []],
            [false, 1, []],
            [false, 2, []],
            [true, 0, ['file1']],
            [true, 1, ['file1']],
            [false, 2, ['file1']],
            [true, 0, ['file1', 'file2']],
            [true, 1, ['file1', 'file2']],
            [true, 2, ['file1', 'file2']],
            [true, 2, ['file1', 'file2', 'file3']],
        ];
    }
}
