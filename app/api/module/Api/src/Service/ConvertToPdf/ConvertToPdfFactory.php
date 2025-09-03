<?php

namespace Dvsa\Olcs\Api\Service\ConvertToPdf;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as HttpClient;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Psr\Container\ContainerInterface;

class ConvertToPdfFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ConvertToPdfInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConvertToPdfInterface
    {
        $config = $container->get('config');
        $logger = $container->get('Logger');
        
        if (!isset($config['convert_to_pdf']['uri'])) {
            throw new \RuntimeException('Missing print service config[convert_to_pdf][uri]');
        }

        $type = $config['convert_to_pdf']['type'] ?? 'webservice';
        $uri = $config['convert_to_pdf']['uri'];
        $httpOptions = $config['convert_to_pdf']['options'] ?? [];

        if ($type === 'gotenberg') {
            $httpClient = new HttpClient($uri, $httpOptions);
            $wrapper = new ClientAdapterLoggingWrapper();
            $wrapper->wrapAdapter($httpClient);
            $wrapper->setShouldLogData(false);
            
            // Configure S3 for Gotenberg (only in dev/qa/local environments)
            $s3Client = null;
            $s3Bucket = null;
            $s3KeyPrefix = null;
            
            $environment = strtolower(getenv('ENVIRONMENT_NAME') ?: '');
            $allowedEnvironments = ['dev', 'qa', 'local'];
            
            $logger->info('Gotenberg S3 Config - Environment: ' . $environment);
            
            if (in_array($environment, $allowedEnvironments)) {
                try {
                    $logger->info('Attempting to configure S3 for Gotenberg');
                    $s3Client = $container->get(\Aws\S3\S3Client::class);
                    
                    // Use the same bucket as email storage
                    // Look for S3File transport configuration
                    if (isset($config['mail']['options']['transport'])) {
                        foreach ($config['mail']['options']['transport'] as $transport) {
                            if (isset($transport['type']) && $transport['type'] === \Dvsa\Olcs\Email\Transport\S3File::class) {
                                $s3Bucket = $transport['options']['bucket'] ?? null;
                                $s3KeyPrefix = $transport['options']['key'] ?? 'pdf-conversions';
                                break;
                            }
                        }
                    }
                    
                    // Fallback to hardcoded bucket if not found in mail config
                    if (!$s3Bucket) {
                        $s3Bucket = 'devapp-olcs-pri-olcs-autotest-s3';
                        $s3KeyPrefix = $config['olcs']['domain'] ?? 'unknown';
                    }
                    
                    $logger->info('S3 configured - Bucket: ' . $s3Bucket . ', KeyPrefix: ' . $s3KeyPrefix);
                } catch (\Exception $e) {
                    // S3 is optional, log error but continue
                    $logger->err('Failed to configure S3 for GotenbergClient: ' . $e->getMessage());
                }
            } else {
                $logger->info('S3 not configured - environment ' . $environment . ' not in allowed list');
            }
            
            return new GotenbergClient($httpClient, $uri, $s3Client, $s3Bucket, $s3KeyPrefix, $logger);
        }
        
        // Default to WebServiceClient for backward compatibility
        $httpClient = new HttpClient($uri, $httpOptions);
        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($httpClient);
        $wrapper->setShouldLogData(false);
        
        return new WebServiceClient($httpClient);
    }
}