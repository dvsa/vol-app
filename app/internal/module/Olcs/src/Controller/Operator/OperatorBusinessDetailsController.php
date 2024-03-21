<?php

/**
 * Operator Business Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Controller\Operator;

use Common\Controller\Traits\CompanySearch;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Operator\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Operator\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Operator\BusinessDetails as BusinessDetailsDto;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\OperatorBusinessDetails as Mapper;
use Olcs\Service\Data\Licence;

/**
 * Operator Business Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorBusinessDetailsController extends OperatorController implements LeftViewProvider
{
    use CompanySearch;

    /**
     * @var string
     */
    protected $section = 'business_details';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_profile';

    protected $organisation = null;

    protected $mapperClass = Mapper::class;
    protected $createDtoClass = CreateDto::class;
    protected $updateDtoClass = UpdateDto::class;
    protected $queryDtoClass = BusinessDetailsDto::class;

    protected TranslationHelperService $translationHelper;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        DateHelperService $dateHelper,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        FlashMessengerHelperService $flashMessengerHelper,
        Licence $licenceDataService,
        QueryService $queryService,
        \Laminas\Navigation\Navigation $navigation,
        TranslationHelperService $translatorHelper
    ) {
        $this->translationHelper = $translatorHelper;
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dateHelper,
            $transferAnnotationBuilder,
            $commandService,
            $flashMessengerHelper,
            $licenceDataService,
            $queryService,
            $navigation
        );
    }

    /**
     * Index action
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        if ($this->isUnlicensed()) {
            return $this->redirectToRoute('operator-unlicensed/business-details', [], [], true);
        }

        $operator = $this->params()->fromRoute('organisation');

        if (!$operator) {
            $this->placeholder()->setPlaceholder('pageTitle', 'Create new operator');
        }

        $this->loadScripts(['operator-profile']);
        $post = $this->params()->fromPost();
        $validateAndSave = true;

        if ($this->getRequest()->isPost() && isset($post['custom'])) {
            return $this->saveConfirmForm();
        }

        if ($this->isButtonPressed('cancel')) {
            // user pressed cancel button in edit form
            if ($operator) {
                $this->flashMessenger()->addSuccessMessage('Your changes have been discarded');
                return $this->redirectToRoute('operator/business-details', ['organisation' => $operator]);
            } else {
                // user pressed cancel button in add form
                return $this->redirectToRoute('operators/operators-params');
            }
        }

        if ($this->getRequest()->isPost()) {
            // if this is post always take organisation type from parameters
            $operatorType = $post['operator-business-type']['type'];
        } elseif (!$operator) {
            // we are in add mode, this is default organisation type
            $operatorType = RefData::ORG_TYPE_REGISTERED_COMPANY;
            $this->pageTitle = 'internal-operator-create-new-operator';
        } else {
            // we are in edit mode, need to fetch original data
            $organisation = $this->getOrganisation($operator);
            $operatorType = $organisation['type']['id'];
        }

        $form = $this->makeFormAlterations($operatorType, $this->getForm('Operator'), $operator);
        // don't need validate form and save data if user just changed organisation's type
        if (isset($post['operator-business-type']['refresh'])) {
            // non-js version of form
            unset($post['operator-business-type']['refresh']);
            $validateAndSave = false;
            $newOperatorData = [
                'operator-business-type' => [
                    'type' => $operatorType
                ]
            ];
            $form->setData($newOperatorData);
        }

        /**
         * if we are in edit mode and just changed the business type or
         * this is not a post we need to populate form with
         * original values, otherwise we use POST values
         */
        if ($operator && (!$validateAndSave || !$this->getRequest()->isPost())) {
            $mapper = $this->mapperClass;
            $originalData = $mapper::mapFromResult($this->getOrganisation($operator));
            if (!$validateAndSave) {
                $originalData['operator-business-type']['type'] = $operatorType;
            }
            $form->setData($originalData);
        }

        $formHelper = $this->formHelper;

        // process company lookup
        if (isset($post['operator-details']['companyNumber']['submit_lookup_company'])) {
            $form->setData($post);
            $companyNumber = $post['operator-details']['companyNumber']['company_number'];
            $detailsFieldset = 'operator-details';
            $addressFieldset = 'registeredAddress';
            if ($this->isValidCompanyNumber($companyNumber)) {
                $form = $this->populateCompanyDetails($formHelper, $form, $detailsFieldset, $addressFieldset, $companyNumber);
            } else {
                $formHelper->setInvalidCompanyNumberErrors($form, $detailsFieldset);
            }
            $validateAndSave = false;
        }

        if ($this->getRequest()->isPost() && $validateAndSave) {
            $action = $operator ? 'edit' : 'add';
            $response = $this->saveForm($form, $action);

            if ($response !== null) {
                return $response;
            }
        }

        return $this->renderForm($form);
    }

    /**
     * get Left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    /**
     * render Form
     *
     * @param \Common\Form\Form $form      form
     * @param null|string       $pageTitle pageTitle
     *
     * @return ViewModel
     */
    protected function renderForm($form, $pageTitle = null)
    {
        $view = $this->getView(['form' => $form]);
        $view->setTemplate('pages/form');
        $this->placeholder()->setPlaceholder('contentTitle', 'Business details');

        return $this->renderView($view, $pageTitle);
    }

    /**
     * saveConfirmForm
     *
     * @return \Laminas\Http\Response
     */
    protected function saveConfirmForm()
    {
        $operator = $this->params()->fromRoute('organisation');
        $postData = (array)$this->getRequest()->getPost();

        $dtoData = json_decode($postData['custom'], true);
        $dtoData['confirm'] = true;

        $commandClass = $this->updateDtoClass;
        $dto = $commandClass::create($dtoData);

        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
            $this->flashMessengerHelper
                ->addSuccessMessage('The operator has been updated successfully');
        } else {
            $this->flashMessengerHelper->addUnknownError();
        }

        return $this->redirectToBusinessDetails($operator);
    }

    /**
     * Save form
     *
     * @param \Laminas\Form\Form $form        form
     * @param string             $action      action
     * @param string             $routePrefix routePrefix
     *
     * @return ViewModel|void
     */
    protected function saveForm($form, $action, $routePrefix = 'operator')
    {
        $postData = (array)$this->getRequest()->getPost();

        if ($action === 'edit' && isset($postData['custom'])) {
            $dtoData = json_decode($postData['custom'], true);
            $dtoData['confirm'] = true;

            $commandClass = $this->updateDtoClass;
            $dto = $commandClass::create($dtoData);
        } else {
            $form->setData($postData);

            if (!$form->isValid()) {
                return $this->renderForm($form);
            }

            $data = $form->getData();

            $mapper = $this->mapperClass;
            $params = $mapper::mapFromForm($data);

            if ($action == 'edit') {
                $commandClass = $this->updateDtoClass;
                $dto = $commandClass::create($params);
            } else {
                $commandClass = $this->createDtoClass;
                $dto = $commandClass::create($params);
            }
        }

        if ($action == 'edit') {
            $message = 'The operator has been updated successfully';
        } else {
            $message = 'The operator has been created successfully';
        }

        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
            $this->flashMessenger()->addSuccessMessage($message);
            $orgId = $response->getResult()['id']['organisation'];
            return $this->redirectToBusinessDetails($orgId, $routePrefix);
        }

        $messages = $response->getResult()['messages'];

        if (isset($messages['BUS_TYP_REQ_CONF'])) {
            $transitions = json_decode($messages['BUS_TYP_REQ_CONF']);

            $labels = [];

            $translation = $this->translationHelper;

            foreach ($transitions as $transition) {
                $labels[] = $translation->translate($transition);
            }

            $label = $translation->translateReplace('BUS_TYP_REQ_CONF', [implode('', $labels)]);

            return $this->confirm($label, $this->getRequest()->isXmlHttpRequest(), json_encode($dto->getArrayCopy()));
        }

        if ($response->isClientError()) {
            $this->mapErrors($form, $response->getResult()['messages']);
        }
        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }
    }

    /**
     * mapErrors
     *
     * @param array $form   form
     * @param array $errors errors
     *
     * @return void
     */
    protected function mapErrors($form, array $errors)
    {
        $mapper = $this->mapperClass;
        $errors = $mapper::mapFromErrors($form, $errors);
        if (!empty($errors)) {
            $fm = $this->flashMessengerHelper;
            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }
    }

    /**
     * Make form alterations
     *
     * @param string             $businessType businessType
     * @param \Laminas\Form\Form $form         form
     * @param int                $operatorId   operatorId
     *
     * @return \Laminas\Form\Form
     */
    private function makeFormAlterations($businessType, $form, $operatorId)
    {
        $formHelper = $this->formHelper;
        switch ($businessType) {
            case RefData::ORG_TYPE_REGISTERED_COMPANY:
            case RefData::ORG_TYPE_LLP:
                $formHelper->remove($form, 'operator-details->firstName');
                $formHelper->remove($form, 'operator-details->lastName');
                $formHelper->remove($form, 'operator-details->personId');
                break;
            case RefData::ORG_TYPE_SOLE_TRADER:
                $formHelper->remove($form, 'operator-details->companyNumber');
                $formHelper->remove($form, 'operator-details->name');
                $formHelper->remove($form, 'registeredAddress');
                break;
            case RefData::ORG_TYPE_PARTNERSHIP:
            case RefData::ORG_TYPE_OTHER:
                $formHelper->remove($form, 'operator-details->firstName');
                $formHelper->remove($form, 'operator-details->lastName');
                $formHelper->remove($form, 'operator-details->personId');
                $formHelper->remove($form, 'registeredAddress');
                $formHelper->remove($form, 'operator-details->companyNumber');
                break;
            case RefData::ORG_TYPE_IRFO:
                $formHelper->remove($form, 'operator-details->companyNumber');
                $formHelper->remove($form, 'operator-details->natureOfBusiness');
                $formHelper->remove($form, 'operator-details->information');
                $formHelper->remove($form, 'operator-details->firstName');
                $formHelper->remove($form, 'operator-details->lastName');
                $formHelper->remove($form, 'operator-details->personId');
                $formHelper->remove($form, 'operator-details->isIrfo');
                $formHelper->remove($form, 'registeredAddress');
                break;
        }
        if (!$operatorId) {
            $formHelper->remove($form, 'operator-id');
        } else {
            $form->get('operator-id')->get('operator-id')->setValue($operatorId);
        }

        return $form;
    }

    /**
     * get method Organisation
     *
     * @param int $organisationId organisationId
     *
     * @return array| null
     */
    protected function getOrganisation($organisationId)
    {
        if (!$this->organisation) {
            $query = $this->queryDtoClass;
            $response = $this->handleQuery($query::create(['id' => $organisationId]));

            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');
                return $this->notFoundAction();
            }
            $this->organisation = $response->getResult();
        }
        return $this->organisation;
    }

    /**
     * redirectToBusinessDetails
     *
     * @param int    $orgId       orgId
     * @param string $routePrefix routePrefix
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToBusinessDetails($orgId, $routePrefix = 'operator')
    {
        return $this->redirect()->toRouteAjax($routePrefix . '/business-details', ['organisation' => $orgId]);
    }
}
