<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyType as SiPenaltyTypeEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType as SiPenaltyRequestedTypeEntity;

/**
 * SiPenalty Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="si_penalty",
 *    indexes={
 *        @ORM\Index(name="ix_si_penalty_serious_infringement_id", columns={"serious_infringement_id"}),
 *        @ORM\Index(name="ix_si_penalty_si_penalty_type_id", columns={"si_penalty_type_id"}),
 *        @ORM\Index(name="ix_si_penalty_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_si_penalty_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class SiPenalty extends AbstractSiPenalty
{
    /**
     * SiPenalty constructor.
     * @param string $imposed
     * @param string $reasonNotImposed
     */
    public function __construct(
        SiEntity $seriousInfringement,
        SiPenaltyTypeEntity $siPenaltyType,
        SiPenaltyRequestedTypeEntity $requestedErru,
        $imposed,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        $reasonNotImposed = null
    ) {
        $this->seriousInfringement = $seriousInfringement;
        $this->update($siPenaltyType, $requestedErru, $imposed, $startDate, $endDate, $reasonNotImposed);
    }

    /**
     * @param string $imposed
     * @param string $reasonNotImposed
     */
    public function update(
        SiPenaltyTypeEntity $siPenaltyType,
        SiPenaltyRequestedTypeEntity $requestedErru,
        $imposed,
        \DateTime $startDate = null,
        \DateTime $endDate = null,
        $reasonNotImposed = null
    ) {
        $this->siPenaltyType = $siPenaltyType;
        $this->imposed = $imposed;
        $this->erruPenaltyRequested = $requestedErru;

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
