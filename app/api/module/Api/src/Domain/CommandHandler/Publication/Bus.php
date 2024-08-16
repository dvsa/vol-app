<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\Publication\Bus as BusCmd;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedBusReg as UnpublishedBusRegQry;

/**
 * Bus command handler
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Publication
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Bus extends AbstractCommandHandler implements TransactionedInterface, PublicationGeneratorAwareInterface
{
    use PublicationGeneratorAwareTrait;
    use CreatePublicationTrait;

    protected $repoServiceName = 'PublicationLink';

    protected $extraRepos = ['Bus', 'Publication', 'TrafficArea'];

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /**
         * @var BusRegEntity $busReg
         * @var CasesEntity $case
         * @var LicenceEntity $licence
         * @var $command BusCmd
         */
        $busReg = $this->getRepo('Bus')->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $licence = $busReg->getLicence();
        $revertStatus = $busReg->getRevertStatus()->getId();
        $trafficAreas = $busReg->getTrafficAreas();
        $shortNotice = $busReg->getIsShortNotice();

        //Areas we've published, NI doesn't have N&P so default this from the start
        $publishedAreas = [TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE => true];

        switch ($revertStatus) {
            case BusRegEntity::STATUS_NEW:
                $handler = 'BusGrantNew';
                $pubSection = ($shortNotice === 'Y'
                    ? PublicationSectionEntity::BUS_NEW_SHORT_SECTION
                    : PublicationSectionEntity::BUS_NEW_SECTION
                );
                break;
            case BusRegEntity::STATUS_VAR:
                $handler = 'BusGrantVariation';
                $pubSection = ($shortNotice === 'Y'
                    ? PublicationSectionEntity::BUS_VAR_SHORT_SECTION
                    : PublicationSectionEntity::BUS_VAR_SECTION
                );
                break;
            case BusRegEntity::STATUS_CANCEL:
                $handler = 'BusGrantCancel';
                $pubSection = ($shortNotice === 'Y'
                    ? PublicationSectionEntity::BUS_CANCEL_SHORT_SECTION
                    : PublicationSectionEntity::BUS_CANCEL_SECTION
                );
                break;
            default:
                throw new ForbiddenException('This status can\'t be published');
        }

        foreach ($trafficAreas as $ta) {
            /**
             * @var TrafficAreaEntity $ta
             * @var UnpublishedBusRegQry $unpublishedQuery
             * @var PublicationEntity $publication
             * @var PublicationLinkEntity $publicationLink
             */
            //shouldn't be attempting for NI (already defaulted as a published area so we don't need to set again)
            if ($ta->getId() === TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE) {
                continue;
            }

            //record that we've dealt with this traffic area
            $publishedAreas[$ta->getId()] = true;

            $publication = $this->getPublication($ta->getId(), 'N&P');
            $publicationSection = $this->getPublicationSection($pubSection);
            $unpublishedQuery = $this->getUnpublishedBusRegQuery($publication->getId(), $busReg->getId(), $pubSection);
            $publicationLink = $this->getPublicationLink($unpublishedQuery);

            if ($publicationLink->getId() === null) {
                $publicationLink->createBusReg(
                    $busReg,
                    $licence,
                    $publication,
                    $publicationSection,
                    $ta,
                    $busReg->getRegNo()
                );
            }

            $result->merge($this->createPublication($handler, $publicationLink, []));
        }

        $allTrafficAreas = $this->getRepo('TrafficArea')->fetchAll();

        //if we haven't published to a traffic area, check whether there's an existing publication we need to delete
        //note this also checks for northern ireland as it's set as a published area by default, so no need to check
        //twice
        foreach ($allTrafficAreas as $ta) {
            if (isset($publishedAreas[$ta->getId()])) {
                continue;
            }

            //check for a previous publication
            $publication = $this->getPublication($ta->getId(), 'N&P');
            $unpublishedQuery = $this->getUnpublishedBusRegQuery($publication->getId(), $busReg->getId(), $pubSection);
            $publicationLink = $this->getPublicationLink($unpublishedQuery);

            //if previous publication is found, remove it
            if ($publicationLink->getId() !== null) {
                $publicationLink->getPoliceDatas()->clear();
                $this->getRepo()->delete($publicationLink);
            }
        }

        return $result;
    }

    /**
     * @param $publication
     * @param $busReg
     * @param $pubSection
     * @return UnpublishedBusRegQry
     */
    private function getUnpublishedBusRegQuery($publication, $busReg, $pubSection)
    {
        $data =  [
            'publication' => $publication,
            'busReg' => $busReg,
            'publicationSection' => $pubSection
        ];

        return UnpublishedBusRegQry::create($data);
    }
}
