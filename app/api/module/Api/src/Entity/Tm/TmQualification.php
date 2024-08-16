<?php

namespace Dvsa\Olcs\Api\Entity\Tm;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * TmQualification Entity
 *
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="tm_qualification",
 *    indexes={
 *        @ORM\Index(name="ix_tm_qualification_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_tm_qualification_country_code", columns={"country_code"}),
 *        @ORM\Index(name="ix_tm_qualification_qualification_type", columns={"qualification_type"}),
 *        @ORM\Index(name="ix_tm_qualification_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_tm_qualification_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_tm_qualification_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class TmQualification extends AbstractTmQualification
{
    public const ERROR_ISSUED_DATE_IN_FUTURE = 'TQ-ID-1';

    // TM Qualification types
    public const QUALIFICATION_TYPE_AR = 'tm_qt_ar';
    public const QUALIFICATION_TYPE_CPCSI = 'tm_qt_cpcsi';
    public const QUALIFICATION_TYPE_EXSI = 'tm_qt_exsi';
    public const QUALIFICATION_TYPE_NIAR = 'tm_qt_niar';
    public const QUALIFICATION_TYPE_NICPCSI = 'tm_qt_nicpcsi';
    public const QUALIFICATION_TYPE_NIEXSI = 'tm_qt_niexsi';
    public const QUALIFICATION_TYPE_LGVAR = 'tm_qt_lgvar';
    public const QUALIFICATION_TYPE_NILGVAR = 'tm_qt_nilgvar';

    /**
     * Create TmQualification object
     *
     *
     * @return TmQualification
     */
    public static function create(TransportManager $tm, Country $country, RefData $qualificationType, string $serialNo)
    {
        $tmQualification = new self();
        $tmQualification->setTransportManager($tm);
        $tmQualification->setCountryCode($country);
        $tmQualification->setQualificationType($qualificationType);
        $tmQualification->setSerialNo($serialNo);

        return $tmQualification;
    }

    public function updateTmQualification(
        $qualificationType,
        $serialNo,
        $issuedDate,
        $countryCode,
        $tm = null
    ) {
        $this->validateTmQualification($issuedDate);

        $this->setQualificationType($qualificationType);
        $this->setSerialNo($serialNo);
        $this->setIssuedDate(new \DateTime($issuedDate));
        $this->setCountryCode($countryCode);
        if ($tm !== null) {
            $this->setTransportManager($tm);
        }
    }

    protected function validateTmQualification($issuedDate)
    {
        if (new \DateTime($issuedDate) > new DateTime('now')) {
            throw new ValidationException(
                [
                    'issuedDate' => [
                        self::ERROR_ISSUED_DATE_IN_FUTURE => 'Issued date should not be in future'
                    ]
                ]
            );
        }
    }
}
