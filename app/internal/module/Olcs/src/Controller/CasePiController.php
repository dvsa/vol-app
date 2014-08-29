<?php

/**
 * Case Public Inquiry Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Common\Controller\CrudInterface;

/**
 * Class to manage Public Inquiry
 */
class CasePiController extends CaseController implements CrudInterface
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
            ),
            'case' => array(
                'properties' => ['id']
            ),
            'presidedByRole' => array(
                'properties' => ['id']
            ),
            'presidingTc' => array(
                'properties' => ['id']
            ),
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
                'main'
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

        $pi = $this->getPiInfoByCaseId($caseId);

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

    public function getPiInfoByCaseId($caseId)
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

    protected function alterFormBeforeValidation($form)
    {
        if ($form->get('main')->has('piTypes')) {
            $form->get('main')->get('piTypes')
                 ->setValueOptions(
                     $this->getListData('RefData',
                     ['refDataCategoryId' => 'pi_type'],
                     'id', 'id', false)
                 );
        }

        if ($form->get('main')->has('piTypes')) {
            $form->get('main')->get('assignedTo')
                 ->setValueOptions(
                     $this->getListData('User',
                     [],
                     'name', 'id', false)
                 );
        }

        if ($form->get('main')->has('reasons')) {
            $form->get('main')->get('reasons')
                 ->setValueOptions(
                     $this->getListData('Reason',
                     [],
                     'sectionCode', 'id', false)
                 );
        }

        if ($form->get('main')->has('presidingTc')) {
            $form->get('main')->get('presidingTc')
                 ->setValueOptions(
                     $this->getListData('PresidingTc',
                     [],
                     'name', 'id', false)
                 );
        }

        if ($form->get('main')->has('presidedByRole')) {
            $form->get('main')->get('presidedByRole')
                 ->setValueOptions(
                     $this->getListData('RefData',
                     ['refDataCategoryId' => 'tc_role'],
                     'id', 'id', false)
                 );
        }

        return $form;
    }

    /**
     * Generate a form with data
     *
     * @param string $name
     * @param callable $callback
     * @param mixed $data
     * @param boolean $tables
     *
     * @return \Zend\Form\Form
     */
    public function generateFormWithData($name, $callback, $data = null, $tables = false)
    {
        $formData = [];

        $id = $this->params()->fromRoute('id');

        if ((null !== $id) && null != ($loadedData = $this->load($id))) {

            $loadedData = $this->processLoad($loadedData);
            $formData = array_merge($formData, $loadedData);
        }

        if (!$this->getRequest()->isPost() && is_array($data)) {
            $formData = array_merge($formData, $data);
        }

        $form = $this->generateForm($name, $callback, $tables);

        $form->setData($formData);

        return $form;
    }

    /**
     * Map the data on load, we need to copy the data into the
     * main fieldset.
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        $data = $this->structureLoadDataForForm($data);

        $data['main'] = $data;

        return $data;
    }

    /**
     * Here we need to restructure the data to properly work
     * with the forms as they require IDs.
     *
     * @param unknown $data
     * @return unknown
     */
    public function structureLoadDataForForm($data)
    {
        $single = [
            'assignedTo', 'piStatus', 'case', 'presidingTc', 'presidedByRole'
        ];

        foreach ($single as $key) {
            if (isset($data[$key]) && is_array($data[$key]) && isset($data[$key]['id'])) {
                $data[$key] = $data[$key]['id'];
            } else if (is_array($data[$key]) && count($data[$key]) == 0) {
                $data[$key] = null;
            }
        }

        unset($single, $key);

        $multiple = [
            'piTypes', 'decisions', 'reasons', 'piHearings'
        ];

        foreach ($multiple as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                for($i=0; $i<count($data[$key]); $i++) {
                    $data[$key][$i] = $data[$key][$i]['id'];
                }
            }
        }

        unset($multiple, $key);

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
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'SLA',
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
            )
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
            )
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
    public function hearing()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'pi-hearing',
            'saveHearing',
            array(
                'case' => $caseId
            )
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
            )
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
        //die('<div>' . print_r($data, 1));

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
