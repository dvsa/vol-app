<?php

namespace CommonTest\Common\Service\Cqrs\Stub;

use Common\Service\Cqrs\CqrsTrait;

class CqrsTraitStub
{
    use CqrsTrait;

    public function testShowApiMessagesFromResponse(\Mockery\LegacyMockInterface $response): void
    {
        $this->showApiMessagesFromResponse($response);
    }

    /**
     * @param \Mockery\LegacyMockInterface&\Mockery\MockInterface&\Common\Service\Helper\FlashMessengerHelperService $msngr
     */
    public function setFlashMessenger(\Common\Service\Helper\FlashMessengerHelperService $msngr): void
    {
        $this->flashMessenger = $msngr;
    }
}
