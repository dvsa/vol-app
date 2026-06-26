<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\Http\Request;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InternalConversationLinkFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $formatterPluginManager = $container->get(FormatterPluginManager::class);
        $refDataStatusFormatter = $formatterPluginManager->get(RefDataStatus::class);
        $urlHelper = $container->get('Helper\Url');
        $route = $container->get(RouteStackInterface::class)->match($container->get(Request::class));

        return new InternalConversationLink($urlHelper, $refDataStatusFormatter, $route);
    }
}
