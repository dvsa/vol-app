<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Doctrine\DBAL\Connection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

final class SetViFlags extends AbstractCommandHandler
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
        $stmt = $this->getDbConnection()->prepare('CALL vi_set_flags()');
        $stmt->executeQuery();
        $this->result->addMessage('VI Flags set');

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->setDbConnection($container->get('doctrine.connection.orm_default'));
        return parent::__invoke($container, $requestedName, $options);
    }
}
