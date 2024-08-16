<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\AbstractServiceManagerFactory;

class ValidatorManagerFactory extends AbstractServiceManagerFactory
{
    public const CONFIG_KEY = 'validator_services';
    public const PLUGIN_MANAGER_CLASS = ValidatorManager::class;
}
