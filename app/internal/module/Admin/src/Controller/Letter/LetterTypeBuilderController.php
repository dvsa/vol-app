<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Dvsa\Olcs\Transfer\Command\Letter\LetterType\PreviewComposition as PreviewCompositionDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterAppendix\GetList as AppendixListDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterChoice\GetList as ChoiceListDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterSection\GetList as SectionListDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterType\Get as LetterTypeDTO;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Letter Type Builder
 *
 * Compose a letter type and see what it produces, against a context you choose, before anything is
 * saved. The existing letter type screens are untouched: this is a second way in, so it can be
 * judged against them rather than replacing them on trust.
 *
 * Nothing here writes. Saving goes through the same Letter\LetterType\Update command the existing
 * edit screen uses, so both surfaces agree about what a letter type is.
 */
class LetterTypeBuilderController extends AbstractInternalController implements LeftViewProvider
{
    /** The largest page size PagedTrait permits. */
    private const LIST_LIMIT = 100;

    protected $inlineScripts = [
        'indexAction' => ['forms/letter-type-builder'],
    ];

    #[\Override]
    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/letter-management',
                'navigationTitle' => 'Letter Management',
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * The builder itself. Everything the composition list offers is loaded up front so reordering
     * and adding are local operations -- the preview is the only thing that needs the server.
     */
    public function indexAction(): ViewModel
    {
        $letterTypeId = (int) $this->params()->fromRoute('id', 0);

        $view = new ViewModel([
            'letterTypeId' => $letterTypeId,
            'letterType' => $letterTypeId > 0 ? $this->fetchLetterType($letterTypeId) : null,
            'availableSections' => $this->fetchList(SectionListDTO::class),
            'availableAppendices' => $this->fetchList(AppendixListDTO::class),
            'availableChoices' => $this->fetchList(ChoiceListDTO::class),
        ]);
        $view->setTemplate('admin/letter-type-builder/index');

        return $view;
    }

    /**
     * Render the composition currently on screen.
     *
     * Proxies to the API command, which builds a throwaway letter instance and discards it. The
     * composition arrives as posted JSON rather than query parameters because it is far too large
     * for a URL once a letter type has any real number of sections.
     */
    public function previewAction(): JsonModel|Response
    {
        $payload = json_decode((string) $this->getRequest()->getContent(), true);

        if (!is_array($payload) || empty($payload['letterType'])) {
            return $this->jsonError('A letter type is required to preview a composition');
        }

        // Every array field is sent on every request, even when empty. An ArrayInput that is
        // absent fails validation rather than defaulting, and the same discipline is what keeps a
        // save from wiping a list the screen simply did not mention.
        $payload += [
            'sections' => [],
            'sectionsRequired' => [],
            'appendices' => [],
            'issues' => [],
            'selectedChoiceIds' => [],
        ];

        $response = $this->handleCommand(PreviewCompositionDTO::create($payload));

        if (!$response->isOk()) {
            return $this->jsonError('Could not render this composition');
        }

        $flags = $response->getResult()['flags'] ?? [];

        return new JsonModel([
            'status' => 200,
            'html' => $flags['html'] ?? '',
            'diagnostics' => $flags['diagnostics'] ?? [],
            'context' => $flags['context'] ?? [],
        ]);
    }

    private function fetchLetterType(int $id): ?array
    {
        $response = $this->handleQuery(LetterTypeDTO::create(['id' => $id]));

        return $response->isOk() ? $response->getResult() : null;
    }

    /**
     * Everything the composition list can offer.
     *
     * PagedTrait caps limit at 100, so a service with more than that many sections would need
     * paging here. There are 18 today, and the builder needs them all in one dropdown, so the
     * ceiling is taken rather than paged around.
     *
     * @param class-string $listDto
     * @return array
     */
    private function fetchList(string $listDto): array
    {
        $response = $this->handleQuery($listDto::create([
            'page' => 1,
            'limit' => self::LIST_LIMIT,
            'sort' => 'id',
            'order' => 'ASC',
        ]));

        if (!$response->isOk()) {
            // An empty dropdown is confusing but harmless, and it must not take the page down --
            // the preview is still usable against the saved composition.
            return [];
        }

        return $response->getResult()['results'] ?? [];
    }

    /**
     * Save the composition through the same command the existing edit screen uses.
     *
     * Update replaces each list it is given wholesale, and sets name and description
     * unconditionally, so the payload has to carry everything the builder is responsible for AND
     * echo back what it merely displays. Anything the builder does not manage -- issues, choices,
     * category -- is omitted so the handler leaves it alone.
     */
    public function saveAction(): JsonModel
    {
        $payload = json_decode((string) $this->getRequest()->getContent(), true);

        if (!is_array($payload) || empty($payload['id'])) {
            return $this->jsonError('A letter type is required to save');
        }

        $letterType = $this->fetchLetterType((int) $payload['id']);

        if ($letterType === null) {
            return $this->jsonError('That letter type no longer exists');
        }

        $sections = array_map('intval', $payload['sections'] ?? []);
        $appendices = array_map('intval', $payload['appendices'] ?? []);

        // A section may appear at most once per letter type: the join is keyed on the pair, so a
        // duplicate fails the insert AFTER the existing rows have already been cleared.
        $hasDuplicates = count($sections) !== count(array_unique($sections))
            || count($appendices) !== count(array_unique($appendices));

        if ($hasDuplicates) {
            return $this->jsonError('Each section and appendix can only be added once');
        }

        $command = UpdateDTO::create([
            'id' => $letterType['id'],
            'version' => $letterType['version'],
            // Echoed rather than edited here: Update sets both unconditionally, so omitting
            // description would silently clear it.
            'name' => $letterType['name'],
            'description' => $letterType['description'] ?? null,
            'sections' => $sections,
            'sectionsRequired' => array_map('intval', $payload['sectionsRequired'] ?? []),
            'appendices' => $appendices,
        ]);

        $response = $this->handleCommand($command);

        if (!$response->isOk()) {
            return $this->jsonError('Could not save this composition');
        }

        return new JsonModel(['status' => 200, 'message' => 'Composition saved']);
    }

    private function jsonError(string $message): JsonModel
    {
        return new JsonModel(['status' => 400, 'message' => $message]);
    }
}
