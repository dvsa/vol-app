<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Command\User\RemindUsernameSelfserve as RemindUsernameDto;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

/**
 * User Forgot Username Controller
 */
class UserForgotUsernameController extends AbstractController
{
    /**
     * Index action
     *
     * @return ViewModel
     */
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
                $result = $this->remindUsername($form);

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

    /**
     * Remind username
     *
     * @param Form $form Form
     *
     * @return ViewModel|null
     */
    private function remindUsername(Form $form)
    {
        $formData = $form->getData();

        $data = $this->mapFromForm($formData);

        $response = $this->handleCommand(
            RemindUsernameDto::create($data)
        );

        if ($response->isOk()) {
            $result = $response->getResult();

            switch (array_shift($result['messages'])) {
                case 'USERNAME_REMINDER_SENT_MULTIPLE':
                    // ask your administrator
                    $content = new ViewModel();
                    $content->setTemplate('olcs/user-forgot-username/ask-admin');
                    $this->placeholder()->setPlaceholder('pageTitle', 'user-forgot-username.page.ask-admin.title');
                    return $content;
                case 'USERNAME_REMINDER_SENT_SINGLE':
                    // check your email page
                    $content = new ViewModel(
                        [
                            'emailAddress' => $formData['fields']['emailAddress']
                        ]
                    );
                    $content->setTemplate('olcs/user-forgot-username/check-email');
                    $this->placeholder()->setPlaceholder('pageTitle', 'user-forgot-username.page.check-email.title');
                    return $content;
                default:
                    $form->get('fields')->get('emailAddress')->setMessages(['ERR_FORGOT_USERNAME_NOT_FOUND']);
                    break;
            }
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return null;
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     * This is mapping, not business logic.
     *
     * @param array $data Data
     *
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
     *
     * @return \Zend\Http\Response
     */
    private function redirectToHome()
    {
        return $this->redirect()->toRoute('index');
    }
}
