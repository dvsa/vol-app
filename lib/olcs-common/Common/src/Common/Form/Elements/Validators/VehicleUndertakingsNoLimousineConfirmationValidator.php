<?php

declare(strict_types=1);

namespace Common\Form\Elements\Validators;

use Laminas\Validator\AbstractValidator;

class VehicleUndertakingsNoLimousineConfirmationValidator extends AbstractValidator
{
    protected array $messageTemplates = [
        'required' => 'application_vehicle-safety_undertakings.limousines.required'
    ];

    #[\Override]
    public function isValid(mixed $value, ?array $context = []): bool
    {
        $requiredContext = $this->getOption('required_context_value');

        if ($context['psvLimousines'] === $requiredContext && $value !== 'Y') {
            $this->error('required');
            return false;
        }

        return true;
    }
}
