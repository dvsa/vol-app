<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SubmissionSectionComment as Sut;
use Laminas\Form\FormInterface;

/**
 * SubmissionSectionComment Mapper Test
 */
class SubmissionSectionCommentTest extends MockeryTestCase
{
    /**
     * @dataProvider mapFromResultDataProvider
     *
     * @param $inData
     * @param $expected
     */
    public function testMapFromResult($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public function mapFromResultDataProvider()
    {
        return [
            // add
            [
                [],
                ['fields' => []]
            ],
            // edit
            [
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
            ],
            // edit
            [
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
            ]
        ];
    }

    /**
     * @dataProvider mapFromFormDataProvider
     *
     * @param $inData
     * @param $expected
     */
    public function testMapFromForm($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public function mapFromFormDataProvider()
    {
        return [
            [
                [
                    'fields' => [
                        'comment' => 'test',
                    ]
                ],
                [

                    'comment' => 'test',
                ]
            ]
        ];
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
