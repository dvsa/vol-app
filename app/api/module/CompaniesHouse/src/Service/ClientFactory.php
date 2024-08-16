<?php

namespace Dvsa\Olcs\CompaniesHouse\Service;

use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as HttpClient;

class ClientFactory implements FactoryInterface
{
    public const CONFIG_NAMESPACE = 'companies_house';

    /**
     * @var array
     */
    protected $options;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Client
    {
        $client = new Client();

        $httpOptions = $this->getOptions($container, 'http');
        $httpClient = new HttpClient();
        $httpClient->setOptions($httpOptions);

        $authOptions = $this->getOptions($container, 'auth');
        if (isset($authOptions['username']) && isset($authOptions['password'])) {
            $httpClient->setAuth($authOptions['username'], $authOptions['password']);
        }

        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($httpClient);

        $client->setHttpClient($httpClient);

        $clientOptions = $this->getOptions($container, 'client');
        if (empty($clientOptions['baseuri'])) {
            throw new RuntimeException(sprintf('Missing required option %s.client.baseuri', static::CONFIG_NAMESPACE));
        }
        $client->setBaseUri($clientOptions['baseuri']);

        return $client;
    }

    /**
     * Gets options from configuration based on name.
     */
    public function getOptions(ContainerInterface $sl, string $key): array
    {
        if (is_null($this->options)) {
            $options = $sl->get('Configuration');
            $this->options = $options[static::CONFIG_NAMESPACE] ?? [];
        }

        $options = $this->options[$key] ?? null;

        if (is_null($options)) {
            throw new RuntimeException(
                sprintf('Options could not be found in "%s.%s".', static::CONFIG_NAMESPACE, $key)
            );
        }

        return $options;
    }
}
