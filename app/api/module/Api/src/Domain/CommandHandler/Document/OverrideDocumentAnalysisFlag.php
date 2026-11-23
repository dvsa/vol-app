<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as DocumentAnalysisEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag as Cmd;

/**
 * A caseworker changes one failed or skipped check of a successful analysis to a pass, or leaves
 * a note on it without changing it.
 *
 * The change is recorded as an annotation; the analyser's result is left as it was. The row must
 * be one the analyser failed or skipped - not what it currently reads as once earlier annotations
 * are laid over it, so a row already changed is still the caseworker's to revisit or undo. The
 * analysis may be changed whether or not it has been decided. The caseworker's name is captured
 * now, so the change reads the same however the user record changes later.
 *
 * An analysis that does not exist is a 404 (fetchUsingId). One that exists but cannot take the
 * change is a 400, as AcceptDocumentAnalysisReview does.
 */
final class OverrideDocumentAnalysisFlag extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'DocumentAnalysis';

    public function __construct(
        private readonly AnalysisResultNormaliser $normaliser,
        private readonly AnalysisAnnotationOverlay $overlay,
    ) {
    }

    /**
     * @param Cmd $command
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $analysisId = (int)$command->getId();
        $rowKey = (string)$command->getRow();

        /** @var DocumentAnalysisRepo $repo */
        $repo = $this->getRepo();

        /** @var DocumentAnalysisEntity $analysis */
        $analysis = $repo->fetchUsingId($command);

        if ($analysis->getStatus() !== DocumentAnalysisEntity::STATUS_SUCCESS) {
            throw new BadRequestException(
                sprintf('Document analysis %d is %s, not SUCCESS, so cannot be changed', $analysisId, $analysis->getStatus())
            );
        }

        $normalised = $this->normaliser->fromStored($analysis->getResultNormalised());

        if ($normalised === null) {
            throw new BadRequestException(
                sprintf('Document analysis %d has no assessment to change', $analysisId)
            );
        }

        // What can be answered here is asked of the analyser's own flag, not of the row as it
        // reads now: the caseworker's answer stands and can be revisited, so a row they have
        // already changed to a pass is still theirs to edit or undo.
        $rawFlag = $normalised->rows()[$rowKey]['flag'] ?? null;

        if (!in_array($rawFlag, AnalysisAnnotationOverlay::OVERRIDABLE_FLAGS, true)) {
            throw new BadRequestException(
                sprintf('Check %s of document analysis %d is not a fail or skipped, so cannot be changed', $rowKey, $analysisId)
            );
        }

        $annotations = $analysis->getAnnotations();

        /** @var UserEntity $user */
        $user = $this->getCurrentUser();

        // "Y" changes the row to a pass. "N" means the analyser's outcome stands: on a row
        // changed earlier that undoes the change; with a comment the comment is kept as a note
        // against the analyser's own flag, and with none and nothing there to take away, nothing
        // is written at all.
        $changeToPass = (string)$command->getChangeRecommendation() === 'Y';
        $comment = trim((string)$command->getComment());
        $undone = !$changeToPass && $this->overlay->apply($normalised, $annotations)->rows()[$rowKey]['flag'] !== $rawFlag;
        $clears = !$changeToPass && $comment === '';

        $this->result->addId('documentAnalysis', $analysisId);

        if ($clears && !$this->overlay->hasRow($annotations, $rowKey)) {
            // Nothing there to take away either, so nothing is written and the version is not bumped.
            $this->result->addMessage(sprintf('Document analysis %d check %s left as it is', $analysisId, $rowKey));

            return $this->result;
        }

        $updated = $clears
            ? $this->overlay->withoutRow($annotations, $rowKey)
            : $this->overlay->withOverride(
                $annotations,
                $rowKey,
                $changeToPass ? NormalisedResult::FLAG_PASS : $rawFlag,
                $rawFlag,
                $comment,
                (int)$user->getId(),
                $this->userName($user),
                new \DateTimeImmutable()
            );

        if ($repo->recordAnnotations($analysisId, $updated, (int)$analysis->getVersion(), $user) === 0) {
            throw new BadRequestException(
                sprintf('Document analysis %d changed while it was being reviewed; reload and try again', $analysisId)
            );
        }

        $this->result->addMessage(sprintf(
            'Document analysis %d check %s %s',
            $analysisId,
            $rowKey,
            match (true) {
                $changeToPass => 'changed to pass',
                $undone => 'changed back to its original flag',
                $clears => 'comment removed',
                default => 'comment saved',
            }
        ));

        return $this->result;
    }

    /** The person's name where the user has one; internal users always should. */
    private function userName(UserEntity $user): string
    {
        $name = $user->getContactDetails()?->getPerson()?->getFullName();

        return is_string($name) && $name !== '' ? $name : (string)$user->getLoginId();
    }
}
