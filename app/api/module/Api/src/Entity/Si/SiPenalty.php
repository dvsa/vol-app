<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyType as SiPenaltyTypeEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested as SiPenaltyErruRequestedEntity;

/**
 * SiPenalty Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="si_penalty",
 *    indexes={
 *        @ORM\Index(name="fk_si_penalty_si_penalty_requested_id_si_penalty_requested_id", columns={"si_penalty_erru_requested_id"}),
 *        @ORM\Index(name="ix_si_penalty_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_si_penalty_serious_infringement_id", columns={"serious_infringement_id"}),
 *        @ORM\Index(name="ix_si_penalty_si_penalty_type_id", columns={"si_penalty_type_id"})
 *    }
 * )
 */
class SiPenalty extends AbstractSiPenalty
{
    public function __construct(
        SiEntity $seriousInfringement,
        SiPenaltyTypeEntity $siPenaltyType,
        SiPenaltyErruRequestedEntity $requestedErru,
        string $imposed,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        string $reasonNotImposed = null
    ) {
        parent::__construct();

        $this->seriousInfringement = $seriousInfringement;
        $this->update($siPenaltyType, $requestedErru, $imposed, $startDate, $endDate, $reasonNotImposed);
    }

    public function update(
        SiPenaltyTypeEntity $siPenaltyType,
        SiPenaltyErruRequestedEntity $requestedErru,
        string $imposed,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        string $reasonNotImposed = null
    ) {
        $this->siPenaltyType = $siPenaltyType;
        $this->imposed = $imposed;
        $this->siPenaltyErruRequested = $requestedErru;

        if ($startDate !== null) {
            $this->startDate = $startDate;
        }
        if ($endDate !== null) {
            $this->endDate = $endDate;
        }
        if ($reasonNotImposed !== null) {
            $this->reasonNotImposed = $reasonNotImposed;
        }
    }
}
