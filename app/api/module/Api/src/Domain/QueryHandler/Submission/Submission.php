<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Submission;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Submission
 */
final class Submission extends AbstractQueryHandler
{
    protected $repoServiceName = 'Submission';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\Submission $repo */
        $repo = $this->getRepo();

        // retrieve reason even if deleted
        $repo->disableSoftDeleteable(
            [
                \Dvsa\Olcs\Api\Entity\Pi\Reason::class,
                \Dvsa\Olcs\Api\Entity\User\User::class
            ]
        );

        /** @var SubmissionEntity $submission */
        $submission = $repo->fetchUsingId($query);

        return $this->result(
            $submission,
            [
                'case',
                'recipientUser' => [
                    'contactDetails' => [
                        'person'
                    ]
                ],
                'senderUser' => [
                    'contactDetails' => [
                        'person'
                    ]
                ],
                'documents' => [
                    'category',
                    'subCategory'
                ],
                'submissionSectionComments' => [
                    'submissionSection'
                ],
                'submissionActions' => [
                    'actionTypes',
                    'reasons',
                    'createdBy' => [
                        'contactDetails' => [
                            'person'
                        ]
                    ],
                ]
            ],
            [
                'canClose' => $submission->canClose(),
                'isClosed' => $submission->isClosed(),
                'canReopen' => $submission->canReopen(),
                'submissionTypeTitle' => $this->getSubmissionTypeTitle($submission),
                'isNi' => $submission->isNi()
            ]
        );
    }

    /**
     * Method that takes the submission type and looks up the ref data title for that submission type
     *
     * @param SubmissionEntity $submission Submission
     *
     * @return string
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function getSubmissionTypeTitle(SubmissionEntity $submission)
    {
        $titleId = str_replace('_o_', '_t_', $submission->getSubmissionType()->getId());
        return $this->getRepo()->getRefdataReference($titleId);
    }
}
