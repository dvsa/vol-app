<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccountSelfserve as UpdateDto;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as ItemDto;
use Zend\View\Model\ViewModel;

/**
 * My Details Controller
 */
class MyDetailsController extends AbstractController
{
    /**
     * Edit action
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     */
    public function editAction()
    {
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest('MyDetails', $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }

            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $this->formatSaveData($form->getData());

                $response = $this->handleCommand(
                    UpdateDto::create($data)
                );

                if ($response->isOk()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addSuccessMessage('generic.updated.success');
                    return $this->redirectToIndex();
                } else {
                    $result = $response->getResult();

                    if (!empty($result['messages']['loginId'])) {
                        $form->setMessages(
                            [
                                'main' => $result['messages']
                            ]
                        );
                    } else {
                        $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                    }
                }
            }
        } else {
            $response = $this->handleQuery(ItemDto::create([]));

            if ($response->isOk()) {
                $data = $this->formatLoadData($response->getResult());
                $form->setData($data);
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        }

        $view = new ViewModel(
            [
                'form' => $form,
                'showNav' => false
            ]
        );
        $view->setTemplate('pages/my-details-page');

        $this->getServiceLocator()->get('Script')->loadFile('my-details');

        return $view;
    }

    /**
     * Formats the data from what the service gives us, to what the form needs.
     *
     * @param array $data Data
     *
     * @return array
     */
    private function formatLoadData($data)
    {
        return [
            'main' => [
                'id' => $data['id'],
                'version' => $data['version'],
                'loginId' => $data['loginId'],
                'translateToWelsh' => $data['translateToWelsh'],
                'emailAddress' => $data['contactDetails']['emailAddress'],
                'emailConfirm' => $data['contactDetails']['emailAddress'],
                'familyName' => $data['contactDetails']['person']['familyName'],
                'forename' => $data['contactDetails']['person']['forename'],
            ]
        ];
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     *
     * @param array $data Data
     *
     * @return array
     */
    private function formatSaveData($data)
    {
        return [
            'id' => $data['main']['id'],
            'version' => $data['main']['version'],
            'loginId' => $data['main']['loginId'],
            'translateToWelsh' => $data['main']['translateToWelsh'],
            'contactDetails' => [
                'emailAddress' => $data['main']['emailAddress'],
                'person' => [
                    'familyName' => $data['main']['familyName'],
                    'forename' => $data['main']['forename'],
                ]
            ]
        ];
    }

    /**
     * Redirects to index
     *
     * @return \Zend\Http\Response
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRoute('your-account', ['action' => 'edit'], array(), false);
    }
}
