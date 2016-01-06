<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PaymentType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class PaymentTypeTest
 * @package OlcsTest\Service\Data
 */
class PaymentTypeTest extends MockeryTestCase
{
    public function testFetchListData()
    {
        $this->markTestSkipped();
        $data = [
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
        ];

        $mockRestClient = m::mock('\Common\Util\RestClient')
            ->shouldReceive('get')
            ->with('/category/fee_pay_method')
            ->andReturn($data)
            ->getMock();

        $sut = new PaymentType();
        $sut->setRestClient($mockRestClient);

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
