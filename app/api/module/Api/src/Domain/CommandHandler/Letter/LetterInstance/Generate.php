<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceChoice;
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

        // Build context for variant resolution
        $context = $this->buildVariantContext($letterInstance, $command);

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
        $goodsOrPsv = $context['goodsOrPsv'];
        $issueVersions = [];
        foreach ($command->getSelectedIssues() ?? [] as $issueId) {
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
        if (!empty($command->getSelectedChoices())) {
            foreach ($command->getSelectedChoices() as $choiceId) {
                $letterChoice = $this->getRepo('LetterChoice')->fetchById($choiceId);
                $instanceChoice = new LetterInstanceChoice();
                $instanceChoice->setLetterInstance($letterInstance);
                $instanceChoice->setLetterChoice($letterChoice);
                $letterInstance->addLetterInstanceChoice($instanceChoice);
            }
        }

        // Resolve grabs now so the caseworker edits real values, not [[TOKENS]]
        $this->grabSnapshotter->snapshot($letterInstance);

        // Save the letter instance with all its related entities
        $this->getRepo()->save($letterInstance);

        $this->result->addId('letterInstance', $letterInstance->getId());
        $this->result->addMessage("Letter instance '{$reference}' generated successfully");

        return $this->result;
    }

    /**
     * Build context array for variant resolution
     *
     * @param LetterInstanceEntity $letterInstance
     * @param Cmd $command
     * @return array
     */
    private function buildVariantContext(LetterInstanceEntity $letterInstance, Cmd $command): array
    {
        $application = $letterInstance->getApplication();
        $goodsOrPsvAndNi = $this->goodsOrPsvAndNi($letterInstance);

        $organisation = $letterInstance->getOrganisation();

        return [
            'goodsOrPsv' => $goodsOrPsvAndNi['goodsOrPsv'],
            'isVariation' => $application ? (bool) $application->getIsVariation() : null,
            'isNi' => $goodsOrPsvAndNi['isNi'],
            'organisationType' => $organisation?->getType()?->getId(),
            'selectedChoiceIds' => $command->getSelectedChoices() ?? [],
        ];
    }
}
