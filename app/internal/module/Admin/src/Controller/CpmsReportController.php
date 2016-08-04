<?php

namespace Admin\Controller;

use Admin\Form\Model\Form\CpmsReport as Form;
use Dvsa\Olcs\Transfer\Command\Cpms\RequestReport as GenerateCmd;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\CpmsReport as Mapper;
use Zend\View\Model\ViewModel;

/**
 * Cpms Report Controller
 */
class CpmsReportController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-report';

    /**
     * @var array
     */
    private $reports = [];

    /**
     * Left View setting
     *
     * @return ViewModel
     */
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

    /**
     * Process action - Index
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToGenerate();
    }

    /**
     * Process action - Generate
     *
     * @return mixed|ViewModel
     */
    public function generateAction()
    {
        $editViewTemplate = 'pages/crud-form';
        $successMessage = 'Report generation in progress';

        $this->placeholder()->setPlaceholder('pageTitle', 'CPMS Financial report');

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $form = $this->getForm(Form::class);
        $this->setSelectReportList($form);

        $this->placeholder()->setPlaceholder('form', $form);

        if ($request->isPost()) {
            $dataFromPost = (array) $this->params()->fromPost();
            $form->setData($dataFromPost);
        }

        if ($request->isPost() && $form->isValid()) {
            $commandData = Mapper::mapFromForm($form->getData());
            $commandData['name'] = $this->getReportName($commandData['reportCode'])
                .' '. $commandData['start'] .' to '. $commandData['end'];
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

    /**
     * Set the list of reports in the select element
     *
     * @param \Common\Form\Form $form Form
     *
     * @return void
     */
    private function setSelectReportList(\Common\Form\Form $form)
    {
        $response = $this->handleQuery(\Dvsa\Olcs\Transfer\Query\Cpms\ReportList::create([]));
        if ($response->isOk()) {
            $select = $form->get('reportOptions')->get('reportCode');
            $options = [];
            foreach ($response->getResult()['results'] as $reportData) {
                $options[$reportData['code']] = $reportData['title'];
            }
            /* @var $select \Zend\Form\Element\Select */
            $select->setValueOptions($options);
            $this->reports = $options;
        }
    }

    /**
     * Get the report name from the code
     *
     * @param string $code Code
     *
     * @return string
     */
    private function getReportName($code)
    {
        if (isset($this->reports[$code])) {
            return $this->reports[$code];
        }

        return "Unknown";
    }

    /**
     * Redirect to generate
     *
     * @return \Zend\Http\Response
     */
    public function redirectToGenerate()
    {
        return $this->redirect()->toRouteAjax(
            'admin-dashboard/admin-report/cpms',
            ['action' => 'generate'],
            ['code' => '303'],
            true
        );
    }
}
