<?php

/**
 * Case Appeal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

/**
 * Case Appeal Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseAppealController extends CaseController
{
    /**
     * Add appeal action
     *
     * @return object
     */
    public function addAction()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'appeal', 'processAddAppeal', array('case' => $caseId)
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add appeal',
                    'pageSubTitle' => ''
                ],
                'form' => $form
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
        $appealId = $this->fromRoute('appeal');

        $details = $this->makeRestCall('Appeal', 'GET', array('id' => $appealId));

        if (empty($details)) {
            return $this->notFoundAction();
        }

        $data = $this->formatDataForEditForm($details);

        $form = $this->generateFormWithData(
            'appeal',
            'processEditAppeal',
            $data,
            true
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Edit appeal',
                    'pageSubTitle' => ''
                ],
                'form' => $form
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
        $this->redirect()->toRoute('case_appeal', array('licence' => $licence, 'case' => $data['case']));
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
        $this->redirect()->toRoute('case_appeal', array('licence' => $licence, 'case' => $data['case']));
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

        return $data;
    }
}
