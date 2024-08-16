<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SlaTargetDate as Sut;
use Laminas\Form\FormInterface;

/**
 * SlaTargetDate Mapper Test
 */
class SlaTargetDateTest extends MockeryTestCase
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
                [
                    'details' => 'details',
                    'document' => [
                        'id' => 88,
                        'description' => 'foobar'
                    ]
                ],
                [
                    'fields' => [
                        'details' => 'details',
                        'document' => 88,
                        'entityType' => 'document',
                        'entityId' => 88,
                        'entityTypeHtml' => 'foobar'
                    ]
                ]
            ],
            // edit
            [
                [
                    'id' => 33,
                    'version' => 2,
                    'details' => 'details',
                    'document' => [
                        'id' => 88,
                        'description' => 'foobar'
                    ]
                ],
                [
                    'fields' => [
                        'id' => 33,
                        'version' => 2,
                        'details' => 'details',
                        'document' => 88,
                        'entityType' => 'document',
                        'entityId' => 88,
                        'entityTypeHtml' => 'foobar'
                    ]
                ]
            ]
        ];
    }

    public function testMapFromForm()
    {
        $inData = [
            'fields' => [
                'entityTypeHtml' => 'foobar',
                'somefield' => 'somevalue'
            ]
        ];
        $expected = [
            'somefield' => 'somevalue'
        ];

        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
