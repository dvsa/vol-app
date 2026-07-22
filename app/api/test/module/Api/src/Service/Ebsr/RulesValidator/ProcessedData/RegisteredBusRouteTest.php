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
final class RegisteredBusRouteTest extends \PHPUnit\Framework\TestCase
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function isValidProvider(): \Iterator
    {
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_NEW, true];
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_VAR, true];
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_CANCEL, true];
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_ADMIN, true];
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_REGISTERED, true];
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_REFUSED, true];
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_WITHDRAWN, true];
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_CNS, true];
        yield [BusRegEntity::TXC_APP_NEW, BusRegEntity::STATUS_CANCELLED, true];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_NEW, false];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_VAR, false];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_CANCEL, false];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_ADMIN, false];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_REGISTERED, true];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_REFUSED, false];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_WITHDRAWN, false];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_CNS, false];
        yield [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_CANCELLED, false];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_NEW, false];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_VAR, false];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_CANCEL, false];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_ADMIN, false];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_REGISTERED, true];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_REFUSED, false];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_WITHDRAWN, false];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_CNS, false];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, BusRegEntity::STATUS_CANCELLED, false];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_NEW, false];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_VAR, false];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_CANCEL, false];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_ADMIN, false];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_REGISTERED, true];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_REFUSED, false];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_WITHDRAWN, false];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_CNS, false];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_CANCELLED, false];
    }
}
