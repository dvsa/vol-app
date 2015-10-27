<?php

/**
 * User Forgot Username Controller
 */
namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Command\User\RemindUsernameSelfserve as RemindUsernameDto;
use Zend\View\Model\ViewModel;

/**
 * User Forgot Username Controller
 */
class UserForgotUsernameController extends AbstractController
{
    public function indexAction()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('UserForgotUsername', $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToHome();
            }

            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $result = $this->remindUsername($form->getData());

                if ($result instanceof ViewModel) {
                    return $result;
                }
            }
        }

        // forgot username page
        $view = new ViewModel(
            [
                'form' => $form
            ]
        );
        $view->setTemplate('olcs/user-forgot-username/index');

        return $view;
    }

    private function remindUsername($formData)
    {
        $data = $this->mapFromForm($formData);

        $response = $this->handleCommand(
            RemindUsernameDto::create($data)
        );

        if ($response->isOk()) {
            $result = $response->getResult();

            if (array_shift($result['messages']) === 'USERNAME_REMINDER_SENT_MULTIPLE') {
                // ask your administrator
                $content = new ViewModel();
                $content->setTemplate('olcs/user-forgot-username/ask-admin');
            } else {
                // check your email page
                $content = new ViewModel(
                    [
                        'emailAddress' => $formData['fields']['emailAddress']
                    ]
                );
                $content->setTemplate('olcs/user-forgot-username/check-email');
            }

            return $content;
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return null;
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     * This is mapping, not business logic.
     *
     * @param $data
     * @return array
     */
    private function mapFromForm($data)
    {
        $output = [];
        $output['licenceNumber'] = $data['fields']['licenceNumber'];
        $output['emailAddress'] = $data['fields']['emailAddress'];

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
