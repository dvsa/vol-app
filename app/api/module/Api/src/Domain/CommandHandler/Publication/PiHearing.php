<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Publication\PiDecision as PiDecisionCmd;
use Dvsa\Olcs\Api\Domain\Command\Publication\PiHearing as PiHearingCmd;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as PiHearingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedPi as UnpublishedPiQry;

/**
 * Pi Hearing command handler
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Publication
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PiHearing extends AbstractCommandHandler implements TransactionedInterface, PublicationGeneratorAwareInterface
{
    use PublicationGeneratorAwareTrait;
    use CreatePublicationTrait;

    protected $repoServiceName = 'PublicationLink';

    protected $extraRepos = ['PiHearing', 'Publication', 'TrafficArea'];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var PiHearingEntity $hearing
         * @var PiEntity $pi
         * @var CasesEntity $case
         * @var LicenceEntity $licence
         * @var $command PiHearingCmd|PiDecisionCmd
         */
        $hearing = $this->getRepo('PiHearing')->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $pi = $hearing->getPi();
        $case = $pi->getCase();

        if ($case->isTm()) {
            return $this->handleTmHearing($command, $hearing, $pi, $case);
        }

        return $this->handleHearing($hearing, $pi, $case, $command);
    }

    public function handleTmHearing(
        CommandInterface $command,
        PiHearingEntity $hearing,
        PiEntity $pi,
        CasesEntity $case
    ) {
        $result = new Result();

        /**
         * @var TransportManagerEntity $transportManager
         * @var PublicationSectionEntity $publicationSection
         * @var TrafficAreaEntity $trafficArea
         * @var PiHearingCmd|PiDecisionCmd $command
         */
        if ($command instanceof PiHearingCmd) {
            $pubSection = PublicationSectionEntity::TM_HEARING_SECTION;
            $handler = 'TmHearingPublication';
        } else {
            $pubSection = PublicationSectionEntity::TM_DECISION_SECTION;
            $handler = 'TmHearingDecision';
        }

        $transportManager = $case->getTransportManager();
        $publicationSection = $this->getPublicationSection($pubSection);
        $trafficAreas = $command->getTrafficAreas();
        $pubTypes = $command->getPubType();

        $allTrafficAreas = $this->getRepo('TrafficArea')->fetchAll();
        $allPubTypes = ['A&D', 'N&P'];

        //default Northern Ireland N&P to already published (as it doesn't exist)
        $publishedAreas = [
            TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE => [
                'N&P' => true
            ]
        ];

        if (in_array('all', $trafficAreas)) {
            $trafficAreas = $allTrafficAreas;
        }

        if (in_array('All', $pubTypes)) {
            $pubTypes = $allPubTypes;
        }

        //process the traffic areas where we're adding or amending the publication
        foreach ($trafficAreas as $ta) {
            foreach ($pubTypes as $pubType) {
                if ($ta instanceof TrafficAreaEntity) {
                    $trafficArea = $this->getRepo()->getReference(TrafficAreaEntity::class, $ta->getId());
                } else {
                    $trafficArea = $this->getRepo()->getReference(TrafficAreaEntity::class, $ta);
                }

                //no N&P for Northern Ireland, this is already defaulted to published, no need to record it twice below
                if (
                    $trafficArea->getId() === TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE
                    && $pubType == 'N&P'
                ) {
                    continue;
                }

                //record that we've dealt with this traffic area and pub type combination
                $publishedAreas[$trafficArea->getId()][$pubType] = true;

                /**
                 * @var UnpublishedPiQry $unpublishedQuery
                 * @var PublicationEntity $publication
                 * @var PublicationLinkEntity $publicationLink
                 */
                $publication = $this->getPublication($trafficArea->getId(), $pubType);
                $unpublishedQuery = $this->getUnpublishedPiQuery($publication->getId(), $pi->getId(), $pubSection);
                $publicationLink = $this->getPublicationLink($unpublishedQuery);

                if ($publicationLink->getId() === null) {
                    $publicationLink->createTmPiHearing(
                        $transportManager,
                        $pi,
                        $publication,
                        $publicationSection,
                        $trafficArea
                    );
                }

                $publicationLink->setText2($command->getText2());
                $publicationLink->maybeSetPublishAfterDate();
                $result->merge(
                    $this->createPublication(
                        $handler,
                        $publicationLink,
                        $this->extractHearingData($hearing)
                    )
                );
            }
        }

        //if we haven't published to a traffic area, check whether there's an existing publication we need to delete
        foreach ($allTrafficAreas as $trafficArea) {
            foreach ($allPubTypes as $pubType) {
                if (isset($publishedAreas[$trafficArea->getId()][$pubType])) {
                    continue;
                }

                //check for a previous publication
                $publication = $this->getPublication($trafficArea->getId(), $pubType);
                $unpublishedQuery = $this->getUnpublishedPiQuery($publication->getId(), $pi->getId(), $pubSection);
                $publicationLink = $this->getPublicationLink($unpublishedQuery);

                //if previous publication is found, remove it
                if ($publicationLink->getId() !== null) {
                    $publicationLink->getPoliceDatas()->clear();
                    $this->getRepo()->delete($publicationLink);
                }
            }
        }

        return $result;
    }

    /**
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleHearing(PiHearingEntity $hearing, PiEntity $pi, CasesEntity $case, CommandInterface $command)
    {
        /**
         * @var LicenceEntity $licence
         * @var PiHearingCmd|PiDecisionCmd $command
         */
        if ($command instanceof PiHearingCmd) {
            $pubSection = PublicationSectionEntity::HEARING_SECTION;
            $handler = 'HearingPublication';
        } else {
            $pubSection = PublicationSectionEntity::DECISION_SECTION;
            $handler = 'HearingDecision';
        }

        $caseType = $case->getCaseType()->getId();
        $licence = $case->getLicence();

        if ($caseType === CasesEntity::APP_CASE_TYPE) {
            $licType = $case->getApplication()->getGoodsOrPsv()->getId();
        } else {
            $licType = $licence->getGoodsOrPsv()->getId();
        }

        $pubType = ($licType == LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE ? 'A&D' : 'N&P');
        $trafficArea = $licence->getTrafficArea();
        $publicationSection = $this->getPublicationSection($pubSection);

        /**
         * @var UnpublishedPiQry $unpublishedQuery
         * @var PublicationEntity $publication
         * @var PublicationLinkEntity $publicationLink
         */
        $publication = $this->getPublication($trafficArea->getId(), $pubType);
        $unpublishedQuery = $this->getUnpublishedPiQuery($publication->getId(), $pi->getId(), $pubSection);
        $publicationLink = $this->getPublicationLink($unpublishedQuery);

        if ($publicationLink->getId() === null) {
            $publicationLink->createPiHearing($licence, $pi, $publication, $publicationSection, $trafficArea);
        }

        $publicationLink->setText2($command->getText2());
        $publicationLink->maybeSetPublishAfterDate();
        return $this->createPublication($handler, $publicationLink, $this->extractHearingData($hearing));
    }

    /**
     * @param int $publication
     * @param int $pi
     * @param int $pubSection
     * @return UnpublishedPiQry
     */
    private function getUnpublishedPiQuery($publication, $pi, $pubSection)
    {
        $data =  [
            'publication' => $publication,
            'pi' => $pi,
            'publicationSection' => $pubSection
        ];

        return UnpublishedPiQry::create($data);
    }

    /**
     * @param PiHearingEntity $hearing
     * @return array
     */
    private function extractHearingData($hearing)
    {
        $venue = $hearing->getVenue();
        $hearingDate = $hearing->getHearingDate();

        //sometimes we have a datetime, and sometimes a string
        if ($hearingDate instanceof \DateTime) {
            $hearingDate = $hearingDate->format('Y-m-d H:i:s');
        }

        return [
            'venue' => ($venue === null ? $venue : $venue->getId()),
            'venueOther' => $hearing->getVenueOther(),
            'hearingDate' => $hearingDate,
            'id' => $hearing->getId()
        ];
    }
}
