<?php

namespace Olcs\Logging\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareTrait;

class LogException implements FactoryInterface
{
    use LoggerAwareTrait;

    public function logException(\Throwable $exception, array $messageData = []): void
    {
        $logMessages = [];

        do {
            $messageData['exception'] = $exception;
            $logMessages[] = $messageData;
            $exception = $exception->getPrevious();
        } while ($exception);

        $lastException = array_shift($logMessages);

        foreach (array_reverse($logMessages) as $logMessage) {
            $this->logger->info('', $logMessage);
        }

        $this->logger->error(
            get_class($lastException['exception']) . ' : ' . $lastException['exception']->getMessage(),
            $lastException
        );
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LogException
    {
        $this->setLogger($container->get('Logger'));
        return $this;
    }
}
