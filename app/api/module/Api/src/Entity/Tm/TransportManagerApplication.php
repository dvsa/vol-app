<?php

namespace Dvsa\Olcs\Api\Entity\Tm;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * TransportManagerApplication Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="transport_manager_application",
 *    indexes={
 *        @ORM\Index(name="ix_transport_manager_application_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_transport_manager_application_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_transport_manager_application_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_transport_manager_application_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_transport_manager_application_tm_type", columns={"tm_type"}),
 *        @ORM\Index(name="ix_transport_manager_application_tm_application_status", columns={"tm_application_status"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_transport_manager_application_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class TransportManagerApplication extends AbstractTransportManagerApplication implements OrganisationProviderInterface
{
    public const ACTION_ADD    = 'A';
    public const ACTION_UPDATE = 'U';
    public const ACTION_DELETE = 'D';

    public const STATUS_INCOMPLETE = 'tmap_st_incomplete';
    public const STATUS_AWAITING_SIGNATURE = 'tmap_st_awaiting_signature';
    public const STATUS_TM_SIGNED = 'tmap_st_tm_signed';
    public const STATUS_OPERATOR_SIGNED = 'tmap_st_operator_signed';
    public const STATUS_POSTAL_APPLICATION = 'tmap_st_postal_application';
    public const STATUS_RECEIVED = 'tmap_st_received';
    public const STATUS_DETAILS_SUBMITTED = 'tmap_st_details_submitted';
    public const STATUS_DETAILS_CHECKED = 'tmap_st_details_checked';
    public const STATUS_OPERATOR_APPROVED = 'tmap_st_operator_approved';

    public const ERROR_TM_EXIST = 'tm_exist';
    public const ERROR_DOB_REQUIRED = 'dob_required';

    public const ERROR_MON = 'err_mon';
    public const ERROR_TUE = 'err_tue';
    public const ERROR_WED = 'err_wed';
    public const ERROR_THU = 'err_thu';
    public const ERROR_FRI = 'err_fri';
    public const ERROR_SAT = 'err_sat';
    public const ERROR_SUN = 'err_sun';

    public const TYPE_INTERNAL = 'tm_t_i';
    public const TYPE_EXTERNAL = 'tm_t_e';
    public const TYPE_BOTH = 'tm_t_b';

    public function updateTransportManagerApplication(
        $application,
        $transportManager,
        $action,
        $tmApplicationStatus
    ) {
        $this->setApplication($application);
        $this->setTransportManager($transportManager);
        $this->setAction($action);
        $this->setTmApplicationStatus($tmApplicationStatus);
    }

    public function updateTransportManagerApplicationFull(
        $tmType = null,
        $isOwner = null,
        $hasUndertakenTraining = null,
        $hoursMon = null,
        $hoursTue = null,
        $hoursWed = null,
        $hoursThu = null,
        $hoursFri = null,
        $hoursSat = null,
        $hoursSun = null,
        $additionalInformation = null,
        $tmApplicationStatus = null
    ) {
        $this->validateTransportManagerApplication(
            $hoursMon,
            $hoursTue,
            $hoursWed,
            $hoursThu,
            $hoursFri,
            $hoursSat,
            $hoursSun
        );
        $this->setTmType($tmType);
        $this->setIsOwner($isOwner);
        $this->setHasUndertakenTraining($hasUndertakenTraining);
        $this->setHoursMon($hoursMon);
        $this->setHoursTue($hoursTue);
        $this->setHoursWed($hoursWed);
        $this->setHoursThu($hoursThu);
        $this->setHoursFri($hoursFri);
        $this->setHoursSat($hoursSat);
        $this->setHoursSun($hoursSun);
        $this->setAdditionalInformation($additionalInformation);
        $this->setTmApplicationStatus($tmApplicationStatus);
    }

    public function updateOperatorDigitalSignature(RefData $signatureType, DigitalSignature $signature): void
    {
        $this->opDigitalSignature = $signature;
        $this->opSignatureType = $signatureType;
    }

    public function updateTmDigitalSignature(RefData $signatureType, DigitalSignature $signature): void
    {
        $this->tmDigitalSignature = $signature;
        $this->tmSignatureType = $signatureType;
    }

    protected function validateTransportManagerApplication(
        $hoursMon,
        $hoursTue,
        $hoursWed,
        $hoursThu,
        $hoursFri,
        $hoursSat,
        $hoursSun
    ) {
        $errors = [];
        if (!is_null($hoursMon)) {
            if ((float) $hoursMon < 0 || (float) $hoursMon > 24) {
                $errors[] = ['hoursMon' => [self::ERROR_MON => 'Mon must be between 0 and 24, inclusively']];
            }
        }
        if (!is_null($hoursTue)) {
            if ((float) $hoursTue < 0 || (float) $hoursTue > 24) {
                $errors[] = ['hoursTue' => [self::ERROR_TUE => 'Tue must be between 0 and 24, inclusively']];
            }
        }
        if (!is_null($hoursWed)) {
            if ((float) $hoursWed < 0 || (float) $hoursWed > 24) {
                $errors[] = ['hoursWed' => [self::ERROR_WED => 'Wed must be between 0 and 24, inclusively']];
            }
        }
        if (!is_null($hoursThu)) {
            if ((float) $hoursThu < 0 || (float) $hoursThu > 24) {
                $errors[] = ['hoursThu' => [self::ERROR_THU => 'Thu must be between 0 and 24, inclusively']];
            }
        }
        if (!is_null($hoursFri)) {
            if ((float) $hoursFri < 0 || (float) $hoursFri > 24) {
                $errors[] = ['hoursFri' => [self::ERROR_FRI => 'Fri must be between 0 and 24, inclusively']];
            }
        }
        if (!is_null($hoursSat)) {
            if ((float) $hoursSat < 0 || (float) $hoursSat > 24) {
                $errors[] = ['hoursSat' => [self::ERROR_SAT => 'Sat must be between 0 and 24, inclusively']];
            }
        }
        if (!is_null($hoursSun)) {
            if ((float) $hoursSun < 0 || (float) $hoursSun > 24) {
                $errors[] = ['hoursSun' => [self::ERROR_SUN => 'Sun must be between 0 and 24, inclusively']];
            }
        }
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Simple method to sum all the daily hours to give a weekly total
     * @return int
     */
    public function getTotalWeeklyHours()
    {
        $weeklyHours = 0;

        $weeklyHours += (int) $this->getHoursMon();
        $weeklyHours += (int) $this->getHoursTue();
        $weeklyHours += (int) $this->getHoursWed();
        $weeklyHours += (int) $this->getHoursThu();
        $weeklyHours += (int) $this->getHoursFri();
        $weeklyHours += (int) $this->getHoursSat();
        $weeklyHours += (int) $this->getHoursSun();

        return $weeklyHours;
    }

    /**
     * Is the TM type External
     *
     * @return bool
     */
    public function isTypeExternal()
    {
        return $this->getTmType() !== null && $this->getTmType()->getId() === self::TYPE_EXTERNAL;
    }

    /**
     * Is the TM type Internal
     *
     * @return bool
     */
    public function isTypeInternal()
    {
        return $this->getTmType() !== null && $this->getTmType()->getId() === self::TYPE_INTERNAL;
    }

    /**
     * @inheritdoc
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getRelatedOrganisation()
    {
        return $this->getApplication()->getLicence()->getOrganisation();
    }
}
