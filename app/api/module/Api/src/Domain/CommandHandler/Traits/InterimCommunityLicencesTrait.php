<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;

trait InterimCommunityLicencesTrait
{
    private function processCommunityLicences(Application $application): void
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
