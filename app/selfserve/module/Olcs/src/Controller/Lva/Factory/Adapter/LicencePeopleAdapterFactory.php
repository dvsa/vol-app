<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva\Factory\Adapter;

use Common\Service\Lva\PeopleLvaService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;

class LicencePeopleAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicencePeopleAdapter
    {
        $peopleLvaService = $container->get(PeopleLvaService::class);
        return new LicencePeopleAdapter($container, $peopleLvaService);
    }
}
