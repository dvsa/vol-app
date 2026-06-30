<?php

namespace Common\Controller\Continuation;

use Common\Controller\Lva\AbstractController;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\Get as GetContinuationDetail;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Mvc\Exception;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * AbstractContinuationController
 */
abstract class AbstractContinuationController extends AbstractController
{
    public const SUCCESS_CONTROLLER = 'ContinuationController/Success';

    public const LICENCE_OVERVIEW_ROUTE = 'lva-licence';

    public const PATH_SR = 1;

    public const PATH_CU = 2;

    public const PATH_NCU = 3;

    public const STEP_START = 'start';

    public const STEP_CHECKLIST = 'checklist';

    public const STEP_CU = 'cu';

    public const STEP_FINANCE = 'finance';

    public const STEP_DECLARATION = 'declaration';

    public const STEP_DEFAULT = 'default';

    /** @var string  */
    protected $layout = 'pages/continuation';

    /** @var string  */
    protected $simpleLayout = 'layouts/simple';

    /** @var array */
    protected $continuationData;

    /** @var array */
    protected $exclusions = [
        'printDeclaration' => [
            'controller' => 'ContinuationController/Declaration',
            'action' => 'print'
        ],
    ];

    protected $currentStep = self::STEP_DEFAULT;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormServiceManager $formServiceManager,
        protected TranslationHelperService $translationHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Get the ViewModel used for continuations
     *
     * @param string    $licNo     Licence number eg OB1234567
     * @param Form|null $form      Form to display on the page
     * @param array     $variables additional variables to view
     *
     * @return ViewModel
     */
    protected function getViewModel($licNo, Form $form = null, $variables = [])
    {
        $stepHeader = $this->getStepHeader($this->currentStep);

        $view = new ViewModel(
            array_merge(['licNo' => $licNo, 'form' => $form, 'stepHeader' => $stepHeader], $variables)
        );

        $view->setTemplate($this->layout);

        return $view;
    }

    /**
     * Get simple ViewModel used for printing
     *
     * @param array $variables additional variables to view
     *
     * @return ViewModel
     */
    protected function getSimpleViewModel($variables = [])
    {
        $view = new ViewModel($variables);

        $layout = new ViewModel();
        $layout->setTemplate($this->simpleLayout);
        $layout->setTerminal(true);
        $layout->addChild($view, 'content');

        $view->setTemplate($this->layout);

        return $layout;
    }

    /**
     * Get a form
     *
     * @param string $formServiceName form service name of the form to generate
     * @param array  $data            data to alter the form
     *
     * @return Form
     */
    protected function getForm($formServiceName, $data = [])
    {
        return $this->formServiceManager
            ->get($formServiceName)
            ->getForm($data);
    }

    /**
     * Get the continuation detail ID
     *
     * @return int
     */
    protected function getContinuationDetailId()
    {
        return (int)$this->params('continuationDetailId');
    }

    /**
     * Get continuation fee data
     *
     * @param bool $forceReload Force reload of data
     *
     * @return array
     */
    protected function getContinuationDetailData($forceReload = false)
    {
        if ($forceReload || $this->continuationData === null) {
            $response = $this->handleQuery(
                GetContinuationDetail::create(
                    ['id' => $this->getContinuationDetailId()]
                )
            );
            $this->continuationData = $response->getResult();
            if (!$response->isOk()) {
                $this->addErrorMessage('unknown-error');
            }
        }

        return $this->continuationData;
    }

    /**
     * Redirect to success page
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToSuccessPage()
    {
        return $this->redirect()->toRoute('continuation/success', [], [], true);
    }

    /**
     * Redirect to payment page
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToPaymentPage()
    {
        return $this->redirect()->toRoute('continuation/payment', [], [], true);
    }

    /**
     * Redirect to licence overview page
     *
     * @param int $licenceId licence Id
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToLicenceOverviewPage($licenceId)
    {
        return $this->redirect()->toRoute(self::LICENCE_OVERVIEW_ROUTE, ['licence' => $licenceId], [], true);
    }

    /**
     * Execute the request
     *
     * @param MvcEvent $e Event
     *
     * @return null|\Laminas\Http\Response
     */
    #[\Override]
    public function onDispatch(MvcEvent $e)
    {
        // If Internal user, then redirect rules do not apply
        if ($this->currentUser()->hasPermission(RefData::PERMISSION_INTERNAL_USER)) {
            return parent::onDispatch($e);
        }

        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
        }

        $data = $this->getContinuationDetailData();
        $status = $data['status']['id'] ?? null;
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');

        if ($controller !== self::SUCCESS_CONTROLLER) {
            if ($this->allowedToAccess($controller, $action)) {
                return parent::onDispatch($e);
            }

            if ($status === RefData::CONTINUATION_STATUS_COMPLETE && (int) $data['isDigital'] === 1) {
                return $this->redirectToSuccessPage();
            }

            if ($status === RefData::CONTINUATION_STATUS_GENERATED) {
                return parent::onDispatch($e);
            }
        } elseif ($status === RefData::CONTINUATION_STATUS_COMPLETE) {
            return parent::onDispatch($e);
        }

        return $this->redirectToLicenceOverviewPage($data['licence']['id']);
    }

    /**
     * Allowed to access
     *
     * @param string $controller controller
     * @param string $action     action
     *
     * @return bool
     */
    protected function allowedToAccess($controller, $action)
    {
        foreach ($this->exclusions as $exclusion) {
            if ($exclusion['controller'] !== $controller) {
                continue;
            }
            if ($exclusion['action'] !== $action) {
                continue;
            }
            return true;
        }

        return false;
    }

    /**
     * Get step header
     *
     * @param string $step step
     *
     * @return string
     */
    protected function getStepHeader($step = null)
    {
        if ($step === null || $step === self::STEP_DEFAULT) {
            return '';
        }

        $data = $this->getContinuationDetailData();
        $licenceType = $data['licence']['licenceType']['id'];
        $hasConditionsUndertakings =
            (
                isset($data['conditionsUndertakings']['licence'])
                && is_array($data['conditionsUndertakings']['licence'])
                && $data['conditionsUndertakings']['licence'] !== []
            )
            ||
            (
                isset($data['conditionsUndertakings']['operatingCentres'])
                && is_array($data['conditionsUndertakings']['operatingCentres'])
                && $data['conditionsUndertakings']['operatingCentres'] !== []
            );

        if ($licenceType === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $path = self::PATH_SR;
        } elseif ($hasConditionsUndertakings || $this->isPsvRestricted($data['licence'])) {
            $path = self::PATH_CU;
        } else {
            $path = self::PATH_NCU;
        }

        $stepDetails = $this->getStepDetails($path, $step);

        return $this->translationHelper->translateReplace(
            'continuations.step.header',
            [$stepDetails['current'],
            $stepDetails['total']]
        );
    }

    /**
     * Get step details
     *
     * @param int    $path path
     * @param string $step step
     *
     * @return int[]|string
     *
     * @psalm-return ''|array{current: int, total: 2|3|4}
     */
    protected function getStepDetails($path, $step)
    {
        $steps = [];

        // special restricted licence
        $steps[self::PATH_SR] = [
            self::STEP_START => ['current' => 1, 'total' => 2],
            self::STEP_CHECKLIST => ['current' => 1, 'total' => 2],
            self::STEP_DECLARATION => ['current' => 2, 'total' => 2],
        ];

        // other licence types with conditions and undertakings
        $steps[self::PATH_CU] = [
            self::STEP_START => ['current' => 1, 'total' => 4],
            self::STEP_CHECKLIST => ['current' => 1, 'total' => 4],
            self::STEP_CU => ['current' => 2, 'total' => 4],
            self::STEP_FINANCE => ['current' => 3, 'total' => 4],
            self::STEP_DECLARATION => ['current' => 4, 'total' => 4],
        ];

        // other licence types with no conditions and undertakings
        $steps[self::PATH_NCU] = [
            self::STEP_START => ['current' => 1, 'total' => 3],
            self::STEP_CHECKLIST => ['current' => 1, 'total' => 3],
            self::STEP_FINANCE => ['current' => 2, 'total' => 3],
            self::STEP_DECLARATION => ['current' => 3, 'total' => 3],
        ];

        return $steps[$path][$step] ?? '';
    }

    /**
     * @param $licence
     */
    protected function isPsvRestricted(array $licence): bool
    {
        return $licence['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV
            && $licence['licenceType']['id'] === RefData::LICENCE_TYPE_RESTRICTED;
    }
}
