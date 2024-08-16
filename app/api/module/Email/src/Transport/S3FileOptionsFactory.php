<?php

namespace Dvsa\Olcs\Email\Transport;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class S3FileOptionsFactory implements FactoryInterface
{
    /**
     * @return array
     */
    private function extractConfig(array $config): array
    {
        $awsOptions = $config['awsOptions'];
        $s3Options = $awsOptions['s3Options'];
        $arraykey = array_search(
            \Dvsa\Olcs\Email\Transport\S3File::class,
            array_column($config['mail']['options']['transport'], 'type')
        );
        $bucket = $config['mail']['options']['transport'][$arraykey]['options']['bucket'];
        $key = $config['mail']['options']['transport'][$arraykey]['options']['key'];
        return [$awsOptions, $s3Options, $bucket, $key];
    }

    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return S3FileOptions
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): S3FileOptions
    {
        $config = $container->get('config');
        $s3Client = $container->get('S3Client');
        [$awsOptions, $s3Options, $bucket, $key] = $this->extractConfig($config);
        $s3fileOptions = new S3FileOptions([
            'awsOptions' => $awsOptions,
            's3Options' => $s3Options,
            's3Bucket' => $bucket,
            's3Key' => $key
        ], $s3Client);
        return $s3fileOptions;
    }
}
