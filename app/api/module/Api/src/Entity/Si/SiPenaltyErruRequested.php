<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiPenaltyErruRequested Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="si_penalty_erru_requested",
 *    indexes={
 *        @ORM\Index(name="ix_si_penalty_erru_requested_serious_infringement_id", columns={"serious_infringement_id"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_si_penalty_requested_type_id", columns={"si_penalty_requested_type_id"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_requested_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class SiPenaltyErruRequested extends AbstractSiPenaltyErruRequested
{
    public function __construct(
        SeriousInfringement $seriousInfringement,
        SiPenaltyRequestedType $siPenaltyRequestedType,
        $duration
    ) {
        $this->seriousInfringement = $seriousInfringement;
        $this->siPenaltyRequestedType = $siPenaltyRequestedType;
        $this->duration = $duration;
    }
}
