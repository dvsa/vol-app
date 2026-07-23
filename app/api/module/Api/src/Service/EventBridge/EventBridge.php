<?php

namespace Dvsa\Olcs\Api\Service\EventBridge;

use Aws\EventBridge\EventBridgeClient;
use Dvsa\Olcs\Api\Service\EventBridge\Events\EventInterface;

readonly class EventBridge
{
    private EventBridgeClient $eventBridgeClient;

    public function __construct(EventBridgeClient $eventBridgeClient)
    {
        $this->eventBridgeClient = $eventBridgeClient;
    }

    /**
     * @throws \JsonException
     */
    public function emit(EventInterface $event): void
    {
        $this->eventBridgeClient->putEvents([
            'Entries' => [
                [
                    'Source' => $event->getSource(),
                    'Version' => $event->getVersion(),
                    'DetailType' => $event->getName(),
                    'Time' => new \DateTimeImmutable(),
                    'Detail' => json_encode($event->getDetail(), JSON_THROW_ON_ERROR ),
                ],
            ],
        ]);
    }
}
