<?php

/**
 * Create Impounding
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Impounding;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as ImpoundingEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\CreateImpounding as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Publication\Impounding as PublishImpoundingCmd;

/**
 * Abstract Impounding class
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
abstract class AbstractImpounding extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Impounding';

    /**
     * Returns collection of legislation types.
     *
     * @param null $impoundingLegislationTypes
     * @return ArrayCollection
     */
    protected function generateImpoundingLegislationTypes($impoundingLegislationTypes = null)
    {
        $result = new ArrayCollection();
        if (!empty($impoundingLegislationTypes)) {
            foreach ($impoundingLegislationTypes as $legislationType) {
                $result->add($this->getRepo()->getRefdataReference($legislationType));
            }
        }
        return $result;
    }

    /**
     * @return PublishImpoundingCmd
     */
    protected function createPublishCommand(ImpoundingEntity $impounding)
    {
        /** @var CasesEntity $case */
        $case = $impounding->getCase();

        $caseType = $case->getCaseType()->getId();

        $commandData =  [
            'id' => $impounding->getId(),
            'trafficArea' => $this->determinePubTrafficArea($case)->getId(),
            'pi' => $this->determinePublicInquiry($case)
        ];

        if ($caseType === CasesEntity::APP_CASE_TYPE) {
            /** @var ApplicationEntity $application */
            $application = $case->getApplication();

            $licType = $application->getGoodsOrPsv()->getId();

            return PublishImpoundingCmd::create(
                array_merge(
                    $commandData,
                    [
                        'pubType' => $this->determinePubType($licType),
                        'application' => $application->getId()
                    ]
                )
            );
        } else {
            /** @var LicenceEntity $licence */
            $licence = $case->getLicence();

            $licType = $licence->getGoodsOrPsv()->getId();

            return PublishImpoundingCmd::create(
                array_merge(
                    $commandData,
                    [
                        'pubType' => $this->determinePubType($licType),
                        'licence' => $licence->getId()
                    ]
                )
            );
        }
    }

    /**
     * Determine the publication type, IF Goods => 'A&D' else 'N&P'
     *
     * @param string $licType
     * @return string
     */
    protected function determinePubType($licType)
    {
        return ($licType == LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE) ? 'A&D' : 'N&P';
    }

    /**
     * Determine the publication traffic area, derived from the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     */
    protected function determinePubTrafficArea(CasesEntity $case)
    {
        if ($case->getCaseType()->getId() === CasesEntity::APP_CASE_TYPE) {
            return $case->getApplication()->getLicence()->getTrafficArea();
        } else {
            return $case->getLicence()->getTrafficArea();
        }
    }

    /**
     * Return Public Inquiry Id
     * @return mixed
     */
    protected function determinePublicInquiry(CasesEntity $case)
    {
        if (!empty($case->getPublicInquiry())) {
            return $case->getPublicInquiry()->getId();
        }

        return null;
    }
}
