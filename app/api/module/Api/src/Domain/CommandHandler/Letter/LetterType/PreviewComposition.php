<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterType as LetterTypeEntity;
use Dvsa\Olcs\Api\Service\Letter\CompositionDiagnostics;
use Dvsa\Olcs\Api\Service\Letter\GrabOutcomeCollector;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\LetterPreviewService;
use Dvsa\Olcs\Api\Service\Letter\MasterTemplateResolver;
use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionCandidate;
use Dvsa\Olcs\Api\Service\Letter\SectionVariantResolver;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\PreviewComposition as Cmd;
use Psr\Container\ContainerInterface;

/**
 * Render a proposed letter type composition without saving it.
 *
 * Letter types are configured blind today: an admin arranges sections in the admin screens and
 * finds out what they built only when somebody generates a real letter. This renders the
 * composition as it stands, against a context the admin chooses, and reports what would go wrong.
 *
 * NOTHING IS WRITTEN. The letter instance is built with `new` and discarded; the repository is
 * never asked to save it. Just as important, the managed LetterType's own collections are never
 * touched: they are mapped cascade-persist with orphanRemoval, so removing an element in memory
 * and flushing anywhere later in the request would delete the saved composition. The proposed
 * composition is therefore assembled from ids into fresh objects, never by editing the entity.
 */
final class PreviewComposition extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterType';

    protected $extraRepos = [
        'LetterSection',
        'LetterIssue',
        'LetterAppendix',
        'Licence',
        'Application',
    ];

    private SectionVariantResolver $sectionVariantResolver;
    private LetterInstanceComposer $letterInstanceComposer;
    private LetterPreviewService $previewService;
    private MasterTemplateResolver $masterTemplateResolver;
    private CompositionDiagnostics $diagnostics;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->sectionVariantResolver = $container->get(SectionVariantResolver::class);
        $this->letterInstanceComposer = $container->get(LetterInstanceComposer::class);
        $this->previewService = $container->get(LetterPreviewService::class);
        $this->masterTemplateResolver = $container->get(MasterTemplateResolver::class);
        $this->diagnostics = $container->get(CompositionDiagnostics::class);

        return parent::__invoke($container, $requestedName, $options);
    }

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var LetterTypeEntity $letterType */
        $letterType = $this->getRepo()->fetchById($command->getLetterType());

        $letterInstance = new LetterInstanceEntity();
        $letterInstance->setLetterType($letterType);

        // Real generation stamps a reference, and the letterhead prints it. Without one the
        // preview shows a bare "Our ref:", which reads as a fault in the letter rather than an
        // artefact of previewing it.
        $letterInstance->setReference(LetterInstanceEntity::generateReference());
        $this->attachContextRecords($letterInstance, $command);

        $context = $this->buildContext($letterInstance, $command);

        $resolution = $this->sectionVariantResolver->resolve(
            $this->buildCandidates($letterType, $command),
            $context
        );

        // Only resolved sections are composed. An unresolved one has no version to read content
        // from and would fatal the renderer; it is reported instead, which is the point of the
        // exercise.
        $this->letterInstanceComposer->composeSections($letterInstance, $resolution);
        $this->letterInstanceComposer->composeIssues($letterInstance, $this->fetchIssueVersions($command));
        $this->letterInstanceComposer->composeTodos($letterInstance);
        $this->letterInstanceComposer->composeAppendices($letterInstance, $this->fetchAppendixVersions($command));

        // Always rendered with the master template. Without it the renderer returns bare content
        // with no A4 page, stylesheet or letterhead, which is not a letter and so not a preview.
        // Measured on a warm local stack the chrome costs ~119ms of a ~1s round trip, which is
        // well inside the debounce and nowhere near the cost the design assumed.
        //
        // The effective isNi is passed through so a Region override picks the matching locale's
        // chrome too -- otherwise an NI preview renders NI wording under a GB letterhead.
        $masterTemplate = $this->masterTemplateResolver->resolve($letterInstance, $context['isNi']);

        // Collecting grab outcomes is what lets the diagnostics flag a token that rendered to
        // nothing -- invisible in the HTML precisely because it was stripped.
        $grabOutcomes = new GrabOutcomeCollector();

        $html = $this->previewService->renderPreview($letterInstance, $masterTemplate, grabOutcomes: $grabOutcomes);

        $this->result->setFlag('html', $html);
        $this->result->setFlag('diagnostics', $this->diagnostics->forResolution($resolution, $html, $grabOutcomes));
        $this->result->setFlag('context', $context);

        return $this->result;
    }

    /**
     * The composition to preview: what the admin currently has on screen, or what is saved when
     * they have not changed anything yet.
     *
     * @return SectionCandidate[]
     */
    private function buildCandidates(LetterTypeEntity $letterType, Cmd $command): array
    {
        $sectionIds = $command->getSections();

        if ($sectionIds === null) {
            return SectionCandidate::listFromLetterType($letterType);
        }

        $requiredIds = array_map('intval', $command->getSectionsRequired() ?? []);
        $candidates = [];
        $displayOrder = 0;

        foreach ($sectionIds as $sectionId) {
            $candidates[] = new SectionCandidate(
                $this->getRepo('LetterSection')->fetchById($sectionId),
                $displayOrder++,
                in_array((int) $sectionId, $requiredIds, true)
            );
        }

        return $candidates;
    }

    /**
     * Attach the records bookmarks resolve against. Both are optional: without them the letter
     * still renders and the unresolvable bookmarks are reported rather than silently blank.
     */
    private function attachContextRecords(LetterInstanceEntity $letterInstance, Cmd $command): void
    {
        if ($command->getLicence() !== null) {
            $licence = $this->getRepo('Licence')->fetchById($command->getLicence());
            $letterInstance->setLicence($licence);
            $letterInstance->setOrganisation($licence->getOrganisation());
        }

        if ($command->getApplication() !== null) {
            $application = $this->getRepo('Application')->fetchById($command->getApplication());
            $letterInstance->setApplication($application);

            $licence = $application->getLicence();
            if ($licence !== null && $letterInstance->getLicence() === null) {
                $letterInstance->setLicence($licence);
                $letterInstance->setOrganisation($licence->getOrganisation());
            }
        }
    }

    /**
     * Variant context: taken from the chosen records, then overridden by whatever the admin set.
     *
     * The overrides are the reason this screen exists. Deriving context purely from a real record
     * means only combinations that real data happens to cover can ever be seen -- and since a
     * licence carries no application, isVariation is null, so every variant that pins it is
     * unreachable. Letting each dimension be set directly lets an admin check the wording a real
     * letter would carry without hunting for a licence that happens to match.
     */
    private function buildContext(LetterInstanceEntity $letterInstance, Cmd $command): array
    {
        $application = $letterInstance->getApplication();
        $licence = $letterInstance->getLicence();
        $organisation = $letterInstance->getOrganisation();

        $derived = [
            'goodsOrPsv' => $application?->getGoodsOrPsv()?->getId() ?? $licence?->getGoodsOrPsv()?->getId(),
            'isVariation' => $application ? (bool) $application->getIsVariation() : null,
            'isNi' => $licence?->isNi(),
            'organisationType' => $organisation?->getType()?->getId(),
            'selectedChoiceIds' => [],
        ];

        $overrides = [
            'goodsOrPsv' => $command->getGoodsOrPsv(),
            'isVariation' => $command->getIsVariation(),
            'isNi' => $command->getIsNi(),
            'organisationType' => $command->getOrganisationType(),
            'selectedChoiceIds' => $command->getSelectedChoiceIds(),
        ];

        foreach ($overrides as $key => $value) {
            if ($value !== null) {
                $derived[$key] = $value;
            }
        }

        // Overrides arrive over HTTP as strings ('1'/'0'), but variant matching compares with
        // !== against Doctrine booleans -- so an un-normalised override never matches a variant
        // that pins isVariation or isNi, it just silently falls back to the default wording.
        foreach (['isVariation', 'isNi'] as $flag) {
            if ($derived[$flag] !== null) {
                $derived[$flag] = filter_var($derived[$flag], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
        }

        $derived['selectedChoiceIds'] = array_map('intval', $derived['selectedChoiceIds'] ?? []);

        return $derived;
    }

    /**
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion[]
     */
    private function fetchIssueVersions(Cmd $command): array
    {
        $versions = [];

        foreach ($command->getIssues() ?? [] as $issueId) {
            $version = $this->getRepo('LetterIssue')->fetchById($issueId)->getCurrentVersion();
            if ($version !== null) {
                $versions[] = $version;
            }
        }

        return $versions;
    }

    /**
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion[]
     */
    private function fetchAppendixVersions(Cmd $command): array
    {
        $versions = [];

        foreach ($command->getAppendices() ?? [] as $appendixId) {
            $version = $this->getRepo('LetterAppendix')->fetchById($appendixId)->getCurrentVersion();
            if ($version !== null) {
                $versions[] = $version;
            }
        }

        return $versions;
    }
}
