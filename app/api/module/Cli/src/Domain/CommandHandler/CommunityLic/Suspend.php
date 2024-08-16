<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;

/**
 * Suspend community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Suspend extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getCommunityLicenceIds();
        $communityLicences = $this->getRepo()->fetchByIds($ids);
        foreach ($communityLicences as $communityLicence) {
            $communityLicence->setStatus(
                $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_SUSPENDED)
            );
            $this->getRepo()->save($communityLicence);
            $this->result->addMessage("Community licence {$communityLicence->getId()} suspended");
        }

        return $this->result;
    }
}
