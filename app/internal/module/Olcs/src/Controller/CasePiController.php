<?php

/**
 * Case Public Inquiry Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * Class to manage Public Inquiry
 */
class CasePiController extends CaseController
{

    /**
     * Gets public inquiry data
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        if (!$caseId || !$licenceId) {
            return $this->notFoundAction();
        }

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        //$bundle = $this->getIndexBundle();

        $variables = array(
            'tab' => 'pi'
        );

        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);
        $view->setTemplate('case/manage');

        return $view;
    }

    public function addAction()
    {
        $type = $this->fromRoute('type');
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_pi' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        switch($type){
            case 'sla':
                return $this->addSlaAction($caseId);
            case 'agreed':
                return $this->addAgreedAction($caseId);
            case 'schedule':
                return $this->addScheduleAction($caseId);
            case 'decision':
                return $this->addDecisionAction($caseId);
        }

    }

    /**
     * Add Public Inquiry data for a case
     *
     * @return ViewModel
     */
    public function addSlaAction($caseId)
    {
        $form = $this->generateFormWithData(
            'pi-sla',
            'processSla',
            array(
                'case' => $caseId
            ),
            true
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add SLA',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'headScript' => array('/static/js/impounding.js')
            ]
        );

        $view->setTemplate('/form');

        return $view;
    }

    /**
     * Add Public Inquiry agreed data for a case
     *
     * @return ViewModel
     */
    public function addAgreedAction($caseId)
    {
        $form = $this->generateFormWithData(
            'pi-agreed',
            'processAgreed',
            array(
                'case' => $caseId
            ),
            true
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add Agreed and Legislation',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'headScript' => array('/static/js/impounding.js')
            ]
        );

        $view->setTemplate('/form');

        return $view;
    }

    /**
     * Add Public Inquiry decision data for a case
     *
     * @return ViewModel
     */
    public function addScheduleAction($caseId)
    {
        $form = $this->generateFormWithData(
            'pi-schedule',
            'processSchedule',
            array(
                'case' => $caseId
            ),
            true
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add Schedule and Publish',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'headScript' => array('/static/js/impounding.js')
            ]
        );

        $view->setTemplate('/form');

        return $view;
    }

    /**
     * Add Public Inquiry decision data for a case
     *
     * @return ViewModel
     */
    public function addDecisionAction($caseId)
    {
        $form = $this->generateFormWithData(
            'pi-decision',
            'processDecision',
            array(
                'case' => $caseId
            ),
            true
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add Register Decision',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'headScript' => array('/static/js/impounding.js')
            ]
        );

        $view->setTemplate('/form');

        return $view;
    }

    /**
     * Processes an SLA form
     */
    public function processSla()
    {

    }

    /**
     * Edit Public Inquiry data for a case
     *
     * @return ViewModel
     */
    public function editAction()
    {

    }

    /**
     * Build the data for a witness select box
     *
     * @return array
     */
    public function getWitnessData()
    {
        $witness = array();

        for ($i = 0; $i <= 100; $i++) {
            $witness[$i] = $i;
        }

        return $witness;
    }
}
