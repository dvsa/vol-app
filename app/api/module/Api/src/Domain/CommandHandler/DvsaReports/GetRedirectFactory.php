<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DvsaReports;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client;

class GetRedirectFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GetRedirect
    {
        return (new GetRedirect(new Client()))->__invoke($container, $requestedName, $options);
    }
}
