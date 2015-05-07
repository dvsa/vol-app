<?php

/**
 * LicenceGracePeriodsController.php
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;

use Common\Controller\Lva\Traits\CrudTableTrait;
use Common\Controller\Lva\AbstractController;

use Zend\Form\FormInterface;

/**
 * Class LicenceGracePeriodController
 *
 * Controller for managing licence grace periods, provides basic crud functionality.
 *
 * @package Olcs\Controller\Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class LicenceGracePeriodsController extends AbstractController implements LicenceControllerInterface
{
    use LicenceControllerTrait,
        CrudTableTrait;

    protected $lva = 'licence';
    protected $location = 'internal';

    protected $section = 'grace-periods';

    /**
     * List the grace periods for this licence.
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        $licence = $this->params()->fromRoute('licence', null);
        if (!is_null($licence)) {
            $licence = $this->getServiceLocator()->get('Entity\Licence')->getExtendedOverview($licence);
        }

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $crudAction = $this->getCrudAction($data);

            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction);
            }
        }

        $gracePeriodEntityService = $this->getServiceLocator()->get('Entity\GracePeriod');
        $gracePeriods = $gracePeriodEntityService->getGracePeriodsForLicence($licence['id']);

        $form = $this->getForm($gracePeriods);

        $this->getServiceLocator()->get('Script')->loadFile('table-actions');

        return $this->render(
            'grace-period',
            $form
        );
    }

    /**
     * Add action.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        return $this->addOrEdit('add');
    }

    /**
     * Edit action.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        return $this->addOrEdit('edit');
    }

    /**
     * Method to call on the delete action.
     */
    public function delete()
    {
        $gracePeriodEntityService = $this->getServiceLocator()
            ->get('Entity\GracePeriod');

        $gracePeriodIds = $this->params()->fromRoute('child_id', null);
        $ids = explode(',', $gracePeriodIds);

        $gracePeriodEntityService->deleteListByIds(array('id' => $ids));
    }

    /**
     * Method to call when either adding or editing a grace period.
     *
     * @param null $addOrEdit
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function addOrEdit($addOrEdit = null)
    {
        $request = $this->getRequest();

        $gracePeriod = $this->params()->fromRoute('child_id', null);
        if (!is_null($gracePeriod)) {
            $gracePeriod = $this->getServiceLocator()->get('Entity\GracePeriod')->getById($gracePeriod);
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest(
                'GracePeriod',
                $request
            );

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $params = array_merge((array)$gracePeriod, (array)$form->getData()['details']);

                // Satisfy the licence FK on 'add'.
                if ($addOrEdit === 'add') {
                    $params['licence'] = $this->params()->fromRoute('licence', null);
                }

                $result = $this->getServiceLocator()
                    ->get('BusinessServiceManager')
                    ->get('Lva\GracePeriod')
                    ->process($params);

                if ($result->getType() === \Common\BusinessService\ResponseInterface::TYPE_SUCCESS) {
                    $this->flashMessenger()->addSuccessMessage('licence.grace-period.saved.success');
                    return $this->redirectToIndex();
                }

                $this->flashMessenger()->addErrorMessage('licence.grace-period.saved.failure');
                return $this->redirectToIndex();
            }
        }

        // Pre-fill the form data.
        if ($addOrEdit === 'edit') {
            $this->getServiceLocator()->get('Helper/Form')->remove($form, 'form-actions->addAnother');
            $form->setData(
                array(
                    'details' => $gracePeriod
                )
            );
        }

        return $this->render($addOrEdit . '-grace-period', $form);
    }

    /**
     * Get the form and table.
     *
     * @param array $gracePeriods
     *
     * @return mixed
     */
    protected function getForm(array $gracePeriods)
    {
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createFormWithRequest(
                'GracePeriods',
                $this->getRequest()
            );

        $this->alterForm($form, $gracePeriods);

        return $form;
    }

    /**
     * Alter the form to set the table and remove un-needed buttons.
     *
     * @param FormInterface $form
     * @param array $gracePeriods
     */
    protected function alterForm(FormInterface $form, array $gracePeriods)
    {
        $this->getServiceLocator()
            ->get('Helper\Form')
            ->removeFieldList(
                $form,
                'form-actions',
                array(
                    'submit',
                    'addAnother',
                    'cancel'
                )
            );

        $form->get('table')->get('table')->setTable(
            $this->getTable($gracePeriods)
        );
    }

    /**
     * Get the table.
     *
     * @param array $gracePeriods
     *
     * @return mixed
     */
    protected function getTable(array $gracePeriods)
    {
        return $this->getServiceLocator()
            ->get('Table')
            ->prepareTable('licence.grace-periods', $this->getTableData($gracePeriods));
    }

    /**
     * Format data for the table.
     *
     * @param array $gracePeriods
     *
     * @return array
     */
    protected function getTableData(array $gracePeriods)
    {
        $gracePeriodHelper = $this->getServiceLocator()
            ->get('Helper\LicenceGracePeriod');

        $tableData = array();
        foreach ($gracePeriods['Results'] as $gracePeriod) {
            $tableData[] = [
                'id' => $gracePeriod['id'],
                'startDate' => $gracePeriod['startDate'],
                'endDate' => $gracePeriod['endDate'],
                'description' => $gracePeriod['description'],
                'status' => ($gracePeriodHelper->isActive($gracePeriod) ? "Active" : "Inactive")
            ];
        }

        return $tableData;
    }

    /**
     * Redirect to the grace-periods page.
     *
     * @return mixed
     */
    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'licence/grace-periods',
            array(
                'licence' => $this->params()->fromRoute('licence', null)
            )
        );
    }
}
