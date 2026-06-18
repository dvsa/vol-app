<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SearchApplicationLicenceNoFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchApplicationLicenceNo
    {
        $urlHelper = $container->get('Helper\Url');
        return new SearchApplicationLicenceNo($urlHelper);
    }
}
