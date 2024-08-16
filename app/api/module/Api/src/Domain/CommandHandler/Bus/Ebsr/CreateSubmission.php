<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\CreateSubmission as CreateSubmissionCmd;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;

/**
 * Create a blank EBSR submission (file has been uploaded but user hasn't clicked confirm)
 */
final class CreateSubmission extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'EbsrSubmission';

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var CreateSubmissionCmd $command */
        $ebsrSub = new EbsrSubmission(
            $this->getCurrentOrganisation(),
            $this->getRepo()->getRefdataReference(EbsrSubmission::UPLOADED_STATUS),
            $this->getRepo()->getRefdataReference(EbsrSubmission::UNKNOWN_SUBMISSION_TYPE),
            $this->getRepo()->getReference(Document::class, $command->getDocument())
        );

        $this->getRepo()->save($ebsrSub);

        $result = new Result();
        $result->addId('ebsrSubmission', $ebsrSub->getId());
        $result->addMessage('Ebsr Submission created');

        return $result;
    }
}
