<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EventHistoryDescriptionFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return EventHistoryDescription
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $router = $container->get('Router');
        $request = $container->get('Request');
        $urlHelper = $container->get('Helper\Url');
        return new EventHistoryDescription($router, $request, $urlHelper);
    }
}
