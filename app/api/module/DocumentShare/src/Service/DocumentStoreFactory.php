<?php

declare(strict_types=1);

namespace Dvsa\Olcs\DocumentShare\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Selects the document store backend at runtime from the `document_share.backend` config value
 * ('webdav' | 's3', defaulting to 'webdav'). Because that value is resolved from SSM / Secrets
 * Manager per environment, the WebDAV->S3 cutover (and rollback) is a config change, not a deploy.
 */
class DocumentStoreFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DocumentStoreInterface
    {
        $config = $container->get('config');
        $backend = $config['document_share']['backend'] ?? 'webdav';

        $factory = $backend === 's3'
            ? new S3DocumentStoreFactory()
            : new ClientFactory();

        return $factory->__invoke($container, $requestedName, $options);
    }
}
