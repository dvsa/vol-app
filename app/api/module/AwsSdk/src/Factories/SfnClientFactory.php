<?php

declare(strict_types=1);

namespace Dvsa\Olcs\AwsSdk\Factories;

use Aws\Sfn\SfnClient;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SfnClientFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SfnClient
    {
        $config = $container->get('config');

        return new SfnClient([
            'region'  => $config['awsOptions']['region'],
            'version' => $config['awsOptions']['version'],
        ]);
    }
}
