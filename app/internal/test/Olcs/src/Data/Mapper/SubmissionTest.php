<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Submission as Sut;

class SubmissionTest extends MockeryTestCase
{
    /**
     *
     * @param $expected
     * @param $inData
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('assignedDateDataProvider')]
    public function testReadOnlyFields(mixed $expected, mixed $inData): void
    {
        $actual = Sut::mapFromResult($inData);
        $this->assertEquals($expected, $actual['readOnlyFields']);
    }

    public function testDefaultDateSet(): void
    {

        $inData = [

            ['assignedDate' => null, 'informationCompleteDate' => null]
        ];
        $actual = Sut::mapFromResult($inData);
        $this->assertEquals((new \DateTime('now'))->format('Y-m-d'), $actual['fields']['assignedDate']);
    }

    public static function assignedDateDataProvider(): array
    {

        return [
            [
                [
                    'assignedDate',
                    'informationCompleteDate'
                ],
                ['assignedDate' => null, 'informationCompleteDate' => 'TEST']
            ]
        ];
    }
}
