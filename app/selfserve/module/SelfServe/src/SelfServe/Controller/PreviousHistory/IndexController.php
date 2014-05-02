<?php
/**
 * @package		selfserve
 * @subpackage  PreviousHistory
 * @author		Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace SelfServe\Controller\PreviousHistory;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

/**
 * @package		selfserve
 * @subpackage  PreviousHistory
 * @author		Jakub Igla <jakub.igla@valtech.co.uk>
 */
class IndexController extends FormJourneyActionController
{

    public function __construct()
    {
        $this->setCurrentSection('previous-history');
    }

    /**
     * Generates the next step form depending on which step the user is on.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function generateStepFormAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $step = $this->params()->fromRoute('step');

        $this->setCurrentStep($step);

        // collect completion status
        $completionStatus = $this->makeRestCall('ApplicationCompletion', 'GET', array('application_id' => $applicationId));

        // create form
        $form = $this->generateSectionForm();

        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
        }

        // Do the post
        $form = $this->formPost($form, $this->getStepProcessMethod($this->getCurrentStep()), ['applicationId' => $applicationId]);

        //for finance step we need to render form in a special way to meet UI expectations
        if ($step == 'finance'){
            $formPartialPath = 'self-serve/forms/previous-history-finance';
        } else{
            $formPartialPath = 'self-serve/forms/previous-history';
        }

        // render the view
        $view = new ViewModel(['form' => $form,
                                'formPartialPath' => $formPartialPath,
                                'completionStatus' => (($completionStatus['Count']>0)?$completionStatus['Results'][0]:Array()),
                                'applicationId' => $applicationId]);
        $view->setTemplate('self-serve/previous-history/index');
        return $view;
    }


    /**
     * Persists valid data to database and redirects to next step
     *
     * @param array $validData
     * @param \Zend\Form\Form $form
     * @param $params
     * @return void
     */
    public function processFinance($validData, $form, $params)
    {

        //prepare data
        $data = $validData['finance'];
        unset($validData['finance']);
        $data = array_merge($data, $validData, array('id' => $params['applicationId']));

        //persist to database
        $this->processEdit($data, 'Application');
        $next_step = $this->evaluateNextStep($form);

        //reditect to next step
        $this->redirect()->toRoute('selfserve/previous-history',
            array('applicationId' => $params['applicationId'],
                'step' => $next_step));

    }

    /**
     * Get data from database and returns in ready to populate form format
     *
     * @return array
     * @throws \OlcsEntities\Exceptions\EntityNotFoundException
     */
    public function getFinanceFormData()
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $entity = $this->makeRestCall('Application', 'GET', ['id' => $applicationId]);

        if (empty($entity)){
            throw new \OlcsEntities\Exceptions\EntityNotFoundException('Application entity not found');
        }

        return array(
            'version' => $entity['version'],
            'finance' => $entity,
        );
    }


    /**
     * End of the journey redirect to finance dashboard
     */
    public function completeAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $this->redirect()->toRoute('selfserve/finance',
            array('applicationId' => $applicationId, 'step' =>
                'index'));
    }


}