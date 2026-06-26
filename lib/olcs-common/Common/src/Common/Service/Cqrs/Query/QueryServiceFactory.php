<?php

namespace Common\Service\Cqrs\Query;

use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Psr\Container\ContainerInterface;
use Laminas\Http\Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\Container;
use RunTimeException;

class QueryServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): QueryService
    {
        $config = $container->get('Config');

        $clientOptions = [];
        if (isset($config['cqrs_client'])) {
            $clientOptions = $config['cqrs_client'];
        }

        $client = new Client(null, $clientOptions);

        $adapter = new ClientAdapterLoggingWrapper();
        $adapter->wrapAdapter($client);

        $sessionName = $config['auth']['session_name'] ?? '';
        if (empty($sessionName)) {
            throw new RunTimeException("Missing auth.session_name from config");
        }

        return new QueryService(
            $container->get('ApiRouter'),
            $client,
            $container->get('CqrsRequest'),
            isset($config['debug']['showApiMessages']) && $config['debug']['showApiMessages'],
            $container->get('Helper\FlashMessenger'),
            new Container($sessionName)
        );
    }
}
