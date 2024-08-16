<?php

/**
 * In Force Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Transfer\Command\Application\PrintInterimDocument as PrintInterimDocumentCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Domain\Command\Application\InForceInterim as Cmd;
use Dvsa\Olcs\Transfer\Query\Application\Application;

/**
 * In Force Interim
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class InForceInterim extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['GoodsDisc', 'CommunityLic'];

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        // @NOTE This needs to be done before processing Community licences
        $application->setInterimStatus(
            $this->getRepo()->getRefdataReference(ApplicationEntity::INTERIM_STATUS_INFORCE)
        );

        $this->grantInterim($application);

        $this->result->addMessage('Interim status updated');
        $this->getRepo()->save($application);

        return $this->result;
    }

    private function grantInterim(ApplicationEntity $application)
    {
        $this->processLicenceVehicleSaving($application);

        $this->processCommunityLicences($application);

        $this->result->merge($this->handleSideEffect(PrintInterimDocumentCmd::create(['id' => $application->getId()])));
    }

    private function processLicenceVehicleSaving(ApplicationEntity $application)
    {
        $count = 0;
        $ceasedCount = 0;
        /** @var LicenceVehicle $licenceVehicle */
        foreach ($application->getLicenceVehicles() as $licenceVehicle) {

            /** @var GoodsDisc $disc */
            foreach ($licenceVehicle->getGoodsDiscs() as $disc) {
                if ($disc->getCeasedDate() == null) {
                    $disc->setCeasedDate(new DateTime());
                    $ceasedCount++;
                }
            }

            if ($licenceVehicle->getInterimApplication() !== null) {
                $count++;
                $licenceVehicle->setSpecifiedDate(new DateTime());

                $newDisc = new GoodsDisc($licenceVehicle);
                $newDisc->setIsInterim('Y');

                $this->getRepo('GoodsDisc')->save($newDisc);
            }
        }

        $this->result->addMessage($count . ' Vehicle(s) specified');
        $this->result->addMessage($count . ' Goods Disc(s) created');
        $this->result->addMessage($ceasedCount . ' Goods Disc(s) ceased');
    }

    private function processCommunityLicences(ApplicationEntity $application)
    {
        $ids = [];

        /** @var CommunityLic $commLic */
        foreach ($application->getLicence()->getCommunityLics() as $commLic) {
            if (
                $commLic->getStatus() !== null
                && $commLic->getStatus()->getId() == CommunityLic::STATUS_PENDING
            ) {
                $commLic->setStatus($this->getRepo()->getRefdataReference(CommunityLic::STATUS_ACTIVE));
                $commLic->setSpecifiedDate(new DateTime());

                $this->getRepo('CommunityLic')->save($commLic);

                $ids[] = $commLic->getId();
            }
        }
        if ($ids) {
            $this->result->addMessage(count($ids) . ' Community licence(s) activated');

            $data = [
                'isBatchReprint' => false,
                'communityLicenceIds' => $ids,
                'licence' => $application->getLicence()->getId(),
                'identifier' => $application->getId()
            ];

            $this->result->merge($this->handleSideEffect(GenerateBatch::create($data)));
        }
    }
}
