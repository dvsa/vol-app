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

    /** @var Service\Document\PrintLetter */
    private $srvPrintLetter;

    /**
     * Handle command
     *
     * @param PrintLetterCmd $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
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
        if ($method === PrintLetterCmd::METHOD_EMAIL && $canEmail) {
            $this->sendEmail($document);
        }

        if ($method === PrintLetterCmd::METHOD_PRINT_AND_POST && $canPrint) {
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

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->srvPrintLetter = $container->get(Service\Document\PrintLetter::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
