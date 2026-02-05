<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\Exception\ResourceNotFoundException;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Common\Util\Escape;
use Common\View\Helper\PersonName;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVariation;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see \OlcsTest\Controller\Lva\Licence\PeopleControllerTest
 * @see PeopleControllerFactory
 */
class PeopleController extends Lva\AbstractPeopleController
{
    use LicenceControllerTrait;

    /**
     * @var string
     */
    protected $lva = 'licence';

    /**
     * @var string
     */
    protected string $location = 'external';

    /**
     * @var string
     */
    protected $section = 'people';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param VariationLvaService $variationLvaService
     * @param GuidanceHelperService $guidanceHelper
     * @param TranslationHelperService $translationHelper
     * @param LicencePeopleAdapter $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        VariationLvaService $variationLvaService,
        GuidanceHelperService $guidanceHelper,
        protected TranslationHelperService $translationHelper,
        LicencePeopleAdapter $lvaAdapter,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $variationLvaService,
            $guidanceHelper,
            $lvaAdapter,
            $flashMessengerHelper
        );
    }

    /**
     * Prevent default licence actions
     *
     * @param Form  $form Form
     * @param array $data Api/Form Data
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
    }

    /**
     * Disallow adding (uses director change variations for add instead)
     *
     * @return Response
     */
    #[\Override]
    public function addAction()
    {
        return $this->redirectToIndexWithPermissionError();
    }

    /**
     * Disallow deleting
     */
    public function deleteAction()
    {
        $licencePeopleAdapter = $this->getLicencePeopleAdapter();
        $licencePeopleAdapter->loadPeopleData($this->lva, $this->getIdentifier());
        if ($licencePeopleAdapter->isExceptionalOrganisation()) {
            return $this->redirectToIndexWithPermissionError();
        }
        return parent::deleteAction();
    }

    /**
     * Handles a request from a user to view the page to edit a person.
     *
     * @return mixed
     * @throws ResourceNotFoundException
     */
    #[\Override]
    public function editAction()
    {
        $response = parent::editAction();

        if ($response instanceof ViewModel) {
            $personId = $this->getEvent()->getRouteMatch()->getParam('child_id');
            $personData = $this->lvaAdapter->getPersonData($personId);
            if (false === $personData) {
                throw new ResourceNotFoundException();
            }
            $view = array_values($response->getChildren())[0];
            $translatedTitle = $this->translationHelper->translate($view->getVariable('title'));
            $fullName = (new PersonName())->__invoke($personData['person']);
            $view->setVariable('title', sprintf($translatedTitle, Escape::html($fullName)));
        }

        return $response;
    }

    /**
     * Intercept the 'Add' POST action on index and create (and redirect to) the director change variation wizard
     *
     * @param array  $data             Data
     * @param array  $rowsNotRequired  Action
     * @param string $childIdParamName Child route identifier
     * @param string $route            Route
     *
     * @return Response
     */
    protected function handleCrudAction(
        $data,
        $rowsNotRequired = ['add'],
        $childIdParamName = 'child_id',
        $route = null
    ) {
        if (!isset($data['action']) or $data['action'] !== 'Add') {
            return parent::handleCrudAction($data, $rowsNotRequired, $childIdParamName, $route);
        }

        return $this->redirectToIndexIfNonPost()
            ?: $this->createNewDirectorChangeVariation();
    }

    /**
     * Redirect to index page if this is not a POST request
     *
     * @return null|Response
     */
    private function redirectToIndexIfNonPost()
    {
        /** @var Request $request */
        $request = $this->request;
        return $request->isPost() ? null : $this->redirectToIndex();
    }

    /**
     * Create a new Director Change Variation and redirect to the first page of the wizard
     *
     * @return Response
     */
    private function createNewDirectorChangeVariation()
    {
        $licencePeopleAdapter = $this->getLicencePeopleAdapter();
        $licencePeopleAdapter->loadPeopleData($this->lva, $this->getIdentifier());
        if ($licencePeopleAdapter->isExceptionalOrganisation() !== false) {
            return $this->redirectToIndexWithPermissionError();
        }

        $variationResult = $this->handleCommand(
            CreateVariation::create(
                [
                    'id' => $id = $this->getLicenceId(),
                    'variationType' => RefData::VARIATION_TYPE_DIRECTOR_CHANGE
                ]
            )
        );

        $variationId = $variationResult->getResult()['id']['application'];

        return $this->redirect()->toUrl(
            $this->url()->fromRoute('lva-director_change/people', ['application' => $variationId])
        );
    }


    /**
     * Redirect to the people index and display a permission flash message
     *
     * @return Response
     * @psalm-suppress UndefinedDocblockClass
     */
    private function redirectToIndexWithPermissionError()
    {
        $this->addErrorMessage('cannot-perform-action');
        return $this->redirectToIndex();
    }

    /**
     * @return Response
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'lva-' . $this->lva . '/' . $this->section,
            [$this->getIdentifierIndex() => $this->getLicenceId()]
        );
    }

    /**
     * Get LicencePeopleAdapter
     *
     * @return LicencePeopleAdapter
     */
    private function getLicencePeopleAdapter()
    {
        /** @var LicencePeopleAdapter $adapter */
        $adapter = $this->lvaAdapter;
        return $adapter;
    }
}
