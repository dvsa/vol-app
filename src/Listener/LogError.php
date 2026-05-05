<?php

namespace Olcs\Logging\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Logging\Helper\LogException;
use Olcs\Logging\Log\Processor\RequestId;
use Psr\Container\ContainerInterface;

class LogError implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    private ?string $identifier = null;

    protected ?LogException $logExceptionHelper = null;

    public function setLogExceptionHelper(LogException $logExceptionHelper): void
    {
        $this->logExceptionHelper = $logExceptionHelper;
    }

    public function getLogExceptionHelper(): ?LogException
    {
        return $this->logExceptionHelper;
    }

    #[\Override]
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onDispatchError'], 0);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'onDispatchError'], 0);
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LogError
    {
        $this->setLogExceptionHelper($container->get(LogException::class));
        $this->setIdentifier(
            $container->get(RequestId::class)->getIdentifier()
        );

        return $this;
    }

    public function onDispatchError(MvcEvent $e)
    {
        if (!$e->getParam('exception')) {
            return;
        }
        // Don't log these exceptions
        if ($e->getParam('exceptionNoLog')) {
            return;
        }
        $data = [];

        $routeMatch = $e->getRouteMatch();
        if ($routeMatch) {
            $data = $routeMatch->getParams();
        }

        $this->getLogExceptionHelper()->logException(
            $e->getParam('exception'),
            ['data' => $data]
        );
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }
}
