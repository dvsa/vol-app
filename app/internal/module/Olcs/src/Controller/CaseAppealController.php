<?php

/**
 * Case Appeal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;
use Olcs\Controller\Traits\DeleteActionTrait;

use Common\Controller\CrudInterface;
/**
 * Case Appeal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseAppealController extends CaseController implements CrudInterface
{
    use DeleteActionTrait;

    /**
     * Should return the name of the service to call for deleting the item
     *
     * @return string
     */
    public function getDeleteServiceName()
    {
        return 'Appeal';
    }

    /**
     * Does what it says on the tin.
     *
     * @return mixed
     */
    public function redirectToIndex()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        return $this->redirect()->toRoute(
            'case_stay_action',
            array(
                'action' => 'index',
                'licence' => $licenceId,
                'case' => $caseId
            )
        );
    }

    /**
     * Add appeal action
     *
     * @return object
     */
    public function addAction()
    {
        $caseId = $this->fromRoute('case');
        $licenceId = $this->fromRoute('licence');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_stay_action' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        $form = $this->generateFormWithData(
            'appeal',
            'processAddAppeal',
            array('case' => $caseId)
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add appeal',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'inlineScript' => $this->getServiceLocator()->get('Script')->loadFiles(['withdrawn'])
            ]
        );

        $view->setTemplate('form');

        return $view;
    }

    /**
     * Edit appeal action
     *
     * @return object
     */
    public function editAction()
    {
        $appealId = $this->fromRoute('id');
        $licenceId = $this->fromRoute('licence');

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $details = $this->makeRestCall('Appeal', 'GET', array('id' => $appealId, 'bundle' => json_encode($bundle)));

        if (empty($details)) {
            return $this->notFoundAction();
        }

        $data = $this->formatDataForEditForm($details);

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_stay_action' => array('licence' => $licenceId, 'case' => $data['case'])
            )
        );

        $form = $this->generateFormWithData(
            'appeal',
            'processEditAppeal',
            $data
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Edit appeal',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'inlineScript' => $this->getServiceLocator()->get('Script')->loadFiles(['withdrawn'])
            ]
        );

        $view->setTemplate('form');

        return $view;
    }

    /**
     * Format the data for the edit form
     *
     * @param array $data
     * @return array
     */
    private function formatDataForEditForm($data)
    {
        $data['case'] = $data['case']['id'];
        $data['details'] = $data;

        $data['details']['reason'] = 'appeal_reason.' . $data['details']['reason'];
        $data['details']['outcome'] = 'appeal_outcome.' . $data['details']['outcome'];

        return $data;
    }

    /**
     * Process the add post
     *
     * @param array $data
     */
    public function processAddAppeal($data)
    {
        $data = $this->processDataBeforePersist($data);

        $this->processAdd($data, 'Appeal');

        $licence = $this->fromRoute('licence');
        $this->redirect()->toRoute('case_stay_action', array('licence' => $licence, 'case' => $data['case']));
    }

    /**
     * Process the edit post
     *
     * @param array $data
     */
    public function processEditAppeal($data)
    {
        $data = $this->processDataBeforePersist($data);

        $this->processEdit($data, 'Appeal');

        $licence = $this->fromRoute('licence');
        $this->redirect()->toRoute('case_stay_action', array('licence' => $licence, 'case' => $data['case']));
    }

    /**
     * Pre-persist data processing
     *
     * @param array $data
     * @return array
     */
    protected function processDataBeforePersist($data)
    {
        $data = array_merge($data, $data['details']);

        unset($data['details']);

        $data['reason'] = str_replace('appeal_reason.', '', $data['reason']);
        $data['outcome'] = str_replace('appeal_outcome.', '', $data['outcome']);

        //if the withdrawn checkbox is 'N' then make sure withdrawn date is null
        if ($data['isWithdrawn'] == 'N') {
            $data['withdrawnDate'] = null;
        }

        return $data;
    }
}
