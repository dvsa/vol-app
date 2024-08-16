<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;

/**
 * Activate community licences
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Activate extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';

    protected $extraRepos = [
        'CommunityLicSuspension',
        'CommunityLicSuspensionReason'
    ];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getCommunityLicenceIds();
        $communityLicences = $this->getRepo()->fetchByIds($ids);
        foreach ($communityLicences as $communityLicence) {
            $communityLicence->setStatus(
                $this->getRepo()->getRefdataReference(CommunityLicEntity::STATUS_ACTIVE)
            );
            $this->removeSuspensionsAndReasons($communityLicence);
            $this->getRepo()->save($communityLicence);
            $this->result->addMessage("Community licence {$communityLicence->getId()} activated");
        }

        return $this->result;
    }

    /**
     * Remove suspension and reasons
     *
     * @param CommunityLicEntity $communityLicence community licence
     *
     * @return void
     */
    protected function removeSuspensionsAndReasons($communityLicence)
    {
        $suspensions = $communityLicence->getCommunityLicSuspensions();
        foreach ($suspensions as $suspension) {
            $suspensionReasons = $suspension->getCommunityLicSuspensionReasons();

            foreach ($suspensionReasons as $reason) {
                $this->getRepo('CommunityLicSuspensionReason')->delete($reason);
            }
            $this->getRepo('CommunityLicSuspension')->delete($suspension);
        }
    }
}
