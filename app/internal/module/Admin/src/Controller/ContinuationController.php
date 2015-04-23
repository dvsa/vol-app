<?php

/**
 * Continuation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Common\Service\Entity\ContinuationEntityService;
use Common\BusinessService\Response;

/**
 * Continuation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationController extends AbstractController
{
    public function indexAction()
    {
        $request = $this->getRequest();

        $form = $this->getContinuationForm();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $form->setData($data);

            // AC Says to redirect to placeholder page until irfo is developed
            if ($data['details']['type'] === ContinuationEntityService::TYPE_IRFO) {
                return $this->redirect()->toRoute(null, ['action' => 'irfo']);
            }

            $criteria = [
                'type' => ContinuationEntityService::TYPE_OPERATOR,
                'month' => (int)$data['details']['date']['month'],
                'year' => (int)$data['details']['date']['year'],
                'trafficArea' => $data['details']['trafficArea']
            ];

            $continuation = $this->getServiceLocator()->get('Entity\Continuation')->find($criteria);

            if ($continuation !== null) {
                return $this->redirect()->toRoute(null, ['action' => 'detail', 'id' => $continuation['id']]);
            }

            // Create continuation
            $response = $this->getServiceLocator()->get('BusinessServiceManager')
                ->get('Admin\Continuation')
                ->process(['data' => $criteria]);

            // We treat success and no_op differently in this case
            if ($response->getType() === Response::TYPE_SUCCESS) {

                $id = $response->getData()['id'];
                return $this->redirect()->toRoute(null, ['action' => 'detail', 'id' => $id]);
            }

            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            if ($response->getType() === Response::TYPE_NO_OP) {
                $fm->addCurrentInfoMessage('admin-continuations-no-licences-found');
            } else {
                $fm->addErrorInfoMessage($response->getMessage());
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');
        $this->setNavigationId('admin-dashboard/continuations');
        $this->getServiceLocator()->get('Script')->loadFile('continuations');

        return $this->renderView($view, 'admin-generate-continuations-title');
    }

    public function detailAction()
    {
        $view = new ViewModel();
        $view->setTemplate('placeholder');
        $this->setNavigationId('admin-dashboard/continuations');
        return $this->renderView($view, 'Continuation list');
    }

    public function irfoAction()
    {
        $view = new ViewModel();
        $view->setTemplate('placeholder');
        $this->setNavigationId('admin-dashboard/continuations');
        return $this->renderView($view, 'IRFO Continuations');
    }

    protected function getContinuationForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('GenerateContinuation');
    }
}
