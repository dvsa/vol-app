<?php

namespace Dvsa\Olcs\Api\Service\EventBridge;

use Aws\EventBridge\EventBridgeClient;

class EventBridgeFactory
{
    public function __invoke($container): EventBridge
    {
        $eventBridgeClient = $container->get(EventBridgeClient::class);
        return new EventBridge($eventBridgeClient);
    }
}
