<?php

namespace Olcs\Controller\Letter;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;

/**
 * Letter Generation Controller
 * Handles database-driven letter creation workflow
 */
class LetterGenerationController extends AbstractInternalController implements ToggleAwareInterface
{
    /**
     * Feature toggle configuration
     */
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::LETTERS_DATABASE_DRIVEN,
        ],
    ];

    /**
     * Inline scripts for form handling
     */
    protected $inlineScripts = [
        'createAction' => ['forms/letter-generation'],
    ];

    /**
     * Constructor
     */
    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelperService,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation
    ) {
        parent::__construct(
            $translationHelperService,
            $formHelperService,
            $flashMessengerHelperService,
            $navigation
        );
    }

    /**
     * Create letter action - main entry point
     *
     * Query parameters expected:
     * - licence: Licence ID (optional)
     * - application: Application ID (optional)
     * - busReg: Bus Registration ID (optional)
     * - transportManager: Transport Manager ID (optional)
     * - irhpApplication: IRHP Application ID (optional)
     * - irfoOrganisation: IRFO Organisation ID (optional)
     * - template: Template ID (required)
     * - returnUrl: URL to return to after completion (optional)
     *
     * @return ViewModel|Response
     */
    public function createAction()
    {
        // Get query parameters
        $queryParams = $this->getRequest()->getQuery()->toArray();

        // Validate required parameters
        if (!isset($queryParams['template'])) {
            $this->flashMessengerHelperService->addErrorMessage('Template ID is required');
            return $this->redirectToReturnUrl($queryParams);
        }

        $templateId = (int) $queryParams['template'];

        // Extract entity context from query params
        $entityContext = $this->extractEntityContext($queryParams);

        // TODO: Implementation in future phases
        // 1. Load letter template and metadata
        // 2. Build dynamic form based on letter type configuration
        // 3. Display letter choices form with sections/appendices/todos
        // 4. Handle form submission
        // 5. Generate letter document
        // 6. Redirect to finalise or return URL

        // For now, show placeholder with context information
        $view = new ViewModel([
            'templateId' => $templateId,
            'entityContext' => $entityContext,
            'queryParams' => $queryParams,
            'message' => 'Database-driven letter creation - Implementation in progress'
        ]);

        $view->setTemplate('pages/letter/create');

        return $view;
    }

    /**
     * Extract entity context from query parameters
     *
     * @param array $queryParams Query parameters
     * @return array Entity context with type and ID
     */
    protected function extractEntityContext(array $queryParams): array
    {
        $entityTypes = [
            'licence',
            'application',
            'busReg',
            'transportManager',
            'irhpApplication',
            'irfoOrganisation'
        ];

        foreach ($entityTypes as $type) {
            if (isset($queryParams[$type])) {
                return [
                    'type' => $type,
                    'id' => (int) $queryParams[$type]
                ];
            }
        }

        return [];
    }

    /**
     * Redirect back to source or default location
     *
     * @param array $queryParams Query parameters
     * @return Response
     */
    protected function redirectToReturnUrl(array $queryParams): Response
    {
        if (isset($queryParams['returnUrl'])) {
            return $this->redirect()->toUrl($queryParams['returnUrl']);
        }

        // Default fallback - redirect to appropriate documents page based on context
        $entityContext = $this->extractEntityContext($queryParams);

        if (!empty($entityContext['type'])) {
            return $this->redirectToDocumentsPage($entityContext);
        }

        // Ultimate fallback - dashboard
        return $this->redirect()->toRoute('dashboard');
    }

    /**
     * Redirect to appropriate documents page based on entity type
     *
     * @param array $entityContext Entity context
     * @return Response
     */
    protected function redirectToDocumentsPage(array $entityContext): Response
    {
        $documentRouteMap = [
            'licence' => ['route' => 'licence/documents', 'param' => 'licence'],
            'application' => ['route' => 'lva-application/documents', 'param' => 'application'],
            'busReg' => ['route' => 'licence/bus-docs', 'param' => 'busRegId'],
            'transportManager' => ['route' => 'transport-manager/documents', 'param' => 'transportManager'],
            'irhpApplication' => ['route' => 'licence/irhp-application-docs', 'param' => 'irhpAppId'],
            'irfoOrganisation' => ['route' => 'operator/documents', 'param' => 'organisation'],
        ];

        $type = $entityContext['type'];
        $id = $entityContext['id'];

        if (isset($documentRouteMap[$type])) {
            $config = $documentRouteMap[$type];
            return $this->redirect()->toRoute(
                $config['route'],
                [$config['param'] => $id, 'action' => 'index']
            );
        }

        return $this->redirect()->toRoute('dashboard');
    }
}
