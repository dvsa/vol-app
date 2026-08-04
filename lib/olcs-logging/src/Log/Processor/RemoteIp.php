<?php

namespace Olcs\Logging\Log\Processor;

use Laminas\Http\PhpEnvironment\RemoteAddress;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RemoteIp implements ProcessorInterface
{
    protected ?RemoteAddress $remoteAddress = null;

    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        $extra['remoteIp'] = $this->getRemoteAddress()->getIpAddress();

        return $record->with(extra: $extra);
    }

    public function getRemoteAddress(): RemoteAddress
    {
        if (!$this->remoteAddress instanceof RemoteAddress) {
            $this->remoteAddress = new RemoteAddress();
        }

        return $this->remoteAddress;
    }

    public function setRemoteAddress(RemoteAddress $remoteAddress): void
    {
        $this->remoteAddress = $remoteAddress;
    }
}
