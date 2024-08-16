<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EndDate;

/**
 * Class EndDateTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator
 */
class EndDateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dpIsValid
     */
    public function testIsValid($txcApp, $endDate, $isValid)
    {
        $value = [
            'txcAppType' => $txcApp,
            'endDate' => $endDate
        ];

        $sut = new EndDate();
        $this->assertEquals($isValid, $sut->isValid($value));
    }

    /**
     * @return array
     */
    public function dpIsValid()
    {
        $date = '2017-12-25';

        return [
            [BusRegEntity::TXC_APP_NEW, null, true],
            [BusRegEntity::TXC_APP_NEW, $date, true],
            [BusRegEntity::TXC_APP_CHARGEABLE, null, true],
            [BusRegEntity::TXC_APP_CHARGEABLE, $date, true],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, null, true],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, $date, true],
            [BusRegEntity::TXC_APP_CANCEL, null, true],
            [BusRegEntity::TXC_APP_CANCEL, $date, false],
        ];
    }
}
