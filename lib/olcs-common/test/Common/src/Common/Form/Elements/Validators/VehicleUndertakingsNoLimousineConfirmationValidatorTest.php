<?php

/**
 * Test VehicleUndertakingsNoLimousineConfirmationValidator
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\VehicleUndertakingsNoLimousineConfirmationValidator;

/**
 * Test VehicleUndertakingsNoLimousineConfirmationValidator
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehicleUndertakingsNoLimousineConfirmationValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testIsValidWhenValid(): void
    {
        $validator = new VehicleUndertakingsNoLimousineConfirmationValidator(
            [
                'required_context_value' => 'Y'
            ]
        );

        $context = [
            'psvLimousines' => 'Y'
        ];

        $this->assertTrue($validator->isValid('Y', $context));
    }

    public function testIsValidWhenNotValid(): void
    {
        $validator = new VehicleUndertakingsNoLimousineConfirmationValidator(
            [
                'required_context_value' => 'Y'
            ]
        );

        $context = [
            'psvLimousines' => 'Y'
        ];

        $this->assertFalse($validator->isValid('N', $context));
    }

    public function testIsValidWhenValueIsNegativeButContextDoesNotMatch(): void
    {
        $validator = new VehicleUndertakingsNoLimousineConfirmationValidator(
            [
                'required_context_value' => 'N'
            ]
        );

        $context = [
            'psvLimousines' => 'Y'
        ];

        $this->assertTrue($validator->isValid('N', $context));
    }
}
