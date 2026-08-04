<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\NewAppAlreadyExists;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see NewAppAlreadyExists
 */
final class NewAppAlreadyExistsTest extends MockeryTestCase
{
    /**
     * tests whether a new application is prevented from reusing an existing number
     *
     *
     * @param string $txcAppType
     * @param BusRegEntity|null $busReg
     * @param bool $valid
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(mixed $txcAppType, mixed $busReg, mixed $valid): void
    {
        $sut = new NewAppAlreadyExists();

        $value = [
            'txcAppType' => $txcAppType,
            'existingRegNo' => '1234/567'
        ];

        $context = ['busRegNoExclusions' => $busReg];

        $this->assertEquals($valid, $sut->isValid($value, $context));
    }

    /**
     * Provider for testIsValid
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function isValidProvider(): \Iterator
    {
        $busMock = m::mock(BusRegEntity::class);
        yield [BusRegEntity::TXC_APP_NEW, $busMock, false];
        yield [BusRegEntity::TXC_APP_NEW, null, true];
        yield [BusRegEntity::TXC_APP_CANCEL, $busMock, true];
        yield [BusRegEntity::TXC_APP_CANCEL, null, true];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, $busMock, true];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, null, true];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, $busMock, true];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, null, true];
    }
}
