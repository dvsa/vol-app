<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as CreateCommentCmd;
use Laminas\Stdlib\ArraySerializableInterface;

/**
 * SubmissionSectionComment
 */
class SubmissionSectionComment extends AbstractRepository
{
    protected $entity = \Dvsa\Olcs\Api\Entity\Submission\SubmissionSectionComment::class;

    /**
     * Check is already comment exists for section
     *
     * @param CreateCommentCmd|ArraySerializableInterface $cmd Http Query or Command
     *
     * @return bool
     */
    public function isExist(ArraySerializableInterface $cmd)
    {
        $qb = $this->createQueryBuilder();
        $expr = $qb->expr();
        $qb
            ->andWhere($expr->eq($this->alias . '.submission', ':SUBMISSION_ID'))
            ->andWhere($expr->eq($this->alias . '.submissionSection', ':SUBMISSION_SECTION'))
            ->setParameter('SUBMISSION_ID', $cmd->getSubmission())
            ->setParameter('SUBMISSION_SECTION', $cmd->getSubmissionSection())
            ->setMaxResults(1);

        return !empty($qb->getQuery()->getResult());
    }
}
