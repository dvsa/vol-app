<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\DeleteSubmission as DeleteEbsrSubmission;
use Dvsa\Olcs\Api\Domain\Repository\CorrespondenceInbox;
use Dvsa\Olcs\Api\Domain\Repository\SlaTargetDate;
use Psr\Container\ContainerInterface;

/**
 * Delete Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DeleteDocument extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Document';

    protected $extraRepos = ['CorrespondenceInbox','SlaTargetDate'];

    /**
     * @var ContentStoreFileUploader
     */
    private $fileUploader;

    /**
     * Deletes a document and optionally triggers side effect of deleting the associated EBSR submission
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Document $document */
        $document = $this->getRepo()->fetchUsingId($command);
        $identifier = $document->getIdentifier();

        if (!empty($identifier)) {
            $this->fileUploader->remove($identifier);
            $result->addMessage('File removed');
        }

        //if it's an EBSR doc, also delete the associated submission
        if ($document->getEbsrSubmission()) {
            $result->merge(
                $this->handleSideEffect(
                    DeleteEbsrSubmission::create(['id' => $document->getEbsrSubmission()->getId()])
                )
            );
        }

        /** @var CorrespondenceInbox $correspondenceInboxeRepo */
        $correspondenceInboxeRepo = $this->getRepo('CorrespondenceInbox');
        $correspondenceInboxes = $correspondenceInboxeRepo->fetchByDocumentId($document->getId());
        foreach ($correspondenceInboxes as $correspondenceInbox) {
            $this->getRepo('CorrespondenceInbox')->delete($correspondenceInbox);
        }

        /** @var SlaTargetDate $slaTargetDateRepo */
        $slaTargetDateRepo = $this->getRepo('SlaTargetDate');
        $slaTargetDates = $slaTargetDateRepo->fetchByDocumentId($document->getId());
        foreach ($slaTargetDates as $slaTargetDate) {
            $this->getRepo('SlaTargetDate')->delete($slaTargetDate);
        }

        // If unlink flag was set, and the document was created by the current user, unlink the licence to
        // keep this deletion from showing on the change history page for the licence vol-5967
        // Needed due to the mysql trigger that updates the change history table
        if (
            $command->getUnlinkLicence() === true
            && $document->getCreatedBy()->getId() === $this->getCurrentUser()->getId()
        ) {
            $document->setLicence(null);
            $document->setApplication(null);
            $this->getRepo()->save($document);
        }

        $this->getRepo()->delete($document);

        $result->addMessage('Document deleted');

        return $result;
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->fileUploader = $container->get('FileUploader');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
