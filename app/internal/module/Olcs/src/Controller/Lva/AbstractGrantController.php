<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\View\Model\Section;
use Dvsa\Olcs\Transfer\Command\Application\Grant as AppGrantCmd;
use Dvsa\Olcs\Transfer\Command\Variation\Grant as VarGrantCmd;
use Dvsa\Olcs\Transfer\Query\Application\Grant;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Element\Radio;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Olcs\Form\Model\Form\Grant as GrantApplicationForm;
use Olcs\Form\Model\Form\GrantAuthorityForm;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Internal Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractGrantController extends AbstractController
{
    protected $lva;
    protected string $location;
    protected $grantCommandMap = [
        'application' => AppGrantCmd::class,
        'variation' => VarGrantCmd::class
    ];

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected FormHelperService $formHelper;
    protected ScriptFactory $scriptFactory;
    protected TranslationHelperService $translationHelper;

    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormHelperService           $formHelper
     * @param ScriptFactory               $scriptFactory
     * @param TranslationHelperService    $translationHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormHelperService $formHelper,
        ScriptFactory $scriptFactory,
        TranslationHelperService $translationHelper
    ) {
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->formHelper = $formHelper;
        $this->scriptFactory = $scriptFactory;
        $this->translationHelper = $translationHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Handles requests from users to perform operations related to granting an application.
     *
     * @return ViewModel|Response
     */
    public function grantAction()
    {
        $id = $this->params('application');

        if ($this->isButtonPressed('cancel') || $this->isButtonPressed('overview')) {
            $this->flashMessengerHelper->addWarningMessage('application-not-granted');
            return $this->redirectToOverview($id);
        }
        $request = $this->getRequest();

        $formHelper = $this->formHelper;
        assert($formHelper instanceof FormHelperService, 'Expected instance of FormHelperService');
        $form = $formHelper->createFormWithRequest(GrantApplicationForm::class, $request);

        $grantData = $this->handleQuery(Grant::create(['id' => $id]))->getResult();

        if (!$grantData['canHaveInspectionRequest']) {
            $formHelper->remove($form, 'inspection-request-details');
            $formHelper->remove($form, 'inspection-request-confirm');

            if ($grantData['canGrant']) {
                $form->get('messages')->get('message')->setValue('confirm-grant-application');
            }
        }

        if (!$grantData['canGrant']) {
            $formHelper->remove($form, 'form-actions->grant');
            $this->addMessages($form, $grantData['reasons']);
            return $this->renderGrantApplicationForm($form);
        }

        if (! $request->isPost()) {
            $form = $formHelper->createFormWithRequest(GrantAuthorityForm::class, $request);
            return $this->renderGrantAuthorityForm($form, $grantData);
        }

        $postData = (array) $request->getPost();
        if (isset($postData['form-actions']['continue-to-grant'])) {
            $grantAuthorityForm = $formHelper->createFormWithRequest(GrantAuthorityForm::class, $request);
            $grantAuthorityForm->setData($postData);
            if (! $grantAuthorityForm->isValid()) {
                return $this->renderGrantAuthorityForm($grantAuthorityForm, $grantData);
            }

            $form->setData([GrantApplicationForm::FIELD_GRANT_AUTHORITY => $postData[GrantAuthorityForm::FIELD_GRANT_AUTHORITY]]);
            return $this->renderGrantApplicationForm($form);
        }

        $form->setData($postData);
        $dtoClass = $this->grantCommandMap[$this->lva];
        $dtoData = [
            'id' => $id,
            'grantAuthority' => $postData[GrantApplicationForm::FIELD_GRANT_AUTHORITY],
        ];

        if (isset($postData['inspection-request-confirm']['createInspectionRequest'])) {
            $value = $postData['inspection-request-confirm']['createInspectionRequest'];
            $dtoData['shouldCreateInspectionRequest'] = $value == 'Y' ? 'Y' : 'N';
        }

        if (isset($postData['inspection-request-grant-details']['dueDate'])) {
            $dtoData['dueDate'] = $postData['inspection-request-grant-details']['dueDate'];
        }

        if (isset($postData['inspection-request-grant-details']['caseworkerNotes'])) {
            $dtoData['notes'] = $postData['inspection-request-grant-details']['caseworkerNotes'];
        }

        $response = $this->handleCommand($dtoClass::create($dtoData));

        if ($response->isOk()) {
            $this->flashMessengerHelper
                ->addSuccessMessage('application-granted-successfully');

            return $this->redirectToOverview($id);
        }

        if ($response->isClientError()) {
            $this->mapErrors($form, $response->getResult()['messages']);
            return $this->renderGrantApplicationForm($form);
        }

        $this->flashMessengerHelper->addCurrentErrorMessage('unknown-error');

        return $this->renderGrantApplicationForm($form);
    }

    /**
     * mapErrors
     *
     * @param Form  $form   form
     * @param array $errors errors
     *
     * @return void
     */
    protected function mapErrors(Form $form, array $errors)
    {
        $formMessages = [];

        if (isset($errors['shouldCreateInspectionRequest'])) {
            foreach ($errors['shouldCreateInspectionRequest'] as $key => $message) {
                $formMessages['inspection-request-confirm']['createInspectionRequest'][] = $message;
            }

            unset($errors['shouldCreateInspectionRequest']);
        }

        if (isset($errors['dueDate'])) {
            foreach ($errors['dueDate'][0] as $key => $message) {
                $formMessages['inspection-request-grant-details']['dueDate'][] = $message;
            }

            unset($errors['dueDate']);
        }

        $fm = $this->flashMessengerHelper;

        if (isset($errors['oood'])) {
            $fm->addCurrentErrorMessage(array_keys($errors['oood'])[0]);
            unset($errors['oood']);
        }
        if (isset($errors['oord'])) {
            $fm->addCurrentErrorMessage(array_keys($errors['oord'])[0]);
            unset($errors['oord']);
        }
        if (isset($errors['s4'])) {
            $fm->addCurrentErrorMessage(array_keys($errors['s4'])[0]);
            unset($errors['s4']);
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }

    /**
     * Render a grant application form.
     *
     * @param  Form $form form
     * @return Section
     */
    protected function renderGrantApplicationForm($form): ViewModel
    {
        $message = $form->get('messages')->get('message')->getValue();
        if (empty($message)) {
            $form->remove('messages');
        }
        return $this->renderForm($form);
    }

    /**
     * Renders a form.
     *
     * @param  Form $form
     * @return ViewModel
     */
    protected function renderForm(Form $form): ViewModel
    {
        $form->get('form-actions')->remove('overview');
        $applicationId = $this->params('application');
        $this->scriptFactory->loadFiles(['forms/confirm-grant']);
        $variables = [
            'route' => 'lva-' . $this->lva,
            'routeParams' => ['application' => $applicationId],
        ];
        return $this->render('grant_application', $form, $variables);
    }

    /**
     * Renders a grant authority form.
     *
     * @param  Form  $form
     * @param  array $grantData
     * @return ViewModel
     */
    protected function renderGrantAuthorityForm(Form $form, array $grantData): ViewModel
    {
        $radio = $form->get(GrantAuthorityForm::FIELD_GRANT_AUTHORITY);
        assert($radio instanceof Radio);
        $valueOptions = $radio->getValueOptions();

        if ('Y' === ($grantData['niFlag'] ?? null)) {
            unset($valueOptions[RefData::GRANT_AUTHORITY_TC]);
        } else {
            unset($valueOptions[RefData::GRANT_AUTHORITY_TR]);
        }
        $radio->setValueOptions($valueOptions);
        return $this->renderForm($form);
    }

    /**
     * Add feedback messages as to why validation failed
     *
     * @todo improve the appearance of these messages
     *
     * @param \Common\Form\Form $form    form
     * @param array             $reasons reasons
     *
     * @return void
     */
    protected function addMessages($form, $reasons)
    {
        $messages = [];

        $translator = $this->translationHelper;

        foreach ($reasons as $reason => $info) {
            if (in_array($reason, ['application-grant-error-sections', 'variation-grant-error-sections'])) {
                $sections = [];
                foreach ($info as $section) {
                    $sections[] = $translator->translate('lva.section.title.' . $section);
                }

                $messages[] = $translator->translateReplace($reason, [implode(', ', $sections)]);
            } else {
                $messages[] = $translator->translate($reason);
            }
        }

        $form->get('messages')->get('message')->setValue(implode('<br>', $messages));
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId lvaId
     *
     * @return null|\Laminas\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        // no-op to avoid LVA predispatch magic kicking in
    }

    /**
     * Redirect to overview
     *
     * @param int $id id
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToOverview($id)
    {
        return $this->redirect()->toRouteAjax('lva-' . $this->lva, ['application' => $id]);
    }
}
