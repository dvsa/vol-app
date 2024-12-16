<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\FeatureToggle;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as RegisterDto;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceRegisteredAddress as LicenceByNumberDto;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Mapper\CreateAccountMapper;
use Olcs\Session\ConsultantRegistration;

/**
 * User Registration Controller
 */
class UserRegistrationController extends AbstractController
{
    public function __construct(
        NiTextTranslation                     $niTextTranslationUtil,
        AuthorizationService                  $authService,
        protected FormHelperService           $formHelper,
        protected ScriptFactory               $scriptFactory,
        protected TranslationHelperService    $translationHelper,
        protected UrlHelperService            $urlHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected CreateAccountMapper         $formatDataMapper,
        protected ConsultantRegistration      $consultantRegistrationSession
    )
    {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Temporary method to start the user registration flow based on Transport Consultant Role feature toggle.
     *
     * @return mixed
     */
    public function startAction()
    {
        if ($this->handleQuery(
            IsEnabledQry::create(['ids' => [FeatureToggle::TRANSPORT_CONSULTANT_ROLE]])
        )->getResult()['isEnabled']) {
            // If the feature toggle is enabled, start the TC journey in new controller
            return $this->forward()->dispatch(ConsultantRegistrationController::class, ['action' => 'add', 'params' => $this->params()->fromQuery()]);
        } else {
            // If disabled, start the normal add journey in this controller
            return $this->forward()->dispatch(static::class, ['action' => 'add']);
        }
    }

    /**
     * Method used for the registration form page
     *
     * @return ViewModel|\Laminas\Http\Response|null
     */
    public function addAction()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->formHelper
            ->createFormWithRequest('UserRegistration', $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToHome();
            }

            $postData = $this->formatDataMapper->formatPostData(
                $this->params()->fromPost()
            );

            $form->setData($postData);
            if ($form->isValid()) {
                return $this->processUserRegistration($form->getData());
            }
        }

        // register page
        $view = new ViewModel(
            [
                'form' => $this->alterForm($form)
            ]
        );
        $view->setTemplate('olcs/user-registration/index');
        $this->placeholder()->setPlaceholder('pageTitle', 'page.title.user-registration.add');

        $this->scriptFactory->loadFile('user-registration');

        return $view;
    }

    /**
     * Alter form
     *
     * @param Form $form Form from form helper
     *
     * @return Form
     */
    protected function alterForm(Form $form)
    {
        // inject link into terms agreed label
        $termsAgreed = $form->get('fields')->get('termsAgreed');

        $label = $this->translationHelper->translateReplace(
            $termsAgreed->getLabel(),
            [
                $this->urlHelper->fromRoute('terms-and-conditions')
            ]
        );

        $termsAgreed->setLabel($label);

        if ($this->consultantRegistrationSession->getOperatorAdmin() === false) {
            $form->get('fields')->get('licenceNumber')->setValue($this->consultantRegistrationSession->getExistingLicence())->setAttribute('type', 'hidden');
            $form->get('fields')->get('isLicenceHolder')->setValue('Y')->setAttribute('type', 'hidden');
            $form->get('fields')->get('isLicenceHolder')->setLabel('');
        }
        return $form;
    }

    /**
     * Generate content for user registration
     *
     * @param array $formData Form data
     * @param array $errors Errors from ZF Validation Chain
     *
     * @return ViewModel
     */
    private function generateContentForUserRegistration(array $formData = [], array $errors = [])
    {
        /** @var \Common\Form\Form $form */
        $form = $this->formHelper
            ->createFormWithRequest('UserRegistration', $this->getRequest());

        if (!empty($formData)) {
            $form->setData($formData);
        }

        if (!empty($errors)) {
            $form->setMessages(
                [
                    'fields' => $errors
                ]
            );
        }

        // register page
        $view = new ViewModel(
            [
                'form' => $this->alterForm($form)
            ]
        );
        $view->setTemplate('olcs/user-registration/index');

        $this->scriptFactory->loadFile('user-registration');

        return $view;
    }

    public function operatorConfirmAction(): ViewModel
    {
        $existingLicence = $this->consultantRegistrationSession->getExistingLicence();
        return $this->showLicence([
            'fields' => [
                'licenceNumber' => $existingLicence,
                'isLicenceHolder' => 'Y'
            ]
        ]);
    }

    /**
     * Process user registration form data
     *
     * @param array $formData Posted form data
     *
     * @return null|ViewModel
     */
    private function processUserRegistration($formData)
    {
        if ($this->isButtonPressed('postAccount')) {
            // create a user for an existing licence
            return $this->createUserWithLic($formData);
        } elseif ('Y' === $formData['fields']['isLicenceHolder']) {
            // show licence details to confirm an address
            return $this->showLicence($formData);
        } else {
            // create a user for a new org
            return $this->createUserWithOrg($formData);
        }
    }

    /**
     * Show licence
     *
     * @param array $formData Posted form data
     *
     * @return ViewModel
     */
    private function showLicence($formData)
    {
        // process errors and display the main page
        $errors = [];
        try {
            $response = $this->handleQuery(
                LicenceByNumberDto::create(
                    [
                        'licenceNumber' => $formData['fields']['licenceNumber']
                    ]
                )
            );

            if ($response->isOk()) {
                // return check details page on success
                $result = $response->getResult();

                /** @var \Common\Form\Form $form */
                $form = $this->formHelper
                    ->createFormWithRequest('UserRegistrationAddress', $this->getRequest());

                $form->setData($formData);

                $view = new ViewModel(
                    [
                        'form' => $form,
                        'address' => $result['correspondenceCd']['address'],
                        'organisationName' => $result['organisation']['name'],
                    ]
                );
                $view->setTemplate('olcs/user-registration/check-details');
                $this->placeholder()->setPlaceholder('pageTitle', 'user-registration.page.check-details.title');

                return $view;
            }

            $result = $response->getResult();

            if (!empty($result['messages']['licenceNumber'])) {
                $errors = $result['messages'];
            } else {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }
        } catch (NotFoundException) {
            $errors = [
                'licenceNumber' => ['record-not-found']
            ];
        }

        return $this->generateContentForUserRegistration($formData, $errors);
    }

    /**
     * Create user with licence
     *
     * @param array $formData Posted form data
     *
     * @return ViewModel
     */
    private function createUserWithLic($formData)
    {
        $hasProcessed = $this->createUser($formData);

        if ($hasProcessed instanceof ViewModel) {
            return $hasProcessed;
        }

        // account created page
        $content = new ViewModel();
        $content->setTemplate('olcs/user-registration/account-created');
        $this->placeholder()->setPlaceholder('pageTitle', 'user-registration.page.account-created.title');

        return $content;
    }

    /**
     * Create user with organisation
     *
     * @param array $formData Posted form data
     *
     * @return null|ViewModel
     */
    private function createUserWithOrg($formData)
    {
        $hasProcessed = $this->createUser($formData);

        if ($hasProcessed instanceof ViewModel) {
            return $hasProcessed;
        }

        // check your email page
        $content = new ViewModel(
            [
                'emailAddress' => $formData['fields']['emailAddress']
            ]
        );
        $content->setTemplate('olcs/user-registration/check-email');
        $this->placeholder()->setPlaceholder('pageTitle', 'user-registration.page.check-email.title');

        return $content;
    }

    /**
     * Create user from registration form
     *
     * @param array $formData Posted form data
     *
     * @return null|ViewModel
     */
    private function createUser($formData)
    {
        $data = $this->formatDataMapper->formatSaveData($formData);

        $response = $this->handleCommand(
            RegisterDto::create($data)
        );

        if ($response->isOk()) {
            // return on success
            return null;
        }

        // process errors and display the main page
        $result = $response->getResult();
        $errors = [];

        if (!empty($result['messages']['licenceNumber']) || !empty($result['messages']['loginId'])) {
            $errors = $result['messages'];
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        return $this->generateContentForUserRegistration($formData, $errors);
    }

    /**
     * Redirects to home
     *
     * @return \Laminas\Http\Response
     */
    private function redirectToHome()
    {
        return $this->redirect()->toRoute('index');
    }
}
