<?php

namespace Dvsa\Olcs\Api\Service\EventBridge\Events;

interface EventInterface
{
    public function getName(): string;
    public function getSource(): string;
    public function getVersion(): int;
    public function getDetail(): array;
}
