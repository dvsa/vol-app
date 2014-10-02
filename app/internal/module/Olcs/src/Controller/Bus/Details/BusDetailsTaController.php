<?php

/**
 * Bus Details Ta Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

/**
 * Bus Details Ta Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsTaController extends BusDetailsController
{
    protected $item = 'ta';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-reg-ta';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
            )
        )
    );

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'trafficAreas' => array(
                'children' => array(
                    'trafficArea' => array(
                        'id'
                    )
                )
            ),
            'localAuthoritys' => array(
                'id'
            )
        )
    );

    /**
     * Array of form fields to disable if this is EBSR
     */
    protected $disableFormFields = array(
        'trafficAreas',
        'localAuthoritys',
        'stoppingArrangements'
    );

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        $trafficAreas = array();

        foreach ($data['trafficAreas'] as $key => $record) {
            $trafficAreas[] = $record['trafficArea']['id'];
        }

        $data['trafficAreas'] = $trafficAreas;
        $data['fields']['trafficAreas'] = $trafficAreas;

        return $data;
    }
}
