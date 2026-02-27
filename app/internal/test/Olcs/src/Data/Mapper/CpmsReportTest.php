<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\CpmsReport as Sut;
use Laminas\Form\FormInterface;

/**
 * CpmsReport Mapper Test
 */
class CpmsReportTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $in = ['foo'];
        $this->assertSame($in, Sut::mapFromResult($in));
    }


    /**
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormDataProvider')]
    public function testMapFromForm(mixed $inData, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public static function mapFromFormDataProvider(): array
    {
        return [
            [
                [
                    'reportOptions' => [
                        'reportCode' => 'FOO',
                        'startDate' => '2015-10-07',
                        'endDate' => '2015-10-08',
                    ]
                ],
                [
                    'reportCode' => 'FOO',
                    'start' => '2015-10-07',
                    'end' => '2015-10-08',
                ]
            ],
        ];
    }

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
