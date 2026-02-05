<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Doctrine\DBAL\Connection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Reset Permits Test Data Command Handler
 *
 * Executes stored procedures to clear and repopulate permits test data
 * for VFT regression testing in non-production environments.
 */
final class ResetPermitsTestData extends AbstractCommandHandler implements TransactionedInterface
{
    private Connection $dbConnection;

    private function setDbConnection(Connection $dbConnection): void
    {
        $this->dbConnection = $dbConnection;
    }

    private function getDbConnection(): Connection
    {
        return $this->dbConnection;
    }

    public function handleCommand(CommandInterface $command): Result
    {
        // Step 1: Clear existing permits data
        $this->result->addMessage('Clearing existing permits data...');
        $clearStmt = $this->getDbConnection()->prepare('CALL sp_permits_test_reset_clear()');
        $clearStmt->executeQuery();
        $this->result->addMessage('Existing permits data cleared successfully');

        // Step 2: Populate fresh test data
        $this->result->addMessage('Populating fresh permits test data...');
        $populateStmt = $this->getDbConnection()->prepare('CALL sp_permits_test_reset_populate()');
        $populateStmt->executeQuery();
        $this->result->addMessage('Permits test data populated successfully');

        $this->result->addMessage('Permits test data reset completed');

        return $this->result;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->setDbConnection($container->get('doctrine.connection.orm_default'));
        return parent::__invoke($container, $requestedName, $options);
    }
}
