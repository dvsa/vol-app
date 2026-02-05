<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\IrfoStockControl as Sut;
use Laminas\Form\FormInterface;

/**
 * IrfoStockControl Mapper Test
 */
class IrfoStockControlTest extends MockeryTestCase
{
    /**
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultDataProvider')]
    public function testMapFromResult($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public static function mapFromResultDataProvider()
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
                    'version' => 1,
                    'status' => [
                        'id' => 200,
                    ],
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'version' => 1,
                        'status' => 200,
                    ],
                ]
            ]
        ];
    }

    /**
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormDataProvider')]
    public function testMapFromForm($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public static function mapFromFormDataProvider()
    {
        return [
            [
                [
                    'fields' => [
                        'description' => 'p1',
                    ],
                ],
                [
                    'description' => 'p1',
                ]
            ],
        ];
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
