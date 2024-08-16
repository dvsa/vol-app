<?php

namespace Dvsa\Olcs\AwsSdk\Factories;

use Aws\S3\S3Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class S3ClientFactory
 *
 * @package Dvsa\Olcs\AwsSdk\Factories
 */
class S3ClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return S3Client
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): S3Client
    {
        $config = $container->get('config');
        $s3Client = new S3Client([
            'region' => $config['awsOptions']['region'],
            'version' => $config['awsOptions']['version'],
        ]);
        /**
         * @var S3Client
         */
        return $s3Client;
    }
}
