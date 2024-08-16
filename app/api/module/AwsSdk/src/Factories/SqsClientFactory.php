<?php

namespace Dvsa\Olcs\AwsSdk\Factories;

use Aws\Sqs\SqsClient;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class S3ClientFactory
 *
 * @package Dvsa\Olcs\AwsSdk\Factories
 */
class SqsClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SqsClient
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SqsClient
    {
        $config = $container->get('config');
        $sqsClient = new SqsClient([
            'region' => $config['awsOptions']['region'],
            'version' => $config['awsOptions']['version'],
            'http'    => [
                'proxy' => $config['companies_house_connection']['proxy']
            ]
        ]);
        /**
         * @var SqsClient
         */
        return $sqsClient;
    }
}
