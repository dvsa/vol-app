<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Cease Goods Discs for and Application
 */
final class CeaseGoodsDiscsForApplication extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'GoodsDisc';

    protected $extraRepos = ['Application'];

    /**
     * Handle command
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscsForApplication $command */
        /** @var \Dvsa\Olcs\Api\Entity\Application\Application $application */
        $application = $this->getRepo('Application')->fetchById($command->getApplication());
        if ($application->isNew()) {
            // if New application, then for safety clear all discs connected to the licence
            $this->getRepo()->ceaseDiscsForLicence($application->getLicence()->getId());
        } else {
            // if variation, only clear on application as licence should be unaffected
            $this->getRepo()->ceaseDiscsForApplication($application->getId());
        }

        $this->result->addMessage('Ceased discs for Application.');

        return $this->result;
    }
}
