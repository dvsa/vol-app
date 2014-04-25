<?php
/**
 * @package		selfserve
 * @subpackage  PreviousHistory
 * @author		Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace SelfServe\Controller\PreviousHistory;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

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

        // create form
        $form = $this->generateSectionForm();

        // Do the post
        $form = $this->formPost($form, $this->getStepProcessMethod($this->getCurrentStep()), ['applicationId' => $applicationId]);

        // prefill form data if persisted
        $formData = $this->getPersistedFormData($form);
        if (isset($formData))
        {
            $form->setData($formData);
        }

        //for finance step we need to render form in a special way to meet UI expectations
        if ($step == 'financee'){
            $formPartialPath = 'self-serve/forms/previous-history-finance';
        } else{
            $formPartialPath = 'self-serve/forms/previous-history';
        }

        // render the view
        $view = new ViewModel(['form' => $form, 'formPartialPath' => $formPartialPath]);
        $view->setTemplate('self-serve/previous-history/index');
        return $view;
    }


    public function processFinance($validData, $form, $params)
    {
        $data = $validData['finance'];
        unset($validData['finance']);
        $data = array_merge($data, $validData);
        unset();

        var_dump($data);exit;
    }

    public function getFinanceFormData()
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $entity = $this->makeRestCall('Application', 'GET', ['id' => $applicationId]);

        if (empty($entity))
            throw new \OlcsEntities\Exceptions\EntityNotFoundException('Entity not found');

        return array(
            'version' => $entity['version'],
            'finance' => $entity,
        );
    }


    /**
     * End of the journey redirect to business type
     */
    public function completeAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $this->redirect()->toRoute('selfserve/finance',
            array('applicationId' => $applicationId, 'step' =>
                'index'));
    }


} 