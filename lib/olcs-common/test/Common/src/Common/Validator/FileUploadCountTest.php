<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Validator\FileUploadCount;

class FileUploadCountTest extends MockeryTestCase
{
    public function testSetOptions(): void
    {
        $sut = new FileUploadCount(['min' => '23']);
        $this->assertEquals(23, $sut->getMin());
    }

    /**
     * @dataProvider dataProviderTestIsValid
     */
    public function testIsValid($expected, $min, $context): void
    {
        $sut = new FileUploadCount(['min' => $min]);

        $valid = $sut->isValid(null, $context);

        $this->assertSame($expected, $valid);
    }

    /**
     * @return (bool|int|int[])[][]
     *
     * @psalm-return list{list{false, 2, array<never, never>}, list{false, 2, array{uploadedFileCount: 1}}, list{true, 2, array{uploadedFileCount: 2}}, list{true, 1, array{uploadedFileCount: 1}}, list{true, 2, array{uploadedFileCount: 2}}}
     */
    public function dataProviderTestIsValid(): array
    {
        return [
            // isValid, min, context
            [false, 2, []],
            [false, 2, ['uploadedFileCount' => 1]],
            [true, 2, ['uploadedFileCount' => 2]],
            [true, 1, ['uploadedFileCount' => 1]],
            [true, 2, ['uploadedFileCount' => 2]],
        ];
    }

    public function testSetMin(): void
    {
        $sut = new FileUploadCount(['min' => 1]);
        $sut->setMin(4);

        $this->assertSame(4, $sut->getMin());
    }

    public function testSetMinInvalid(): void
    {
        $sut = new FileUploadCount(['min' => 1]);
        $this->expectException(\Laminas\Validator\Exception\InvalidArgumentException::class);
        $sut->setMin('X');
    }
}
