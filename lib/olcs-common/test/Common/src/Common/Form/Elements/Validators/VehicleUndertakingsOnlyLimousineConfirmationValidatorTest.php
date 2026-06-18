<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\VehicleUndertakingsOnlyLimousineConfirmationValidator;
use Common\RefData;

class VehicleUndertakingsOnlyLimousineConfirmationValidatorTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @dataProvider dpValidSizeProvider
     */
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

    /**
     * @dataProvider dpValidSizeProvider
     */
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

    /**
     * @dataProvider dpValidSizeProvider
     */
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

    public function dpValidSizeProvider(): array
    {
        return [
            ['large' => RefData::PSV_VEHICLE_SIZE_MEDIUM_LARGE],
            ['both' => RefData::PSV_VEHICLE_SIZE_BOTH],
        ];
    }
}
