<?php

namespace Common\Controller\Lva;

use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Shared logic between People controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractPeopleController extends AbstractController
{
    use Traits\CrudTableTrait;

    /**
     * Needed by the Crud Table Trait
     */
    protected $section = 'people';

    protected string $baseRoute = 'lva-%s/people';


    protected FormHelperService $formHelper;

    protected FlashMessengerHelperService $flashMessengerHelper;

    /**
     * @param $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected VariationLvaService $variationLvaService,
        protected GuidanceHelperService $guidanceHelper,
        protected $lvaAdapter,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index action
     *
     * @return array|\Common\View\Model\Section|\Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        try {
            $this->lvaAdapter->loadPeopleData($this->lva, $this->getIdentifier());
        } catch (\RuntimeException) {
            return $this->notFoundAction();
        }

        if ($this->location === self::LOC_EXTERNAL) {
            $this->addGuidanceMessage();
        }

        if ($this->lvaAdapter->isSoleTrader()) {
            return $this->handleSoleTrader();
        }

        return $this->handleNonSoleTrader();
    }

    /**
     * Handle all except sole trader
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    private function handleNonSoleTrader()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $adapter = $this->lvaAdapter;

        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm(
                ['canModify' => $adapter->canModify(), 'isPartnership' => $adapter->isPartnership()]
            );

        $table = $this->lvaAdapter->createTable();

        $form->get('table')
            ->get('table')
            ->setTable($table);

        $form->get('table')->get('rows')->setValue(count($table->getRows()));

        $this->alterForm($form, $table, $this->lvaAdapter->getOrganisationType());

        $this->lvaAdapter->alterFormForOrganisation($form, $table);

        if ($request->isPost()) {
            $postData = (array)$request->getPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $crudAction = $this->getCrudAction([$postData['table']]);

                if ($crudAction !== null) {
                    return $this->handleCrudAction($crudAction);
                }

                $this->updateCompletion();

                return $this->completeSection('people');
            }
        }

        $this->scriptFactory->loadFiles(['lva-crud-delta', 'more-actions']);

        $variables = [
            'title' => $this->getPageTitle($this->lvaAdapter->getOrganisationType()),
        ];
        return $this->render('people', $form, $variables);
    }

    /**
     * Handle indexAction if a sole trader
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    private function handleSoleTrader()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->lvaAdapter;

        /** @var array $personData */
        $personData = $adapter->getFirstPersonData();

        $orgId = $adapter->getOrganisationId();

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array) $request->getPost();
        } elseif ($personData === false) {
            $data['data'] = [];
        } else {
            $data['data'] = $personData['person'];
            $data['data']['position'] = $personData['position'];
        }

        $params = [
            'location' => $this->location,
            'canModify' => $adapter->canModify(),
            'orgType' => $adapter->getOrganisationType()
        ];

        if ($this->location === self::LOC_INTERNAL && $personData !== false) {
            $personId = $personData['person']['id'] ?? null;

            $params['disqualifyUrl'] = $this->url()->fromRoute(
                'operator/disqualify_person',
                ['organisation' => $orgId, 'person' => $personId]
            );
            $params['isDisqualified'] = $this->isPersonDisqualified($personData);
            $params['personId'] = $personId;
        }

        /** @var \Laminas\Form\FormInterface $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-sole_trader')
            ->getForm($params);

        $form->setData($data);

        $this->alterCrudForm($form, 'edit', $adapter->getOrganisation());

        if ($request->isPost() && $form->isValid()) {
            $data = $this->formatCrudDataForSave($form->getData());

            if ($form->getAttribute('locked') !== true) {
                $this->savePerson($data);
                $this->postSaveCommands();
            } else {
                $this->updateCompletion();
            }

            return $this->completeSection('people');
        }

        return $this->render('person', $form);
    }

    /**
     * post save commands
     *
     * @return void
     */
    protected function postSaveCommands()
    {
        $this->updateCompletion();

        //  update organisation name
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->lvaAdapter;
        if (!$adapter->isSoleTrader() && !$adapter->isPartnership()) {
            return;
        }

        $this->handleCommand(
            TransferCmd\Organisation\GenerateName::create(
                [
                    'organisation' => $adapter->getOrganisationId(),
                    'application' => ($this->lva === self::LVA_APP ? $this->getIdentifier() : null),
                ]
            )
        );
    }

    /**
     * Update completion
     *
     * @return void
     */
    protected function updateCompletion()
    {
        if ($this->lva !== self::LVA_LIC) {
            $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Application\UpdateCompletion::create(
                    ['id' => $this->getIdentifier(), 'section' => 'people']
                )
            );
        }
    }

    /**
     * save person
     *
     * @param array $data data
     */
    private function savePerson($data): void
    {
        /* @var Adapters\AbstractPeopleAdapter $adapter */
        $adapter = $this->lvaAdapter;
        if (empty($data['id'])) {
            $adapter->create($data);
        } else {
            $adapter->update($data);
        }
    }

    /**
     * Get the page title
     *
     * @param string $organisationTypeId Organisation type refdata ID
     *
     * @return string
     */
    private function getPageTitle($organisationTypeId)
    {
        $pageTitle = 'selfserve-app-subSection-your-business-people-tableHeader';

        match ($organisationTypeId) {
            RefData::ORG_TYPE_REGISTERED_COMPANY => $pageTitle .= 'Directors',
            RefData::ORG_TYPE_LLP => $pageTitle .= 'PartnersMembers',
            RefData::ORG_TYPE_PARTNERSHIP => $pageTitle .= 'Partners',
            RefData::ORG_TYPE_OTHER => $pageTitle .= 'People',
            default => $pageTitle,
        };

        return $pageTitle;
    }

    /**
     * Alter form based on company type
     *
     * @param Form                               $form               form
     * @param \Common\Service\Table\TableBuilder $table              table builder
     * @param int                                $organisationTypeId organisation id
     */
    private function alterForm($form, \Common\Service\Table\TableBuilder $table, $organisationTypeId): void
    {
        $this->alterFormForLva($form);

        // if not on internal then remove the disqual column
        if ($this->location !== self::LOC_INTERNAL) {
            $table->removeColumn('disqual');
        }

        // a separate if saves repeating this three times in the switch...
        if ($organisationTypeId !== RefData::ORG_TYPE_OTHER) {
            $table->removeColumn('position');
        }
    }

    /**
     * add guidance message
     */
    private function addGuidanceMessage(): void
    {
        $guidanceLabel = 'selfserve-app-subSection-your-business-people-guidance';
        switch ($this->lvaAdapter->getOrganisationType()) {
            case RefData::ORG_TYPE_REGISTERED_COMPANY:
                $guidanceLabel .= 'LC';
                break;
            case RefData::ORG_TYPE_LLP:
                $guidanceLabel .= 'LLP';
                break;
            case RefData::ORG_TYPE_PARTNERSHIP:
                $guidanceLabel .= 'P';
                break;
            case RefData::ORG_TYPE_OTHER:
                $guidanceLabel .= 'O';
                break;
            default:
                $guidanceLabel = null;
        }

        $additionalGuidanceLabel = null;
        if (
            $this->lva === self::LVA_VAR
            && $this->lvaAdapter->hasMoreThanOneValidCurtailedOrSuspendedLicences()
        ) {
            $additionalGuidanceLabel = 'selfserve-app-subSection-your-business-people-guidanceAdditional';
        }

        if ($this->lvaAdapter->canModify()) {
            if ($guidanceLabel !== null) {
                $this->guidanceHelper->append($guidanceLabel);
            }

            if ($additionalGuidanceLabel !== null) {
                $this->guidanceHelper->append($additionalGuidanceLabel);
            }
        } elseif (
            $this->lva === self::LVA_LIC
            &&
            (
            ($this->lvaAdapter->isOrganisationLimited() &&
                $this->lvaAdapter->getLicenceType() !== \Common\RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) ||
            $this->lvaAdapter->isOrganisationOther()
            )
        ) {
            $this->variationLvaService->addVariationMessage($this->getLicenceId(), 'people');
        } else {
            $this->guidanceHelper->append(
                'selfserve-app-subSection-your-business-people-guidance-disabled'
            );
        }
    }

    /**
     * alter crud form
     *
     * @param Form   $form    form
     * @param string $mode    mode
     * @param array  $orgData organisation data
     */
    private function alterCrudForm($form, $mode, $orgData): void
    {
        if ($mode !== 'add') {
            $form->get('form-actions')->remove('addAnother');
        }

        $personData = $this->lvaAdapter->getFirstPersonData();
        $personId = $personData['person']['id'] ?? null;
        // if not internal OR no  person OR already disqualified then hide the disqualify button

        //  allow for internal user do not specify DoB
        if ($this->location === self::LOC_INTERNAL) {
            /** @var \Laminas\Form\Element $elm */
            $elm = $form->get('data')->get('birthDate');
            $elm->setOption('label-suffix', '(optional)');

            /** @var \Laminas\InputFilter\Input $birthDateInputFltr */
            $birthDateInputFltr = $form->getInputFilter()->get('data')->get('birthDate');

            $birthDateInputFltr
                ->setAllowEmpty(true)
                ->setContinueIfEmpty(true);
        }

        if (
            $this->location !== self::LOC_INTERNAL ||
            empty($personId) ||
            $this->isPersonDisqualified($personData) ||
            !$this->lvaAdapter->isSoleTrader()
        ) {
            $this->formHelper->remove($form, 'form-actions->disqualify');
        } else {
            $form->get('form-actions')->get('disqualify')->setValue(
                $this->url()->fromRoute(
                    'operator/disqualify_person',
                    ['organisation' => $this->lvaAdapter->getOrganisationId(), 'person' => $personId]
                )
            );
        }

        if ($orgData['type']['id'] !== RefData::ORG_TYPE_OTHER) {
            // otherwise we're not interested in position at all, bin it off
            $this->formHelper
                ->remove($form, 'data->position');
        }
    }

    /**
     * Add person action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function addAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->lvaAdapter;
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());
        if (!$adapter->canModify()) {
            return $this->redirectWithoutPermission();
        }

        return $this->addOrEdit('add');
    }

    /**
     * Edit person action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function editAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->lvaAdapter;
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());
        return $this->addOrEdit('edit');
    }

    /**
     * Helper method as both add and edit pretty
     * much do the same thing
     *
     * @param string $mode mode
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    private function addOrEdit($mode)
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->lvaAdapter;
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $data = [];
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $personId = (int) $this->params('child_id');
            $personData = $adapter->getPersonData($personId);

            if ($personData) {
                $data['data'] = $personData['person'];
                $data['data']['position'] = $personData['position'];
            }
        }

        /** @var \Common\Form\Form $form */
        $form = $this->formHelper
            ->createFormWithRequest('Lva\Person', $request);

        $this->alterCrudForm($form, $mode, $adapter->getOrganisation());

        $adapter->alterAddOrEditFormForOrganisation($form);

        $form->setData($data);

        if ($request->isPost() && $form->isValid()) {
            $data = $this->formatCrudDataForSave($form->getData());

            $this->savePerson($data);
            $this->postSaveCommands();

            return $this->handlePostSave(null, false);
        }

        return $this->render($mode . '_people', $form);
    }

    /**
     * Format data from CRUD form
     *
     * @param array $data data
     *
     * @return array
     */
    private function formatCrudDataForSave($data)
    {
        return array_filter(
            $data['data'],
            static fn($v) => $v !== null
        );
    }

    /**
     * Mechanism to *actually* delete a person, invoked by the
     * underlying delete action
     *
     * @return null|\Laminas\Http\Response
     */
    protected function delete()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->lvaAdapter;

        $adapter->loadPeopleData($this->lva, $this->getIdentifier());
        if (!$adapter->canModify()) {
            return $this->redirectWithoutPermission();
        }

        $adapter->delete(
            explode(',', $this->params('child_id'))
        );

        $this->postSaveCommands();

        return null;
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-person';
    }

    /**
     * Redirect users who don't have permission
     *
     * @return \Laminas\Http\Response
     */
    private function redirectWithoutPermission()
    {
        $this->addErrorMessage('cannot-perform-action');
        return $this->redirect()->toRouteAjax(
            null,
            [$this->getIdentifierIndex() => $this->getIdentifier()]
        );
    }

    /**
     * Restore action
     *
     * @return \Laminas\Http\Response
     */
    public function restoreAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->lvaAdapter;
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());

        $id = $this->params('child_id');
        $ids = explode(',', $id);
        $adapter->restore($ids);

        return $this->redirect()->toRouteAjax(
            $this->getBaseRoute(),
            [$this->getIdentifierIndex() => $this->getIdentifier()]
        );
    }

    /**
     * Is the person in the personData disqualified
     *
     * @param array $personData array of person data
     *
     * @return boolean
     */
    protected function isPersonDisqualified($personData)
    {
        if (isset($personData['person']['disqualificationStatus'])) {
            return $personData['person']['disqualificationStatus'] !== 'None';
        }

        return false;
    }
}
