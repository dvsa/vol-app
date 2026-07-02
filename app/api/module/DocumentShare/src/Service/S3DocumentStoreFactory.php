<?php

declare(strict_types=1);

namespace Dvsa\Olcs\DocumentShare\Service;

use Aws\S3\S3Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class S3DocumentStoreFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): S3DocumentStore
    {
        $config = $container->get('config');
        $s3Config = $config['document_share']['s3'] ?? [];

        if (empty($s3Config['bucket'])) {
            throw new RuntimeException('Missing required option document_share.s3.bucket');
        }

        return new S3DocumentStore(
            $container->get(S3Client::class),
            $s3Config['bucket'],
            $s3Config['key_prefix'] ?? '',
            $container->get('Logger')
        );
    }
}
