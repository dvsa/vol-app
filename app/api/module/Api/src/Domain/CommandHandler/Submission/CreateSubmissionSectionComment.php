<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\EditorJsConverterAwareInterface;
use Dvsa\Olcs\Api\Domain\EditorJsConverterAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionSectionComment;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\EditorJsConversionTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as Cmd;

/**
 * Create SubmissionSectionComment
 */
final class CreateSubmissionSectionComment extends AbstractCommandHandler implements EditorJsConverterAwareInterface
{
    use EditorJsConversionTrait;

    public const string ERR_COMMENT_EXISTS = 'Comment already exists';

    protected $repoServiceName = 'SubmissionSectionComment';

    /**
     * Command Handler
     *
     * @param Cmd $command Command
     *
     * @return Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\SubmissionSectionComment $repo */
        $repo = $this->getRepo();

        if ($repo->isExist($command)) {
            throw new ValidationException(
                [
                    'NOT_COMPLETE' => self::ERR_COMMENT_EXISTS,
                ]
            );
        }

        //  create comment entity
        $comment = new SubmissionSectionComment(
            $repo->getReference(Submission::class, $command->getSubmission()),
            $repo->getRefdataReference($command->getSubmissionSection())
        );

        if ($command->getComment() !== null) {
            $commentText = $this->convertCommentForStorage($command->getComment());
            $comment->setComment($commentText);
        }

        //  same comment
        $repo->save($comment);

        $result =  new Result();
        $result->addId('submissionSectionComment', $comment->getId())
            ->addId('submissionSection', $comment->getSubmissionSection()->getId())
            ->addMessage('Submission section comment created successfully');

        return $result;
    }
}
