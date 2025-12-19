<?php

namespace Dvsa\Olcs\Api\Entity\Tm;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * TransportManager Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="transport_manager",
 *    indexes={
 *        @ORM\Index(name="ix_transport_manager_tm_status", columns={"tm_status"}),
 *        @ORM\Index(name="ix_transport_manager_tm_type", columns={"tm_type"}),
 *        @ORM\Index(name="ix_transport_manager_home_cd_id", columns={"home_cd_id"}),
 *        @ORM\Index(name="ix_transport_manager_merge_to_transport_manager_id",
 *          columns={"merge_to_transport_manager_id"}),
 *        @ORM\Index(name="ix_transport_manager_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_transport_manager_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_transport_manager_work_cd_id", columns={"work_cd_id"})
 *    }
 * )
 */
class TransportManager extends AbstractTransportManager implements
    ContextProviderInterface,
    OrganisationProviderInterface
{
    public const TRANSPORT_MANAGER_STATUS_CURRENT = 'tm_s_cur';
    public const TRANSPORT_MANAGER_STATUS_DISQUALIFIED = 'tm_s_dis';
    public const TRANSPORT_MANAGER_STATUS_REMOVED = 'tm_s_rem';
    public const TRANSPORT_MANAGER_TYPE_EXTERNAL = 'tm_t_e';
    public const TRANSPORT_MANAGER_TYPE_BOTH = 'tm_t_b';
    public const TRANSPORT_MANAGER_TYPE_INTERNAL = 'tm_t_i';

    /**
     * Update type, status and home/work contact details of the TM
     *
     * @param $type
     * @param $status
     * @param ContactDetails|null $workCd
     * @param ContactDetails|null $homeCd
     */
    public function updateTransportManager(
        $type,
        $status,
        $workCd = null,
        $homeCd = null
    ) {
        $this->setTmType($type);
        $this->setTmStatus($status);
        if ($workCd !== null) {
            $this->setWorkCd($workCd);
        }
        if ($homeCd !== null) {
            $this->setHomeCd($homeCd);
        }
    }

    /**
     * Update the NYSIIS name fields
     *
     * @param string|null $nysiisForename
     * @param string|null $nysiisFamilyname
     */
    public function updateNysiis(
        $nysiisForename = null,
        $nysiisFamilyname = null
    ) {
        if (!empty($nysiisForename)) {
            $this->setNysiisForename($nysiisForename);
        }
        if (!empty($nysiisFamilyname)) {
            $this->setNysiisFamilyname($nysiisFamilyname);
        }
    }

    /**
     * Get a list of Organisations that this TM is associated with
     *
     * @return array
     */
    public function getAssociatedOrganisations()
    {
        $organisations = [];

        /* @var $tma TransportManagerApplication */
        foreach ($this->getTmApplicationsValid() as $tma) {
            $organisation = $tma->getApplication()->getLicence()->getOrganisation();
            $organisations[$organisation->getId()] = $organisation;
        }

        /* @var $tml TransportManagerLicence */
        foreach ($this->getTmLicencesValid() as $tml) {
            $organisation = $tml->getLicence()->getOrganisation();
            $organisations[$organisation->getId()] = $organisation;
        }

        return $organisations;
    }

    /**
     * Get the total auth vehicles that this TM is linked to
     *
     * @return int
     */
    public function getTotAuthVehicles()
    {
        $total = 0;
        /* @var $tma TransportManagerApplication */
        foreach ($this->getTmApplicationsValid() as $tma) {
            $total += $tma->getApplication()->getTotAuthVehicles();
        }

        /* @var $tml TransportManagerLicence */
        foreach ($this->getTmLicencesValid() as $tml) {
            $total += $tml->getLicence()->getTotAuthVehicles();
        }

        return $total;
    }

    /**
     * Get a list of TM applications that are valid for the associated org and tot auth vehicle counts
     *
     * @return array
     */
    protected function getTmApplicationsValid($countVariations = false)
    {
        $applicationStatuses = [
            \Dvsa\Olcs\Api\Entity\Application\Application::APPLICATION_STATUS_GRANTED,
            \Dvsa\Olcs\Api\Entity\Application\Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
        ];

        $validTmas = [];
        /* @var $tma TransportManagerApplication */
        foreach ($this->getTmApplications() as $tma) {
            // only new apps are counted
            if (!$countVariations && $tma->getApplication()->getIsVariation()) {
                continue;
            }

            // must be granted or under consideration
            if (in_array($tma->getApplication()->getStatus()->getId(), $applicationStatuses)) {
                $validTmas[] = $tma;
            }
        }

        return $validTmas;
    }

    /**
     * Get a list of TM licences that are valid for the associated org and tot auth vehicle counts
     *
     * @return array
     */
    protected function getTmLicencesValid()
    {
        $licenceStatuses = [
            \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_VALID,
            \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_CURTAILED,
            \Dvsa\Olcs\Api\Entity\Licence\Licence::LICENCE_STATUS_SUSPENDED,
        ];

        $validTmls = [];
        /* @var $tml TransportManagerLicence */
        foreach ($this->getTmLicences() as $tml) {
            // must be valid, curtailed or suspended
            if (in_array($tml->getLicence()->getStatus()->getId(), $licenceStatuses)) {
                $validTmls[] = $tml;
            }
        }

        return $validTmls;
    }

    /**
     * Does this Transport Manager has a valid qualification for Standard International licence in Nortern Ireland
     *
     * @return boolean
     */
    public function hasValidSiNiQualification()
    {
        $validQuals = [
            TmQualification::QUALIFICATION_TYPE_NIAR,
            TmQualification::QUALIFICATION_TYPE_NICPCSI,
            TmQualification::QUALIFICATION_TYPE_NIEXSI,
        ];

        /* @var $qualification TmQualification */
        foreach ($this->getQualifications() as $qualification) {
            if (in_array($qualification->getQualificationType()->getId(), $validQuals)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does this Transport Manager has a valid qualification for Standard International licence in Great Britain
     *
     * @return boolean
     */
    public function hasValidSiGbQualification()
    {
        $validQuals = [
            TmQualification::QUALIFICATION_TYPE_AR,
            TmQualification::QUALIFICATION_TYPE_CPCSI,
            TmQualification::QUALIFICATION_TYPE_EXSI,
        ];

        /* @var $qualification TmQualification */
        foreach ($this->getQualifications() as $qualification) {
            if (in_array($qualification->getQualificationType()->getId(), $validQuals)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get LGV Acquired Rights qualification, if this Transport Manager has one
     *
     * @return TmQualification|null
     */
    public function getLgvAcquiredRightsQualification(): ?TmQualification
    {
        $validQuals = [
            TmQualification::QUALIFICATION_TYPE_LGVAR,
            TmQualification::QUALIFICATION_TYPE_NILGVAR,
        ];

        /* @var $qualification TmQualification */
        foreach ($this->getQualifications() as $qualification) {
            if (in_array($qualification->getQualificationType()->getId(), $validQuals)) {
                return $qualification;
            }
        }

        return null;
    }

    /**
     * Has this Transport Manager got LGV Acquired Rights qualification
     *
     * @return boolean
     */
    public function hasLgvAcquiredRightsQualification(): bool
    {
        return $this->getLgvAcquiredRightsQualification() !== null;
    }

    /**
     * Is an SI Qualification required for this TM
     *
     * @param string $niFlag 'N' = GB, 'Y' = NI
     *
     * @return boolean
     */
    public function isSiQualificationRequired($niFlag)
    {
        /* @var $tma TransportManagerApplication */
        foreach ($this->getTmApplicationsValid(true) as $tma) {
            // check niFlag
            if ($tma->getApplication()->getNiFlag() != $niFlag) {
                continue;
            }
            // only SI
            if ($tma->getApplication()->getLicenceType()->getId() !== Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
                continue;
            }
            return true;
        }

        /* @var $tml TransportManagerLicence */
        foreach ($this->getTmLicencesValid() as $tml) {
            // check niFlag
            if ($tml->getLicence()->getNiFlag() != $niFlag) {
                continue;
            }
            // only SI
            if ($tml->getLicence()->getLicenceType()->getId() !== Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
                continue;
            }
            return true;
        }

        return false;
    }

    /**
     * Is an SI Qualification required for this TM
     *
     * @param string $niFlag 'N' = GB, 'Y' = NI
     *
     * @return boolean
     */
    public function isSiQualificationRequiredOnVariation($niFlag)
    {
        $tmLicences = $this->getTmLicencesValid();
        foreach ($tmLicences as $tml) {
            // check niFlag
            if ($tml->getLicence()->getNiFlag() != $niFlag) {
                continue;
            }

            $variations = $tml->getLicence()->getVariations();
            foreach ($variations as $variation) {
                // only SI
                if (!$variation->isStandardInternational()) {
                    continue;
                }
                return true;
            }
        }
        return false;
    }

    public function isDetached()
    {
        $applicationStatuses = [
            Application::APPLICATION_STATUS_NOT_SUBMITTED,
            Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
            Application::APPLICATION_STATUS_GRANTED
        ];

        if (count($this->getCases()) > 0) {
            return false;
        }

        if (count($this->getTmLicencesValid()) > 0) {
            return false;
        }

        /** @var TransportManagerApplication $tmApplication */
        foreach ($this->getTmApplications() as $tmApplication) {
            if (in_array($tmApplication->getApplication()->getStatus()->getId(), $applicationStatuses)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMostRecentQualification()
    {
        $criteria = Criteria::create()
            ->orderBy(['issuedDate' => Criteria::DESC, 'id' => Criteria::DESC])
            ->setMaxResults(1);

        return $this->getQualifications()->matching($criteria);
    }

    /**
     * Does the entity have all address data for a repute check
     */
    public function hasReputeCheckAddress(): bool
    {
        // mandatory fields
        $fields = [
            'getForename',
            'getFamilyName',
            'getBirthDate',
            'getBirthPlace'
        ];

        $person = $this->homeCd->getPerson();

        if (!$person instanceof Person) {
            return false;
        }

        foreach ($fields as $field) {
            if (empty($person->$field())) {
                return false;
            }
        }

        return true;
    }

    public function hasQualifications(): bool
    {
        return !$this->qualifications->isEmpty();
    }

    /**
     * Return extra properties when serialzed
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'hasValidSiGbQualification' => $this->hasValidSiGbQualification(),
            'requireSiGbQualification' => $this->isSiQualificationRequired('N'),
            'hasValidSiNiQualification' => $this->hasValidSiNiQualification(),
            'requireSiNiQualification' => $this->isSiQualificationRequired('Y'),
            'associatedOrganisationCount' => count($this->getAssociatedOrganisations()),
            'associatedTotalAuthVehicles' => $this->getTotAuthVehicles(),
            'isDetached' => $this->isDetached(),
            'requireSiGbQualificationOnVariation' => $this->isSiQualificationRequiredOnVariation('N'),
            'requireSiNiQualificationOnVariation' => $this->isSiQualificationRequiredOnVariation('Y'),
        ];
    }

    public function getContextValue()
    {
        return $this->getId();
    }

    /**
     * Get organisations this entity is linked to
     *
     * @return array Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getRelatedOrganisation()
    {
        $organisations = [];

        /* @var $tma TransportManagerApplication */
        foreach ($this->getTmApplications() as $tma) {
            $organisation = $tma->getApplication()->getRelatedOrganisation();
            $organisations[$organisation->getId()] = $organisation;
        }

        /* @var $tml TransportManagerLicence */
        foreach ($this->getTmLicences() as $tml) {
            $organisation = $tml->getLicence()->getRelatedOrganisation();
            $organisations[$organisation->getId()] = $organisation;
        }

        return $organisations;
    }

    public function getFullName(): string
    {
        $person = $this->homeCd->getPerson();

        if (!$person instanceof Person) {
            return 'unknown name';
        }

        return $person->getFullName();
    }
}
