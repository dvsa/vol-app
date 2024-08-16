<?php

namespace Dvsa\Olcs\DocumentShare\Service;

use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use RuntimeException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as HttpClient;
use Sabre\DAV\Client as SabreClient;
use Psr\Container\ContainerInterface;

class ClientFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @throws \RuntimeException
     */
    public function getHttpClient(ContainerInterface $container): HttpClient
    {
        $options = $this->getOptions($container, 'http');
        $httpClient = new HttpClient();
        $httpClient->setOptions($options);

        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($httpClient);
        $wrapper->setShouldLogData(false);

        return $httpClient;
    }

    /**
     * Gets options from configuration based on name.
     *
     * @param string $key Key
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getOptions(ContainerInterface $container, $key)
    {
        if (is_null($this->options)) {
            $options = $container->get('Configuration');
            $this->options = $options['document_share'] ?? [];
        }

        $options = $this->options[$key] ?? null;

        if (null === $options) {
            throw new RuntimeException(
                sprintf(
                    'Options could not be found in "document_share.%s".',
                    $key
                )
            );
        }
        return $options;
    }

    /**
     * @param $clientOptions
     */
    private function validateWebDavConfig($clientOptions): void
    {
        if (empty($clientOptions['workspace'])) {
            throw new RuntimeException('Missing required option document_share.client.workspace');
        }

        if (empty($clientOptions['webdav_baseuri'])) {
            throw new RuntimeException('Missing required option document_share.client.webdav_baseuri');
        }

        if (empty($clientOptions['username'])) {
            throw new RuntimeException('Missing required option document_share.client.username');
        }

        if (empty($clientOptions['password'])) {
            throw new RuntimeException('Missing required option document_share.client.password');
        }
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $clientOptions = $this->getOptions($container, 'client');
        $clientOptions['httpClient'] = $this->getHttpClient($container);

        $this->validateWebDavConfig($clientOptions);
        $sabreClient = new SabreClient(
            [
                'baseUri' => $clientOptions['webdav_baseuri'],
                'username' => $clientOptions['username'],
                'password' => $clientOptions['password']
            ]
        );

        $adapter = new WebDAVAdapter($sabreClient, $clientOptions['workspace']);
        $fileSystem = new Filesystem($adapter);

        return new WebDavClient($fileSystem);
    }
}
