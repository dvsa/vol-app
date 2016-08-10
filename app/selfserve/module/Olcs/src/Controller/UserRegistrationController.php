<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as RegisterDto;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceRegisteredAddress as LicenceByNumberDto;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

/**
 * User Registration Controller
 */
class UserRegistrationController extends AbstractController
{
    public function addAction()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('UserRegistration', $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToHome();
            }

            $form->setData($this->params()->fromPost());

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

    private function showLicence($formData)
    {
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

        // process errors and display the main page
        $errors = [];

        if ($response->isNotFound()) {
            $errors = [
                'licenceNumber' => ['record-not-found']
            ];
        } else {
            $result = $response->getResult();

            if (!empty($result['messages']['licenceNumber'])) {
                $errors = $result['messages'];
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        }

        return $this->generateContentForUserRegistration($formData, $errors);
    }

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
     * @param $data
     * @return array
     */
    private function formatSaveData($data)
    {
        $output = [];
        $output['loginId'] = $data['fields']['loginId'];
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
     * Redirects to home
     */
    private function redirectToHome()
    {
        return $this->redirect()->toRoute('index');
    }
}
