<?php

/**
 * Bus Details Service Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

/**
 * Bus Details Service Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsServiceController extends BusDetailsController
{
    protected $item = 'service';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-service-number-and-type';

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
            'subsidised' => array(
                'id'
            )
        )
    );

    /**
     * Array of form fields to disable if this is EBSR
     */
    /*protected $disableFormFields = array(
        'useAllStops',
        'hasManoeuvre',
        'manoeuvreDetail',
        'needNewStop',
        'newStopDetail',
        'hasNotFixedStop',
        'notFixedStopDetail',
        'subsidised',
        'subsidyDetail'
    );*/
}
