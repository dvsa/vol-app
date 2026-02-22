<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention;

use Doctrine\DBAL\Driver\PDO\Connection as PDOConnection;
use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\Command\DataRetention\Precheck as PrecheckCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

final class Precheck extends AbstractCommandHandler
{
    private \PDO $connection;

    protected $extraRepos = ['SystemParameter'];

    /**
     * Handle command
     *
     * @param CommandInterface|PrecheckCommand $command DTO
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $limit = $command->getLimit();
        if ($limit <= 0) {
            $limit = $this->getDefaultLimit();
            $this->result->addMessage(
                "Limit option not set or invalid; defaulting to SystemParameter::DR_DELETE_LIMIT=$limit"
            );
        }

        $this->result->addMessage(
            "Calling stored procedure sp_dr_precheck($limit)"
        );

        /** @var \PDOStatement $stmt */
        $stmt = $this->connection->prepare("CALL sp_dr_precheck($limit);");
        $stmt->execute();
        $this->result->addMessage("Precheck procedure executed.");
        return $this->result;
    }

    /**
     * Fetches the default Data Retention Delete Limit from the SystemParameter table.
     *
     * @return int
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function getDefaultLimit(): int
    {
        /** @var SystemParameter $systemParameterRepo */
        $systemParameterRepo = $this->getRepo('SystemParameter');

        return $systemParameterRepo->getDataRetentionDeleteLimit();
    }
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
