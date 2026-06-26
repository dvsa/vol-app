<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\Service\Helper\UrlHelperService;

class ExternalConversationLinkFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $urlHelper = $container->get(UrlHelperService::class);

        return new ExternalConversationLink($urlHelper);
    }
}
