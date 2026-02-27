<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\VariationNumber;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class VariationNumberTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ProcessedData
 */
class VariationNumberTest extends MockeryTestCase
{
    /**
     * tests isValid returns true for a valid new app
     */
    public function testValidNewApp(): void
    {
        $sut = new VariationNumber();

        $value = $this->getValue(BusRegEntity::TXC_APP_NEW, 0);
        $isValid = $sut->isValid($value, ['busReg' => null]);

        $this->assertEquals(true, $isValid);
        $this->assertCount(0, $sut->getMessages());
    }

    /**
     * tests isValid returns false for invalid new app, checks correct error message
     */
    public function testInvalidNewApp(): void
    {
        $sut = new VariationNumber();

        $value = $this->getValue(BusRegEntity::TXC_APP_NEW, 1);
        $isValid = $sut->isValid($value, ['busReg' => null]);
        $messages = $sut->getMessages();

        $this->assertEquals(false, $isValid);
        $this->assertEquals(
            $sut->getMessageTemplates()[VariationNumber::NEW_VARIATION_NUMBER_ERROR],
            $messages[VariationNumber::NEW_VARIATION_NUMBER_ERROR]
        );
        $this->assertCount(1, $messages);
    }

    /**
     * tests isValid returns true for a valid cancellation
     */
    public function testValidCancellation(): void
    {
        $sut = new VariationNumber();
        $variationNo = 1;

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getVariationNo')->once()->withNoArgs()->andReturn($variationNo);

        $value = $this->getValue(BusRegEntity::TXC_APP_CANCEL, $variationNo);
        $isValid = $sut->isValid($value, ['busReg' => $busReg]);

        $this->assertEquals(true, $isValid);
        $this->assertCount(0, $sut->getMessages());
    }

    /**
     * tests isValid returns false for invalid cancellation, and checks message
     */
    public function testInvalidCancellation(): void
    {
        $sut = new VariationNumber();
        $expectedVariationNo = 1;
        $error = 'For cancellations, the variation number must be equal to previous variation number. '
            . 'The expected variation number was ' . $expectedVariationNo;

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getVariationNo')->once()->withNoArgs()->andReturn($expectedVariationNo);

        $value = $this->getValue(BusRegEntity::TXC_APP_CANCEL, 2);
        $isValid = $sut->isValid($value, ['busReg' => $busReg]);
        $messages = $sut->getMessages();

        $this->assertEquals(false, $isValid);
        $this->assertEquals(
            $messages[VariationNumber::CANCELLATION_VARIATION_NUMBER_ERROR],
            $error
        );
        $this->assertCount(1, $messages);
    }

    /**
     * tests isValid returns true for a valid variation
     *
     *
     * @param string $txcAppType
     * @param int $variationNoValue
     * @param int $variationNoBusReg
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('validVariationProvider')]
    public function testValidVariation(mixed $txcAppType, mixed $variationNoValue, mixed $variationNoBusReg): void
    {
        $sut = new VariationNumber();

        $variationBusReg = m::mock(BusRegEntity::class);
        $variationBusReg->shouldReceive('getVariationNo')->once()->withNoArgs()->andReturn($variationNoBusReg);

        $regNo = 'OB1234567/8';
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getRegNo')->once()->withNoArgs()->andReturn($regNo);
        $busReg->shouldReceive('getLicence->getLatestBusVariation')
            ->once()
            ->with($regNo, [])
            ->andReturn($variationBusReg);

        $value = $this->getValue($txcAppType, $variationNoValue);
        $this->assertEquals(true, $sut->isValid($value, ['busReg' => $busReg]));
        $this->assertCount(0, $sut->getMessages());
    }

    /**
     * Provider for testValidVariation
     *
     * @return array
     */
    public static function validVariationProvider(): array
    {
        return [
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, 1, 0],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, 2, 1],
            [BusRegEntity::TXC_APP_CHARGEABLE, 1, 0],
            [BusRegEntity::TXC_APP_CHARGEABLE, 2, 1],
        ];
    }

    /**
     * tests isValid returns false for the correct data, as well as the correct error message
     *
     *
     * @param string $txcAppType
     * @param int $variationNoValue
     * @param int $variationNoBusReg
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidVariationProvider')]
    public function testInvalidVariation(mixed $txcAppType, mixed $variationNoValue, mixed $variationNoBusReg): void
    {
        $sut = new VariationNumber();

        $expectedVariationNo = $variationNoBusReg + 1;
        $error = 'For variations, the variation number should be one greater than the previous variation number. '
            . 'The expected variation number was ' . $expectedVariationNo;

        $variationBusReg = m::mock(BusRegEntity::class);
        $variationBusReg->shouldReceive('getVariationNo')->once()->withNoArgs()->andReturn($variationNoBusReg);

        $regNo = 'OB1234567/8';
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getRegNo')->once()->withNoArgs()->andReturn($regNo);
        $busReg->shouldReceive('getLicence->getLatestBusVariation')
            ->once()
            ->with($regNo, [])
            ->andReturn($variationBusReg);

        $value = $this->getValue($txcAppType, $variationNoValue);
        $this->assertEquals(false, $sut->isValid($value, ['busReg' => $busReg]));
        $messages = $sut->getMessages();

        $this->assertEquals($error, $messages[VariationNumber::VARIATION_VARIATION_NUMBER_ERROR]);
        $this->assertCount(1, $messages);
    }

    /**
     * Provider for testInvalidVariation
     *
     * @return array
     */
    public static function invalidVariationProvider(): array
    {
        return [
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, 2, 0],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, 0, 0],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, 0, 1],
            [BusRegEntity::TXC_APP_NON_CHARGEABLE, 0, 2],
            [BusRegEntity::TXC_APP_CHARGEABLE, 2, 0],
            [BusRegEntity::TXC_APP_CHARGEABLE, 0, 0],
            [BusRegEntity::TXC_APP_CHARGEABLE, 0, 1],
            [BusRegEntity::TXC_APP_CHARGEABLE, 0, 2]
        ];
    }

    /**
     * @param $txcAppType
     * @param $variationNo
     * @return array
     */
    private function getValue(mixed $txcAppType, mixed $variationNo): array
    {
        return [
            'txcAppType' => $txcAppType,
            'variationNo' => $variationNo
        ];
    }
}
