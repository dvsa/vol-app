<?php

namespace Common\Controller\Lva;

use Common\Controller\Traits\GenericUpload;
use Common\Exception\ResourceConflictException;
use Common\RefData;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Table\TableBuilder;
use Common\Util;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Exception;
use Laminas\Mvc\MvcEvent;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Lva Abstract Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @method \Common\Service\Cqrs\Response handleQuery(\Dvsa\Olcs\Transfer\Query\QueryInterface $query)
 * @method \Common\Service\Cqrs\Response handleCommand(\Dvsa\Olcs\Transfer\Command\CommandInterface $query)
 * @method \Common\Service\Cqrs\Response handleCancelRedirect($lvaId)
 * @method \Laminas\Http\Response handlePostSave($prefix = null)
 * @method \Common\Controller\Plugin\Redirect redirect()
 * @method boolean isGranted(string $permission)
 * @method \Common\Controller\Plugin\CurrentUser currentUser()
 * @method \Laminas\Http\Response completeSection($section, $prg = [])
 * @method TableBuilder table()
 *
 * @see   \Olcs\Controller\Lva\Traits\ApplicationControllerTrait::render
 * @method \Common\View\Model\Section render($titleSuffix, Form $form = null, $variables = [])
 */
abstract class AbstractController extends AbstractActionController
{
    use Util\FlashMessengerTrait;
    use GenericUpload;

    public const LVA_LIC = 'licence';

    public const LVA_APP = 'application';

    public const LVA_VAR = 'variation';

    public const LOC_INTERNAL = 'internal';

    public const LOC_EXTERNAL = 'external';

    public const FLASH_MESSENGER_CREATED_PERSON_NAMESPACE = 'createPerson';

    /**
     * Internal/External
     */
    protected string $location;

    /**
     * Licence/Variation/Application
     *
     * @var string
     */
    protected $lva;

    protected string $baseRoute;

    /**
     * Current messages
     */
    protected array $currentMessages = [
        'default' => [],
        'error' => [],
        'info' => [],
        'warning' => [],
        'success' => []
    ];

    protected array $defaultBundles = [
        'licence' => Licence::class,
        'variation' => Application::class,
        'application' => Application::class
    ];

    public function __construct(protected NiTextTranslation $niTextTranslationUtil, protected AuthorizationService $authService)
    {
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
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
        }

        $this->maybeTranslateForNi();
        $action = $routeMatch->getParam('action', 'not-found');
        $method = static::getMethodFromAction($action);
        if (!method_exists($this, $method)) {
            $method = 'notFoundAction';
        }

        if ($routeMatch->getParam('skipPreDispatch', false) || ($actionResponse = $this->preDispatch()) === null) {
            try {
                $actionResponse = $this->$method();
            } catch (ResourceConflictException) {
                $this->addErrorMessage('version-conflict-message');
                $actionResponse = $this->reload();
            }
        }

        $e->setResult($actionResponse);
        return $actionResponse;
    }

    /**
     * May be Translate For Ni
     *
     * @return void
     */
    protected function maybeTranslateForNi()
    {
        if ($this->lva !== null && $this->getIdentifier() !== null) {
            $tolData = $this->getTypeOfLicenceData();
            $this->niTextTranslationUtil->setLocaleForNiFlag($tolData['niFlag']);
        }
    }

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
    }

    /**
     * Check if a button is pressed
     *
     * @param string $button Button id
     * @param array  $data   Form Data
     *
     * @return bool
     */
    protected function isButtonPressed($button, $data = [])
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        }

        return isset($data['form-actions'][$button]);
    }

    /**
     * Get accessible sections
     *
     * @param bool $keysOnly Define if you only want to return keys of array
     *
     * @return array
     */
    protected function getAccessibleSections($keysOnly = true)
    {
        $data = $this->fetchDataForLva();

        $sections = $data['sections'];

        if ($keysOnly) {
            return array_keys($sections);
        }

        return $sections;
    }

    /**
     * Fetch Data for Lva
     *
     * @NOTE This is a new method to load the generic LVA bundle (which is cached)
     *
     * @return array|mixed
     */
    protected function fetchDataForLva()
    {
        $dtoClass = $this->defaultBundles[$this->lva];

        $response = $this->handleQuery($dtoClass::create(['id' => $this->getIdentifier()]));

        return $response->getResult();
    }

    /**
     * Get licence type information
     *
     * @NOTE migrated
     *
     * @return array
     */
    protected function getTypeOfLicenceData()
    {
        $data = $this->fetchDataForLva();

        return [
            'version' => $data['version'],
            'niFlag' => $data['niFlag'],
            'licenceType' => $data['licenceType']['id'] ?? null,
            'goodsOrPsv' => $data['goodsOrPsv']['id'] ?? null
        ];
    }

    /**
     * Wrapper method so we can extend this behaviour
     *
     * @param int $lvaId LVA identifier
     *
     * @return \Laminas\Http\Response
     */
    protected function goToOverviewAfterSave($lvaId = null)
    {
        return $this->goToOverview($lvaId);
    }

    /**
     * Go to overview page
     *
     * @param int $lvaId LVA identifier
     *
     * @return \Laminas\Http\Response
     */
    protected function goToOverview($lvaId = null)
    {
        if ($lvaId === null) {
            $lvaId = $this->getIdentifier();
        }

        return $this->redirect()->toRouteAjax('lva-' . $this->lva, [$this->getIdentifierIndex() => $lvaId]);
    }

    /**
     * Redirect to the next section
     *
     * @param string $currentSection Section
     *
     * @return \Laminas\Http\Response
     */
    protected function goToNextSection($currentSection)
    {
        $sections = $this->getAccessibleSections();

        $index = array_search($currentSection, $sections, false);

        // If there is no next section
        if (!isset($sections[$index + 1])) {
            return $this->goToOverview($this->getApplicationId());
        }
        return $this->redirect()
            ->toRoute(
                'lva-' . $this->lva . '/' . $sections[$index + 1],
                [$this->getIdentifierIndex() => $this->getApplicationId()]
            );
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId LVA Identifier
     *
     * @return \Common\Service\Cqrs\Response|null|\Laminas\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        if (!$this->isButtonPressed('cancel')) {
            return null;
        }

        // If we are on a sub-section, we need to go back to the section
        if ($this->params('action') !== 'index') {
            return $this->redirect()->toRoute(
                $this->getBaseRoute(),
                [$this->getIdentifierIndex() => $lvaId],
                ['query' => $this->getRequest()->getQuery()->toArray()]
            );
        }

        return $this->handleCancelRedirect($lvaId);
    }

    /**
     * No-op but extended
     *
     * @param Form  $form Form
     * @param array $data Form Data
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
    }

    /**
     * Reload the current page
     *
     * @return \Laminas\Http\Response
     */
    protected function reload()
    {
        return $this->redirect()->refreshAjax();
    }

    /**
     * Attach messages to display in the current response
     *
     * @return void
     * @deprecated  is not used anythere
     */
    protected function attachCurrentMessages()
    {
        foreach ($this->currentMessages as $namespace => $messages) {
            foreach ($messages as $message) {
                $this->addMessage($message, $namespace);
            }
        }
    }

    /**
     * Get Identifier
     *
     * @return mixed|\Laminas\Mvc\Controller\Plugin\Params
     */
    protected function getIdentifier()
    {
        return $this->params($this->getIdentifierIndex());
    }

    /**
     * Get Identifier Index
     *
     * @return string
     */
    protected function getIdentifierIndex()
    {
        if ($this->lva === self::LVA_LIC) {
            return 'licence';
        }

        return 'application';
    }

    /**
     * This method is overidden for applications
     *
     * @param int $applicationId Application Id
     *
     * @return int
     */
    protected function getLicenceId($applicationId = null)
    {
        return $this->getIdentifier();
    }

    /**
     * Is External
     */
    protected function isExternal(): bool
    {
        return $this->location === self::LOC_EXTERNAL;
    }

    /**
     * @deprecated Have left this in place for now as the number of controllers extending it made removal impractical
     * @see \Common\Rbac\Service\Permission for the preferred way to access this logic via dependency injection
     */
    protected function isInternalReadOnly(): bool
    {
        return (
            $this->authService->isGranted(RefData::PERMISSION_INTERNAL_USER)
            && !$this->authService->isGranted(RefData::PERMISSION_INTERNAL_EDIT)
        );
    }
}
