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
    protected $section = 'service';
    protected $subNavRoute = 'licence_bus_service';

    protected $item = 'service';

    /* properties required by CrudAbstract */
    protected $formName = 'BusRegisterService';

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
     * Override to ensure any form submit redirects to alternative controller
     * details action.
     *
     * @return mixed|\Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    /**
     * Override to ensure any form submit redirects to alternative controller
     * details action.
     *
     * @return mixed|\Zend\Http\Response
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            'licence/bus-register-service',
            ['action' => 'edit'],
            [],
            true
        );
    }
    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        return parent::processLoad($data);
    }

    public function processSave($data)
    {
        return parent::processSave($data);
    }

    /**
     * Gets a from from either a built or custom form config.
     * @param type $type
     * @return type
     */
    public function getForm($type)
    {
        $form = $this->getRegisterServiceForm();

        // The vast majority of forms thus far don't have actions, but
        // that means when rendered out of context (e.g. in a JS modal) they
        // submit the parent page.
        // Adding an explicit attribute should be completely backwards compatible
        // because browsers interpret no action as submit the current page
        if (!$form->hasAttribute('action')) {
            $form->setAttribute('action', $this->getRequest()->getUri()->getPath());
        }

        $form = $this->processPostcodeLookup($form);

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
        $data = $this->makeRestCall('ConditionUndertaking', 'GET', [], $this->conditionsBundle);

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
