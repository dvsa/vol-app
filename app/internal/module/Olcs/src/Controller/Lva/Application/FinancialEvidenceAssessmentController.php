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
        $analyses = $this->getSuccessfulAnalyses();
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

    protected function getTabsFromAnalyses(array $analyses, array $documents): array
    {
        $documentsByDocumentId = [];
        foreach ($documents as $document) {
            $documentsByDocumentId[$document['document']] = $document;
        }

        // Temporary caseworker stamp until stamping is implemented; independent of processing status.
        $caseworkerStamp = 'APPROVED';
        $tabs = [];

        foreach ($analyses as $analysis) {
            $doc = $documentsByDocumentId[$analysis['documentId']] ?? null;
            $date = isset($doc['issuedDate']) ? (new \DateTime($doc['issuedDate']))->format('d/m/Y') : null;
            $isLatest = $tabs === [];

            $tabs[] = [
                'id'        => $isLatest ? 'latest' : 'analysis-' . $analysis['id'],
                'label'     => $isLatest ? 'Latest' : ($date ?? 'Unknown date'),
                'date'      => $date,
                'status'    => $this->mapStatus($caseworkerStamp),
                'statusTag' => $this->mapStatusTagClass($caseworkerStamp),
            ];
        }

        return $tabs;
    }

    protected function mapStatus(?string $caseworkerStamp): string
    {
        return match ($caseworkerStamp) {
            'APPROVED' => 'Approved',
            'REJECTED' => 'Rejected',
            'PENDING'  => 'Pending',
            default    => 'Unknown',
        };
    }

    protected function mapStatusTagClass(?string $caseworkerStamp): string
    {
        return match ($caseworkerStamp) {
            'APPROVED' => 'govuk-tag--green',
            'REJECTED' => 'govuk-tag--red',
            'PENDING'  => 'govuk-tag--grey',
            default    => 'govuk-tag--grey',
        };
    }

    protected function getSuccessfulAnalyses(): array
    {
        $response = $this->handleQuery(
            DocumentAnalysisList::create([
                'application' => $this->getIdentifier(),
                'status' => 'SUCCESS',
            ])
        );

        if (!$response->isOk()) {
            return [];
        }

        $analyses = $response->getResult()['analyses'] ?? [];

        // "Latest" means the most recently completed successful analysis.
        usort($analyses, static fn($a, $b) => strcmp($b['completedAt'] ?? '', $a['completedAt'] ?? ''));

        return $analyses;
    }
}
