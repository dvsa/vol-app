<?php

namespace Dvsa\Olcs\Api\Entity\Opposition;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Opposition\Opposer;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Opposition Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="opposition",
 *    indexes={
 *        @ORM\Index(name="ix_opposition_opposer_id", columns={"opposer_id"}),
 *        @ORM\Index(name="ix_opposition_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_opposition_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_opposition_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_opposition_opposition_type", columns={"opposition_type"}),
 *        @ORM\Index(name="ix_opposition_is_valid", columns={"is_valid"}),
 *        @ORM\Index(name="ix_opposition_status", columns={"status"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="ux_olbs_key", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Opposition extends AbstractOpposition
{
    public const OPPOSITION_TYPE_ENV = 'otf_eob';

    public function __construct(
        Cases $case,
        Opposer $opposer,
        RefData $oppositionType,
        $isValid,
        $isCopied,
        $isInTime,
        $isWillingToAttendPi,
        $isWithdrawn
    ) {
        parent::__construct();
        $this->setCase($case);
        $this->setOpposer($opposer);
        $this->setOppositionType($oppositionType);
        $this->setIsValid($isValid);
        $this->setIsCopied($isCopied);
        $this->setIsInTime($isInTime);
        $this->setIsWillingToAttendPi($isWillingToAttendPi);
        $this->setIsWithdrawn($isWithdrawn);
    }
}
