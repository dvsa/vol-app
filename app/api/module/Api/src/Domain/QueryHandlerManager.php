<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Query\Queue\NextItem;
use Dvsa\Olcs\Transfer\Query\LoggerOmitResponseInterface;
use Psr\Container\ContainerInterface;
use Olcs\Logging\Log\Logger;
use Laminas\ServiceManager\AbstractPluginManager;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\QueryHandlerInterface;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\HandlerInterface as ValidationHandlerInterface;

/**
 * @template-extends AbstractPluginManager<QueryHandlerInterface>
 */
class QueryHandlerManager extends AbstractPluginManager
{
    protected $instanceOf = QueryHandlerInterface::class;
    private readonly ValidationHandlerManager $validationHandlerManager;

    private array $queryLoggingExcluded = [
        NextItem::class,
    ];

    public function __construct(ContainerInterface $container, array $config = [])
    {
        $this->validationHandlerManager = $container->get('ValidationHandlerManager');
        parent::__construct($container, $config);
    }

    public function handleQuery(QueryInterface $query, $validate = true)
    {
        $start = microtime(true);

        $queryFqcl = $query::class;
        $shouldLogQuery = !in_array($queryFqcl, $this->queryLoggingExcluded);

        /** @var QueryHandlerInterface $queryHandler */
        $queryHandler = $this->get($queryFqcl);

        if ($shouldLogQuery) {
            Logger::debug(
                'Query Received: ' . $queryFqcl,
                ['data' => ['queryData' => $query->getArrayCopy()]],
            );
        }

        $queryHandler->checkEnabled();

        $queryHandlerFqcl = $queryHandler::class;

        if ($validate) {
            $this->validateDto($query, $queryHandlerFqcl);
        }

        $response = $queryHandler->handleQuery($query);
        if ($query instanceof LoggerOmitResponseInterface) {
            $logData = ['*** OMITTED ***'];
        } elseif (is_object($response) && $response instanceof QueryHandler\BundleSerializableInterface) {
            // if response is an Entity
            $logData = $response->serialize();
        } else {
            $logData = (array)$response;
        }

        if ($shouldLogQuery) {
            Logger::debug(
                'Query Handler Response: ' . $queryHandlerFqcl,
                [
                    'data' => [
                        'response' => $logData,
                        'time' => round(microtime(true) - $start, 5),
                    ],
                ]
            );
        }

        return $response;
    }

    protected function validateDto($dto, $queryHandlerFqcl)
    {
        /** @var ValidationHandlerInterface $validationHandler */
        $validationHandler = $this->validationHandlerManager->get($queryHandlerFqcl);

        if (!$validationHandler->isValid($dto)) {
            Logger::debug(
                'DTO Failed validation',
                [
                    'handler' => $queryHandlerFqcl,
                    'data' => $dto->getArrayCopy(),
                ]
            );
            throw new ForbiddenException('You do not have access to this resource');
        }
    }
}
