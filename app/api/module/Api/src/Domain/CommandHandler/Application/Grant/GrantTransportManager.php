<?php

/**
 * Grant Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\Licence\TmNominatedTask;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\EntityCloner;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;

/**
 * Grant Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantTransportManager extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['TransportManagerLicence', 'OtherLicence'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);
        $licence = $application->getLicence();

        if ($licence->isRestricted()) {
            $this->deleteAllTransportManagersForLicence($licence);
            $result->addMessage('All transport managers removed');
            return $result;
        }

        $tmas = $application->getTransportManagers();
        $isDeleting = false;

        /** @var TransportManagerApplication $tma */
        foreach ($tmas as $tma) {
            switch ($tma->getAction()) {
                case 'A':
                case 'U':
                    $this->createTransportManager($tma, $licence);
                    break;
                case 'D':
                    $isDeleting = true;
                    $this->deleteTransportManager($tma->getTransportManager(), $licence);
                    break;
            }
        }

        if ($application->isVariation() && $isDeleting) {
            $result->merge($this->handleSideEffect(TmNominatedTask::create(['ids' => [$licence->getId()]])));
        }

        $result->addMessage('Transport managers copied to licence');

        return $result;
    }

    protected function createTransportManager(TransportManagerApplication $tma, Licence $licence)
    {
        $dtoData = [
            'entityId' => $tma->getId(),
            'type' => Queue::TYPE_TM_SNAPSHOT,
            'status' => Queue::STATUS_QUEUED
        ];

        $this->handleSideEffect(Create::create($dtoData));

        if ($this->licenceHasTransportManager($tma->getTransportManager(), $licence)) {
            $this->deleteTransportManager($tma->getTransportManager(), $licence);
        }

        $tml = new TransportManagerLicence($licence, $tma->getTransportManager());

        $ignore = [
            'action',
            'application',
            'tmApplicationStatus',
            'otherLicences'
        ];

        EntityCloner::cloneEntityInto($tma, $tml, $ignore);

        $this->getRepo('TransportManagerLicence')->save($tml);

        $otherLicences = $tma->getOtherLicences();

        foreach ($otherLicences as $otherLicence) {
            $this->createOtherLicence($otherLicence, $tml);
        }
    }

    protected function createOtherLicence(OtherLicence $otherLicence, TransportManagerLicence $tml)
    {
        $newOtherLicence = EntityCloner::cloneEntity($otherLicence, ['transportManagerApplication']);
        $newOtherLicence->setTransportManagerLicence($tml);

        $this->getRepo('OtherLicence')->save($newOtherLicence);
    }

    protected function licenceHasTransportManager(TransportManager $transportManager, Licence $licence)
    {
        $matching = $this->getCorrespondingRecord($transportManager, $licence);

        return $matching->count() > 0;
    }

    protected function getCorrespondingRecord(TransportManager $transportManager, Licence $licence)
    {
        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->eq('transportManager', $transportManager));

        return $licence->getTmLicences()->matching($criteria);
    }

    protected function deleteTransportManager(TransportManager $transportManager, Licence $licence)
    {
        $this->deleteTmlList($this->getCorrespondingRecord($transportManager, $licence));
    }

    protected function deleteAllTransportManagersForLicence(Licence $licence)
    {
        $this->deleteTmlList($licence->getTmLicences());
    }

    protected function deleteTmlList($list)
    {
        foreach ($list as $item) {
            $this->getRepo('TransportManagerLicence')->delete($item);
        }
    }
}
