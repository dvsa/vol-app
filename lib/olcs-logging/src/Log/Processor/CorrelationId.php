<?php

namespace Olcs\Logging\Log\Processor;

use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Stdlib\RequestInterface;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class CorrelationId implements ProcessorInterface
{
    private ?string $identifier = null;

    public function __construct(protected RequestInterface $request)
    {
    }

    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        $extra['correlationId'] = $this->getIdentifier();

        return $record->with(extra: $extra);
    }

    protected function getIdentifier(): ?string
    {
        if ($this->identifier !== null) {
            return $this->identifier;
        }

        if ($this->request instanceof HttpRequest) {
            $correlationHeader = $this->request->getHeader('X-Correlation-Id');

            if ($correlationHeader) {
                $this->identifier = $correlationHeader->getFieldValue();
            }
        }

        return $this->identifier;
    }
}
