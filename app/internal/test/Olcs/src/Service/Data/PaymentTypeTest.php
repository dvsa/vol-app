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
        $sut = new PaymentType();
        $sut->setData('fee_pay_method', $data);

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
