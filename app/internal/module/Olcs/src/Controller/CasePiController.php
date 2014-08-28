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
    protected $dataBundle = [
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
        $bundle = $this->getDataBundle();

        $pis = $this->makeRestCall('Pi', 'GET', array('case' => $caseId, 'limit' => 1), $bundle);

        return current($pis['Results']);
    }

    public function addEditAction()
    {
        $section = $this->fromRoute('section');

        return call_user_func(array($this, strtolower($section)));
    }

    /**
     * Generate a form with data
     *
     * @param string $name
     * @param callable $callback
     * @param mixed $data
     * @param boolean $tables
     * @return object
     */
    public function generateFormWithData($name, $callback, $data = null, $tables = false)
    {
        $form = $this->generateForm($name, $callback, $tables);

        if (!$this->getRequest()->isPost() && is_array($data)) {
            $form->setData($data);
        } else {
            if ($id = $this->fromRoute('id') && null != ($loadedData = $this->load($id))) {
                $form->setData($loadedData);
            }
        }
        return $form;
    }

    /**
     * Load data for the form
     *
     * This method should be overridden
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        $loadedData = parent::load($id);

        $loadedData = $this->processLoad($loadedData)

        return $loadedData;
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        return $data;
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
                    'pageTitle' => 'SLA',
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
                    'pageTitle' => 'Agreed and Legislation',
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
                    'pageTitle' => 'Schedule and Publish',
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
                    'pageTitle' => 'Hearing',
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
                    'pageTitle' => 'Register Decision',
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

        return $this->redirect()->toRoute('case_pi', ['action' => 'index'], [], true);
    }

    /**
     * Edit Public Inquiry data for a case
     *
     * @return ViewModel
     */
    public function addAction()
    {
        return $this->addEditAction();
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
