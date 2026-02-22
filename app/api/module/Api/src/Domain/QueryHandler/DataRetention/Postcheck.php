<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention;

use Doctrine\DBAL\Driver\PDO\Connection as PDOConnection;
use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Data Retention post-delete checker
 */
class Postcheck extends AbstractQueryHandler
{
    /** @var PDOConnection  */
    private \PDO $connection;

    /**
     * Execute post-check stored procedure and
     *
     * @param QueryInterface $query
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->connection->prepare('CALL sp_dr_postcheck();');
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $results;
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Postcheck
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $this->connection = $entityManager->getConnection()->getNativeConnection();
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
