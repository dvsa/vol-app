<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * StoreSubmissionSnapshot
 */
final class StoreSubmissionSnapshot extends AbstractCommandHandler implements
    TransactionedInterface
{
    protected $repoServiceName = 'Submission';

    /**
     * Handle command
     *
     * @param CommandInterface $command DTO
     *
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $submission = $this->getRepo()->fetchUsingId($command);

        $this->result->merge($this->generateDocument($command->getHtml(), $submission));
        $this->result->addId('submission', $submission->getId());
        $this->result->addMessage('Submission snapshot created');

        return $this->result;
    }

    /**
     * Generate the document for the snapshot and store it on the docstore
     *
     * @param string     $content    HTML snapshot content
     * @param Submission $submission Submission snapshot is for
     *
     * @return Result
     */
    protected function generateDocument($content, Submission $submission)
    {
        $licNo = 'No attached licence';
        $case = $submission->getCase();
        $licence = $case->getLicence();
        $caseId = $case->getId();

        if ($licence instanceof LicenceEntity) {
            $licNo = $licence->getLicNo();
        }

        $name = sprintf(
            '%s - Submission - %d - Case %d - %s',
            $submission->getSubmissionType()->getDescription(),
            $submission->getId(),
            $caseId,
            $licNo
        );

        $data = [
            'content' => base64_encode(trim($content)),
            'case' => $caseId,
            'category' => Category::CATEGORY_SUBMISSION,
            'subCategory' => Category::SUBMISSION_SUB_CATEGORY_OTHER,
            'isExternal' => false,
            'isScan' => false,
            'filename' => $name . '.html',
            'description' => $name,
        ];

        return $this->handleSideEffect(Upload::create($data));
    }
}
