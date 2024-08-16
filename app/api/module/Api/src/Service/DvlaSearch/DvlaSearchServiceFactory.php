<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\DvlaSearch;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;
use Olcs\Logging\Log\LaminasLogPsr3Adapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DvlaSearchServiceFactory implements FactoryInterface
{
    /**
     * @var array<mixed>
     */
    protected $options;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DvlaSearchService
    {
        $logger = new LaminasLogPsr3Adapter($container->get('Logger'));
        $config = $container->get('config');
        $this->options = $config['dvla_search'];
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());

        $httpClient = new HttpClient([
            'handler' => $stack,
            'base_uri' => $this->getOptions('base_uri'),
            'proxy' => $this->getOptions('proxy'),
            'headers' => [
                'x-api-key' => $this->getOptions('api_key'),
                'user-agent' => 'olcs-dvla-search'
            ],
            'max_retry_attempts' => $this->getOptions('max_retry_attempts', 3),
            'retry_on_status' => [
                204,
                429,
                500,
                503
            ]
        ]);

        return new DvlaSearchService($httpClient, $logger);
    }

    /**
     * Gets options from configuration based on name.
     *
     *
     * @return mixed
     */
    public function getOptions(string $key, mixed $default = null)
    {
        $options = $this->options[$key] ?? $default;

        if (null === $options) {
            throw new \RuntimeException("Option could not be found: {$key}");
        }

        return $options;
    }
}
