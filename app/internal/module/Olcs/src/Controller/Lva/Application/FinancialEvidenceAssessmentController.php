<?php

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList;
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
        protected RestrictionHelperService $restrictionHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    public function indexAction()
    {
        $view = new ViewModel([
            'title' => 'lva.section.title.financial_evidence_assessment',
            'tabs'  => $this->getTabs(),
        ]);
        $view->setTemplate('sections/lva/financial-evidence-assessment');

        return $this->render($view);
    }

    /**
     * One tab per analysed document, most recent first. First tab is always
     * labelled "Latest"; the rest are labelled by their received date.
     *
     * @return array<int, array{id: string, label: string, date: ?string, status: ?string}>
     */
    protected function getTabs(): array
    {
        $analyses = $this->getAnalyses();

        $tabs = [];

        foreach ($analyses as $index => $analysis) {
            $tabs[] = [
                'id'     => 'analysis-' . $analysis['id'],
                'label'  => $index === 0 ? 'Latest' : ($analysis['completedAt'] ?? 'Unknown date'),
                'date'   => $analysis['completedAt'],
                'status' => $this->mapStatus($analysis['status']),
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
            default   => 'Pending',
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

        $analyses = $response->getResult()['analyses'];

        usort($analyses, static fn($a, $b) => strcmp($b['completedAt'] ?? '', $a['completedAt'] ?? ''));

        return $analyses;
    }
}
