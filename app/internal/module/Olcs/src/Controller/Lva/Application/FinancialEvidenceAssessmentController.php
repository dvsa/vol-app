<?php

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
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
     * Build the tab list: one guaranteed "Latest" tab, plus
     * any additional tabs sourced from data.
     *
     * @return array<int, array{id: string, label: string, template: string, variables: array}>
     */
    protected function getTabs(): array
    {
        $tabs = [
            [
                'id'        => 'latest',
                'label'     => 'Latest',
                'template'  => 'sections/lva/financial-evidence-assessment/tab-latest',
                'variables' => [],
            ],
        ];

        foreach ($this->getAdditionalTabsData() as $tabData) {
            $tabs[] = [
                'id'        => $tabData['id'],
                'label'     => $tabData['label'],
                'template'  => 'sections/lva/financial-evidence-assessment/tab-generic',
                'variables' => ['data' => $tabData],
            ];
        }

        return $tabs;
    }

    protected function getAdditionalTabsData(): array
    {
        // Stub for now — swap for a real query/repo call.
        return [];
    }
}
