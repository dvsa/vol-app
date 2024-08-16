<?php

namespace Dvsa\Olcs\Api\Entity\Publication;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddDays;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddWorkingDays;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as ImpoundingEntity;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * PublicationLink Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="publication_link",
 *    indexes={
 *        @ORM\Index(name="ix_publication_link_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_publication_link_publication_id", columns={"publication_id"}),
 *        @ORM\Index(name="ix_publication_link_pi_id", columns={"pi_id"}),
 *        @ORM\Index(name="ix_publication_link_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_publication_link_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_publication_link_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_publication_link_publication_section_id", columns={"publication_section_id"}),
 *        @ORM\Index(name="ix_publication_link_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_publication_link_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_publication_link_transport_manager1_idx", columns={"transport_manager_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_publication_link_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class PublicationLink extends AbstractPublicationLink
{
    public const ADD_ENTRY_ERROR = 'Can only create publication entries for publications with status new';
    public const EDIT_ENTRY_ERROR = 'Only publications with status of New may be edited';

    /**
     * Creates Application publication entry
     *
     *
     * @return void
     * @throws ForbiddenException
     */
    public function createApplication(
        ApplicationEntity $application,
        LicenceEntity $licence,
        PublicationEntity $publication,
        PublicationSectionEntity $publicationSection,
        TrafficAreaEntity $trafficArea
    ) {
        if (!$publication->canGenerate()) {
            throw new ForbiddenException(self::ADD_ENTRY_ERROR);
        }

        $this->application = $application;
        $this->licence = $licence;
        $this->publication = $publication;
        $this->publicationSection = $publicationSection;
        $this->trafficArea = $trafficArea;
    }

    /**
     * Creates a Bus Registration publication entry
     *
     * @param string                   $text1
     *
     * @return void
     * @throws ForbiddenException
     */
    public function createBusReg(
        BusRegEntity $busReg,
        LicenceEntity $licence,
        PublicationEntity $publication,
        PublicationSectionEntity $publicationSection,
        TrafficAreaEntity $trafficArea,
        $text1
    ) {
        if (!$publication->canGenerate()) {
            throw new ForbiddenException(self::ADD_ENTRY_ERROR);
        }

        $this->busReg = $busReg;
        $this->licence = $licence;
        $this->publication = $publication;
        $this->publicationSection = $publicationSection;
        $this->trafficArea = $trafficArea;
        $this->text1 = $text1;
    }

    /**
     * Creates a Pi hearing publication entry
     *
     *
     * @return void
     * @throws ForbiddenException
     */
    public function createPiHearing(
        LicenceEntity $licence,
        PiEntity $pi,
        PublicationEntity $publication,
        PublicationSectionEntity $publicationSection,
        TrafficAreaEntity $trafficArea
    ) {
        if (!$publication->canGenerate()) {
            throw new ForbiddenException(self::ADD_ENTRY_ERROR);
        }

        $this->licence = $licence;
        $this->pi = $pi;
        $this->publication = $publication;
        $this->publicationSection = $publicationSection;
        $this->trafficArea = $trafficArea;
    }

    /**
     * Creates a Tm Pi Hearing publication entry
     *
     *
     * @return void
     * @throws ForbiddenException
     */
    public function createTmPiHearing(
        TransportManagerEntity $transportManager,
        PiEntity $pi,
        PublicationEntity $publication,
        PublicationSectionEntity $publicationSection,
        TrafficAreaEntity $trafficArea
    ) {
        if (!$publication->canGenerate()) {
            throw new ForbiddenException(self::ADD_ENTRY_ERROR);
        }

        $this->transportManager = $transportManager;
        $this->pi = $pi;
        $this->publication = $publication;
        $this->publicationSection = $publicationSection;
        $this->trafficArea = $trafficArea;
    }

    /**
     * Create a publication link for a licence
     *
     *
     * @return void
     * @throws ForbiddenException
     */
    public function createLicence(
        LicenceEntity $licence,
        PublicationEntity $publication,
        PublicationSectionEntity $publicationSection,
        TrafficAreaEntity $trafficArea
    ) {
        if (!$publication->canGenerate()) {
            throw new ForbiddenException(self::ADD_ENTRY_ERROR);
        }

        $this->licence = $licence;
        $this->publication = $publication;
        $this->publicationSection = $publicationSection;
        $this->trafficArea = $trafficArea;
    }

    /**
     * Create a publication link for an Impounding
     *
     *
     * @return void
     * @throws ForbiddenException
     */
    public function createImpounding(
        ImpoundingEntity $impounding,
        PublicationEntity $publication,
        PublicationSectionEntity $publicationSection,
        TrafficAreaEntity $trafficArea,
        LicenceEntity $licence = null,
        ApplicationEntity $application = null
    ) {
        if (!$publication->canGenerate()) {
            throw new ForbiddenException(self::ADD_ENTRY_ERROR);
        }

        $this->impounding = $impounding;
        $this->publication = $publication;
        $this->publicationSection = $publicationSection;
        $this->trafficArea = $trafficArea;
        $this->licence = $licence;
        $this->application = $application;
    }

    /**
     * Updates text fields on the publication link
     *
     * @param string $text1
     * @param string $text2
     * @param string $text3
     *
     * @return void
     * @throws ForbiddenException
     */
    public function updateText($text1, $text2, $text3)
    {
        if (!$this->publication->canGenerate()) {
            throw new ForbiddenException(self::EDIT_ENTRY_ERROR);
        }

        $this->text1 = $text1;
        $this->text2 = $text2;
        $this->text3 = $text3;
    }

    public function maybeSetPublishAfterDate()
    {
        if ($this->getPi() !== null) {
            $dateTimeDaysProcessor = new AddDays();
            $dateTimeWorkingDaysProcessor = new AddWorkingDays($dateTimeDaysProcessor);
            $publishAfterDate = $dateTimeWorkingDaysProcessor->calculateDate(
                new DateTime(),
                PiHearing::PUBLISH_AFTER_DAYS
            );
            $this->setPublishAfterDate($publishAfterDate);
        }
    }
}
