<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva\Factory\Adapter;

use Common\Service\Lva\PeopleLvaService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;

class ApplicationPeopleAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationPeopleAdapter
    {
        $peopleLvaService = $container->get(PeopleLvaService::class);
        return new ApplicationPeopleAdapter($container, $peopleLvaService);
    }
}
