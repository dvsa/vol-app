<?php

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Data\CategoryDataService as Category;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

class FinancialEvidenceAssessmentController extends AbstractController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected StringHelperService $stringHelper,
        protected RestrictionHelperService $restrictionHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected $navigation
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    public function indexAction()
    {
        $analyses = $this->getAnalyses();
        $documents = $this->findDocumentsForApplication()['results'] ?? [];

        $view = new ViewModel([
            'title'        => 'lva.section.title.financial_evidence_assessment',
            'hasDocuments' => !empty($documents),
            'tabs'         => $this->getTabsFromAnalyses($analyses, $documents),
        ]);
        $view->setTemplate('sections/lva/financial-evidence-assessment');

        return $this->render($view);
    }

    protected function findDocumentsForApplication(): array
    {
        $query = DocumentList::create([
            'application'         => $this->getIdentifier(),
            'category'            => Category::CATEGORY_APPLICATION,
            'documentSubCategory' => [Category::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL],
            'sort'                => 'id',
            'order'               => 'desc',
            'page'                => '1',
            'limit'               => '10',
            'showDocs'            => 'tsw_self_only',
        ]);

        return $this->handleQuery($query)->getResult();
    }

    protected function getDocumentsWithAnalysisStatus(array $analyses): array
    {
        $documentsResult = $this->findDocumentsForApplication();
        $documents = $documentsResult['results'] ?? [];

        $analysesByDocumentId = [];
        foreach ($analyses as $analysis) {
            $analysesByDocumentId[$analysis['documentId']] = $analysis;
        }

        foreach ($documents as &$document) {
            $analysis = $analysesByDocumentId[$document['document']] ?? null;
            $document['analysisStatus']    = $analysis['status'] ?? null;
            $document['analysisStatusTag'] = $this->mapStatusTagClass($analysis['status'] ?? null);
            $document['analysisLabel']     = $this->mapStatus($analysis['status'] ?? null);
        }
        unset($document);

        return $documents;
    }

    protected function getTabsFromAnalyses(array $analyses, array $documents): array
    {
        $documentsByDocumentId = [];
        foreach ($documents as $document) {
            $documentsByDocumentId[$document['document']] = $document;
        }

        $latest = $analyses[0] ?? null;
        $latestDoc = $latest !== null ? ($documentsByDocumentId[$latest['documentId']] ?? null) : null;
        $latestDate = isset($latestDoc['issuedDate']) ? (new \DateTime($latestDoc['issuedDate']))->format('d/m/Y') : null;

        $tabs = [
            [
                'id'        => 'latest',
                'label'     => 'Latest',
                'date'      => $latestDate,
                'status'    => $latest !== null ? $this->mapStatus($latest['status']) : 'No analysis yet',
                'statusTag' => $latest !== null ? $this->mapStatusTagClass($latest['status']) : 'govuk-tag--grey',
            ],
        ];

        foreach (array_slice($analyses, 1) as $analysis) {
            $doc = $documentsByDocumentId[$analysis['documentId']] ?? null;
            $date = isset($doc['issuedDate']) ? (new \DateTime($doc['issuedDate']))->format('d/m/Y') : null;

            $tabs[] = [
                'id'        => 'analysis-' . $analysis['id'],
                'label'     => $date ?? 'Unknown date',
                'date'      => $date,
                'status'    => $this->mapStatus($analysis['status']),
                'statusTag' => $this->mapStatusTagClass($analysis['status']),
            ];
        }

        return $tabs;
    }

    protected function mapStatus(?string $status): string
    {
        return match ($status) {
            'SUCCESS' => 'Approved',
            'ERROR'   => 'Rejected',
            'PENDING' => 'Pending',
            default   => 'Unknown',
        };
    }

    protected function mapStatusTagClass(?string $status): string
    {
        return match ($status) {
            'SUCCESS' => 'govuk-tag--green',
            'ERROR'   => 'govuk-tag--red',
            'PENDING' => 'govuk-tag--grey',
            default   => 'govuk-tag--grey',
        };
    }

    protected function getAnalyses(): array
    {
        $response = $this->handleQuery(
            DocumentAnalysisList::create(['application' => $this->getIdentifier()])
        );

        if (!$response->isOk()) {
            return [];
        }

        $analyses = $response->getResult()['analyses'] ?? [];

        usort($analyses, static fn($a, $b) => strcmp($b['completedAt'] ?? '', $a['completedAt'] ?? ''));
        //dd($analyses);
        return $analyses;
    }
}
