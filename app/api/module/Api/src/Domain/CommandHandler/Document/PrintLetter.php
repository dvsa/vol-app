<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
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

        $method = $command->getMethod();
        $sent = false;
        if ($method === PrintLetterCmd::METHOD_EMAIL && $canEmail) {
            $sent = $this->sendEmail($document);
        }

        if ($method === PrintLetterCmd::METHOD_PRINT_AND_POST && $canPrint) {
            $this->attemptPrint($document);
            $sent = true;
        }

        if ($sent) {
            $this->markLetterSent($document);
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
     * @return bool whether it was sent
     */
    private function sendEmail(Entity\Doc\Document $document): bool
    {
        $licence = $document->getRelatedLicence();
        if ($licence === null) {
            return false;
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

        return true;
    }

    /**
     * A letter from the letter builder is sent once its document goes out, which locks it: what's
     * on it stays what was sent. A resend keeps the date it was first sent.
     */
    private function markLetterSent(Entity\Doc\Document $document): void
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\LetterInstance $repo */
        $repo = $this->getRepo('LetterInstance');
        $letterInstance = $repo->fetchByDocument((int) $document->getId());
        if ($letterInstance === null || $letterInstance->isSent()) {
            return;
        }

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
