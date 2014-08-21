<?php

/**
 * Vehicle PSV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Application\VehicleSafety;

use Common\Form\Elements\Validators\TableRequiredValidator;

/**
 * Vehicle PSV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclePsvController extends VehicleSafetyController
{
    /**
     * Action service
     *
     * @var string
     */
    protected $actionService = 'Vehicle';

    /**
     * Holds the action data bundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'vrm',
            'makeModel',
            'psvType',
            'isNovelty'
        )
    );

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Holds the data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'totAuthSmallVehicles',
            'totAuthMediumVehicles',
            'totAuthLargeVehicles',
            'hasEnteredReg'
        ),
        'children' => array(
            'licence' => array(
                'properties' => null,
                'children' => array(
                    'licenceVehicles' => array(
                        'properties' => null,
                        'children' => array(
                            'vehicle' => array(
                                'properties' => array(
                                    'id',
                                    'vrm',
                                    'makeModel',
                                    'isNovelty'
                                ),
                                'children' => array(
                                    'psvType' => array(
                                        'properties' => array('id')
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * Holds the form tables
     *
     * @var array
     */
    protected $formTables = array(
        'small' => 'application_vehicle-safety_vehicle-psv-small',
        'medium' => 'application_vehicle-safety_vehicle-psv-medium',
        'large' => 'application_vehicle-safety_vehicle-psv-large'
    );

    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Add large vehicles
     *
     * @return Response
     */
    public function largeAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit large vehicles
     *
     * @return Response
     */
    public function largeEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete large vehicle
     *
     * @return Response
     */
    public function largeDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Add medium vehicles
     *
     * @return Response
     */
    public function mediumAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit medium vehicles
     *
     * @return Response
     */
    public function mediumEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete medium vehicle
     *
     * @return Response
     */
    public function mediumDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Add small vehicles
     *
     * @return Response
     */
    public function smallAddAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit small vehicles
     *
     * @return Response
     */
    public function smallEditAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete small vehicle
     *
     * @return Response
     */
    public function smallDeleteAction()
    {
        return $this->delete();
    }

    /**
     * Alter action form
     *
     * @param Form $form
     * @return Form
     */
    public function alterActionForm($form)
    {
        $actionName = $this->getActionName();

        if (!in_array($actionName, array('small-add', 'small-edit'))) {
            $form->get('data')->remove('isNovelty');
            $form->get('data')->remove('makeModel');
        }

        return $form;
    }

    /**
     * Remove vehicle size tables based on OC data
     *
     * @param Form $form
     * @return Form
     */
    public function alterForm($form)
    {
        $data = $this->load($this->getIdentifier());

        $isPost = $this->getRequest()->isPost();
        $post = $this->getRequest()->getPost();

        $isCrudPressed = (isset($post['large']['action']) && !empty($post['large']['action']))
            || (isset($post['medium']['action']) && !empty($post['medium']['action']))
            || (isset($post['small']['action']) && !empty($post['small']['action']));

        foreach (array_keys($this->formTables) as $table) {

            $ucTable = ucwords($table);

            if (isset($data['totAuth' . $ucTable . 'Vehicles']) && $data['totAuth' . $ucTable . 'Vehicles'] < 1) {

                $form->remove($table);

            } elseif (
                !$isCrudPressed && $isPost
                && isset($post['data']['hasEnteredReg']) && $post['data']['hasEnteredReg'] == 'Y'
            ) {
                $input = $form->getInputFilter()->get($table)->get('table');
                $input->setRequired(true)->setAllowEmpty(false)->setContinueIfEmpty(true);

                $validatorChain = $input->getValidatorChain();
                $validatorChain->attach(new TableRequiredValidator(array('label' => $table . ' vehicle')));
            }
        }

        if ($this->getLicenceType() == self::LICENCE_TYPE_RESTRICTED && $form->has('large')) {

            $form->remove('large');
        }

        return $form;
    }

    /**
     * Return the form table data
     *
     * @return array
     */
    protected function getFormTableData($id, $table)
    {
        $data = $this->load($id);

        $rows = array();

        $type = $this->getPsvTypeFromType($table);

        foreach ($data['licence']['licenceVehicles'] as $vehicle) {
            if (!isset($vehicle['vehicle']['psvType']['id'])
                || $vehicle['vehicle']['psvType']['id'] != $type) {
                continue;
            }

            $rows[] = $vehicle['vehicle'];
        }

        return $rows;
    }

    /**
     * Save the vehicle
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $parts = explode('-', $this->getActionName());

        $action = array_pop($parts);

        $this->saveVehicle($data, $action);
    }

    /**
     * Process load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        return array('data' => $data);
    }

    /**
     * Process action load
     *
     * @param array $data
     * @return array
     */
    protected function processActionLoad($data)
    {
        $parts = explode('-', $this->getActionName());

        $type = array_shift($parts);

        $data['psvType'] = $this->getPsvTypeFromType($type);

        return array('data' => $data);
    }

    /**
     * Get a PSV type from type
     *
     * @param string $type
     * @return string|null
     */
    private function getPsvTypeFromType($type)
    {
        switch ($type) {
            case 'large':
                return 'vhl_t_c'; // @todo Not sure if this is the right ref data key
            case 'medium':
                return 'vhl_t_b';
            case 'small':
                return 'vhl_t_a';
        }

        return null;
    }

    /**
     * Get a type from PSV type
     *
     * @param string $type
     * @return string|null
     */
    private function getTypeFromPsvType($type)
    {
        switch ($type) {
            case 'vhl_t_c':
                return 'large'; // @todo Not sure if this is the right ref data key
            case 'vhl_t_b':
                return 'medium';
            case 'vhl_t_a':
                return 'small';
        }

        return null;
    }
}
