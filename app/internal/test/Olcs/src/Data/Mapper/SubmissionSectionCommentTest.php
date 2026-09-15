<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SubmissionSectionComment as Sut;
use Laminas\Form\FormInterface;

/**
 * SubmissionSectionComment Mapper Test
 */
final class SubmissionSectionCommentTest extends MockeryTestCase
{
    /**
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultDataProvider')]
    public function testMapFromResult(mixed $inData, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public static function mapFromResultDataProvider(): \Iterator
    {
        // add
        yield [
            [],
            ['fields' => []]
        ];
        // edit
        yield [
            [
                'id' => 987,
                'submission' => ['id' => 100],
                'submissionSection' => 'case-summary',
                'comment' => 'test',
            ],
            [
                'fields' => [
                    'id' => 987,
                    'submission' => 100,
                    'submissionSection' => 'case-summary',
                    'comment' => 'test',
                ],
            ]
        ];
        // edit
        yield [
            [
                'id' => 987,
                'comment' => 'test'
            ],
            [
                'fields' => [
                    'id' => 987,
                    'comment' => 'test'
                ],
            ]
        ];
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

    public static function mapFromFormDataProvider(): \Iterator
    {
        yield [
            [
                'fields' => [
                    'comment' => 'test',
                ]
            ],
            [

                'comment' => 'test',
            ]
        ];
    }

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
