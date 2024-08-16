<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusNoticePeriod Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="bus_notice_period",
 *    indexes={
 *        @ORM\Index(name="ix_bus_notice_period_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_bus_notice_period_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class BusNoticePeriod extends AbstractBusNoticePeriod
{
    public const NOTICE_PERIOD_SCOTLAND = 1;
    public const NOTICE_PERIOD_OTHER = 2;
    public const NOTICE_PERIOD_WALES = 3;

    /**
     * Create a new bus notice period
     *
     *
     * @return self
     */
    public static function createNew(string $noticeArea, int $standardPeriod, int $cancellationPeriod = 0): self
    {
        $entity = new self();
        $entity->setNoticeArea($noticeArea);
        $entity->setStandardPeriod($standardPeriod);
        $entity->setCancellationPeriod($cancellationPeriod);

        return $entity;
    }

    /**
     * Returns whether the notice period is scottish rules, usually called from the parent busReg
     *
     * @return bool
     */
    public function isScottishRules(): bool
    {
        return $this->id === self::NOTICE_PERIOD_SCOTLAND;
    }

    public function isWalesRules(): bool
    {
        return $this->id === self::NOTICE_PERIOD_WALES;
    }
}
