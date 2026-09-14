<?php

namespace Dvsa\Olcs\Db\Service\Search;

use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Olcs\Logging\Log\Logger;
use OpenSearch\Client;
use OpenSearch\GuzzleClientFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

/**
 * Class ClientFactory
 * @package Olcs\Db\Service\Search
 */
class ClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Client
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws Exception\InvalidServiceException
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Client
    {
        $config = $container->get('config');
        if (!isset($config['elastic_search'])) {
            throw new Exception\InvalidServiceException('Elastic search config not found');
        }

        return (new GuzzleClientFactory())->create($this->getHttpClientOptions($config['elastic_search']));
    }

    /**
     * Map the elastic_search config block onto Guzzle client options
     *
     * @param array $config The elastic_search config block (host, port, transport, timeout, curl, log)
     *
     * @return array
     */
    public function getHttpClientOptions(array $config): array
    {
        $options = [
            'base_uri' => sprintf(
                '%s://%s:%s',
                strtolower($config['transport'] ?? 'http'),
                $config['host'] ?? 'localhost',
                $config['port'] ?? 9200
            ),
            // Same default request timeout as the previous (Elastica) client
            'timeout' => $config['timeout'] ?? 300,
        ];

        if (!empty($config['curl'])) {
            $options['curl'] = $config['curl'];
        }

        if (!empty($config['log'])) {
            $options['middleware'] = [
                Middleware::log(Logger::getLogger(), new MessageFormatter(MessageFormatter::SHORT), LogLevel::DEBUG),
            ];
        }

        return $options;
    }
}
