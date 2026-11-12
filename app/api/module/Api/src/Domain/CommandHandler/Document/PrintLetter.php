<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Doctrine\ORM\EntityNotFoundException;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterOpenForChangesTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
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
    use LetterOpenForChangesTrait;

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
        $email = $method === PrintLetterCmd::METHOD_EMAIL && $canEmail;
        $print = $method === PrintLetterCmd::METHOD_PRINT_AND_POST && $canPrint;

        // locked first: an edit that landed since the check above fails this save (the letter's
        // version has moved on) before anything is queued to go out. Only the first time it goes
        // out: a resend keeps the date it was first sent and doesn't get a second task.
        if (($email || $print) && $letterInstance !== null && !$letterInstance->isSent()) {
            $this->markLetterSent($letterInstance);
            $this->createResponseTask($letterInstance);
        }

        if ($email) {
            if ($this->sendEmail($document)) {
                $this->queueSms($document);
            }
        }

        if ($print) {
            $this->attemptPrint($document);
            $this->queueSms($document);
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
     * Queue the "letter issued" SMS
     *
     * @param Entity\Doc\Document $document Document
     *
     * @return void
     */
    private function queueSms(Entity\Doc\Document $document)
    {
        $this->result->merge(
            $this->handleSideEffect(DomainCmd\Sms\QueueLetterSms::create(['document' => $document->getId()]))
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
     * @return bool True if the correspondence record was created
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
     * Refuse a PDF that no longer matches its letter (a newer one has been prepared since, or the
     * letter has changed since, which takes it back to draft) or whose letter was cancelled
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

        if ($letterInstance->isCancelled()) {
            throw new ValidationException(['This letter has been cancelled, so it can\'t be sent.']);
        }
    }

    /**
     * A letter from the letter builder is sent once its PDF goes out, which locks it: what's on it
     * stays what was sent.
     */
    private function markLetterSent(Entity\Letter\LetterInstance $letterInstance): void
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\LetterInstance $repo */
        $repo = $this->getRepo('LetterInstance');
        $letterInstance->markAsSent($repo->getRefdataReference(Entity\Letter\LetterInstance::STATUS_SENT));
        $this->saveLetterChange(fn () => $repo->save($letterInstance));
    }

    /**
     * A first or final request letter is chased up: the caseworker who wrote it gets a task to
     * check for the response 14 days after it's sent (VOL-7714). The older template letters get
     * the same task in CreateLetter, and like theirs it isn't marked urgent.
     *
     * The task belongs to an application, so a letter that isn't on one gets none.
     */
    private function createResponseTask(Entity\Letter\LetterInstance $letterInstance): void
    {
        $application = $letterInstance->getApplication();
        if ($application === null) {
            return;
        }

        $choices = $letterInstance->getSelectedChoiceKeys();
        if (in_array(Entity\Letter\LetterChoice::KEY_FIRST_REQUEST, $choices, true)) {
            $subCategory = Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_RESPONSE_TO_FIRST_REQUEST;
            $description = 'Check response to first letters';
        } elseif (in_array(Entity\Letter\LetterChoice::KEY_FINAL_REQUEST, $choices, true)) {
            $subCategory = Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_RESPONSE_TO_FINAL_REQUEST;
            $description = 'Check response to final letters';
        } else {
            return;
        }

        // with no writer to give it to, the task is still made and task allocation decides who gets it
        $writerId = $this->writerIdOf($letterInstance);

        $this->result->merge($this->handleSideEffect(DomainCmd\Task\CreateTask::create([
            'category' => Entity\System\Category::CATEGORY_APPLICATION,
            'subCategory' => $subCategory,
            'description' => $description,
            'actionDate' => (new DateTime())->add(new \DateInterval('P14D'))->format('Y-m-d'),
            'assignedToUser' => $writerId,
            'assignedByUser' => $writerId,
            'urgent' => 'N',
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId(),
        ])));
    }

    /**
     * The id of the caseworker who wrote the letter, if they can be given a task. Null when there's
     * no record of them, or their account has been deleted since they wrote it.
     */
    private function writerIdOf(Entity\Letter\LetterInstance $letterInstance): ?int
    {
        $writer = $letterInstance->getCreatedBy();
        if ($writer === null) {
            return null;
        }

        try {
            // the task goes to the writer's team as well. Reading the team loads the user, and a
            // deleted user can't be loaded: all that's left of them here is the id
            $writer->getTeam();
        } catch (EntityNotFoundException) {
            return null;
        }

        return $writer->getId();
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $fullContainer = $container;

        $this->srvPrintLetter = $container->get(Service\Document\PrintLetter::class);
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
