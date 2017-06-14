<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as RegisterDto;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceRegisteredAddress as LicenceByNumberDto;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

/**
 * User Registration Controller
 */
class UserRegistrationController extends AbstractController
{
    /**
     * Method used for the registration form page
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function addAction()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('UserRegistration', $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToHome();
            }

            $postData = $this->formatPostData(
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

        $this->getServiceLocator()->get('Script')->loadFile('user-registration');

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

        $label = $this->getServiceLocator()->get('Helper\Translation')->translateReplace(
            $termsAgreed->getLabel(),
            [
                $this->getServiceLocator()->get('Helper\Url')->fromRoute('terms-and-conditions')
            ]
        );

        $termsAgreed->setLabel($label);

        return $form;
    }

    /**
     * Generate content for user registration
     *
     * @param array $formData Form data
     * @param array $errors   Errors from ZF Validation Chain
     *
     * @return ViewModel
     */
    private function generateContentForUserRegistration(array $formData = [], array $errors = [])
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')
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

        $this->getServiceLocator()->get('Script')->loadFile('user-registration');

        return $view;
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
                $form = $this->getServiceLocator()->get('Helper\Form')
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
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        } catch (NotFoundException $e) {
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
        $data = $this->formatSaveData($formData);

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
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $this->generateContentForUserRegistration($formData, $errors);
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     * This is mapping, not business logic.
     *
     * @param array $data Posted form data
     *
     * @return array
     */
    private function formatSaveData($data)
    {
        $output = [];
        $output['loginId'] = $data['fields']['loginId'];
        $output['translateToWelsh'] = $data['fields']['translateToWelsh'];
        $output['contactDetails']['emailAddress'] = $data['fields']['emailAddress'];
        $output['contactDetails']['person']['familyName'] = $data['fields']['familyName'];
        $output['contactDetails']['person']['forename']   = $data['fields']['forename'];

        if ('Y' === $data['fields']['isLicenceHolder']) {
            $output['licenceNumber'] = $data['fields']['licenceNumber'];
        } else {
            $output['organisationName'] = $data['fields']['organisationName'];
            $output['businessType'] = $data['fields']['businessType'];
        }

        return $output;
    }

    /**
     * A radio button is used and validated only if a checkbox is selected.
     * As browsers by default do not post the value or default value of a radio
     * button.  We specify an empty input for this field.
     *
     * @param array $postData Data from posted form
     *
     * @return array
     */
    private function formatPostData(array $postData)
    {
        if (empty($postData['fields']['businessType'])) {
            $postData['fields']['businessType'] = null;
        }

        return $postData;
    }

    /**
     * Redirects to home
     *
     * @return \Zend\Http\Response
     */
    private function redirectToHome()
    {
        return $this->redirect()->toRoute('index');
    }
}
