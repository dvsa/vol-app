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
    public function generateStepFormAction() {
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

        // render the view
        $view = new ViewModel(['licenceTypeForm' => $form]);
        $view->setTemplate('self-serve/licence/index');
        return $view;
    }


} 