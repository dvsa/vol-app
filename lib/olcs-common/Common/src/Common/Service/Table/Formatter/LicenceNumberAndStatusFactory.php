<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LicenceNumberAndStatusFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return LicenceNumberAndStatus
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $urlHelper = $container->get(UrlHelperService::class);
        return new LicenceNumberAndStatus($urlHelper);
    }
}
