<?php

/**
 * Cpms Report Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Command\Cpms\RequestReport as GenerateCmd;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\CpmsReport as Mapper;
use Admin\Form\Model\Form\CpmsReport as Form;
use Zend\View\Model\ViewModel;

/**
 * Cpms Report Controller
 */
class CpmsReportController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-report';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-report',
                'navigationTitle' => 'Reports'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    public function addAction()
    {
        return $this->notFoundAction();
    }

    public function generateAction()
    {
        $editViewTemplate = 'pages/crud-form';
        $successMessage = 'Report generation in progress';

        $this->placeholder()->setPlaceholder('pageTitle', 'CPMS Financial report');

        $request = $this->getRequest();
        $action = ucfirst($this->params()->fromRoute('action'));
        $form = $this->getForm(Form::class);
        $this->placeholder()->setPlaceholder('form', $form);

        if ($request->isPost()) {
            $dataFromPost = (array) $this->params()->fromPost();
            $form->setData($dataFromPost);
        }

        if ($request->isPost() && $form->isValid()) {
            $commandData = Mapper::mapFromForm($form->getData());
            $response = $this->handleCommand(GenerateCmd::create($commandData));

            if ($response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            if ($response->isClientError()) {
                $flashErrors = Mapper::mapFromErrors($form, $response->getResult());

                foreach ($flashErrors as $error) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($error);
                }
            }

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage($successMessage);
                return $this->redirectTo($response->getResult());
            }
        }

        return $this->viewBuilder()->buildViewFromTemplate($editViewTemplate);
    }

    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'admin-dashboard/admin-report/cpms',
            ['action' => 'generate'],
            ['code' => '303'],
            true
        );
    }
}
