<?php

declare(strict_types=1);

namespace Common\Form\Elements\Validators\Messages;

use Laminas\Validator\ValidatorPluginManager;

/**
 * @see \CommonTest\Form\Elements\Validators\Messages\ValidatorDefaultMessageProviderTest
 */
class ValidatorDefaultMessageProvider
{
    public function __construct(protected ValidatorPluginManager $pluginManager, private string $validatorName)
    {
    }

    public function getValidatorName(): string
    {
        return $this->validatorName;
    }

    public function __invoke(string $messageKey): ?string
    {
        $validator = $this->pluginManager->get($this->getValidatorName());
        return $validator->getMessageTemplates()[$messageKey] ?? null;
    }
}
