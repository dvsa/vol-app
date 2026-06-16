<?php

namespace Olcs\Logging\Log\Processor;

use Laminas\Session\Container;
use Laminas\Session\ManagerInterface as Manager;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class SessionId implements ProcessorInterface
{
    protected ?Manager $sessionManager = null;

    public function setSessionManager(Manager $sessionManager): SessionId
    {
        $this->sessionManager = $sessionManager;
        return $this;
    }

    public function getSessionManager(): Manager
    {
        if (!$this->sessionManager instanceof Manager) {
            $this->sessionManager = Container::getDefaultManager();
        }
        return $this->sessionManager;
    }

    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        // This currently uses the php/laminas session id, could be altered to use open AM sessid when an auth solution
        // has been implemented
        $this->getSessionManager()->start();

        $extra = $record->extra;
        $extra['sessionId'] = $this->getSessionManager()->getId();

        return $record->with(extra: $extra);
    }
}
