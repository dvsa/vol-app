<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Cases\Statement;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Pi\Pi;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke;

/**
 * SlaTargetDate Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="sla_target_date",
 *    indexes={
 *        @ORM\Index(name="ix_sla_target_date_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_sla_target_date_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_sla_target_date_document_id", columns={"document_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_sla_target_date_document_id", columns={"document_id"})
 *    }
 * )
 */
class SlaTargetDate extends AbstractSlaTargetDate
{
    /**
     * SlaTargetDate constructor.
     *
     * Creates a new SlaTargetDate entity and sets required data. Any entity must be checked here by setting the
     * appropriate field (such as Document) to maintain a foregn key constraint.
     *
     * @param mixed $entity          Entity
     * @param mixed $agreedDate      Agreed Date
     * @param mixed $underDelegation Under Delegation
     */
    public function __construct(mixed $entity, mixed $agreedDate, mixed $underDelegation = null)
    {
        if ($entity instanceof Document) {
            $this->setDocument($entity);
        } elseif ($entity instanceof Pi) {
            $this->setPi($entity);
        } elseif ($entity instanceof Submission) {
            $this->setSubmission($entity);
        } elseif ($entity instanceof ProposeToRevoke) {
            $this->setProposeToRevoke($entity);
        } elseif ($entity instanceof Statement) {
            $this->setStatement($entity);
        } else {
            throw new NotFoundException('Entity not found');
        }

        $this->setAgreedDate($agreedDate);
        $this->setUnderDelegation($underDelegation);
    }
}
