<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\VehicleUndertakingsOnlyLimousineConfirmationValidator;
use Common\RefData;

final class VehicleUndertakingsOnlyLimousineConfirmationValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testSmallVehiclesSkipped()
    {
        $validator = new VehicleUndertakingsOnlyLimousineConfirmationValidator(
            [
                'required_context_value' => 'anything',
            ]
        );

        $context = [
            'size' =>  RefData::PSV_VEHICLE_SIZE_SMALL,
        ];

        $this->assertTrue($validator->isValid('anything', $context));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpValidSizeProvider')]
    public function testIsValidWhenValid(string $vehicleSize): void
    {
        $validator = new VehicleUndertakingsOnlyLimousineConfirmationValidator(
            [
                'required_context_value' => 'Y',
            ]
        );

        $context = [
            'psvLimousines' => 'Y',
            'size' =>  $vehicleSize,
        ];

        $this->assertTrue($validator->isValid('Y', $context));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpValidSizeProvider')]
    public function testIsValidWhenNotValid(string $vehicleSize): void
    {
        $validator = new VehicleUndertakingsOnlyLimousineConfirmationValidator(
            [
                'required_context_value' => 'Y',
            ]
        );

        $context = [
            'psvLimousines' => 'Y',
            'size' =>  $vehicleSize,
        ];

        $this->assertFalse($validator->isValid('N', $context));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpValidSizeProvider')]
    public function testIsValidWhenValueIsNegativeButContextDoesNotMatch(string $vehicleSize): void
    {
        $validator = new VehicleUndertakingsOnlyLimousineConfirmationValidator(
            [
                'required_context_value' => 'N',
            ]
        );

        $context = [
            'psvLimousines' => 'Y',
            'size' =>  $vehicleSize,
        ];

        $this->assertTrue($validator->isValid('N', $context));
    }

    public static function dpValidSizeProvider(): \Iterator
    {
        yield ['vehicleSize' => RefData::PSV_VEHICLE_SIZE_MEDIUM_LARGE];
        yield ['vehicleSize' => RefData::PSV_VEHICLE_SIZE_BOTH];
    }
}
