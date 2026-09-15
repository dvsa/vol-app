<?php

declare(strict_types=1);

namespace Common\Form\Elements\Validators;

use Common\RefData;
use Laminas\Validator\AbstractValidator;

class VehicleUndertakingsOnlyLimousineConfirmationValidator extends AbstractValidator
{
    protected array $messageTemplates = [
        'required' => 'application_vehicle-safety_undertakings.limousines.required'
    ];

    #[\Override]
    public function isValid(mixed $value, ?array $context = [])
    {
        $requiredContext = $this->getOption('required_context_value');

        //we ignore this field for small vehicles
        if ($context['size'] === RefData::PSV_VEHICLE_SIZE_SMALL) {
            return true;
        }

        // This only gets used if psvOperateSmallVhl is shown
        if ($context['psvLimousines'] === $requiredContext && $value !== 'Y') {
            $this->error('required');

            return false;
        }

        return true;
    }
}
