<?php

/**
 * CContinuationChecklistReminderController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Common\Controller\Lva\Traits\CrudActionTrait;

/**
 * ContinuationChecklistReminderController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationChecklistReminderController extends AbstractController
{
    use CrudActionTrait;

    /**
     * Display a list of Continuation checklist reminders
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array) $request->getPost();

            $crudAction = $this->getCrudAction([$data]);
            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction);
            }
        }

        $nowDate = $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m');
        list($year, $month) = explode('-', $nowDate);

        $filterForm = $this->getChecklistReminderFilterForm($month, $year);
        if ($filterForm->isValid()) {
            list($year, $month) = explode('-', $filterForm->getData()['filters']['date']);
        }

        $results = $this->getServiceLocator()->get('Entity\ContinuationDetail')
            ->getChecklistReminderList($month, $year);

        $table = $this->getServiceLocator()->get('Table')
            ->prepareTable('admin-continuations-checklist', $results['Results']);
        $subTitle = date('M Y', strtotime($year . '-' . $month . '-01'));
        $table->setVariable('title', $subTitle .': '. $results['Count'] . ' licence(s)');

        $this->getServiceLocator()->get('Script')->loadFiles(['forms/filter', 'table-actions']);

        $view = new ViewModel(['table' => $table, 'filterForm' => $filterForm]);
        $view->setTemplate('partials/table');

        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('tableFilters')->set($filterForm);
        $this->setNavigationId('admin-dashboard/continuations');

        return $this->renderView($view, 'admin-generate-continuation-details-title');
    }

    /**
     * Get the filter form
     *
     * @param int $defaultMonth
     * @param int $defaultYear
     *
     * @return \Zend\Form\Form
     */
    protected function getChecklistReminderFilterForm($defaultMonth, $defaultYear)
    {
        $queryData = (array) $this->params()->fromQuery('filters');
        $defaults = [
            'date' => [
                'month' => $defaultMonth,
                'year' => $defaultYear,
            ]
        ];

        $filters = array_merge($defaults, $queryData);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('ChecklistReminderFilter', false)->setData(['filters' => $filters]);

        if (empty($queryData)) {
            $formHelper->restoreFormState($form);
        } else {
            $formHelper->saveFormState($form, ['filters' => $filters]);
        }

        return $form;
    }

    /**
     * Generate Continuation checklist reminder letters
     */
    public function generateLettersAction()
    {
        $continuationDetailIds = explode(',', $this->params('child_id'));

        $response = $this->getServiceLocator()->get('BusinessServiceManager')
              ->get('ContinuationChecklistReminderQueueLetters')
              ->process(['continuationDetailIds' => $continuationDetailIds]);

        $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('The checklist reminder letters have been generated.');
        } else {
            $flashMessenger->addErrorMessage('The checklist reminder letters could not be generated, please try again');
        }

        return $this->redirect()->toRouteAjax(null, ['action' => null, 'child_id' => null], [], true);
    }
}
