<?php

/**
 * Grant Community Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;

/**
 * Grant Community Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantCommunityLicence extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['CommunityLic', 'Licence'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);
        $licence = $application->getLicence();

        if ($licence->canHaveCommunityLicences()) {
            $this->grant($licence, $result);
        } else {
            $count = $this->voidActivePending($licence);
            $this->clearCommunityLicencesCount($licence);
            $result->addMessage('Total community licence(s) count cleared');
            $result->addMessage($count . ' community licence(s) voided');
        }

        return $result;
    }

    protected function grant(Licence $licence, Result $result)
    {
        $criteria = Criteria::create();
        $criteria->andWhere(
            $criteria->expr()->isNull('specifiedDate')
        );
        $criteria->andWhere(
            $criteria->expr()->eq('status', $this->getRepo()->getRefdataReference(CommunityLic::STATUS_PENDING))
        );

        $pendingRecords = $licence->getCommunityLics()->matching($criteria);

        $identifiers = [];
        /** @var CommunityLic $pendingRecord */
        foreach ($pendingRecords as $pendingRecord) {
            $identifiers[] = $pendingRecord->getId();
            $pendingRecord->setStatus($this->getRepo()->getRefdataReference(CommunityLic::STATUS_ACTIVE));
            $pendingRecord->setSpecifiedDate(new DateTime());
            $this->getRepo('CommunityLic')->save($pendingRecord);
        }

        $result->addMessage(count($identifiers) . ' community licence(s) activated');

        $data = [
            'isBatchReprint' => false,
            'licence' => $licence->getId(),
            'communityLicenceIds' => $identifiers
        ];

        $result->merge($this->handleSideEffect(GenerateBatch::create($data)));
    }

    protected function voidActivePending(Licence $licence)
    {
        $activePendingLicences = $licence->getCommunityLics()->filter(
            fn($element) => in_array(
                $element->getStatus(),
                [
                    CommunityLic::STATUS_ACTIVE,
                    CommunityLic::STATUS_PENDING,
                ]
            )
        );

        /** @var CommunityLic $activePendingLicence */
        foreach ($activePendingLicences as $activePendingLicence) {
            $activePendingLicence->setStatus($this->getRepo()->getRefdataReference(CommunityLic::STATUS_RETURNDED));
            $activePendingLicence->setExpiredDate(new DateTime());
            $this->getRepo('CommunityLic')->save($activePendingLicence);
        }

        return $activePendingLicences->count();
    }

    protected function clearCommunityLicencesCount($licence)
    {
        $licence->setTotCommunityLicences(0);
        $this->getRepo('Licence')->save($licence);
    }
}
