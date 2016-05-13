<?php

/**
 * Create task temporary controller
 * @todo remove after task allocation rules will be tested (OLCS-6844 & OLCS-12638)
 */
namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;
use \Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Common\Controller\Traits\GenericRenderView;

/**
 * Create task temporary controller
 */
class CreateTaskTempController extends ZendAbstractActionController
{
    use GenericRenderView;

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();
        } else {
            $data = [
                'details' => [
                    'category' => CategoryDataService::CATEGORY_LICENSING,
                    'subCategory' => CategoryDataService::SCAN_SUB_CATEGORY_CHANGE_OF_ENTITY
                ]
            ];
        }

        $form = $this->createFormWithData($data);

        $this->getServiceLocator()->get('Script')->loadFile('forms/create-task-temp');

        if ($this->getRequest()->isPost()) {

            $details = $data['details'];

            if ($form->isValid()) {

                /* @var $response \Common\Service\Cqrs\Response */
                $response = $this->handleCommand(
                    \Dvsa\Olcs\Transfer\Command\Task\CreateTaskTemp::create(
                        [
                            'category' => $details['category'],
                            'subCategory' => $details['subCategory'],
                            'licence' => $details['entityIdentifier'],
                            'description' => 'test description',
                            'actionDate' => '2016-01-01',
                            'urgent' => 'N',
                            'assignedToTeam' => null
                        ]
                    )
                );

                if (!$response->isOk()) {
                    if (isset($response->getResult()['messages'])) {
                        $form->setMessages($response->getResult()['messages']);
                        foreach ($response->getResult()['messages'] as $key => $messages) {
                            if (is_array($messages)) {
                                foreach ($messages as $message) {
                                    $this->flashMessenger()->addErrorMessage(($key . ' => ' . $message));
                                }
                            } else {
                                $this->flashMessenger()->addErrorMessage(($messages));
                            }
                        }
                    } else {
                        $this->flashMessenger()->addErrorMessage(('Unknown error'));
                    }
                } else {
                    $this->flashMessenger()->addSuccessMessage('Task was created');

                    $form = $this->createFormWithData(
                        [
                            'details' => [
                                'category' => $details['category'],
                                'entityIdentifier' => $details['entityIdentifier']
                            ]
                        ]
                    );
                    $this->getServiceLocator()->get('Script')->loadFile('scanning-success');
                }
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Create task');
    }

    private function createFormWithData($data)
    {
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('CreateTaskTemp')
            ->setData($data);

        $form->get('form-actions')->remove('cancel');

        return $form;
    }
}
