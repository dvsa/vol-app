<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * SiPenaltyErruImposed Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="si_penalty_erru_imposed",
 *    indexes={
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_serious_infringement_id", columns={"serious_infringement_id"}),
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_si_penalty_imposed_type_id", columns={"si_penalty_imposed_type_id"}),
 *        @ORM\Index(name="ix_si_penalty_erru_imposed_executed", columns={"executed"})
 *    }
 * )
 */
class SiPenaltyErruImposed extends AbstractSiPenaltyErruImposed
{
    public function __construct(
        SeriousInfringement $seriousInfringement,
        SiPenaltyImposedType $siPenaltyImposedType,
        RefData $executed,
        \DateTime $startDate,
        \DateTime $endDate,
        \DateTime $finalDecisionDate,
        int $penaltyImposedIdentifier
    ) {
        $this->seriousInfringement = $seriousInfringement;
        $this->siPenaltyImposedType = $siPenaltyImposedType;
        $this->executed = $executed;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->finalDecisionDate = $finalDecisionDate;
        $this->penaltyImposedIdentifier = $penaltyImposedIdentifier;
    }
}
