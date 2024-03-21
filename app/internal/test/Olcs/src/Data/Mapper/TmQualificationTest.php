<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\TmQualification as Sut;
use Laminas\Form\Form;

/**
 * Transport Manager Mapper Test
 */
class TmQualificationTest extends MockeryTestCase
{
    public function testMapFromErrors()
    {
        $mockForm = new Form();

        $errors['messages'] = [
            'issuedDate'        => ['error1'],
            'serialNo'          => ['error2'],
            'qualificationType' => ['error3'],
            'general'           => ['error4'],
        ];

        $expected = ['general' => ['error4']];

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromForm()
    {
        $data = [
            'qualification-details' => ['foo' => 'bar'],
            'transportManager'      => 1,
        ];

        $expected = [
            'foo'              => 'bar',
            'transportManager' => 1,
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    /**
     * @dataProvider mapFromResultProvider
     */
    public function testMapFromResult($data, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function mapFromResultProvider()
    {
        return [
            [
                [
                    'id'                => 1,
                    'version'           => 2,
                    'issuedDate'        => '2015-01-01',
                    'serialNo'          => 123,
                    'qualificationType' => [
                        'id' => 3,
                    ],
                    'countryCode'       => [
                        'id' => 'GB',
                    ],
                    'transportManager'  => 1,
                ],
                [
                    'qualification-details' => [
                        'id'                => 1,
                        'version'           => 2,
                        'issuedDate'        => '2015-01-01',
                        'serialNo'          => 123,
                        'qualificationType' => 3,
                        'countryCode'       => 'GB',
                    ],
                    'transportManager'      => 1,
                ],
            ],
            [
                [
                    'transportManager' => 1,
                ],
                [
                    'qualification-details' => [
                        'countryCode' => 'GB',
                    ],
                    'transportManager'      => 1,
                ],
            ],
        ];
    }

    public function testMapFromDocumentsResult()
    {
        $this->assertEquals(
            'foo',
            Sut::mapFromDocumentsResult(['result' => 'foo'])
        );
    }
}
