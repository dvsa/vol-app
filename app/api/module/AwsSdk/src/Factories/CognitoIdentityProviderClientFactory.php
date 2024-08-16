<?php

declare(strict_types=1);

namespace Dvsa\Olcs\AwsSdk\Factories;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CognitoIdentityProviderClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CognitoIdentityProviderClient
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CognitoIdentityProviderClient
    {
        $config = $container->get('config');
        return  new CognitoIdentityProviderClient([
            'version' => '2016-04-18',
            'region' => $config['awsOptions']['cognito']['region'],
        ]);
    }
}
