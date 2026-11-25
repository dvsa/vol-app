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

/**
 * A caseworker's answer on one check of a successful analysis: change it to the subclass's
 * target flag, or leave the analyser's outcome standing, with or without a note. The change is
 * stored as an annotation over the analyser's result, and can be revisited or undone later.
 */
abstract class AbstractOverrideDocumentAnalysisFlag extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /** The key of the refusal to change a check with no reason given. */
    public const string ERR_REASON_REQUIRED = 'ERR_DOCUMENT_ANALYSIS_REASON_REQUIRED';

    public const string MSG_REASON_REQUIRED = 'A reason is required to change this check';

    protected $repoServiceName = 'DocumentAnalysis';

    public function __construct(
        private readonly AnalysisResultNormaliser $normaliser,
        private readonly AnalysisAnnotationOverlay $overlay,
    ) {
    }

    /** The flag "Y" changes a check to. */
    abstract protected function targetFlag(): string;

    /**
     * The analyser flags a check must have for this command to answer it.
     *
     * @return list<string>
     */
    abstract protected function answerableFlags(): array;

    /** The refusal for a check whose analyser flag is not one of answerableFlags(). */
    abstract protected function notAnswerableMessage(string $rowKey, int $analysisId): string;

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

        $analyserFlag = $normalised->rows()[$rowKey]['flag'] ?? null;

        if (!in_array($analyserFlag, $this->answerableFlags(), true)) {
            throw new BadRequestException($this->notAnswerableMessage($rowKey, $analysisId));
        }

        $annotations = $analysis->getAnnotations();
        $currentFlag = $this->overlay->apply($normalised, $annotations)->rows()[$rowKey]['flag'] ?? null;

        $change = (string)$command->getChangeRecommendation() === 'Y';
        $comment = trim((string)$command->getComment());

        // "N" with no comment is valid (it clears the entry), so the command cannot make the
        // comment required itself; a reason is only required when changing a genuine pass/fail.
        if ($change && $comment === '' && $analyserFlag !== NormalisedResult::FLAG_SKIPPED) {
            throw new ValidationException([static::ERR_REASON_REQUIRED => static::MSG_REASON_REQUIRED]);
        }

        $changedBack = !$change && $currentFlag !== $analyserFlag;
        $clears = !$change && $comment === '';

        $this->result->addId('documentAnalysis', $analysisId);

        if ($clears && !$this->overlay->hasRow($annotations, $rowKey)) {
            $this->result->addMessage(sprintf('Document analysis %d check %s left as it is', $analysisId, $rowKey));

            return $this->result;
        }

        /** @var UserEntity $user */
        $user = $this->getCurrentUser();

        $updated = $clears
            ? $this->overlay->withoutRow($annotations, $rowKey)
            : $this->overlay->withOverride(
                $annotations,
                $rowKey,
                $change ? $this->targetFlag() : $analyserFlag,
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
                $change => 'changed to ' . $this->targetFlag(),
                $changedBack => 'changed back to ' . $analyserFlag,
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
