<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\RegisteredBusRoute;

/**
 * Class RegisteredBusRouteTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
class RegisteredBusRouteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * tests whether the bus route has a status of registered
     *
     *
     * @param string $txcAppType
     * @param string $status
     * @param bool $valid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $txcAppType, mixed $status, mixed $valid): void
    {
        $sut = new RegisteredBusRoute();
        $busReg = new BusRegEntity();
        $busReg->setStatus(new RefData($status));

        $value = [
            'txcAppType' => $txcAppType
        ];

        $context = ['busReg' => $busReg];

        $this->assertEquals($valid, $sut->isValid($value, $context));
    }

    /**
     * Provider for testIsValid
     *
     * @return array
     */
    public static function isValidProvider(): array
    {
        return [
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_NEW, true],
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_VAR, true],
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_CANCEL, true],
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_ADMIN, true],
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_REGISTERED, true],
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_REFUSED, true],
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_WITHDRAWN, true],
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_CNS, true],
            [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_CANCELLED, true],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_NEW, false],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_VAR, false],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_CANCEL, false],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_ADMIN, false],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_REGISTERED, true],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_REFUSED, false],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_WITHDRAWN, false],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_CNS, false],
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_CANCELLED, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_NEW, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_VAR, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_CANCEL, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_ADMIN, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_REGISTERED, true],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_REFUSED, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_WITHDRAWN, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_CNS, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_CANCELLED, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_NEW, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_VAR, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_CANCEL, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_ADMIN, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_REGISTERED, true],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_REFUSED, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_WITHDRAWN, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_CNS, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_CANCELLED, false]
        ];
    }
}
