<?php

/**
 * Bus Service Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Bus\Service;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Service Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class BusServiceController extends BusController
{
    protected $layoutFile = 'layout/wide-layout';

    protected $section = 'service';
    protected $subNavRoute = 'licence_bus_register_service';

    protected $item = 'service';

    /* properties required by CrudAbstract */
    protected $formName = 'BusRegisterService';

    protected $identifierName = 'busRegId';

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = [
        'properties' => 'ALL',
        'children' => [
            'operatingCentre',
            'licence' => [
                'children' => [
                    'correspondenceCd'  => [
                        'children' => [
                            'address'
                        ]
                    ],
                    'operatingCentres'  => [
                        'children' => [
                            'operatingCentre' => [
                                'children' => [
                                    'address'
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'busNoticePeriod',
            'status',
            'variationReasons'
        ]
    ];

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'timetable',
                'conditions',
                'fields'
            )
        )
    );

    /**
     * Override to ensure params are set in route
     *
     * @return mixed|\Zend\Http\Response
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute('licence/bus-register-service', [], [], true);
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $data['timetable']['timetableAcceptable'] = $data['timetableAcceptable'];
        $data['timetable']['mapSupplied'] = $data['mapSupplied'];
        $data['timetable']['routeDescription'] = $data['routeDescription'];
        $data['conditions']['trcConditionChecked'] = $data['trcConditionChecked'];
        $data['conditions']['trcNotes'] = $data['trcNotes'];

        $variationReasons = [];

        foreach ($data['variationReasons'] as $reason) {
            $variationReasons[] = $reason['description'];
        }

        $data['variationReasons'] = implode(', ', $variationReasons);

        return parent::processLoad($data);
    }

    public function alterForm($form)
    {
        $data = $this->loadCurrent();

        if ($data['status']['id'] == 'breg_s_cancelled') {
            $form->remove('timetable');
        }

        // If Scottish rules identified by busNoticePeriod = 1, remove radio and replace with hidden field
        if ($data['busNoticePeriod']['id'] !== 1) {
            $form->get('fields')->remove('opNotifiedLaPte');
        } else {
            $form->get('fields')->remove('opNotifiedLaPteHidden');
        }

        $correspondenceAddress = [
            '' => 'Licence correspondence address: ' .
                $data['licence']['correspondenceCd']['address']['addressLine1'] .
                '' . $data['licence']['correspondenceCd']['address']['addressLine2'] .
                ' ' . $data['licence']['correspondenceCd']['address']['town']
        ];

        $newOptions = $correspondenceAddress +
        $form->get('fields')->get('operatingCentre')
            ->getValueOptions();

        // add correspondence address to list of OC addresses
        $form->get('fields')->get('operatingCentre')
            ->setValueOptions($newOptions);

        return $form;
    }

    /**
     * Gets a from from either a built or custom form config.
     * @param type $type
     * @return type
     */
    public function getForm($type)
    {
        $form = $this->getRegisterServiceForm();

        if (!$form->hasAttribute('action')) {
            $form->setAttribute('action', $this->getRequest()->getUri()->getPath());
        }

        return $form;
    }

    /**
     * Get safety form
     *
     * @return \Zend\Form\Form
     */
    protected function getRegisterServiceForm()
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm($this->formName);
        $formHelper->populateFormTable($form->get('conditions')->get('table'), $this->getConditionsTable());

        return $form;
    }

    /**
     * Get conditions table
     */
    protected function getConditionsTable()
    {
        return $this->getServiceLocator()->get('Table')->prepareTable('Bus/conditions', $this->getTableData());
    }

    /**
     * Get table data
     *
     * @return array
     */
    protected function getTableData()
    {
        $licence = $this->params()->fromRoute('licence');
        $data = $this->makeRestCall(
            'ConditionUndertaking',
            'GET',
            [
                'licence' => $licence,
                'conditionType' => 'cdt_con'
            ],
            $this->conditionsBundle
        );

        return $data['Results'];
    }


    /**
     * Holds the Conditions Bundle
     *
     * @var array
     */
    protected $conditionsBundle = array(
        'properties' => 'ALL',
        'children' => array(
            'case' => array(
                'properties' => array('id')
            ),
            'attachedTo' => array(
                'properties' => array('id', 'description')
            ),
            'operatingCentre' => array(
                'properties' => array('id'),
                'children' => array(
                    'address' => array(
                        'properties' => array(
                            'addressLine1',
                            'addressLine2',
                            'addressLine3',
                            'addressLine4',
                            'town',
                            'postcode'
                        ),
                        'children' => array(
                            'countryCode' => array(
                                'properties' => array(
                                    'id'
                                )
                            )
                        )
                    )
                )
            ),
            'addedVia' => array(
                'properties' => array('id', 'description')
            ),
        )
    );
}
