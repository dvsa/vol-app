<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Traits;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Data\FeeTypeDataService;
use Common\Service\Entity\FeeEntityService;
use Common\Service\Data\CategoryDataService;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractController
{
    use Traits\LicenceControllerTrait,
        Traits\FeesActionTrait,
        Traits\ApplicationControllerTrait;

    /**
     * Shows fees table
     */
    public function feesAction()
    {
        $response = $this->checkActionRedirect('licence');
        if ($response) {
            return $response;
        }

        $licenceId = $this->getServiceLocator()
            ->get('Entity\Application')
            ->getLicenceIdForApplication(
                $this->params('application')
            );

        return $this->commonFeesAction($licenceId);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function caseAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        return $this->render($view);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function environmentalAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        return $this->render($view);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function documentAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        return $this->render($view);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function processingAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        return $this->render($view);
    }

    public function grantAction()
    {
        $request = $this->getRequest();
        $id = $this->params('application');

        if ($request->isPost()) {

            if (!$this->isButtonPressed('cancel')) {

                $licenceId = $this->getServiceLocator()->get('Entity\Application')->getLicenceIdForApplication($id);
                $this->grantApplication($id);
                $this->grantLicence($licenceId);
                $taskId = $this->createGrantTask($id, $licenceId);
                $this->createGrantFee($id, $licenceId, $taskId);

                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('The application was granted successfully');
            }

            return $this->redirect()->toRoute('lva-application', array('application' => $id));
        }

        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('GenericConfirmation');

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('application/grant');

        return $this->render($view);
    }

    public function undoGrantAction()
    {
        $request = $this->getRequest();
        $id = $this->params('application');

        if ($request->isPost()) {

            if (!$this->isButtonPressed('cancel')) {

                $licenceId = $this->getServiceLocator()->get('Entity\Application')->getLicenceIdForApplication($id);
                $this->undoGrantApplication($id);
                $this->undoGrantLicence($licenceId);
                $this->cancelFees($licenceId);
                $this->closeGrantTask($id, $licenceId);

                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('The application grant has been undone successfully');
            }

            return $this->redirect()->toRoute('lva-application', array('application' => $id));
        }

        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('GenericConfirmation');

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('application/undo-grant');

        return $this->render($view);
    }

    protected function cancelFees($licenceId)
    {
        $this->getServiceLocator()->get('Entity\Fee')->cancelForLicence($licenceId);
    }

    protected function undoGrantApplication($id)
    {
        $applicationData = array(
            'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
            'grantedDate' => null
        );

        $this->getServiceLocator()->get('Entity\Application')->forceUpdate($id, $applicationData);
    }

    protected function undoGrantLicence($id)
    {
        $licenceData = array(
            'status' => LicenceEntityService::LICENCE_STATUS_UNDER_CONSIDERATION,
            'grantedDate' => null
        );

        $this->getServiceLocator()->get('Entity\Licence')->forceUpdate($id, $licenceData);
    }

    protected function grantApplication($id)
    {
        $grantedDate = $this->getServiceLocator()->get('Helper\Date')->getDate();

        $applicationData = array(
            'status' => ApplicationEntityService::APPLICATION_STATUS_GRANTED,
            'grantedDate' => $grantedDate
        );

        $this->getServiceLocator()->get('Entity\Application')->forceUpdate($id, $applicationData);
    }

    protected function grantLicence($id)
    {
        $grantedDate = $this->getServiceLocator()->get('Helper\Date')->getDate();

        $licenceData = array(
            'status' => LicenceEntityService::LICENCE_STATUS_GRANTED,
            'grantedDate' => $grantedDate
        );

        $this->getServiceLocator()->get('Entity\Licence')->forceUpdate($id, $licenceData);
    }

    protected function closeGrantTask($id, $licenceId)
    {
        $this->getServiceLocator()->get('Entity\Task')->closeByQuery(
            array(
                'category' => CategoryDataService::CATEGORY_APPLICATION,
                'taskSubCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE,
                'licence' => $licenceId,
                'application' => $id
            )
        );
    }

    protected function createGrantTask($id, $licenceId)
    {
        $user = $this->getServiceLocator()->get('Entity\User')->getCurrentUser();
        $date = $this->getServiceLocator()->get('Helper\Date')->getDate();

        $data = array(
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'taskSubCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE,
            'description' => 'Grant fee due',
            'actionDate' => $date,
            'assignedToUser' => $user['id'],
            'assignedToTeam' => $user['team']['id'],
            'isClosed' => 'N',
            'urgent' => 'N',
            'application' => $id,
            'licence' => $licenceId,
        );

        $saved = $this->getServiceLocator()->get('Entity\Task')->save($data);

        return $saved['id'];
    }

    protected function createGrantFee($applicationId, $licenceId, $taskId)
    {
        $feeType = $this->getFeeTypeForLicence($applicationId, $licenceId);
        $date = $this->getServiceLocator()->get('Helper\Date')->getDate();

        $feeData = array(
            'amount' => (float)($feeType['fixedValue'] === '0.00' ? $feeType['fiveYearValue'] : $feeType['fixedValue']),
            'application' => $applicationId,
            'licence' => $licenceId,
            'invoicedDate' => $date,
            'feeType' => $feeType['id'],
            'description' => $feeType['description'] . ' for application ' . $applicationId,
            'feeStatus' => FeeEntityService::STATUS_OUTSTANDING,
            'task' => $taskId
        );

        $this->getServiceLocator()->get('Entity\Fee')->save($feeData);
    }

    /**
     * Get the latest fee type for a licence
     *
     * @todo Maybe move this so it can be re-used
     *
     * @param int $licenceId
     * @return int
     */
    protected function getFeeTypeForLicence($applicationId, $licenceId)
    {
        $data = $this->getServiceLocator()->get('Entity\Licence')->getTypeOfLicenceData($licenceId);

        $date = $this->getServiceLocator()->get('Entity\Application')->getApplicationDate($applicationId);

        return $this->getServiceLocator()->get('Data\FeeType')->getLatest(
            FeeTypeDataService::FEE_TYPE_GRANT,
            $data['goodsOrPsv'],
            $data['licenceType'],
            $date,
            ($data['niFlag'] === 'Y')
        );
    }
}
