<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PaymentType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * Class PaymentTypeTest
 * @package OlcsTest\Service\Data
 */
class PaymentTypeTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $data = [
            'results' => [
                [
                    'id' => 'fpm_cash',
                    'description' => 'Cash'
                ], [
                    'id' => 'will_be_filtered',
                    'description' => 'Foo'
                ], [
                    'id' => 'fpm_card_offline',
                    'description' => 'Will Be Overridden'
                ]
            ]
        ];

        $mockTransferAnnotationBuilder = m::mock()
            ->shouldReceive('createQuery')->once()->andReturn('query')
            ->once()
            ->getMock();

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getResult')
            ->andReturn($data)
            ->twice()
            ->getMock();

        $sut = new PaymentType();
        $this->mockHandleQuery($sut, $mockTransferAnnotationBuilder, $mockResponse, $data);
        $this->mockServiceLocator
            ->shouldReceive('get')
            ->with('LanguagePreference')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getPreference')
                    ->andReturn('en')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $result = [
            [
                'id' => 'fpm_cash',
                'description' => 'Cash'
            ], [
                'id' => 'fpm_card_offline',
                'description' => 'Card Payment'
            ]
        ];

        $this->assertEquals($result, $sut->fetchListData());
    }
}
