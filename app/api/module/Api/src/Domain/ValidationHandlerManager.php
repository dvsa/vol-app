<?php

namespace Dvsa\Olcs\Api\Domain;

use Laminas\ServiceManager\AbstractPluginManager;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\HandlerInterface;

/**
 * @template-extends AbstractPluginManager<HandlerInterface>
 */
class ValidationHandlerManager extends AbstractPluginManager
{
    protected $instanceOf = HandlerInterface::class;
}
