<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceChoice;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceGrabSnapshotter;
use Dvsa\Olcs\Api\Service\Letter\SectionVariantResolver;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\Generate as Cmd;
use Psr\Container\ContainerInterface;

/**
 * Generate LetterInstance
 *
 * Creates a new letter instance from a letter type and user selections.
 */
final class Generate extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;
    use LetterContextTrait;

    protected $repoServiceName = 'LetterInstance';

    protected $extraRepos = [
        'LetterType',
        'LetterIssue',
        'LetterAppendix',
        'LetterChoice',
        'Licence',
        'Application',
        'Cases',
        'BusReg',
        'TransportManager',
        'IrhpApplication',
        'Organisation',
    ];

    private SectionVariantResolver $sectionVariantResolver;
    private LetterInstanceComposer $letterInstanceComposer;
    private LetterInstanceGrabSnapshotter $grabSnapshotter;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->sectionVariantResolver = $container->get(SectionVariantResolver::class);
        $this->letterInstanceComposer = $container->get(LetterInstanceComposer::class);
        $this->grabSnapshotter = $container->get(LetterInstanceGrabSnapshotter::class);
        return parent::__invoke($container, $requestedName, $options);
    }

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        // Create the letter instance
        $letterInstance = new LetterInstanceEntity();

        // Generate reference if not provided
        $reference = LetterInstanceEntity::generateReference();
        $letterInstance->setReference($reference);

        // Set letter type (required)
        $letterType = $this->getRepo('LetterType')->fetchById($command->getLetterType());
        $letterInstance->setLetterType($letterType);

        // Set status to DRAFT
        $status = $this->getRepo()->getRefdataReference(LetterInstanceEntity::STATUS_DRAFT);
        $letterInstance->setStatus($status);

        // Blameable only stamps createdBy on flush, and the caseworker grabs need it before then.
        // Same rule as OlcsBlameableListener: never persist the transient anonymous user.
        $currentUser = $this->getCurrentUser();
        if ($currentUser !== null && !$currentUser->isAnonymous()) {
            $letterInstance->setCreatedBy($currentUser);
        }

        // Set optional relations (licence, application, case, etc.)
        $this->setOptionalRelations($letterInstance, $command);

        // A letter already sent, picked to start this one from
        $sentLetter = $this->sentLetterToStartFrom($command, $letterInstance);

        // Drop choices that don't apply to the licence's Goods/PSV before they can steer variants
        $goodsOrPsv = $this->goodsOrPsvAndNi($letterInstance)['goodsOrPsv'];
        $selectedChoiceIds = [];
        $letterChoices = [];
        foreach ($command->getSelectedChoices() ?? [] as $choiceId) {
            $letterChoice = $this->getRepo('LetterChoice')->fetchById($choiceId);
            if ($letterChoice->appliesToGoodsOrPsv($goodsOrPsv)) {
                $selectedChoiceIds[] = $choiceId;
                $letterChoices[] = $letterChoice;
            }
        }

        // Build context for variant resolution
        $context = $this->buildVariantContext($letterInstance, $selectedChoiceIds);

        // Populate instance sections from letter type assembly, resolving variants
        $resolution = $this->sectionVariantResolver->resolveForLetterType($letterType, $context);

        $this->letterInstanceComposer->composeSections($letterInstance, $resolution);

        // Warn about any sections that couldn't be resolved
        foreach ($resolution->getUnresolvedRequired() as $unresolvedSection) {
            $this->result->addMessage(
                'Required section "' . $unresolvedSection->getSectionName() . '" could not be included — no matching variant for the current context'
            );
        }

        foreach ($resolution->getUnresolvedOptional() as $unresolvedSection) {
            $this->result->addMessage(
                'Optional section "' . $unresolvedSection->getSectionName() . '" could not be included — no matching variant for the current context'
            );
        }

        if ($resolution->hasUnresolved()) {
            $this->result->setFlag('hasRequiredSectionWarnings', true);
        }

        // Create instance issues from selected issues. The screen only offers issues that
        // match the licence's Goods/PSV, this catches anything posted that doesn't.
        $issueVersions = [];
        foreach (array_unique($command->getSelectedIssues() ?? []) as $issueId) {
            $issueVersion = $this->getRepo('LetterIssue')->fetchById($issueId)->getCurrentVersion();
            if ($issueVersion && ($goodsOrPsv === null || $issueVersion->appliesToType($goodsOrPsv))) {
                $issueVersions[] = $issueVersion;
            }
        }
        $this->letterInstanceComposer->composeIssues($letterInstance, $issueVersions);

        $this->letterInstanceComposer->composeTodos($letterInstance);

        // Create instance appendices from selected appendices
        $appendixVersions = [];
        foreach ($command->getSelectedAppendices() ?? [] as $appendixId) {
            $appendixVersion = $this->getRepo('LetterAppendix')->fetchById($appendixId)->getCurrentVersion();
            if ($appendixVersion) {
                $appendixVersions[] = $appendixVersion;
            }
        }
        $this->letterInstanceComposer->composeAppendices($letterInstance, $appendixVersions);

        // Record selected letter choices
        foreach ($letterChoices as $letterChoice) {
            $instanceChoice = new LetterInstanceChoice();
            $instanceChoice->setLetterInstance($letterInstance);
            $instanceChoice->setLetterChoice($letterChoice);
            $letterInstance->addLetterInstanceChoice($instanceChoice);
        }

        // Resolve grabs now so the caseworker edits real values, not [[TOKENS]]
        $this->grabSnapshotter->snapshot($letterInstance);

        // Then the wording changed on the sent letter goes over what was generated. It comes
        // over as it was sent, dates and all, so the answer names the issues to check.
        if ($sentLetter !== null) {
            $copiedTo = $this->letterInstanceComposer->bringOverWording($letterInstance, $sentLetter);
            if ($copiedTo !== []) {
                $this->result->setFlag('wordingCopiedTo', array_map(
                    static fn (LetterInstanceIssue $issue): string => $issue->getDisplayName() ?: 'Issue',
                    $copiedTo
                ));
            }
        }

        // Save the letter instance with all its related entities
        $this->getRepo()->save($letterInstance);

        $this->result->addId('letterInstance', $letterInstance->getId());
        $this->result->addMessage("Letter instance '{$reference}' generated successfully");

        return $this->result;
    }

    /**
     * The sent letter the caseworker picked to start this one from, if they picked one.
     *
     * The New letter form only offers letters of the same type that were sent for whatever this
     * one is raised on (see Repository\LetterInstance::fetchLastSent). Anything else didn't come
     * from the form, and another record's letter wording isn't handed out.
     */
    private function sentLetterToStartFrom(Cmd $command, LetterInstanceEntity $letterInstance): ?LetterInstanceEntity
    {
        // nothing picked arrives as null, an empty or unreadable value as an empty string
        if (empty($command->getStartFromLetter())) {
            return null;
        }

        try {
            /** @var LetterInstanceEntity $sentLetter */
            $sentLetter = $this->getRepo()->fetchById((int) $command->getStartFromLetter());
        } catch (NotFoundException) {
            $sentLetter = null;
        }

        $offered = $sentLetter !== null
            && $sentLetter->isSent()
            && (int) $sentLetter->getLetterType()?->getId() === (int) $letterInstance->getLetterType()->getId()
            && $sentLetter->getRaisedOn() !== null
            && $sentLetter->getRaisedOn() === $letterInstance->getRaisedOn();

        if (!$offered) {
            throw new ValidationException(['The letter you picked to start from cannot be used for this letter']);
        }

        return $sentLetter;
    }

    /**
     * Build context array for variant resolution
     *
     * @param LetterInstanceEntity $letterInstance
     * @param array $selectedChoiceIds Choices that apply to this letter
     * @return array
     */
    private function buildVariantContext(LetterInstanceEntity $letterInstance, array $selectedChoiceIds): array
    {
        $application = $letterInstance->getApplication();
        $goodsOrPsvAndNi = $this->goodsOrPsvAndNi($letterInstance);

        $organisation = $letterInstance->getOrganisation();

        return [
            'goodsOrPsv' => $goodsOrPsvAndNi['goodsOrPsv'],
            'isVariation' => $application ? (bool) $application->getIsVariation() : null,
            'isNi' => $goodsOrPsvAndNi['isNi'],
            'organisationType' => $organisation?->getType()?->getId(),
            'selectedChoiceIds' => $selectedChoiceIds,
        ];
    }
}
