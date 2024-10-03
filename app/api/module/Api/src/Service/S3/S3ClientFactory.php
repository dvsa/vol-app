<?php

namespace Dvsa\Olcs\Api\Service\S3;

use Aws\S3\S3Client;
use Psr\Container\ContainerInterface;

class S3ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $awsOptions = $config['awsOptions'];

        $s3Client = new S3Client([
            'version'     => $awsOptions['version'],
            'region'      => $awsOptions['region'],
            'use_path_style_endpoint' => $awsOptions['s3']['use_path_style_endpoint'],
        ]);

        return $s3Client;
    }
}
