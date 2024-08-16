<?php

namespace Dvsa\Olcs\Queue\Service\Message;

interface MessageInterface
{
    public function processMessageData(): void;
}
