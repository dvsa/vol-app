<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractBatchCommand extends AbstractOlcsCommand
{
    public function __construct(
        CommandHandlerManager $commandHandlerManager,
        protected QueryHandlerManager $queryHandlerManager
    ) {
        parent::__construct(
            $commandHandlerManager
        );
    }

    /**
     * Add common options to the command
     *
     * @return void
     */
    protected function addCommonOptions(): void
    {
        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'Perform a dry run without making any changes.'
        );
    }

    /**
     * Process a query DTO
     *
     * @return BundleSerializableInterface|mixed|null
     */
    protected function handleQuery(QueryInterface $dto)
    {
        try {
            $this->logAndWriteVerboseMessage("Handling query: " . $dto::class);
            $result = $this->queryHandlerManager->handleQuery($dto);
            return $result;
        } catch (NotFoundException $e) {
            $this->logAndWriteVerboseMessage("NotFoundException: {$e->getMessage()}", \Laminas\Log\Logger::WARN, true);
        } catch (\Exception $e) {
            $this->logAndWriteVerboseMessage("Exception: {$e->getMessage()}", \Laminas\Log\Logger::ERR, true);
        } catch (\Throwable $e) {
            $this->logAndWriteVerboseMessage("Unhandled Error: {$e->getMessage()}", \Laminas\Log\Logger::CRIT, true);
        }
        return null;
    }
}
