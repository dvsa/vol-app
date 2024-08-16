<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\PublicationGeneratorAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Transfer\Command\Publication\Application as ApplicationCmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedApplication as UnpublishedApplicationQry;

/**
 * Application command handler
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Publication
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Application extends AbstractCommandHandler implements TransactionedInterface, PublicationGeneratorAwareInterface
{
    use PublicationGeneratorAwareTrait;
    use CreatePublicationTrait;

    protected $repoServiceName = 'PublicationLink';

    protected $extraRepos = ['Application', 'Publication', 'TrafficArea'];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var LicenceEntity $licence
         * @var ApplicationEntity $application
         * @var PublicationSectionEntity $publicationSection
         * @var PublicationEntity $publication
         * @var PublicationLinkEntity $publicationLink
         * @var $command ApplicationCmd
         */
        $application = $this->getRepo('Application')->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $licence = $application->getLicence();
        $pubType = $application->getGoodsOrPsv()->getId() === LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE ?
            'A&D' : 'N&P';

        $pubSection = $command->getPublicationSection();
        if (empty($pubSection)) {
            $pubSection = $this->getPublicationSectionId($application);
        }
        $trafficArea = $this->getRepo()->getReference(TrafficAreaEntity::class, $command->getTrafficArea());

        $publication = $this->getPublication($command->getTrafficArea(), $pubType);

        $publicationSection = $this->getPublicationSection($pubSection);

        $unpublishedQuery = $this->getUnpublishedApplicationQuery(
            $publication->getId(),
            $application->getId(),
            $pubSection
        );

        $publicationLink = $this->getPublicationLink($unpublishedQuery);

        if ($publicationLink->getId() === null) {
            $publicationLink->createApplication(
                $application,
                $licence,
                $publication,
                $publicationSection,
                $trafficArea
            );
        }

        $publicationConfig = $this->getPublicationConfig($application, $pubSection);

        return $this->createPublication($publicationConfig, $publicationLink, []);
    }

    /**
     * Get the publication config section to use
     *
     * @param int $publicationSection
     * @return string
     */
    private function getPublicationConfig(ApplicationEntity $application, $publicationSection)
    {
        if ($this->isSchedule41Section($publicationSection)) {
            // if S4 is a trueS4, (uses first S4 as in reality it is only possible to have one S4 per application)
            return ($application->getS4s()->first()->getIsTrueS4() === 'Y') ?
                'Schedule41TruePublication' :
                'Schedule41UntruePublication';
        }

        return $application->isNew() ? 'ApplicationPublication' : 'VariationPublication';
    }

    /**
     * Is the publication section a Schedule41 section
     *
     * @param int $publicationSection
     *
     * @return bool
     */
    private function isSchedule41Section($publicationSection)
    {
        $schedule41Sections = [
            PublicationSectionEntity::SCHEDULE_1_NI_NEW,
            PublicationSectionEntity::SCHEDULE_1_NI_TRUE,
            PublicationSectionEntity::SCHEDULE_1_NI_UNTRUE,
            PublicationSectionEntity::SCHEDULE_4_NEW,
            PublicationSectionEntity::SCHEDULE_4_TRUE,
            PublicationSectionEntity::SCHEDULE_4_UNTRUE,
        ];

        return in_array($publicationSection, $schedule41Sections);
    }

    /**
     * Auto detect which section we should be publishing to
     *
     *
     * @return int publicationSection ID
     * @throws ForbiddenException
     */
    public function getPublicationSectionId(ApplicationEntity $application)
    {
        $newApplicationMap = [
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION => PublicationSectionEntity::APP_NEW_SECTION,
            ApplicationEntity::APPLICATION_STATUS_GRANTED => PublicationSectionEntity::APP_GRANTED_SECTION,
            ApplicationEntity::APPLICATION_STATUS_REFUSED => PublicationSectionEntity::APP_REFUSED_SECTION,
            ApplicationEntity::APPLICATION_STATUS_WITHDRAWN => PublicationSectionEntity::APP_WITHDRAWN_SECTION,
            ApplicationEntity::APPLICATION_STATUS_NOT_TAKEN_UP => PublicationSectionEntity::APP_GRANT_NOT_TAKEN_SECTION,
        ];
        $varApplicationMap = [
            ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION => PublicationSectionEntity::VAR_NEW_SECTION,
            ApplicationEntity::APPLICATION_STATUS_GRANTED => PublicationSectionEntity::VAR_GRANTED_SECTION,
            ApplicationEntity::APPLICATION_STATUS_REFUSED => PublicationSectionEntity::VAR_REFUSED_SECTION,
            ApplicationEntity::APPLICATION_STATUS_WITHDRAWN => PublicationSectionEntity::APP_WITHDRAWN_SECTION,
        ];

        $map = ($application->isNew()) ? $newApplicationMap : $varApplicationMap;

        if (isset($map[$application->getStatus()->getId()])) {
            return $map[$application->getStatus()->getId()];
        }

        throw new \RuntimeException('Could not match to a publication section');
    }

    /**
     * @param int $publication
     * @param int $application
     * @param int $pubSection
     * @return UnpublishedApplicationQry
     */
    public function getUnpublishedApplicationQuery($publication, $application, $pubSection)
    {
        $data =  [
            'publication' => $publication,
            'application' => $application,
            'publicationSection' => $pubSection
        ];

        return UnpublishedApplicationQry::create($data);
    }
}
