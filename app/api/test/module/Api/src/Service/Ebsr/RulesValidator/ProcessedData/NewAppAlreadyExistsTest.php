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
    public function testIsValid(mixed $txcAppType, bool $hasBusReg, mixed $valid): void
    {
        $busReg = $hasBusReg ? m::mock(BusRegEntity::class) : null;
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
        yield [BusRegEntity::TXC_APP_NEW, true, false];
        yield [BusRegEntity::TXC_APP_NEW, false, true];
        yield [BusRegEntity::TXC_APP_CANCEL, true, true];
        yield [BusRegEntity::TXC_APP_CANCEL, false, true];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, true, true];
        yield [BusRegEntity::TXC_APP_NON_CHARGEABLE, false, true];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, true, true];
        yield [BusRegEntity::TXC_APP_CHARGEABLE, false, true];
    }
}
