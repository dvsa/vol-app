<?php

/**
 * Vehicle Undertakings Controller (OLCS-2855)
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Controller\Application\VehicleSafety;

/**
 * Vehicle Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class UndertakingsController extends VehicleSafetyController
{


    /**
     * Action service
     *
     * @var string
     */
    protected $actionService = 'Vehicle';

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
     * Holds the licenceDataBundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'trafficArea' => array(
                'properties' => array(
                    'id',
                    'applyScottishRules',
                ),
            )
        )
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
     * Placeholder for save
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {
    }

    /**
     * Save the vehicle
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $this->saveVehicle($data, $this->getActionName());
    }

    /**
     * Format the data for the form
     *
     * @param array $data
     * @return array
     */
    protected function processActionLoad($data)
    {
        return array('data' => $data);
    }

    /**
     * Add customisation to the table
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $data = $this->load($this->getIdentifier());

        if ( is_null($data['totAuthSmallVehicles']) ) {
            // no smalls - case 3
            $form->remove('smallVehiclesIntention');
            $form->remove('smallVehiclesUndertaking');
        } else {
            // Small vehicles - cases 1, 2, 4, 5
            if ( is_null($data['totAuthMediumVehicles'])
                    && is_null($data['totAuthLargeVehicles']) ) {
                // Small only, cases 1, 2
                if ( $data['trafficArea']['applyScottishRules'] ) {
                    // Case 2 - Scottish small only
                    $form->remove('smallVehiclesIntention');
                    $form->remove('nineOrMore');
                    $form->get('limousinesNoveltyVehicles')->remove('optLimousinesNine');
                } else {
                    // Case 1 - England/Wales small only
                    $form->remove('nineOrMore');
                    $form->get('limousinesNoveltyVehicles')->remove('optLimousinesNine');
                }
            } else {
                // cases 4, 5
                if ( $data['trafficArea']['applyScottishRules'] ) {
                    // Case 5 Mix Scotland
                    $form->remove('smallVehiclesIntention');
                    $form->remove('nineOrMore');
                } else {
                    // Case 4 Mix England/Wales
                    $form->remove('nineOrMore');
                }

            }
        }

        return $form;
    }
}