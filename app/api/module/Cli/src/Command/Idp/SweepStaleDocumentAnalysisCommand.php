<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Command\Idp;

use Dvsa\Olcs\Api\Domain\Command\Document\SweepStaleDocumentAnalysis;
use Dvsa\Olcs\Cli\Command\Batch\AbstractBatchCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Resolves stale PENDING document analyses to TIMEOUT. This is the only thing covering lost
 * work in the pipeline, so it must stay scheduled - it is not optional maintenance.
 */
class SweepStaleDocumentAnalysisCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'idp:sweep-stale-document-analysis';

    #[\Override]
    protected function configure(): void
    {
        $this->setDescription('Resolve stale PENDING IDP document analyses to TIMEOUT.')
            ->addOption(
                'threshold-minutes',
                null,
                InputOption::VALUE_REQUIRED,
                'Age in minutes beyond which a PENDING analysis is considered timed out. '
                . 'Defaults to the configured idp.sweeper_threshold_minutes.'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $thresholdMinutes = $input->getOption('threshold-minutes');

        $result = $this->handleCommand([
            SweepStaleDocumentAnalysis::create([
                'thresholdMinutes' => $thresholdMinutes === null ? null : (int)$thresholdMinutes,
            ]),
        ]);

        if ($result) {
            $this->logAndWriteVerboseMessage('<error>Failed to sweep stale document analyses.</error>');
            return Command::FAILURE;
        }

        $this->logAndWriteVerboseMessage('<info>Successfully swept stale document analyses.</info>');
        return Command::SUCCESS;
    }
}
