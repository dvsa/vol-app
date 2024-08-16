<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\Validation\Validators\ValidatorInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @template-extends AbstractPluginManager<ValidatorInterface>
 */
class ValidatorManager extends AbstractPluginManager
{
    protected $instanceOf = ValidatorInterface::class;
}
