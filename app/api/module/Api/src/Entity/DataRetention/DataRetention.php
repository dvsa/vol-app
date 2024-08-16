<?php

namespace Dvsa\Olcs\Api\Entity\DataRetention;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime as CustomDatetime;

/**
 * DataRetention Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="data_retention",
 *    indexes={
 *        @ORM\Index(name="ix_entity_name", columns={"entity_name"}),
 *        @ORM\Index(name="ix_data_retention_rule_id", columns={"data_retention_rule_id"}),
 *        @ORM\Index(name="ix_delete_confirmation", columns={"action_confirmation"}),
 *        @ORM\Index(name="ix_deleted_date", columns={"deleted_date"}),
 *        @ORM\Index(name="ix_data_retention_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_data_retention_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_organisation_id", columns={"organisation_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_entity_name_entity_pk", columns={"entity_name","entity_pk"})
 *    }
 * )
 */
class DataRetention extends AbstractDataRetention
{
    /**
     * Mark for delay
     *
     * @param \DateTime|null $date Date to delay review until
     *
     * @return $this
     */
    public function markForDelay($date)
    {
        $this->markForReview();
        $this->nextReviewDate = $this->processDate($date);

        return $this;
    }

    /**
     * Mark for delete
     *
     * @return $this
     */
    public function markForDelete()
    {
        $this->actionedDate = new CustomDatetime();
        $this->actionConfirmation = true;
        $this->nextReviewDate = null;

        return $this;
    }

    /**
     * Mark for review
     *
     * @return $this
     */
    public function markForReview()
    {
        $this->actionedDate = null;
        $this->actionConfirmation = false;
        $this->nextReviewDate = null;

        return $this;
    }
}
