<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Service;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter as PrintLetterCmd;
use Psr\Container\ContainerInterface;

/**
 * Print Letter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class PrintLetter extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Document';

    protected $extraRepos = ['LetterInstance'];

    /** @var Service\Document\PrintLetter */
    private $srvPrintLetter;

    /**
     * Handle command
     *
     * @param PrintLetterCmd $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        /** @var Entity\Doc\Document $document */
        $document = $this->getRepo()->fetchUsingId($command);

        $canPrint = $this->srvPrintLetter->canPrint($document);
        $canEmail = $this->srvPrintLetter->canEmail($document, $command->getForceCorrespondence());

        // We always want a translation task if the translateToWelsh flag is on
        if (!$canPrint) {
            $this->createTranslationTask($document);
        }

        // a PDF from the letter builder can only go out while it matches its letter
        $letterInstance = $this->letterFor($document);
        if ($letterInstance !== null) {
            $this->assertUpToDate($letterInstance, $document);
        }

        $method = $command->getMethod();
        $email = $method === PrintLetterCmd::METHOD_EMAIL && $canEmail && $document->getRelatedLicence() !== null;
        $print = $method === PrintLetterCmd::METHOD_PRINT_AND_POST && $canPrint;

        // locked first: an edit that landed since the check above fails this save (the letter's
        // version has moved on) before anything is queued to go out
        if (($email || $print) && $letterInstance !== null) {
            $this->markLetterSent($letterInstance);
        }

        if ($email) {
            $this->sendEmail($document);
        }

        if ($print) {
            $this->attemptPrint($document);
        }

        return $this->result;
    }

    /**
     * Attempt to print
     *
     * @param Entity\Doc\Document $document Document
     *
     * @return void
     */
    private function attemptPrint(Entity\Doc\Document $document)
    {
        $cmd = DomainCmd\PrintScheduler\Enqueue::create(
            [
                'documentId' => $document->getId(),
                'jobName' => $document->getDescription(),
            ]
        );

        $this->result->merge(
            $this->handleSideEffect($cmd)
        );
    }

    /**
     * Create translation task
     *
     * @param Entity\Doc\Document $document Document
     *
     * @return void
     */
    private function createTranslationTask(Entity\Doc\Document $document)
    {
        $lic = $document->getRelatedLicence();
        if ($lic === null) {
            return;
        }

        $cmd = DomainCmd\Task\CreateTranslateToWelshTask::create(
            [
                'description' => $document->getDescription(),
                'licence' => $lic->getId(),
            ]
        );

        $this->result->merge(
            $this->handleSideEffect($cmd)
        );
    }

    /**
     * Send email
     *
     * @param Entity\Doc\Document $document Document
     *
     * @return void
     */
    private function sendEmail(Entity\Doc\Document $document)
    {
        $licence = $document->getRelatedLicence();
        if ($licence === null) {
            return;
        }

        $cmd = DomainCmd\Email\CreateCorrespondenceRecord::create(
            [
                'licence' => $licence->getId(),
                'document' => $document->getId(),
                'type' => DomainCmd\Email\CreateCorrespondenceRecord::TYPE_STANDARD,
            ]
        );

        $this->result->merge(
            $this->handleSideEffect($cmd)
        );
    }

    /**
     * The letter a PDF was prepared from, if it came from the letter builder: from the PDF's own
     * record of it, or for PDFs prepared before that was kept, the letter's link to its PDF
     */
    private function letterFor(Entity\Doc\Document $document): ?Entity\Letter\LetterInstance
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\LetterInstance $repo */
        $repo = $this->getRepo('LetterInstance');

        $metadata = json_decode((string) $document->getMetadata(), true);
        $letterInstanceId = is_array($metadata) ? ($metadata['details']['letterInstance'] ?? null) : null;
        if (is_numeric($letterInstanceId)) {
            try {
                return $repo->fetchById((int) $letterInstanceId);
            } catch (NotFoundException) {
                return null;
            }
        }

        return $repo->fetchByDocument((int) $document->getId());
    }

    /**
     * Refuse a PDF that no longer matches its letter: a newer one has been prepared since, or the
     * letter has changed since (which takes it back to draft)
     *
     * @throws ValidationException
     */
    private function assertUpToDate(Entity\Letter\LetterInstance $letterInstance, Entity\Doc\Document $document): void
    {
        if ($letterInstance->getDocument()?->getId() !== $document->getId()) {
            throw new ValidationException(['A newer version of this letter has been prepared. Send that one instead.']);
        }

        if ($letterInstance->isDraft()) {
            throw new ValidationException([
                'This letter has changed since it was prepared. Prepare it to send again, then send the new one.',
            ]);
        }
    }

    /**
     * A letter from the letter builder is sent once its PDF goes out, which locks it: what's on it
     * stays what was sent. A resend keeps the date it was first sent.
     */
    private function markLetterSent(Entity\Letter\LetterInstance $letterInstance): void
    {
        if ($letterInstance->isSent()) {
            return;
        }

        /** @var \Dvsa\Olcs\Api\Domain\Repository\LetterInstance $repo */
        $repo = $this->getRepo('LetterInstance');
        $letterInstance->markAsSent($repo->getRefdataReference(Entity\Letter\LetterInstance::STATUS_SENT));
        $repo->save($letterInstance);
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $fullContainer = $container;

        $this->srvPrintLetter = $container->get(Service\Document\PrintLetter::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
