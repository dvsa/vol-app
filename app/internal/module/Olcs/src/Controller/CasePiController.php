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
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = null;

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Pi';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Gets public inquiry data
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        $pi = $this->getPiInfo($caseId);

        //die('<pre>' . print_r($pi, 1));

        $table = $this->buildPiHearingsTable($pi);

        $variables = array(
            'tab' => 'pi',
            'pi' => $pi,
            'piTable' => $table,
        );

        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);
        $view->setTemplate('case/manage');

        return $view;
    }

    public function buildPiHearingsTable($pi)
    {
        if (!isset($pi['piHearings'])) {
            return null;
        }

        return $this->buildTable('hearings', $pi['piHearings'], array());
    }

    public function getPiInfo($caseId)
    {
        $bundle = [
            'children' => [
                'piStatus' => [
                    'properties' => 'ALL',
                ],
                'piTypes' => [
                    'properties' => 'ALL',
                ],
                'presidingTc' => [
                    'properties' => 'ALL',
                ],
                'reasons' => [
                    'properties' => 'ALL',
                    'children' => [
                        'reason' => [
                            'properties' => 'ALL',
                        ]
                    ],
                ],
                'piHearings' => array(
                    'properties' => 'ALL',
                    'children' => [
                        'presidingTc' => [
                            'properties' => 'ALL',
                        ],
                        'presidedByRole' => [
                            'properties' => 'ALL',
                        ],
                    ],
                ),
                'decisionPresidingTc' => array(
                    'properties' => 'ALL'
                ),
                'decisions' => array(
                    'properties' => 'ALL'
                ),
                'assignedTo' => array(
                    'properties' => 'ALL'
                )
            ]
        ];

        $pis = $this->makeRestCall('Pi', 'GET', array('case' => $caseId, 'limit' => 1), $bundle);

        return current($pis['Results']);
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

        return call_user_func(array($this, strtolower($type)), $caseId);
    }

    /**
     * Add Public Inquiry data for a case
     *
     * @return ViewModel
     */
    public function sla()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'pi-sla',
            'processSave',
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
    public function agreed()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'pi-agreed',
            'processPi',
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
                //'headScript' => array('/static/js/impounding.js')
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
    public function schedule()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'pi-schedule',
            'processPi',
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
                //'headScript' => array('/static/js/impounding.js')
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
    public function headring()
    {
        $caseId = $this->fromRoute('case');
        $piId = $this->fromRoute('pi');

        $form = $this->generateFormWithData(
            'pi-hearing',
            'saveHearing',
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
                //'headScript' => array('/static/js/impounding.js')
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
    public function decision()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'pi-decision',
            'processPi',
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
                //'headScript' => array('/static/js/impounding.js')
            ]
        );

        $view->setTemplate('/form');

        return $view;
    }

    /**
     * Saves a Pi hearing record.
     *
     * @param array $data
     * @return mixed
     */
    public function saveHearing($data)
    {
        $fieldsetName = 'main';
        $data = array_merge($data, $data[$fieldsetName]);
        unset($data[$fieldsetName]);

        return $this->save($data, 'PiHearing');
    }

    /**
     * Processes an SLA form
     */
    public function processPi($data)
    {
        $this->processSave($data);

        return $this->redirect('case_pi', ['action' => 'index'], [], true);
    }

    /**
     * Edit Public Inquiry data for a case
     *
     * @return ViewModel
     */
    public function editAction()
    {
        return $this->addAction();
    }
}
