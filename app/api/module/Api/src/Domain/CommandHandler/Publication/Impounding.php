<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Publication\Impounding as ImpoundingPublicationCmd;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as ImpoundingEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedImpounding as UnpublishedImpoundingQry;

/**
 * Impounding publication command handler
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Publication
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Impounding extends AbstractCommandHandler implements TransactionedInterface, PublicationGeneratorAwareInterface
{
    use PublicationGeneratorAwareTrait;
    use CreatePublicationTrait;

    protected $repoServiceName = 'PublicationLink';

    protected $extraRepos = ['Impounding', 'Publication', 'TrafficArea', 'Licence', 'Application'];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var ImpoundingEntity $impounding
         * @var CasesEntity $case
         * @var $command ImpoundingPublicationCmd
         */
        $impounding = $this->getRepo('Impounding')->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $case = $impounding->getCase();

        return $this->handleImpoundingPublication($command, $impounding, $case);
    }

    private function handleImpoundingPublication(
        CommandInterface $command,
        ImpoundingEntity $impounding,
        CasesEntity $case
    ) {
        $result = new Result();

        /**
         * @var PublicationSectionEntity $publicationSection
         * @var TrafficAreaEntity $trafficArea
         * @var ImpoundingPublicationCmd $command
         */
        $pubSection = PublicationSectionEntity::HEARING_SECTION;

        if ($case->getCaseType() == CasesEntity::APP_CASE_TYPE) {
            $handler = 'ImpoundingApplicationPublication';
            $application = $this->getRepo()->getReference(ApplicationEntity::class, $command->getApplication());
            $licence = $application->getLicence();
        } else {
            $handler = 'ImpoundingLicencePublication';
            $licence = $this->getRepo()->getReference(LicenceEntity::class, $command->getLicence());
            $application = null;
        }

        $publicationSection = $this->getPublicationSection(PublicationSectionEntity::HEARING_SECTION);
        $trafficArea = $this->getRepo()->getReference(TrafficAreaEntity::class, $command->getTrafficArea());

        $pubType = $command->getPubType();

        //default Northern Ireland N&P to already published (as it doesn't exist)
        $publishedAreas = [
            TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE => [
                'N&P' => true
            ]
        ];

        //process the traffic areas where we're adding or amending the publication

        //no N&P for Northern Ireland, this is already defaulted to published, no need to record it twice below
        if (
            ($trafficArea->getId() !== TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE) &&
            ($pubType !== 'N&P')
        ) {
            //record that we've dealt with this traffic area and pub type combination
            $publishedAreas[$trafficArea->getId()][$pubType] = true;
            /**
             * @var UnpublishedImpoundingQry $unpublishedQuery
             * @var PublicationEntity $publication
             * @var PublicationLinkEntity $publicationLink
             */
            $publication = $this->getPublication($trafficArea->getId(), $pubType);

            $unpublishedQuery = $this->getUnpublishedImpoundingQuery(
                $publication->getId(),
                $impounding->getId(),
                $pubSection
            );

            $publicationLink = $this->getPublicationLink($unpublishedQuery);

            if ($publicationLink->getId() === null) {
                $publicationLink->createImpounding(
                    $impounding,
                    $publication,
                    $publicationSection,
                    $trafficArea,
                    $licence,
                    $application
                );
            }

            $result = $this->createPublication(
                $handler,
                $publicationLink,
                $this->extractImpoundingData($impounding)
            );
        }

        $allTrafficAreas = $this->getRepo('TrafficArea')->fetchAll();
        $allPubTypes = ['A&D', 'N&P'];
        //if we haven't published to a traffic area, check whether there's an existing publication we need to delete
        foreach ($allTrafficAreas as $trafficArea) {
            foreach ($allPubTypes as $pubType) {
                if (isset($publishedAreas[$trafficArea->getId()][$pubType])) {
                    continue;
                }

                //check for a previous publication
                $publication = $this->getPublication($trafficArea->getId(), $pubType);
                $unpublishedQuery = $this->getUnpublishedImpoundingQuery(
                    $publication->getId(),
                    $impounding->getId(),
                    $pubSection
                );
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
     * @param int $publication
     * @param int $impoundingId
     * @param int $pubSection
     * @return UnpublishedImpoundingQry
     */
    private function getUnpublishedImpoundingQuery($publication, $impoundingId, $pubSection)
    {
        $data =  [
            'publication' => $publication,
            'impounding' => $impoundingId,
            'publicationSection' => $pubSection
        ];

        return UnpublishedImpoundingQry::create($data);
    }

    /**
     * @param ImpoundingEntity $impounding
     * @return array
     */
    private function extractImpoundingData($impounding)
    {
        $venue = $impounding->getVenue();

        $hearingDate = $impounding->getHearingDate();

        //sometimes we have a datetime, and sometimes a string
        if ($hearingDate instanceof \DateTime) {
            $hearingDate = $hearingDate->format('Y-m-d H:i:s');
        }

        return [
            'venue' => ($venue === null ? $venue : $venue->getId()),
            'venueOther' => $impounding->getVenueOther(),
            'hearingDate' => $hearingDate,
            'id' => $impounding->getId()
        ];
    }
}
