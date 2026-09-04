<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Command\Idp;

use Dvsa\Olcs\Api\Domain\Command\Document\StoreDocumentAnalysisResult as Cmd;
use Dvsa\Olcs\Cli\Command\Batch\AbstractBatchCommand;
use Olcs\Logging\Log\Logger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Retrieves an IDP document analysis result from S3 and stores it in document_analysis.
 *
 * All failures are treated as retryable (exit code 1) so AWS Batch will retry.
 */
class StoreDocumentAnalysisResultCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'idp:store-document-analysis-result';

    #[\Override]
    protected function configure(): void
    {
        $this->setDescription('Retrieve an IDP analysis result from S3 and store it in document_analysis.')
            ->addOption(
                'analysis-token',
                null,
                InputOption::VALUE_REQUIRED,
                'UUID of the document_analysis row to update.'
            )
            ->addOption(
                'execution-arn',
                null,
                InputOption::VALUE_REQUIRED,
                'ARN of the Step Functions execution whose output contains the S3 result location.'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $analysisToken = $input->getOption('analysis-token');
        $executionArn  = $input->getOption('execution-arn');

        if (empty($analysisToken) || empty($executionArn)) {
            Logger::log(LogLevel::ERROR, 'idp:store-document-analysis-result: --analysis-token and --execution-arn are required');
            return Command::FAILURE;
        }

        try {
            $result = $this->commandHandlerManager->handleCommand(
                Cmd::create([
                    'analysisToken' => $analysisToken,
                    'executionArn'  => $executionArn,
                ])
            );

            foreach ($result->getMessages() as $message) {
                $this->logAndWriteVerboseMessage($message);
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Logger::log(LogLevel::ERROR, 'idp:store-document-analysis-result: failure: ' . $e->getMessage());
            $this->logAndWriteVerboseMessage('<error>Failure: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
