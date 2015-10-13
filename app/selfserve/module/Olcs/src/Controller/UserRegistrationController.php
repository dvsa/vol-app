<?php

/**
 * User Registration Controller
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as RegisterDto;
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
                return $this->redirectToIndex();
            }

            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $this->formatSaveData($form->getData());

                $hasProcessed = $this->processUserRegistration($data);

                if ($hasProcessed instanceof ViewModel) {
                    return $hasProcessed;
                }
            }
        }

        $view = new ViewModel(
            [
                'form' => $form
            ]
        );
        $view->setTemplate('olcs/user-registration/index');

        $this->getServiceLocator()->get('Script')->loadFile('user-registration');

        return $view;
    }

    public function processUserRegistration($data)
    {
        $response = $this->handleCommand(
            RegisterDto::create($data)
        );

        if ($response->isOk()) {
            // check your email page
            $content = new ViewModel(
                [
                    'emailAddress' => $data['contactDetails']['emailAddress']
                ]
            );
            $content->setTemplate('olcs/user-registration/check-email');

            return $content;
        } else {
            $this->getFlashMessenger()->addErrorMessage('unknown-error');
        }
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
     * Gets a flash messenger object.
     *
     * @return \Common\Service\Helper\FlashMessengerHelperService
     */
    public function getFlashMessenger()
    {
        return $this->getServiceLocator()->get('Helper\FlashMessenger');
    }

    /**
     * Returns a params object. Made literal here.
     *
     * @return \Zend\Mvc\Controller\Plugin\Params
     */
    protected function params()
    {
        return $this->getPluginManager()->get('params');
    }

    /**
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        return $this->getEvent()->getRequest();
    }

    /**
     * Redirects to index
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRoute(null, ['action' => 'add'], array(), false);
    }
}
