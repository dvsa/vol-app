<?php

namespace Common\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LinkBackFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LinkBack
    {
        $request = $container->get('Request');

        return new LinkBack($request);
    }
}
