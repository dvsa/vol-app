<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate;
use Dvsa\Olcs\Api\Service\Letter\LetterPreviewService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;

/**
 * Preview LetterInstance - fetches letter instance and renders preview HTML
 *
 * Returns both the letter instance data and the rendered preview HTML.
 */
class Preview extends AbstractQueryHandler
{
    protected $repoServiceName = 'LetterInstance';
    protected $extraRepos = ['MasterTemplate'];

    private LetterPreviewService $previewService;

    /**
     * Bundle for fetching letter instance with all relationships
     * Same as Get handler
     */
    protected array $bundle = [
        'letterType' => [
            'masterTemplate'
        ],
        'licence' => [
            'organisation' => [
                'contactDetails' => [
                    'address'
                ]
            ],
            'trafficArea'
        ],
        'application',
        'case',
        'organisation' => [
            'contactDetails' => [
                'address'
            ]
        ],
        'letterInstanceSections' => [
            'letterSectionVersion' => [
                'sectionType'
            ]
        ],
        'letterInstanceIssues' => [
            'letterIssueVersion' => [
                'letterIssueType'
            ]
        ],
        'letterInstanceTodos' => [
            'letterTodoVersion'
        ],
        'letterInstanceAppendices' => [
            'letterAppendixVersion' => [
                'document'
            ]
        ],
        'createdBy' => [
            'contactDetails' => [
                'person'
            ]
        ]
    ];

    /**
     * Handle the preview query
     *
     * @param QueryInterface $query
     * @return array
     */
    public function handleQuery(QueryInterface $query): array
    {
        /** @var LetterInstanceEntity $letterInstance */
        $letterInstance = $this->getRepo()->fetchUsingId($query);

        // Get the master template from the letter type, or fall back to default
        $masterTemplate = $this->getMasterTemplate($letterInstance);

        // Render the preview HTML
        $previewHtml = $this->previewService->renderPreview($letterInstance, $masterTemplate);

        // Build sections list for the sidebar
        $sectionsList = $this->buildSectionsList($letterInstance);

        // Serialize the letter instance
        $letterInstanceData = $letterInstance->serialize($this->bundle);

        return [
            'letterInstance' => $letterInstanceData,
            'previewHtml' => $previewHtml,
            'sectionsList' => $sectionsList,
        ];
    }

    /**
     * Get the master template to use for rendering
     *
     * @param LetterInstanceEntity $letterInstance
     * @return MasterTemplate|null
     */
    private function getMasterTemplate(LetterInstanceEntity $letterInstance): ?MasterTemplate
    {
        // First try to get from the letter type
        $letterType = $letterInstance->getLetterType();
        if ($letterType !== null && $letterType->getMasterTemplate() !== null) {
            return $letterType->getMasterTemplate();
        }

        // Fall back to default template for locale
        try {
            $repo = $this->getRepo('MasterTemplate');
            $result = $repo->fetchList(
                \Dvsa\Olcs\Transfer\Query\Letter\MasterTemplate\GetList::create([
                    'isDefault' => true,
                    'locale' => MasterTemplate::LOCALE_EN_GB,
                    'limit' => 1
                ])
            );

            if (count($result) > 0) {
                return $result[0];
            }
        } catch (\Exception) {
            // Log and continue without template
        }

        return null;
    }

    /**
     * Build the sections list for the sidebar
     *
     * Groups issues by their Issue Type and returns unique types
     * for display as checkboxes in the sidebar.
     *
     * @param LetterInstanceEntity $letterInstance
     * @return array Array of unique Issue Types
     */
    private function buildSectionsList(LetterInstanceEntity $letterInstance): array
    {
        $issueTypeMap = [];

        foreach ($letterInstance->getLetterInstanceIssues() as $issue) {
            $issueVersion = $issue->getLetterIssueVersion();
            $issueType = $issueVersion->getLetterIssueType();

            if ($issueType !== null) {
                $typeId = $issueType->getId();
                // Only add if not already in the map (unique types)
                if (!isset($issueTypeMap[$typeId])) {
                    $issueTypeMap[$typeId] = [
                        'id' => $typeId,
                        'name' => $issueType->getName(),
                        'type' => 'issueType',
                    ];
                }
            }
        }

        return array_values($issueTypeMap);
    }

    /**
     * Factory method for dependency injection
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return self
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): self
    {
        $this->previewService = $container->get(LetterPreviewService::class);
        return parent::__invoke($container, $requestedName, $options);
    }
}
