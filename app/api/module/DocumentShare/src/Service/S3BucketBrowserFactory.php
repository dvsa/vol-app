<?php

declare(strict_types=1);

namespace Dvsa\Olcs\DocumentShare\Service;

use Aws\S3\S3Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class S3BucketBrowserFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): S3BucketBrowser
    {
        $config = $container->get('config');

        return new S3BucketBrowser(
            $container->get(S3Client::class),
            // Don't throw on a missing bucket here: the handler is constructed before the
            // feature-toggle and system-admin gates run, so a hard failure would 500 a disabled or
            // unauthorised request. The browser fails closed at call time instead.
            (string) ($config['document_share']['s3']['bucket'] ?? ''),
            // Root the browser at the same prefix the document store uses, so admins land in the
            // document store rather than the raw bucket root (empty = whole bucket).
            (string) ($config['document_share']['s3']['key_prefix'] ?? ''),
            $container->get('Logger')
        );
    }
}
