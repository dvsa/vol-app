<?php

/**
 * Case Impounding Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * Class to manage Impounding
 */
class CaseImpoundingController extends CaseController
{

    /**
     * Show a table of impounding data for the given case
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        if ((int) $caseId == 0) {
            return $this->notFoundAction();
        }

        $licenceId = $this->fromRoute('licence');
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        $bundle = $this->getBundle();

        $results = $this->makeRestCall(
            'VosaCase', 'GET', array(
            'id' => $caseId, 'bundle' => json_encode($bundle))
        );

        print_r($results);

        $variables = array(
            'tab' => 'impounding',
            'table' => $this->buildTable('Impounding', $results['impoundings'], array())
        );

        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);
        $view->setTemplate('case/manage');

        return $view;
    }

    /**
     * Add impounding data for a case
     *
     * @return ViewModel
     */
    public function addAction()
    {

    }

    /**
     * Loads the edit impounding page
     *
     * @return ViewModel
     */
    public function editAction()
    {

    }

    /**
     * Method to return the bundle required for impounding
     *
     * @return array
     */
    private function getBundle()
    {
        return array(
            'properties' => array(
                'id'
            ),
            'children' => array(
                'impoundings' => array(
                    'properties' => array(
                        'id',
                    ),
                    'children' => array(
                        'presidingTC' => array(
                            'properties' => array(
                                'tcName'
                            ),
                        ),
                    )
                )
            )
        );
    }
}
