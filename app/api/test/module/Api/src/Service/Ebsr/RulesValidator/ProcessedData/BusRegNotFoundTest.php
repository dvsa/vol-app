<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\BusRegNotFound;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class BusRegNotFoundTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
class BusRegNotFoundTest extends MockeryTestCase
{
    /**
     * tests whether a bus reg has been found
     *
     *
     * @param string $txcAppType
     * @param BusRegEntity|null $busReg
     * @param bool $valid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $txcAppType, mixed $busReg, mixed $valid): void
    {
        $sut = new BusRegNotFound();

        $value = [
            'txcAppType' => $txcAppType,
            'existingRegNo' => '1234/567'
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
        $busMock = m::mock(BusRegEntity::class);

        return [
            [BusRegEntity::TXC_APP_NEW, $busMock, true],
            [BusRegEntity::TXC_APP_NEW, null, true],
            [BusRegEntity::TXC_APP_CANCEL, $busMock, true],
            [BusRegEntity::TXC_APP_CANCEL, null, false],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, $busMock, true],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, null, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, $busMock, true],
            [BusRegEntity::TXC_APP_CHARGEABLE, null, false],
        ];
    }
}
