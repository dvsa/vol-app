<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Ebsr;

use Aws\S3\S3Client;
use Aws\Sts\StsClient;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Logging\Log\LaminasLogPsr3Adapter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class S3ProcessorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return S3Processor
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): S3Processor
    {
        $config = $container->get('config');
        $stsClient = new StsClient([
                'region' => $config['awsOptions']['region'],
                'version' => '2011-06-15'
            ] + ($config['awsOptions']['sts'] ?? []) + ($config['awsOptions']['global'] ?? []));

        $arn = $config['ebsr']['txc_consumer_role_arn'];

        $result = $stsClient->AssumeRole([
            'RoleArn' => $arn,
            'RoleSessionName' => 'TransXChangeS3Session'
        ]);

        $s3ClientConfiguration = [
                'region' => $config['awsOptions']['region'],
                'version' => '2006-03-01',
                'credentials' => [
                    'key'    => $result['Credentials']['AccessKeyId'],
                    'secret' => $result['Credentials']['SecretAccessKey'],
                    'token'  => $result['Credentials']['SessionToken']
                ],
            ] + ($config['awsOptions']['s3'] ?? []) + ($config['awsOptions']['global'] ?? []);

        $bucketName = $config['ebsr']['input_s3_bucket'];
        $s3Client = new S3Client($s3ClientConfiguration);
        $fileUploader = $container->get('FileUploader');
        $logger = new LaminasLogPsr3Adapter($container->get('Logger'));
        return new S3Processor($s3Client, $bucketName, $fileUploader, $logger);
    }
}
