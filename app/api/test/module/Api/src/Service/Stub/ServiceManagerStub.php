<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Stub;

use Laminas\ServiceManager\AbstractPluginManager;

class ServiceManagerStub extends AbstractPluginManager
{
    /** @SuppressWarnings("unused") */
    public function validatePlugin(mixed $plugin): void
    {
    }
}
