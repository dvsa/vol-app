<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Stub;

use Dvsa\Olcs\Api\Service\AbstractServiceManagerFactory;

/**
 * Stub class for testing AbstractServiceManagerFactory
 */
class AbstractServiceManagerFactoryStub extends AbstractServiceManagerFactory
{
    public const CONFIG_KEY = 'unit_SrvMngFactory';
    public const PLUGIN_MANAGER_CLASS = ServiceManagerStub::class;
}
