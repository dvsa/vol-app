<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\CommunityLic\Activate as ActivateCommunityLic;
use Dvsa\Olcs\Cli\Domain\Command\CommunityLic\Suspend as SuspendCommunityLic;
use Dvsa\Olcs\Cli\Domain\Query\CommunityLic\CommunityLicencesForActivationList;
use Dvsa\Olcs\Cli\Domain\Query\CommunityLic\CommunityLicencesForSuspensionList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommunityLicencesCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:process-community-licences';

    protected function configure()
    {
        $this
            ->setDescription('Process community licences for suspension and activation.');
        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $dryRun = $input->getOption('dry-run');
        $date = (new \DateTime())->setTime(0, 0, 0)->format('Y-m-d');

        $suspensionResult = $this->handleQuery(CommunityLicencesForSuspensionList::create(['date' => $date]));
        $activationResult = $this->handleQuery(CommunityLicencesForActivationList::create(['date' => $date]));

        if (!$dryRun) {
            if (isset($suspensionResult['result']) && !empty($suspensionResult['result'])) {
                $this->logAndWriteVerboseMessage(sprintf("<info>%d community licence(s) found for suspension.</info>", count($suspensionResult['result'])));
                $this->handleCommand([SuspendCommunityLic::create(['communityLicenceIds' => array_column($suspensionResult['result'], 'id')])]);
            }

            if (isset($activationResult['result']) && !empty($activationResult['result'])) {
                $this->logAndWriteVerboseMessage(sprintf("<info>%d community licence(s) found for activation.</info>", count($activationResult['result'])));
                $this->handleCommand([ActivateCommunityLic::create(['communityLicenceIds' => array_column($activationResult['result'], 'id')])]);
            }
        } else {
            $this->logAndWriteVerboseMessage("<comment>Dry run enabled. No changes made.</comment>");
        }

        return Command::SUCCESS;
    }
}
