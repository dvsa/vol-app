<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\ORM\Mapping as ORM;
use DateInterval;
use DateTime;
use RuntimeException;

/**
 * IrhpPermitType Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_permit_type",
 *    indexes={
 *        @ORM\Index(name="irhp_permit_type_ref_data_id_fk", columns={"name"}),
 *        @ORM\Index(name="fk_irhp_permit_type_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_type_last_modified_by_user_id",
     *     columns={"last_modified_by"})
 *    }
 * )
 */
class IrhpPermitType extends AbstractIrhpPermitType
{
    public const IRHP_PERMIT_TYPE_ID_ECMT = 1;
    public const IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM = 2;
    public const IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL = 3;
    public const IRHP_PERMIT_TYPE_ID_BILATERAL = 4;
    public const IRHP_PERMIT_TYPE_ID_MULTILATERAL = 5;
    public const IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE = 6;
    public const IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER = 7;

    public const IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL_EXPIRY_INTERVAL = 'P1Y';

    public const CERTIFICATE_TYPES = [
        self::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
        self::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
    ];

    public const CONSTRAINED_COUNTRIES_TYPES = [
        self::IRHP_PERMIT_TYPE_ID_ECMT,
        self::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
    ];

    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'isEcmtAnnual' => $this->isEcmtAnnual(),
            'isEcmtShortTerm' => $this->isEcmtShortTerm(),
            'isEcmtRemoval' => $this->isEcmtRemoval(),
            'isBilateral' => $this->isBilateral(),
            'isMultilateral' => $this->isMultilateral(),
            'isCertificateOfRoadworthiness' => $this->isCertificateOfRoadworthiness(),
            'isApplicationPathEnabled' => $this->isApplicationPathEnabled(),
        ];
    }

    /**
     * Is this ECMT Annual
     *
     * @return bool
     */
    public function isEcmtAnnual()
    {
        return $this->id === self::IRHP_PERMIT_TYPE_ID_ECMT;
    }

    /**
     * Is this ECMT Short Term
     *
     * @return bool
     */
    public function isEcmtShortTerm()
    {
        return $this->id === self::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM;
    }

    /**
     * Is this ECMT Removal
     *
     * @return bool
     */
    public function isEcmtRemoval()
    {
        return $this->id === self::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL;
    }

    /**
     * Is this Bilateral
     *
     * @return bool
     */
    public function isBilateral()
    {
        return $this->id === self::IRHP_PERMIT_TYPE_ID_BILATERAL;
    }

    /**
     * Is this Multilateral
     *
     * @return bool
     */
    public function isMultilateral()
    {
        return $this->id === self::IRHP_PERMIT_TYPE_ID_MULTILATERAL;
    }

    /**
     * Can this permit type have apply for more than one stock on a single application?
     *
     * @return bool
     */
    public function isMultiStock(): bool
    {
        return $this->isMultilateral() || $this->isBilateral();
    }

    /**
     * Is application path enabled
     *
     * @return bool
     */
    public function isApplicationPathEnabled()
    {
        return $this->isEcmtShortTerm() || $this->isEcmtRemoval() || $this->isCertificateOfRoadworthiness() || $this->isEcmtAnnual();
    }

    /**
     * Is irhp permit application path enabled
     *
     * @return bool
     */
    public function isIrhpPermitApplicationPathEnabled()
    {
        return $this->isBilateral();
    }

    /**
     * Generate an expiry date for this permit type using the supplied date time as an issue date
     *
     *
     * @return DateTime
     */
    public function generateExpiryDate(DateTime $issueDateTime)
    {
        $expiryDateTime = clone $issueDateTime;

        if ($this->isEcmtRemoval()) {
            $expiryDateTime->add(new DateInterval('P1Y'));
            $expiryDateTime->sub(new DateInterval('P1D'));

            return $expiryDateTime;
        } elseif ($this->isBilateral()) {
            // only applicable to Morocco - other countries do not have an expiry date
            $expiryDateTime->add(new DateInterval('P3M'));

            return $expiryDateTime;
        } elseif ($this->isEcmtShortTerm()) {
            // short term ECMTs last for 30 days
            $expiryDateTime->add(new DateInterval('P30D'));

            return $expiryDateTime;
        }

        throw new RuntimeException('Unable to generate an expiry date for permit type ' . $this->id);
    }

    /**
     * Is this a certificate of roadworthiness
     *
     * @return bool
     */
    public function isCertificateOfRoadworthiness(): bool
    {
        return $this->isCertificateOfRoadworthinessVehicle() || $this->isCertificateOfRoadworthinessTrailer();
    }

    /**
     * Is this a certificate of roadworthiness vehicle
     *
     * @return bool
     */
    public function isCertificateOfRoadworthinessVehicle(): bool
    {
        return $this->id === self::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE;
    }

    /**
     * Is this a certificate of roadworthiness trailer
     *
     * @return bool
     */
    public function isCertificateOfRoadworthinessTrailer(): bool
    {
        return $this->id === self::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER;
    }

    /**
     * Whether this permit type needs the multi stock behaviour on licence selection
     *
     * @return bool
     */
    public function usesMultiStockLicenceBehaviour(): bool
    {
        return $this->isMultiStock() || $this->isEcmtRemoval() || $this->isCertificateOfRoadworthiness();
    }

    /**
     * Whether this permit type is one to which the concept of constrained countries applies
     *
     * @return bool
     */
    public function isConstrainedCountriesType()
    {
        return in_array($this->id, self::CONSTRAINED_COUNTRIES_TYPES);
    }
}
