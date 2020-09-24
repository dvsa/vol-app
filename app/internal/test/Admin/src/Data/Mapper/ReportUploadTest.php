<?php

namespace AdminTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Data\Mapper\ReportUpload as Sut;

/**
 * Report Upload Mapper Test
 */
class ReportUploadTest extends MockeryTestCase
{
    public function testMapLetterTemplateOptions()
    {
        $data = [
            'results' =>
                [
                    [
                        'id' => 1,
                        'templateSlug' => 'slug1',
                        'documentId' => 323,
                        'otherFields' => 'content'
                    ],
                    [
                        'id' => 2,
                        'templateSlug' => 'slug2',
                        'documentId' => 324,
                        'otherFields' => 'more content'
                    ]
                ]
        ];

        $expected = [
            [
                'value' => 'slug1',
                'label' => 'slug1'
            ],
            [
                'value' => 'slug2',
                'label' => 'slug2'
            ]
        ];

        $this->assertEquals($expected, Sut::mapLetterTemplateOptions($data));
    }

    public function testMapEmailTemplateOptions()
    {
        $data = [
            'results' =>
                [
                    [
                        'id' => 1,
                        'name' => 'template1',
                        'templateTestDataId' => 323,
                        'otherFields' => 'content'
                    ],
                    [
                        'id' => 2,
                        'name' => 'template1',
                        'templateTestDataId' => 324,
                        'otherFields' => 'more content'
                    ],
                    [
                        'id' => 3,
                        'name' => 'template2',
                        'templateTestDataId' => 324,
                        'otherFields' => 'more content'
                    ],
                    [
                        'id' => 4,
                        'name' => 'template1',
                        'templateTestDataId' => 324,
                        'otherFields' => 'more content'
                    ],
                ]
        ];

        $expected = [
            [
                'value' => 'template1',
                'label' => 'template1'
            ],
            [
                'value' => 'template2',
                'label' => 'template2'
            ]
        ];

        $this->assertEquals($expected, Sut::mapEmailTemplateOptions($data));
    }
}
