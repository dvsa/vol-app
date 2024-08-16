<?php

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\RefDataTestCase;
use Olcs\Service\Data\PaymentType;

/**
 * Class PaymentTypeTest
 * @package OlcsTest\Service\Data
 */
class PaymentTypeTest extends RefDataTestCase
{
    /** @var PaymentType */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new PaymentType($this->refDataServices);
    }

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
        $this->sut->setData('fee_pay_method', $data);

        $result = [
            [
                'id' => 'fpm_cash',
                'description' => 'Cash'
            ], [
                'id' => 'fpm_card_offline',
                'description' => 'Card Payment'
            ]
        ];

        $this->assertEquals($result, $this->sut->fetchListData());
    }
}
