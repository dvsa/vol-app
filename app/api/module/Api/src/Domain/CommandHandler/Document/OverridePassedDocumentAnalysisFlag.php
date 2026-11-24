<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as DocumentAnalysisEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\OverridePassedDocumentAnalysisFlag as Cmd;

/**
 * A caseworker changes one passed check of a successful analysis to a fail. The reverse of
 * OverrideDocumentAnalysisFlag.
 *
 * The change is recorded as an annotation; the analyser's result is left as it was. The check
 * must be one the analyser passed. The caseworker's answer on it stands and can be revisited: "Y"
 * makes it a fail (or keeps it one and replaces the comment). "N" means the analyser's outcome
 * stands, which changes a check already failed back to a pass: with a comment the comment is kept
 * as a note, and with none the caseworker's entry for the check is deleted altogether.
 *
 * "Y" needs a reason: without one the command is refused with a ValidationException keyed
 * ERR_REASON_REQUIRED, whoever the caller is. The analysis may be changed
 * whether or not it has been decided. The caseworker's name is captured now, so the
 * change reads the same however the user record changes later.
 *
 * An analysis that does not exist is a 404 (fetchUsingId). One that exists but cannot take the
 * change is a 400, as OverrideDocumentAnalysisFlag does.
 */
final class OverridePassedDocumentAnalysisFlag extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /** The key of the refusal to change a passed check to a fail with no reason given. */
    public const string ERR_REASON_REQUIRED = 'ERR_DOCUMENT_ANALYSIS_REASON_REQUIRED';

    public const string MSG_REASON_REQUIRED = 'A reason is required to change a passed check to a fail';

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

        $annotations = $analysis->getAnnotations();

        // Which checks can be answered here is asked of the analyser's own flag, not of the check
        // as it reads now: the caseworker's answer stands and can be revisited, so a pass they
        // have already changed to a fail is still theirs to edit or change back.
        $analyserFlag = $normalised->rows()[$rowKey]['flag'] ?? null;

        if (!in_array($analyserFlag, AnalysisAnnotationOverlay::OVERRIDABLE_TO_FAIL_FLAGS, true)) {
            throw new BadRequestException(sprintf(
                'Check %s of document analysis %d was not assessed as a pass, so cannot be changed to a fail',
                $rowKey,
                $analysisId
            ));
        }

        $currentFlag = $this->overlay->apply($normalised, $annotations)->rows()[$rowKey]['flag'];

        /** @var UserEntity $user */
        $user = $this->getCurrentUser();

        // "Y" makes the check a fail, or keeps it one and replaces the comment. "N" means the
        // analyser's outcome stands: a check already changed goes back to a pass.
        $changeToFail = (string)$command->getChangeRecommendation() === 'Y';
        $comment = trim((string)$command->getComment());

        // Every check this command can answer is a genuine pass, so failing it needs a reason on
        // record. The internal app's form asks for one; this is the same rule for any other
        // caller of the API. The command's own validation cannot make the comment required,
        // because "N" with no comment is a real request (it removes the caseworker's entry).
        if ($changeToFail && $comment === '') {
            throw new ValidationException([self::ERR_REASON_REQUIRED => self::MSG_REASON_REQUIRED]);
        }
        $changedBack = !$changeToFail && $currentFlag !== $analyserFlag;

        // "N" with nothing to say leaves no caseworker entry at all, so the check reads exactly
        // as the analyser left it. "N" with a comment keeps the comment as a note against the
        // analyser's own flag.
        $clears = !$changeToFail && $comment === '';

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
                $changeToFail ? NormalisedResult::FLAG_FAIL : $analyserFlag,
                $analyserFlag,
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
                $changeToFail => 'changed to fail',
                $changedBack => 'changed back to pass',
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
